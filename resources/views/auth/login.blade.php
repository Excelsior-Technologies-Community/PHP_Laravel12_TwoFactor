<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Security Matrix</title>
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
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black tracking-wide text-cyan-400">🛡️ SECURE PORTAL</h1>
            <p class="text-gray-400 text-sm mt-2">Identify signature token boundary authorization</p>
        </div>

        @if($errors->any())
            <div class="bg-rose-500/20 border border-rose-500 text-rose-300 p-4 rounded-xl text-xs mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/login" method="POST" class="space-y-5">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Email Address</label>
                <input type="email" name="email" required class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-sm focus:outline-none focus:border-cyan-400">
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Password Encryption</label>
                <input type="password" name="password" required class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-sm focus:outline-none focus:border-cyan-400">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:scale-102 duration-300 py-3 rounded-xl font-bold text-sm tracking-wide shadow-lg">
                Authenticate Connection
            </button>
        </form>
    </div>
</body>
</html>