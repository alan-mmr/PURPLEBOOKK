<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP PURPLEBOOK</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 480px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #6f42c1, #a855f7); padding: 30px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 24px; letter-spacing: 2px; }
        .body { padding: 30px; text-align: center; }
        .body p { color: #555; font-size: 15px; line-height: 1.6; }
        .otp-box { background: #f8f4ff; border: 2px dashed #6f42c1; border-radius: 10px; padding: 20px; margin: 25px 0; }
        .otp-code { font-size: 42px; font-weight: bold; color: #6f42c1; letter-spacing: 10px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 16px; border-radius: 4px; text-align: left; font-size: 13px; color: #856404; }
        .footer { background: #f8f4ff; padding: 15px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header dengan nama aplikasi --}}
        <div class="header">
            <h1>📚 PURPLEBOOK</h1>
        </div>

        <div class="body">
            <p>Halo, <strong>{{ $userName }}</strong>!</p>
            <p>Kamu baru saja melakukan login ke <strong>PURPLEBOOK</strong>.<br>
               Gunakan kode OTP berikut untuk menyelesaikan proses login:</p>

            {{-- Kotak OTP yang mencolok --}}
            <div class="otp-box">
                <div class="otp-code">{{ $otpCode }}</div>
            </div>

            {{-- Peringatan keamanan --}}
            <div class="warning">
                ⚠️ <strong>Perhatian:</strong> Kode ini hanya berlaku untuk <strong>sekali pakai</strong>.
                Jangan bagikan kode ini kepada siapapun.
            </div>

            <p style="margin-top: 20px; font-size: 13px; color: #999;">
                Jika kamu tidak melakukan login, abaikan email ini.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} PURPLEBOOK. Email ini dikirim otomatis, jangan dibalas.
        </div>
    </div>
</body>
</html>
