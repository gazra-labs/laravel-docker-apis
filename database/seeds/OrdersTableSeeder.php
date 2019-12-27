<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Order as Order;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Order::truncate();

        $faker = \Faker\Factory::create();
        // And now, let's create a few Orders in our database:
        for ($i = 0; $i < 50; $i++) {
            Order::create([
                'origin_lat' => $faker->latitude(-90,90),
                'origin_lng' => $faker->longitude(-180,180),
                'destination_lat' => $faker->latitude(-90,90),
                'destination_lng' => $faker->longitude(-180,180),
                'distance' => rand(1000.00,20000.99),
                'status' => $faker->randomElement([Order::UNASSIGNED, Order::TAKEN]),
            ]);
        }
    }
}
