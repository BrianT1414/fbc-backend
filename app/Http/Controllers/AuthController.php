<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
	public function checkUser()
	{
		if (Auth::check()) {
			return response()->json(Auth::user(), 200);
		}

		return response()->json(['message' => 'Not logged in'], 401);
	}

	public function login(Request $request)
	{
		Auth::attempt($request->only('email', 'password'));

		if (Auth::check()) {
			return response()->json(Auth::user(), 200);
		}

		return response()->json(['message' => 'Invalid credentials'], 400);
	}

	public function logout() {
		Auth::logout();
		return response()->json(['message' => 'Logged out'], 200);
	}
}