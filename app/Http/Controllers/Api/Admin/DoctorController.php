<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DoctorResource;
use App\Models\Doctor;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    use FormatsApiResponse;

    public function index()
    {
        try {
            $doctors = Doctor::latest()->paginate(10);
            return $this->paginatedResourceResponse(
                $doctors,
                'doctors',
                DoctorResource::collection($doctors->getCollection())->resolve(),
                'Data dokter berhasil diambil'
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreDoctorRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $photoName = FileHelper::uploadImage($request->file('photo'), 'doctors');

            if (!$photoName) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Gagal upload foto'),
                    500
                );
            }

            $doctor = Doctor::create([
                'name' => $request->name,
                'specialization' => $request->specialization,
                'photo' => $photoName,
                'schedule' => $request->schedule,
                'statement' => $request->statement,
            ]);

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new DoctorResource($doctor), 'Dokter berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($photoName)) {
                FileHelper::deleteImage('doctors/' . $photoName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan dokter: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $doctor = Doctor::find($id);

            if (!$doctor) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Dokter tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new DoctorResource($doctor), 'Detail dokter berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateDoctorRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $doctor = Doctor::find($id);

            if (!$doctor) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Dokter tidak ditemukan'),
                    404
                );
            }

            $oldPhoto = $doctor->photo;

            if ($request->hasFile('photo')) {
                $photoName = FileHelper::uploadImage($request->file('photo'), 'doctors');
                if ($photoName) {
                    $doctor->photo = $photoName;
                }
            }

            if ($request->has('name')) $doctor->name = $request->name;
            if ($request->has('specialization')) $doctor->specialization = $request->specialization;
            if ($request->has('schedule')) $doctor->schedule = $request->schedule;
            if ($request->has('statement')) $doctor->statement = $request->statement;

            $doctor->save();

            if ($request->hasFile('photo') && $oldPhoto) {
                FileHelper::deleteImage('doctors/' . $oldPhoto);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new DoctorResource($doctor), 'Dokter berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate dokter: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $doctor = Doctor::find($id);

            if (!$doctor) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Dokter tidak ditemukan'),
                    404
                );
            }

            $oldPhoto = $doctor->photo;

            $doctor->delete();

            if ($oldPhoto) {
                FileHelper::deleteImage('doctors/' . $oldPhoto);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Dokter berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus dokter: ' . $e->getMessage()),
                500
            );
        }
    }
}
