<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashRegister;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

use App\Services\PrintBridgeQueue;

class CashRegisterController extends Controller
{
    public function set(Request $request)
    {
        $branchId = session('branch_id');
        $request->validate([
            'cash_register_id' => [
                'required',
                Rule::exists('cash_registers', 'id')->where(function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId);
                }),
            ],
        ]);
        $caja = CashRegister::query()
            ->where('id', $request->cash_register_id)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        session([
            'cash_register_id' => $caja->id,
            'cash_register_name' => $caja->number,
            'cash_register_number' => $caja->number,
            'force_cash_register_modal' => false,
        ]);
        
        return back()->with('success', "Caja cambiada a: {$caja->number}");
    }

    public function select()
    {
        return redirect('/')->with('warning', 'Por favor seleccione una caja en la barra superior.');
    }

    public function openDrawer(Request $request, PrintBridgeQueue $queue)
    {
        $branchId = (int) (session('branch_id') ?: effective_branch_id());
        $printerName = trim((string) $request->input('printer_name', ''));
        
        if (!$printerName && $branchId) {
            $printerId = DB::table('branch_parameters as bp')
                ->join('parameters as p', 'p.id', '=', 'bp.parameter_id')
                ->where('bp.branch_id', $branchId)
                ->whereNull('bp.deleted_at')
                ->whereNull('p.deleted_at')
                ->where('p.description', 'Impresora de comprobantes y precuentas')
                ->value('bp.value');

            if ($printerId && is_numeric($printerId)) {
                $printerName = (string) \App\Models\PrinterBranch::where('id', $printerId)->value('name');
            }
        }

        if (!$printerName && $branchId) {
            $printerName = (string) \App\Models\PrinterBranch::where('branch_id', $branchId)->where('status', 'E')->value('name');
        }

        if (!$printerName) {
            return response()->json([
                'success' => false,
                'message' => 'No hay impresora configurada para abrir el cajon en esta sucursal.',
            ], 422);
        }

        // ESC/POS Pulse command bytes (Pin 2 + Pin 5)
        $escposPulse = "\x1B\x70\x00\x19\xFA\x1B\x70\x01\x19\xFA";

        if ($branchId) {
            $queue->push($branchId, $printerName, $escposPulse, 'caja');
        }

        return response()->json([
            'success' => true,
            'printer_name' => $printerName,
            'message' => "Pulso de apertura enviado a la impresora '{$printerName}'."
        ]);
    }
}
