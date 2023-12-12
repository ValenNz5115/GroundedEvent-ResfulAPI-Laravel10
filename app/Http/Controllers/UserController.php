<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{

    public function register(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required'
        ], [
                'name.required' => 'Nama Pegawai Dibutuhkan !',
                'eamil.required' => 'Email Pegawai Dibutuhkan !',
                'email.string' => 'Email Berupa Huruf / Angka !',
                'email.email' => 'Format Email Harus Sesuai !',
                'email.unique' => 'Email Sudah Digunakan !',
                'password.required' => 'Password Pegawai Dibutuhkan !',
                'password.string' => 'Password Berupa Huruf / Angka !',
                'password.min' => 'Password Minimal 6 Karakter !',
            ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->toJson()]);
        }
        $user = User::create([
            'name' => $req->get('name'),
            'email' => $req->get('email'),
            'password' => Hash::make($req->get('password')),
            'role' => $req->get('role'),
        ]);
        if ($user) {
            return response()->json(['status' => 'success', 'message' => 'Sukses Tambah Pegawai',]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Gagal Tambah Pegawai',]);
        }
    }

    public function login(req $req)
    {
        $credentials = $req->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['status' => 'error', 'message' => 'Email / Password Salah'], );
            }
        } catch (JWTException $e) {
            return response()->json(['status' => 'error', 'message' => 'could_not_create_token']);
        }
        return response()->json(['status' => 'success', 'message' => 'Sukses Login', 'token' => $token]);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return \Response::json(['status' => 'error', 'message' => 'user_not_found']);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return \Response::json(['status' => 'error', 'message' => 'token_expired']);
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return \Response::json(['status' => 'error', 'message' => 'token_invalid']);
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return \Response::json(['status' => 'error', 'message' => 'token_absent'], );
        }
        return \Response::json(['status' => 'success', 'user' => $user]);
    }



}
