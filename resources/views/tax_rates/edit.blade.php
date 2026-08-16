@php
    $initialTaxRate = $taxRate ?? null;
    $viewId = request('view_id');
@endphp

<x-ui.modal
    x-data="{
        open: {{ $initialTaxRate ? 'true' : 'false' }},
        taxRateId: {{ \Illuminate\Support\Js::from($initialTaxRate?->id) }},
        code: {{ \Illuminate\Support\Js::from($initialTaxRate?->code ?? '') }},
        description: {{ \Illuminate\Support\Js::from($initialTaxRate?->description ?? '') }},
        tax_rate: {{ \Illuminate\Support\Js::from($initialTaxRate?->tax_rate ?? '') }},
        order_num: {{ \Illuminate\Support\Js::from($initialTaxRate?->order_num ?? '') }},
        status: {{ \Illuminate\Support\Js::from((bool) ($initialTaxRate?->status ?? true)) }}
    }"
    @open-edit-tax-rate-modal.window="
        open = true;
        taxRateId = $event.detail.id;
        code = $event.detail.code;
        description = $event.detail.description;
        tax_rate = $event.detail.tax_rate;
        order_num = $event.detail.order_num;
        status = !!$event.detail.status;
    "
    @close-edit-tax-rate-modal.window="open = false"
    :isOpen="$initialTaxRate ? true : false"
    :showCloseButton="false"
    class="max-w-3xl"
>
    <div class="p-6 sm:p-8">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#111827]/10 text-[#111827] dark:bg-[#111827]/20">
                    <i class="ri-percent-line text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Editar tasa de impuesto</h3>
                    <p class="mt-1 text-sm text-gray-500">Actualiza la informacion de la tasa de impuesto.</p>
                </div>
            </div>
            <button
                type="button"
                @click="open = false"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                aria-label="Cerrar"
            >
                <i class="ri-close-line text-xl"></i>
            </button>
        </div>

        @if ($errors->any())
            <div class="mb-5">
                <x-ui.alert variant="error" title="Revisa los campos" message="Hay errores en el formulario, corrige los datos e intenta nuevamente." />
            </div>
        @endif

        <form
            method="POST"
            x-bind:action="taxRateId ? '{{ url('/admin/herramientas/tasas-impuesto') }}/' + taxRateId + '{{ $viewId ? '?view_id=' . $viewId : '' }}' : '#'"
            class="space-y-6"
        >
            @csrf
            @method('PUT')
            @if ($viewId)
                <input type="hidden" name="view_id" value="{{ $viewId }}">
            @endif

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Codigo</label>
                    <input
                        type="text"
                        name="code"
                        x-model="code"
                        required
                        placeholder="Ingrese el codigo"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-[#111827] focus:ring-[#111827]/10 dark:focus:border-[#111827] h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Descripcion</label>
                    <input
                        type="text"
                        name="description"
                        x-model="description"
                        required
                        placeholder="Ingrese la descripcion"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-[#111827] focus:ring-[#111827]/10 dark:focus:border-[#111827] h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tasa de impuesto (%)</label>
                    <input
                        type="number"
                        step="0.01"
                        name="tax_rate"
                        x-model="tax_rate"
                        required
                        placeholder="Ingrese la tasa de impuesto"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-[#111827] focus:ring-[#111827]/10 dark:focus:border-[#111827] h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Orden</label>
                    <input
                        type="number"
                        name="order_num"
                        x-model="order_num"
                        required
                        placeholder="Ingrese el orden"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-[#111827] focus:ring-[#111827]/10 dark:focus:border-[#111827] h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Estado</label>
                    <div class="flex items-center gap-2 pt-2">
                        <input
                            type="checkbox"
                            name="status"
                            value="1"
                            x-model="status"
                            class="h-4 w-4 rounded border-gray-300 text-[#111827] focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800"
                        />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Activo</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-ui.button type="submit" size="md" variant="primary">
                    <i class="ri-save-line"></i>
                    <span>Actualizar</span>
                </x-ui.button>
                <x-ui.button type="button" size="md" variant="outline" @click="open = false">
                    <i class="ri-close-line"></i>
                    <span>Cancelar</span>
                </x-ui.button>
            </div>
        </form>
    </div>
</x-ui.modal>
