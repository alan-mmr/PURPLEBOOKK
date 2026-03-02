<!DOCTYPE html>
<html>
<head>
    <title>Undangan Resmi Purplebook</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            line-height: 1.6;
            padding: 40px;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .header-logo {
            width: 80px;
            text-align: center;
        }
        .header-text {
            text-align: center;
        }
        .header-text h1 {
            margin: 0;
            color: #6f42c1;
            font-size: 28px;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 5px 0;
            font-size: 12px;
        }
        .content {
            margin-top: 30px;
        }
        .info-table {
            width: 100%;
            margin: 20px 0;
        }
        .info-table td {
            vertical-align: top;
            padding: 5px 0;
        }
        .footer {
            margin-top: 50px;
            float: right;
            width: 250px;
            text-align: center;
        }
        .sign-space {
            height: 80px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <div style="font-size: 40px; font-weight: bold; color: #6f42c1;">PB</div>
            </td>
            <td class="header-text">
                <h1>PURPLEBOOK DIGITAL LIBRARY</h1>
                <p>Jl. Literasi No. 42, Kota Buku, Indonesia 60286</p>
                <p>Telp: (021) 123456 | Website: purplebook.test | Email: hello@purplebook.test</p>
            </td>
        </tr>
    </table>

    <div class="content">
        <table width="100%">
            <tr>
                <td>Nomor : {{ $nomor_surat }}</td>
                <td align="right">22 Februari 2026</td>
            </tr>
            <tr>
                <td>Perihal : <strong>Undangan Meet & Greet Penulis</strong></td>
                <td></td>
            </tr>
        </table>

        <p style="margin-top: 30px;">Yth. Sdr/i  <strong>{{ $nama }}</strong>,</p>
        <p>Dengan hormat,</p>
        <p>Dalam rangka mempererat tali silaturahmi antar komunitas pembaca, manajemen Purplebook berencana menyelenggarakan acara 
        exclusive Meet & Greet bersama penulis terkemuka. Sehubungan dengan hal tersebut, kami mengundang Bapak/Ibu untuk hadir pada:</p>

        <table class="info-table">
            <tr>
                <td width="150">Hari, Tanggal</td>
                <td width="20">:</td>
                <td><strong>{{ $tanggal_acara }}</strong></td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>:</td>
                <td>10.00 - 13.00 WIB</td>
            </tr>
            <tr>
                <td>Tempat</td>
                <td>:</td>
                <td>Ruang Baca Digital (Lantai 2), Kantor Pusat Purplebook</td>
            </tr>
            <tr>
                <td>Agenda</td>
                <td>:</td>
                <td>Diskusi Buku dan Tanda Tangan Eksklusif</td>
            </tr>
        </table>

        <p>Demikian undangan ini kami sampaikan. Kami sangat menantikan kehadiran Anda di acara tersebut.</p>

        <div class="footer">
            Manajer Event,
            <div class="sign-space"></div>
            <strong>Dian Yulie Reindrawati</strong><br>
            NIP. 197607071999032001
        </div>
    </div>
</body>
</html>
