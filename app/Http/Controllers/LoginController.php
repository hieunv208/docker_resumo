<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostCodePhoneRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UserInfoGakkuseiRequest;
use App\Http\Requests\UserInfoRequest;
use App\Mail\VerifyMail;
use App\Models\User;
use App\Models\UserInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Validator;
use Twilio\Rest\Client;

class LoginController extends Controller
{
    public function emailLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return \response()->json([
                'message' => 'The email field is required'
            ]);
        } else {
            $email = $request->email;
            $user = User::where('email', $email)->count();
            if ($user > 0) {
//                 SMS code gen
                $sms_code = mt_rand(100000, 999999);
                //update code gen
                $sms_update = User::where('email', $email)->update(['code_sms' => $sms_code]);
                $details = [
                    'sms_code' => $sms_code,
                ];

                Mail::to($email)->send(new VerifyMail(['sms_code' => $sms_code]));
                return response()->json([
                    'message' => 'Operation successfully',
                    'status' => Response::HTTP_OK
                ], Response::HTTP_OK);


            } else {
                return response()->json([
                    'message' => 'Login by number phone instead',
                    'status' => Response::HTTP_ACCEPTED
                ], Response::HTTP_ACCEPTED);
            }
        }
    }

    public function postCodeEmail(Request $request)
    {
        $code_sms = $request->code_sms;

        $validator = Validator::make($request->all(), [
            'code_sms' => 'required'
        ]);

        if ($validator->fails()) {
            return \response()->json([
                'message' => 'Code SMS field is required'
            ]);
        } else {

            $user_code = User::where('code_sms', $code_sms)->first();
            if ($user_code) {
                $time = Carbon::now()->format("Y-m-d H:m:s");
                $user_code['last_login_time'] = $time;

                $data = [
                    "id" => $user_code['id'],
                    "name" => $user_code['name'],
                    "email" => $user_code['email'],
                    "phone_number" => $user_code['phone_number'],
                ];

                return response()->json([
                    'message' => 'Operation sucessfully',
                    'data' => $data,
                    'token' => $user_code->createToken('myapptoken')->plainTextToken,
                    'status' => Response::HTTP_CREATED

                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'message' => 'Bad credentials',
                    'status' => Response::HTTP_FORBIDDEN
                ],  Response::HTTP_FORBIDDEN);
            }

        }
    }

    public function store(Request $request)
    {
        $phone_number = $request->phone_number;
        $users = User::where('phone_number', $phone_number)->first();

        $LoginRequest = New RegisterRequest();
        $validator = Validator::make($request->all(), $LoginRequest->rules(), $LoginRequest->messages());

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user_type = $request->user_type;
        $user_name = $request->name;
        $email = $request->email;

        if ($user_type == 0) {
            $userInfoRequest = New UserInfoRequest();
            $validator = Validator::make($request->all(), $userInfoRequest->rules(), $userInfoRequest->messages());

            if ($validator->fails()) {
                return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } else {
            $LoginRequest = New UserInfoGakkuseiRequest();
            $validator = Validator::make($request->all(), $LoginRequest->rules(), $LoginRequest->messages());

            if ($validator->fails()) {
                return response()->json($validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        $time = Carbon::now()->format("Y-m-d H:m:s");
        $users->update([
            'name' => $user_name,
            'email' => $email,
            'user_type' => $user_type,
            'last_login_time' => $time

        ]);


        $user = User::with('user_profile')->findOrFail($users['id']);

        if ($user['user_type'] == 0) {
            if ($user->user_profile === null) {
                $menkyou_number = $request->menkyou_number;
                $ryouka = $request->ryouka;
                $workplace_name = $request->workplace_name;
                $occupation = $request->occupation;
                $infor = new UserInfo([
                    'menkyou_number' => $menkyou_number,
                    'ryouka' => $ryouka,
                    'workplace_name' => $workplace_name,
                    'occupation' => $occupation,
                ]);
                $user->user_profile()->save($infor);
            }

            return response()->json([
                'message' => 'Operation sucessfully',
                'data' => $user,
                'token' => $user->createToken('myapptoken')->plainTextToken,
                'status' => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);
        } else {
            if ($user->user_profile === null) {
                $dob = $request->dob;
                $university_name = $request->university_name;
                $year_graduated = $request->year_graduated;

                $infor = new UserInfo([
                    'dob' => $dob . ' ' . '00:00:00',
                    'university_name' => $university_name,
                    'year_graduated' => $year_graduated,
                ]);
                $user->user_profile()->save($infor);
            }

            return response()->json([
                'message' => 'Operation sucessfully',
                'data' => $user,
                'token' => $user->createToken('myapptoken')->plainTextToken,
                'status' => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);
        }
    }

    public function phoneLogin(Request $request)
    {
        $phone_number = $request->phone_number;
        $sms_code = mt_rand(100000, 999999);
        $usr = User::where('phone_number', $phone_number)->first();
        if ($usr) {
            $sms_update = User::where('email', $usr->email)
                ->update(['code_sms' => $sms_code]);
            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_number = getenv("TWILIO_NUMBER");
            $client = new Client($account_sid, $auth_token);
            $phonenumber = substr($phone_number, 1);
            $phone_format = "+84";
            $phone_format .= "$phonenumber";
            $body = "電話番号:" . $phone_format;
            $body .= "コード認証: " . $sms_code;
            $client->messages->create($phone_format,
                ['from' => $twilio_number, 'body' => $body]);
            return response()->json([
                'message' => 'Send code to verify',
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);

        } else {
            $user = new User();
            $user->phone_number = $phone_number;
            $user->code_sms = $sms_code;
            $user->save();
            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_number = getenv("TWILIO_NUMBER");
            $client = new Client($account_sid, $auth_token);
            $phonenumber = substr($phone_number, 1);
            $phone_format = "+84";
            $phone_format .= "$phonenumber";
            $body = "電話番号:" . $phone_format;
            $body .= "コード認証: " . $sms_code;
            $client->messages->create($phone_format,
                ['from' => $twilio_number, 'body' => $body]);

            return response()->json([
                'message' => 'Please register',
                'status' => Response::HTTP_ACCEPTED
            ], Response::HTTP_ACCEPTED);
        }
    }

    public function postCodePhone(Request $request)
    {
        $phone_number = $request->phone_number;
        $sms_code = $request->sms_code;
        $phoneVerifyRequest = New PostCodePhoneRequest();
        $validator = Validator::make($request->all(), $phoneVerifyRequest->rules(), $phoneVerifyRequest->messages());

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_PRECONDITION_REQUIRED);
        }
        $usr = User::where('phone_number', $phone_number)->where('code_sms', $sms_code)->first();

        if ($usr) {
            return response()->json([
                'message' => 'Operation sucessfully',
                'data' => $usr,
                'token' => $usr->createToken('myapptoken')->plainTextToken,
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'Wrong passcode, please try again',
                'status' => Response::HTTP_PRECONDITION_FAILED
            ], Response::HTTP_PRECONDITION_FAILED);
        }

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return \response()->json([
            'message' => 'Logout success'
        ]);
    }
}
