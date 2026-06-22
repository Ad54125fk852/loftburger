<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
          Restaurant::create([
        'name' => 'LOFT BURGER',
        'tagline' => 'Thank you for your purchase',
        'address' => 'Calle 41 #4k - 43, B/ Macarena Parte Alta',
        'phone' => ' ', 
        'email' => ' ',
        'website' => ' ',

        'takeout_enabled' => 1,
        'delivery_enabled' => 1,
        'pending_order_sync_time' => 5,
        'waiter_sync_time' => 5,
        'minimum_delivery_time' => 30, 
        'minimum_preparation_time' => 20, 
        'order_live_view' => 'desc',
        'kot_live_view' => 'asc',


        'payment_options' => json_encode(['cash']),

    
        'social_media' => null,

        'tax_rate' => 0.00, 
        'currency_symbol' => '$',

        'reservation_enabled' => 1,
        'reservation_advance_notice' => 120, // minutes

        'created_at' => now(),
        'updated_at' => now(),

        'waiter_module_enabled' => false,
        'kitchen_module_enabled' => false,
        'biller_printer' => null,
        'kitchen_printer' => null,
    ]);

    }
}
