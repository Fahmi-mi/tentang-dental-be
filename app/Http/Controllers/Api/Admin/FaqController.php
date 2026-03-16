<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\FormatsApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\FaqResource;
use App\Models\Faq;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Helpers\FileHelper;

class FaqController extends Controller
{
    use FormatsApiResponse;

    public function index()
    {
        try {
            $faqs = Faq::latest()->paginate(10);
            return $this->paginatedResourceResponse(
                $faqs,
                'faqs',
                FaqResource::collection($faqs->getCollection())->resolve(),
                'Data FAQ berhasil diambil'
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function store(StoreFaqRequest $request)
    {
        try {
            $faq = Faq::create([
                'question' => $request->question,
                'answer' => $request->answer,
            ]);

            return response()->json(
                FileHelper::formatResponse(true, new FaqResource($faq), 'FAQ berhasil ditambahkan'),
                201
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menambahkan FAQ: ' . $e->getMessage()),
                500
            );
        }
    }

    public function show($id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'FAQ tidak ditemukan'),
                    404
                );
            }

            return response()->json(
                FileHelper::formatResponse(true, new FaqResource($faq), 'Detail FAQ berhasil diambil'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Terjadi kesalahan: ' . $e->getMessage()),
                500
            );
        }
    }

    public function update(UpdateFaqRequest $request, $id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'FAQ tidak ditemukan'),
                    404
                );
            }

            if ($request->has('question')) {
                $faq->question = $request->question;
            }

            if ($request->has('answer')) {
                $faq->answer = $request->answer;
            }

            $faq->save();

            return response()->json(
                FileHelper::formatResponse(true, new FaqResource($faq), 'FAQ berhasil diupdate'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal mengupdate FAQ: ' . $e->getMessage()),
                500
            );
        }
    }

    public function destroy($id)
    {
        try {
            $faq = Faq::find($id);

            if (!$faq) {
                return response()->json(
                    FileHelper::formatResponse(false, null, 'FAQ tidak ditemukan'),
                    404
                );
            }

            $faq->delete();

            return response()->json(
                FileHelper::formatResponse(true, null, 'FAQ berhasil dihapus'),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                FileHelper::formatResponse(false, null, 'Gagal menghapus FAQ: ' . $e->getMessage()),
                500
            );
        }
    }
}
