<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Store a newly created resource in storage.
    */
    public function store(StorePostRequest $request)
    {
        if ($request->has('picture')) {
            $imageName = time() . '.' . $request->picture->getClientOriginalExtension();
            $request->picture->move(public_path('uploads/post_images'), 'post_images/' . $imageName);
            $data['filePath'] = 'post_images/' . $imageName;
        }

        $validatedData = $request->validated();

        $data['user_id'] = Auth::user()->id;
        $data['content'] = $validatedData['post_content'];
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $status = Post::create($data);

        if ($status) {
            return to_route('index');
        } else {
            return back();
        }
    }

    /**
     * Display the specified resource.
    */
    public function show(string $id)
    {
        $userInfo = Auth::user();

        $post = Post::with([
                'user:id,fname,lname,email,profile_image_path'
            ])
            ->where([
                'id' => $id
            ])
            ->first()->toArray();

        return view('post.view', [
            'post' => $post,
            'userInfo' => $userInfo
        ]);
    }

    /**
     * Show the form for editing the specified resource.
    */
    public function edit(string $id)
    {
        $userInfo = Auth::user();
        $post = Post::where('id', $id)->first();

        return view('post.edit', compact('post', 'userInfo'));
    }

    /**
     * Update the specified resource in storage.
    */
    public function update(UpdatePostRequest $request, string $id)
    {
        $validatedPost = $request->validated();
        $data = [];

        if (isset($validatedPost['picture'])) {
            $imageName = time() . '.' . $validatedPost['picture']->getClientOriginalExtension();
            $validatedPost['picture']->move(public_path('uploads/post_images'), 'post_images/' . $imageName);
            $data['filePath'] = 'post_images/' . $imageName;

            // delete previous image of this post from public directory
            $this->deleteImage($id);
        }

        $data['content'] = $validatedPost['post_content'];

        $status = Post::where('id', $id)->update($data);
        
        if ($status) {
            return to_route('posts.show', $id);
        } else {
            return back()->withErrors([
                'post_content' => 'Can\'t update! Soemthing went wrong!'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
    */
    public function destroy(string $id)
    {
        // delete the post's image from public directory
        $this->deleteImage($id);

        $status = Post::where([
                'id' => $id,
                'user_id' => Auth::user()->id
            ])
            ->delete();
        
        if ($status) {
            return to_route('index');
        }
    }

    public function searchUser(Request $request, string $id)
    {
        if (isset($request->search_text)) {

            $userInfo = Auth::user();

            $post = Post::with([
                    'user:id,fname,lname,email'
                ])
                ->where('id', $id)
                ->first()
                ->toArray();

            $searchedUsers = User::when($request->search_text, function ($q) use ($request) {
                    return $q->where('username', $request->search_text)
                            ->orWhere('email', $request->search_text)
                            ->orWhere('fname', 'LIKE', '%' . $request->search_text . '%')
                            ->orWhere('lname', 'LIKE', '%' . $request->search_text . '%');
                    })
                    ->get()
                    ->toArray();

            return view('post.view', compact('post', 'userInfo', 'searchedUsers'));
        } else {
            return $this->show($id);
        }
    }

    /**
     * Delete associated image of the post if exists
    */
    public function deleteImage(string $id)
    {
        $postImage = Post::select('filePath')->find($id);
        if (isset($postImage->filePath)) {
            $postImageName = explode('/', $postImage->filePath)[1];
            $postImagePath = public_path('uploads/post_images/' . $postImageName);

            if (file_exists($postImagePath)) {
                unlink($postImagePath);
            }
        }
    }
}
