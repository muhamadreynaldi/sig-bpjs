<?php

namespace App\Http\Controllers;

use App\Models\Penerima;
use App\Http\Requests\StorePenerimaRequest;
use App\Http\Requests\UpdatePenerimaRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PenerimaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View // Tambahkan return type View
    {
        $search = $request->input('search');

        $penerimas = Penerima::query()
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', "%{$search}%")
                             ->orWhere('nik', 'like', "%{$search}%")
                             ->orWhere('dusun', 'like', "%{$search}%");
            })
            ->orderBy('nama', 'asc') // Urutkan berdasarkan nama
            ->paginate(10); // Paginasi 10 data per halaman

        return view('pages.penerima.index', compact('penerimas', 'search'));
    }

    public function create(): View
    {
        $statusOptions = ['Aktif', 'Nonaktif', 'Meninggal'];
        // Ambil daftar dusun unik dari database jika ingin dijadikan dropdown
        // $dusunOptions = Penerima::distinct()->pluck('dusun')->filter()->sort()->toArray();
        return view('pages.penerima.create', compact('statusOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePenerimaRequest $request): RedirectResponse
    {
        Penerima::create($request->validated());

        return redirect()->route('penerima.index')
                         ->with('success', 'Data penerima berhasil ditambahkan.');
    }

        public function show(Penerima $penerima): View
    {
        // Kirim data penerima ke view detail.blade.php
        // Untuk saat ini, kita bisa buat view sederhana atau redirect ke edit/index jika tidak ada halaman detail khusus.
        return view('pages.penerima.show', compact('penerima'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penerima $penerima): View // Route Model Binding
    {
        $statusOptions = ['Aktif', 'Nonaktif', 'Meninggal'];
        return view('pages.penerima.edit', compact('penerima', 'statusOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePenerimaRequest $request, Penerima $penerima): RedirectResponse // Route Model Binding
    {
        $penerima->update($request->validated());

        return redirect()->route('penerima.index')
                         ->with('success', 'Data penerima berhasil diperbarui.');
    }

        public function destroy(Penerima $penerima): RedirectResponse // Route Model Binding
    {
        try {
            $penerima->delete();
            return redirect()->route('penerima.index')
                             ->with('success', 'Data penerima berhasil dihapus.');
        } catch (\Exception $e) {
            // Log error jika perlu: Log::error($e->getMessage());
            return redirect()->route('penerima.index')
                             ->with('error', 'Gagal menghapus data penerima. Error: ' . $e->getMessage());
        }
    }

    public function ajaxSelect2Suggestions(Request $request): JsonResponse
    {
        $term = $request->input('q'); // Select2 biasanya mengirim parameter 'q' atau 'term'
        if (empty($term)) {
            return response()->json(['results' => []]);
        }

        $penerimas = Penerima::where('nama', 'LIKE', "%{$term}%")
                             ->orWhere('nik', 'LIKE', "%{$term}%")
                             ->select('id', 'nik', 'nama')
                             ->limit(15) // Batasi jumlah suggestions
                             ->get();

        $results = $penerimas->map(function ($penerima) {
            $text = $penerima->nik . ' - ' . $penerima->nama;
            return [
                'id' => $text, // Kita akan menggunakan teks ini sebagai 'id' yang akan disubmit
                               // Ini agar backend tetap menerima string pencarian
                'text' => $text, // Teks yang akan ditampilkan di dropdown suggestion
            ];
        });

        return response()->json(['results' => $results]);
    }

        public function apiShowDetail(Penerima $penerima): JsonResponse // Menggunakan Route Model Binding
    {
        // Pastikan hanya data yang relevan dan aman yang dikirim
        return response()->json([
            'id' => $penerima->id,
            'nik' => $penerima->nik,
            'nama' => $penerima->nama,
            'alamat' => $penerima->alamat,
            'dusun' => $penerima->dusun,
            'status' => $penerima->status,
            'lat' => $penerima->lat,
            'lng' => $penerima->lng,
            'updated_at_formatted' => $penerima->updated_at->format('d M Y, H:i:s'),
        ]);
    }
}