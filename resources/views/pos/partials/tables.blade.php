<div class="space-y-10">
@foreach ($tablesWithLocations as $location => $tables)
    <div>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">
            {{ ucfirst($location) }} Localizador
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-5">
        @foreach ($tables as $table)
            @php
                $orderStatus = $table->active_order_status;
            @endphp

            <div
                id="table-{{ $table->id }}"
                class="table-item rounded-xl p-3 text-white cursor-pointer"
                style="
                    background-color:
                    {{
                        $orderStatus === 'processing'
                            ? 'yellow'
                            : (
                                in_array($orderStatus, ['ready_for_pickup', 'closed'])
                                    ? 'green'
                                    : '#9ca3af'
                            )
                    }};
                "
                onclick="selectTable({{ $table->id }})"
            >
                <h2 class="text-2xl font-bold text-center">{{ $table->name }}</h2>

                @if ($table->order_sum)
                    <p class="text-center mt-1 font-semibold">
                        $ {{ number_format($table->order_sum, 0, ',', '.') }}
                    </p>
                @endif
            </div>
        @endforeach
        </div>
    </div>
@endforeach
</div>
