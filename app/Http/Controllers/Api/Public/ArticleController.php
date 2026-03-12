<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        try {
            $articles = Article::with('admin:id,name')
                ->select('id', 'admin_id', 'title', 'slug', 'image', 'created_at')
                ->latest()
                ->paginate(10);

            $data = [
                'articles' => $articles->map(function ($article) {
                    return [
                        'id' => $article->id,
                        'title' => $article->title,
                        'slug' => $article->slug,
                        'image_url' => $article->image ? asset('storage/articles/' . $article->image) : null,
                        'writer' => $article->admin ? $article->admin->name : null,
                        'published_at' => $article->created_at->format('d M Y'),
                        'published_at_full' => $article->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                ],
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Data artikel berhasil diambil'),
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }
}
