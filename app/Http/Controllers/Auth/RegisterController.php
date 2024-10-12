<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserRegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['password'] = Hash::make($validatedData['password']);
        
        $data['fname'] = $validatedData['first_name'];
        $data['lname'] = $validatedData['last_name'];
        $data['email'] = $validatedData['email'];
        $data['password'] = $validatedData['password'];
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $status = User::create($data);

        if ($status) {
            return to_route('login');
        } else {
            return back();
        }
    }
}
