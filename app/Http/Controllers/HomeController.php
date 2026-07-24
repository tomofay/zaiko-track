<?php

namespace App\Http\Controllers;
use App\Models\Barang;
use App\Models\DetailPeminjaman;
use App\Models\Inventaris;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $users = User::count();
        $peminjaman = DetailPeminjaman::where('status', 'dipinjam')->count();
        $jumlahBarangRusak = Inventaris::where('kondisi_barang', 'rusak')->count();
        $perlengkapan = Barang::where('id_jenis_barang', '1')->count();
        $alatPraktik = Barang::where('id_jenis_barang', '2')->count();
        $bahanPraktik = Barang::where('id_jenis_barang', '3')->sum('stok_barang');
        $inventaris = Inventaris::selectRaw('SUM(COALESCE(jumlah_barang, 1)) as total_jumlah_barang')
            ->value('total_jumlah_barang');
        if($inventaris == null){
            $inventaris = 0;
        }
        $jadwalKembali = DetailPeminjaman::with(['peminjaman', 'inventaris.barang'])
            ->where('detail_peminjaman.status', 'dipinjam')
            ->join('peminjaman', 'detail_peminjaman.id_peminjaman', '=', 'peminjaman.id_peminjaman')
            ->orderBy('peminjaman.tgl_kembali')
            ->limit(15)
            ->get();   

        $user = Auth::user();
        if ($user->level == 'siswa') {
            $jadwals = $user->peminjaman()
            ->whereHas('detailPeminjaman', function ($query) use ($user) {
                $query->where('id_users', $user->id_users);
            })
            ->with(['detailPeminjaman.inventaris.barang'])->limit(15)->get();
        } else {
            $jadwals = $jadwalKembali->groupBy('id_peminjaman');
        }
        
        return view('home',[
            'inventaris' => $inventaris,
            'peminjaman' => $peminjaman,
            'barangRusak' => $jumlahBarangRusak,
            'users' => $users,
            'perlengkapan' => $perlengkapan,
            'alatPraktik' => $alatPraktik,
            'bahanPraktik' => $bahanPraktik,
            'jadwals'=> $jadwals,
            'user' => $user,
        ]);
    }
}