<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email:dns,rfc',
            'mot_de_passe' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validation->errors()
            ], 400);
        }

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->mot_de_passe, $admin->mot_de_passe)) {
            return response()->json([
                'status' => 401,
                'message' => 'Les identifiants fournis sont incorrects'
            ], 401);
        }

        $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;
        $admin['role'] = 'admin';

        return response()->json([
            'status' => 200,
            'admin' => $admin,
            'token' => $token
        ]);
    }

    public function logout(int $id)
    {
        $admin = Admin::where('id', $id)->first();
        if ($admin) {
            $admin->tokens()->delete();
        }
    }
}
