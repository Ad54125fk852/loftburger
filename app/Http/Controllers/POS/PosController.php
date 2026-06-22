<?php

namespace App\Http\Controllers\POS;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\TableStatus;
use App\Helpers\BillHelper;
use App\Helpers\RestaurantHelper;
use App\Helpers\TableHelper;
use App\Http\Controllers\Controller;
use App\Http\Service\MenuService;
use App\Http\Service\RestaurantService;
use App\Http\Service\TableService;
use App\Jobs\SaveAndPrintBill;
use App\Models\Bill;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PosController extends Controller
{
    private $menuService;
    private $tableService;
    private $restaurantService;

    public function __construct(MenuService $menuService, TableService $tableService, RestaurantService $restaurantService)
    {
        $this->menuService = $menuService;
        $this->tableService = $tableService;
        $this->restaurantService = $restaurantService;
    }
    public function index(Request $request)
    {
        $tableId = $request->tableId;

        $orderType = $tableId === 'takeaway' ? OrderType::Takeaway : OrderType::DineIn;

        // Get categories with menus, predefined notes, and payment types
        $categoriesWithMenus = $this->menuService->getCatergoriesWithMenus();
        $predefinedNotes = config('predefined_options.notes');
        $paymentTypes = json_decode($this->restaurantService->getRestaurantDetails()->payment_options);

        // Initialize variables
        $table = null;

        if ($orderType === OrderType::DineIn) {
            $table = Table::find($tableId);

            // Redirect if the table is not found
            if (!$table) {
                return redirect()->route('pos.tables');
            }
        }

        return view('pos.pos-index', compact(
            'categoriesWithMenus',
            'predefinedNotes',
            'paymentTypes',
            'table',
            'orderType'
        ));
    }


    public function tableStatuses()
{
    $tables = Table::with(['orders' => function ($q) {
        $q->where('status', '!=', OrderStatus::Closed);
    }])->get();

    return response()->json(
        $tables->map(function ($table) {
            $order = $table->orders->first();

            $visualStatus = $table->status->value;

            if ($order) {
                $visualStatus = match ($order->status) {
                    OrderStatus::New => 'runningKOT',
                    OrderStatus::Processing => 'running',
                    OrderStatus::ReadyForPickup => 'printed',
                    OrderStatus::Served => 'paid',
                    OrderStatus::Cancelled => 'unavailable',
                    default => $visualStatus,
                };
            }

            return [
                'id' => $table->id,
                'status' => $visualStatus,
                'order_sum' => $table->order_sum,
                'taken_at' => $table->taken_at,
            ];
        })
    );
}


    public function selectTable()
    {
        $tablesWithLocations = $this->tableService->getTablesWithOrderSums()->groupBy('location.name');

        $table_colors =  config('predefined_options.table_colors');

        $paymentTypes = json_decode($this->restaurantService->getRestaurantDetails()->payment_options);

        return view('pos.tables', compact('tablesWithLocations', 'table_colors', 'paymentTypes'));
    }

    public function billTable(Request $request)
    {
        $tableId = $request->tableId;
        $notes = $request->notes ? $request->notes : '';
        $paymentType = $request->paymentType ? $request->paymentType : 'cash';
        $discount = $request->discount ? $request->discount : 0;

        if ($request->has('printDuplicateBill')) {
            $billId = BillHelper::getLatestBillId($tableId);
            SaveAndPrintBill::dispatch($billId, $printDuplicateBill = true);

            return response()->json(['status' => 'success', 'message' => 'Duplicate bill printed']);
        }

        $billId = BillHelper::createTableBill($tableId, $request->notes, $paymentType, $discount);

        SaveAndPrintBill::dispatch($billId);

        return response()->json(['status' => 'success', 'billId' => $billId]);
    }


   public function settleTable(Request $request)
{
    $request->validate([
        'tableId' => 'required|integer',
    ]);

    $tableId = $request->tableId;
    $table = Table::findOrFail($tableId);

    // 1️⃣ Cerrar órdenes abiertas
    $orders = Order::where('table_id', $tableId)
        ->where('status', '!=', OrderStatus::Closed)
        ->get();

    foreach ($orders as $order) {
        $order->status = OrderStatus::Closed;
        $order->save();
    }

    // 2️⃣ Buscar bill abierto
    $bill = Bill::where('table_id', $tableId)
        ->where('status', 'open')
        ->latest()
        ->first();

    // 3️⃣ Si NO existe bill → crearlo usando payment_method de la orden
    if (!$bill) {
        $lastOrder = Order::where('table_id', $tableId)
            ->latest()
            ->first();

        if (!$lastOrder) {
            return response()->json([
                'status' => 'error',
                'message' => 'No orders found for table'
            ], 400);
        }

        $paymentMethod = $lastOrder->payment_method ?? 'cash';

        $billId = BillHelper::createTableBill(
            $tableId,
            null,
            $paymentMethod,
            0
        );

        $bill = Bill::findOrFail($billId);
    }

    // 4️⃣ Cerrar bill
    $bill->status = 'closed';
    $bill->save();

    // 5️⃣ Liberar mesa
    TableHelper::markTableAsPaid($tableId);

    return response()->json([
        'status' => 'success',
        'message' => 'Table settled successfully'
    ]);
}




    //tableOrders
    public function tableOrders($tableId)
    {

        $table = Table::find($tableId);

        $orders = Order::with('orderDetails')->with('orderDetails.menu')
            ->where('table_id', $tableId)
            ->where('status', '!=', OrderStatus::Closed)
            ->get();


        $billedOrders = Bill::where('table_id', $tableId)->where('created_at', '>=', $table->taken_at)->with('orders')->get();

        return view('pos.table-orders', compact('orders', 'table', 'billedOrders'));
    }
}
