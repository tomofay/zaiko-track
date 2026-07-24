<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Label</title>
<head>
    <link rel="stylesheet" href="{{ public_path('/css/label.css') }}">
</head>
<body>
    <table class="w-full">
        @php $count = 0 @endphp <!-- Menghitung jumlah item yang ditampilkan -->
        @foreach($barang as $key => $br)
            @if($count % 2 == 0) <!-- Mulai baris baru setiap 2 item -->
                <tr>
            @endif
            <td>
            <div class="label">
                <div class="label-content">
                    <div class="qr">
                        <img src="{{ public_path('/storage/qrcode/' . $br->qrcode_image) }}">
                    </div>
                    <div class="kodeTahun">
                        <p>{{ $br->kode_barang }}/2026</p>
                        <p>{{ $br->nama_barang }}</p>   
                </div>
            </div>
            </td>
            @php $count++ @endphp <!-- Menambah hitungan item -->
            @if($count % 2 == 0 || $loop->last) <!-- Menutup baris setiap 3 item atau pada item terakhir -->
                </tr>
            @endif
        @endforeach
    </table>
</body>
</html>
