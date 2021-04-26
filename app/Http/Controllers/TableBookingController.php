<?php

namespace App\Http\Controllers;

use App\Models\TableBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TableBookingController extends Controller
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
     * Display the specified resource.
     *
     * @param  \App\Models\TableBooking  $tableBooking
     * @return \Illuminate\Http\Response
     */
    public function show(TableBooking $tableBooking)
    {
        //
    }

    public function store(Request $request)
    {
        //
        $user = Auth::user();
        $order = '';
        $msg = '';
        if (isset($request->id)) {
            $order = TableBooking::where('id', $request->id)->first();
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
                    if (isset($request->coupon_value)) {
                        $order->coupon_value = $request->coupon_value;
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
                    if (isset($request->lat)) {
                        $order->lat = $request->lat;
                    }
                    if (isset($request->lng)) {
                        $order->lng = $request->lng;
                    }
                    if (isset($request->delivery_note)) {
                        $order->delivery_note = $request->delivery_note;
                    }
                    if (isset($request->contact_number)) {
                        $order->contact_number = $request->contact_number;
                    }
                    if (isset($request->city)) {
                        $order->city = $request->city;
                    }
                    if (isset($request->label)) {
                        $order->label = $request->label;
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
               
                "bookingdetails" => "required"
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

            //  addPoints
            if (isset($request->order_status)) {
                $data['order_status'] = $request->order_status;
            }
            if (isset($request->payment_method)) {
                $data['payment_method'] = $request->payment_method;
            }

            if (isset($request->lat)) {
                $data['lat'] = $request->lat;
            }
            if (isset($request->lng)) {
                $data['lng'] = $request->lng;
            }
            if (isset($request->delivery_note)) {
                $data['delivery_note'] = $request->delivery_note;
            }
            if (isset($request->contact_number)) {
                $data['contact_number'] = $request->contact_number;
            }
            if (isset($request->city)) {
                $data['city'] = $request->city;
            }
            if (isset($request->label)) {
                $data['label'] = $request->label;
            }

            if (isset($request->points)) {
                //TODO POINT DEDUCTION (CHECK AGAIN)

                $customer = User::with(['tier'])->find($user->id);           //for updating the user model
                $customer->points = $user->points - $request->points;
            }
            if (isset($request->wallet)) {
                //TODO WALLET DEDUCTION (CHECK AGAIN)
                $customer->wallet = $user->wallet  - $request->wallet;
            }

            $loyalty = new Loyalty();


            if (isset($request->tax)) {
                $data['tax'] = $request->tax;
            }
            if (isset($request->delivery_charge)) {
                $data['delivery_charge'] = $request->delivery_charge;
            }
            if (isset($request->coupon_value)) {
                $data['coupon_value'] = $request->coupon_value;
            }
            $shoplist =  array();                // List of Shop Ids
            $pointsearned= $loyalty->addPoints($customer,$request->amount_due,$request->wallet??0 ,$shoplist);
            if($pointsearned){
                $data['points_earned'] = $pointsearned;
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
                if (isset($orderdetails['booking_time'])) {
                    $arr['booking_time'] = $orderdetails['booking_time'];
                }
                if (isset($orderdetails['shop_id'])) {
                    $arr['shop_id'] = $orderdetails['shop_id'];
                }
                // if (isset($orderdetails['shop_id'])) {
                //     $arr['shop_id'] = $orderdetails['shop_id'];
                //     if(!in_array($shoplist,$orderdetails['shop_id'])){
                //         array_push($shoplist,['shop_id'=> $orderdetails['shop_id'] ,'price' => $orderdetails['price'] ]);
                //     }
                //     else{
                //         $shoplist['price']= $shoplist['price'] + $orderdetails['price'];
                //     }
                //     return $shoplist;
                // }
                if (isset($orderdetails['price'])) {
                    $arr['price'] = $orderdetails['price'];
                }
                if (isset($orderdetails['color_id'])) {
                    if ($orderdetails['color_id'] == -1) {
                    } else
                        $arr['color_id'] = $orderdetails['color_id'];
                }
                if (isset($orderdetails['size_id'])) {
                    $arr['size_id'] = $orderdetails['size_id'];
                }
                if (isset($orderdetails['booking_date'])) {
                    $arr['booking_date'] = $orderdetails['booking_date'];
                }
                if (isset($orderdetails['timeslot_id'])) {
                    $arr['timeslot_id'] = $orderdetails['timeslot_id'];
                }
                if (isset($orderdetails['addons'])) {

                    $arr['addons'] = implode(',', $orderdetails['addons']);
                }
                $arr['order_id'] =  $order->id;
                $detail = Detail::create($arr);
            }
            
               
            $customer->points+=$pointsearned;
            
            $customer->save();
            $loyalty->calculateTier($customer,$request->amount_due,$request->wallet);
            $msg = 'Order has been added';

            


            return response()->json(['success' => !!$order, 'message' => $msg, 'user' => User::find($customer->id)]);
        }
    }
}
