<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPembelian;
use App\Models\DetailPeminjaman;
use App\Models\JenisBarang;
use App\Models\Inventaris;
use App\Exports\AlatPerlengkapanExport;
use App\Exports\BahanExport;
use Endroid\QrCode\QrCode;
use PDF;
use Illuminate\Http\Request;
use Endroid\QrCode\Writer\PngWriter;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{
    public function index(){

        $alatdanperlengkapan = Barang::with('inventaris')->where('id_jenis_barang', '!=', 3)->get();

        $borrowedInventarisIds = DetailPeminjaman::where('status', 'dipinjam')
            ->pluck('id_inventaris')
            ->toArray();

        $inventarisByBarang = Inventaris::whereIn('id_inventaris', $borrowedInventarisIds)
            ->get()
            ->groupBy('id_barang');

        $alatdanperlengkapan->each(function ($item) use ($inventarisByBarang) {
            $itemInventaris = $inventarisByBarang->get($item->id_barang, collect());
            $item->is_dipinjam = $itemInventaris->isNotEmpty() 
                && $item->inventaris->pluck('id_inventaris')->intersect($itemInventaris->pluck('id_inventaris'))->isNotEmpty();
        });

        $bahan = Barang::where('id_jenis_barang',  3)->get();
        $barang = Barang::all();
        $inventaris = Inventaris::all();
        $jenisBarang = JenisBarang::all()->except('3');
        $totals = $inventaris->groupBy('id_barang')->map(function ($group) {
            return $group->sum('jumlah_barang');
        });
        // dd($alatdanperlengkapan);
          // Create an associative array where keys are id_barang and values are updated stok_barang
          $barangIds = $totals->keys()->toArray();
        $barangs = Barang::whereIn('id_barang', $barangIds)->pluck('stok_barang', 'id_barang');
        $updatedStokBarang = $totals->map(function ($jumlah_barang, $id_barang) use ($barangs) {
            $updatedStok = ($barangs[$id_barang] ?? 0) + $jumlah_barang;
            return $updatedStok;
        });

        return view('barang.index',[
        'barang' => $barang,
        'bahan' => $bahan,
        'alatdanperlengkapan' => $alatdanperlengkapan,
        'jenisBarang' => $jenisBarang,
        'updatedStokBarang' => $updatedStokBarang,
        'totals' => $totals,
        ]);
    }


            public function store(Request $request)
        {
            $request->validate([
                'nama_barang' => 'required',
                'merek' => 'required',
                'kode_barang' => 'nullable',
                'stok_barang' => 'nullable',
                'id_jenis_barang' => 'nullable',
            ]);

            // Check if both kode_barang and id_jenis_barang are null
            if ($request->kode_barang === null && $request->id_jenis_barang === null) {
                return redirect()->back()->with(['error_message' => 'Data belum terisi.']);
            }

            if ($request->kode_barang !== null) {
                $existingBarang = Barang::where('kode_barang', $request->kode_barang)->first();
            
                if ($existingBarang) {
                    return redirect()->back()->with(['error' => 'Kode barang sudah tersedia.']);
                }
            }
            

            $barang = new Barang();
            $barang->id_detail_pembelian = 1;
            $barang->nama_barang = $request->nama_barang;
            $barang->merek = $request->merek;
            $barang->stok_barang = $request->stok_barang;

            // Check if id_jenis_barang is provided, if not set it to a default value
            $barang->id_jenis_barang = $request->id_jenis_barang ?? '3';

            if ($request->kode_barang) {
                $barang->kode_barang = $request->kode_barang;
                $imageName = $this->generateBarcode($request->kode_barang);
                $barang->qrcode_image = $imageName;
            } else {
                $barang->kode_barang = null;
                $barang->qrcode_image = null;
            }

            $barang->save();
           if($barang -> stok_barang == '0'){
            return redirect()->back()->with(['success_message' => 'Data telah tersimpan.']);
           }else{
            $inventaris = new Inventaris();
            $inventaris->id_barang = $barang->id_barang;
            $inventaris->jumlah_barang =  $barang->stok_barang; 
            $inventaris->kondisi_barang = 'lengkap'; 
            $inventaris->id_ruangan = 3; 
            $inventaris->save();
            
            return redirect()->back()->with(['success_message' => 'Data telah tersimpan.']);
           }
        }


    public function generateBarcode($kode_barang)
    {
        $ImageName = 'qrcode_' . $kode_barang;
        
        if (!empty($kode_barang)) {
            $qrCode = QrCode::create($kode_barang)->setSize(200)->setMargin(5);
            $storagePath = public_path('storage/qrcode/' . $ImageName . '.png');
            
            // Create a PngWriter instance
            $writer = new PngWriter();
            $qrCodeData = $writer->write($qrCode)->getString();
    
            file_put_contents($storagePath, $qrCodeData);
    
            return $ImageName . '.png';
        } else {
            return null;
        }

    }
    public function update(Request $request, $id_barang){
        $request->validate([
            'nama_barang' => 'required',
            'merek' => 'required',
            'kode_barang' => 'nullable',
            'stok_barang' => 'nullable',
            'id_jenis_barang' => 'required',
        ]);
        // dd($request);
        $barang = Barang::find($id_barang);
        $barang->nama_barang = $request->nama_barang;
        $barang->merek = $request->merek;
        $barang->stok_barang = $request->stok_barang;
        $barang->kode_barang = $request->kode_barang;
        $barang->id_jenis_barang = $request->id_jenis_barang;

        $imageName = $barang->kode_barang ? $this->generateBarcode($barang->kode_barang) : null;

        // Update path QR code pada barang yang baru dibuat
        $barang->qrcode_image = $imageName;
        $barang->save();
        return redirect()->back()->with(['success_message' => 'Data telah tersimpan.',
    ]);
    }

    public function destroy($id_barang){
        $barang = Barang::find($id_barang);
        if($barang){
            $barang->delete();
        }
        return redirect()->back()->with(['success_message' => 'Data telah terhapus.',]);
    }

    public function exportAlatPerlengkapan(){
        $barangs = Barang::with(['jenisbarang'])->where('id_jenis_barang', '!=', 3)->get();
        return Excel::download((new AlatPerlengkapanExport)
            ->setAlatPerlengkapan($barangs), 'Alat & Perlengkapan.xlsx');
    }
    
    public function exportBahan(){
        $bahans = Barang::with(['jenisbarang'])->where('id_jenis_barang', 3)->get();
        return Excel::download((new BahanExport)
        ->setBahan($bahans), 'Bahan Praktik.xlsx');

    }

    public function print($id_barang){
        $barang = Barang::with(['detailPembelian.pembelian'])->where('id_barang', $id_barang)->get();
        $tahunPembelian = DetailPembelian::with(['pembelian'])->where('id_barang', $id_barang)->get();

        $pdf = PDF::loadView('barang.export.print', [
            'barang' => $barang,
            'tahunPembelian' => $tahunPembelian,
        ])->setPaper('a4', 'potrait');
        return $pdf->download('LabelQRcode.pdf');   
    } 
    
    public function selectPrint(Request $request){

        $selectedData = $request->input('id_barang');
        if (!$selectedData) {
            return redirect()->back()->with(['error' => 'Tidak ada data yang di pilih.']);
        }

        $barang = Barang::with(['detailPembelian.pembelian'])->whereIn('id_barang', $selectedData)->get();
        $pdf = PDF::loadView('barang.export.selectPrint', [
            'barang' => $barang,
            // 'tahunPembelian' => $tahunPembelian,
        ])->setPaper('a4', 'potrait');
        return $pdf->download('LabelQRcode.pdf');   
        // dd($selectedData);
    } 
}