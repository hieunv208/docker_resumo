<?php

namespace App\Http\Controllers;

use App\Mail\VerifyMail;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        $email = $request->email;
        // Step 1: Email validation 

        // Check email exits 
        $user = User::where('email', $email)->first();

        if ($user) {
            // SMS code gen 

            $sms_code = mt_rand(100000, 999999);
            //update code gen 
            $sms_update = User::where('email', $email)
                ->where('email', $email)
                ->update(['code' => $sms_code]);

            //send email 

            $data = [
                'sms_code' => $sms_code,
            ];
            Mail::to($email)->send(new VerifyMail($data));
            return response()->json([
                'message' => 'Operation successfully'
            ], 202);
        } else {
            return response()->json([
                'message' => 'User email not found'
            ], 404);
        }
    }

    public function postCode(Request $request)
    {
        $sms_code = $request->sms_code;

        //validate sms_code
        // #todo

        $user_code = User::where('code', $sms_code)->first();


        if ($user_code) {
            return response()->json([
                'message' => 'Operation sucessfully',
                'data' => $user_code,
                'token' => $user_code->createToken('myapptoken')->plainTextToken,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Bad credentials'
            ], 403);
        }
    }

    public function store(Request $request)
    {
        $field = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'user_type' => 'required',
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            return response()->json([
                'message' => 'Existed user. Login instead'
            ]);
        } else {
            $user_type = $field['user_type'];
            $p = User::create([
                'name' => $field['name'],
                'email' => $field['email'],
                'user_type' => $field['user_type'],
                'active' => 0,
            ]);
            $users = User::where('email', $email)->get();
            foreach ($users as $user) {
                $user = User::with('user_profile')->findOrFail($user->id);
            }
            if ($user_type == 0) {
                if ($user->user_profile === null) {
                    $field = $request->validate([
                        'menkyou_number' => 'required',
                        'ryouka' => 'required',
                        'workplace_name' => 'required',
                        'occupation' => 'required',
                    ]);
                    $infor = new UserInfo([
                        'menkyou_number' => $field['menkyou_number'],
                        'ryouka' => $field['ryouka'],
                        'workplace_name' => $field['workplace_name'],
                        'occupation' => $field['occupation'],
                    ]);
                    $user->user_profile()->save($infor);
                }

                return response()->json([
                    'message' => 'Operation sucessfully',
                    'data' => $p,
                    'token' => $p->createToken('myapptoken')->plainTextToken,
                ], 200);
            } else {
                if ($user->user_profile === null) {
                    $field = $request->validate([
                        'dob' => 'required',
                        'university_name' => 'required',
                        'year_graduated' => 'required',
                    ]);
                    $infor = new UserInfo([
                        'dob' => $field['dob'],
                        'university_name' => $field['university_name'],
                        'year_graduated' => $field['year_graduated'],
                    ]);
                    $user->user_profile()->save($infor);
                }

                return response()->json([
                    'message' => 'Operation sucessfully',
                    'data' => $p,
                    'token' => $p->createToken('myapptoken')->plainTextToken,
                ], 200);
            }
        }

    }
}
