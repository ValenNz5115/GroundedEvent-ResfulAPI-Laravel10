<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\user;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


use JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{

    public function register(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,officer', // Check if the role is either 'admin' or 'officer'
        ], [
            'username.required' => 'Username is required!',
            'email.required' => 'Email is required!',
            'email.string' => 'Email is a letter/number!',
            'email.email' => 'Email format must match!',
            'email.unique' => 'Email already in use!',
            'password.required' => 'Password is required!',
            'password.string' => 'Password is a letter/number!',
            'password.min' => 'Password is at least 8 characters!',
            'role.required' => 'Employee role is required!',
            'role.in' => 'The role must be admin or officer!',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->toJson()]);
        }

        try {
            $user = User::create([
                'username' => $req->input('username'),
                'email' => $req->input('email'),
                'password' => Hash::make($req->input('password')),
                'role' => $req->input('role'),
            ]);

            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'Successfully updated a new user',
                    'data' => $user
                ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to add a new user', 'error' => $e->getMessage()]);
        }
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!Auth::attempt($credentials)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid email or password'], 401);
            }

            $user = Auth::user();
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json(['status' => 'error', 'message' => 'Could not create token'], 500);
        }

        return response()->json(['status' => 'success', 'message' => 'Login successful', 'token' => $token, 'user' => $user]);
    }

    public function getAuthenticatedUser()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            }

        } catch (TokenExpiredException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token invalid'], $e->getStatusCode());

        } catch (JWTException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token absent'], $e->getStatusCode());
        }

        return response()->json(['status' => 'success', 'data' => ['user' => $user]]);
    }

    public function logout()
    {
        try {
            // Invalidate the current token
            JWTAuth::invalidate(JWTAuth::getToken());

            // Log the user out of the application
            Auth::logout();

            return response()->json(['status' => 'success', 'message' => 'Logout successful']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to logout', 'error' => $e->getMessage()]);
        }
    }


}
