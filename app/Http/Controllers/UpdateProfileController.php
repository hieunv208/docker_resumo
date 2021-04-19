<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UpdateProfileController extends Controller
{
    public function updateMail(Request $request)
    {
        $new_email = $request->new_email;
        $user_exist = User::where('email', $new_email)->count();
        if ($user_exist > 0) {
            return response()->json(['message' => 'Update user failed']);
        } else {
            $user = User::find(auth()->user()->id);
            $user->email = $new_email;
            $user->save();
            return response()->json(['message' => 'Update user successfully',
                'data' => $new_email
            ]);
        }
    }

    public function updatePhone(Request $request)
    {
        $new_phone = $request->new_phone;
        $user_exist = User::where('phone_number', $new_phone)->count();
        if ($user_exist > 0) {
            return response()->json(['message' => 'Update user failed']);
        } else {
            $user = User::find(auth()->user()->id);
            $user->phone_number = $new_phone;
            $user->save();
            return response()->json(['message' => 'Update user phone successfully',
                'data' => $new_phone
            ]);
        }
    }
}
