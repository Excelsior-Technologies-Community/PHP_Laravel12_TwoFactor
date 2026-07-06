<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Verification Step</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(to right, #0f172a, #1e293b);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen text-white flex items-center justify-center p-4">
    <div class="glass-card w-full max-w-md rounded-3xl p-8 shadow-2xl">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-black tracking-wide text-amber-400">季 2FA VERIFICATION</h1>
            <p class="text-gray-400 text-xs mt-2">Scan QR Code with Google Authenticator App or enter backup sequence</p>
        </div>

        @if($qrCodeSvg)
            <div class="flex flex-col items-center justify-center bg-white p-4 rounded-2xl my-4 shadow-inner">
                <img src="{{ $qrCodeSvg }}" alt="Scan QR Code" class="rounded-lg shadow">
                <span class="text-slate-900 text-[10px] font-bold mt-2 uppercase tracking-widest">Scan with Google Authenticator</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-500/20 border border-rose-500 text-rose-300 p-4 rounded-xl text-xs mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('2fa.verify') }}" method="POST" class="space-y-5">
            @csrf
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">6-Digit App Passcode / Recovery Code</label>
                <input type="text" name="code" required autocomplete="off" placeholder="######" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-center tracking-widest font-mono text-lg focus:outline-none focus:border-amber-400">
            </div>

            <div class="flex items-center justify-between bg-slate-900/50 p-3 rounded-xl border border-white/5">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember_device" id="remember_device" value="1" class="rounded border-slate-700 bg-slate-900 text-amber-500 focus:ring-0">
                    <label for="remember_device" class="text-xs text-gray-300 cursor-pointer select-none">Remember this device for 30 days</label>
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-orange-600 hover:scale-102 duration-300 py-3 rounded-xl font-bold text-sm tracking-wide shadow-lg">
                Verify Secure Authorization
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-white/10 text-center">
            <a href="{{ route('2fa.recovery.download') }}" class="text-xs font-bold text-cyan-400 hover:underline">📥 Download Backup Recovery Codes</a>
        </div>
    </div>
</body>
</html>