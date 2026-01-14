<?php

namespace App\Http\Controllers\Kitchen;

use App\Enums\OrderStatus;
use App\Helpers\RestaurantHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KOTController extends Controller
{
    public function displayKOTs()
    {
        // tu código actual aquí
    }

    public function partial()
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isBiller()) {
            $currentOrderComponent = 'order-running-component-for-admin';
        } else {
            $currentOrderComponent = 'order-component-for-waiter';
        }

        $isKitchenStaff = !$user->isWaiter();

        $orders = Order::with(['table', 'waiter'])
            ->whereNotIn('status', ['cancelled', 'closed'])
            ->latest()
            ->get();

        $tableOrders = $orders->where('table_id', '!=', null)->groupBy('table_id');
        $takeawayOrders = $orders->where('table_id', null);

        return view('kot._orders_partial', compact(
            'tableOrders',
            'takeawayOrders',
            'currentOrderComponent',
            'isKitchenStaff'
        ));
    }
}
