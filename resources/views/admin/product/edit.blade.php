<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-y-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Edit Product') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Update the product details below.') }}
                </p>
            </div>

            <x-secondary-button onclick="window.location.href='{{ route('products.index') }}'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to Product List') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                <form method="POST" action="{{ route('products.update', $product->id) }}">
                    @csrf
                    @method('PATCH')

                    <div class="p-6 md:p-8 space-y-6">

                        {{-- Product Information --}}
                        <div class="space-y-4">
                            <h3
                                class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-2 mb-4">
                                Product Details
                            </h3>

                            {{-- Product Name --}}
                            <div>
                                <x-input-label for="name">
                                    {{ __('Product Name') }} <span class="text-red-500">*</span>
                                </x-input-label>
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                    :value="old('name', $product->name)" required autofocus autocomplete="off"
                                    placeholder="e.g., Laptop Pro 15 inch" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            {{-- SKU (Stock Keeping Unit) --}}
                            <div>
                                <x-input-label for="sku_category">
                                    {{ __('SKU (Stock Keeping Unit)') }} <span class="text-red-500">*</span>
                                </x-input-label>
                                <div class="mt-1 grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-2 items-start"
                                    x-data="{
                                        category: '{{ old('sku_category', $skuParts[0] ?? '') }}',
                                        productCode: '{{ old('sku_product_code', $skuParts[1] ?? '') }}',
                                        sequence: '{{ old('sku_sequence', $skuParts[2] ?? '') }}'
                                    }">

                                    {{-- Kode Kategori --}}
                                    <div class="col-span-1">
                                        <x-text-input id="sku_category"
                                            class="block w-full text-center uppercase tracking-wider" type="text"
                                            name="sku_category" placeholder="CAT" maxlength="4" x-model="category"
                                            x-on:input="category = $el.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 4)"
                                            required />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">Category
                                            Code (Max 4)</p>
                                        <x-input-error :messages="$errors->get('sku_category')" class="mt-1 text-center" />
                                    </div>

                                    {{-- Kode Produk --}}
                                    <div class="col-span-1">
                                        <x-text-input id="sku_product_code"
                                            class="block w-full text-center uppercase tracking-wider" type="text"
                                            name="sku_product_code" placeholder="PROD" maxlength="4"
                                            x-model="productCode"
                                            x-on:input="productCode = $el.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 4)"
                                            required />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">Product
                                            Code (Max 4)</p>
                                        <x-input-error :messages="$errors->get('sku_product_code')" class="mt-1 text-center" />
                                    </div>

                                    {{-- Nomor Urut --}}
                                    <div class="col-span-1">
                                        <x-text-input id="sku_sequence" class="block w-full text-center tracking-wider"
                                            type="text" name="sku_sequence" placeholder="001" maxlength="5"
                                            x-model="sequence"
                                            x-on:input="sequence = $el.value.replace(/[^0-9]/g, '').substring(0, 5)"
                                            required />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">Sequence
                                            (Max 5)</p>
                                        <x-input-error :messages="$errors->get('sku_sequence')" class="mt-1 text-center" />
                                    </div>

                                    <input type="hidden" name="sku">

                                    {{-- Tampilkan Preview SKU --}}
                                    <div class="sm:col-span-3 mt-2 text-center sm:text-left">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            Generated SKU Preview:
                                            <strong x-show="category && productCode && sequence"
                                                x-text="`${category}-${productCode}-${sequence}`.toUpperCase()"
                                                class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded"></strong>
                                            <em x-show="!category || !productCode || !sequence"
                                                class="text-gray-400 dark:text-gray-500 text-xs">Fill all parts</em>
                                        </p>
                                        <x-input-error :messages="$errors->get('sku')" class="mt-1" />
                                    </div>
                                </div>
                            </div>

                            {{-- Description --}}
                            <div>
                                <x-input-label for="description" :value="__('Description (Optional)')" />
                                <textarea id="description" name="description" rows="4"
                                    placeholder="Enter product details, features, or specifications..."
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $product->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Inventory --}}
                        <div class="space-y-4 pt-6 border-t dark:border-gray-700">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                Inventory
                            </h3>
                            <div>
                                <x-input-label for="current_stock" :value="__('Current Stock Quantity')" />
                                <div class="flex items-center gap-3 mt-1">
                                    <x-text-input id="current_stock_display"
                                        class="block w-full bg-gray-100 dark:bg-gray-700" type="number"
                                        name="current_stock_display" :value="$product->current_stock" disabled />

                                    {{-- Tombol untuk 'Adjust Stock' --}}
                                    <x-secondary-button type="button" class="flex-shrink-0"
                                        onclick="window.location.href='{{ route('stock-management.create') }}?product_id={{ $product->id }}'">

                                        {{-- Ikon untuk penyesuaian --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        {{ __('Adjust Stock') }}
                                    </x-secondary-button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Stock can only be changed via the "Stock Management" menu') }}
                                </p>
                            </div>
                        </div>

                    </div>

                    {{-- Action Buttons in Footer --}}
                    <div
                        class="flex items-center justify-end px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700 sm:rounded-b-lg">
                        <x-secondary-button type="button"
                            onclick="window.location.href='{{ route('products.index') }}'">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                        {{-- Ganti Teks Tombol --}}
                        <x-primary-button class="ms-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('Update Product') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
