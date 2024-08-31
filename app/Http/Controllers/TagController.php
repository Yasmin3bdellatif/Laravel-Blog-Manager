<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{

    //View all tags
    public function index()
    {
        return response()->json(Tag::all(), 200);
    }

    //Store new tag
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:tags|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tag = Tag::create($request->all());
        return response()->json($tag, 201);
    }

    public function show(Tag $tag)
    {
        return response()->json($tag, 200);
    }

    //  Update a single tag
    public function update(Request $request, Tag $tag)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:tags,name,' . $tag->id . '|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tag->update($request->all());
        return response()->json($tag, 200);
    }

    // d. Delete a single tag
    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->json(['message' => 'Tag deleted successfully'], 200);
    }

}
