<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * OtpMail
 *
 * Kelas Mailable untuk mengirimkan kode OTP ke email user.
 * Kelas ini digunakan oleh AuthController setelah user berhasil login,
 * baik melalui login normal (email+password) maupun Google SSO.
 */
class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Kode OTP yang akan dikirimkan ke email user.
     * Dideklarasikan public agar bisa langsung diakses dari view (email template).
     */
    public string $otpCode;

    /**
     * Nama user yang login (untuk personalisasi email).
     */
    public string $userName;

    /**
     * Constructor: menerima kode OTP dan nama user dari AuthController
     *
     * @param string $otpCode  Kode OTP 6 karakter yang sudah di-generate
     * @param string $userName Nama user yang akan menerima OTP
     */
    public function __construct(string $otpCode, string $userName)
    {
        $this->otpCode  = $otpCode;
        $this->userName = $userName;
    }

    /**
     * Envelope: konfigurasi subject dan metadata email
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode OTP Login PURPLEBOOK',
        );
    }

    /**
     * Content: menentukan template Blade yang digunakan sebagai isi email
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp', // resources/views/emails/otp.blade.php
        );
    }
}
