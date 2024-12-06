<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Model User
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // Fungsi untuk Register User
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:siswa,admin'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
        
    }

    //Get User
    // Fungsi untuk mendapatkan semua user
public function getAllUsers()
{
    $users = User::all();

    return response()->json([
        'status' => true,
        'message' => 'Users retrieved successfully',
        'users' => $users
    ], 200);
}

// Fungsi untuk mendapatkan user berdasarkan ID
public function getUser($id)
{
    // Cari user berdasarkan ID
    $user = User::find($id);

    // Jika user tidak ditemukan
    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found'
        ], 404);
    }

    // Jika user ditemukan
    return response()->json([
        'status' => true,
        'message' => 'User retrieved successfully',
        'user' => $user
    ], 200);
}

    //Update User
    public function updateUser(Request $req, $id)
    {
        // Validasi input
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6', // Opsional, hanya jika ingin mengubah password
            'role' => 'required|in:siswa,admin',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 400);
        }
    
        // Cari user berdasarkan ID
        $user = User::find($id);
    
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
    
        // Update data user
        $user->name = $req->get('name');
        $user->email = $req->get('email');
        $user->role = $req->get('role');
    
        // Hash password jika ada
        if ($req->has('password')) {
            $user->password = Hash::make($req->get('password'));
        }
    
        // Simpan data
        try {
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    //Delete User
    public function deleteUser($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found'
        ], 404);
    }

    // Hapus user
    try {
        $user->delete();
        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to delete user',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // Fungsi untuk Login User
    public function login(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //get credentials from request
        $credentials = $request->only('email', 'password');

        //if auth failed
        if(!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
        }

        //if auth success
        return response()->json([
            'success' => true,
            'user'    => auth()->guard('api')->user(),    
            'token'   => $token   
        ], 200);
    }
    public function getAuthenticatedUser()
    {
        try {
            // Autentikasi token
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token tidak valid'], 401);
        }

        return response()->json(compact('user'));
    }
}