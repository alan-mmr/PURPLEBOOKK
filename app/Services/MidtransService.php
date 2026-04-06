<?php

namespace App\Services;

use App\Models\Pesanan;

class MidtransService
{
    /**
     * Buat Snap Token untuk halaman pembayaran Midtrans.
     * Dipanggil saat order pertama kali dibuat.
     *
     * @param Pesanan $pesanan
     * @return string snap_token
     * @throws \Exception
     */
    public function createSnapToken(Pesanan $pesanan): string
    {
        $this->configureMidtrans();

        // Payload untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id'     => $pesanan->transaction_id, // ID unik per transaksi
                'gross_amount' => $pesanan->total_harga,
            ],
            'customer_details' => [
                'first_name' => $pesanan->nama_pemesan,
            ],
            // Aktifkan Virtual Account dan QRIS
            'enabled_payments' => [
                'bca_va', 'bni_va', 'bri_va', 'permata_va',
                'mandiri_bill', 'other_va', 'gopay', 'qris',
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);
        return $snapToken;
    }

    /**
     * Verifikasi status transaksi langsung dari API Midtrans.
     * Digunakan sebagai "double check" setelah menerima webhook
     * agar tidak hanya bergantung pada data callback.
     *
     * @param string $orderId — nilai transaction_id di tabel pesanan
     * @return array status response dari Midtrans API
     * @throws \Exception
     */
    public function verifyTransaction(string $orderId): array
    {
        $this->configureMidtrans();

        // Panggil API status Midtrans langsung (server-to-server)
        $status = \Midtrans\Transaction::status($orderId);

        // Cast ke array agar mudah diakses
        return (array) $status;
    }

    /**
     * Set konfigurasi Midtrans dari config/services.php.
     * Dipanggil sebelum setiap request ke gateway.
     */
    private function configureMidtrans(): void
    {
        \Midtrans\Config::$serverKey    = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized  = true;  // Bersihkan input otomatis
        \Midtrans\Config::$is3ds        = true;  // Wajib untuk QRIS & VA
    }
}
