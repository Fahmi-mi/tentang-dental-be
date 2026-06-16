<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PromoResource;
use App\Models\Promo;
use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\UpdatePromoRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;

class PromoController extends Controller
{
    use FormatsApiResponse;

    public function index()
    {
        try {
            $promos = Promo::latest()->paginate(10);
            return $this->paginatedResourceResponse(
                $promos,
                ['promos' => PromoResource::collection($promos->getCollection())],
                'Data promo berhasil diambil'
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StorePromoRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $imageName = FileHelper::uploadImage($request->file('image'), 'promos');

            if (!$imageName) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Gagal upload gambar'),
                    500
                );
            }

            $promo = Promo::create([
                'name' => $request->name,
                'image' => $imageName,
                'detail' => $request->detail,
                'original_price' => $request->original_price,
                'promo_price' => $request->promo_price,
            ]);

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new PromoResource($promo), 'Promo berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($imageName)) {
                FileHelper::deleteImage('promos/' . $imageName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan promo: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $promo = Promo::find($id);

            if (!$promo) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Promo tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new PromoResource($promo), 'Detail promo berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdatePromoRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $promo = Promo::find($id);

            if (!$promo) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Promo tidak ditemukan'),
                    404
                );
            }

            $oldImage = $promo->image;

            if ($request->hasFile('image')) {
                $imageName = FileHelper::uploadImage($request->file('image'), 'promos');
                
                if (!$imageName) {
                    return response()->json(
                        FileHelper::formatResponse(false, null, 'Gagal upload gambar'),
                        500
                    );
                }

                $promo->image = $imageName;
            }

            if ($request->has('name')) $promo->name = $request->name;
            if ($request->has('detail')) $promo->detail = $request->detail;
            if ($request->has('original_price')) $promo->original_price = $request->original_price;
            if ($request->has('promo_price')) $promo->promo_price = $request->promo_price;

            $promo->save();

            if ($request->hasFile('image') && $oldImage) {
                FileHelper::deleteImage('promos/' . $oldImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new PromoResource($promo), 'Promo berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($imageName)) {
                FileHelper::deleteImage('promos/' . $imageName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate promo: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $promo = Promo::find($id);

            if (!$promo) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Promo tidak ditemukan'),
                    404
                );
            }

            $oldImage = $promo->image;

            $promo->delete();

            if ($oldImage) {
                FileHelper::deleteImage('promos/' . $oldImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Promo berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus promo: ' . $e->getMessage()),
                500
            );
        }
    }
}
