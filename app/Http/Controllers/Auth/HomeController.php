<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $allPosts = DB::table('posts')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->select('posts.*', 'users.id as userId', 'users.fname', 'users.lname', 'users.email','users.profile_image_path')
            ->get();

        return view('index', [
            'searchedStatus' => false,
            'allPosts' => $allPosts,
            'userInfo' => Auth::user()
        ]);
    }

    public function searchUser(Request $request)
    {
        if ($request->search_text !== null) {
            $searchedUsers = User::when($request->search_text, function ($q) use ($request) {
                    return $q->where('username', $request->search_text)
                            ->orWhere('email', $request->search_text)
                            ->orWhere('fname', 'LIKE', '%' . $request->search_text . '%')
                            ->orWhere('lname', 'LIKE', '%' . $request->search_text . '%');
                })
                ->get()->toArray();
            
            return view('index', [
                'searchedStatus' => true,
                'searchedUsers' => $searchedUsers,
                'userInfo' => Auth::user()
            ]);
        } else {
            return $this->index();
        }
    }
}
