<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FarmerController extends Controller
{
     public function index(Request $request)
     {
          $farmers = Farmer::query()
               ->when($request->filled('search'), function ($query) use ($request) {
                    $query->where('name', 'ilike', '%' . $request->input('search') . '%');
               })
               ->orderBy('name')
               ->paginate(10)
               ->withQueryString();

          return view('farmers.index', compact('farmers'));
     }

     public function store(Request $request)
     {
          $validated = $request->validate($this->rules(), $this->messages());

          Farmer::create($validated);

          return redirect()->route('farmers.index')
               ->with('success', 'Petani berhasil ditambahkan.');
     }

     public function update(Request $request, Farmer $farmer)
     {
          $validated = $request->validate($this->rules($farmer), $this->messages());

          $farmer->update($validated);

          return redirect()->route('farmers.index')
               ->with('success', 'Data petani berhasil diperbarui.');
     }

     public function destroy(Farmer $farmer)
     {
          if ($farmer->purchases()->exists()) {
               return redirect()->route('farmers.index')
                    ->with('error', 'Petani tidak dapat dihapus karena masih memiliki transaksi pembelian.');
          }

          $farmer->delete();

          return redirect()->route('farmers.index')
               ->with('success', 'Petani berhasil dihapus.');
     }

     private function rules(?Farmer $farmer = null): array
     {
          return [
               'name' => ['required', 'string', 'max:100'],
               'nik' => ['nullable', 'digits:16', Rule::unique('farmers', 'nik')->ignore($farmer?->id)],
               'phone' => ['nullable', 'regex:/^(\+62|62|0)[0-9]{8,13}$/'],
               'address' => ['nullable', 'string', 'max:255'],
          ];
     }

     private function messages(): array
     {
          return [
               'name.required' => 'Nama petani wajib diisi.',
               'name.max' => 'Nama petani maksimal 100 karakter.',
               'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
               'nik.unique' => 'NIK sudah terdaftar.',
               'phone.regex' => 'Format nomor telepon tidak valid.',
               'address.max' => 'Alamat maksimal 255 karakter.',
          ];
     }
}