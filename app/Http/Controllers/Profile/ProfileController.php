<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(string $id)
    {
        $userInfo = Auth::user();
        $profileViewerInfo = User::find($id);

        $allPostsByUser = Post::with([
                'user:id,fname,lname,email,profile_image_path'
            ])
            ->where('user_id', $profileViewerInfo->id)
            ->get()->toArray();
        
        return view('profile.view', compact('profileViewerInfo', 'userInfo', 'allPostsByUser'));
    }

    public function edit()
    {
        $userInfo = Auth::user();

        return view('profile.edit', ['userInfo' => $userInfo]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $userId = Auth::user()->id;
        $validatedData = $request->validated();

        if (isset($validatedData['avatar'])) {
            $profileImageName = time() . '.' . $validatedData['avatar']->getClientOriginalExtension();
            $validatedData['avatar']->move(public_path('uploads/profile_images'), $userId . '_' . $profileImageName);
            $data['profile_image_path'] = 'profile_images/' . $userId . '_' . $profileImageName;

            // delete previous profle image of this user from public directory
            $previousImage = User::select('profile_image_path')->find($userId);
            if (isset($previousImage->profile_image_path)) {
                $previousImageName = explode('/', $previousImage->profile_image_path)[1];
                $previousImagePath = public_path('uploads/profile_images/' . $previousImageName);

                if (file_exists($previousImagePath)) {
                    unlink($previousImagePath);
                }
            }
        }

        $data['fname'] = $validatedData['first_name'];
        $data['lname'] = $validatedData['last_name'];
        $data['email'] = $validatedData['email'];

        if (isset($validatedData['password'])) {
            $data['password'] = Hash::make($validatedData['password']);
        }

        if (isset($validatedData['bio'])) {
            $data['bio'] = $validatedData['bio'];
        }

        if (isset($validatedData['username'])) {
            $data['username'] = $validatedData['username'];
        }

        $status = User::where('id', $userId)
                    ->update($data);

        if ($status) {
            return to_route('profile.show', [
                'id' => $userId
            ]);
        } else {
            return back()->withErrors([
                'first_name' => 'Can\'t update! Something went wrong!'
            ]);
        }
    }

    public function searchUser(Request $request, string $id)
    {
        if (isset($request->search_text)) {

            $userInfo = Auth::user();
            $profileViewerInfo = User::find($id);

            $searchedUsers = User::when($request->search_text, function ($q) use ($request) {
                        return $q->where('username', $request->search_text)
                                ->orWhere('email', $request->search_text)
                                ->orWhere('fname', 'LIKE', '%' . $request->search_text . '%')
                                ->orWhere('lname', 'LIKE', '%' . $request->search_text . '%');
                    })
                    ->get()->toArray();

            $allPostsByUser = Post::with([
                        'user:id,fname,lname,email'
                    ])
                    ->where('user_id', $profileViewerInfo->id)
                    ->get()->toArray();

            return view('profile.view', [
                'userInfo' => $userInfo,
                'profileViewerInfo' => $profileViewerInfo,
                'searchedUsers' => $searchedUsers,
                'allPostsByUser' => $allPostsByUser
            ]);

        } else {
            return $this->show($id);
        }
    }
}
