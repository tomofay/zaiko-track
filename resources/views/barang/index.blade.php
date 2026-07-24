@extends('layouts.demo')
@section('title', 'List Barang')
@section('css')
<link rel="stylesheet" href="{{asset('fontawesome-free-6.4.2-web\css\all.min.css')}}">
<style>
    .nav-pills {
        height: 50px; /* Sesuaikan tinggi sesuai kebutuhan */
        color: aqua;
    }

    .nav-pills .nav-link {
        height: 90%; /* Sesuaikan tinggi sesuai kebutuhan */
        font-size: 18px; /* Sesuaikan ukuran font sesuai kebutuhan */
    }
</style>

@endsection
@section('breadcrumb-name')
Barang
@endsection
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-2">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-md-7 mt-2">
                            <h4 class="text-dark">List Barang</h4>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-12 text-end">                            
                            <ul class="nav nav-pills nav-fill p-1" role="tablist">
                                <li class="nav-item" id="option1">
                                    <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center active" data-bs-toggle="tab" href="javascript:;" role="tab" aria-selected="false">
                                        <i class="fa-solid fa-screwdriver-wrench"></i>
                                        <span class="ms-2">Alat & Perlengkapan</span>
                                    </a>
                                </li>
                                <li class="nav-item" id="option2">
                                    <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center " data-bs-toggle="tab" href="javascript:;" role="tab" aria-selected="false">
                                        <i class="fa-solid fa-suitcase"></i>
                                        <span class="ms-2">Bahan Praktik</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div id="tableAlatPerlengkapan" class="card-body m-0">
                    <div class="mb-2 d-flex justify-content-between">
                        <div>
                            <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#addModalPeralatan">Tambah</button>
                        </div>
                        <div>
                            <button class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#modalPrint">Print Label</button>
                            <a href="{{route('barang.exportAlatPerlengkapan')}}" class="btn btn-danger mb-2">Export Excel</a>
                        </div>
                    </div>
                    <div class="modal fade" id="modalPrint" role="dialog" >
                        <div class="modal-dialog modal-lg modal-dialog-centered  modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Pilih Barang</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <i class="fa fa-close" style="color: black;"></i>
                                    </button>
                                </div>
                                <div class="modal-body form">
                                    <form action='{{route("barang.selectPrint")}}' method="POST" id="form" class="form-horizontal" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-body">
                                            <div class="row">
                                                <div class="table-responsive">
                                                    <div class="form-check form-switch" style="display: flex; justify-content: flex-end;">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="selectAll">
                                                        <label class="form-check-label" for="selectAll">
                                                            Pilih Semua
                                                        </label>
                                                      </div>
                                                    <table id="selectTable" class="table table-bordered table-striped align-items-center mb-0" id="example2">
                                                        <thead>
                                                            <tr>
                                                                <th>No.</th>
                                                                <th>Nama Barang</th>
                                                                <th>Kode Barang</th>
                                                                <th>Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($alatdanperlengkapan as $key => $ap)
                                                                <tr>
                                                                    <td>{{$key+1}}</td>
                                                                    <td>{{$ap->nama_barang}}
                                                                    <td>{{$ap->kode_barang}}
                                                                    <td class="align-items-center">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" value="{{$ap->id_barang}}" name="id_barang[]" id="flexCheckDefault">
                                                                      </div>                                                                    
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Print</button>
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                        <div class="table-responsive ">
                            <table id="myTable" class="table table-bordered table-striped align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Barang</th>
                                        <th>Merek</th>
                                        <th>Kode Barang</th>
                                        <th>Qrcode</th>
                                        <th style="text-align: center;">
                                            Terinventarisasi <a href="{{ route('inventaris.index') }}" class="fas fa-link"></a>
                                        </th>
                                        <th>Jenis Barang</th>
                                        <th>Status</th>
                                        <th style="width:189px;">Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alatdanperlengkapan as $key => $br)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$br->nama_barang}}</td>
                                        <td>{{$br->merek}}</td>
                                        <td>{{$br->kode_barang}}</td>
                                        <td>
                                            <a href="{{ asset('/storage/qrcode/'. $br->qrcode_image) }}" download>
                                                <img  src="{{ asset('/storage/qrcode/' . $br->qrcode_image) }}" style="width: 50px;">
                                            </a>
                                        </td>
                                        <td style="text-align: center">
                                            @if($br->inventaris->isNotEmpty())
                                                <span class="badge bg-gradient-success">Sudah</span>
                                            @else
                                                <span class="badge bg-gradient-secondary">Belum</span>
                                            @endif                                     
                                        </td>
                                        <td>{{$br->jenisbarang->nama_jenis_barang}}</td>
                                        <td>
                                            @if($br->is_dipinjam)
                                                <span class="badge bg-gradient-warning">Dipinjam</span>
                                            @else
                                                <span class="badge bg-gradient-success">Tersedia</span>
                                            @endif
                                        </td>
                                        
                                        <td>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-primary btn-xs edit-button" data-bs-toggle="modal" data-bs-target="#editModalPerlengkapan{{$br->id_barang}}"
                                                    data-id="{{$br->id_barang}}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="{{ route('barang.destroy', $br->id_barang) }}" onclick="notificationBeforeDelete(event, this, {{$key+1}})"
                                                    class="btn btn-danger btn-xs" style="margin-left: 5px; margin-right: 5px;">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                                <a href="{{ route('barang.print', $br->id_barang) }}" class="btn btn-success btn-xs">
                                                    <i class="fa-solid fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                </div> 
                <div id="tableBahanPraktik" class="card-body m-0">
                    <div class="mb-2 d-flex justify-content-between">
                        <div>
                            <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#addModalBahan">Tambah</button>
                        </div>
                        <div>
                            <a href="{{route('barang.exportBahan')}}" class="btn btn-danger mb-2">Export Excel</a>
                        </div>
                    </div>
                        <div class="table-responsive ">
                            <table id="myTable2" class="table table-bordered table-striped align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Barang</th>
                                        <th>Merek</th>
                                        <th style="text-align: center;">Total Stok<br>Barang</th>
                                        <th>Jumlah Barang</th>
                                        <th style="text-align: center;">
                                            Terinventarisasi <a href="{{ route('inventaris.index') }}" class="fas fa-link"></a>
                                        </th>
                                        {{-- <th>Jenis Barang</th> --}}
                                        <th style="width:189px;">Opsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bahan as $key => $br)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$br->nama_barang}}</td>
                                        <td>{{$br->merek}}</td>
                                        <td>{{ $updatedStokBarang[$br->id_barang] ?? $br->stok_barang}}</td>   
                                        <td>{{$br->stok_barang}}</td>
                                        <td>{{ $totals[$br->id_barang] ?? '-'}}</td>
                                        {{-- <td>{{$br->jenisbarang->nama_jenis_barang}}</td> --}}
                                        <td>
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-primary btn-xs edit-button" data-bs-toggle="modal" data-bs-target="#editModalBahan{{$br->id_barang}}"
                                                    data-id="{{$br->id_barang}}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="{{ route('barang.destroy', $br->id_barang) }}" onclick="notificationBeforeDelete(event, this, {{$key+1}})"
                                                    class="btn btn-danger btn-xs mx-1">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModalPeralatan" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Tambah Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-close" style="color: black;"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addForm" action="{{route('barang.store')}}" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="nama_barang">Nama Barang</label>
                        <input type="text" maxlength="35" name="nama_barang" id="nama_barang" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="merek">Merek</label>
                        <input type="text" name="merek" id="merek" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="kode_barang">Kode Barang</label>
                        <input type="text" name="kode_barang" id="kode_barang" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="jenis_barang">Jenis Barang</label>
                        <select class="form-select" name="id_jenis_barang" id="id_jenis_barang" required>
                            @foreach($jenisBarang as $key => $jb)
                            <option value="{{$jb->id_jenis_barang}}" @if( old('id_jenis_barang')==$jb->
                                id_jenis_barang)selected @endif>
                                {{$jb->nama_jenis_barang}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addModalBahan" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Tambah barang bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-close" style="color: black;"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addForm" action="{{route('barang.store')}}" method="post">
                    @csrf
                    <input type="hidden" name="id_jenis_barang" value="3">
                    <div class="form-group">
                        <label for="nama_barang">Nama Barang</label>
                        <input type="text" name="nama_barang" id="nama_barang" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="merek">Merek</label>
                        <input type="text" name="merek" id="merek" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stok_barang">Stok Barang</label>
                        <input type="number" name="stok_barang" id="stok_barang" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    @foreach($alatdanperlengkapan as $key => $br)
    <div class="modal fade" id="editModalPerlengkapan{{$br->id_barang}}" tabindex="-1" role="dialog"
        aria-labelledby="editModalLabel{{$br->id_barang}}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-close" style="color: black;"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addForm" action="{{route('barang.update', $br->id_barang)}}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" name="nama_barang" id="nama_barang" class="form-control"
                                value="{{old('nama_barang', $br->nama_barang)}}">
                        </div>
                        <div class="form-group">
                            <label for="merek">Merek</label>
                            <input type="text" name="merek" id="merek" class="form-control"
                                value="{{old('merek', $br->merek)}}" required>
                        </div>
                        <div class="form-group">
                            <label for="kode_barang">Kode Barang</label>
                            <input type="text" name="kode_barang" id="kode_barang" class="form-control"
                            value="{{old('kode_barang', $br->kode_barang)}}">
                        </div>
                        <div class="form-group">
                            <label for="jenis_barang">Jenis Barang</label>
                            <select class="form-select" name="id_jenis_barang" id="id_jenis_barang" required>
                                @foreach($jenisBarang as $key => $jb)
                                <option value="{{$jb->id_jenis_barang}}" @if( old('id_jenis_barang')==$jb->
                                    id_jenis_barang)selected @endif>
                                    {{$jb->nama_jenis_barang}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    @foreach($bahan as $key => $br)
    <div class="modal fade" id="editModalBahan{{$br->id_barang}}" tabindex="-1" role="dialog"
        aria-labelledby="editModalLabel{{$br->id_barang}}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-close" style="color: black;"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addForm" action="{{route('barang.update', $br->id_barang)}}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" name="nama_barang" id="nama_barang" class="form-control"
                                value="{{old('nama_barang', $br->nama_barang)}}">
                        </div>
                        <div class="form-group">
                            <label for="merek">Merek</label>
                            <input type="text" name="merek" id="merek" class="form-control"
                                value="{{old('merek', $br->merek)}}" required>
                        </div>
                        <div class="form-group">
                            <label for="stok_barang">Stok Barang</label>
                            <input type="number" name="stok_barang" id="stok_barang" class="form-control"
                                value="{{old('stok_barang', $br->stok_barang)}}" required>
                        </div>
                        <div class="form-group">
                            <label for="jenis_barang" style="display: none;">Jenis Barang</label>
                            <input type="hidden" name="id_jenis_barang" value="3">
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach


@stop
@push('js')

<form action="" id="delete-form" method="post">
    @method('delete')
    @csrf
</form>
<script>
$(document).ready(function() {

    $('#myTable').DataTable({
        "fixedHeader": true,
        "responsive": true,
        "language": {
            "paginate": {
                "previous": "<",
                "next": ">"
            }
        }
    });

    $('#myTable2').DataTable({
        "responsive": true,
        "language": {
            "paginate": {
                "previous": "<",
                "next": ">"
            }
        }
    });
    $('#selectTable').DataTable({
        "responsive": true,
        "info": false,
        "ordering": false,
        "paging": false
    });
    function toggleCheckboxes() {
        var checkboxes = document.querySelectorAll('#selectTable tbody input[type="checkbox"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = document.getElementById('selectAll').checked;
        });
    }

    // Tambahkan event listener ke checkbox "Pilih Semua"
    document.getElementById('selectAll').addEventListener('change', toggleCheckboxes);
    });

    
    document.addEventListener('DOMContentLoaded', function () {
        // Simpan referensi ke elemen-elemen yang diperlukan
        const option1 = document.getElementById('option1');
        const option2 = document.getElementById('option2');
        const tableAlatPerlengkapan = document.getElementById('tableAlatPerlengkapan');
        const tableBahanPraktik = document.getElementById('tableBahanPraktik');

        // Tentukan fungsi untuk menampilkan atau menyembunyikan tabel berdasarkan radio button yang dipilih
        function handleRadioChange() {
            if (option1.classList.contains('active')) {
                tableAlatPerlengkapan.style.display = 'block';
                tableBahanPraktik.style.display = 'none';
            } else if (option2.classList.contains('active')) {
                tableAlatPerlengkapan.style.display = 'none';
                tableBahanPraktik.style.display = 'block';
            }
        }

        // Tambahkan kelas active secara langsung dan panggil handleRadioChange
        option1.classList.add('active');
        handleRadioChange();

        // Tambahkan event listener untuk perubahan pada radio button
        option1.addEventListener('click', function() {
            option1.classList.add('active');
            option2.classList.remove('active');
            handleRadioChange();
        });

        option2.addEventListener('click', function() {
            option2.classList.add('active');
            option1.classList.remove('active');
            handleRadioChange();
        });
    });

    function scrollToTable() {
        // Temukan elemen tabel target berdasarkan ID
        var targetTable = document.getElementById('myTable2');
        consol.log(targetTable);
        // Lakukan scroll ke tabel target
        targetTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

</script>
@endpush