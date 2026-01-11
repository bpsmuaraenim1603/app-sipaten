<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak - SIPATEN</title>

    {{-- Kalau kamu pakai Vite + Tailwind, ini akan ikut styling --}}
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        /* fallback kalau Tailwind/Vite tidak jalan di hosting */
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; background:#f7f7f8; }
        .wrap { min-height: 100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
        .card { width:100%; max-width:720px; background:#fff; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.08); overflow:hidden; }
        .header { padding:22px 24px; background:#111827; color:#fff; }
        .content { padding:22px 24px; }
        .badge { display:inline-block; font-size:12px; padding:6px 10px; border-radius:999px; background:#fee2e2; color:#991b1b; font-weight:600; }
        .muted { color:#6b7280; }
        .btns { display:flex; gap:10px; flex-wrap:wrap; margin-top:18px; }
        .btn { display:inline-block; text-decoration:none; padding:10px 14px; border-radius:10px; font-weight:600; }
        .btn-primary { background:#2563eb; color:#fff; }
        .btn-outline { border:1px solid #d1d5db; color:#111827; background:#fff; }
        .small { font-size:12px; margin-top:16px; }
        .box { background:#f9fafb; border:1px solid #e5e7eb; padding:12px 14px; border-radius:12px; margin-top:14px; }
        code { background:#111827; color:#fff; padding:2px 6px; border-radius:6px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="header">
            <div class="badge">AKSES DITOLAK</div>
            <h1 style="margin:10px 0 0; font-size:22px; line-height:1.2;">
                SIPATEN hanya untuk Pegawai BPS Kabupaten Muara Enim
            </h1>
            <p style="margin:8px 0 0;" class="muted">
                Kamu berhasil login SSO, tapi tidak memenuhi syarat akses aplikasi ini.
            </p>
        </div>

        <div class="content">
            @if (session('error'))
                <div class="box" style="border-color:#fecaca;">
                    <strong style="color:#991b1b;">Info:</strong>
                    <span class="muted">{{ session('error') }}</span>
                </div>
            @endif

            <div class="box">
                <p style="margin:0;">
                    Jika kamu merasa ini keliru, biasanya data unit kerja/kabupaten perlu pembaruan di sumber SSO/API Pegawai.
                </p>
                <p class="muted" style="margin:10px 0 0;">
                    Silakan hubungi admin SIPATEN / Tim SPBE Muara Enim untuk pengecekan.
                </p>
            </div>

            <div class="btns">
                {{-- Balik ke beranda (nanti akan lempar ke SSO redirect lagi kalau belum login) --}}
                <a class="btn btn-primary" href="{{ url('/') }}">Kembali ke Beranda</a>

                {{-- Logout aplikasi (dan kalau kamu sudah bikin logout SSO, ini akan ikut logout SSO) --}}
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="cursor:pointer;">
                        Logout
                    </button>
                </form>
            </div>

            <div class="small muted">
                Kode: <code>403</code> • Halaman ini ditampilkan karena pembatasan akses berdasarkan satuan kerja kabupaten.
            </div>
        </div>
    </div>
</div>
</body>
</html>
