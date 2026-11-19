<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Agar fokus ke ORM, model dikembalikan langsung. Di proyek nyata pakai API Resource.
class MahasiswaController extends Controller
{
    /** GET /api/v1/mahasiswa?q=andi&per_page=10 */
    public function index(Request $request)
    {
        return Mahasiswa::query()
            ->withCount('surat')                                   // tambah kolom surat_count
            ->when($request->q, fn ($q, $kata) => $q->where(fn ($q) => $q
                ->where('nama', 'like', "%{$kata}%")
                ->orWhere('nim', 'like', "%{$kata}%")))
            ->orderBy('nama')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();
    }

    /** POST /api/v1/mahasiswa */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nim'      => ['required', 'regex:/^\d{5,15}$/', 'unique:mahasiswa,nim'],
            'nama'     => ['required', 'string', 'max:100'],
            'prodi'    => ['required', 'string', 'max:50'],
            'angkatan' => ['required', 'integer', 'between:2000,2100'],
        ]);

        $mahasiswa = Mahasiswa::create($data);                    // INSERT

        return response()->json(['data' => $mahasiswa], 201);
    }

    /** GET /api/v1/mahasiswa/{mahasiswa} */
    public function show(Mahasiswa $mahasiswa)
    {
        // eager loading: surat + jenis suratnya
        return ['data' => $mahasiswa->load('surat.jenisSurat')];
    }

    /** PUT/PATCH /api/v1/mahasiswa/{mahasiswa} */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $data = $request->validate([
            'nim'      => ['sometimes', 'regex:/^\d{5,15}$/',
                           Rule::unique('mahasiswa', 'nim')->ignore($mahasiswa->id)],
            'nama'     => ['sometimes', 'string', 'max:100'],
            'prodi'    => ['sometimes', 'string', 'max:50'],
            'angkatan' => ['sometimes', 'integer', 'between:2000,2100'],
        ]);

        $mahasiswa->update($data);                                // UPDATE ... WHERE id = ?

        return ['data' => $mahasiswa];
    }

    /** DELETE /api/v1/mahasiswa/{mahasiswa} */
    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();                                     // surat ikut terhapus (cascade)

        return response()->noContent();
    }
}