<?php

namespace App\Repositories;

use App\Order;
use Czim\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository
{
    /**
     * Returns specified model class name.
     *
     * @return string
     */
    public function model()
    {
        return Order::class;
    }

    public function list($data)
    {
        try {

            $page = (int) $data['page'];
            $limit = (int) $data['limit'];
            $order = Order::select(['id', 'distance', 'status'])->paginate($limit);

            return $order->items();
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * @desc Function used to store the order
     *
     * @param array $data
     *
     * @return User|\Illuminate\Database\Eloquent\Model|null
     */
    public function create(array $data)
    {

        try{
            list($origin_lat, $origin_lng) = $data['origin'];
            list($destination_lat, $destination_lng) = $data['destination'];

            $distance = $this->getDistance($origin_lat, $origin_lng, $destination_lat, $destination_lng);

            if (!is_array($distance)) {
                return [
                    'error' => "Could not find distance for provided coordinates."
                ];
            }

            $insertData = [
                'origin_lat'        => $origin_lat,
                'origin_lng'        => $origin_lng,
                'destination_lat'   => $destination_lat,
                'destination_lng'   => $destination_lng,
                'distance'          => $distance['distance'],
                'status'            => Order::UNASSIGNED
            ];

            return DB::transaction(function () use ($insertData) {
                $orderModel = new Order();
                $orderModel->origin_lat = $insertData['origin_lat'];
                $orderModel->origin_lng = $insertData['origin_lng'];
                $orderModel->destination_lat = $insertData['destination_lat'];
                $orderModel->destination_lng = $insertData['destination_lng'];
                $orderModel->distance = $insertData['distance'];
                $orderModel->status = $insertData['status'];
                $orderModel->save();
                if ($orderModel) {
                    return [
                        'id' => $orderModel->id,
                        'distance' => $orderModel->distance,
                        'status' => $orderModel->status
                    ];
                }
            });
        }catch ( \Exception $e ) {
            throw $e;
        }

        
    }

    /**
     * @desc Function used to update the order
     *
     * @param array $data
     *
     * @return User|\Illuminate\Database\Eloquent\Model|null
     */
    public function update(array $data, $id, $attribute = NULL)
    {

        try {
            $order = Order::find($id);

            if ($order == null) {
                return response()->json([
                    'error' => 'Provided Order Id is invalid'
                ])->setStatusCode(404);
            }

            if (!isset($data['status']) || $data['status'] != 'TAKEN') {
                return response()->json([
                    'error' => 'Invalid data provided'
                ])->setStatusCode(422);
            }

            if ($order['status'] == Order::TAKEN && $data['status'] == Order::TAKEN) {
                return response()->json([
                    'error' => 'Order has been taken already.'
                ])->setStatusCode(409);
            }

            return DB::transaction(function () use ($data, $id) {
                $orderModel = Order::where([
                    ['status', '=', Order::UNASSIGNED],
                    ['id', '=', $id],
                ])->lockForUpdate()->update([
                    'status' => $data['status']
                ]);

                if ($orderModel) {
                    return response()->json([
                        'status' => 'SUCCESS'
                    ])->setStatusCode(200);
                } else {
                    return response()->json([
                        'error' => 'Order has been taken already.'
                    ])->setStatusCode(409);
                }
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getDistance($lat1, $long1, $lat2, $long2)
    {

        if (empty(config('app.GOOGLE_API_KEY'))) {
            return 'Api key is could not be blank';
        }

        $url = config('app.GOOGLE_API_URL') . "&origins=" . $lat1 . "," . $long1 . "&destinations=" . $lat2 . "," . $long2 . "&key=" . config('app.GOOGLE_API_KEY');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $json = curl_exec($ch);

        if ($json === null || $json == FALSE || $json == '') {
            $err = curl_error($ch);
            return $err;
        } else {
            curl_close($ch);

            $arr = json_decode($json, true);

            if ($arr['rows'][0]['elements'][0]['status'] != 'ZERO_RESULTS') {
                $dist = $arr['rows'][0]['elements'][0]['distance']['value'];
                return array('distance' => $dist);
            } else {
                return "No distance found";
            }
        }
    }
}
