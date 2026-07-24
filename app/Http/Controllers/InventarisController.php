<?php

namespace App\Http\Controllers;
use App\Models\Inventaris;
use App\Models\Barang;
use App\Models\Ruangan;
use App\Models\DetailPeminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;


class InventarisController extends Controller
{
    public function index(){

        
        $inventaris = Ruangan::all();

        return view('inventaris.index',[
            'inventaris' => $inventaris,
            'ruangan'   => Ruangan::all(),
            'barang'    => Barang::all()
        ]);
    }

  
    public function store(Request $request){
        
        // dd($request);
        $request->validate([
            'id_barang' => 'required',
            'id_ruangan' => 'required',
            'jumlah_barang' => 'nullable',
            'kondisi_barang' => 'nullable',
            'ket_barang' => 'nullable|max:50'
        ]);

        $existingInventaris = Inventaris::where('id_ruangan', $request->id_ruangan)
        ->where('id_barang', $request->id_barang)
        ->where('kondisi_barang', 'lengkap')
        ->first();

    if ($existingInventaris) {
        $stokBarang = Barang::where('id_barang', $request->id_barang)->first();

        if (!$stokBarang || $stokBarang->stok_barang < ($existingInventaris->jumlah_barang + $request->jumlah_barang)) {
            return redirect()->back()->with(['error' => 'Jumlah barang melebihi stok barang yang tersedia.']);
        }

        $existingInventaris->jumlah_barang = $existingInventaris->jumlah_barang + $request->jumlah_barang;        
        $existingInventaris->ket_barang = $request->ket_barang;
        $existingInventaris->save();

        return redirect()->back()->with(['success_message' => 'Data telah diperbarui.']);
    }

    $stokBarang = Barang::where('id_barang', $request->id_barang)->first();

    if (!$stokBarang || $stokBarang->stok_barang < $request->jumlah_barang || $stokBarang->stok_barang - $request->jumlah_barang < 0) {
        return redirect()->back()->with(['error' => 'Stok barang tidak mencukupi.']);
    }
 
        $inventaris = new Inventaris();
        $inventaris->id_barang = $request->id_barang;
        $inventaris->id_ruangan = $request->id_ruangan;
        $inventaris->jumlah_barang = $request->jumlah_barang;
        $inventaris->kondisi_barang = $request->kondisi_barang;
        $inventaris->ket_barang = $request->ket_barang;
        
        $inventaris->save();

        $id_ruangan  = $inventaris->id_ruangan;

        return redirect()->route('inventaris.showDetail', ["id_ruangan" => $id_ruangan])
        ->with(['success_message' => 'Data telah tersimpan.']);

    }

    public function fetchIdBarang($id_barang) {
        $barang = Barang::where('id_barang', $id_barang)
        ->select('nama_barang')
        ->first();
    
        return response()->json($barang);
    }

    public function getBarangDataByKode(Request $request)
    {
      $kodeBarang = $request->validate([
        'kode_barang' => 'required|string|trim', // Validation example
      ])['kode_barang'];
    
      $barang = Barang::where('kode_barang', $kodeBarang)->first(); // Example query
    
      if ($barang) {
        return response()->json([
          'nama_barang' => $barang->nama_barang,
        ]);
      } else {
        return response()->json(['message' => 'Barang not found'], 404);
      }
    }
    

    public function fetchKodeBarang($kode_barang)
{
    // Find the record in the database based on the kode_barang
    $barang = Barang::where('kode_barang', $kode_barang)->first();

    // Check if the record exists
    if ($barang) {
        // If the record exists, return the id_barang
        return response()->json(['id_barang' => $barang->id_barang]);
    } else {
        // If the record doesn't exist, return an error response
        return response()->json(['error' => 'Barang not found for the given kode_barang.'], 404);
    }
}


    public function update(Request $request, $id_inventaris){
        
        $request->validate([
        'id_barang' => 'required',
            'id_ruangan' => 'required',
            'jumlah_barang' => 'nullable',
            'kondisi_barang' => 'nullable',
            'ket_barang' => 'nullable|max:50'
        ]);

        $inventaris = Inventaris::find($id_inventaris);

        $stokBarang = Barang::where('id_barang', $request->id_barang)->first();

        if (!$stokBarang) {
            return redirect()->back()->with(['error' => 'Barang not found.']);
        }
    
        // Calculate the difference between the new and the current quantity
        $quantityDifference = $request->jumlah_barang - $inventaris->jumlah_barang;
    
        // Check if the stock is sufficient
        if ($stokBarang->stok_barang < $quantityDifference || $stokBarang->stok_barang - $quantityDifference < 0) {
            return redirect()->back()->with(['error' => 'Stok barang tidak mencukupi.']);
        }

        $barang = Barang::find($request->id_barang);
        $existingInventaris = Inventaris::where('id_inventaris', '!=', $id_inventaris)
        ->whereHas('barang', function($query) use ($barang) {
            $query->where('kode_barang', $barang->kode_barang);
        })
        ->whereNull('jumlah_barang')
        ->first();

    // Check if an existing inventaris item is found
    if ($existingInventaris) {
        return redirect()->back()->with(['error' => 'An inventaris item with the same Kode Barang already exists.']);
    }
            
        $inventaris->id_barang = $request->id_barang;
        $inventaris->id_ruangan = $request->id_ruangan;
        $inventaris->jumlah_barang = $request->jumlah_barang;
        $inventaris->kondisi_barang = $request->kondisi_barang;
        $inventaris->ket_barang = $request->ket_barang;
        $inventaris->save();

        return redirect()->back()->with(['success_message' => 'Data telah tersimpan.']);
    }


    
    public function ruangan(Request $request, $id_inventaris)
    {
        $request->validate([
            'id_ruangan' => 'required',
            'jumlah_barang' => 'nullable|integer|min:1',
        ]);
    
        $inventaris = Inventaris::find($id_inventaris);

    // Check if the Inventaris record exists
    if (!$inventaris) {
        return redirect()->back()->withErrors(['error_message' => 'Inventaris not found.']);
    }

    // Fetch the associated Barang record
    $barang = Barang::find($inventaris->id_barang);

    // Check if the Barang record exists
    if (!$barang) {
        return redirect()->back()->withErrors(['error_message' => 'Barang not found.']);
    }

    // If id_jenis_barang equals 3, validate and handle jumlah_barang
            if ($barang->id_jenis_barang == 3) {
            
                    $request->validate([
                        'jumlah_barang' => 'required|integer|min:1',
                    ]);
            
                    // If the requested jumlah_barang is less than current jumlah_barang
                    if ($request->jumlah_barang < $inventaris->jumlah_barang) {
                        // Calculate remaining quantity
                        $remaining_quantity = $inventaris->jumlah_barang - $request->jumlah_barang;
            
                        // Update current inventaris with the new jumlah_barang
                        $inventaris->jumlah_barang = $remaining_quantity;
                        
                        // Save the changes to current inventaris
                        $inventaris->save();
            
                        // Create a new inventaris for the remaining quantity
                        $new_inventaris = new Inventaris();
                        $new_inventaris->id_barang = $inventaris->id_barang;
                        $new_inventaris->id_ruangan = $request->id_ruangan; // Assign the new room
                        $new_inventaris->jumlah_barang = $request->jumlah_barang;
                        $new_inventaris->save();
            
                        return redirect()->back()->with(['success_message' => 'Barang Telah Dipindahkan dan sebagian dipisahkan ke inventaris baru.']);
                    }
                
            } else {
                // For id_jenis_barang other than 3, just update the room
                $inventaris->id_ruangan = $request->id_ruangan;
                $inventaris->save();

                return redirect()->back()->with(['success_message' => 'Barang Telah Dipindahkan.']);
            }
        }
    

    
    
    
    public function ruangans(Request $request) {
        // Separate the 'id_inventaris' array and ensure each element is an array of integers
        $idInventaris = $request->input('id_inventaris', []);
        $flattenedIds = [];
        foreach ($idInventaris as $ids) {
            $ids = explode(',', $ids);
            $flattenedIds = array_merge($flattenedIds, $ids);
        }
    
        // Validate each flattened ID to ensure it's an integer
        foreach ($flattenedIds as $id) {
            if (!is_numeric($id)) {
                // Log the problematic value
                Log::error("Invalid id_inventaris value: $id");
    
                // Return an error response if any element is not an integer
                return response()->json(['error' => 'Invalid data provided.'], 422);
            }
        }
    
    
        // Validate the remaining request data
        $request->validate([
            'id_ruangan' => 'required|integer',
        ]);
    
        // If all elements are integers and the other data is valid, proceed with your logic
        $idRuangan = $request->id_ruangan;
    
        // Perform the update operation in a single query
        $updatedRows = Inventaris::whereIn('id_inventaris', $flattenedIds)
        ->update(['id_ruangan' => $idRuangan]);
    
        // Check if the update was successful
        if ($updatedRows > 0) {
            // Set a success message
            $successMessage = 'Data telah berhasil diperbarui.';
            return redirect()->back()->with(['success_message' => $successMessage]);
        } else {
            // Set an error message if no rows were updated
            $errorMessage = 'Tidak ada data yang diperbarui.';
            return redirect()->back()->with(['error' => $errorMessage]);
        }
    }

    public function barcode($id_ruangan)
    {

    
        $id_ruangan = Ruangan::find($id_ruangan);
        // dd($id_ruangan);

      
        return view('inventaris.barcode', [ 'id_ruangan'   => $id_ruangan]);
    }

 
    public function AddBarcode(Request $request , $id_ruangan)   {

        // dd($request);
            $request->validate([
                'kode_barang' => 'required', // Ensure kode_barang is unique in the inventaris table
                'kondisi_barang' => 'required',
                'ket_barang' => 'nullable|max:50',
            ]);
            
            // Mendapatkan objek Inventaris berdasarkan id_ruangan dan id_barang
            $id_barang = Barang::where('kode_barang', $request->kode_barang)->first();
            // Pastikan Inventaris ditemukan sebelum melanjutkan
            if (!$id_barang) {
                return redirect()->back()->with(['error' => 'Data barang sudah diinventarisasikan.']);
            }    

            $existingInventaris = Inventaris::where('id_barang', $id_barang->id_barang)
                                     ->first();

          // If the combination already exists, return an error message
            if ($existingInventaris) {
                return redirect()->back()->with(['error' => 'Data barang sudah diinventarisasikan.']);
            }
            
            
            $inventaris = new Inventaris();
                $inventaris->id_barang = $id_barang->id_barang;
                $inventaris->id_ruangan = $id_ruangan;
                $inventaris->kondisi_barang = $request->kondisi_barang;
                $inventaris->ket_barang = $request->ket_barang; 
                $inventaris ->save();
            
          
                $id_ruangan  = $inventaris->id_ruangan;

                return redirect()->route('inventaris.showDetail', ["id_ruangan" => $id_ruangan])
                ->with(['success_message' => 'Data telah tersimpan.']);
    
    }

    public function showDetail($id_ruangan)
    {
        
        $ruangan = Ruangan::findOrFail($id_ruangan);
    
      
        // Ambil semua barang yang terkait dengan inventaris melalui ruangan
        $barangsInRuangan = $ruangan->barang;
    
        $barangEdit = Barang::orderByRaw("LOWER(nama_barang)")->pluck('nama_barang', 'id_barang');
        
        $inventariss = Inventaris::all();
        $inventaris = Inventaris::whereHas('barang', function ($query) use ($id_ruangan) {
            $query->where('id_ruangan', $id_ruangan);
        })->get();

        $inventarisAlat = Inventaris::whereHas('barang', function ($query) use ($id_ruangan) {
            $query->where('id_ruangan', $id_ruangan)
                ->where('id_jenis_barang', '!=', 3); 
        })->get();
    
        $inventarisBahan = Inventaris::whereHas('barang', function ($query) use ($id_ruangan) {
            $query->where('id_ruangan', $id_ruangan)
                ->where('id_jenis_barang', 3); 
        })->get();

        $used_ids = Inventaris::pluck('id_barang');

        $BarangAlat = Barang::where('id_jenis_barang', '!=', 3)
         ->whereNotIn('id_barang', $used_ids)
        ->get();

        $barangEdit = collect();

        // Loop through each inventaris item
        foreach ($inventarisAlat as $inventarisItem) {
            // Fetch all used id_barang values except for the one selected in the current inventaris item
            $used_ids_except_current = Inventaris::where('id_inventaris', '!=', $inventarisItem->id_inventaris)->pluck('id_barang');
        
            // Fetch all Barang objects
            $barangEditForItem = Barang::where('id_jenis_barang', '!=', 3)
                ->whereNotIn('id_barang', $used_ids_except_current->isEmpty() ? [0] : $used_ids_except_current->toArray()) // Handle empty $used_ids_except_current array
                ->get();
        
            // Merge the Barang objects into the main collection
            $barangEdit = $barangEdit->merge($barangEditForItem);
        }

        $inventarisRuanganAlat = Inventaris::whereHas('barang', function ($query) use ($id_ruangan) {
            $query->where('id_ruangan', $id_ruangan)
                ->where('id_jenis_barang', '!=', 3); 
        })->get();

        $inventarisRuanganBahan = Inventaris::whereHas('barang', function ($query) use ($id_ruangan) {
            $query->where('id_ruangan', $id_ruangan);
        })->get();

        
        $ruangans = Ruangan::all();

        $Barangbahan = Barang::where('id_jenis_barang',  3)->get();
        
        $Barangbahan = Barang::where('id_jenis_barang',  3)->get();

        $borrowedIds = DetailPeminjaman::where('status', 'dipinjam')
            ->whereIn('id_inventaris', $inventarisAlat->pluck('id_inventaris'))
            ->pluck('id_inventaris')
            ->toArray();

        $inventarisAlat->each(function ($item) use ($borrowedIds) {
            $item->status_pinjam = in_array($item->id_inventaris, $borrowedIds);
        });
        
        return view('inventaris.show', [
            'inventaris' => $inventaris,
            'inventariss' => $inventariss,
            'ruangan' => $ruangan,
            'ruangans' => $ruangans,
            'barangsInRuangan' => $barangsInRuangan,
            'barangEdit' => $barangEdit,
            'inventarisAlat' => $inventarisAlat,

            'inventarisRuanganAlat' => $inventarisRuanganAlat,
            'inventarisRuanganBahan' => $inventarisRuanganBahan,
            'inventarisBahan' => $inventarisBahan,
            'BarangAlat' => $BarangAlat,
            'barangEdit' => $barangEdit,
            'Barangbahan' => $Barangbahan,
            'used_ids' => $used_ids
        ]);
    }

    public function destroy($id_inventaris)
    {
    
        $inventaris = Inventaris::findOrFail($id_inventaris);
    
        if ($inventaris){
            $inventaris -> delete();
        }
       
        return redirect()->back()->with(['success_message' => 'Data telah terhapus.',]);

    }

    public function destroyRuangan($id_ruangan)
    {
        // Delete all records with the specified id_ruangan
        $deletedRows = Inventaris::where('id_ruangan', $id_ruangan)->delete();
        
        if ($deletedRows > 0) {
            return redirect()->back()->with(['success_message' => 'Data telah terhapus.']);
        } else {
            return redirect()->back()->with(['error' => 'Data tidak ditemukan.']);
        }
    }
}