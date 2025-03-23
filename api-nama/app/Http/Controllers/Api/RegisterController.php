<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//import model dan validator
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Lcobucci\JWT\Validation\Constraint\ValidAt;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // atur validasi
        $validator = Validator::make($request->all(), [
            'name'      =>'required',
            'email'     =>'required|email|unique:users',
            'password'  =>'required|min:8|confirmed',
        ]);

        //kalau validasi gagal
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        //create users
        $user=User::create([
            'name'      =>$request->name,
            'email'     =>$request->email,
            'password'  =>bcrypt($request->password)
        ]);

        //berikan respon json user dibuat
        if($user){
            return response()->json([
                'sucess'    => true,
                'user'      => $user,
            ],201);
        }

        //berikan respon json user gagal dibuat
        return response()->json([
            'sucess'    => false,
        ],409);

    }
}
