<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\GalleryResource;
use App\Models\Gallery;
use App\Http\Requests\StoreGalleryRequest;
use App\Http\Requests\UpdateGalleryRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;

class GalleryController extends Controller
{
    use FormatsApiResponse;

    public function index()
    {
        try {
            $galleries = Gallery::latest()->paginate(10);
            return $this->paginatedResourceResponse(
                $galleries,
                ['galleries' => GalleryResource::collection($galleries->getCollection())],
                'Data galeri berhasil diambil'
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreGalleryRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $imageName = FileHelper::uploadImage($request->file('image'), 'galleries');

            if (!$imageName) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Gagal upload gambar'),
                    500
                );
            }

            $gallery = Gallery::create([
                'image' => $imageName,
                'caption' => $request->caption,
            ]);

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new GalleryResource($gallery), 'Galeri berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($imageName)) {
                FileHelper::deleteImage('galleries/' . $imageName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan galeri: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $gallery = Gallery::find($id);

            if (!$gallery) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Galeri tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new GalleryResource($gallery), 'Detail galeri berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateGalleryRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $gallery = Gallery::find($id);

            if (!$gallery) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Galeri tidak ditemukan'),
                    404
                );
            }

            $oldImage = $gallery->image;

            if ($request->hasFile('image')) {
                $imageName = FileHelper::uploadImage($request->file('image'), 'galleries');
                if ($imageName) {
                    $gallery->image = $imageName;
                }
            }

            if ($request->has('caption')) {
                $gallery->caption = $request->caption;
            }

            $gallery->save();

            if ($request->hasFile('image') && $oldImage) {
                FileHelper::deleteImage('galleries/' . $oldImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new GalleryResource($gallery), 'Galeri berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate galeri: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $gallery = Gallery::find($id);

            if (!$gallery) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Galeri tidak ditemukan'),
                    404
                );
            }

            $oldImage = $gallery->image;

            $gallery->delete();

            if ($oldImage) {
                FileHelper::deleteImage('galleries/' . $oldImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Galeri berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus galeri: ' . $e->getMessage()),
                500
            );
        }
    }
}
