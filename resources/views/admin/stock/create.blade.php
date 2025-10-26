<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-y-2">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Add / Reduce Stock') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Manage your product stock inventory') }}
                </p>
            </div>

            <x-secondary-button onclick="window.location.href='{{ route('stock-management.index') }}'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to Stock Movement') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('stock-management.store') }}" x-data="{
                    type: '{{ old('type', request()->query('type', 'in')) }}',
                    product_id: '{{ old('product_id', request()->query('product_id', '')) }}',
                    quantity: '{{ old('quantity', '') }}',
                    currentStock: 0,
                    products: {{ Js::from($products) }},
                    updateCurrentStock() {
                        if (this.product_id) {
                            const product = this.products.find(p => p.id == this.product_id);
                            this.currentStock = product ? product.current_stock : 0;
                        } else {
                            this.currentStock = 0;
                        }
                    }
                }"
                    x-init="updateCurrentStock()">
                    @csrf

                    <div class="p-6 md:p-8 space-y-6">

                        {{-- Stock Movement Type --}}
                        <div class="space-y-4">
                            <h3
                                class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-2 mb-4">
                                {{ __('Stock Movement Details') }}
                            </h3>

                            <div>
                                <x-input-label for="type">
                                    {{ __('Movement Type') }} <span class="text-red-500">*</span>
                                </x-input-label>
                                <div class="mt-2 grid grid-cols-2 gap-4">
                                    <label
                                        class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none transition-all duration-150"
                                        :class="type === 'in'
                                            ?
                                            'border-green-500 dark:border-green-600 bg-green-50 dark:bg-green-900/20 ring-2 ring-green-500 dark:ring-green-600' :
                                            'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'">
                                        <input type="radio" name="type" value="in" class="sr-only"
                                            x-model="type" required>
                                        <span class="flex flex-1">
                                            <span class="flex flex-col">
                                                <span class="flex items-center text-sm font-medium"
                                                    :class="type === 'in' ? 'text-green-900 dark:text-green-100' :
                                                        'text-gray-900 dark:text-gray-100'">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                                    </svg>
                                                    {{ __('Add Stock') }}
                                                </span>
                                                <span class="mt-1 flex items-center text-xs"
                                                    :class="type === 'in' ? 'text-green-600 dark:text-green-300' :
                                                        'text-gray-500 dark:text-gray-400'">
                                                    {{ __('Increase product quantity') }}
                                                </span>
                                            </span>
                                        </span>
                                        <svg class="h-5 w-5 text-green-600" x-show="type === 'in'" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </label>

                                    <label
                                        class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none transition-all duration-150"
                                        :class="type === 'out'
                                            ?
                                            'border-red-500 dark:border-red-600 bg-red-50 dark:bg-red-900/20 ring-2 ring-red-500 dark:ring-red-600' :
                                            'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'">
                                        <input type="radio" name="type" value="out" class="sr-only"
                                            x-model="type" required>
                                        <span class="flex flex-1">
                                            <span class="flex flex-col">
                                                <span class="flex items-center text-sm font-medium"
                                                    :class="type === 'out' ? 'text-red-900 dark:text-red-100' :
                                                        'text-gray-900 dark:text-gray-100'">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                                    </svg>
                                                    {{ __('Reduce Stock') }}
                                                </span>
                                                <span class="mt-1 flex items-center text-xs"
                                                    :class="type === 'out' ? 'text-red-600 dark:text-red-300' :
                                                        'text-gray-500 dark:text-gray-400'">
                                                    {{ __('Decrease product quantity') }}
                                                </span>
                                            </span>
                                        </span>
                                        <svg class="h-5 w-5 text-red-600" x-show="type === 'out'" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            {{-- Product Selection --}}
                            <div>
                                <x-input-label for="product_id">
                                    {{ __('Select Product') }} <span class="text-red-500">*</span>
                                </x-input-label>
                                <select id="product_id" name="product_id"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    x-model="product_id" @change="updateCurrentStock()" required>
                                    <option value="">{{ __('-- Select a product --') }}</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->name }} ({{ $product->sku }}) - Current Stock:
                                            {{ $product->current_stock }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('product_id')" class="mt-2" />

                                {{-- Current Stock Display --}}
                                <div x-show="product_id" x-transition class="mt-3 p-4 rounded-lg"
                                    :class="type === 'in' ?
                                        'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' :
                                        'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium"
                                                :class="type === 'in' ? 'text-green-800 dark:text-green-200' :
                                                    'text-red-800 dark:text-red-200'">
                                                {{ __('Current Stock') }}
                                            </p>
                                            <p class="text-2xl font-bold mt-1"
                                                :class="type === 'in' ? 'text-green-900 dark:text-green-100' :
                                                    'text-red-900 dark:text-red-100'"
                                                x-text="currentStock + ' units'">
                                            </p>
                                        </div>
                                        <div class="text-right" x-show="quantity > 0">
                                            <p class="text-sm font-medium"
                                                :class="type === 'in' ? 'text-green-800 dark:text-green-200' :
                                                    'text-red-800 dark:text-red-200'">
                                                {{ __('After Transaction') }}
                                            </p>
                                            <p class="text-2xl font-bold mt-1"
                                                :class="type === 'in' ? 'text-green-900 dark:text-green-100' :
                                                    'text-red-900 dark:text-red-100'"
                                                x-text="(type === 'in' ? (parseInt(currentStock) + parseInt(quantity || 0)) : (parseInt(currentStock) - parseInt(quantity || 0))) + ' units'">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Quantity --}}
                            <div>
                                <x-input-label for="quantity">
                                    {{ __('Quantity') }} <span class="text-red-500">*</span>
                                </x-input-label>
                                <x-text-input id="quantity" class="block mt-1 w-full" type="number"
                                    name="quantity" x-model="quantity" :value="old('quantity')" required min="1"
                                    placeholder="Enter quantity" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Enter the number of units to add or reduce') }}
                                </p>
                                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />

                                {{-- Warning for stock out --}}
                                <div x-show="type === 'out' && product_id && quantity > 0 && quantity > currentStock"
                                    x-transition
                                    class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-2 flex-shrink-0"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                                {{ __('Insufficient Stock!') }}
                                            </p>
                                            <p class="text-xs text-red-700 dark:text-red-300 mt-1">
                                                {{ __('The quantity you entered exceeds the current stock. Please reduce the quantity.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                <textarea id="notes" name="notes" rows="4"
                                    placeholder="Enter notes about this stock movement (e.g., supplier, reason, etc.)"
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Summary Card --}}
                        <div x-show="product_id && quantity > 0" x-transition
                            class="p-6 rounded-lg border-2 border-dashed"
                            :class="type === 'in' ?
                                'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/10' :
                                'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/10'">
                            <h4 class="font-semibold text-lg mb-4"
                                :class="type === 'in' ? 'text-green-900 dark:text-green-100' :
                                    'text-red-900 dark:text-red-100'">
                                {{ __('Transaction Summary') }}
                            </h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span
                                        :class="type === 'in' ? 'text-green-700 dark:text-green-300' :
                                            'text-red-700 dark:text-red-300'">
                                        {{ __('Movement Type:') }}
                                    </span>
                                    <span class="font-semibold"
                                        :class="type === 'in' ? 'text-green-900 dark:text-green-100' :
                                            'text-red-900 dark:text-red-100'"
                                        x-text="type === 'in' ? 'Stock In' : 'Stock Out'">
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span
                                        :class="type === 'in' ? 'text-green-700 dark:text-green-300' :
                                            'text-red-700 dark:text-red-300'">
                                        {{ __('Quantity:') }}
                                    </span>
                                    <span class="font-semibold"
                                        :class="type === 'in' ? 'text-green-900 dark:text-green-100' :
                                            'text-red-900 dark:text-red-100'"
                                        x-text="(type === 'in' ? '+' : '-') + quantity + ' units'">
                                    </span>
                                </div>
                                <div class="pt-2 mt-2 border-t"
                                    :class="type === 'in' ? 'border-green-300 dark:border-green-700' :
                                        'border-red-300 dark:border-red-700'">
                                    <div class="flex justify-between">
                                        <span
                                            :class="type === 'in' ? 'text-green-700 dark:text-green-300' :
                                                'text-red-700 dark:text-red-300'">
                                            {{ __('Current Stock:') }}
                                        </span>
                                        <span class="font-semibold"
                                            :class="type === 'in' ? 'text-green-900 dark:text-green-100' :
                                                'text-red-900 dark:text-red-100'"
                                            x-text="currentStock + ' units'">
                                        </span>
                                    </div>
                                    <div class="flex justify-between mt-1">
                                        <span class="font-semibold"
                                            :class="type === 'in' ? 'text-green-800 dark:text-green-200' :
                                                'text-red-800 dark:text-red-200'">
                                            {{ __('New Stock:') }}
                                        </span>
                                        <span class="font-bold text-lg"
                                            :class="type === 'in' ? 'text-green-900 dark:text-green-100' :
                                                'text-red-900 dark:text-red-100'"
                                            x-text="(type === 'in' ? (parseInt(currentStock) + parseInt(quantity)) : (parseInt(currentStock) - parseInt(quantity))) + ' units'">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Action Buttons --}}
                    <div
                        class="flex items-center justify-end px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700 sm:rounded-b-lg gap-3">
                        <x-secondary-button type="button"
                            onclick="window.location.href='{{ route('stock-management.index') }}'">
                            {{ __('Cancel') }}
                        </x-secondary-button>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                            :class="type === 'in'
                                ?
                                'bg-green-600 hover:bg-green-700 active:bg-green-800 focus:ring-green-500' :
                                'bg-red-600 hover:bg-red-700 active:bg-red-800 focus:ring-red-500'">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span
                                x-text="type === 'in' ? '{{ __('Add Stock') }}' : '{{ __('Reduce Stock') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
