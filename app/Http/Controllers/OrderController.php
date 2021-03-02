<?php

namespace App\Http\Controllers;

use App\Models\Detail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $user = Auth::user();
        $order = '';
        $msg = '';
        if (isset($request->id)) {
            $order = Order::where('id', $request->id)->first();
            switch ($request->action) {
                case 'delete':
                    $order->delete();
                    $msg = 'Order has been deleted';
                    return response()->json(['success' => !!$order, 'message' => $msg]);
                    break;
                case 'update':
                    if (isset($request->ref)) {
                        $order->ref = $request->ref;
                    }
                    if (isset($request->total_amount)) {
                        $order->total_amount = $request->total_amount;
                    }
                    if (isset($request->amount_due)) {
                        $order->amount_due = $request->amount_due;
                    }
                    if (isset($request->order_status)) {
                        $order->order_status = $request->order_status;
                    }
                    if (isset($request->payment_method)) {
                        $order->payment_method = $request->payment_method;
                    }
                    if (isset($request->tax)) {
                        $order->tax = $request->tax;
                    }
                    if (isset($request->delivery_charge)) {
                        $order->delivery_charge = $request->delivery_charge;
                    }
                    if (isset($request->delivery_location)) {
                        $order->delivery_location = $request->delivery_location;
                    }
                    if (isset($request->user_id)) {
                        $order->user_id = $request->user_id;
                    }
                    $msg = 'Order has been updated';

                    $order->save();
                    return response()->json(['success' => !!$order, 'message' => $msg]);
                    break;
            }
        } else {
            $validator = Validator::make($request->all(), [
                "total_amount" => "required",
                "amount_due" => "required",
                "delivery_location" => "required",
                "orderdetails" => "required"
            ]);

            if ($validator->fails()) {
                return response()->json(["error" => $validator->errors(),  "status_code" => 0]);
            }
            $data = array();
            $data['user_id'] = $user->id;
            $data['ref'] = Str::uuid();
            $data['total_amount'] = $request->total_amount;
            $data['amount_due'] = $request->amount_due;
            $data['delivery_location'] = $request->delivery_location;
            if (isset($request->order_status)) {
                $data['order_status'] = $request->order_status;
            }
            if (isset($request->payment_method)) {
                $data['payment_method'] = $request->payment_method;
            }
            if (isset($request->tax)) {
                $data['tax'] = $request->tax;
            }
            if (isset($request->delivery_charge)) {
                $data['delivery_charge'] = $request->delivery_charge;
            }


            $order = Order::create($data);
            foreach ($request->orderdetails as $orderdetails) {
                $arr = array();
                if (isset($orderdetails['product_id'])) {
                    $arr['product_id'] = $orderdetails['product_id'];
                }
                if (isset($orderdetails['qty'])) {
                    $arr['qty'] = $orderdetails['qty'];
                }
                if (isset($orderdetails['shop_id'])) {
                    $arr['shop_id'] = $orderdetails['shop_id'];
                }
                if (isset($orderdetails['price'])) {
                    $arr['price'] = $orderdetails['price'];
                }
                if (isset($orderdetails['color_id'])) {
                    $arr['color_id'] = $orderdetails['color_id'];
                }
                if (isset($orderdetails['size_id'])) {
                    $arr['size_id'] = $orderdetails['size_id'];
                }
                if (isset($orderdetails['booking_date'])) {
                    $arr['booking_date'] = $orderdetails['booking_date'];
                }
                if (isset($orderdetails['booking_time'])) {
                    $arr['booking_time'] = $orderdetails['booking_time'];
                }
                if (isset($orderdetails['addons'])) {
                    
                    $arr['addons'] = implode(',', $orderdetails['addons']);
                }
                $arr['order_id'] =  $order->id;
                $detail = Detail::create($arr);
            }

            $msg = 'Order has been added';
            return response()->json(['success' => !!$order, 'message' => $msg]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}