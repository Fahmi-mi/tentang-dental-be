<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all();

        return response()->json([
            'success' => true,
            'data'    => $tags,
        ]);
    }
}