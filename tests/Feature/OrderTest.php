<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Order as Order;

class OrderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->yc = "\033[33m"; // terminal color yellow
        $this->gc = "\033[32m"; // terminal color green
        $this->rc = "\033[31m"; // terminal color red
        $this->nc = "\033[0m";  // terminal end color 
        $this->bt = "\033[1m";
    }

    public function testShowOrder_valid()
    {
        $param = [
            'page' => 1,
            'limit' => 10
        ];

        $response = $this->call('GET', '/orders', $param);
        $data = json_decode($response->getContent(), true);

        echo "\n $this->yc =============================================$this->bt STARTING INTEGRATION TEST CASES FOR ORDER API.$this->nc $this->yc============================================= $this->nc \n\n";
        echo "\n $this->yc ============= #1. SHOW ORDERS LIST TEST ==================== $this->nc \n";
        echo "\n $this->gc 1.) Test for valid / correct Order List $this->nc \n";

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $response->assertStatus(200);
        foreach ($data as $order) {
            $this->assertArrayHasKey('id', (array) $order);
            $this->assertArrayHasKey('distance', (array) $order);
            $this->assertArrayHasKey('status', (array) $order);
        }
        $this->assertCount(10, $data);
    }

    public function testShowOrder_InvalidPageValue()
    {
        $param = [
            "limit" => 10,
            "page" => 0
        ];

        $response = $this->call('GET', '/orders', $param);
        $data = json_decode($response->getContent(), true);

        echo "\n $this->gc 2.) Test for invalid request data for Order List $this->nc \n";

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $response->assertStatus(422);
        $this->assertArrayHasKey('error', $data);
    }

    public function testShowOrder_InvalidLimitValue()
    {
        $param = [
            "limit" => -10,
            "page" => 1
        ];

        $response = $this->call('GET', '/orders', $param);
        $data = json_decode($response->getContent(), true);

        echo "\n $this->gc 3.) Test for invalid limit for Order List $this->nc \n";

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $response->assertStatus(422);
        $this->assertArrayHasKey('error', $data);
    }

    public function testShowOrder_EmptyRequestData()
    {
        $param = [];

        $response = $this->call('GET', '/orders', $param);
        $data = json_decode($response->getContent(), true);

        echo "\n $this->gc 4.) Test for empty request data for Order List $this->nc \n";

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $response->assertStatus(422);
        $this->assertArrayHasKey('error', $data);
    }



    public function testCreateOrder_valid()
    {
        $param = [
            'origin' => ["28.6019042", "77.1848588"], // Origin Location 
            'destination' => ["28.5974392", "77.2016173"], //Destination Location
        ];

        $response = $this->call('POST', '/orders', $param);
        $data = json_decode($response->getContent(), true);

        echo "\n $this->yc ============= #2. CREATING ORDERS TEST ==================== $this->nc \n";
        echo "\n $this->gc 1.) Test for valid / correct Order creation $this->nc \n";

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('distance', $data);
        $this->assertIsInt($data['distance']);
        $this->assertArrayHasKey('status', $data);
        $this->assertInstanceOf('Illuminate\Foundation\Testing\TestResponse', $response);
    }

    public function testCreateOrder_invalidData()
    {
        $param = [
            'origin' => ["50.6019042", "88.1848588"],
            'destination' => ["20.5974392", "55.2016173"],
        ];

        $response = $this->call('POST', '/orders', $param);
        $data = json_decode($response->getContent(), true);
        echo "\n $this->gc 2.) Test for Vague Coordinates for Order creation $this->nc \n";
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";
        $this->assertEquals(503, $response->getStatusCode());



        $param = [
            'origin' => [28.6019042, "77.1848588"],
            'destination' => ["28.5974392", "77.2016173", "77.2016173"],
        ];

        $response = $this->call('POST', '/orders', $param);
        $data = json_decode($response->getContent(), true);
        echo "\n $this->gc 3.) Test for invalid data (data-type, length of origin) for Order creation $this->nc \n";
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";
        $this->assertEquals(422, $response->getStatusCode());


        $param = [];

        $response = $this->call('POST', '/orders', $param);
        $data = json_decode($response->getContent(), true);
        echo "\n $this->gc 4.) Test for empty for Order creation $this->nc \n";
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";
        $this->assertEquals(422, $response->getStatusCode());
    }


    public function testUpdateOrder_valid()
    {
        $order = Order::where('status', '=', 'UNASSIGNED')->first()->toArray();
        $id = $order['id'];
        $param = [
            'status' => 'TAKEN'
        ];

        $response = $this->call('PATCH', '/orders/' . $id, $param);
        $data = json_decode($response->getContent(), true);
        echo "\n $this->yc ============= #3. UPDATING ORDERS TEST ==================== $this->nc \n";
        echo "\n $this->gc 1.) Test for valid / correct for Order update $this->nc \n";
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";
        $this->assertEquals(200, $response->getStatusCode());
    }



    public function testUpdateOrder_invalid()
    {
        $order = Order::where('status', '=', 'UNASSIGNED')->first()->toArray();
        $id = $order['id'];
        $param = [];

        $response = $this->call('PATCH', '/orders/' . $id, $param);
        $data = json_decode($response->getContent(), true);
        echo "\n $this->gc 2.) Test for empty request data for Order update $this->nc \n";
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";
        $this->assertEquals(422, $response->getStatusCode());

        $param = [
            'status' => 'UNASSIGNED'
        ];

        $response = $this->call('PATCH', '/orders/' . $id, $param);
        $data = json_decode($response->getContent(), true);
        echo "\n $this->gc 3.) Test for invalid status value for Order update $this->nc \n";
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";
        $this->assertEquals(422, $response->getStatusCode());

        $param = [
            'status1' => 'TAKEN'
        ];

        $response = $this->call('PATCH', '/orders/' . $id, $param);
        $data = json_decode($response->getContent(), true);
        echo "\n $this->gc 4.) Test for invalid status key for Order update $this->nc \n";
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";
        $this->assertEquals(422, $response->getStatusCode());

        $param = [
            'status1' => 'TAKEN'
        ];

        $response = $this->call('PATCH', '/orders/', $param);
        $data = json_decode($response->getContent(), true);
        echo "\n $this->gc 5.) Test for invalid route and method for Order update $this->nc \n";
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";
        $this->assertEquals(400, $response->getStatusCode());
    }
}
