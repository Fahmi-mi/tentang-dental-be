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
            $query = Rontgen::with(['patient', 'doctor', 'primaryImage']);

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

            $doctorId = $request->doctor_id;
            if (!$doctorId) {
                $doctorId = $patient->reservations()->latest('reservation_date')->value('doctor_id');
            }

            if (!$doctorId) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Dokter belum tersedia untuk pasien ini. Silakan kirim doctor_id'),
                    422
                );
            }

            $rontgen = Rontgen::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => $doctorId,
                'detail' => $request->detail ?? null,
            ]);

            $storedImages = [];
            foreach ($request->file('images', []) as $imageFile) {
                $imageName = FileHelper::uploadImage($imageFile, 'rontgen');

                if (!$imageName) {
                    throw new \Exception('Gagal mengupload salah satu gambar pemeriksaan');
                }

                $storedImages[] = $imageName;

                $rontgen->examinationImages()->create([
                    'image_path' => $imageName,
                    'image_type' => (string) $imageFile->getClientMimeType(),
                ]);
            }

            if ($request->filled('tag_ids')) {
                $rontgen->tags()->sync($request->tag_ids);
            }

            $rontgen->load(['patient', 'doctor', 'primaryImage', 'tags']);
            $rontgen->setRelation('patient', $patient);

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new RontgenListResource($rontgen), 'Data rontgen berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($storedImages)) {
                $this->deleteRontgenImages($storedImages);
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
            $rontgen = Rontgen::with(['patient.medicalHistory', 'patient.dentalHistory', 'doctor', 'primaryImage', 'examinationImages', 'tags'])->find($id);

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
            $rontgen = Rontgen::with(['examinationImages', 'patient', 'doctor', 'tags'])->find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            if ($request->has('doctor_id')) {
                $rontgen->doctor_id = $request->doctor_id;
            }

            if ($request->hasFile('images')) {
                $oldImages = $rontgen->examinationImages->pluck('image_path')->filter()->values()->all();
                $newImages = [];

                foreach ($request->file('images', []) as $imageFile) {
                    $imageName = FileHelper::uploadImage($imageFile, 'rontgen');

                    if (!$imageName) {
                        throw new \Exception('Gagal mengupload salah satu gambar pemeriksaan');
                    }

                    $newImages[] = $imageName;
                }

                $rontgen->examinationImages()->delete();

                foreach ($newImages as $index => $imagePath) {
                    $mimeType = (string) $request->file('images')[$index]->getClientMimeType();
                    $rontgen->examinationImages()->create([
                        'image_path' => $imagePath,
                        'image_type' => $mimeType,
                    ]);
                }

                $this->deleteRontgenImages($oldImages);
            }

            if ($request->has('detail')) {
                $rontgen->detail = $request->detail;
            }

            if ($request->has('tag_ids')) {
                $rontgen->tags()->sync($request->tag_ids ?? []);
            }

            $rontgen->save();

            DB::commit();

            $rontgen->load(['patient', 'doctor', 'primaryImage', 'tags']);

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
            $rontgen = Rontgen::with('examinationImages')->find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $oldImages = $rontgen->examinationImages->pluck('image_path')->filter()->values()->all();

            $rontgen->delete();

            $this->deleteRontgenImages($oldImages);

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
            $rontgen = Rontgen::with('primaryImage')->find($id);

            if (!$rontgen) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Data rontgen tidak ditemukan'),
                    404
                );
            }

            $fileName = optional($rontgen->primaryImage)->image_path;

            if (!$fileName) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'File rontgen tidak ditemukan di storage'),
                    404
                );
            }

            $possiblePaths = [
                'rontgen/' . $fileName,
                'rontgens/' . $fileName,
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

    private function deleteRontgenImages(array $fileNames): void
    {
        foreach ($fileNames as $fileName) {
            FileHelper::deleteImage('rontgen/' . $fileName);
            FileHelper::deleteImage('rontgens/' . $fileName);
        }
    }
}
