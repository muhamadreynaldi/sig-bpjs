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
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $penerimas = Penerima::query()
            ->when($search, function ($query, $search) {
                return $query->where('nama', 'like', "%{$search}%")
                             ->orWhere('nik', 'like', "%{$search}%")
                             ->orWhere('dusun', 'like', "%{$search}%");
            })
            ->orderBy('nama', 'asc')
            ->paginate(10);

        return view('pages.penerima.index', compact('penerimas', 'search'));
    }

    public function create(): View
    {
        $statusOptions = ['Aktif', 'Nonaktif', 'Meninggal'];
        return view('pages.penerima.create', compact('statusOptions'));
    }

    public function store(StorePenerimaRequest $request): RedirectResponse
    {
        Penerima::create($request->validated());

        return redirect()->route('penerima.index')
                         ->with('success', 'Data penerima berhasil ditambahkan.');
    }

        public function show(Penerima $penerima): View
    {
        return view('pages.penerima.show', compact('penerima'));
    }

    public function edit(Penerima $penerima): View
    {
        $statusOptions = ['Aktif', 'Nonaktif', 'Meninggal'];
        return view('pages.penerima.edit', compact('penerima', 'statusOptions'));
    }

    public function update(UpdatePenerimaRequest $request, Penerima $penerima): RedirectResponse
    {
        $penerima->update($request->validated());

        return redirect()->route('penerima.index')
                         ->with('success', 'Data penerima berhasil diperbarui.');
    }

        public function destroy(Penerima $penerima): RedirectResponse
    {
        try {
            $penerima->delete();
            return redirect()->route('penerima.index')
                             ->with('success', 'Data penerima berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('penerima.index')
                             ->with('error', 'Gagal menghapus data penerima. Error: ' . $e->getMessage());
        }
    }

    public function ajaxSelect2Suggestions(Request $request): JsonResponse
    {
        $term = $request->input('q');
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
                'id' => $text,
                'text' => $text,
            ];
        });

        return response()->json(['results' => $results]);
    }

        public function apiShowDetail(Penerima $penerima): JsonResponse
    {
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