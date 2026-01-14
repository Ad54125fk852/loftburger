<div class="grid grid-cols-1 @if ($isKitchenStaff) lg:grid-cols-2 @endif gap-8">

    {{-- =====================
         MESAS
    ====================== --}}
    <div class="flex flex-col space-y-4">
        <h2 class="text-2xl font-semibold text-gray-700 text-center bg-white p-3 rounded-lg shadow-sm sticky top-0 z-10">
            <i class="fas fa-utensils mr-2"></i> Ordenes
        </h2>

        <div class="space-y-6">
            @forelse ($tableOrders as $tableId => $ordersForTable)
                <div class="bg-white p-4 rounded-xl shadow-lg">
                    <h3 class="text-xl font-bold text-blue-600 mb-3">
                        Mesa {{ $ordersForTable->first()->table->name ?? 'N/A' }}
                    </h3>

                    <div class="flex flex-wrap -m-2">
                        @foreach ($ordersForTable as $order)
                            <div
                                id="kot-order-{{ $order->id }}"
                                class="w-full sm:w-1/2 @if ($isKitchenStaff) lg:w-full xl:w-1/2 @endif p-2"
                            >
                                <x-dynamic-component
                                    :component="$currentOrderComponent"
                                    :order="$order"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-500 bg-white rounded-lg shadow">
                    <p>No active table orders.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- =====================
         TAKE AWAY
    ====================== --}}
    @if ($isKitchenStaff)
        <div class="flex flex-col space-y-4">
            <h2 class="text-2xl font-semibold text-gray-700 text-center bg-white p-3 rounded-lg shadow-sm sticky top-0 z-10">
                <i class="fas fa-shopping-bag mr-2"></i> Otros
            </h2>

            @if ($takeawayOrders->isNotEmpty())
                <div class="bg-white p-4 rounded-xl shadow-lg">
                    <div class="flex flex-wrap -m-2">
                        @foreach ($takeawayOrders as $order)
                            <div
                                id="kot-order-{{ $order->id }}"
                                class="w-full sm:w-1/2 lg:w-1/3 p-2 min-w-fit"
                            >
                                <x-dynamic-component
                                    :component="$currentOrderComponent"
                                    :order="$order"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="w-full text-center py-10 text-gray-500 bg-white rounded-lg shadow">
                    <p>No active takeaway orders.</p>
                </div>
            @endif
        </div>
    @endif

</div>
