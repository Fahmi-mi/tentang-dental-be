<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ArticleResource;
use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    use FormatsApiResponse;

    public function index()
    {
        try {
            $articles = Article::with('admin:id,name')
                ->latest()
                ->paginate(10);
            return $this->paginatedResourceResponse(
                $articles,
                'articles',
                ArticleResource::collection($articles->getCollection())->resolve(),
                'Data artikel berhasil diambil'
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreArticleRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $imageName = FileHelper::uploadImage($request->file('image'), 'articles');

            if (!$imageName) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Gagal upload gambar'),
                    500
                );
            }

            $slug = FileHelper::generateSlug($request->title);
            
            $originalSlug = $slug;
            $counter = 1;
            while (Article::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $article = Article::create([
                'admin_id' => $request->user()->id,
                'title' => $request->title,
                'slug' => $slug,
                'content' => $request->content,
                'image' => $imageName,
            ]);
            $article->load('admin:id,name');

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new ArticleResource($article), 'Artikel berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($imageName)) {
                FileHelper::deleteImage('articles/' . $imageName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan artikel: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $article = Article::with('admin:id,name')->find($id);

            if (!$article) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Artikel tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new ArticleResource($article), 'Detail artikel berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateArticleRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $article = Article::find($id);

            if (!$article) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Artikel tidak ditemukan'),
                    404
                );
            }

            $oldImage = $article->image;

            if ($request->hasFile('image')) {
                $imageName = FileHelper::uploadImage($request->file('image'), 'articles');
                if ($imageName) {
                    $article->image = $imageName;
                }
            }

            if ($request->has('title') && $request->title != $article->title) {
                $article->title = $request->title;
                
                $slug = FileHelper::generateSlug($request->title);
                $originalSlug = $slug;
                $counter = 1;
                while (Article::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                $article->slug = $slug;
            }

            if ($request->has('content')) {
                $article->content = $request->content;
            }

            $article->save();

            if ($request->hasFile('image') && $oldImage) {
                FileHelper::deleteImage('articles/' . $oldImage);
            }

            $article->load('admin:id,name');

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new ArticleResource($article), 'Artikel berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate artikel: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $article = Article::find($id);

            if (!$article) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Artikel tidak ditemukan'),
                    404
                );
            }

            $oldImage = $article->image;

            $article->delete();

            if ($oldImage) {
                FileHelper::deleteImage('articles/' . $oldImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Artikel berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus artikel: ' . $e->getMessage()),
                500
            );
        }
    }
}
