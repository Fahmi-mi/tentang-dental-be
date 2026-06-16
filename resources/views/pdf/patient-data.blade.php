<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pasien</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        h2 { font-size: 14px; margin-top: 20px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 4px; border-bottom: 1px solid #eee; vertical-align: top; }
        td.label { width: 34%; font-weight: bold; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h1>Data Pasien</h1>
    <div class="muted">Tanggal cetak: {{ now()->format('d-m-Y H:i') }}</div>

    <h2>Profil Pasien</h2>
    <table>
        <tr><td class="label">Nama Lengkap</td><td>{{ $patient->name }}</td></tr>
        <tr><td class="label">Nama Panggilan</td><td>{{ $patient->nickname ?? '-' }}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td>{{ $patient->gender ?? '-' }}</td></tr>
        <tr><td class="label">Umur</td><td>{{ $patient->age ?? '-' }}</td></tr>
        <tr><td class="label">Tempat Lahir</td><td>{{ $patient->birth_place ?? '-' }}</td></tr>
        <tr><td class="label">Tanggal Lahir</td><td>{{ $patient->birth_date ? \Carbon\Carbon::parse($patient->birth_date)->format('d-m-Y') : '-' }}</td></tr>
        <tr><td class="label">Alamat</td><td>{{ $patient->address ?? '-' }}</td></tr>
        <tr><td class="label">Kelurahan</td><td>{{ $patient->village ?? '-' }}</td></tr>
        <tr><td class="label">Kecamatan</td><td>{{ $patient->district ?? '-' }}</td></tr>
        <tr><td class="label">Kota</td><td>{{ $patient->city ?? '-' }}</td></tr>
        <tr><td class="label">Nomor Telepon</td><td>{{ $patient->phone }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td>{{ $patient->occupation ?? '-' }}</td></tr>
        <tr><td class="label">Nama Orang Tua</td><td>{{ $patient->parent_name ?? '-' }}</td></tr>
        <tr><td class="label">Tinggi Badan</td><td>{{ $patient->height ?? '-' }}</td></tr>
        <tr><td class="label">Berat Badan</td><td>{{ $patient->weight ?? '-' }}</td></tr>
    </table>

    <h2>Riwayat Kesehatan Umum</h2>
    <table>
        <tr><td class="label">Memiliki Alergi</td><td>{{ optional($medicalHistory)->has_allergy ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Detail Alergi</td><td>{{ optional($medicalHistory)->allergy_detail ?? '-' }}</td></tr>
        <tr><td class="label">Penyakit Sistemik</td><td>{{ optional($medicalHistory)->has_systemic_disease ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Detail Penyakit Sistemik</td><td>{{ optional($medicalHistory)->systemic_disease_detail ?? '-' }}</td></tr>
        <tr><td class="label">Sedang Pengobatan</td><td>{{ optional($medicalHistory)->undergoing_treatment ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Detail Pengobatan</td><td>{{ optional($medicalHistory)->treatment_detail ?? '-' }}</td></tr>
        <tr><td class="label">Pernah Dirawat RS</td><td>{{ optional($medicalHistory)->ever_hospitalized ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Alasan Dirawat</td><td>{{ optional($medicalHistory)->hospitalized_reason ?? '-' }}</td></tr>
        <tr><td class="label">Merokok/Alkohol</td><td>{{ optional($medicalHistory)->smoking_or_alcohol ? 'Ya' : 'Tidak' }}</td></tr>
    </table>

    <h2>Riwayat Kesehatan Gigi</h2>
    <table>
        <tr><td class="label">Sering Sakit Gigi</td><td>{{ optional($dentalHistory)->frequent_tooth_pain ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Detail Sakit Gigi</td><td>{{ optional($dentalHistory)->tooth_pain_detail ?? '-' }}</td></tr>
        <tr><td class="label">Gusi Berdarah</td><td>{{ optional($dentalHistory)->bleeding_gums ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Pernah Perawatan Gigi</td><td>{{ optional($dentalHistory)->ever_dental_treatment ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Detail Perawatan Gigi</td><td>{{ optional($dentalHistory)->dental_treatment_detail ?? '-' }}</td></tr>
        <tr><td class="label">Frekuensi Sikat Gigi</td><td>{{ optional($dentalHistory)->brushing_frequency ?? '-' }}</td></tr>
        <tr><td class="label">Floss/Mouthwash</td><td>{{ optional($dentalHistory)->use_floss_or_mouthwash ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Kebiasaan Buruk</td><td>{{ optional($dentalHistory)->bad_habits ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Detail Kebiasaan Buruk</td><td>{{ optional($dentalHistory)->bad_habits_detail ?? '-' }}</td></tr>
        <tr><td class="label">Pernah Behel</td><td>{{ optional($dentalHistory)->ever_braces ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Lama Pakai Behel (tahun)</td><td>{{ optional($dentalHistory)->braces_years ?? '-' }}</td></tr>
        <tr><td class="label">Pernah PSA</td><td>{{ optional($dentalHistory)->root_canal_treatment ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Detail PSA</td><td>{{ optional($dentalHistory)->root_canal_detail ?? '-' }}</td></tr>
        <tr><td class="label">Memiliki Gigi Palsu</td><td>{{ optional($dentalHistory)->dentures ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Rutin Kontrol</td><td>{{ optional($dentalHistory)->routine_checkup ? 'Ya' : 'Tidak' }}</td></tr>
        <tr><td class="label">Frekuensi Kontrol</td><td>{{ optional($dentalHistory)->dental_checkup_frequency ?? '-' }}</td></tr>
        <tr><td class="label">Catatan Dokter</td><td>{{ optional($dentalHistory)->doctor_notes ?? '-' }}</td></tr>
    </table>
</body>
</html>
