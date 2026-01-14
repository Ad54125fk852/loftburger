let lastOrderId = 0;

document.addEventListener("DOMContentLoaded", function () {
    setInterval(checkOrderUpdates, orderSyncTime);
});

function markAsServed(orderId) {
    showLoader();
    $.ajax({
        type: "POST",
        url: markAsServedRoute,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            orderId: orderId,
        },
        success: function (response) {
            console.log(response);
            if (response.status === "success") {
                $("#order" + orderId).remove();
            } else {
                alert("Something went wrong");
            }
        },
        error: function (error) {
            console.error("Error marking order as served:", error);
        },
        complete: function () {
            hideLoader();
        },
    });
}
function markAsPrepared(orderId) {
    showLoader();
    $.ajax({
        type: "POST",
        url: markAsPreparedRoute,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            orderId: orderId,
        },
        success: function (response) {
            console.log(response);
            if (response.status === "success") {
                window.location.reload();
            } else {
                alert("Something went wrong");
            }
        },
        error: function (error) {
            console.error("Error marking order as prepared:", error);
        },
        complete: function () {
            hideLoader();
        },
    });
}

function markAsClosed(orderId) {
    showLoader();

    $.ajax({
        type: "POST",
        url: markAsClosedRoute,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: { orderId },
        success: function (response) {
            if (response.status === "success") {

                // 🔄 actualizar estado visual
                const statusEl = document.getElementById(`order-status-${orderId}`);
                if (statusEl) {
                    statusEl.innerText = "Completed";
                    statusEl.className = "px-3 py-1 rounded text-white text-sm bg-gray-600";
                }

                // ❌ quitar botones
                const orderEl = document.getElementById(`order-${orderId}`);
                if (orderEl) {
                    orderEl.querySelectorAll(".options").forEach(el => el.remove());
                }
            }
        },
        complete: hideLoader,
    });
}


function checkOrderUpdates() {
    $.ajax({
        url: checkOrderUpdatesRoute,
        method: "GET",
        dataType: "json",
        data: {
            lastOrderId: lastOrderId,
        },
        success: function (response) {
            if (response.status === "success") {
            }

            if (response.lastOrderId) {
                lastOrderId = response.lastOrderId;
            }
        }
    });
}

function cancelOrderAndRefresh(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    $.ajax({
        url: '/order/cancel',
        method: 'POST',
        data: {
            orderId: orderId,
            _token: document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content')
        },
        success: function () {

            // 🧹 elimina el ticket del Kitchen SIN recargar
            const orderElement = document.getElementById(`order-${orderId}`);
            if (orderElement) {
                orderElement.remove();
            }

        },
        error: function () {
            alert('Failed to cancel order');
        }
    });
}







