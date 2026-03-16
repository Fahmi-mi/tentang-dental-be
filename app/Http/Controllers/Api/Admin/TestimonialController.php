<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TestimonialResource;
use App\Models\Testimonial;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;

class TestimonialController extends Controller
{
    use FormatsApiResponse;

    public function index()
    {
        try {
            $testimonials = Testimonial::latest()->paginate(10);
            return $this->paginatedResourceResponse(
                $testimonials,
                ['testimonials' => TestimonialResource::collection($testimonials->getCollection())],
                'Data testimoni berhasil diambil'
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreTestimonialRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $photoName = null;

            if ($request->hasFile('photo')) {
                $photoName = FileHelper::uploadImage($request->file('photo'), 'testimonials');
            }

            $testimonial = Testimonial::create([
                'name' => $request->name,
                'rating' => $request->rating,
                'testimoni' => $request->testimoni,
                'photo' => $photoName,
            ]);

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new TestimonialResource($testimonial), 'Testimoni berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($photoName)) {
                FileHelper::deleteImage('testimonials/' . $photoName);
            }

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan testimoni: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $testimonial = Testimonial::find($id);

            if (!$testimonial) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Testimoni tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new TestimonialResource($testimonial), 'Detail testimoni berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateTestimonialRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $testimonial = Testimonial::find($id);

            if (!$testimonial) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Testimoni tidak ditemukan'),
                    404
                );
            }

            $oldPhoto = $testimonial->photo;

            if ($request->hasFile('photo')) {
                $photoName = FileHelper::uploadImage($request->file('photo'), 'testimonials');
                if ($photoName) {
                    $testimonial->photo = $photoName;
                }
            }

            if ($request->has('name')) $testimonial->name = $request->name;
            if ($request->has('rating')) $testimonial->rating = $request->rating;
            if ($request->has('testimoni')) $testimonial->testimoni = $request->testimoni;

            $testimonial->save();

            if ($request->hasFile('photo') && $oldPhoto) {
                FileHelper::deleteImage('testimonials/' . $oldPhoto);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, new TestimonialResource($testimonial), 'Testimoni berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate testimoni: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $testimonial = Testimonial::find($id);

            if (!$testimonial) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'Testimoni tidak ditemukan'),
                    404
                );
            }

            $oldPhoto = $testimonial->photo;

            $testimonial->delete();

            if ($oldPhoto) {
                FileHelper::deleteImage('testimonials/' . $oldPhoto);
            }

            DB::commit();

            return response()->json(
                FileHelper::formatResponse(true, null, 'Testimoni berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus testimoni: ' . $e->getMessage()),
                500
            );
        }
    }
}
