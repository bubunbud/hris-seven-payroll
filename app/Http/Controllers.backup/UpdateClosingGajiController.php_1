<?php

namespace App\Http\Controllers;

use App\Models\Closing;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Gapok;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateClosingGajiController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('periode_dari', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('periode_sampai', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $nik = $request->get('nik');
        $divisi = $request->get('divisi');

        $query = Closing::with(['karyawan', 'divisi', 'gapok'])
            ->whereBetween('periode', [$startDate, $endDate])
            ->orderBy('periode', 'desc')
            ->orderBy('vcNik');

        if ($nik) {
            $query->where('vcNik', 'like', '%' . $nik . '%');
        }

        if ($divisi && $divisi != 'SEMUA') {
            $query->where('vcKodeDivisi', $divisi);
        }

        $records = $query->paginate(25);
        $divisis = Divisi::orderBy('vcKodeDivisi')->get();

        return view('proses.update-closing-gaji.index', compact('records', 'divisis', 'startDate', 'endDate', 'nik', 'divisi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vcPeriodeAwal' => 'required|date',
            'vcPeriodeAkhir' => 'required|date|after_or_equal:vcPeriodeAwal',
            'vcNik' => 'required|string|max:8|exists:m_karyawan,Nik',
            'periode' => 'required|date',
            'vcClosingKe' => 'required|in:1,2',
            'jumlahHari' => 'nullable|integer|min:0',
            'vcKodeGolongan' => 'nullable|string|max:10',
            'vcKodeDivisi' => 'nullable|string|max:10',
            'vcStatusPegawai' => 'nullable|string|max:20',
            'decGapok' => 'nullable|numeric|min:0',
            'decJamKerja' => 'nullable|numeric|min:0',
            'decPotonganHC' => 'nullable|numeric|min:0',
            'decPotonganBPR' => 'nullable|numeric|min:0',
            'decIuranSPN' => 'nullable|numeric|min:0',
            'decPotonganBPJSKes' => 'nullable|numeric|min:0',
            'decPotonganBPJSJHT' => 'nullable|numeric|min:0',
            'decPotonganBPJSJP' => 'nullable|numeric|min:0',
            'decPotonganKoperasi' => 'nullable|numeric|min:0',
            'decPotonganAbsen' => 'nullable|numeric|min:0',
            'decPotonganLain' => 'nullable|numeric|min:0',
            'decVarMakan' => 'nullable|numeric|min:0',
            'decVarTransport' => 'nullable|numeric|min:0',
            'decRapel' => 'nullable|numeric',
            'decUangMakan' => 'nullable|numeric|min:0',
            'decTransport' => 'nullable|numeric|min:0',
            'intMakan' => 'nullable|integer|min:0',
            'intTransport' => 'nullable|integer|min:0',
            'intHC' => 'nullable|integer|min:0',
            'intKHL' => 'nullable|integer|min:0',
            'intHadir' => 'nullable|integer|min:0',
            'intTidakMasuk' => 'nullable|integer|min:0',
            'intJumlahHari' => 'nullable|integer|min:0',
            'intJmlSakit' => 'nullable|integer|min:0',
            'intJmlAlpha' => 'nullable|integer|min:0',
            'intJmlIzin' => 'nullable|integer|min:0',
            'intJmlIzinR' => 'nullable|integer|min:0',
            'intJmlCuti' => 'nullable|integer|min:0',
            'intJmlTelat' => 'nullable|integer|min:0',
            'decPremi' => 'nullable|numeric|min:0',
            'decJamLemburKerja1' => 'nullable|numeric|min:0',
            'decJamLemburKerja2' => 'nullable|numeric|min:0',
            'decJamLemburKerja3' => 'nullable|numeric|min:0',
            'decLemburKerja1' => 'nullable|numeric|min:0',
            'decLemburKerja2' => 'nullable|numeric|min:0',
            'decLemburKerja3' => 'nullable|numeric|min:0',
            'decJamLemburLibur2' => 'nullable|numeric|min:0',
            'decJamLemburLibur3' => 'nullable|numeric|min:0',
            'decLembur2' => 'nullable|numeric|min:0',
            'decLembur3' => 'nullable|numeric|min:0',
            'decJamLemburKerja' => 'nullable|numeric|min:0',
            'decJamLemburLibur' => 'nullable|numeric|min:0',
            'decTotallembur1' => 'nullable|numeric|min:0',
            'decTotallembur2' => 'nullable|numeric|min:0',
            'decTotallembur3' => 'nullable|numeric|min:0',
            'intCutiLalu' => 'nullable|integer|min:0',
            'intSakitLalu' => 'nullable|integer|min:0',
            'intHcLalu' => 'nullable|integer|min:0',
            'intIzinLalu' => 'nullable|integer|min:0',
            'intAlphaLalu' => 'nullable|integer|min:0',
            'intTelatLalu' => 'nullable|integer|min:0',
            'intMakanKerja' => 'nullable|integer|min:0',
            'intMakanLibur' => 'nullable|integer|min:0',
            'intTransportKerja' => 'nullable|integer|min:0',
            'intTransportLibur' => 'nullable|integer|min:0',
            'decBpjsKesehatan' => 'nullable|numeric|min:0',
            'decBpjsNaker' => 'nullable|numeric|min:0',
            'decBpjsPensiun' => 'nullable|numeric|min:0',
            'decBebanTgi' => 'nullable|numeric|min:0',
            'decBebanSiaExp' => 'nullable|numeric|min:0',
            'decBebanSiaProd' => 'nullable|numeric|min:0',
            'decBebanRma' => 'nullable|numeric|min:0',
            'decBebanSmu' => 'nullable|numeric|min:0',
            'decBebanAbnJkt' => 'nullable|numeric|min:0',
        ]);

        // Cek apakah sudah ada dengan composite key yang sama
        $exists = Closing::where('vcPeriodeAwal', $request->vcPeriodeAwal)
            ->where('vcPeriodeAkhir', $request->vcPeriodeAkhir)
            ->where('vcNik', $request->vcNik)
            ->where('periode', $request->periode)
            ->where('vcClosingKe', $request->vcClosingKe)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data closing dengan periode dan NIK yang sama sudah ada'
            ], 422);
        }

        // Ambil data karyawan untuk auto-fill
        $karyawan = Karyawan::find($request->vcNik);
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 422);
        }

        // Auto-fill jika kosong
        $data = $request->all();
        if (empty($data['vcKodeGolongan'])) {
            $data['vcKodeGolongan'] = $karyawan->Gol;
        }
        if (empty($data['vcKodeDivisi'])) {
            $data['vcKodeDivisi'] = $karyawan->Divisi;
        }
        if (empty($data['vcStatusPegawai'])) {
            $data['vcStatusPegawai'] = $karyawan->Status_Pegawai;
        }
        $data['dtCreate'] = Carbon::now();
        $data['dtChange'] = Carbon::now();

        // Set default 0 untuk numeric fields yang kosong
        $numericFields = [
            'jumlahHari',
            'decGapok',
            'decJamKerja',
            'decPotonganHC',
            'decPotonganBPR',
            'decIuranSPN',
            'decPotonganBPJSKes',
            'decPotonganBPJSJHT',
            'decPotonganBPJSJP',
            'decPotonganKoperasi',
            'decPotonganAbsen',
            'decPotonganLain',
            'decVarMakan',
            'decVarTransport',
            'decRapel',
            'decUangMakan',
            'decTransport',
            'intMakan',
            'intTransport',
            'intHC',
            'intKHL',
            'intHadir',
            'intTidakMasuk',
            'intJumlahHari',
            'intJmlSakit',
            'intJmlAlpha',
            'intJmlIzin',
            'intJmlIzinR',
            'intJmlCuti',
            'intJmlTelat',
            'decPremi',
            'decJamLemburKerja1',
            'decJamLemburKerja2',
            'decJamLemburKerja3',
            'decLemburKerja1',
            'decLemburKerja2',
            'decLemburKerja3',
            'decJamLemburLibur2',
            'decJamLemburLibur3',
            'decLembur2',
            'decLembur3',
            'decJamLemburKerja',
            'decJamLemburLibur',
            'decTotallembur1',
            'decTotallembur2',
            'decTotallembur3',
            'intCutiLalu',
            'intSakitLalu',
            'intHcLalu',
            'intIzinLalu',
            'intAlphaLalu',
            'intTelatLalu',
            'intMakanKerja',
            'intMakanLibur',
            'intTransportKerja',
            'intTransportLibur',
            'decBpjsKesehatan',
            'decBpjsNaker',
            'decBpjsPensiun',
            'decBebanTgi',
            'decBebanSiaExp',
            'decBebanSiaProd',
            'decBebanRma',
            'decBebanSmu',
            'decBebanAbnJkt'
        ];

        foreach ($numericFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $data[$field] = 0;
            }
        }

        // Jika closing ke-2, copy data absensi P1 dari closing ke-1 ke field int*Lalu
        if (($request->vcClosingKe ?? '') == '2') {
            // Tentukan periode P1 berdasarkan periode gajian
            $periode = Carbon::parse($request->periode);
            $periodeP1 = null;
            if ($periode->day == 15) {
                // Periode gajian 15, cari closing P1 dengan periode tanggal 1 di bulan yang sama
                $periodeP1 = $periode->copy()->day(1)->format('Y-m-d');
            } else {
                // Periode gajian 1, cari closing P1 dengan periode tanggal 15 di bulan sebelumnya
                $periodeP1 = $periode->copy()->subMonth()->day(15)->format('Y-m-d');
            }

            $closingP1 = Closing::where('vcNik', $request->vcNik)
                ->where('periode', $periodeP1)
                ->where('vcClosingKe', '1')
                ->where('vcKodeDivisi', $request->vcKodeDivisi)
                ->first();

            if ($closingP1) {
                // Copy data absensi P1 ke field int*Lalu
                $data['intCutiLalu'] = $closingP1->intJmlCuti ?? 0;
                $data['intSakitLalu'] = $closingP1->intJmlSakit ?? 0;
                $data['intHcLalu'] = $closingP1->intHC ?? 0;
                $data['intIzinLalu'] = $closingP1->intJmlIzin ?? 0;
                $data['intAlphaLalu'] = $closingP1->intJmlAlpha ?? 0;
                $data['intTelatLalu'] = $closingP1->intJmlTelat ?? 0;
            }
        }

        DB::table('t_closing')->insert($data);

        return response()->json(['success' => true, 'message' => 'Data Closing Gaji berhasil ditambahkan']);
    }

    public function show(string $id)
    {
        // Decode composite key: vcPeriodeAwal|vcPeriodeAkhir|vcNik|periode|vcClosingKe
        $parts = explode('|', base64_decode($id));
        if (count($parts) != 5) {
            return response()->json(['success' => false, 'message' => 'Invalid ID'], 400);
        }

        $record = Closing::where('vcPeriodeAwal', $parts[0])
            ->where('vcPeriodeAkhir', $parts[1])
            ->where('vcNik', $parts[2])
            ->where('periode', $parts[3])
            ->where('vcClosingKe', $parts[4])
            ->with(['karyawan', 'divisi', 'gapok'])
            ->first();

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        // Format data untuk form
        $payload = $record->toArray();
        $payload['vcPeriodeAwal'] = $record->vcPeriodeAwal ? $record->vcPeriodeAwal->format('Y-m-d') : null;
        $payload['vcPeriodeAkhir'] = $record->vcPeriodeAkhir ? $record->vcPeriodeAkhir->format('Y-m-d') : null;
        $payload['periode'] = $record->periode ? $record->periode->format('Y-m-d') : null;

        return response()->json(['success' => true, 'record' => $payload]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'vcPeriodeAwal' => 'required|date',
            'vcPeriodeAkhir' => 'required|date|after_or_equal:vcPeriodeAwal',
            'vcNik' => 'required|string|max:8|exists:m_karyawan,Nik',
            'periode' => 'required|date',
            'vcClosingKe' => 'required|in:1,2',
            'jumlahHari' => 'nullable|integer|min:0',
            'vcKodeGolongan' => 'nullable|string|max:10',
            'vcKodeDivisi' => 'nullable|string|max:10',
            'vcStatusPegawai' => 'nullable|string|max:20',
            'decGapok' => 'nullable|numeric|min:0',
            'decJamKerja' => 'nullable|numeric|min:0',
            'decPotonganHC' => 'nullable|numeric|min:0',
            'decPotonganBPR' => 'nullable|numeric|min:0',
            'decIuranSPN' => 'nullable|numeric|min:0',
            'decPotonganBPJSKes' => 'nullable|numeric|min:0',
            'decPotonganBPJSJHT' => 'nullable|numeric|min:0',
            'decPotonganBPJSJP' => 'nullable|numeric|min:0',
            'decPotonganKoperasi' => 'nullable|numeric|min:0',
            'decPotonganAbsen' => 'nullable|numeric|min:0',
            'decPotonganLain' => 'nullable|numeric|min:0',
            'decVarMakan' => 'nullable|numeric|min:0',
            'decVarTransport' => 'nullable|numeric|min:0',
            'decRapel' => 'nullable|numeric',
            'decUangMakan' => 'nullable|numeric|min:0',
            'decTransport' => 'nullable|numeric|min:0',
            'intMakan' => 'nullable|integer|min:0',
            'intTransport' => 'nullable|integer|min:0',
            'intHC' => 'nullable|integer|min:0',
            'intKHL' => 'nullable|integer|min:0',
            'intHadir' => 'nullable|integer|min:0',
            'intTidakMasuk' => 'nullable|integer|min:0',
            'intJumlahHari' => 'nullable|integer|min:0',
            'intJmlSakit' => 'nullable|integer|min:0',
            'intJmlAlpha' => 'nullable|integer|min:0',
            'intJmlIzin' => 'nullable|integer|min:0',
            'intJmlIzinR' => 'nullable|integer|min:0',
            'intJmlCuti' => 'nullable|integer|min:0',
            'intJmlTelat' => 'nullable|integer|min:0',
            'decPremi' => 'nullable|numeric|min:0',
            'decJamLemburKerja1' => 'nullable|numeric|min:0',
            'decJamLemburKerja2' => 'nullable|numeric|min:0',
            'decJamLemburKerja3' => 'nullable|numeric|min:0',
            'decLemburKerja1' => 'nullable|numeric|min:0',
            'decLemburKerja2' => 'nullable|numeric|min:0',
            'decLemburKerja3' => 'nullable|numeric|min:0',
            'decJamLemburLibur2' => 'nullable|numeric|min:0',
            'decJamLemburLibur3' => 'nullable|numeric|min:0',
            'decLembur2' => 'nullable|numeric|min:0',
            'decLembur3' => 'nullable|numeric|min:0',
            'decJamLemburKerja' => 'nullable|numeric|min:0',
            'decJamLemburLibur' => 'nullable|numeric|min:0',
            'decTotallembur1' => 'nullable|numeric|min:0',
            'decTotallembur2' => 'nullable|numeric|min:0',
            'decTotallembur3' => 'nullable|numeric|min:0',
            'intCutiLalu' => 'nullable|integer|min:0',
            'intSakitLalu' => 'nullable|integer|min:0',
            'intHcLalu' => 'nullable|integer|min:0',
            'intIzinLalu' => 'nullable|integer|min:0',
            'intAlphaLalu' => 'nullable|integer|min:0',
            'intTelatLalu' => 'nullable|integer|min:0',
            'intMakanKerja' => 'nullable|integer|min:0',
            'intMakanLibur' => 'nullable|integer|min:0',
            'intTransportKerja' => 'nullable|integer|min:0',
            'intTransportLibur' => 'nullable|integer|min:0',
            'decBpjsKesehatan' => 'nullable|numeric|min:0',
            'decBpjsNaker' => 'nullable|numeric|min:0',
            'decBpjsPensiun' => 'nullable|numeric|min:0',
            'decBebanTgi' => 'nullable|numeric|min:0',
            'decBebanSiaExp' => 'nullable|numeric|min:0',
            'decBebanSiaProd' => 'nullable|numeric|min:0',
            'decBebanRma' => 'nullable|numeric|min:0',
            'decBebanSmu' => 'nullable|numeric|min:0',
            'decBebanAbnJkt' => 'nullable|numeric|min:0',
        ]);

        // Decode composite key
        $parts = explode('|', base64_decode($id));
        if (count($parts) != 5) {
            return response()->json(['success' => false, 'message' => 'Invalid ID'], 400);
        }

        // Cek apakah composite key berubah
        $oldKey = [
            'vcPeriodeAwal' => $parts[0],
            'vcPeriodeAkhir' => $parts[1],
            'vcNik' => $parts[2],
            'periode' => $parts[3],
            'vcClosingKe' => $parts[4],
        ];

        $newKey = [
            'vcPeriodeAwal' => $request->vcPeriodeAwal,
            'vcPeriodeAkhir' => $request->vcPeriodeAkhir,
            'vcNik' => $request->vcNik,
            'periode' => $request->periode,
            'vcClosingKe' => $request->vcClosingKe,
        ];

        // Jika key berubah, cek duplikasi
        if ($oldKey != $newKey) {
            $exists = Closing::where('vcPeriodeAwal', $newKey['vcPeriodeAwal'])
                ->where('vcPeriodeAkhir', $newKey['vcPeriodeAkhir'])
                ->where('vcNik', $newKey['vcNik'])
                ->where('periode', $newKey['periode'])
                ->where('vcClosingKe', $newKey['vcClosingKe'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data closing dengan periode dan NIK yang sama sudah ada'
                ], 422);
            }
        }

        // Update menggunakan DB::table karena composite key
        $data = $request->all();
        $data['dtChange'] = Carbon::now();

        // Set default 0 untuk numeric fields yang kosong
        $numericFields = [
            'jumlahHari',
            'decGapok',
            'decJamKerja',
            'decPotonganHC',
            'decPotonganBPR',
            'decIuranSPN',
            'decPotonganBPJSKes',
            'decPotonganBPJSJHT',
            'decPotonganBPJSJP',
            'decPotonganKoperasi',
            'decPotonganAbsen',
            'decPotonganLain',
            'decVarMakan',
            'decVarTransport',
            'decRapel',
            'decUangMakan',
            'decTransport',
            'intMakan',
            'intTransport',
            'intHC',
            'intKHL',
            'intHadir',
            'intTidakMasuk',
            'intJumlahHari',
            'intJmlSakit',
            'intJmlAlpha',
            'intJmlIzin',
            'intJmlIzinR',
            'intJmlCuti',
            'intJmlTelat',
            'decPremi',
            'decJamLemburKerja1',
            'decJamLemburKerja2',
            'decJamLemburKerja3',
            'decLemburKerja1',
            'decLemburKerja2',
            'decLemburKerja3',
            'decJamLemburLibur2',
            'decJamLemburLibur3',
            'decLembur2',
            'decLembur3',
            'decJamLemburKerja',
            'decJamLemburLibur',
            'decTotallembur1',
            'decTotallembur2',
            'decTotallembur3',
            'intCutiLalu',
            'intSakitLalu',
            'intHcLalu',
            'intIzinLalu',
            'intAlphaLalu',
            'intTelatLalu',
            'intMakanKerja',
            'intMakanLibur',
            'intTransportKerja',
            'intTransportLibur',
            'decBpjsKesehatan',
            'decBpjsNaker',
            'decBpjsPensiun',
            'decBebanTgi',
            'decBebanSiaExp',
            'decBebanSiaProd',
            'decBebanRma',
            'decBebanSmu',
            'decBebanAbnJkt'
        ];

        foreach ($numericFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $data[$field] = 0;
            }
        }

        // Jika closing ke-2, copy data absensi P1 dari closing ke-1 ke field int*Lalu
        if (($request->vcClosingKe ?? '') == '2') {
            // Tentukan periode P1 berdasarkan periode gajian
            $periode = Carbon::parse($request->periode);
            $periodeP1 = null;
            if ($periode->day == 15) {
                // Periode gajian 15, cari closing P1 dengan periode tanggal 1 di bulan yang sama
                $periodeP1 = $periode->copy()->day(1)->format('Y-m-d');
            } else {
                // Periode gajian 1, cari closing P1 dengan periode tanggal 15 di bulan sebelumnya
                $periodeP1 = $periode->copy()->subMonth()->day(15)->format('Y-m-d');
            }

            $closingP1 = Closing::where('vcNik', $request->vcNik)
                ->where('periode', $periodeP1)
                ->where('vcClosingKe', '1')
                ->where('vcKodeDivisi', $request->vcKodeDivisi)
                ->first();

            if ($closingP1) {
                // Copy data absensi P1 ke field int*Lalu
                $data['intCutiLalu'] = $closingP1->intJmlCuti ?? 0;
                $data['intSakitLalu'] = $closingP1->intJmlSakit ?? 0;
                $data['intHcLalu'] = $closingP1->intHC ?? 0;
                $data['intIzinLalu'] = $closingP1->intJmlIzin ?? 0;
                $data['intAlphaLalu'] = $closingP1->intJmlAlpha ?? 0;
                $data['intTelatLalu'] = $closingP1->intJmlTelat ?? 0;
            }
        }

        // Jika composite key berubah, hapus yang lama dan insert yang baru
        if ($oldKey != $newKey) {
            // Ambil dtCreate dari record lama sebelum delete
            $oldRecord = DB::table('t_closing')
                ->where('vcPeriodeAwal', $oldKey['vcPeriodeAwal'])
                ->where('vcPeriodeAkhir', $oldKey['vcPeriodeAkhir'])
                ->where('vcNik', $oldKey['vcNik'])
                ->where('periode', $oldKey['periode'])
                ->where('vcClosingKe', $oldKey['vcClosingKe'])
                ->first();

            if ($oldRecord && isset($oldRecord->dtCreate)) {
                $data['dtCreate'] = $oldRecord->dtCreate;
            } else {
                $data['dtCreate'] = Carbon::now();
            }

            // Hapus record lama
            DB::table('t_closing')
                ->where('vcPeriodeAwal', $oldKey['vcPeriodeAwal'])
                ->where('vcPeriodeAkhir', $oldKey['vcPeriodeAkhir'])
                ->where('vcNik', $oldKey['vcNik'])
                ->where('periode', $oldKey['periode'])
                ->where('vcClosingKe', $oldKey['vcClosingKe'])
                ->delete();

            // Insert record baru
            DB::table('t_closing')->insert($data);
        } else {
            // Update langsung
            DB::table('t_closing')
                ->where('vcPeriodeAwal', $oldKey['vcPeriodeAwal'])
                ->where('vcPeriodeAkhir', $oldKey['vcPeriodeAkhir'])
                ->where('vcNik', $oldKey['vcNik'])
                ->where('periode', $oldKey['periode'])
                ->where('vcClosingKe', $oldKey['vcClosingKe'])
                ->update($data);
        }

        return response()->json(['success' => true, 'message' => 'Data Closing Gaji berhasil diperbarui']);
    }

    public function destroy(string $id)
    {
        // Decode composite key
        $parts = explode('|', base64_decode($id));
        if (count($parts) != 5) {
            return response()->json(['success' => false, 'message' => 'Invalid ID'], 400);
        }

        DB::table('t_closing')
            ->where('vcPeriodeAwal', $parts[0])
            ->where('vcPeriodeAkhir', $parts[1])
            ->where('vcNik', $parts[2])
            ->where('periode', $parts[3])
            ->where('vcClosingKe', $parts[4])
            ->delete();

        return response()->json(['success' => true, 'message' => 'Data Closing Gaji berhasil dihapus']);
    }

    /**
     * Calculate working days (API endpoint)
     */
    public function calculateWorkingDays(Request $request)
    {
        $request->validate([
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
        ]);

        $tanggalAwal = Carbon::parse($request->periode_awal);
        $tanggalAkhir = Carbon::parse($request->periode_akhir);

        // Get hari libur (weekend + holidays)
        $hariLibur = HariLibur::whereBetween('dtTanggal', [$tanggalAwal->format('Y-m-d'), $tanggalAkhir->format('Y-m-d')])
            ->pluck('dtTanggal')
            ->map(function ($tanggal) {
                return $tanggal instanceof Carbon ? $tanggal->format('Y-m-d') : Carbon::parse($tanggal)->format('Y-m-d');
            })
            ->toArray();

        // Add weekends
        $current = $tanggalAwal->copy();
        while ($current->lte($tanggalAkhir)) {
            if (in_array($current->dayOfWeek, [0, 6])) { // 0 = Minggu, 6 = Sabtu
                $tanggalStr = $current->format('Y-m-d');
                if (!in_array($tanggalStr, $hariLibur)) {
                    $hariLibur[] = $tanggalStr;
                }
            }
            $current->addDay();
        }

        // Calculate working days
        $count = 0;
        $current = $tanggalAwal->copy();
        while ($current->lte($tanggalAkhir)) {
            $tanggalStr = $current->format('Y-m-d');
            if (!in_array($tanggalStr, $hariLibur)) {
                $count++;
            }
            $current->addDay();
        }

        return response()->json([
            'success' => true,
            'jumlah_hari' => $count
        ]);
    }

    /**
     * Get gapok data by NIK (API endpoint)
     */
    public function getGapokByNik(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
        ]);

        $karyawan = Karyawan::find($request->nik);
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        $gapok = Gapok::find($karyawan->Gol);
        if (!$gapok) {
            return response()->json([
                'success' => false,
                'message' => 'Golongan tidak ditemukan'
            ], 404);
        }

        // Calculate gaji pokok per bulan
        $gapokPerBulan = (float) ($gapok->upah ?? 0)
            + (float) ($gapok->tunj_keluarga ?? 0)
            + (float) ($gapok->tunj_masa_kerja ?? 0)
            + (float) ($gapok->tunj_jabatan1 ?? 0)
            + (float) ($gapok->tunj_jabatan2 ?? 0);

        return response()->json([
            'success' => true,
            'tarif_makan' => (float) ($gapok->uang_makan ?? 0),
            'tarif_transport' => (float) ($gapok->uang_transport ?? 0),
            'tarif_premi' => (float) ($gapok->premi ?? 0),
            'gapok_per_bulan' => $gapokPerBulan,
            'gapok_setengah_bulan' => $gapokPerBulan / 2
        ]);
    }

    /**
     * Get absensi periode sebelumnya (P1) untuk periode 2
     */
    public function getAbsensiPeriodeSebelumnya(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'periode' => 'required|date', // Periode gajian (tanggal 1 atau 15)
            'vcKodeDivisi' => 'required|string',
        ]);

        $periode = Carbon::parse($request->periode);

        // Tentukan periode P1 berdasarkan periode gajian
        $periodeP1 = null;
        if ($periode->day == 15) {
            // Periode gajian 15, cari closing P1 dengan periode tanggal 1 di bulan yang sama
            $periodeP1 = $periode->copy()->day(1)->format('Y-m-d');
        } else {
            // Periode gajian 1, cari closing P1 dengan periode tanggal 15 di bulan sebelumnya
            $periodeP1 = $periode->copy()->subMonth()->day(15)->format('Y-m-d');
        }

        // Cari periode 1 dengan periode P1 yang sudah ditentukan
        $periodeSebelumnya = \App\Models\PeriodeGaji::where('vcKodeDivisi', $request->vcKodeDivisi)
            ->where('periode', $periodeP1)
            ->where('vcQuarter', '1')
            ->first();

        if (!$periodeSebelumnya) {
            return response()->json([
                'success' => false,
                'message' => 'Periode 1 tidak ditemukan untuk periode gajian ini'
            ], 404);
        }

        // Ambil closing data periode 1 dengan periode P1 yang sudah ditentukan
        $closingP1 = Closing::where('vcNik', $request->nik)
            ->where('periode', $periodeP1)
            ->where('vcClosingKe', '1')
            ->where('vcKodeDivisi', $request->vcKodeDivisi)
            ->first();

        if (!$closingP1) {
            return response()->json([
                'success' => true,
                'periode_awal' => $periodeSebelumnya->dtPeriodeFrom,
                'periode_akhir' => $periodeSebelumnya->dtPeriodeTo,
                'absensi' => [
                    'intJmlCuti' => 0,
                    'intJmlSakit' => 0,
                    'intJmlIzin' => 0,
                    'intJmlAlpha' => 0,
                    'intJmlTelat' => 0,
                    'intHC' => 0,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'periode_awal' => $closingP1->vcPeriodeAwal->format('Y-m-d'),
            'periode_akhir' => $closingP1->vcPeriodeAkhir->format('Y-m-d'),
            'absensi' => [
                'intJmlCuti' => $closingP1->intJmlCuti ?? 0,
                'intJmlSakit' => $closingP1->intJmlSakit ?? 0,
                'intJmlIzin' => $closingP1->intJmlIzin ?? 0,
                'intJmlAlpha' => $closingP1->intJmlAlpha ?? 0,
                'intJmlTelat' => $closingP1->intJmlTelat ?? 0,
                'intHC' => $closingP1->intHC ?? 0,
            ]
        ]);
    }
}
