<?php

namespace App\Http\Controllers\Billing;

use App\Enums\UserRole;
use App\Helpers\PDFHelper;
use App\Http\Controllers\Controller;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Enums\OrderStatus;

class BillController extends Controller
{
    //

    // function getBills(){

    //     return view('admin.bills.index');
    // }

public function getBillsByDate(Request $request)
{
    $startDate = $request->filled('startDate')
        ? Carbon::parse($request->startDate)->startOfDay()
        : Carbon::now()->startOfDay();

    $endDate = $request->filled('endDate')
        ? Carbon::parse($request->endDate)->endOfDay()
        : Carbon::now()->endOfDay();

    $includeDeleted = $request->input('includeDeleted') === 'true';
    $onlyDeleted = $request->input('onlyDeleted') === 'true';
    $paymentMethod = $request->input('paymentMethod'); // 👈 NUEVO

    /** @var \App\User */
    $user = auth()->user();

    // 🔹 BASE QUERY
    $billsQuery = Bill::where('status', 'closed')
        ->whereBetween('created_at', [$startDate, $endDate]);

    // 💳 FILTRO POR MÉTODO DE PAGO
   // 💳 FILTRO POR MÉTODO DE PAGO (MULTIPLE)
if (!empty($paymentMethod)) {
    $methods = array_map('trim', explode(',', $paymentMethod));

    $billsQuery->whereIn('payment_method', $methods);
}


    // 🗑️ SOFT DELETES (solo admin)
    if ($user->hasPermission(UserRole::Admin)) {
        if ($onlyDeleted) {
            $billsQuery->onlyTrashed();
        } elseif ($includeDeleted) {
            $billsQuery->withTrashed();
        }
    }

    // 🔢 TOTAL CORRECTO (YA FILTRADO)
    $totalSales = $billsQuery->sum('grand_total');

    // 📋 RESULTADOS
    $bills = $billsQuery
        ->orderBy('created_at', 'desc')
        ->get();

    // 🧩 HTML
    $html = '';
    foreach ($bills as $index => $bill) {
        $html .= view('components.bill-component', compact('bill', 'index'))->render();
    }

    return response()->json([
        "status" => "success",
        "bills" => $html,
        "totalSales" => $totalSales
    ]);
}




    function viewBill($id)
    {

        $bill = Bill::where('id', $id)->with('orders')->with('orders.orderDetails')->with('orders.orderDetails.menu')->withTrashed()->first();

        return view('admin.bills.view', compact('bill'));
    }


    function StreamBillToBrowser($id)
    {
        $billId = Bill::where('id', $id)->first()->bill_id;
        $fileName = 'bill_' . $billId . '.pdf';
        $filePath = 'bills/' . $fileName;

        if (!Storage::exists($filePath)) {
            PDFHelper::saveBillToDisk($id);
        }

        $fileContent = Storage::get($filePath);

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        ];

        // Stream the file to the browser
        return response($fileContent, 200, $headers);
    }

    public function destroy($billid)
{
    $user = auth()->user();

    // 🔒 Solo administrador puede eliminar
    if (!$user || !$user->isAdmin()) {
        abort(403, 'No tienes permiso para eliminar facturas');
    }

    $bill = Bill::findOrFail($billid);
    $bill->delete();

    return redirect()
        ->route('admin.bills.index')
        ->with('success', 'Bill deleted successfully.');
}

public function tablePreview($tableId)
{
    $order = Order::where('table_id', $tableId)
        ->whereNotIn('status', [
            OrderStatus::Closed,
            OrderStatus::Cancelled
        ])
        ->latest()
        ->firstOrFail();

    // reutiliza EXACTAMENTE la vista que ya funciona
    return redirect()->route('pos.order.preview', $order->id);

}
}
