<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Hash;

class AuthController extends Controller
{
    public function register(Request $request){
       // $request->validate([
      //      'name'=>'required|min:2|max:100',
      //      'email'=>'required|email|unique:users',
      //      'password'=>'required|min:6|max:100',
       //     'confirm_password'=>'required|same:password'
      //  ]);

        $validator = Validator::make($request->all(), [
            'first_name'=>'required|min:2|max:100',
            'last_name'=>'required|min:2|max:100',
            'middle_name'=>'min:2|max:100',
            'email'=>'required|email|unique:users',
            'phone'=>'required|min:11|max:11',
            'password'=>'required|min:6|max:100',
            'confirm_password'=>'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message'=>'Validations fails',
                'errors'=>$validator->errors()
            ],422);
        }

        $user = User::create([
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'middle_name'=>$request->middle_name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'password'=>Hash::make($request->password)
        ]);
        $user->save();
        return response()->json([
            'message'=>'Registration successfull',
            'data'=>$user
        ],200);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email'=>'required|email',
            'password'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message'=>'Validation fails',
                'errors'=>$validator->errors()
            ],422);
        }
        $user = User::where('email',$request->email)->first();
        if($user){
            if(Hash::check($request->password,$user->password)){
                $token=$user->createToken('auth-token')->plainTextToken;
                return response()->json([
                    'message'=>'Login successfull',
                    'token'=>$token,
                    'data'=>$user
                ],200);

            }else{
                return response()->json([
                    'message'=>'Incorrect credentials',
                ],400);
            }
        }else{
            return response()->json([
                'message'=>'Incorrect credentials',
            ],400);
        }

    }
}
