<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhysicalExamination;
use Illuminate\Http\Request;

class PhysicalExaminationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rontgen_id'     => 'required|exists:rontgen,id',
            'blood_pressure' => 'nullable|string',
            'height'         => 'nullable|integer',
            'weight'         => 'nullable|integer',
            'pulse'          => 'nullable|integer',
            'respiration'    => 'nullable|integer',
            'temperature'    => 'nullable|numeric',
        ]);

        // Upsert — kalau sudah ada update, kalau belum buat baru
        $exam = PhysicalExamination::updateOrCreate(
            ['rontgen_id' => $request->rontgen_id],
            [
                'blood_pressure' => $request->blood_pressure,
                'height'         => $request->height,
                'weight'         => $request->weight,
                'pulse'          => $request->pulse,
                'respiration'    => $request->respiration,
                'temperature'    => $request->temperature,
            ]
        );

        return response()->json([
            'success' => true,
            'data'    => $exam,
            'message' => 'Data pemeriksaan fisik berhasil disimpan',
        ], 201);
    }

    public function show($rontgenId)
    {
        $exam = PhysicalExamination::where('rontgen_id', $rontgenId)->first();

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