@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-slate-50 dark:bg-gray-900 py-4 sm:py-6 px-3 sm:px-6">
        <div class="mx-auto max-w-7xl space-y-6">

            {{-- BANNER DE BIENVENIDA MOZO --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-5 sm:p-6 shadow-lg text-white border border-slate-800">
                <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-medium text-indigo-200 border border-white/10">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Panel de Atención · Mozo
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                            👋 ¡Hola, {{ $waiterName }}!
                        </h1>
                        <p class="text-sm text-slate-300">
                            {{ ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY')) }} · <span class="text-indigo-300 font-semibold">{{ $activeOrdersCount }} mesa(s) activas</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- MÉTRICAS RÁPIDAS DEL DÍA --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                {{-- Total Pedidos --}}
                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-slate-200/80 dark:border-gray-700/80 shadow-xs flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl shrink-0">
                        <i class="ri-survey-line"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Pedidos Hoy</p>
                        <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $totalOrdersCount }}</p>
                    </div>
                </div>

                {{-- En Atención (Mesas Abiertas) --}}
                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-amber-200/80 dark:border-amber-900/50 shadow-xs flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl shrink-0">
                        <i class="ri-time-line"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">En Atención</p>
                        <p class="text-xl font-bold text-amber-900 dark:text-amber-200 mt-0.5">{{ $activeOrdersCount }}</p>
                    </div>
                </div>

                {{-- Completadas --}}
                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-emerald-200/80 dark:border-emerald-900/50 shadow-xs flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl shrink-0">
                        <i class="ri-checkbox-circle-line"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Atenciones Listas</p>
                        <p class="text-xl font-bold text-emerald-900 dark:text-emerald-200 mt-0.5">{{ $completedOrdersCount }}</p>
                    </div>
                </div>

                {{-- Total Ventas Atendidas --}}
                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-indigo-200/80 dark:border-indigo-900/50 shadow-xs flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl shrink-0">
                        <i class="ri-hand-coin-line"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider">Total Atendido</p>
                        <p class="text-xl font-bold text-indigo-900 dark:text-indigo-200 mt-0.5">S/ {{ number_format($totalSalesAmount, 2) }}</p>
                    </div>
                </div>
            </div>

            {{-- BARRA DE CONTROLES, NAVEGACIÓN Y BÚSQUEDA --}}
            <div class="rounded-2xl bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 p-4 shadow-sm space-y-4">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    
                    {{-- Toggle Alcance: Mis Pedidos | Todas las Mesas --}}
                    <div class="inline-flex rounded-xl p-1 bg-slate-100 dark:bg-gray-700/60 border border-slate-200 dark:border-gray-600 shrink-0">
                        <button type="submit" name="scope" value="my" 
                                class="px-4 py-2 text-xs font-bold rounded-lg transition-all {{ $scope === 'my' ? 'bg-white dark:bg-gray-900 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-gray-400 hover:text-slate-900' }}">
                            <i class="ri-user-3-line mr-1"></i> Mis Pedidos
                        </button>
                        <button type="submit" name="scope" value="all" 
                                class="px-4 py-2 text-xs font-bold rounded-lg transition-all {{ $scope === 'all' ? 'bg-white dark:bg-gray-900 text-slate-900 dark:text-white shadow-xs' : 'text-slate-600 dark:text-gray-400 hover:text-slate-900' }}">
                            <i class="ri-store-2-line mr-1"></i> Todas las Mesas ({{ $allVenueOrdersCount }})
                        </button>
                    </div>

                    {{-- Filtros de estado --}}
                    <div class="flex items-center gap-1 overflow-x-auto custom-scrollbar pb-1 md:pb-0">
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['filter' => 'all'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors {{ $filter === 'all' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-gray-700 dark:text-gray-300' }}">
                            Todos ({{ $totalOrdersCount }})
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['filter' => 'active'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors {{ $filter === 'active' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300' }}">
                            En Atención ({{ $activeOrdersCount }})
                        </a>
                        <a href="{{ route('dashboard', array_merge(request()->query(), ['filter' => 'completed'])) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors {{ $filter === 'completed' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                            Finalizados ({{ $completedOrdersCount }})
                        </a>
                    </div>

                    {{-- Búsqueda por Mesa o Producto --}}
                    <div class="relative w-full md:w-64">
                        <input type="text" name="search" value="{{ $search }}" 
                               placeholder="Buscar mesa o plato..." 
                               class="w-full pl-9 pr-8 py-2 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900 text-xs font-medium text-slate-800 dark:text-gray-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="ri-search-line absolute left-3 top-2.5 text-slate-400 text-sm"></i>
                        @if($search)
                            <a href="{{ route('dashboard', array_merge(request()->except('search'))) }}" 
                               class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs">
                                <i class="ri-close-line"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- TIMELINE DE PEDIDOS DE ATENCIÓN --}}
            @if($orders->isEmpty())
                <div class="rounded-3xl bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 p-8 sm:p-12 text-center shadow-xs">
                    <div class="mx-auto w-20 h-20 rounded-full bg-slate-100 dark:bg-gray-700/50 flex items-center justify-center text-slate-400 dark:text-gray-500 text-3xl mb-4">
                        <i class="ri-restaurant-line"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">No hay pedidos registrados en esta vista</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400 max-w-md mx-auto mt-1 mb-6">
                        @if($scope === 'my')
                            Aún no has registrado comandas hoy o la búsqueda no coincide. ¡Inicia la atención en las mesas!
                        @else
                            No se encontraron comandas o atenciones registradas hoy en el local con los filtros seleccionados.
                        @endif
                    </p>
                    <div class="flex justify-center gap-3">
                        <a href="{{ route('orders.index') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md">
                            <i class="ri-restaurant-2-line mr-1"></i> Ir al Mapa de Mesas
                        </a>
                        @if($scope === 'my')
                            <a href="{{ route('dashboard', ['scope' => 'all']) }}" class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-gray-700 text-slate-800 dark:text-gray-200 font-semibold text-xs">
                                Ver todas las mesas del local
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    <div class="flex items-center justify-between px-1">
                        <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-500 dark:text-gray-400 flex items-center gap-2">
                            <i class="ri-history-line text-indigo-500"></i> Historial de Atenciones del Día
                        </h2>
                        <span class="text-xs font-semibold text-slate-400">{{ $orders->count() }} resultado(s)</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($orders as $order)
                            @php
                                $statusStr = strtoupper((string) ($order->status ?? ''));
                                $isOpen = in_array($statusStr, ['PENDIENTE', 'P', 'EN_PROCESO', 'A', 'OPEN'], true);
                                $isClosed = in_array($statusStr, ['FINALIZADO', 'F', 'COBRADO', 'C', 'CLOSED'], true);

                                $tableNumber = $order->table ? ($order->table->number ?? $order->table->name ?? 'Mesa') : 'Mostrador';
                                $areaName = $order->area ? $order->area->name : null;
                                $timeFormatted = $order->created_at ? $order->created_at->format('H:i') : '--:--';
                                $elapsedMinutes = $order->created_at ? $order->created_at->diffInMinutes(now()) : 0;
                                $orderWaiterName = $order->movement?->person_name ?: ($order->movement?->person ? trim(($order->movement->person->first_name ?? '') . ' ' . ($order->movement->person->last_name ?? '')) : ($order->movement?->user_name ?? ($order->movement?->user?->name ?? '')));
                            @endphp

                            <div class="rounded-2xl bg-white dark:bg-gray-800 border {{ $isOpen ? 'border-amber-300/80 dark:border-amber-700/60 shadow-md shadow-amber-500/5' : 'border-slate-200 dark:border-gray-700 shadow-xs' }} flex flex-col overflow-hidden transition-all hover:shadow-lg">
                                
                                {{-- Cabecera de la Tarjeta de Pedido --}}
                                <div class="px-4 py-3 border-b {{ $isOpen ? 'bg-amber-50/70 dark:bg-amber-950/30 border-amber-200/80 dark:border-amber-900/40' : 'bg-slate-50 dark:bg-gray-800/80 border-slate-100 dark:border-gray-700' }} flex items-center justify-between">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-xl text-xs font-black {{ $isOpen ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-800 text-white dark:bg-gray-700' }}">
                                            @if($order->table)
                                                Mesa {{ $tableNumber }}
                                            @else
                                                Llevar / Directo
                                            @endif
                                        </span>
                                        @if($areaName)
                                            <span class="text-xs font-semibold text-slate-500 dark:text-gray-400 truncate">
                                                · {{ $areaName }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        @if($isOpen)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                                En atención
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">
                                                <i class="ri-check-line"></i> Finalizado
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Meta Info (Hora, Mozo, Personas) --}}
                                <div class="px-4 py-2 bg-slate-50/50 dark:bg-gray-800/40 border-b border-slate-100 dark:border-gray-700/50 text-[11px] text-slate-500 dark:text-gray-400 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-700 dark:text-gray-300">
                                            <i class="ri-time-line text-indigo-500"></i> {{ $timeFormatted }} 
                                            <span class="font-normal text-slate-400">({{ $elapsedMinutes < 1 ? 'ahora' : 'hace '.$elapsedMinutes.'m' }})</span>
                                        </span>
                                        @if($order->people_count)
                                            <span>· 👥 {{ $order->people_count }} pax</span>
                                        @endif
                                    </div>

                                    @if($orderWaiterName)
                                        <span class="font-medium text-slate-500 truncate max-w-[120px]" title="Atendido por: {{ $orderWaiterName }}">
                                            👤 {{ $orderWaiterName }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Detalle de Productos Comandados --}}
                                <div class="p-4 flex-1 space-y-2.5 bg-white dark:bg-gray-800 max-h-56 overflow-y-auto custom-scrollbar">
                                    @foreach($order->details as $detail)
                                        <div class="flex items-start justify-between gap-2 text-xs py-1 border-b border-slate-100 dark:border-gray-700/40 last:border-0">
                                            <div class="flex items-start gap-2 min-w-0">
                                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-gray-700 font-bold text-slate-800 dark:text-gray-200 shrink-0">
                                                    {{ (int) $detail->quantity }}x
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="font-bold text-slate-800 dark:text-gray-200 leading-tight">
                                                        {{ $detail->description }}
                                                    </p>
                                                    @if(!empty($detail->comment))
                                                        <p class="text-[11px] font-medium text-amber-700 dark:text-amber-400 mt-0.5 flex items-center gap-1">
                                                            <i class="ri-chat-1-line text-[10px]"></i> {{ $detail->comment }}
                                                        </p>
                                                    @endif
                                                    @if(!empty($detail->commanded_at))
                                                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium inline-block mt-0.5">
                                                            ✓ Comandado a las {{ $detail->commanded_at->format('H:i') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="font-semibold text-slate-600 dark:text-gray-400 shrink-0">
                                                S/ {{ number_format((float) $detail->amount, 2) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Footer con Total y Acciones --}}
                                <div class="px-4 py-3 bg-slate-50 dark:bg-gray-800/90 border-t border-slate-200/80 dark:border-gray-700 flex flex-col gap-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-slate-500 dark:text-gray-400">Total Pedido</span>
                                        <span class="text-base font-extrabold text-slate-900 dark:text-white">
                                            S/ {{ number_format((float) $order->total, 2) }}
                                        </span>
                                    </div>

                                    <div class="mt-1">
                                        @if($order->table_id)
                                            <a href="{{ route('orders.create', ['table_id' => $order->table_id]) }}" 
                                               class="w-full px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs text-center transition-all flex items-center justify-center gap-2 shadow-xs">
                                                <i class="ri-restaurant-2-line text-sm"></i>
                                                <span>{{ $isOpen ? 'Atender Mesa' : 'Ver Mesa' }}</span>
                                            </a>
                                        @else
                                            <a href="{{ route('orders.create', ['order_id' => $order->id]) }}" 
                                               class="w-full px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs text-center transition-all flex items-center justify-center gap-2 shadow-xs">
                                                <i class="ri-eye-line text-sm"></i>
                                                <span>Ver Detalle</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
