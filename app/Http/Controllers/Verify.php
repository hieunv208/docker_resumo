<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class Verify extends Controller
{
    public function postVerify(Request $request) {
        $user = User::where('code', $request->code)->first();
        if ($user) {
            $user->active = 1;
            $user->code = null;
            $user->save();
        }
    }
}
