@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6 pb-12">
        {{-- NAVEGACIÓN Y BREADCRUMB --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('sales.index', $viewId ? ['view_id' => $viewId] : []) }}" class="hover:text-brand-500 transition-colors">Ventas</a>
                    <i class="ri-arrow-right-s-line"></i>
                    <span>Detalle de Comprobante</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <span>Venta N° {{ $sale->number }}</span>
                    @php
                        $docName = mb_strtolower(trim((string) ($sale->documentType?->name ?? '')), 'UTF-8');
                        $status = $sale->status ?? 'A';
                        $statusColor = $status === 'A' ? 'success' : ($status === 'P' ? 'warning' : 'error');
                        $statusText = $status === 'A' ? 'Activo' : ($status === 'P' ? 'Pendiente' : 'Inactivo');
                    @endphp
                    <x-ui.badge variant="light" color="{{ $statusColor }}" class="text-xs font-semibold px-2.5 py-1">
                        {{ $statusText }}
                    </x-ui.badge>
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('sales.index', $viewId ? ['view_id' => $viewId] : []) }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-750">
                    <i class="ri-arrow-left-line"></i>
                    <span>Volver a Ventas</span>
                </a>

                @if(Route::has('admin.sales.print.pdf'))
                    <a href="{{ route('admin.sales.print.pdf', $sale->id) }}" target="_blank"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">
                        <i class="ri-file-pdf-line text-red-400"></i>
                        <span>Imprimir PDF</span>
                    </a>
                @endif

                @if(Route::has('admin.sales.print.ticket'))
                    <a href="{{ route('admin.sales.print.ticket', $sale->id) }}" target="_blank"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-700">
                        <i class="ri-printer-line"></i>
                        <span>Imprimir Ticket</span>
                    </a>
                @endif

                @php
                    $isEligibleSunat = str_contains($docName, 'boleta') || str_contains($docName, 'factura');
                    $isAlreadySentSunat = !empty($sale->electronic_invoice_external_id) || ($sale->electronic_invoice_status ?? '') === 'SENT';
                @endphp
                @if ($isEligibleSunat && !$isAlreadySentSunat && !$sale->trashed())
                    <form method="POST" action="{{ route('sales.emit.sunat', $sale->id) }}" class="inline-block">
                        @csrf
                        @if ($viewId)
                            <input type="hidden" name="view_id" value="{{ $viewId }}">
                        @endif
                        <button type="submit"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                            <i class="ri-cloud-upload-line"></i>
                            <span>Enviar a APISUNAT</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- RESUMEN TRIBUTARIO Y MONETARIO (CARDS) --}}
        @php
            $subtotal = round((float) ($sale->salesMovement?->subtotal ?? $sale->orderMovement?->subtotal ?? 0), 2);
            $tax = round((float) ($sale->salesMovement?->tax ?? $sale->orderMovement?->tax ?? 0), 2);
            $total = round((float) ($sale->salesMovement?->total ?? $sale->orderMovement?->total ?? 0), 2);
            $paymentType = strtoupper((string) ($sale->salesMovement?->payment_type ?? 'CONTADO'));
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Subtotal</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        <i class="ri-calculator-line text-lg"></i>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-extrabold text-gray-900 dark:text-white">S/ {{ number_format($subtotal, 2) }}</p>
                <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sin impuestos</span>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">IGV (18%)</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                        <i class="ri-percent-line text-lg"></i>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-extrabold text-amber-600 dark:text-amber-400">S/ {{ number_format($tax, 2) }}</p>
                <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">Impuesto fiscal</span>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-950/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Total Venta</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                        <i class="ri-money-dollar-circle-line text-lg"></i>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-black text-emerald-700 dark:text-emerald-400">S/ {{ number_format($total, 2) }}</p>
                <span class="mt-1 text-xs text-emerald-600/80 dark:text-emerald-400/80">Importe total pagado/cobrado</span>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Condición</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        <i class="ri-bank-card-line text-lg"></i>
                    </div>
                </div>
                <p class="mt-3 text-xl font-bold text-gray-900 dark:text-white">{{ $paymentType }}</p>
                <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">Forma de pago registrada</span>
            </div>
        </div>

        {{-- INFORMACIÓN DETALLADA EN 2 COLUMNAS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- DATOS DEL COMPROBANTE Y CLIENTE --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <i class="ri-user-3-line text-brand-500"></i>
                    <span>Información de la Venta</span>
                </h3>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tipo Documento</dt>
                        <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $sale->documentType?->name ?? 'Venta' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha y Hora</dt>
                        <dd class="mt-1 font-semibold text-gray-900 dark:text-white">
                            {{ $sale->moved_at ? $sale->moved_at->format('d/m/Y H:i:s') : '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cliente / Razón Social</dt>
                        <dd class="mt-1 font-bold text-gray-900 dark:text-white">{{ $sale->person_name ?? 'CLIENTES VARIOS' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">DNI / RUC</dt>
                        <dd class="mt-1 font-semibold text-gray-900 dark:text-white">
                            {{ $sale->person?->document_number ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sucursal</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-gray-200">{{ $sale->branch?->name ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vendedor / Usuario</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-gray-200">{{ $sale->user_name ?? '-' }}</dd>
                    </div>
                </dl>

                @if($sale->comment)
                    <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <span class="font-bold">Observación:</span> {{ $sale->comment }}
                    </div>
                @endif
            </div>

            {{-- FACTURACIÓN ELECTRÓNICA APISUNAT --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <i class="ri-cloud-line text-blue-500"></i>
                    <span>Estado SUNAT / Facturación Electrónica</span>
                </h3>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado Emisión</dt>
                        <dd class="mt-1 font-bold">
                            @if(!empty($sale->electronic_invoice_external_id) || ($sale->electronic_invoice_status ?? '') === 'SENT')
                                <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                    <i class="ri-checkbox-circle-fill"></i> Emitido / Aceptado SUNAT
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                    <i class="ri-time-line"></i> Pendiente de emisión
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Proveedor</dt>
                        <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ strtoupper($sale->electronic_invoice_provider ?? 'APISUNAT') }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Serie y Correlativo</dt>
                        <dd class="mt-1 font-mono font-bold text-blue-600 dark:text-blue-400">
                            {{ $sale->electronic_invoice_number ?: ($sale->salesMovement?->series ? $sale->salesMovement->series . '-' . $sale->number : $sale->number) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ID Externo (SUNAT)</dt>
                        <dd class="mt-1 font-mono text-xs text-gray-700 dark:text-gray-300 truncate" title="{{ $sale->electronic_invoice_external_id ?? '-' }}">
                            {{ $sale->electronic_invoice_external_id ?? '-' }}
                        </dd>
                    </div>
                </dl>

                @if(!empty($sale->electronic_invoice_pdf_ticket_url) || !empty($sale->electronic_invoice_pdf_a4_url))
                    <div class="flex flex-wrap items-center gap-2 pt-2">
                        @if(!empty($sale->electronic_invoice_pdf_ticket_url))
                            <a href="{{ $sale->electronic_invoice_pdf_ticket_url }}" target="_blank"
                                class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-800 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <i class="ri-ticket-line text-blue-500"></i> PDF Ticket (80mm)
                            </a>
                        @endif
                        @if(!empty($sale->electronic_invoice_pdf_a4_url))
                            <a href="{{ $sale->electronic_invoice_pdf_a4_url }}" target="_blank"
                                class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-800 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <i class="ri-file-text-line text-red-500"></i> PDF Formato A4
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- TABLA DE DETALLE DE PRODUCTOS / ITEMS --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-4 bg-gray-50 dark:bg-gray-800/40">
                <h3 class="font-bold text-gray-900 dark:text-white text-base flex items-center gap-2">
                    <i class="ri-shopping-basket-2-line text-emerald-500"></i>
                    <span>Detalle de Productos Vendidos</span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-900 text-white text-xs uppercase font-semibold">
                            <th class="px-6 py-3 text-center w-12">#</th>
                            <th class="px-6 py-3">Producto / Descripción</th>
                            <th class="px-6 py-3 text-center">Cantidad</th>
                            <th class="px-6 py-3 text-right">P. Unitario</th>
                            <th class="px-6 py-3 text-right">Impuesto</th>
                            <th class="px-6 py-3 text-right">Importe Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @php
                            $details = $sale->salesMovement?->details ?? $sale->orderMovement?->details ?? collect();
                        @endphp
                        @forelse($details as $idx => $detail)
                            @php
                                $qty = (float) ($detail->quantity ?? 1);
                                $courtesyQty = (float) ($detail->courtesy_quantity ?? 0);
                                $amount = (float) ($detail->amount ?? 0);
                                $unitPrice = $qty > 0 ? ($amount / $qty) : 0;
                                $prodName = $detail->product?->description ?? $detail->description ?? 'Producto #' . $detail->product_id;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 text-center font-medium text-gray-500 dark:text-gray-400">{{ $idx + 1 }}</td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900 dark:text-white">{{ $prodName }}</p>
                                    @if($detail->comment)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 italic">Nota: {{ $detail->comment }}</p>
                                    @endif
                                    @if($courtesyQty > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                            Cortésía: {{ $courtesyQty }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($qty, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-700 dark:text-gray-300">
                                    S/ {{ number_format($unitPrice, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right text-xs text-gray-500 dark:text-gray-400">
                                    18% IGV incl.
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                    S/ {{ number_format($amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No se encontraron productos registrados en este comprobante.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-bold text-gray-900 dark:bg-gray-800/60 dark:text-white">
                            <td colspan="5" class="px-6 py-3 text-right">TOTAL COMPROBANTE:</td>
                            <td class="px-6 py-3 text-right text-lg text-emerald-600 dark:text-emerald-400">S/ {{ number_format($total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- DESGLOSE DE MÉTODOS DE PAGO REGISTRADOS --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-4 bg-gray-50 dark:bg-gray-800/40">
                <h3 class="font-bold text-gray-900 dark:text-white text-base flex items-center gap-2">
                    <i class="ri-wallet-3-line text-blue-500"></i>
                    <span>Desglose de Pagos Registrados</span>
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800 text-xs uppercase font-semibold text-gray-600 dark:text-gray-300">
                            <th class="px-6 py-3">Método de Pago</th>
                            <th class="px-6 py-3">Detalle / Banco / Billetera</th>
                            <th class="px-6 py-3">Referencia / N° Op</th>
                            <th class="px-6 py-3 text-right">Monto Cobrado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @php
                            $cashDetails = $sale->cashMovement?->details ?? collect();
                        @endphp
                        @forelse($cashDetails as $pay)
                            @php
                                $pmName = $pay->payment_method ?: 'Efectivo';
                                $subDetail = $pay->bank ?: ($pay->digital_wallet ?: ($pay->card ?: '-'));
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                <td class="px-6 py-3.5 font-bold text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center gap-1.5">
                                        <i class="ri-checkbox-blank-circle-fill text-[8px] text-emerald-500"></i>
                                        {{ $pmName }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-gray-700 dark:text-gray-300">{{ $subDetail }}</td>
                                <td class="px-6 py-3.5 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $pay->number ?: ($pay->comment ?: '-') }}</td>
                                <td class="px-6 py-3.5 text-right font-bold text-gray-900 dark:text-white">S/ {{ number_format((float) $pay->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                                    {{ $paymentType === 'CREDIT' ? 'Venta registrada a Crédito (sin pagos inmediatos).' : 'No hay detalle de caja desglosado.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
