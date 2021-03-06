<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\ApplePass;
use App\Models\Loyalty;
use App\Models\Otp;
use App\Models\Project;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\PushNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Thenextweb\Definitions\StoreCard;
use Thenextweb\PassGenerator;

class UserController extends Controller
{
    use SendsPasswordResetEmails;
    public function forgetPassword(Request $request)
    {
        if (isset($request->email)) {
            $user = User::where('email', $request->email)->whereNotNull('email_verified_at')->first();
            if ($user) {
                $this->sendResetLinkEmail($request);
                return response()->json(['response' => "If the email you specified was in our system, we sent it a password reset link."]);
            }
            return response()->json(['error' => "The email you specified was not found in our system"]);
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //

        $user = Auth::user();
        switch ($user->typeofuser) {
            case 'S':
                $perpage = 15;
                if (isset($request->perpage)) {
                    $perpage = $request->perpage;
                }
                if (isset($request->all)) {


                    return User::with(['tier'])->get();
                }
                if (isset($request->search)) {
                    return User::where(function ($user) use ($request) {
                        return $user->where('name', 'LIKE', '%' . $request->search . '%')->orWhere('email', 'LIKE', '%' . $request->search . '%')->orWhere('contact', 'LIKE', '%' . $request->search . '%');
                    })->with(['tier'])->paginate($perpage);
                }
                return User::with(['tier'])->paginate($perpage);
                break;

            default:
                # code...
                break;
        }
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

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            if ($user->email_verified_at != NULL) {
                $success["message"] = "Login successful";
                $success["token"] = $user->createToken('MyApp')->accessToken;
                $u = User::with(['tier', 'shop'])->find($user->id);

                return response()->json(["success" => $success, "user" => $u, "status_code" => 1],);
            } else {
                return response()->json(["error" => "Please verify the email"]);
            }
        } else {
            return response()->json(["error" => "Invalid Email/Password"], 400);
        }
    }



    public function register(Request $request)
    {
        $msg = '';
        $token = '';
        $user = User::with(['tier'])->where('contact', $request->contact)->first();
        $settings = Project::first();


        if ($user) {
            $dbUser = Auth::login($user);
            $dbUser = Auth::user();
            $msg = 'User already exists.';
            $token = $dbUser->createToken('MyApp')->accessToken;
            if ($dbUser->name == null || $dbUser->email == null) {
                $msg = 'User profile not updated.';
            }
        } else {
            $validator = Validator::make($request->all(), [
                "contact" => "required",
            ]);

            if ($validator->fails()) {
                return response()->json(["error" => $validator->errors(),  "status_code" => 0]);
            }
            $data = array();
            if (isset($request->name)) {
                $data['name'] = $request->name;
            }
            if (isset($request->email)) {
                $data['email'] = $request->email;
            }
            if (isset($request->password)) {
                $data['password'] = bcrypt($request->password);
            }
            if (isset($request->contact)) {
                $data['contact'] = $request->contact;
            }
            if (isset($request->eid)) {
                $data['eid'] = $request->eid;
            }
            if (isset($request->passport)) {
                $data['passport'] = $request->passport;
            }
            if (isset($request->others)) {
                $data['others'] = $request->others;
            }
            if (isset($request->apple_id)) {
                $data['apple_id'] = $request->apple_id;
            }
            if (isset($request->google_id)) {
                $data['google_id'] = $request->google_id;
            }
            if (isset($request->active)) {
                $data['active'] = $request->active;
            }
            if (isset($request->verified)) {
                $data['verified'] = $request->verified;
            }
            if (isset($request->typeofuser)) {
                $data['typeofuser'] = $request->typeofuser;
            }
            if (isset($request->gender)) {
                $data['gender'] = $request->gender;
            }
            if (isset($request->invitationcode)) {
                $data['invitationcode'] = $request->invitationcode;
            }
            if (isset($request->invites)) {
                $data['invites'] = $request->invites;
            }

            $start = '1';
            $end = '';
            for ($i = 0; $i < $settings->invcode - 1; $i++) {

                $start .= '0';
            }
            for ($i = 0; $i < $settings->invcode; $i++) {

                $end .= '9';
            }
            $run = true;
            $code = 'P-' . rand(intval($start), intval($end));

            while ($run) {

                $user = User::where('invitation_code', $code)->first();
                if ($user != null) {
                    $code = 'P-' . rand(intval($start), intval($end));
                } else {
                    $data['invitation_code'] = $code;
                    $run = false;
                }
            }
            $data['points'] = $settings->registerpts;

            $user = User::create($data);
            //Update foodics database
            $dbUser = Auth::login($user);
            $user = User::with(['tier'])->find($user->id);
            $dbUser = Auth::user();
            $token = $dbUser->createToken('MyApp')->accessToken;
            $msg = 'User has been added';
        }

        return response()->json(['success' => !!$user, 'message' => $msg, 'token' => $token, 'user' => $user]);
    }

    public function myProfile(Request $request)
    {
        $user = User::find(Auth::id());
        if (isset($request->action)) {
            switch ($request->action) {
                case 'get':
                    $users = User::with(['tier'])->find($user->id);
                    return $users;
                    break;

                case 'update':
                    $msg = '';
                    if (isset($request->name)) {
                        $user->name = $request->name;
                    }
                    if (isset($request->email)) {
                        $user->email = $request->email;
                    }
                    if (isset($request->bday)) {
                        $user->bday = $request->bday;
                    }
                    if (isset($request->points)) {
                        $user->points = $request->points;
                    }
                    // if(isset($request->contact)){
                    //     $user->contact = $request->contact;
                    // }
                    if (isset($request->gender)) {
                        $user->gender = $request->gender;
                    }

                    if (isset($request->password)) {
                        $user->password = bcrypt($request->password);
                    }

                    $user->save();

                    (new ApplePass())->createLoyaltyPass($user);
                    Loyalty::notifyApple(explode('.', $user->loyaltyidentifier)[0]);

                    if ($user->accessidentifier != null) {
                        (new ApplePass())->createAccessPass($user->id, null);
                    }
                    // (new FoodicsController())->createUser($user);

                    $msg = 'Account details updated successfully.';
                    return response()->json(['success' => !!$user, 'message' => $msg, 'user' => $user]);
                    break;
            }
        }
    }

    public function autologin(Request $request)
    {
        $user = Auth::user();



        $userl = User::with('shop')->where('id', $user->id)->first();


        return response()->json(['user' => $userl]);
    }

    public function dashLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "type" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors(),  "status_code" => 0]);
        }
        switch ($request->type) {
            case 'email':
                $validator = Validator::make($request->all(), [
                    "email" => "required",
                    "password" => "required",

                ]);

                if ($validator->fails()) {
                    return response()->json(["error" => $validator->errors(),  "status_code" => 0]);
                }
                if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                    $user = Auth::user();
                    if ($user->typeofuser != "U") {
                        if ($user->email_verified_at != NULL) {
                            $success["message"] = "Login successful";
                            $success["token"] = $user->createToken('MyApp')->accessToken;
                            $u = User::with(['tier', 'designer'])->find($user->id);

                            return response()->json(["success" => $success, "user" => $u]);
                        } else {
                            return response()->json(["error" => "Please verify the email"], 400);
                        }
                    } else {
                        return response()->json(["error" => "You don't have enough permissions."], 400);
                    }
                } else {
                    return response()->json(["error" => "Invalid Email/Password"], 400);
                }
                break;
            case 'phone':
                $validator = Validator::make($request->all(), [
                    "contact" => "required",
                    "otp" => "required",

                ]);

                if ($validator->fails()) {
                    return response()->json(["error" => $validator->errors(),  "status_code" => 0]);
                }
                $otp = Otp::where('contact', $request->contact)->where('verified', 0)->first();
                $verified = false;
                $msg = '';
                $success = array();
                if ($otp != null) {
                    if (isset($request->otp)) {
                        if ($otp->otp == $request->otp) {
                            $verified  = true;
                            $otp->verified = true;
                            $msg = 'OTP has been verified. Login successful.';
                            $otp->save();

                            $user = User::with(['tier'])->where('contact', $request->contact)->first();
                            if ($user->typeofuser != "U") {
                                Auth::login($user);
                                $loggeduser = Auth::user();
                                if ($user) {
                                    $success['message'] = $msg;
                                    $success['token'] = $loggeduser->createToken('MyApp')->accessToken;
                                }
                            } else {
                                return response()->json(["error" => "You don't have enough permissions."], 400);
                            }
                        } else {
                            $verified = false;
                            $msg = 'OTP entered is incorrect.';
                            $success['message'] = $msg;
                        }
                    }
                } else {
                    return response()->json(["error" => "Invalid OTP entered."], 400);
                }

                return response()->json(['success' => $verified, 'result' => $success, 'user' => $user]);
                break;
            default:
                # code...
                break;
        }
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $logged = Auth::user();
        $settings = Project::first();
        switch ($logged->typeofuser) {
            case 'S':
                $user = '';
                $msg = '';
                if (isset($request->id)) {
                    $user = User::where('id', $request->id)->first();
                    switch ($request->action) {
                        case 'delete':
                            $user->delete();
                            $msg = 'User has been deleted';
                            return response()->json(['success' => !!$user, 'message' => $msg]);
                            break;
                        case 'update':
                            if (isset($request->name)) {
                                $user->name = $request->name;
                            }
                            if (isset($request->email)) {
                                $user->email = $request->email;
                            }
                            if (isset($request->password)) {
                                $user->password = bcrypt($request->password);
                            }
                            if (isset($request->bday)) {
                                $user->bday = $request->bday;
                            }
                            if (isset($request->contact)) {
                                $user->contact = $request->contact;
                            }
                            if (isset($request->eid)) {
                                $user->eid = $request->eid;
                            }
                            if (isset($request->passport)) {
                                $user->passport = $request->passport;
                            }
                            if (isset($request->others)) {
                                $user->others = $request->others;
                            }
                            if (isset($request->apple_id)) {
                                $user->apple_id = $request->apple_id;
                            }
                            if (isset($request->google_id)) {
                                $user->google_id = $request->google_id;
                            }
                            if (isset($request->active)) {
                                $user->active = $request->active;
                            }
                            if (isset($request->verified)) {
                                $user->verified = $request->verified;
                            }
                            if (isset($request->typeofuser)) {
                                $user->typeofuser = $request->typeofuser;
                            }
                            if (isset($request->gender)) {
                                $user->gender = $request->gender;
                            }
                            if (isset($request->points)) {
                                $user->points = $request->points;
                            }
                            if (isset($request->invitationcode)) {
                                $user->invitationcode = $request->invitationcode;
                            }
                            if (isset($request->invites)) {
                                $user->invites = $request->invites;
                            }
                            $msg = 'User has been updated';
                            $user->save();
                            $newuser = User::where('id', $user->id)->first();
                            if (!is_null($newuser->name) && !is_null($newuser->contact) && !is_null($newuser->email)) {

                                (new ApplePass())->createLoyaltyPass($newuser);
                                Loyalty::notifyApple(explode('.', $newuser->loyaltyidentifier)[0]);
                            }
                            return response()->json(['success' => !!$user, 'message' => $msg]);
                            break;
                    }
                } else {
                    $data = array();
                    if (isset($request->name)) {
                        $data['name'] = $request->name;
                    }
                    if (isset($request->email)) {
                        $data['email'] = $request->email;
                    }
                    if (isset($request->password)) {
                        $data['password'] = bcrypt($request->password);
                    }
                    if (isset($request->contact)) {
                        $data['contact'] = $request->contact;
                    }
                    if (isset($request->eid)) {
                        $data['eid'] = $request->eid;
                    }
                    if (isset($request->passport)) {
                        $data['passport'] = $request->passport;
                    }
                    if (isset($request->others)) {
                        $data['others'] = $request->others;
                    }
                    if (isset($request->bday)) {
                        $data['bday'] = $request->bday;
                    }
                    if (isset($request->apple_id)) {
                        $data['apple_id'] = $request->apple_id;
                    }
                    if (isset($request->google_id)) {
                        $data['google_id'] = $request->google_id;
                    }
                    if (isset($request->active)) {
                        $data['active'] = $request->active;
                    }
                    if (isset($request->verified)) {
                        $data['verified'] = $request->verified;
                    }
                    if (isset($request->typeofuser)) {
                        $data['typeofuser'] = $request->typeofuser;
                    }
                    if (isset($request->gender)) {
                        $data['gender'] = $request->gender;
                    }
                    if (isset($request->points)) {
                        $data['points'] = $request->points;
                    }
                    if (isset($request->invitationcode)) {
                        $data['invitationcode'] = $request->invitationcode;
                    }
                    if (isset($request->invites)) {
                        $data['invites'] = $request->invites;
                    }
                    $start = '1';
                    $end = '';
                    for ($i = 0; $i < $settings->invcode - 1; $i++) {

                        $start .= '0';
                    }
                    for ($i = 0; $i < $settings->invcode; $i++) {

                        $end .= '9';
                    }
                    $run = true;
                    $code = 'P-' . rand(intval($start), intval($end));

                    while ($run) {

                        $user = User::where('invitation_code', $code)->first();
                        if ($user != null) {
                            $code = 'P-' . rand(intval($start), intval($end));
                        } else {
                            $data['invitation_code'] = $code;
                            $run = false;
                        }
                    }
                    $data['points'] = $settings->registerpts;
                    $user = User::create($data);
                    $msg = 'User has been added';
                    if (!is_null($user->name) && !is_null($user->contact) && !is_null($user->email)) {

                        (new ApplePass())->createLoyaltyPass($user);
                    }
                    return response()->json(['success' => !!$user, 'message' => $msg]);
                }
                break;

            default:
                # code...
                break;
        }
    }

    public function topUpWallet(Request $request)
    {
        $authuser = Auth::user();

        if ($authuser) {
            if ($authuser->typeofuser == 'S') {
                if (isset($request->amount)) {
                    $user = User::with(['tier'])->find($request->user_id);
                    $user->wallet = $request->amount;
                    $user->save();
                    return response()->json(['success' => !!$user, 'user' => $user, 'message' => "The user's wallet balance has been updated"]);
                }
            } else {
                if (isset($request->amount)) {
                    $user = User::with(['tier'])->find($authuser->id);

                    $trans = new Transaction();
                    $trans->amount = $request->amount;
                    $trans->ref = Str::uuid();
                    // $trans->order_id = 0;
                    $trans->status = 0;
                    $trans->type = 'Wallet';
                    $trans->user_id = $authuser->id;
                    $trans->save();
                    if (isset($request->web)) {
                        $paygateway = $trans->createpayment($user, $request->amount, Str::uuid(), $trans->id, true);
                    }
                    $paygateway = $trans->createpayment($user, $request->amount, Str::uuid(), $trans->id, true);
                    // Mail::send('datadata', ['data' => $paygateway], function ($m) {
                    //     $m->from('noreply@dark.ae', 'PLENTY WALLET TEST');

                    //     $m->to('abubakar@mvp-apps.ae')->subject(`'PLENTY WALLET TEST`);
                    // });
                    return response()->json(['success' => true, 'message' => $paygateway, 'user' => $authuser]);

                    // $user->wallet += $request->amount;
                    // $user->save();
                    return response()->json(['success' => !!$user, 'user' => $user]);
                }
            }
        } else {
            return response()->json(["error" => 'Unauthorized User']);
        }
    }
    // Vendors sign up and login  for the Bazar
    public function vendorsRegister(Request $request)
    {

        $user = Auth::user();
        $settings = Project::first();

        if ($user->typeofuser == 'S') {

            $newuser = new User();
            if (isset($request->name)) {
                $newuser->name = $request->name;
            }
            if (isset($request->email)) {
                $newuser->email = $request->email;
            }
            if (isset($request->password)) {
                $newuser->password =  Hash::make($request->password);
            }
            if (isset($request->contact)) {
                $newuser->contact = $request->contact;
            }
            if (isset($request->name)) {
                $newuser->name = $request->name;
            }
            $newuser->email_verified_at = now();
            $newuser->typeofuser = 'V';
            $start = '1';
            $end = '';
            for ($i = 0; $i < $settings->invcode - 1; $i++) {

                $start .= '0';
            }
            for ($i = 0; $i < $settings->invcode; $i++) {

                $end .= '9';
            }
            $run = true;
            $code = 'P-' . rand(intval($start), intval($end));

            while ($run) {

                $user = User::where('invitation_code', $code)->first();
                if ($user != null) {
                    $code = 'P-' . rand(intval($start), intval($end));
                } else {
                    $newuser->invitation_code = $code;
                    $run = false;
                }
            }

            $newuser->save();
            $shop = new Shop();
            if (isset($request->name_en)) {
                $shop->name_en  = $request->name_en;
            }
            if (isset($request->name_ar)) {
                $shop->name_ar  = $request->name_ar;
            }
            if (isset($request->desc_en)) {
                $shop->desc_en  = $request->desc_en;
            }
            if (isset($request->desc_ar)) {
                $shop->desc_ar  = $request->desc_ar;
            }
            if (isset($request->eventcat_id)) {
                $shop->eventcat_id  = $request->eventcat_id;
            }
            if (isset($request->active)) {
                $shop->active  = $request->active;
            }
            $shop->user_id = $newuser->id;
            $shop->isvendor = 1;

            $shop->save();
            return response()->json(['success' => !!$user, 'Vendor' => $shop]);
        }
    }

    public function vendorSignup(Request $request)
    {


        $settings = Project::first();

        $newuser = new User();
        if (isset($request->name)) {
            $newuser->name = $request->name;
        }
        if (isset($request->email)) {
            $newuser->email = $request->email;
        }
        if (isset($request->password)) {
            $newuser->password =  Hash::make($request->password);
        }
        if (isset($request->contact)) {
            $newuser->contact = $request->contact;
        }
        if (isset($request->name)) {
            $newuser->name = $request->name;
        }
        if (isset($request->active)) {
            $newuser->active = $request->active;
        }
        // $newuser-> email_verified_at= now();
        $newuser->typeofuser = 'V';
        $start = '1';
        $end = '';
        for ($i = 0; $i < $settings->invcode - 1; $i++) {

            $start .= '0';
        }
        for ($i = 0; $i < $settings->invcode; $i++) {

            $end .= '9';
        }
        $run = true;
        $code = 'P-' . rand(intval($start), intval($end));

        while ($run) {

            $user = User::where('invitation_code', $code)->first();
            if ($user != null) {
                $code = 'P-' . rand(intval($start), intval($end));
            } else {
                $newuser->invitation_code = $code;
                $run = false;
            }
        }

        $newuser->save();
        $shop = new Shop();
        if (isset($request->name_en)) {
            $shop->name_en  = $request->name_en;
        }
        if (isset($request->name_ar)) {
            $shop->name_ar  = $request->name_ar;
        }
        if (isset($request->desc_en)) {
            $shop->desc_en  = $request->desc_en;
        }
        if (isset($request->desc_ar)) {
            $shop->desc_ar  = $request->desc_ar;
        }
        if (isset($request->eventcat_id)) {
            $shop->eventcat_id  = $request->eventcat_id;
        }
        if (isset($request->active)) {
            $shop->active  = $request->active;
        }
        $shop->user_id = $newuser->id;
        $shop->isvendor = 1;

        $shop->save();
        return response()->json(['success' => !!$user, 'Vendor' => $shop]);
    }

    public function vendorslogin(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            if ($user->typeofuser == 'V') {
                if ($user->email_verified_at != NULL) {
                    $success["message"] = "Login successful";
                    $success["token"] = $user->createToken('MyApp')->accessToken;
                    $u = User::with('shop')->where('id', $user->id)->first();

                    return response()->json(["success" => $success, "user" => $u, "status_code" => 1],);
                } else {
                    return response()->json(["error" => "Your account has not been approved yet. Please contact the administrator to obtain approval."]);
                }
            } else if ($user->typeofuser == 'B') {
                if ($user->email_verified_at != NULL) {
                    $success["message"] = "Login successful";
                    $success["token"] = $user->createToken('MyApp')->accessToken;
                    $u = User::with(['shop'  => function ($shop) {
                        return $shop->with(['style', 'cat'])->whereNotNull('cat_id');
                    }])->where('id', $user->id)->first();

                    return response()->json(["success" => $success, "user" => $u, "status_code" => 1],);
                } else {
                    return response()->json(["error" => "Your account has not been approved yet. Please contact the administrator to obtain approval."]);
                }
            } else {
                return response()->json(["error" => "You do not have permissions to log in. Please contact the administrator for more information."]);
            }
        } else {
            return response()->json(["error" => "Invalid Email/Password"]);
        }
    }


    public function sendNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required",
            "subtitle" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors(),  "status_code" => 0]);
        }
        $user = Auth::user();
        $title = $request->title;
        $subtitle = $request->subtitle;
        if ($user->typeofuser == 'S') {
            PushNotification::sendAllFCM($title, $subtitle);
            return response()->json(["message" => 'Notification has been sent!', "status_code" => 1],);
        } else {
            return response()->json(["error" => 'Unauthorized User']);
        }
    }

    public function updateFCM(Request $request)
    {
        $user = Auth::user();
        User::where("id", $user->id)->update(["fcm" => $request->fcm]);

        return response()->json(['success' => "true"]);
    }
}
