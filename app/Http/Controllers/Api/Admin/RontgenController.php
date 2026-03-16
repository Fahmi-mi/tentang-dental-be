<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\RontgenDetailResource;
use App\Http\Resources\Admin\RontgenListResource;
use App\Http\Resources\Admin\RontgenUpdateResource;
use App\Models\Rontgen;
use App\Models\Patient;
use App\Http\Requests\StoreRontgenRequest;
use App\Http\Requests\UpdateRontgenRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class RontgenController extends Controller
{
    use FormatsApiResponse;

    public function index(Request $request)
    {
        try {
            $query = Rontgen::with('patient');

            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }

            $rontgens = $query->latest()->paginate(10);
            return $this->paginatedResourceResponse(
                $rontgens,
                ['rontgens' => RontgenListResource::collection($rontgens->getCollection())],
                'Data rontgen berhasil diambil'
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreRontgenRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $patient = Patient::find($request->patient_id);
            
            if (!$patient) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Pasien tidak ditemukan'),
                    404
                );
            }

            $imageName = FileHelper::uploadImage($request->file('xray_image'), 'rontgen');

            if (!$imageName) {
                throw new \Exception('Gagal mengupload gambar rontgen');
            }

            $rontgen = Rontgen::create([
                'patient_id' => $request->patient_id,
                'xray_image' => $imageName,
                'detail' => $request->detail ?? null,
            ]);
            $rontgen->setRelation('patient', $patient);

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new RontgenListResource($rontgen), 'Data rontgen berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($imageName)) {
                FileHelper::deleteImage('rontgen/' . $imageName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan rontgen: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $rontgen = Rontgen::with('patient.medicalHistory', 'patient.dentalHistory')->find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new RontgenDetailResource($rontgen), 'Detail rontgen berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateRontgenRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $rontgen = Rontgen::find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $oldImage = $rontgen->xray_image;

            if ($request->hasFile('xray_image')) {
                $imageName = FileHelper::uploadImage($request->file('xray_image'), 'rontgen');
                if ($imageName) {
                    $rontgen->xray_image = $imageName;
                }
            }

            if ($request->has('detail')) {
                $rontgen->detail = $request->detail;
            }

            $rontgen->save();

            if ($request->hasFile('xray_image') && $oldImage) {
                FileHelper::deleteImage('rontgen/' . $oldImage);
            }

            DB::commit();

            $rontgen->load('patient');

            return response()->json(
                FileHelper::formatResponse(true, new RontgenUpdateResource($rontgen), 'Data rontgen berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate rontgen: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $rontgen = Rontgen::find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $oldImage = $rontgen->xray_image;

            $rontgen->delete();

            if ($oldImage) {
                FileHelper::deleteImage('rontgen/' . $oldImage);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Data rontgen berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus rontgen: ' . $e->getMessage()),
                500
            );
        }
    }

    public function download($id)
    {
        try {
            $rontgen = Rontgen::find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $possiblePaths = [
                'rontgen/' . $rontgen->xray_image,
                'rontgens/' . $rontgen->xray_image,
            ];

            $path = null;
            foreach ($possiblePaths as $candidate) {
                if (Storage::disk('public')->exists($candidate)) {
                    $path = $candidate;
                    break;
                }
            }

            if (!$path) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'File rontgen tidak ditemukan di storage'),
                    404
                );
            }

            return response()->download(storage_path('app/public/' . $path), basename($path));
        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal download rontgen: ' . $e->getMessage()),
                500
            );
        }
    }
}
