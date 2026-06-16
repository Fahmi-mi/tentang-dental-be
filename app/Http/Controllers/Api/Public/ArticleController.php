<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\FileHelper;
use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\ArticleDetailResource;
use App\Http\Resources\Public\ArticleListResource;
use App\Models\Article;

class ArticleController extends Controller
{
    use FormatsApiResponse;

    public function index()
    {
        try {
            $articles = Article::with('admin:id,name')
                ->select('id', 'admin_id', 'title', 'slug', 'image', 'created_at')
                ->latest()
                ->paginate(10);

            return $this->paginatedResourceResponse(
                $articles,
                ['articles' => ArticleListResource::collection($articles->getCollection())],
                'Data artikel berhasil diambil'
            );
        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($slug)
    {
        try {
            $article = Article::with('admin:id,name')
                ->where('slug', $slug)
                ->first();

            if (!$article) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Artikel tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new ArticleDetailResource($article), 'Detail artikel berhasil diambil'),
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
