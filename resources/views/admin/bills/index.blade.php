<x-master-layout>
    @section('title', 'All Bills')
    @include('components.analytics.datatable')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            All Bills
        </h2>
    </x-slot>

<div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

<div class="mb-6 px-6">
    <x-search-by-date>
        <div class="flex items-center gap-6 flex-wrap">

            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-600 whitespace-nowrap">
                    Payment Method
                </label>

                <select id="paymentMethod"
                    class="rounded border-gray-300 shadow-sm min-w-[180px]">
                    <option value="">All</option>
                    <option value="EFECTIVO">Efectivo</option>
<option value="TARJETA">Tarjeta</option>
<option value="TRANSFE">Transferencia</option>
<option value="COURTESY">Cortesía</option>
<option value="RAPPI">Rappi</option>
<option value="PENDIENT">Pendiente</option>
                </select>
            </div>

            @if (Auth::user()->isAdmin())
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="includeDeleted">
                    <span class="text-sm">Include Deleted</span>
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" id="onlyDeleted">
                    <span class="text-sm">Only Deleted</span>
                </label>
            @endif

        </div>
    </x-search-by-date>
</div>

                <div class="overflow-x-auto">
                    <table id="bills-table" class="min-w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    S.No</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bill ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bill Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Grand Total</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="bills-table-body" class="bg-white divide-y divide-gray-200">
                            {{-- AJAX will populate this section --}}
                        </tbody>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <td colspan="3"
                                    class="px-6 py-4 text-right text-sm font-bold text-gray-700 uppercase">
                                    Total Sales Amount:
                                </td>
                                <td id="total-sales-amount" class="px-6 py-4 text-left text-sm font-bold text-gray-900">
                                    {{-- AJAX will populate this --}}
                                </td>
                                <td></td> {{-- Empty cell for alignment --}}
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- The original script block is preserved --}}


<script>
const paymentMethodMap = {
    EFECTIVO: ['EFECTIVO', 'cash'],
    TARJETA: ['TARJETA', 'tarjeta', 'CARD', 'card'],
    TRANSFE: ['upi', 'TRANSFE', 'TRANSFERENCIA'],
    COURTESY: ['COURTESY', 'courtesy'],
    RAPPI: ['RAPPI', 'rappi'],
    PENDIENT: ['PENDIENT', 'pendient']
};
</script>



<script>
document.getElementById("searchByDate").addEventListener("click", function () {

    // 📅 fechas (ya las tienes)
    let startDate = new Date(getSelectPickrFormattedDate(startDateObject));
    let endDate = getSelectPickrFormattedDate(endDateObject);

    // 🧾 filtros extra
    let selectedMethod = document.getElementById("paymentMethod")?.value ?? '';
let paymentMethods = null;

if (selectedMethod && paymentMethodMap[selectedMethod]) {
    paymentMethods = paymentMethodMap[selectedMethod];
}

    let includeDeleted = document.getElementById("includeDeleted")?.checked ?? false;
    let onlyDeleted = document.getElementById("onlyDeleted")?.checked ?? false;

    if (startDate > endDate) {
        alert("Start date should be less than end date");
        return;
    }

    startDate = formatDateToYYYYMMDD(startDate);
    endDate = formatDateToYYYYMMDD(endDate);

    let url =
    `{{ route('admin.bills.by.date', [], false) }}` +
    `?startDate=${startDate}` +
    `&endDate=${endDate}` +
    (paymentMethods ? `&paymentMethod=${paymentMethods.join(',')}` : '') +
    `&includeDeleted=${includeDeleted}` +
    `&onlyDeleted=${onlyDeleted}`;


    $.ajax({
        url: url,
        method: 'GET',
        success: function (data) {

            // 🔄 reiniciar DataTable
            $('#bills-table').DataTable().destroy();

            // 🧩 insertar filas
            document.getElementById("bills-table-body").innerHTML = data.bills;

            // 💰 total
            document.getElementById("total-sales-amount").innerText =
                formatCurrency(data.totalSales);

            // 🔁 volver a crear DataTable
            $('#bills-table').DataTable({
                paging: true,
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'csv', exportOptions: { columns: ':lt(4)' } },
                    { extend: 'excel', exportOptions: { columns: ':lt(4)' } },
                    { extend: 'pdf', exportOptions: { columns: ':lt(4)' } },
                    { extend: 'print', exportOptions: { columns: ':lt(4)' } }
                ]
            });
        },
        error: function (err) {
            console.error("Error loading bills", err);
        }
    });
});

// 📆 helper (si ya lo tienes, no dupliques)
function formatDateToYYYYMMDD(date) {
    let y = date.getFullYear();
    let m = String(date.getMonth() + 1).padStart(2, '0');
    let d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}
</script>
<script>
document.getElementById("paymentMethod")
    ?.addEventListener("change", function () {
        document.getElementById("searchByDate").click();
    });
</script>

</x-master-layout>
