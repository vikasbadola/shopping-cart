<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\ApiBaseController as ApiBaseController;

class AuthController extends ApiBaseController {

    /**
     * Handles Registration Request
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', ['error'=>$validator->errors()->all()],400);  
        }
        $request['password'] = Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $token = $user->createToken('Vikas Badola Test Case')->accessToken;
        $response = ['token' => $token];
        return $this->sendResponse($response, 'User register successfully.');
    }

    /**
     * Handles Login Request
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request) {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Vikas Badola Test Case')->accessToken;
                $response = ['token' => $token];
                return $this->sendResponse($response, 'User login successfully.');
            } else {
                $response = ["message" => "Password mismatch"];
                return $this->sendError('Password mismatch.',['error'=>'Password does not match.'],403);
            }
        } else {
            return $this->sendError('User does not exist.',['error'=>'User does not exist.'],404);
        }
    }

}
