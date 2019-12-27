<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Http\Controllers\OrderController;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Requests\OrderShowRequest;
use App\Order as Order;
use App\Repositories\OrderRepository as OrderRepository;

use Illuminate\Http\Request as Request;

use Faker\Factory as Faker;

class OrderTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    public function setUp(): void
    {
        parent::setUp();
        $this->orderRepositoryMock = \Mockery::mock(OrderRepository::class);
        $this->orderMock = \Mockery::mock(Order::class);
        $this->orderControllerMock = $this->app->instance(
            OrderController::class,
            new OrderController($this->orderRepositoryMock)
        );
        $this->faker = Faker::create();
        $this->yc = "\033[33m"; // terminal color yellow
        $this->gc = "\033[32m"; // terminal color green
        $this->rc = "\033[31m"; // terminal color red
        $this->nc = "\033[0m";  // terminal end color 
        $this->bt = "\033[1m";
    }

    public function tearDown(): void
    {
        \Mockery::close();
    }




    public function getOrderShowRequest(array $requestData)
    {
        $request = new OrderShowRequest();
        $request->replace($requestData);
        return $request;
    }
    public function getOrderCreateRequest(array $requestData)
    {
        $request = new OrderCreateRequest();
        $request->replace($requestData);
        return $request;
    }
    public function getOrderUpdateRequest(array $requestData)
    {
        $request = new OrderUpdateRequest();
        $request->replace($requestData);
        return $request;
    }




    /*
    *  ======================== Start Create Order Test ================================ **
    */

    public function createOrder($data)
    {
        $id = rand(1, 100);

        list($origin_lat, $origin_lng) = $data['origin'];
        list($destination_lat, $destination_lng) = $data['destination'];
        $distance = rand(1000.00, 10000.99); //random distance

        $this->orderRepositoryMock
            ->shouldReceive('getDistance')
            ->withAnyArgs()
            ->andReturn($distance);
        $order = new Order();
        $order->id = $id;
        $order->origin_lat = $origin_lat;
        $order->origin_lng = $origin_lng;
        $order->destination_lat = $destination_lat;
        $order->destination_lng = $destination_lng;
        $order->distance = $distance;
        $order->status = Order::UNASSIGNED;
        $order->created_at = date('Y-m-d H:i:s');
        $order->updated_at = date('Y-m-d H:i:s');

        return [
            'id' => $order->id,
            'distance' => $order->distance,
            'status' => $order->status
        ];
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCreateOrderSuccess()
    {
        $param = [
            'origin' => ["28.6019042", "77.1848588"], // Origin Location 
            'destination' => ["28.5974392", "77.2016173"], //Destination Location
        ];

        $order = $this->createOrder($param);
        $param = $this->getOrderCreateRequest($param);
        $this->orderRepositoryMock
            ->shouldReceive('create')
            ->withAnyArgs()
            ->once()
            ->andReturn($order);
        $response = $this->orderControllerMock->store($param);
        $data = json_decode($response->getContent(), true);

        echo "\n $this->yc =============================================$this->bt STARTING UNIT TEST CASES FOR ORDER API.$this->nc $this->yc============================================= $this->nc \n\n";
        echo "\n $this->yc ============= #1. CREATING ORDERS TEST ==================== $this->nc \n";
        echo "\n $this->gc 1.) Test for valid / correct Order creation $this->nc \n";

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('distance', $data);
        $this->assertIsInt($data['distance']);
        $this->assertArrayHasKey('status', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }


    public function testCreateOrderFail_EmptyData()
    {
        echo "\n $this->gc 2.) Test for empty request data for Order creation $this->nc \n";

        $param = [];
        $response =  $this->call('POST', '/orders', $param);
        $data = json_decode($response->getContent(), true);
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";
        $response->assertJsonStructure(['error']);
        $this->assertIsArray($data);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertInstanceOf('Illuminate\Foundation\Testing\TestResponse', $response);
    }

    public function testCreateOrderFail_InvalidCoordinates()
    {
        echo "\n $this->gc 3.) Test with Invalid Coordinates (Array length, datatype and lat-lng boundry) for Order creation $this->nc \n";

        $param = [
            'origin' => [28.6019042, 77.8897, "28.601982"], // Float Origin Latitude, Integer Longitude, length of origin array
            'destination' => ["", 477.2016173], //Out of boundry Destination Longitude
        ];

        $response =  $this->call('POST', '/orders', $param);
        $data = json_decode($response->getContent(), true);
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";
        $response->assertJsonStructure(['error']);
        $this->assertIsArray($data);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertInstanceOf('Illuminate\Foundation\Testing\TestResponse', $response);
    }

    public function testCreateOrderFail_EmptyReponse()
    {
        echo "\n $this->gc 4.) Test with Empty Reponse for Order creation $this->nc \n";

        $param = [
            'origin' => ["28.6019042", "77.1848588"], // Origin Location 
            'destination' => ["28.5974392", "77.2016173"], //Destination Location
        ];
        $param = $this->getOrderCreateRequest($param);

        $this->orderRepositoryMock
            ->shouldReceive('create')
            ->withAnyArgs()
            ->once()
            ->andReturn([]);

        $response = $this->orderControllerMock->store($param);
        $data = json_decode($response->getContent(), true);
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }

    public function testCreateOrderFail_Exception()
    {
        echo "\n $this->gc 5.) Test with Exception for Order creation $this->nc \n";

        $param = [
            'origin' => ["28.6019042", "77.1848588"], // Origin Location 
            'destination' => ["28.5974392", "77.2016173"], //Destination Location
        ];
        $param = $this->getOrderCreateRequest($param);

        $this->orderRepositoryMock
            ->shouldReceive('create')
            ->withAnyArgs()
            ->once()
            ->andThrow(new \Exception());

        $response = $this->orderControllerMock->store($param);
        $data = json_decode($response->getContent(), true);
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }
    /*
    *  ======================== End Create Order Test ================================ **
    */







    /*
    *  ======================== Start Update Order Test ================================ **
    */
    public function updateOrder($data, $id)
    {
        $order = new Order;
        $order->id = $id;
        $order->status = Order::UNASSIGNED;
        if ($order == null) {
            return response()->json([
                'error' => 'Provided Order Id is invalid'
            ])->setStatusCode(404);
        }

        if (!isset($data['status']) || $data['status'] != 'TAKEN') {
            return response()->json([
                'error' => 'Invalid status provided'
            ])->setStatusCode(422);
        }

        if ($order['status'] == Order::TAKEN && $data['status'] == Order::TAKEN) {
            return response()->json([
                'error' => 'Order has been taken already.'
            ])->setStatusCode(409);
        }
    }

    public function findOrder($id)
    {
        $order = null;

        if ($order == null) {
            return response()->json([
                'error' => 'Provided Order Id is invalid'
            ])->setStatusCode(404);
        }
    }

    public function testUpdateOrderSuccess()
    {
        echo "\n\n\n $this->yc============= #2. UPDATING ORDERS TEST ==================== $this->nc \n";
        echo "\n $this->gc 1.) Test for valid / correct Order update $this->nc \n";

        $param = ['status' => "TAKEN"];
        $id = rand(1, 100);
        $this->orderRepositoryMock
            ->shouldReceive('update')
            ->andReturn([
                'status' => 'SUCCESS'
            ]);

        $response =  $this->call('PATCH', '/orders/' . $id, $param);

        $data = json_decode($response->getContent(), true);

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertSame('SUCCESS', $data['status']);
        $this->assertInstanceOf('Illuminate\Foundation\Testing\TestResponse', $response);
    }

    public function testUpdateOrderFail_WrongMethod()
    {
        echo "\n $this->gc 2.) Test for wrong method or url for Order update $this->nc \n";

        $param = [];
        $response =  $this->call('PATCH', '/orders', $param);
        $data = json_decode($response->getContent(), true);

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertInstanceOf('Illuminate\Foundation\Testing\TestResponse', $response);
    }


    public function testUpdateOrderFail_EmptyRequestdata()
    {
        echo "\n $this->gc 3.) Test for Empty request data for Order update $this->nc \n";

        $param = [];
        $id = rand(1, 100);

        $response =  $this->call('PATCH', '/orders/' . $id, $param);
        $data = json_decode($response->getContent(), true);

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertInstanceOf('Illuminate\Foundation\Testing\TestResponse', $response);
    }

    public function testUpdateOrderFail_InvalidStatus()
    {
        echo "\n $this->gc 4.) Test for Invalid Status for Order update $this->nc \n";

        $param = [
            'status' => 'UNASSIGNED'
        ];
        $id = rand(1, 100);
        $this->orderRepositoryMock
            ->shouldReceive('update')
            ->andReturn($this->updateOrder($param, $id));

        $response =  $this->call('PATCH', '/orders/' . $id, $param);
        $data = json_decode($response->getContent(), true);

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertInstanceOf('Illuminate\Foundation\Testing\TestResponse', $response);
    }

    public function testUpdateOrderFail_InvalidOrderId()
    {
        echo "\n $this->gc 5.) Test for Invalid Order for Order update $this->nc \n";

        $param = [
            'status' => 'TAKEN'
        ];
        $id = rand(100000000, 999999999);
        $param = $this->getOrderUpdateRequest($param);
        $this->orderRepositoryMock
            ->shouldReceive('update')
            ->andReturn($this->findOrder($id));

        $response = $this->orderControllerMock->update($param, $id);

        $data = json_decode($response->getContent(), true);

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }

    public function testUpdateOrderFail_Exception()
    {
        echo "\n $this->gc 6.) Test with Exception for Order udpate $this->nc \n";

        $param = [
            'status' => 'TAKEN'
        ];
        $id = rand(1, 100);

        $this->orderRepositoryMock
            ->shouldReceive('update')
            ->withAnyArgs()
            ->once()
            ->andThrow(new \Exception());

        $param = $this->getOrderUpdateRequest($param);
        $response = $this->orderControllerMock->update($param, $id);


        $data = json_decode($response->getContent(), true);
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }


    /*
    *  ======================== End Update Order Test ================================ **
    */









    /*
    *  ======================== Start Show Order List Test ================================ **
    */

    public function listOrders()
    {

        return [
            ["id" => 1, "distance" => 3392, "status" => "UNASSIGNED"],
            ["id" => 2, "distance" => 2366, "status" => "TAKEN"],
            ["id" => 3, "distance" => 1822, "status" => "UNASSIGNED"],
            ["id" => 4, "distance" => 14370, "status" => "TAKEN"],
            ["id" => 5, "distance" => 8371, "status" => "TAKEN"]
        ];
    }

    public function testShowOrderSuccess()
    {

        $param = [
            'page' => 1,
            'limit' => 5
        ];

        $param = $this->getOrderShowRequest($param);
        $this->orderRepositoryMock
            ->shouldReceive('list')
            ->withAnyArgs()
            ->once()
            ->andReturn($this->listOrders($param));
        $response = $this->orderControllerMock->index($param);
        $data = json_decode($response->getContent(), true);

        echo "\n\n $this->yc ============= #3. SHOW ORDERS TEST ==================== $this->nc \n";
        echo "\n $this->gc 1.) Test for valid Order List  $this->nc \n";

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('distance', $data[0]);
        $this->assertIsInt($data[0]['distance']);
        $this->assertArrayHasKey('status', $data[0]);
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }

    public function testShowOrderFail_EmptyRequestdata()
    {
        echo "\n $this->gc 2.) Test for Empty request data for Order list $this->nc \n";

        $param = [];
        $id = rand(1, 100);

        $response =  $this->call('GET', '/orders', $param);
        $data = json_decode($response->getContent(), true);

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertInstanceOf('Illuminate\Foundation\Testing\TestResponse', $response);
    }

    public function testShowOrderFail_InvalidRequestData()
    {
        echo "\n $this->gc 3.) Test for Invalid request data (min-max of limit) for Order list $this->nc \n";

        $param = [
            'page' => "0",
            'limit' => 101
        ];
        $id = rand(1, 100);

        $response =  $this->call('GET', '/orders', $param);
        $data = json_decode($response->getContent(), true);

        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertInstanceOf('Illuminate\Foundation\Testing\TestResponse', $response);
    }

    public function testShowOrderFail_Exception()
    {
        echo "\n $this->gc 4.) Test with Exception for Order udpate $this->nc \n";

        $param = [
            'page' => 1,
            'limit' => 30
        ];

        $this->orderRepositoryMock
            ->shouldReceive('list')
            ->withAnyArgs()
            ->once()
            ->andThrow(new \Exception());

        $param = $this->getOrderShowRequest($param);
        $response = $this->orderControllerMock->index($param);


        $data = json_decode($response->getContent(), true);
        echo "Code:-  " . $response->getStatusCode() . " \nResponse:- " . json_encode($data) . "\n\n";

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertInstanceOf('Illuminate\Http\JsonResponse', $response);
    }

    /*
    *  ======================== End Show Order List Test ================================ **
    */
}
