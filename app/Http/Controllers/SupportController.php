<?php

namespace App\Http\Controllers;

use App\Models\Support;
use App\Models\User;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function postSupport(Request $request)
    {
        $content_body = $request->content_support;

        $validator = Validator::make($request->all(), [
            'content_support' => 'required'
        ]);

        if ($validator->fails()) {
            return \response()->json([
                'message' => 'The content field is required'
            ]);
        } else {
            $user_id = $request->user()->id;
            $user = User::with('support')->findOrFail($user_id);
            $support = new Support([
                'content_support' => $content_body,
            ]);
            $user->support()->save($support);
            return response()->json([
                'message' => 'Thanks for your information'
            ]);
        }

    }
}
