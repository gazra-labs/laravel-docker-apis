<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\Repositories\OrderRepository;
use Exception;

use App\Http\Requests\OrderCreateRequest as OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest as OrderUpdateRequest;
use App\Http\Requests\OrderShowRequest as OrderShowRequest;

/**
 * @OA\Info(
 *   title="Order APIs",
 *   description="Order APIs",
 *   version="1.0",
 * )
 */

class OrderController extends Controller
{
    private $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    /**
     * @OA\Get(path="/orders?page=:page&limit=:limit",
     *   tags={"OrderList"},
     *   summary="Get the list of orders",
     *   description="To get the list of orders with id, status and distance in meter",
     *   operationId="orderList",
     *   parameters={},
     *   @OA\Parameter(
     *       name="page",
     *       in="query",
     *       description="Page number and it must be a valid integer with minimum 1",
     *       required=true,
     *       @OA\Schema(
     *           type="integer",
     *           format="int64"
     *          )
     *       ),
     *   @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       description="Limit of records to fetch, it must be a valid interger with minimum 1",
     *       required=true,
     *       @OA\Schema(
     *           type="integer",
     *           format="int64"
     *          )
     *       ),
     *   @OA\Response(
     *     response=200,
     *     description="Successfull"
     *   ),
     *   @OA\Response(
     *      response=400, 
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=422, 
     *      description="Invalid data provided"
     *   ),
     *   @OA\Response(
     *      response=500, 
     *      description="Internal Server Error"
     *   )
     *
     * )
     */
    public function index(OrderShowRequest $request)
    {
        try {
            $data = $this->orderRepo->list($request->all());
            if (!($data instanceof \Exception)) {
                return response()->json($data)->setStatusCode(200);
            } else {
                return response()->json([
                    'error' => __('Something went wrong.')
                ])->setStatusCode(500);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => __('Something went wrong.')
            ])->setStatusCode(500);
        }
    }

    /**
     * @OA\Post(
     *      path="/orders",
     *      operationId="orderCreate",
     *      tags={"Orders"},
     *      summary="To create order",
     *      description="Create orders and store driver distance of given coordinates",
     *      @OA\RequestBody(
     *             @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="origin",
     *                     type="array",
     *                     @OA\Items(
     *                          type="string"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="destination",
     *                     type="array",
     *                      @OA\Items(
     *                          type="string"
     *                     )
     *                 ),
     *                  example={"origin": {"28.6019042", "77.1848588"}, "destination": {"28.5974392", "77.2016173"}}
     *             )
     *           )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfull",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad requests",
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Invalid data provided",
     *       ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *       ),
     *       @OA\Response(
     *          response=503, 
     *          description="Service Unavailable"
     *      ),
     *     )
     *
     */
    
    public function store(OrderCreateRequest $request)
    {
        try {
            $data = $this->orderRepo->create($request->all());
            if ($data && isset($data['id'])) {
                return response()->json($data)->setStatusCode(200);
            } elseif (isset($data['error'])) {
                return response()->json([
                    'error' => $data['error']
                ])->setStatusCode(503);
            } else {
                return response()->json([
                    'error' => __('Order not created, please try again')
                ])->setStatusCode(500);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => __('Something went wrong.')
            ])->setStatusCode(500);
        }
    }

    /**
     * @OA\Patch(
     *      path="/orders/{id}",
     *      tags={"OrderTaken"},
     *      summary="Take Order",
     *      description="Change status from unassigned to taken",
     *      operationId="orderTaken",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Id of an order, should be interger and valid.",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="status",
     *                      type="string",
     *                  ),
     *                  example={"status": "TAKEN"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successfull",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Method not allowed"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Order not found"
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Order has been taken already."
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Invalid data provided"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error"
     *      ),
     *      @OA\Response(
     *          response=501,
     *          description="Order is not taken, please try again."
     *      )
     * )
     */

    public function update(OrderUpdateRequest $request, $id)
    {
        try {
            $data = $this->orderRepo->update($request->all(), $id);
            if (!($data instanceof \Exception)) {
                return $data;
            } else {
                return response()->json([
                    'error' => __('Order is not taken, please try again.')
                ])->setStatusCode(501);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong.'
            ])->setStatusCode(500);
        }
    }
}
