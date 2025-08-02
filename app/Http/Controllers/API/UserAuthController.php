<?php

namespace App\Http\Controllers\API;

use App\Helpers\UserSystemInfo;
use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Models\ReferralBonus;
use App\Models\User;
use App\Models\UserLogin;
use App\Traits\Notify;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserAuthController extends Controller
{
    use ApiResponse,Notify;
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('username', 'password');
            $validator = Validator::make($credentials, [
                'username' => 'required|string',
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }
            $user = User::where('email', $credentials['username'])
                ->orWhere('username', $credentials['username'])
                ->first();
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json($this->withError('credentials do not match'));
            }
            $data['message'] = 'User logged in successfully.';
            $data['token'] = $user->createToken($user->email)->plainTextToken;
            return response()->json($this->withSuccess($data));
        }catch (\Exception $exception){
            return response()->json($this->withError($exception->getMessage()));
        }
    }

    public function register(Request $request)
    {
        $basic = basicControl();
        $data = $request->all();
        $registerRules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'username' => 'required|string|alpha_dash|min:5|unique:users,username',
            'email' => 'required|string|email|unique:users,email',
            'password' => $basic->strong_password == 0 ?
                ['required', 'confirmed', 'min:6'] :
                ['required', 'confirmed',  Password::min(6)->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()],
            'phone' => ['required', 'numeric', 'unique:users,phone'],
            'phone_code' => ['required', 'numeric'],
            'country' => ['required'],
            'country_code' => ['required']

        ];
        $message = [
            'password.letters' => 'password must be contain letters',
            'password.mixed' => 'password must be contain 1 uppercase and lowercase character',
            'password.symbols' => 'password must be contain symbols',
        ];
        $validation = Validator::make($request->all(), $registerRules,$message);
        if ($validation->fails()) {
            return response()->json($this->withError(collect($validation->errors())->collapse()));
        }
        $user =  User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'phone_code' => $data['phone_code'],
            'country' => $data['country'],
            'country_code' => $data['country_code'],
            'password' => Hash::make($data['password']),
            'email_verification' => ($basic->email_verification) ? 0 : 1,
            'sms_verification' => ($basic->sms_verification) ? 0 : 1
        ]);

        $this->registered($request, $user);

        return response()->json($this->withSuccess($user->createToken('token')->plainTextToken , 'Your Account Created Successfully'));
    }


    protected function registered(Request $request, $user)
    {
        $user->last_login = \Carbon\Carbon::now();
        $user->last_seen = Carbon::now();
        $user->two_fa_verify = ($user->two_fa == 1) ? 0 : 1;
        $user->save();

        $info = @json_decode(json_encode(getIpInfo()), true);
        $ul['user_id'] = $user->id;

        $ul['longitude'] = (!empty(@$info['long'])) ? implode(',', $info['long']) : null;
        $ul['latitude'] = (!empty(@$info['lat'])) ? implode(',', $info['lat']) : null;
        $ul['country_code'] = (!empty(@$info['code'])) ? implode(',', $info['code']) : null;
        $ul['location'] = (!empty(@$info['city'])) ? implode(',', $info['city']) . (" - " . @implode(',', @$info['area']) . "- ") . @implode(',', $info['country']) . (" - " . @implode(',', $info['code']) . " ") : null;
        $ul['country'] = (!empty(@$info['country'])) ? @implode(',', @$info['country']) : null;

        $ul['ip_address'] = UserSystemInfo::get_ip();
        $ul['browser'] = UserSystemInfo::get_browsers();
        $ul['os'] = UserSystemInfo::get_os();
        $ul['get_device'] = UserSystemInfo::get_device();

        UserLogin::create($ul);
        $this->sendWelcomeNotification($user);
    }


    public function sendWelcomeNotification($user)
    {
        $params = [
            'username' => $user->username,

        ];
        $action = [
            'link' => "#",
            'icon' => 'fa fa-money-bill-alt text-white'
        ];
        $firebaseAction = "#";
        $this->userPushNotification($user, 'USER_REGISTRATION', $params, $action);
        $this->userFirebasePushNotification($user, 'USER_REGISTRATION', $params, $firebaseAction);
        $this->sendMailSms($user, 'USER_REGISTRATION', $params);
    }

    public function logout()
    {

        $user = Auth::user();

        // Revoke all tokens...
        $user->tokens()->delete();

        return response()->json($this->withSuccess('Logged out successfully'));
    }

    public function getEmailForRecoverPass(Request $request)
    {
        $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
            ]);

        if ($validateUser->fails()) {
            return response()->json($this->withError(collect($validateUser->errors())->collapse()));
        }

        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json($this->withError('Email does not exit on record'));
            }

            $code = rand(10000, 99999);
            $data['email'] = $request->email;
            $data['message'] = 'OTP has been send';
            $user->verify_code = $code;
            $user->save();

            $basic = basicControl();
            $message = 'Your Password Recovery Code is ' . $code;
            $email_from = $basic->sender_email;
            @Mail::to($user)->queue(new SendMail($email_from, "Recovery Code", $message));

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function getCodeForRecoverPass(Request $request)
    {
        $validateUser = Validator::make($request->all(),
            [
                'code' => 'required',
                'email' => 'required|email',
            ]);

        if ($validateUser->fails()) {
            return response()->json($this->withError(collect($validateUser->errors())->collapse()));
        }

        DB::beginTransaction();
        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json($this->withError('Email does not exist on record'));
            }

            if ($user->verify_code == $request->code && $user->updated_at > \Carbon\Carbon::now()->subMinutes(5)) {
                $user->verify_code = null;
                $user->save();

                $token = Str::random(64);
                DB::table('password_resets')->insert([
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]);

                DB::commit();
                return response()->json($this->withSuccess(['token' => $token]));

            }

            return response()->json($this->withError('Invalid Code'));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function updatePass(Request $request)
    {
        $basic = basicControl();
        $rules = [
            'email' => 'required|email|exists:users,email',
            'password' => $basic->strong_password == 0 ?
                ['required', 'confirmed', 'min:6'] :
                ['required', 'confirmed', $this->strongPassword()],
            'password_confirmation' => 'required| min:6',
            'token' => 'required',
        ];
        $message = [
            'email.exists' => 'Email does not exist on record'
        ];

        $validateUser = Validator::make($request->all(), $rules,$message);
        if ($validateUser->fails()) {
            return response()->json($this->withError(collect($validateUser->errors())->collapse()));
        }

        $token = DB::table('password_resets')->where('token' , $request->token)->first();

        if (!$token) {
            return response()->json($this->withError('Invalid token'));
        }
        $expireTime = \Carbon\Carbon::now()->addMinutes(10);

        if ($token->created_at > $expireTime) {
            return response()->json($this->withError('Invalid token'));
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        DB::table('password_resets')->where('token', $request->token)->delete();
        return response()->json($this->withSuccess('Password Updated'));
    }

    public function deleteAccount()
    {
        if (config('demo.IS_DEMO') == true){
            return response()->json($this->withError("You can't delete demo account"));
        }
        $user = User::where('id',\auth()->id())->first();
        $user->delete();
        return response()->json($this->withSuccess('Account Deleted'));
    }
}
