<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    /**
     * Menampilkan halaman daftar dokumen yang bisa dicetak.
     */
    public function index()
    {
        return view('pdf.index');
    }

    /**
     * Generate PDF Sertifikat Apresiasi (Landscape A4)
     * Studi Kasus 2a
     */
    public function cetakSertifikat()
    {
        $data = [
            'nama' => auth()->user()->name,
            'tanggal' => date('d F Y'),
            'nomor' => 'PB/CERT/' . date('Ymd') . '/' . auth()->id(),
        ];

        $pdf = Pdf::loadView('pdf.sertifikat', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('Sertifikat_Apresiasi_Purplebook.pdf');
    }

    /**
     * Generate PDF Undangan Event (Portrait A4 + Header)
     * Studi Kasus 2b
     */
    public function cetakUndangan()
    {
        $data = [
            'nama' => auth()->user()->name,
            'tanggal_acara' => '25 Maret 2026',
            'nomor_surat' => '556/B/PURPLEBOOK/III/2026',
        ];

        $pdf = Pdf::loadView('pdf.undangan', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Undangan_Event_Purplebook.pdf');
    }
}
