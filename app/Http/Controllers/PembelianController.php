<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPembelian;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class PembelianController extends Controller
{
    public function index(){
        $pembelian = Pembelian::where('id_pembelian', '!=', 1)
        ->orderBy('tgl_pembelian', 'desc')
        ->withSum('detailPembelian', 'subtotal_pembelian')
        ->withSum('detailPembelian', 'jumlah_barang')
        ->get();
        
        $subtotals = $pembelian->pluck('detail_pembelian_sum_subtotal_pembelian', 'id_pembelian');
        $stoks = $pembelian->pluck('detail_pembelian_sum_jumlah_barang', 'id_pembelian');

        return view("pembelian.index",[
            'pembelian' => $pembelian,
            'subtotalPembelian' => $subtotals,
            'stoklPembelian' => $stoks
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'tgl_pembelian' => 'required',
            'nama_toko' => 'required',
            'keterangan_anggaran' => 'required',
            'nota_pembelian' => 'mimes:jpg,jpeg,png',
        ]);
        // dd($request);

        $pembelian = new Pembelian;
        $pembelian->tgl_pembelian = $request->tgl_pembelian;
        $pembelian->nama_toko = $request->nama_toko;
        $pembelian->keterangan_anggaran = $request->keterangan_anggaran;

        if($request->hasFile('nota_pembelian')) {
            $file = $request->file('nota_pembelian');
            $fileName = Str::random(10).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('/storage/nota_pembelian'), $fileName);            
            $pembelian->nota_pembelian = $fileName;
        }

        $pembelian->save();
        return redirect()->route('pembelian.showDetail', ['id_pembelian' => $pembelian->id_pembelian])->with('success_message', 'Data telah tersimpan.');

    }

    public function update(Request $request, $id_pembelian){
        
        $request->validate([
            'tgl_pembelian' => 'required',
            'nama_toko' => 'required',
            'keterangan_anggaran' => 'required',
            'nota_pembelian' => 'mimes:jpg,jpng,png',
        ]);

        $pembelian = Pembelian::find($id_pembelian);
       
        $pembelian->tgl_pembelian = $request->tgl_pembelian;
        $pembelian->nama_toko = $request->nama_toko;
        $pembelian->keterangan_anggaran = $request->keterangan_anggaran;

        if ($request->hasFile('nota_pembelian')) {
            // Hapus gambar lama jika ada
            if ($pembelian->nota_pembelian) {
                Storage::disk('public')->delete('nota_pembelian/' . $pembelian->nota_pembelian);
            }
        
            // Simpan gambar baru   
            $nota_pembelian = $request->file('nota_pembelian');
            $nama_nota_pembelian = Str::random(10) . '.' . $nota_pembelian->getClientOriginalExtension();
            Storage::disk('public')->put('nota_pembelian/' . $nama_nota_pembelian, file_get_contents($nota_pembelian));
            $pembelian->nota_pembelian = $nama_nota_pembelian;
        }

        $pembelian->save();
        return redirect()->back()->with(['success_message' => 'Data telah tersimpan.']);
    }

    public function destroy($id_pembelian){
        $pembelian = Pembelian::find($id_pembelian);
        if ($pembelian){
            $pembelian -> delete();
        }
        return redirect()->back()->with(['success_message' => 'Data telah terhapus.']);
    }
}