<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Label</title>
    <link rel="stylesheet" href="{{ public_path('/css/label.css') }}">
</head>
<body>
    <table class="w-full">
        <tr>
            @foreach($barang as $key => $br)
                <td style="width: 50%;">
                    <div class="label">
                        <div class="label-content">
                            <div class="qr">
                                <img src="{{ public_path('/storage/qrcode/' . $br->qrcode_image) }}">
                            </div>
                            <div class="kodeTahun">
                                <p><strong>{{ $br->kode_barang }}</strong></p>
                                <p>{{ $br->nama_barang }}</p>
                            </div>
                        </div>
                    </div>
                </td>
            @endforeach
        </tr>
    </table>
</body>
</html>
