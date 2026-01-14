<?php

namespace App\Helpers;

use App\Models\Bill;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PDFHelper
{

public static function streamOrderPreviewToBrowser(Order $order)
{
    // 1️⃣ Datos del restaurante (igual que Bill)
    $restaurantDetails = RestaurantHelper::getCachedRestaurantDetails();

    $restaurant = [
        "name"    => $restaurantDetails->name,
        "address" => $restaurantDetails->address,
        "phone"   => $restaurantDetails->phone,
        "tagline" => $restaurantDetails->tagline,
    ];

    // 2️⃣ Datos tipo Bill (pero sin Bill)
    $billDetails = collect([
        'id'          => 'PRE-' . $order->id,
        'table_no'    => $order->table_id ? $order->table_id : 'Pick Up',
        'grand_total' => $order->grand_total ?? 0,
        'discount'    => 0,
        'date'        => now()->format('d M, Y h:i a'),
    ]);

    // 3️⃣ Items (misma estructura que el recibo real)
    $orderDetails = collect([]);

    foreach ($order->orderDetails as $detail) {
        $orderDetails->put(
            $detail->menu?->name ?? 'Item',
            [
                'quantity' => $detail->quantity,
                'price'    => $detail->menu?->price ?? 0,
            ]
        );
    }

    // 4️⃣ USAMOS TU GENERADOR REAL
    return self::saveAsPDF(
        $restaurant,
        $billDetails,
        $orderDetails,
        'order_preview_' . $order->id,
        true // 👈 stream al navegador
    );
}


    public static function saveBillToDisk($id)
    {

        $bill = Bill::where('id', $id)->with('orders')->with('orders.orderDetails')->with('orders.orderDetails.menu')->first();


        $billFullId = $bill->bill_id;

        $billDetails = collect([
            'id' => $billFullId,
            'table_no' => $bill->table_id ? $bill->table_id : 'Pick Up',
            'grand_total' => $bill->grand_total,
            'discount' => $bill->discount,
            "date" => $bill->created_at->format('d M, Y h:i a')
        ]);

        $orderDetails = collect([]);

        foreach ($bill->orders as $order) {
            foreach ($order->orderDetails as $orderDetail) {
                $itemName = $orderDetail->menu->name;
                $quantity = $orderDetail->quantity;
                $price = $orderDetail->menu->price;


                // if key alredy exists it wil increment
                $orderDetails->put($itemName, ['quantity' => $quantity, 'price' => $price + 5]);
            }
        }

        $restaurantDetails = RestaurantHelper::getCachedRestaurantDetails();


        $restaurant = [
            "name" => $restaurantDetails->name,
            "address" => $restaurantDetails->address,
            "phone" => $restaurantDetails->phone,
            "tagline" => $restaurantDetails->tagline,
        ];


        $fileName = 'bill_' . $billFullId . '.pdf';

        //self::saveAsTXT($restaurant, $billDetails, $orderDetails, $billFullId);

        self::saveAsPDF($restaurant, $billDetails, $orderDetails, $billFullId);

        return $billFullId;
    }
    public static function saveAsTXT($restaurant, $billDetails, $orderDetails, $billFullId)
    {
        BillGenerator::generateThermalPrint($restaurant, $billDetails, $orderDetails, $billFullId);
    }
    public static function saveAsPDF(
    $restaurant,
    $billDetails,
    $orderDetails,
    $billFullId,
    bool $stream = false
) {
    $fileName = 'bill_' . $billFullId . '.pdf';

    $html = view('admin.bills.print', compact(
        'billDetails',
        'orderDetails',
        'restaurant'
    ))->render();

    $paper = self::getNewOptimizedPaper($html);

    $dompdf = Pdf::loadHTML($html);
    $dompdf->set_paper($paper->toArray());

    if ($stream) {
        // 🔥 PRE-RECIBO → NO GUARDA, SOLO MUESTRA
        return response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ]
        );
    }

    // 🔒 RECIBO FINAL → GUARDA EN DISCO
    $filePath = 'bills/' . $fileName;
    Storage::put($filePath, $dompdf->output());

    return $filePath;
}


    public static function  getNewOptimizedPaper($html)
    {
        $paper = Paper::getPaper();

        $pdf = Pdf::loadHTML($html)->setPaper($paper->toArray());

        $pdf->render();

        $canvas = $pdf->getCanvas();

        $page_count = $canvas->get_page_number();

        unset($pdf); // clear old pdf

        $newHeight = $paper->getHeight() * $page_count + 20;

        $paper->setHeight($newHeight);

        return $paper;
    }

    public static function saveKOTToDisk($KOT)
    {
    }

    public static function printKOT($KOTPath)
    {
    }


}
