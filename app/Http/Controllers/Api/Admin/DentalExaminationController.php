<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DentalExamination;
use App\Models\ExaminationImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DentalExaminationController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    //  STORE — simpan kunjungan baru beserta foto (jika ada)
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'rontgen_id'   => 'required|exists:rontgen,id',
            'visit_number' => 'required|integer|min:1',
            'visit_date'   => 'nullable|date',
            'subjective'   => 'nullable|string',
            'objective'    => 'nullable|string',
            'assessment'   => 'nullable|string',
            'planning'     => 'nullable|string',
            'treatment'    => 'nullable|string',
            'foto_before'  => 'nullable|image|max:10240',
            'foto_after'   => 'nullable|image|max:10240',
        ]);

        $exam = DentalExamination::create([
            'rontgen_id'   => $request->rontgen_id,
            'visit_number' => $request->visit_number,
            'visit_date'   => $request->visit_date,
            'subjective'   => $request->subjective,
            'objective'    => $request->objective,
            'assessment'   => $request->assessment,
            'planning'     => $request->planning,
            'treatment'    => $request->treatment,
            'created_at'   => now(),
        ]);

        if ($request->hasFile('foto_before')) {
            $imageName = \App\Helpers\FileHelper::uploadImage($request->file('foto_before'), 'rontgen');
            if ($imageName) {
                ExaminationImage::create([
                    'rontgen_id'            => $exam->rontgen_id,
                    'dental_examination_id' => $exam->id, // ← relasi langsung
                    'image_path'            => $imageName,
                    'image_type'            => 'dental',
                    'image_phase'           => 'before',
                ]);
            }
        }

        if ($request->hasFile('foto_after')) {
            $imageName = \App\Helpers\FileHelper::uploadImage($request->file('foto_after'), 'rontgen');
            if ($imageName) {
                ExaminationImage::create([
                    'rontgen_id'            => $exam->rontgen_id,
                    'dental_examination_id' => $exam->id, // ← relasi langsung
                    'image_path'            => $imageName,
                    'image_type'            => 'dental',
                    'image_phase'           => 'after',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $exam,
            'message' => 'Data lembar pemeriksaan gigi dan foto berhasil disimpan',
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  INDEX — ambil semua kunjungan + foto masing-masing via relasi langsung
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $request->validate([
            'rontgen_id' => 'required|exists:rontgen,id',
        ]);

        $exams = DentalExamination::where('rontgen_id', $request->rontgen_id)
            ->orderBy('visit_number')
            ->get();

        // Ambil foto per kunjungan berdasarkan dental_examination_id (bukan index array)
        $examsWithPhotos = $exams->map(function ($exam) {
            $before = ExaminationImage::where('dental_examination_id', $exam->id)
                ->where('image_phase', 'before')
                ->first();

            $after = ExaminationImage::where('dental_examination_id', $exam->id)
                ->where('image_phase', 'after')
                ->first();

            return [
                'id'              => $exam->id,
                'rontgen_id'      => $exam->rontgen_id,
                'visit_number'    => $exam->visit_number,
                'visit_date'      => $exam->visit_date,
                'subjective'      => $exam->subjective,
                'objective'       => $exam->objective,
                'assessment'      => $exam->assessment,
                'planning'        => $exam->planning,
                'treatment'       => $exam->treatment,
                'foto_before_url' => $before ? self::toImageUrl($before->image_path) : null,
                'foto_after_url'  => $after  ? self::toImageUrl($after->image_path)  : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $examsWithPhotos,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  UPDATE — update kunjungan + ganti foto jika dikirim foto baru
    // ─────────────────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'visit_date'  => 'nullable|date',
            'subjective'  => 'nullable|string',
            'objective'   => 'nullable|string',
            'assessment'  => 'nullable|string',
            'planning'    => 'nullable|string',
            'treatment'   => 'nullable|string',
            'foto_before' => 'nullable|image|max:10240',
            'foto_after'  => 'nullable|image|max:10240',
        ]);

        $exam = DentalExamination::findOrFail($id);

        $exam->update($request->only([
            'visit_date', 'subjective', 'objective',
            'assessment', 'planning', 'treatment',
        ]));

        if ($request->hasFile('foto_before')) {
            // Hapus foto lama dulu jika ada
            $oldBefore = ExaminationImage::where('dental_examination_id', $exam->id)
                ->where('image_phase', 'before')
                ->first();
            if ($oldBefore) {
                \App\Helpers\FileHelper::deleteImage('rontgen/' . $oldBefore->image_path);
                \App\Helpers\FileHelper::deleteImage('rontgens/' . $oldBefore->image_path);
                $oldBefore->delete();
            }

            $imageName = \App\Helpers\FileHelper::uploadImage($request->file('foto_before'), 'rontgen');
            if ($imageName) {
                ExaminationImage::create([
                    'rontgen_id'            => $exam->rontgen_id,
                    'dental_examination_id' => $exam->id, // ← relasi langsung
                    'image_path'            => $imageName,
                    'image_type'            => 'dental',
                    'image_phase'           => 'before',
                ]);
            }
        }

        if ($request->hasFile('foto_after')) {
            // Hapus foto lama dulu jika ada
            $oldAfter = ExaminationImage::where('dental_examination_id', $exam->id)
                ->where('image_phase', 'after')
                ->first();
            if ($oldAfter) {
                \App\Helpers\FileHelper::deleteImage('rontgen/' . $oldAfter->image_path);
                \App\Helpers\FileHelper::deleteImage('rontgens/' . $oldAfter->image_path);
                $oldAfter->delete();
            }

            $imageName = \App\Helpers\FileHelper::uploadImage($request->file('foto_after'), 'rontgen');
            if ($imageName) {
                ExaminationImage::create([
                    'rontgen_id'            => $exam->rontgen_id,
                    'dental_examination_id' => $exam->id, // ← relasi langsung
                    'image_path'            => $imageName,
                    'image_type'            => 'dental',
                    'image_phase'           => 'after',
                ]);
            }
        }

        // Kembalikan data beserta URL foto terbaru
        $before = ExaminationImage::where('dental_examination_id', $exam->id)
            ->where('image_phase', 'before')->first();
        $after = ExaminationImage::where('dental_examination_id', $exam->id)
            ->where('image_phase', 'after')->first();

        return response()->json([
            'success' => true,
            'data'    => array_merge($exam->toArray(), [
                'foto_before_url' => $before ? self::toImageUrl($before->image_path) : null,
                'foto_after_url'  => $after  ? self::toImageUrl($after->image_path)  : null,
            ]),
            'message' => 'Data berhasil diupdate',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helper — cek dua kemungkinan path storage (rontgen/ dan rontgens/)
    // ─────────────────────────────────────────────────────────────────────────
    private static function toImageUrl(?string $fileName): ?string
    {
        if (!$fileName) return null;

        if (Storage::disk('public')->exists('rontgen/' . $fileName)) {
            return asset('storage/rontgen/' . $fileName);
        }

        if (Storage::disk('public')->exists('rontgens/' . $fileName)) {
            return asset('storage/rontgens/' . $fileName);
        }

        return null;
    }
}