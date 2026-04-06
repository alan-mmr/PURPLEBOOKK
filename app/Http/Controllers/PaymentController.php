<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(protected MidtransService $midtrans) {}

    /**
     * GET /pesan/{id}/bayar
     * Tampilkan halaman bayar — embed Midtrans Snap.js
     */
    public function pay($id)
    {
        $pesanan = Pesanan::with('vendor')->findOrFail($id);

        // Jika sudah paid, langsung ke halaman status
        if ($pesanan->isBayarFinal()) {
            return redirect()->route('pesan.status', $id);
        }

        $snapUrl   = config('services.midtrans.snap_url');
        $clientKey = config('services.midtrans.client_key');

        return view('pages.pesan.bayar', compact('pesanan', 'snapUrl', 'clientKey'));
    }

    /**
     * POST /midtrans/webhook
     * Endpoint server-to-server dari Midtrans.
     * Di-exclude dari CSRF middleware (lihat routes/web.php).
     *
     * Flow:
     * 1. Validasi signature key (SHA512)
     * 2. Ambil order_id dari payload
     * 3. Double verify ke API Midtrans langsung
     * 4. Update status_bayar di DB dalam transaksi
     * 5. Immutable: jika sudah paid, skip
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();

        // ── Step 1: Validasi Signature Key ─────────────────────────────────
        // Signature = SHA512(order_id + status_code + gross_amount + server_key)
        $serverKey    = config('services.midtrans.server_key');
        $orderId      = $payload['order_id']      ?? '';
        $statusCode   = $payload['status_code']   ?? '';
        $grossAmount  = $payload['gross_amount']  ?? '';

        $expectedSig  = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        $receivedSig  = $payload['signature_key'] ?? '';

        if (!hash_equals($expectedSig, $receivedSig)) {
            Log::warning('Midtrans webhook: invalid signature', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // ── Step 2: Cari pesanan berdasarkan order_id ───────────────────────
        $pesanan = Pesanan::where('transaction_id', $orderId)->first();

        if (!$pesanan) {
            Log::warning('Midtrans webhook: order not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // ── Step 3: Immutable check ─────────────────────────────────────────
        // Jika sudah paid, tidak perlu proses lagi
        if ($pesanan->isBayarFinal()) {
            return response()->json(['message' => 'Already paid, skipped']);
        }

        // ── Step 4: Double verify ke API Midtrans ──────────────────────────
        try {
            $verified = $this->midtrans->verifyTransaction($orderId);
        } catch (\Exception $e) {
            Log::error('Midtrans verify error: ' . $e->getMessage(), ['order_id' => $orderId]);
            return response()->json(['message' => 'Verification failed'], 500);
        }

        $verifiedStatus = $verified['transaction_status'] ?? '';
        $fraudStatus    = $verified['fraud_status']       ?? 'accept';

        // ── Step 5: Update DB dalam transaksi ──────────────────────────────
        DB::beginTransaction();
        try {
            if ($verifiedStatus === 'capture' && $fraudStatus === 'accept'
                || $verifiedStatus === 'settlement') {
                // PAID
                $pesanan->update([
                    'status_bayar' => 'paid',
                    'payment_type' => $verified['payment_type'] ?? null,
                    'paid_at'      => now(),
                ]);

            } elseif (in_array($verifiedStatus, ['deny', 'cancel', 'failure'])) {
                // FAILED
                $pesanan->update(['status_bayar' => 'failed']);

            } elseif ($verifiedStatus === 'expire') {
                // EXPIRED
                $pesanan->update(['status_bayar' => 'expired']);
            }

            DB::commit();
            return response()->json(['message' => 'OK']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans webhook DB error: ' . $e->getMessage(), ['order_id' => $orderId]);
            return response()->json(['message' => 'DB error'], 500);
        }
    }
}
