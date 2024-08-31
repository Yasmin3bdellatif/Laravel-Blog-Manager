<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    public function index()
    {
        $posts = auth()->user()->posts()->orderBy('pinned', 'desc')->get();
        return response()->json($posts, 200);
    }

    // Store new post
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => 'image',
            'pinned' => 'required|boolean',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('cover_images');
        }

        $post = auth()->user()->posts()->create($data);
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post, 201);
    }

    // View a single post
    public function show($id)
    {
        $post = auth()->user()->posts()->findOrFail($id);
        return response()->json($post, 200);
    }

    //  Update a single post
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => 'nullable|image',
            'pinned' => 'required|boolean',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post = auth()->user()->posts()->findOrFail($id);

        $data = $request->all();
        if ($request->hasFile('cover_image')) {
            // Delete old cover image if exists
            if ($post->cover_image) {
                Storage::delete($post->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('cover_images');
        }

        $post->update($data);
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post, 200);
    }

    // Delete softly a single post
    public function destroy($id)
    {
        $post = auth()->user()->posts()->findOrFail($id);
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully'], 200);
    }

    // View deleted posts
    public function trashed()
    {
        //dd(auth()->user()->posts()->onlyTrashed()->get());
     //   $posts = Post::withTrashed()->get();

        //dd(1213);
        // Retrieve only the trashed posts for the authenticated user
     /*   $trashedPosts = auth()->user()->posts()->onlyTrashed()->get();
     // dd($trashedPosts);

        if ($trashedPosts->isEmpty()) {
            return response()->json(['message' => 'No trashed posts found.'], 404);
        }

        return response()->json($posts, 200);*/

        $posts = Post::onlyTrashed()->get();

        if ($posts->isEmpty()) {
            return response()->json(['message' => 'No trashed posts found.'], 404);
        }

        // Return the posts as a JSON response or use a view
        return response()->json($posts);
    }


    //  Restore a deleted post
    public function restore($id)
    {
        // Find the trashed post by ID for the authenticated user
        $post = auth()->user()->posts()->onlyTrashed()->find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        // Restore the post
        $post->restore();

        return response()->json($post, 200);
    }


}
