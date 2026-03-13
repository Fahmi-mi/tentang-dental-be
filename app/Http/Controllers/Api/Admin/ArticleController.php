<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        try {
            $articles = Article::with('admin:id,name')
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
                        'created_at' => $article->created_at->format('Y-m-d H:i:s'),
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

            DB::commit();

            $data = [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'image_url' => asset('storage/articles/' . $article->image),
                'writer' => $request->user()->name,
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Artikel berhasil ditambahkan'),
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

            $data = [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'image_url' => $article->image ? asset('storage/articles/' . $article->image) : null,
                'writer' => $article->admin ? $article->admin->name : null,
                'created_at' => $article->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $article->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Detail artikel berhasil diambil'),
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

            DB::commit();

            $data = [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'image_url' => asset('storage/articles/' . $article->image),
            ];

            return response()->json(
                FileHelper::formatResponse(true, $data, 'Artikel berhasil diupdate'),
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
