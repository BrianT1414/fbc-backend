<?php

namespace App\Http\Controllers;

use Auth;
use Storage;
use Illuminate\Http\Request;

class ErrorController extends Controller
{
	public function index()
	{
		if (Auth::check()) {
			return response()->json(json_decode(Storage::disk('public')->get('errors/js_errors.log')), 200);
		}

		return response()->json(['message' => 'You are not authorized to view this resource.'], 403);
	}

	public function store(Request $request)
	{
        $errorArray = [
            'date' => now(),
            'error' => $request->get('error'),
            'stack_trace' => $request->get('errorInfo')
        ];

        $errors = Storage::disk('public')->exists('errors/js_errors.log') ?
            json_decode(Storage::disk('public')->get('errors/js_errors.log')) :
            [];

        array_push($errors, $errorArray);

        Storage::disk('public')->put('errors/js_errors.log', json_encode($errors));
        
        return response()->json(['message' => 'Error logged.'], 200);
	}
}