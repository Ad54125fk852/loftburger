<x-main-order-component :order="$order">
    <div class="options flex items-center justify-between pt-2">
        <!--<button class="py-3 px-6 font-bold text-white bg-red-600 hover:bg-red-700 transition-colors rounded-bl-lg"
            id="discardOrder" onclick="discardOrder({{ $order->id }})">
            Reject
        </button>-->
        <button class="py-3 px-6 font-bold text-white bg-green-600 hover:bg-green-700 transition-colors rounded-br-lg"
            id="acceptOrder" style="padding: 15px 50px; border-radius: 10px;"  onclick="acceptOrder({{ $order->id }})">
            Aceptar
        </button>
    </div>
</x-main-order-component>
