@extends('layouts.demo')
@section('title', 'List Peminjaman')
@section('css')
<link rel="stylesheet" href="{{asset('dist\css\selectize.bootstrap5.css')}}">
<style>
    .form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  margin-bottom: 8px;
}

.form-input-group {
  display: flex;
}

.form-input-text1{
  width: 380px; /* Sesuaikan lebar input field sesuai kebutuhan */
  margin-right: 16px;
}
.form-input-text{
  width: 380px; /* Sesuaikan lebar input field sesuai kebutuhan */
}

</style>
@endsection
@section('breadcrumb-name')
Peminjaman
@endsection
@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h4 class="m-0 text-dark">List Peminjaman</h4>
                </div>
                <div class="card-body m-0">
                    <div class="row align-items-end">
                      
                            
                        
                    </div>
                
                    <div class="row">
                        <div class="d-flex">
                            <div class="col-4 col-md-6 mb-2">
                                <button class="btn btn-primary mb-2"
                                    onclick="notificationBeforeAdds(event, this)">Tambah</button>
                            </div>
                         
                        </div>

                    </div>

                    <div class="table-responsive ">
                        <table id="myTable" class="table table-bordered table-striped align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Tanggal Pinjam</th>
                                    @cannot('isSiswa')
                                    <th>Nama</th>
                                    <th>Jurusan</th>
                                    @endcan
                                    <th>List Barang</th>
                                    <th>Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                              
                                @foreach($peminjaman as $key => $peminjaman)
                                <tr>
                                    <td></td>
                                    <td>{{\Carbon\Carbon::parse($peminjaman->tgl_pinjam)->format('d F Y')}}</td>
                                    @cannot('isSiswa')
                                    @if ($peminjaman->status == 'guru')
                                    <td>{{ $peminjaman->guru ? $peminjaman->guru->nama_guru : 'N/A' }}</td>
                                    @elseif ($peminjaman->status == 'karyawan')
                                    <td>{{ $peminjaman->karyawan ? $peminjaman->karyawan->nama_karyawan : 'N/A' }}
                                    </td>
                                    @else
                                    <td>{{ $peminjaman->users ? $peminjaman->users->name : 'N/A' }}</td>
                                    @endif

                                   
                                    @if($peminjaman->status == "siswa")
                                   
                                        
                                    <td>{{ optional($peminjaman->users->profile)->kelas }} {{ optional($peminjaman->users->profile)->jurusan }}</td>                                     
                                   
                                     @else  
                                     <td>
                                        <div style='display: flex; justify-content: center;'>-
                                        </div>


                                    </td>
                                    @endif
                                    @endcan
                                    <td>

                                        <a href="{{ route('peminjaman.showDetail', $peminjaman->id_peminjaman) }}"
                                            class="btn btn-info btn-xs mx-1">
                                            <i class="fa fa-rectangle-list"></i>
                                        </a>
                                    </td>
                                    <td>
                                        @if ($hasDetailPeminjaman)
                                        {{-- @include('components.action-buttons', ['id' =>  $peminjaman->id_peminjaman, 'key'
                                        => $key, 'route' => 'peminjaman']) --}}
                                        <a href="#" class="btn btn-primary btn-xs edit-button" data-toggle="modal" data-target="#editModal{{$peminjaman->id_peminjaman}}"
                                            data-id="{{$peminjaman->id_peminjaman}}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="{{ route('peminjaman.destroy', $peminjaman->id_peminjaman) }}" onclick="notificationBeforeDelete(event, this, {{$key+1}})"
                                            class="btn btn-danger btn-xs mx-1">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        @else
                                        <div style='display: flex; justify-content: center;'>
                                            <span><i class="fas fa-check-circle fa-2x"
                                                    style="color: #42e619; align-items: center;"></i></span>
                                        </div>
                                        @endif
                                    </td>
                                    <!-- Modal Edit Pegawai -->
                                    <div class="modal fade" id="editModal{{$peminjaman->id_peminjaman}}" tabindex="-1"
                                        role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel">Edit
                                                        Peminjaman</h5>
                                                    <button type="button" class="btn-close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <i class="fa fa-close" style="color: black;"></i>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form id="updateForm{{ $peminjaman->id_peminjaman }}" data-id-peminjaman="{{ $peminjaman->id_peminjaman }}"
                                                        action="{{ route('peminjaman.update', $peminjaman->id_peminjaman) }}"
                                                        method="post" enctype="multipart/form-data">
                                                        @csrf
                                                        @method('PUT')
                                                        @if(auth()->user()->level == "siswa")
                                                            <input type="hidden" name="status" value="siswa">
                                                            <input type="hidden" name="id_users" value="{{ auth()->user()->id_users }}">
                                                        @else
                                                            <div class="form-group">
                                                                <label for="status{{$peminjaman->id_peminjaman}}">Status</label>
                                                                <select class="form-select @error('status') is-invalid @enderror selectpicker"
                                                                    data-live-search="true" id="status{{$peminjaman->id_peminjaman}}" name="status">
                                                                    <option value="siswa" @if($peminjaman->status == 'siswa' || old('status') == 'siswa') selected @endif>Siswa</option>
                                                                    <option value="guru" @if($peminjaman->status == 'guru' || old('status') == 'guru') selected @endif>Guru</option>
                                                                    <option value="karyawan" @if($peminjaman->status == 'karyawan' || old('status') == 'karyawan') selected @endif>Karyawan</option>
                                                                </select>
                                                                @error('status')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div id="siswaForm{{$peminjaman->id_peminjaman}}" style="display: none;">
                                                                <!-- Siswa form fields -->
                                                                <div class="form-group">
                                                                    <label for="id_users">Nama Siswa</label>
                                                                    <select name="id_users" class="form-select" id="normalize{{$peminjaman->id_peminjaman}}">
                                                                        <option value="" selected disabled>Pilih Nama</option>
                                                                        @foreach($users as $user)
                                                                        @if($user->level == 'siswa')
                                                                        <option value="{{ $user->id_users }}" @if($user->id_users == old('id_users')) selected @endif>{{ $user->name }}</option>
                                                                        @endif
                                                                        @endforeach
                                                                    </select>
                                                                    @error('id_users')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="kelas{{$peminjaman->id_peminjaman}}">Kelas</label>
                                                                            <input type="text" name="kelas" id="kelas{{$peminjaman->id_peminjaman}}" class="form-control" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="nis{{$peminjaman->id_peminjaman}}">NIS</label>
                                                                            <input type="text" name="nis" id="nis{{$peminjaman->id_peminjaman}}" class="form-control" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="guruForm{{$peminjaman->id_peminjaman}}" style="display: none;">
                                                                <!-- Guru form fields -->
                                                                <div class="form-group">
                                                                    <label for="id_guru">Nama Guru</label>
                                                                    <select class="form-select" name="id_guru" id="normalize1{{$peminjaman->id_peminjaman}}">
                                                                        <option value="" selected disabled>Pilih Nama</option>
                                                                        @foreach($guru as $g)
                                                                        <option value="{{ $g->id_guru }}" @if($g->id_guru == old('id_guru')) selected @endif>{{ $g->nama_guru }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('id_guru')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="nip{{$peminjaman->id_peminjaman}}">NIP</label>
                                                                    <input type="text" name="nip" id="nip{{$peminjaman->id_peminjaman}}" class="form-control" readonly>
                                                                </div>
                                                            </div>
                                                            <div id="karyawanForm{{$peminjaman->id_peminjaman}}" style="display: none;">
                                                                <!-- Karyawan form fields -->
                                                                <div class="form-group">
                                                                    <label for="id_karyawan">Nama Karyawan</label>
                                                                    <select class="form-select" name="id_karyawan" id="normalize2{{$peminjaman->id_peminjaman}}">
                                                                        <option value="" selected disabled>Pilih Nama</option>
                                                                        @foreach($karyawan as $k)
                                                                        <option value="{{ $k->id_karyawan }}" @if($k->id_karyawan == old('id_karyawan')) selected @endif>{{ $k->nama_karyawan }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    @error('id_karyawan')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        @endif
                                                
                                                        <div class="form-group mt-2">
                                                            <label for="keterangan_peminjaman">Keterangan Peminjaman</label>
                                                            <input type="text" name="keterangan_peminjaman" id="keterangan_peminjaman{{$peminjaman->id_peminjaman}}" class="form-control  @error('keterangan_peminjaman') is-invalid @enderror" value="{{ old('keterangan_peminjaman', $peminjaman->keterangan_peminjaman) }}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="tgl_kembali{{$peminjaman->id_peminjaman}}">Tanggal Kembali</label>
                                                            <input type="date" name="tgl_kembali" id="tgl_kembali{{$peminjaman->id_peminjaman}}" class="form-control" value="{{ old('tgl_kembali', $peminjaman->tgl_kembali) }}" required>
                                                        </div>
                                                
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                                                        </div>
                                                    </form>
                                                </div>
                                        </div>
                                    </div>
                                    @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@push('js')
<script src="../dist/js/selectize.js"></script>

<form action="" id="delete-form" method="post">
    @method('delete')
    @csrf
</form>
{{-- <script src="../js/script.js"></script> --}}


<script>
$(document).ready(function() {
    var table = $('#myTable').DataTable({
        "responsive": true,
        "order": [
            [0, 'desc']
        ],
        "language": {
            "paginate": {
                "previous": "<",
                "next": ">"
            }
        }
    });

    table.on('order.dt search.dt', function() {
        table.column(0, {
            search: 'applied',
            order: 'applied'
        }).nodes().each(function(cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();
});


function notificationBeforeAdds(event, el, dt) {
    event.preventDefault();

    Swal.fire({
        title: 'Pilihan Tambah Data',
        text: 'Pilih cara untuk menambahkan data:',
        icon: 'info',
        showCloseButton: true, // Menambahkan tombol "X" di pojok kanan atas
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Dengan Barcode',
        cancelButtonText: 'Tanpa Barcode',
    }).then((result) => {
        if (result.isConfirmed) {
            // Jika pengguna memilih "Dengan Barcode"
            window.location.href = '/peminjaman/barcode'; // Ganti dengan URL halaman yang sesuai
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Jika pengguna memilih "Tanpa Barcode"
            window.location.href = '/peminjaman/create'; // Ganti dengan URL halaman yang sesuai
        } 
    });
}



$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Set up global AJAX settings to include the CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });

    // Handler for the edit button click
    $('.edit-button').click(function(e) {
        e.preventDefault();
        const IdPeminjaman = $(this).data('id');
        initializeSelectize(IdPeminjaman);

        $('#normalize' + IdPeminjaman).selectize({

        }); 
        $('#normalize1' + IdPeminjaman).selectize({

        }); 
        $('#normalize2' + IdPeminjaman).selectize({

        }); 

        $.get(`/fetch-peminjaman-data/${IdPeminjaman}`)
        .done(response => {
            console.log('Data terkirim!!', response);

                    // Define elements
                    const namaSiswaElement = document.getElementById('siswaForm' + IdPeminjaman);
                    const namaGuruElement = document.getElementById('guruForm' + IdPeminjaman);
                    const namaKaryawanElement = document.getElementById('karyawanForm' + IdPeminjaman);
                    const kelasElement = document.querySelector('#kelas' + IdPeminjaman);
                    const nisElement = document.querySelector('#nis' + IdPeminjaman);
                    const nipElement = document.querySelector('#nip' + IdPeminjaman);

                    // Hide all forms initially
                    namaSiswaElement.style.display = 'none';
                    namaGuruElement.style.display = 'none';
                    namaKaryawanElement.style.display = 'none';

                    // Show the form based on the status
                    if (response.status === 'siswa') {
                        if (namaSiswaElement) namaSiswaElement.style.display = 'block';
                        const selectSiswa = document.getElementById(`normalize${IdPeminjaman}`);
                    
                        const selectizeInstance = $(`#normalize${IdPeminjaman}`).selectize()[0].selectize;
                        console.log('Selected user ID:', response.id_users);
                        var existingOption = selectizeInstance.options[response.id_users];
                    if (!existingOption) {
                        selectizeInstance.addOption({ value: response.id_users, text: 'User Name' }); // Replace 'User Name' with actual name
                    }
                    selectizeInstance.setValue(response.id_users);
                        fetchProfileData(response.id_users, '/fetch-id-siswa/', nisElement, kelasElement, selectizeInstance);

                    } else if (response.status === 'guru') {
                        if (namaGuruElement) namaGuruElement.style.display = 'block';
                        const selectGuru = document.getElementById(`#normalize1${IdPeminjaman}`);
                        const selectizeInstance = $(`#normalize1${IdPeminjaman}`).selectize()[0].selectize;
                        console.log('Selected user ID:', response.id_guru);
                        var existingOption = selectizeInstance.options[response.id_guru];
                    if (!existingOption) {
                        selectizeInstance.addOption({ value: response.id_guru, text: 'User Name' }); // Replace 'User Name' with actual name
                    }
                    selectizeInstance.setValue(response.id_guru);
                            fetchProfileData(response.id_guru, '/fetch-id-guru/', nipElement, selectizeInstance);
                        
                    } else if (response.status === 'karyawan') {
                        if (namaKaryawanElement) namaKaryawanElement.style.display = 'block';
                        const selectKaryawan = document.getElementById('normalize2' + IdPeminjaman);
                        var selectizeInstance = $(selectKaryawan).selectize()[0].selectize;
                    var existingOption = selectizeInstance.options[response.id_karyawan];
                    if (!existingOption) {
                        selectizeInstance.addOption({ value: response.id_karyawan, text: 'User Name' }); // Replace 'User Name' with actual name
                    }
                    selectizeInstance.setValue(response.id_karyawan); 
                        if (selectKaryawan) {
                            selectKaryawan.value = response.id_karyawan;
                            $(selectKaryawan).trigger('change'); // Trigger change if using Selectize or similar
                        }

                    }
                })
                .fail((jqXHR, textStatus, errorThrown) => {
                    console.error('Data fetch failed:', errorThrown);
                });
    });

    // Handler for form submission
    $('form[id^="updateForm"]').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const formId = form.attr('id');
        const IdPeminjaman = formId.match(/\d+/)[0];
        const status = $(`#status${IdPeminjaman}`).val();
        const formData = form.serializeArray();

        if (status === 'siswa') {
            formData.push({ name: 'id_users', value: $(`#normalize${IdPeminjaman}`).val() });
        } else if (status === 'guru') {
            formData.push({ name: 'id_guru', value: $(`#normalize1${IdPeminjaman}`).val() });
        } else if (status === 'karyawan') {
            formData.push({ name: 'id_karyawan', value: $(`#normalize2${IdPeminjaman}`).val() });
        }

        $.ajax({
            type: 'PUT',
            url: form.attr('action'),
            data: $.param(formData),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: response => {
                console.log('Form submitted successfully:', response);
                Swal.fire({
                    title: 'Success!',
                    text: 'Your changes have been saved.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = "{{ route('peminjaman.index') }}";
                });
            },
            error: (jqXHR, textStatus, errorThrown) => {
                console.error('Form submission failed:', errorThrown);
            }
         });
    });

    function initializeSelectize(IdPeminjaman) {
        $(`#normalize${IdPeminjaman}, #normalize1${IdPeminjaman}, #normalize2${IdPeminjaman}`).selectize();
    }

    function handleStatusChange(IdPeminjaman, status, id_users, id_guru, id_karyawan) {
        const statusElements = {
            siswa: $(`#siswaForm${IdPeminjaman}`),
            guru: $(`#guruForm${IdPeminjaman}`),
            karyawan: $(`#karyawanForm${IdPeminjaman}`)
        };

        Object.values(statusElements).forEach(element => element.hide());
        statusElements[status].show();

        initializeSelectize(IdPeminjaman);

        handleSelectizeChange(`#normalize${IdPeminjaman}`, '/fetch-id-siswa/', $('#nis' + IdPeminjaman), $('#kelas' + IdPeminjaman));
        handleSelectizeChange(`#normalize1${IdPeminjaman}`, '/fetch-id-guru/', $('#nip' + IdPeminjaman), null);
        handleSelectizeChange(`#normalize2${IdPeminjaman}`, '/fetch-id-karyawan/', null, null);
    }

    function handleSelectizeChange(selectId, url, nisOrNipSelector, kelasSelector) {
        const selectizeInstance = $(selectId).selectize()[0].selectize;
        selectizeInstance.on('change', function(value) {
            fetchProfileData(value, url, nisOrNipSelector, kelasSelector);
        });
    }

    function fetchProfileData(id, url, nisOrNipSelector, kelasSelector) {
        fetch(`${url}${id}`)
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch profile data');
                return response.json();
            })
            .then(data => {
                if (nisOrNipSelector) $(nisOrNipSelector).val(data.nis || data.nip || '');
                if (kelasSelector) $(kelasSelector).val(`${data.kelas || ''} ${data.jurusan || ''}`);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                if (nisOrNipSelector) $(nisOrNipSelector).val('');
                if (kelasSelector) $(kelasSelector).val('');
            });
    }

      // Handler for status change
      $(document).on('change', 'select[id^="status"]', function() {
        const IdPeminjaman = $(this).attr('id').match(/\d+/)[0];
        const status = $(this).val();
        handleStatusChange(IdPeminjaman, status);
    });
});


</script>
@if(count($errors))
<script>
Swal.fire({
    title: 'Input tidak sesuai!',
    text: 'Pastikan inputan sudah sesuai',
    icon: 'error',
});
</script>
@endif



@endpush