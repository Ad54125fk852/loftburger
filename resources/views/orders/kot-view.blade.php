<x-master-layout>
@section('title', 'KOT View')

@php
    $user = auth()->user();

    if ($user->isAdmin() || $user->isBiller()) {
        $currentOrderComponent = 'order-running-component-for-admin';
    } else {
        $currentOrderComponent = 'order-component-for-waiter';
    }

    $isKitchenStaff = !$user->isWaiter();
@endphp

<div class="min-h-screen bg-gray-100 p-4">

    {{-- =====================
        HEADER (NO SE REFRESCA)
    ====================== --}}
    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Ordenes de Tickets (KOT)</h1>
        <p class="text-gray-500">Ordenes en Progreso</p>
    </div>

    {{-- =====================
        SOLO TARJETAS
    ====================== --}}
    <div id="kot-orders-container">
        {{-- aquí entra /kot/partial --}}
    </div>

</div>

{{-- ===============================
    🔄 AUTO REFRESH (POLLING)
=============================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    console.log('✅ KOT polling activo');

    const container = document.getElementById('kot-orders-container');
    if (!container) {
        console.error('❌ kot-orders-container no encontrado');
        return;
    }

    async function refreshKOT() {
        try {
            const response = await fetch('/kot/partial', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) return;

            const html = await response.text();
            container.innerHTML = html;

        } catch (e) {
            console.error('❌ Error refrescando KOT:', e);
        }
    }

    refreshKOT();                 // carga inicial
    setInterval(refreshKOT, 3000); // cada 3 segundos
});
</script>

{{-- ===============================
    ❌ CANCELAR ORDEN (SIN RELOAD)
=============================== --}}
<script>
function cancelOrderAndRefresh(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    fetch('/order/cancel', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content')
        },
        body: JSON.stringify({ orderId })
    })
    .then(() => {
        const el = document.getElementById(`kot-order-${orderId}`);
        if (el) el.remove();
    })
    .catch(() => alert('Failed to cancel order'));
}
</script>

<script>
window.printPreview = function (url) {

    if (!confirm('¿Deseas imprimir la cuenta ahora?')) {
        return;
    }

    // 🔹 Crear iframe oculto
    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    iframe.src = url;

    document.body.appendChild(iframe);

    iframe.onload = function () {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        // 🔥 limpiar iframe luego
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 1000);
    };
};
</script>



</x-master-layout>
