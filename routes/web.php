<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\TableLocationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Billing\BillController;
use App\Http\Controllers\BillerController;
use App\Http\Controllers\Frontend\CategoryController as FrontendCategoryController;
use App\Http\Controllers\Frontend\MenuController as FrontendMenuController;
use App\Http\Controllers\Frontend\ReservationController as FrontendReservationController;
use App\Http\Controllers\Order\OrderController as OrderController;
use App\Http\Controllers\Frontend\WelcomeController;
use App\Http\Controllers\Kitchen\KOTController;
use App\Http\Controllers\Order\OrderSyncController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Restaurant\RestaurantController;
use App\Http\Controllers\POS\PosController;
use App\Http\Controllers\RedirectController;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Helpers\PDFHelper;


/**
 * -----------------------------------------------------------------------------------------------------------------------------
 * Routes for FrontEnd & Reservation
 * -----------------------------------------------------------------------------------------------------------------------------
 */
Route::get('/', [WelcomeController::class, 'index']);
Route::get('/categories', [FrontendCategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [FrontendCategoryController::class, 'show'])->name('categories.show');
Route::get('/menus', [FrontendMenuController::class, 'index'])->name('menus.index');

Route::get('/reservation/step-one', [FrontendReservationController::class, 'stepOne'])->name('reservations.step.one');
Route::post('/reservation/step-one', [FrontendReservationController::class, 'storeStepOne'])->name('reservations.store.step.one');
Route::get('/reservation/step-two', [FrontendReservationController::class, 'stepTwo'])->name('reservations.step.two');
Route::post('/reservation/step-two', [FrontendReservationController::class, 'storeStepTwo'])->name('reservations.store.step.two');
Route::get('/thankyou', [WelcomeController::class, 'thankyou'])->name('thankyou');

/**
 * -----------------------------------------------------------------------------------------------------------------------------
 * Routes for Dashboard
 * -----------------------------------------------------------------------------------------------------------------------------
 */

Route::middleware(['auth'])->get('/dashboard', [RedirectController::class, 'dashboard'])->name('dashboard');
Route::get('/kot/partial', [KOTController::class, 'partial'])
    ->name('kot.partial');

/**
 * -----------------------------------------------------------------------------------------------------------------------------
 * Routes for Admin
 * -----------------------------------------------------------------------------------------------------------------------------
 */

Route::middleware(['auth', 'admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::resource('/categories', CategoryController::class);
    Route::post('categories/update-ranks', [CategoryController::class, 'updateRanks'])->name('categories.updateRanks');

    Route::resource('/menus', MenuController::class);
    Route::resource('/tables', TableController::class);
    Route::resource('/table-location', TableLocationController::class);
    Route::resource('/reservations', ReservationController::class);
    Route::resource('/users', UserController::class);

    Route::get("/bills", function () {
        return view('admin.bills.index');
    })->name('bills.index');

    Route::delete('/bill/{id}', [BillController::class, 'destroy'])->name('bill.destroy');


    Route::get('/bills-by-date', [BillController::class, 'getBillsByDate'])->name('bills.by.date');


    Route::get('/bill/view/{id}', [BillController::class, 'viewBill'])->name('view.bill');

    Route::get('/bill/print/{id}', [BillController::class, 'StreamBillToBrowser'])->name('stream.bill');

    Route::get('/bills/fd', [BillController::class, 'getBills'])->name('bills.update');

    Route::get('/KOTs', [KOTController::class, 'displayKOTs'])->name('KOTs');

    /**
     * -----------------------------------------------------------------------------------------------------------------------------
     * Routes for Restaurant
     * -----------------------------------------------------------------------------------------------------------------------------
     */

    Route::prefix('restaurant')->name('restaurant.')->group(function () { // Nested prefix for cleaner routes
        Route::get('/config', [RestaurantController::class, 'showConfig'])->name('show.config');
        Route::post('/update-config', [RestaurantController::class, 'updateConfig'])->name('update.config');

        // New route for fetching module status (GET request)
        Route::get('/module-status', [RestaurantController::class, 'getModuleStatus'])->name('module.status');

        // Routes for module enabling/disabling via AJAX
        Route::post('/enable-waiter-module', [RestaurantController::class, 'enableWaiterModule'])->name('enable_waiter_module');
        Route::post('/disable-waiter-module', [RestaurantController::class, 'disableWaiterModule'])->name('disable_waiter_module');
        Route::post('/enable-kitchen-module', [RestaurantController::class, 'enableKitchenModule'])->name('enable_kitchen_module');
        Route::post('/disable-kitchen-module', [RestaurantController::class, 'disableKitchenModule'])->name('disable_kitchen_module');
    });
});

/**
 * -----------------------------------------------------------------------------------------------------------------------------
 * Routes for Biller
 * -----------------------------------------------------------------------------------------------------------------------------
 */

Route::middleware(['auth', 'biller'])->name('biller.')->prefix('biller')->group(function () {
    Route::get('/', [BillerController::class, 'index'])->name('index');
});


/**
 * -----------------------------------------------------------------------------------------------------------------------------
 * Routes for POS
 * -----------------------------------------------------------------------------------------------------------------------------
 */


Route::middleware(['auth', 'biller', 'ensure.pos.configured'])->name('pos.')->prefix('pos')->group(function () {
    Route::get('/select-table', [PosController::class, 'selectTable'])->name('tables');
    Route::get('/order', [PosController::class, 'index'])->name('main');
    Route::post('/table/submit-for-billing', [PosController::class, 'billTable'])->name('table.bill');
    Route::post('/table/settle', [PosController::class, 'settleTable'])->name('table.settle');
    Route::get('/table/orders/{tableId}', [PosController::class, 'tableOrders'])->name('table.orders');
});


/**
 * -------------------------------------------------s----------------------------------------------------------------------------
 * Routes preview recibo
 * -----------------------------------------------------------------------------------------------------------------------------
 */

Route::get('/pos/orders/{order}/preview', function (Order $order) {
    return PDFHelper::streamOrderPreviewToBrowser($order);
})->name('pos.order.preview');


/**
 * -------------------------------------------------s----------------------------------------------------------------------------
 * Routes for Order
 * -----------------------------------------------------------------------------------------------------------------------------
 */

Route::get('/kitchen/check-order/{orderId}', function ($orderId) {

    $order = Order::find($orderId);

    // 🔥 Si la orden ya no existe, no está activa en cocina
    if (!$order) {
        return response()->json([
            'active' => false
        ]);
    }

    // 🔥 Solo NEW y PROCESSING permanecen en Kitchen
    return response()->json([
        'active' => in_array($order->status, [
            OrderStatus::New,
            OrderStatus::Processing,
        ])
    ]);

})->name('kitchen.check.order');

Route::get('/kitchen/completed-orders', function () {

    $orders = Order::with([
            'table',
            'orderDetails.menu',   // 👈 CLAVE
        ])
        ->whereIn('status', [
            OrderStatus::ReadyForPickup,
            OrderStatus::Served,
            OrderStatus::Closed,
        ])
        ->latest('updated_at')
        ->take(3)
        ->get()
        ->map(function ($order) {

            return [
                'id' => $order->id,
                'label' => $order->table
                    ? 'Mesa ' . $order->table->name
                    : 'Take Away',
                'time' => $order->updated_at->format('H:i'),
                'items' => $order->orderDetails->map(function ($detail) {

                    // 🔥 PRIORIDAD CORRECTA
                    $name =
                        $detail->menu?->name
                        ?? $detail->item_name
                        ?? $detail->menu_name
                        ?? 'Item';

                    return [
                        'name' => $name,
                        'qty'  => $detail->quantity,
                    ];
                })->toArray()
            ];
        });

    return response()->json($orders);
})->name('kitchen.completed.orders');



Route::middleware(['auth'])->name('order.')->prefix('order')->group(function () {

    Route::post('/submit', [OrderController::class, 'submit'])->name('submit');

    Route::get('/KOT-view', [OrderController::class, 'KOTView'])->name('KOT.view');

    Route::post('/mark-as-served', [OrderController::class, 'markAsServed'])->name('mark.as.served');
    Route::post('/mark-as-prepared', [OrderController::class, 'markAsPrepared'])->name('mark.as.prepared');
    Route::post('/mark-as-closed', [OrderController::class, 'markAsClosed'])->name('mark.as.closed');
    Route::post('/cancel', [OrderController::class, 'cancelOrder'])->name('cancel');
    Route::get('/order/status/{id}', [OrderController::class, 'getStatus'])->name('order.status');
});

/**
 * -----------------------------------------------------------------------------------------------------------------------------
 * Routes for Sync
 * -----------------------------------------------------------------------------------------------------------------------------
 */

Route::middleware(['auth'])->name('sync.')->prefix('sync')->group(function () {

    Route::get('/check-pending-orders-updates', [OrderSyncController::class, 'syncPendingOrder'])->name('pending.orders');
    Route::get('/check-pickup-orders-updates', [OrderSyncController::class, 'syncPickUpOrder'])->name('pickup.orders');
    
});

require __DIR__ . '/auth.php';

