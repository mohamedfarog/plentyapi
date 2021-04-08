<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    
    public function index(Request $request)
    {
        $today = Carbon::now();

        $couponexist= Coupon::where('code',strtoupper($request->code))->first();
        if(!$couponexist){
            return response()->json(['Response'=>false,   'Coupon'=>'Coupon Code does not exist']);

        }

        $q = Coupon::with('shop');
        if (isset($request->isexpired))
            $q = $q->where('expiry', '<', Carbon::now());
        if (isset($request->shop_id))
            $q = $q->where('shop_id', '=', $request->shop_id);
        if (isset($request->code) && $request->code != '') {
            $q = $q->where('code', 'like', "%" . $request->code . "%",);
        }
  
        if( $today->gt($q->first()->expiry)){
            return response()->json(['Response'=>false,   'Coupon'=>'Coupon is expired']);

        }
        return response()->json(['Response'=>!!$q, 'Coupon'=>$q->first()]);
    }

  
    public function store(Request $request)
    {
    
        $validator = Validator::make($request->all(), [
            "action" => "required|string"
        ]);
        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()]);
        }
        
        if (isset($request->action)) {
            switch ($request->action) {
                case 'create':
                    $request->validate([
                        "value" => "required",
                        "code" => ["required", Rule::unique('coupons')]
                    ]);
                    $coupon = new Coupon();
                    


                    if (Coupon::where('code', '=', strtoupper($request->code))->first()) {

                        return response()->json(["error" => $validator->errors(),  "status_code" => 0]);
                    } else {
                        $coupon->code = strtoupper($request->code);
                        $coupon->value = $request->value;
                    }
                    if (isset($request->shop_id)) {
                        if($request->shop_id == '0'){
                        }
                        else{
                            $coupon->shop_id = $request->shop_id;

                        }
                    }
                    else{

                    }
                    if (isset($request->expiry)) {
                        $coupon->expiry = $request->expiry;
                    }
                    if (isset($request->percentage)) {
                        $coupon->ispercentage = $request->ispercentage;

                    }

                    $coupon->save();

                    return response()->json(['error' => "Coupons added"]);
                    break;

                case "activate":

                    if (isset($request->id)) {
                        $coupon = Coupon::where('id', $request->id)->first();
                        $coupon->update(['expiry' =>  $coupon->expiry->addDays(7) ]);
                        return redirect('/coupons');

                        // return response()->json(['status' => !!$coupon, 'data' => $coupon]);
                    }
                    return response()->json(['error' => "something went wrong"]);
                    break;

                case "update":
                    if (isset($request->update_id)) {
                    
                        $coupon =  Coupon::findorfail($request->update_id);
                        $coupon->value = $request->value;
                        if (isset($request->shop_id)) {
                            $coupon->shop_id = $request->shop_id;
                        }
                        if (isset($request->expiry)) {
                            $coupon->expiry = $request->expiry;
                        }
                        $coupon->ispercentage = $request->ispercentage;
    
                        $coupon->save();
                        return back();
                    }

                case 'expire':
                    if (isset($request->ids)) {
                        $coupon = Coupon::where('id', $request->ids)->update(['expiry' => Carbon::now()]);
                        return redirect('/coupons');
                    }
                    return response()->json(['error' => "something went wrong"]);
                    break;
                case 'coupon':

                    $c = Coupon::with('shop')->where('code', '=', strtoupper($request->coupon))->first();
                    if ($c)
                        return $c;
                    return response()->json(["error" => "Not found"]);
                    break;
                default:
                    return response()->json(['error' => "something went wrong"]);
            }
        }
    }

   
}