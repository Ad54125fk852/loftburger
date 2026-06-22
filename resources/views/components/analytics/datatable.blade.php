<link rel="stylesheet" href="{{ asset('css/datatable/dataTables.min.css') }} ">
<link rel="stylesheet" href="{{ asset('css/datatable/buttons.dataTables.css') }}">
<link rel="stylesheet" href="{{ asset('css/flatpickr.css') }}">
<script src="{{ asset('js/datatable/dataTables.min.js') }}"></script>
<script src="{{ asset('js/datatable/dataTables.buttons.js') }}"></script>
<script src="{{ asset('js/datatable/buttons.dataTables.js') }}"></script>
<script src="{{ asset('js/datatable/jszip.min.js') }}"></script>
<script src="{{ asset('js/datatable/pdfmake.min.js') }}"></script>
<script src="{{ asset('js/datatable/vfs_fonts.js') }}"></script>
<script src="{{ asset('js/datatable/html5.min.js') }}"></script>
<script src="{{ asset('js/datatable/buttons.print.min.js') }}"></script>
<script src="{{ asset('js/chart.js') }}"></script>
<script src="{{ mix('js/datepicker.js') }}"></script>

<style>
    td,
    th {
        text-align: center !important;
    }

    main {
        padding: 0px !important;
        margin: 0px !important;
    }
</style>


<script>
function formatCurrency(amount) {

    amount = parseFloat(amount);

    if (isNaN(amount)) {
        console.error("Invalid amount:", amount);
        return '';
    }

    return '$ ' + amount.toLocaleString('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}
</script>

