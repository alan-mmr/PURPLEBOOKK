<!DOCTYPE html>
<html>
<head>
    <title>Sertifikat Apresiasi Purplebook</title>
    <style>
        @page { margin: 0; size: landscape; }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
        }
        .cert-container {
            width: 1000px; /* Lebar tetap untuk landscape */
            margin: 20px auto;
            padding: 30px;
            border: 15px solid #6f42c1;
            box-sizing: border-box;
            position: relative;
            background-color: #fff;
        }
        .cert-inner {
            border: 3px solid #6f42c1;
            padding: 30px;
            text-align: center;
        }
        .header {
            font-size: 44px;
            color: #6f42c1;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .sub-header {
            font-size: 20px;
            margin-bottom: 30px;
        }
        .name {
            font-size: 36px;
            font-weight: bold;
            border-bottom: 2px solid #000;
            display: inline-block;
            margin-bottom: 25px;
            color: #333;
        }
        .content {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        .footer {
            margin-top: 30px;
        }
        .signature-table {
            width: 100%;
        }
        .sign-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 60px auto 5px auto;
        }
        .watermark {
            position: absolute;
            top: 200px;
            left: 350px;
            opacity: 0.1;
            font-size: 200px;
            font-weight: bold;
            color: #6f42c1;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="cert-container">
        <div class="watermark">PB</div>
        <div class="cert-inner">
            <div class="header">SERTIFIKAT</div>
            <div class="sub-header">DIANUGERAHKAN KEPADA :</div>
            
            <div class="name">{{ $nama }}</div>

            <div class="content">
                Atas partisipasi dan loyalitasnya sebagai <strong>Pembaca Aktif</strong><br>
                di platform Koleksi Buku Digital <strong>PURPLEBOOK</strong>.<br>
                Teruslah membaca untuk membuka jendela dunia.
            </div>

            <table class="signature-table">
                <tr>
                    <td align="left" style="font-size: 13px; vertical-align: bottom; padding-bottom: 10px;">
                        No: {{ $nomor }}<br>
                        Tanggal: {{ $tanggal }}
                    </td>
                    <td align="center" width="300" style="vertical-align: top;">
                        <div style="margin-bottom: 50px;">
                            <strong>Founder Purplebook</strong>
                        </div>
                        <div class="sign-line"></div>
                        <div style="font-weight: bold; margin-top: 5px;">Alan MMR</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
