<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//import
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //atur validator
        $validator = Validator::make($request->all(),[
            'email'     => 'required',
            'password'  => 'required',
        ]);

        // kalau gagal validasi
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        //mendapat kredensial dari request
        $credentials = $request->only('email','password');

        //kalau autentikasi gagal
        if(!$token = auth()->guard('api')->attempt($credentials)){
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah',
            ],401);
        }

        //if auth success
        return response()->json([
            'success' => true,
            'user'    => auth()->guard('api')->user(),
            'token'   => $token
        ], 200);
    }
}
