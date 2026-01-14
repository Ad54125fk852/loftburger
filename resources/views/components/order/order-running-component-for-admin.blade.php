<div id="order-{{ $order->id }}">
    <x-main-order-component :order="$order">

    <div style="" class="text-center mb-3">
    <span
        id="order-status-{{ $order->id }}"
        class="px-3 py-1 rounded text-white text-sm
            @if ($order->status === App\Enums\OrderStatus::New) bg-yellow-500
            @elseif ($order->status === App\Enums\OrderStatus::Processing) bg-blue-500
            @elseif ($order->status === App\Enums\OrderStatus::ReadyForPickup) bg-green-600
            @elseif ($order->status === App\Enums\OrderStatus::Served) bg-indigo-600
            @elseif ($order->status === App\Enums\OrderStatus::Closed) bg-gray-600
            @elseif ($order->status === App\Enums\OrderStatus::Cancelled) bg-red-600
            @endif
        ">
        {{ App\Enums\OrderStatus::getDescription($order->status) }}
    </span>
</div>

<!-- {{-- Mark as Prepared --}}
    @if (
        $order->status == App\Enums\OrderStatus::New ||
        $order->status == App\Enums\OrderStatus::Processing
    )
        <div class="flex justify-center options">
            <button
                class="bg-green-600 w-full m-2 p-2 rounded"
                onclick="markAsPrepared(@json($order->id))">
                Completado
            </button>
        </div>
    @endif 

    {{-- Table orders --}}
    @if ($order->table_id)

        {{-- Mark as Served --}}
        @if ($order->status == App\Enums\OrderStatus::ReadyForPickup)
            <div class="flex justify-center options">
                <button
                    class="bg-green-600 w-full m-2 p-2 rounded"
                    onclick="markAsServed(@json($order->id))">
                    Completado
                </button>
            </div>
        @endif

    {{-- Pickup orders --}}
    @else

        {{-- Mark as Closed --}}
        @if (
            $order->status == App\Enums\OrderStatus::ReadyForPickup ||
            $order->status == App\Enums\OrderStatus::Served
        )
            <div class="flex justify-center options">
                <button
                    class="bg-green-600 w-full m-2 p-2 rounded"
                    onclick="markAsClosed(@json($order->id))">
                    Completado
                </button>
            </div>
        @endif

    @endif-->

    {{-- 🔴 CANCEL / PRE-RECEIPT BUTTONS --}}
@if (
    $order->status != App\Enums\OrderStatus::Closed &&
    $order->status != App\Enums\OrderStatus::Cancelled
)
    <div class="flex justify-center options flex-col">

        {{-- 🧾 PRE-RECEIPT / VER CUENTA --}}
        <button
    type="button"
    class="bg-gray-700 w-full m-2 p-2 rounded text-white text-center hover:bg-gray-800"
    onclick="printPreview('{{ route('pos.order.preview', $order->id) }}')"
>
    Imprimir cuenta
</button>


        {{-- 🔴 CANCEL ORDER --}}
        <button
            class="bg-red-600 w-full m-2 p-2 rounded text-white"
            onclick="cancelOrderAndRefresh({{ $order->id }})"
        >
            Cancelar
        </button>

    </div>
@endif


</x-main-order-component>
</div>