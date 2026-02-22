<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Verifikasi OTP - PURPLEBOOK</title>

    {{-- Vendor CSS (Bootstrap + Material Design Icons) --}}
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">

    {{-- Template CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <style>
        /* ── Styling tambahan khusus halaman OTP ── */

        /* Container kotak OTP */
        .otp-input-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 25px 0;
        }

        /* Setiap kotak input digit OTP */
        .otp-input {
            width: 52px;
            height: 60px;
            font-size: 26px;
            font-weight: bold;
            text-align: center;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            color: #6f42c1;
        }

        /* Efek saat kotak OTP diklik/fokus */
        .otp-input:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.2);
        }

        /* Efek saat kotak OTP sudah terisi */
        .otp-input:not(:placeholder-shown) {
            border-color: #6f42c1;
            background-color: #f8f4ff;
        }

        /* Badge email tujuan */
        .email-badge {
            background: #f8f4ff;
            border: 1px solid #d4c5f9;
            color: #6f42c1;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth px-0">
                <div class="row w-100 mx-0">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left py-5 px-4 px-sm-5" style="border-radius: 12px; box-shadow: 0 8px 32px rgba(111,66,193,0.12);">

                            {{-- Logo / Judul App --}}
                            <div class="brand-logo text-center mb-2">
                                <h2 style="color: #6f42c1; font-weight: 800; letter-spacing: 2px;">📚 PURPLEBOOK</h2>
                            </div>

                            {{-- Icon kunci --}}
                            <div class="text-center mb-3">
                                <i class="mdi mdi-shield-key-outline" style="font-size: 48px; color: #6f42c1;"></i>
                            </div>

                            <h4 class="text-center" style="color: #333; font-weight: 700;">Verifikasi OTP</h4>

                            {{-- Keterangan email tujuan --}}
                            <p class="text-muted text-center mb-1" style="font-size: 14px;">
                                Kode OTP telah dikirim ke:
                            </p>
                            <div class="text-center">
                                <span class="email-badge">
                                    <i class="mdi mdi-email-outline mr-1"></i>
                                    {{ session('otp_email', 'email kamu') }}
                                </span>
                            </div>

                            {{-- Tampilkan error jika OTP salah --}}
                            @if ($errors->any())
                                <div class="alert alert-danger py-2" style="font-size: 13px;">
                                    <i class="mdi mdi-alert-circle-outline mr-1"></i>
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            {{-- Form OTP --}}
                            <form class="pt-2" method="POST" action="{{ route('otp.verify') }}" id="otp-form">
                                @csrf

                                {{-- Input tersembunyi untuk menampung nilai OTP yang dikumpulkan dari 6 kotak --}}
                                <input type="hidden" name="otp" id="otp-combined">

                                {{-- 6 Kotak Input OTP --}}
                                <div class="otp-input-group">
                                    <input class="otp-input" type="text" maxlength="1" placeholder="·" id="otp-1" inputmode="text">
                                    <input class="otp-input" type="text" maxlength="1" placeholder="·" id="otp-2" inputmode="text">
                                    <input class="otp-input" type="text" maxlength="1" placeholder="·" id="otp-3" inputmode="text">
                                    <input class="otp-input" type="text" maxlength="1" placeholder="·" id="otp-4" inputmode="text">
                                    <input class="otp-input" type="text" maxlength="1" placeholder="·" id="otp-5" inputmode="text">
                                    <input class="otp-input" type="text" maxlength="1" placeholder="·" id="otp-6" inputmode="text">
                                </div>

                                {{-- Tombol submit --}}
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-block btn-gradient-primary btn-lg font-weight-medium auth-form-btn">
                                        <i class="mdi mdi-check-circle-outline mr-1"></i>
                                        Verifikasi
                                    </button>
                                </div>
                            </form>

                            {{-- Link kembali ke login --}}
                            <div class="text-center mt-4">
                                <a href="{{ route('login') }}" class="text-muted" style="font-size: 13px;">
                                    <i class="mdi mdi-arrow-left mr-1"></i>Kembali ke login
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vendor JS --}}
    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>

    <script>
    /**
     * Script untuk OTP Input:
     * 1. Auto-focus ke kotak berikutnya saat satu digit diisi
     * 2. Auto-focus ke kotak sebelumnya saat Backspace ditekan
     * 3. Sebelum submit: gabungkan 6 digit menjadi 1 string dan isi ke input hidden
     * 4. Hanya menerima huruf dan angka (alphanumeric), otomatis jadi uppercase
     */
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.otp-input');
        const form   = document.getElementById('otp-form');
        const hidden = document.getElementById('otp-combined');

        inputs.forEach(function (input, index) {
            // Saat user mengetik di kotak ini
            input.addEventListener('input', function () {
                // Ambil hanya 1 karakter (huruf atau angka), ubah ke uppercase
                const val = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                this.value = val.slice(0, 1); // Batasi 1 karakter

                // Pindah fokus ke kotak berikutnya jika sudah terisi
                if (val && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            // Saat user tekan Backspace
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    // Jika kotak kosong dan tekan Backspace → mundur ke kotak sebelumnya
                    inputs[index - 1].focus();
                }
            });

            // Support Paste: otomatis isi semua kotak
            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData)
                    .getData('text')
                    .replace(/[^a-zA-Z0-9]/g, '')
                    .toUpperCase()
                    .slice(0, 6);

                pasted.split('').forEach(function (char, i) {
                    if (inputs[i]) inputs[i].value = char;
                });

                // Fokus ke kotak terakhir yang terisi
                const lastIndex = Math.min(pasted.length, inputs.length) - 1;
                if (lastIndex >= 0) inputs[lastIndex].focus();
            });
        });

        // Sebelum form di-submit: kumpulkan semua digit ke input hidden
        form.addEventListener('submit', function () {
            let combined = '';
            inputs.forEach(function (input) {
                combined += input.value;
            });
            hidden.value = combined;
        });
    });
    </script>
</body>
</html>
