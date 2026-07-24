<?php

namespace App\Http\Controllers;
use App\Models\Peminjaman;
use App\Models\DetailPeminjaman;
use App\Models\Inventaris;
use App\Models\Barang;
use App\Models\Ruangan;
use App\Models\User;
use App\Models\Guru;
use App\Models\Karyawan;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\PeminjamanExport;
use Maatwebsite\Excel\Facades\Excel;

class PeminjamanController extends Controller
{
    
    public function index(Request $request)
{
    $user = Auth::user();
    $peminjamanQuery = Peminjaman::query();

    // Filter by user level
    if ($user->level == 'siswa') {
        $peminjamanQuery->where('id_users', auth()->user()->id_users);
    }

    // Filter by date range
    $tanggal_awal = $request->input('tanggal_awal');
    $tanggal_akhir = $request->input('tanggal_akhir');
    if ($tanggal_awal && $tanggal_akhir) {
        $peminjamanQuery->whereBetween('tgl_pinjam', [$tanggal_awal, $tanggal_akhir]);
    }

    // Filter by selected id_barang
    $id_barang = $request->input('id_barang');
    if ($id_barang) {
        $peminjamanQuery->whereHas('detailPeminjaman.inventaris.barang', function ($query) use ($id_barang) {
            $query->where('id_barang', $id_barang);
        });
    }

    // Retrieve the filtered Peminjaman records
    $peminjaman = $peminjamanQuery->orderBy('id_peminjaman', 'desc')->with(['users.profile'])->get();
    session()->put('selected_id_barang', $id_barang);

    // Retrieve other necessary data
    $barang = Barang::where('id_jenis_barang', '!=', 3)->get();
    $hasDetailPeminjaman = DetailPeminjaman::exists();

    $users = User::where('id_users', '!=', 1)->with('profile')->orderByRaw("LOWER(name)")->get();
    

    $guru = Guru::where('id_guru', '!=', 1)->orderByRaw("LOWER(nama_guru)")->get();
    $karyawan = Karyawan::where('id_karyawan', '!=', 1)->orderByRaw("LOWER(nama_karyawan)")->get(); 

    return view('peminjaman.index', [
        'peminjaman' => $peminjaman,
        'barang' => $barang,
        'id_barang' => $id_barang,
        'hasDetailPeminjaman' => $hasDetailPeminjaman,
        'users' => $users,
        'guru' => $guru,
        'karyawan' => $karyawan,
    ]);
}


    public function create()
    {
      
        $peminjaman = Peminjaman::all();
        $id_users = $user = Auth::user();
        
        // Get the maximum id_inventaris for each id_ruangan
        $ruangan = Inventaris::select('id_ruangan', DB::raw('MAX(id_inventaris) as max_id_inventaris'))
            ->groupBy('id_ruangan')
            ->get();

          $id_barang_options = Inventaris::whereIn('id_ruangan', $ruangan->pluck('id_ruangan'))
            ->join('barang', 'inventaris.id_barang', '=', 'barang.id_barang')
            ->leftJoin('detail_peminjaman', 'inventaris.id_inventaris', '=', 'detail_peminjaman.id_inventaris')
            ->whereNotIn('barang.id_jenis_barang', [3]) // Exclude specific jenis_barang
            ->where(function($query) {
                $query->whereNull('detail_peminjaman.status') // Include cases with no status (not borrowed)
                    ->orWhere('detail_peminjaman.status', '!=', 'dipinjam'); // Exclude borrowed status
            })
            ->select('inventaris.id_barang', DB::raw('MAX(inventaris.id_inventaris) as max_id_inventaris'))
            ->groupBy('inventaris.id_barang')
            ->get();
        
        $barang =  Barang::where('id_jenis_barang', '!=', 3)->get();
    
        $users = User::where('level', 'siswa' )->whereNotIn('id_users', [1])
            ->orderByRaw("LOWER(name)")
            ->get();
        $guru = Guru::where('id_guru', '!=', 1)  
        ->orderByRaw("LOWER(nama_guru)")  
        ->get(); 
        $karyawan = Karyawan::where('id_karyawan', '!=', 1)  
        ->orderByRaw("LOWER(nama_karyawan)")  
        ->get(); 
        $peminjamans = Peminjaman::with('detailPeminjaman')->get();
        
        
        
        return view('peminjaman.create', [
            'peminjaman' => $peminjaman,
            'peminjamans' => $peminjamans,
            'users' => $users,
            'guru' => $guru,
            'karyawan' => $karyawan,
            'ruangan' => $ruangan,
            'id_barang_options' => $id_barang_options,
            'barang' => $barang,
            'id_users' => $id_users,
        
            
        ]);
    }

    public function notifikasi(Request $request)
{
    $peminjamans = Peminjaman::whereHas('detailPeminjaman', function ($query) {
        $query->where('status', 'dipinjam');
    })->with('users')->get();

    foreach ($peminjamans as $peminjaman) {
        // Assuming tgl_kembali is the return date column
        $tgl_kembali = $peminjaman->tgl_kembali;

        // Check if the return date has passed
        if (Carbon::now()->greaterThan($tgl_kembali)) {
            $peminjaman->status = 'dipinjam';
            $peminjaman->save();

            $pengguna = $peminjaman->users;
            if (!$pengguna) continue;
            $notifikasi = new Notifikasi();
            $notifikasi->judul = 'Peminjaman Barang ';
            $notifikasi->pesan = 'Peminjaman barang yang Anda lakukan belum dikembalikan. Mohon segera mengembalikan barang yang dipinjam untuk mencegah keterlambatan.';
            $notifikasi->is_dibaca = 'tidak_dibaca';
            $notifikasi->label = 'info';
            $notifikasi->send_email = 'yes';
            $notifikasi->link = '/peminjaman';  
            $notifikasi->id_users = $pengguna->id_users;
            $notifikasi->save();

            $notifikasiTeknisi = User::where('level', 'teknisi')->get();
            
            foreach ($notifikasiTeknisi as $na) {
                $notifikasi = new Notifikasi();
                $notifikasi->judul = 'Peminjaman Barang ';
                $notifikasi->pesan =  'Peminjaman dari '.$pengguna->name.' sudah melewati batas waktu peminjaman.'; 
                $notifikasi->is_dibaca = 'tidak_dibaca';
                $notifikasi->label = 'info';
                $notifikasi->link = '/peminjaman';
                $notifikasi->send_email = 'no';
                $notifikasi->id_users = $na->id_users;
                $notifikasi->save();
            }
        }
    }

    // Redirect after processing notifications
    return response()->json(['success' => true, 'message' => 'Notifikasi telah terkirim.']);
}   
 

    public function Barcode()
    {
      
        $peminjaman = Peminjaman::all();
        $id_users = $user = Auth::user();
        
        
        // Get the maximum id_inventaris for each id_ruangan
        $ruangan = Inventaris::select('id_ruangan', DB::raw('MAX(id_inventaris) as max_id_inventaris'))
            ->groupBy('id_ruangan')
            ->get();
    
        // Get the maximum id_inventaris for each id_barang within the selected id_ruangan
        $id_barang_options = Inventaris::whereIn('id_ruangan', $ruangan->pluck('id_ruangan'))
            ->select('id_barang', DB::raw('MAX(id_inventaris) as max_id_inventaris'))
            ->groupBy('id_barang')
            ->get();
      
        $users = User::where('id_users', '!=', 1)  
        ->orderByRaw("LOWER(name)")  
        ->get(); 
        $guru = Guru::where('id_guru', '!=', 1)  
        ->orderByRaw("LOWER(nama_guru)")  
        ->get(); 
        $karyawan = Karyawan::where('id_karyawan', '!=', 1)  
        ->orderByRaw("LOWER(nama_karyawan)")  
        ->get(); 
        $peminjamans = Peminjaman::with('detailPeminjaman')->get();
        $idPeminjaman = session('id_peminjaman');
        
        
        return view('peminjaman.barcode', [
            'peminjaman' => $peminjaman,
            'peminjamans' => $peminjamans,
            'users' => $users,
            'guru' => $guru,
            'karyawan' => $karyawan,
            'ruangan' => $ruangan,
            'id_barang_options' => $id_barang_options,
            'idPeminjaman' => $idPeminjaman,
            'id_users' => $id_users
            
        ]);
    }

    public function Qrcode($id_peminjaman)
    {
        $id_peminjaman = Peminjaman::find($id_peminjaman);
        return view('peminjaman.add', [ 'id_peminjaman'   => $id_peminjaman]);
    }


    
    public function showDetail($id_peminjaman)
    {
        $peminjaman = Peminjaman::with(['users.profile'])->find($id_peminjaman);
        $detailPeminjamans = DetailPeminjaman::with(['inventaris.barang', 'inventaris.ruangan'])
            ->where('id_peminjaman', $id_peminjaman)
            ->get();
        $ruangan = Inventaris::select('id_ruangan', DB::raw('MAX(id_inventaris) as max_id_inventaris'))
        ->groupBy('id_ruangan')
        ->get();
        $id_barang_options = Inventaris::whereIn('id_ruangan', $ruangan->pluck('id_ruangan'))
            ->join('barang', 'inventaris.id_barang', '=', 'barang.id_barang')
            ->leftJoin('detail_peminjaman', 'inventaris.id_inventaris', '=', 'detail_peminjaman.id_inventaris')
            ->whereNotIn('barang.id_jenis_barang', [3]) // Exclude specific jenis_barang
            ->where(function($query) {
                $query->whereNull('detail_peminjaman.status') // Include cases with no status (not borrowed)
                    ->orWhere('detail_peminjaman.status', '!=', 'dipinjam'); // Exclude borrowed status
            })
            ->select('inventaris.id_barang', DB::raw('MAX(inventaris.id_inventaris) as max_id_inventaris'))
            ->groupBy('inventaris.id_barang')
            ->get();
        // dd($id_barang_options);
        $id_barang_edit = Inventaris::whereIn('id_ruangan', $ruangan->pluck('id_ruangan'))
        ->join('barang', 'inventaris.id_barang', '=', 'barang.id_barang')
        ->leftJoin('detail_peminjaman', 'inventaris.id_inventaris', '=', 'detail_peminjaman.id_inventaris')
        ->whereNotIn('barang.id_jenis_barang', [3])
        ->get();
        
        $ruangans = Ruangan::all();
        $id_users = $user = Auth::user();
      
    
        return view('peminjaman.show', [
            'peminjaman' => $peminjaman,
            'detailPeminjamans' => $detailPeminjamans,
            'detailPeminjaman' => $detailPeminjamans,
            'id_barang_options' => $id_barang_options,
            'id_barang_edit' => $id_barang_edit,
            'ruangan' => $ruangan,
            'ruangans' => $ruangans,
            'id_users' => $id_users
            
           
        ]);
    }

   

    public function fetchIdBarang($id_barang) {
        $id_ruangan_option = Inventaris::where('id_barang', $id_barang)
        ->select('id_ruangan') // Pilih kolom 'kondisi_barang' di sini
        ->distinct()
        ->with(['ruangan:id_ruangan,nama_ruangan']) // Specify the columns you want
        ->get();
    
        return response()->json($id_ruangan_option);
    }

    public function fetchNamaBarang($id_barang)
    {
        $namaBarangOptions = Inventaris::where('id_barang', $id_barang)
            ->select('id_barang') // Select both columns
            ->distinct()
            ->with(['barang:id_barang,nama_barang']) 
            ->get();
    
        return response()->json($namaBarangOptions);
    }

    public function fetchSiswa($id_users)
    {
       
        $user = User::find($id_users);
    
        if (!$user) {
          
            return response()->json(['error' => 'User not found for the given ID.'], 404);
        }
    
        // Fetch the profile associated with the user
        $profile = $user->profile;
    
        if ($profile) {
         
            return response()->json([
                'nis' => $profile->nis,
                'kelas' => $profile->kelas,
                'jurusan' => $profile->jurusan
            ]);
        } else {
          
    
            return response()->json(['error' => 'Profile not found for the given user.'], 404);
        }
    }

        public function fetchGuru($id_guru)
    {
        // Fetch the guru based on id_guru
        $guru = Guru::findOrFail($id_guru);

        // Extract the required details
        $nip = $guru->nip;
        $jurusan = $guru->jurusan;

        return response()->json([
            'nip' => $nip,
            'jurusan' => $jurusan
        ]);
    }

    public function fetchPemakaianData($id_peminjaman){
        
        $peminjaman = Peminjaman::where('id_peminjaman', $id_peminjaman)->first();

        return response()->json([
            'id_users' => $peminjaman->id_users,    // Ganti dengan nilai sesuai kebutuhan
            'id_guru' => $peminjaman->id_guru,    // Ganti dengan nilai sesuai kebutuhan
            'id_karyawan' => $peminjaman->id_karyawan, // Ganti dengan nilai sesuai kebutuhan
            'tgl_kembali' => $peminjaman->tgl_kembali,             // Ganti dengan nilai sesuai kebutuhan
            'keterangan_peminjaman' => $peminjaman->keterangan_peminjaman,             // Ganti dengan nilai sesuai kebutuhan
            'status' => $peminjaman->status,             // Ganti dengan nilai sesuai kebutuhan
        ]);
    }

  

    public function store(Request $request)
{
    try {
        $request->validate([
            'id_users' => 'nullable',
            'id_karyawan' => 'nullable',
            'id_guru' => 'nullable',
            'status' => 'nullable',
            'keterangan_peminjaman' => 'nullable|max:50',
            'tgl_kembali' => 'required|date|after_or_equal:tgl_pinjam',
        ]);
    } catch (ValidationException $e) {
        return response()->json(['error' => $e->validator->errors()], 422);
    }

    $id_users = $request->filled('id_users') ? $request->id_users : 1;
    $id_karyawan = $request->filled('id_karyawan') ? $request->id_karyawan : 1;
    $id_guru = $request->filled('id_guru') ? $request->id_guru : 1;

    if ($id_users == 1 && $id_karyawan == 1 && $id_guru == 1) {
        return response()->json(['error' => 'Nama Lengkap Belum Diisi.'], 400);
    }

    $peminjaman = new Peminjaman([
        'id_users' => $id_users,
        'id_guru' => $id_guru,
        'id_karyawan' => $id_karyawan,
        'status' => $request->status,
        'tgl_kembali' => $request->tgl_kembali,
        'keterangan_peminjaman' => $request->keterangan_peminjaman,
    ]);
    $peminjaman->save();

    if ($request->ajax()) {
        return response()->json(['id_peminjaman' => $peminjaman->id_peminjaman, 'message' => 'Peminjaman berhasil disimpan']);
    } else {
        return redirect()->back()->with(['success_message' => 'Data telah tersimpan.']);
    }
}


   
    public function update(Request $request, $id_peminjaman)
    {
        try {
            $request->validate([
                'id_users' => 'nullable',
                'id_karyawan' => 'nullable',
                'id_guru' => 'nullable',
                'status' => 'nullable',
                'keterangan_peminjaman' => 'nullable|max:50',
                'tgl_kembali' => 'required|date|after_or_equal:tgl_pinjam',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        }
        
        try {
            $peminjaman = Peminjaman::findOrFail($id_peminjaman);
        } catch (ModelNotFoundException $ex) {
            return response()->json(['error' => 'Peminjaman not found'], 404);
        }
        $id_users = $request->filled('id_users') ? $request->id_users : 1;
        $id_karyawan = $request->filled('id_karyawan') ? $request->id_karyawan : 1;
        $id_guru = $request->filled('id_guru') ? $request->id_guru : 1;
        
        if(Auth::user()->level == 'siswa'){
            $tgl_pinjam = $peminjaman->tgl_pinjam;
        }else{
            $tgl_pinjam = now();
        }

        // dd($request->all());
        // Update existing record
        $peminjaman->update([
            'id_users' => $id_users,
            'id_guru' => $id_guru,
            'id_karyawan' => $id_karyawan,
            'status' => $request->status,
            'tgl_pinjam' => $tgl_pinjam,
            'tgl_kembali' => $request->tgl_kembali,
            'keterangan_peminjaman' => $request->keterangan_peminjaman,
        ]);
        
        if ($request->ajax()) {
            return response()->json(['id_peminjaman' => $peminjaman->id_peminjaman, 'message' => 'Peminjaman berhasil disimpan']);
        } else {
            return redirect()->back()->with(['success_message' => 'Data telah tersimpan.']);
        }
    }
    
    public function fetchPeminjamanStatus($id_peminjaman)
    {
        $peminjaman = Peminjaman::findOrFail($id_peminjaman);

        // Return the status of the fetched Peminjaman record
        return response()->json(['status' => $peminjaman->status]);
    }

    public function export(Request $request)
    {
        $defaultStartDate = '2023-01-01';
        $defaultEndDate = '2023-12-31';
    
        $tglawal = $request->input('tglawal', $defaultStartDate);
        $tglakhir = $request->input('tglakhir', $defaultEndDate);
        $id_barang = $request->input('id_barang');
 
        
        // Cek apakah tglawal dan tglakhir diberikan atau tidak
        if ($request->filled('tglawal') && $request->filled('tglakhir')) {
            $peminjamans = Peminjaman::with(['users', 'guru', 'karyawan'])
                ->whereBetween('tgl_pakai', [$tglawal, $tglakhir])
                ->orderBy('id_users')
                ->orderBy('id_guru')
                ->orderBy('id_karyawan')
                ->orderBy('tgl_pinjam')
                ->get();
        } elseif ($request->filled('id_barang') ){
            $peminjamans = Peminjaman::with(['users', 'guru', 'karyawan'])
            ->whereHas('detailPeminjaman.inventaris.barang', function ($query) use ($id_barang) {
                $query->where('id_barang', $id_barang);
            })
            ->orderBy('id_users')
            ->orderBy('id_guru')
            ->orderBy('id_karyawan')
            ->orderBy('tgl_pinjam')
            ->get();

        } elseif($request->filled('tglawal') && $request->filled('tglakhir') && $request->filled('id_barang')) {
          
                $peminjamans = Peminjaman::with(['users', 'guru', 'karyawan'])
                ->whereHas('detailPeminjaman.inventaris.barang', function ($query) use ($id_barang) {
                    $query->where('id_barang', $id_barang);
                })
                ->whereBetween('tgl_pakai', [$tglawal, $tglakhir])
                ->orderBy('id_users')
                ->orderBy('id_guru')
                ->orderBy('id_karyawan')
                ->orderBy('tgl_pinjam')
                ->get();
        }else{
            $peminjamans = Peminjaman::with(['users', 'guru', 'karyawan'])
                ->orderBy('id_users')
                ->orderBy('id_guru')
                ->orderBy('id_karyawan')
                ->orderBy('tgl_pinjam')
                ->get();
        }
    
        $dataDetail = DetailPeminjaman::with(['inventaris.barang'])
            ->whereIn('id_peminjaman', $peminjamans->pluck('id_peminjaman'))
            ->get();
    
        return Excel::download((new PeminjamanExport)
            ->setPeminjamans($peminjamans)
            ->setDataDetail($dataDetail)
            ->setBarang($id_barang)
            ->setStartDate($tglawal)
            ->setEndDate($tglakhir), 'peminjaman.xlsx');
    }
    


    
    public function filter(Request $request)
    {
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        $barang_id = $request->input('barang_id');
    
        // Query to filter Peminjaman records
        $peminjamanQuery = Peminjaman::whereBetween('tgl_pinjam', [$tanggal_awal, $tanggal_akhir]);
    
        // If barang_id is provided, add condition to filter by barang_id
        if ($barang_id) {
            $peminjamanQuery->where('id_barang', $barang_id);
        }
    
        // Fetch filtered Peminjaman records
        $filteredPeminjaman = $peminjamanQuery->get();
    
        // Return the filtered Peminjaman records as JSON response or to a view
        return view('peminjaman.index', compact('filteredPeminjaman'));
    } 
  
 

    Public function destroy($id_peminjaman)
    {
        $peminjaman = Peminjaman::find($id_peminjaman);

     
        DetailPeminjaman::where('id_peminjaman', $id_peminjaman)->delete();
        $peminjaman->delete();
        if (request()->ajax()) {
        return response()->json(['success' => true, 'message' => 'Data telah terhapus.']);
        }else{
        return redirect()->route('peminjaman.index')->with('success_message', 'Data telah terhapus.');
        }
    }
    
}