<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtraOralExamination;
use Illuminate\Http\Request;

class ExtraOralExaminationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rontgen_id'              => 'required|exists:rontgen,id',
            'face'                    => 'nullable|in:symmetric,asymmetric',
            'facial_skin_neck'        => 'nullable|in:normal,abnormal',
            'lymph_nodes'             => 'nullable|in:palpable,not_palpable',
            'temporomandibular_joint' => 'nullable|in:normal,abnormal',
            'muscle_mass'             => 'nullable|in:normal,abnormal',
            'facial_swelling'         => 'nullable|in:present,absent',
            'eyes_nose'               => 'nullable|in:normal,abnormal',
        ]);

        $exam = ExtraOralExamination::updateOrCreate(
            ['rontgen_id' => $request->rontgen_id],
            $request->only([
                'face', 'facial_skin_neck', 'lymph_nodes',
                'temporomandibular_joint', 'muscle_mass',
                'facial_swelling', 'eyes_nose',
            ])
        );

        return response()->json([
            'success' => true,
            'data'    => $exam,
            'message' => 'Data pemeriksaan extra oral berhasil disimpan',
        ], 201);
    }

    public function show($rontgenId)
    {
        $exam = ExtraOralExamination::where('rontgen_id', $rontgenId)->first();

        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $exam,
        ]);
    }
}