<?php

namespace App\Services;

use App\Models\PrintJob;
use App\Models\PrinterBranch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrintBridgeQueue
{
    public function stationPrinterNames(): array
    {
        $names = array_merge(
            config('qz.secondary_first_printer_names', []),
            config('qz.tertiary_first_printer_names', [])
        );

        return array_values(array_unique(array_filter(array_map('trim', $names))));
    }

    public function shouldQueueToStation(PrinterBranch $printer, bool $remoteRequest = false): bool
    {
        if (! config('qz.enabled', true)) {
            return false;
        }

        return $remoteRequest || ! filled((string) $printer->ip);
    }

    public function isStationPrinterName(string $name): bool
    {
        $normalized = mb_strtolower(trim($name));
        if ($normalized === '') {
            return false;
        }

        foreach ($this->stationPrinterNames() as $allowed) {
            if ($normalized === mb_strtolower(trim($allowed))) {
                return true;
            }
        }

        return false;
    }

    public function push(int $branchId, string $printerName, string $escposRaw, string $kind = 'comanda'): PrintJob
    {
        return PrintJob::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_id' => $branchId,
            'requested_by' => auth()->id(),
            'printer_name' => trim($printerName),
            'kind' => $kind,
            'payload_base64' => base64_encode($escposRaw),
            'status' => 'pending',
        ]);
    }

    /** Reserva un trabajo sin sacarlo de Pendiente. */
    public function peek(int $branchId, ?string $printerName = null): ?array
    {
        return DB::transaction(function () use ($branchId, $printerName) {
            $claimTimeout = max(1, (int) config('print_bridge.claim_timeout_seconds', 3));

            $job = PrintJob::query()
                ->where('branch_id', $branchId)
                ->whereExists(function ($exists) {
                    $exists->select(DB::raw(1))
                        ->from('printers_branch')
                        ->whereColumn('printers_branch.branch_id', 'print_jobs.branch_id')
                        ->where('printers_branch.status', 'E')
                        ->whereRaw('LOWER(TRIM(printers_branch.name)) = LOWER(TRIM(print_jobs.printer_name))');
                })
                ->when(filled($printerName), fn ($query) => $query->whereRaw(
                    'LOWER(TRIM(printer_name)) = ?',
                    [mb_strtolower(trim((string) $printerName))]
                ))
                ->where(function ($query) use ($claimTimeout) {
                    $query->where('status', 'pending')
                        ->orWhere(function ($stale) use ($claimTimeout) {
                            $stale->where('status', 'processing')
                                ->where('claimed_at', '<=', now()->subSeconds($claimTimeout));
                        });
                })
                ->where(function ($query) use ($claimTimeout) {
                    $query->whereNull('claimed_at')
                        ->orWhere('claimed_at', '<=', now()->subSeconds($claimTimeout));
                })
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if (! $job) {
                return null;
            }

            $job->update([
                'claimed_at' => now(),
                'attempts' => $job->attempts + 1,
                'last_error' => null,
            ]);

            return [
                'id' => $job->uuid,
                'b64' => $job->payload_base64,
                'kind' => $job->kind,
                'attempts' => $job->attempts,
                'printer_name' => $job->printer_name,
            ];
        }, 3);
    }

    public function ack(int $branchId, string $printerName, string $jobUuid): bool
    {
        return PrintJob::query()
            ->where('branch_id', $branchId)
            ->where('uuid', $jobUuid)
            ->whereRaw('LOWER(TRIM(printer_name)) = ?', [mb_strtolower(trim($printerName))])
            ->whereIn('status', ['pending', 'processing'])
            ->update([
                'status' => 'printed',
                'printed_at' => now(),
                'claimed_at' => null,
                'last_error' => null,
                'updated_at' => now(),
            ]) > 0;
    }

    public function fail(int $branchId, string $printerName, string $jobUuid, string $error): bool
    {
        return PrintJob::query()
            ->where('branch_id', $branchId)
            ->where('uuid', $jobUuid)
            ->whereRaw('LOWER(TRIM(printer_name)) = ?', [mb_strtolower(trim($printerName))])
            ->whereIn('status', ['pending', 'processing'])
            ->update([
                'status' => 'failed',
                'last_error' => Str::limit(trim($error), 1000, ''),
                'claimed_at' => null,
                'updated_at' => now(),
            ]) > 0;
    }

    public function retry(int $branchId, int $jobId): bool
    {
        return PrintJob::query()
            ->where('branch_id', $branchId)
            ->where('id', $jobId)
            ->where('status', 'failed')
            ->update([
                'status' => 'pending',
                'claimed_at' => null,
                'last_error' => null,
                'updated_at' => now(),
            ]) > 0;
    }

    public function discard(int $branchId, int $jobId): bool
    {
        return PrintJob::query()
            ->where('branch_id', $branchId)
            ->where('id', $jobId)
            ->whereIn('status', ['pending', 'processing', 'failed'])
            ->delete() > 0;
    }

    public function markPrinted(int $branchId, int $jobId): bool
    {
        return PrintJob::query()->where('branch_id', $branchId)->where('id', $jobId)->update([
            'status' => 'printed', 'printed_at' => now(), 'claimed_at' => null,
            'last_error' => null, 'updated_at' => now(),
        ]) > 0;
    }

    public function markFailed(int $branchId, int $jobId, string $error): bool
    {
        return PrintJob::query()->where('branch_id', $branchId)->where('id', $jobId)->update([
            'status' => 'failed', 'claimed_at' => null,
            'last_error' => Str::limit(trim($error), 1000, ''), 'updated_at' => now(),
        ]) > 0;
    }

    public function requeue(int $branchId, int $jobId): bool
    {
        return PrintJob::query()->where('branch_id', $branchId)->where('id', $jobId)->update([
            'status' => 'pending', 'claimed_at' => null, 'printed_at' => null,
            'last_error' => null, 'updated_at' => now(),
        ]) > 0;
    }

    public function unresolvedForBranch(int $branchId, int $limit = 20)
    {
        return PrintJob::query()
            ->where('branch_id', $branchId)
            ->whereIn('status', ['pending', 'processing', 'failed'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
