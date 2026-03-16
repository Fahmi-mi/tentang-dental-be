<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ServiceResource;
use App\Models\Service;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    use FormatsApiResponse;

    public function index()
    {
        try {
            $services = Service::latest()->paginate(10);
            return $this->paginatedResourceResponse(
                $services,
                'services',
                ServiceResource::collection($services->getCollection())->resolve(),
                'Data layanan berhasil diambil'
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreServiceRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $iconName = FileHelper::uploadImage($request->file('icon'), 'services');
            
            $supportImageName = FileHelper::uploadImage($request->file('support_image'), 'services');

            if (!$iconName || !$supportImageName) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Gagal upload gambar'),
                    500
                );
            }

            $service = Service::create([
                'name' => $request->name,
                'detail' => $request->detail,
                'icon' => $iconName,
                'article_content' => $request->article_content,
                'support_image' => $supportImageName,
            ]);

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new ServiceResource($service), 'Layanan berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($iconName)) FileHelper::deleteImage('services/' . $iconName);
            if (isset($supportImageName)) FileHelper::deleteImage('services/' . $supportImageName);

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan layanan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $service = Service::find($id);

            if (!$service) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Layanan tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new ServiceResource($service), 'Detail layanan berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateServiceRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $service = Service::find($id);

            if (!$service) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Layanan tidak ditemukan'),
                    404
                );
            }

            $oldIcon = $service->icon;
            $oldSupportImage = $service->support_image;

            if ($request->hasFile('icon')) {
                $iconName = FileHelper::uploadImage($request->file('icon'), 'services');
                if ($iconName) {
                    $service->icon = $iconName;
                }
            }

            if ($request->hasFile('support_image')) {
                $supportImageName = FileHelper::uploadImage($request->file('support_image'), 'services');
                if ($supportImageName) {
                    $service->support_image = $supportImageName;
                }
            }

            if ($request->has('name')) $service->name = $request->name;
            if ($request->has('detail')) $service->detail = $request->detail;
            if ($request->has('article_content')) $service->article_content = $request->article_content;

            $service->save();

            if ($request->hasFile('icon') && $oldIcon) {
                FileHelper::deleteImage('services/' . $oldIcon);
            }
            if ($request->hasFile('support_image') && $oldSupportImage) {
                FileHelper::deleteImage('services/' . $oldSupportImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new ServiceResource($service), 'Layanan berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate layanan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $service = Service::find($id);

            if (!$service) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Layanan tidak ditemukan'),
                    404
                );
            }

            $oldIcon = $service->icon;
            $oldSupportImage = $service->support_image;

            $service->delete();

            if ($oldIcon) FileHelper::deleteImage('services/' . $oldIcon);
            if ($oldSupportImage) FileHelper::deleteImage('services/' . $oldSupportImage);

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Layanan berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus layanan: ' . $e->getMessage()),
                500
            );
        }
    }
}
