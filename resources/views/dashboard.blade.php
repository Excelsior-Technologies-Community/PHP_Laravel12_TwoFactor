<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Identity Dashboard</title>
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
<body class="min-h-screen text-white p-6">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="glass-card rounded-3xl p-8 flex justify-between items-center shadow-xl">
            <div>
                <h1 class="text-3xl font-black text-cyan-400">🚀 Dashboard Boundary</h1>
                <p class="text-gray-400 text-sm mt-1">Welcome, {{ Auth::user()->name }} • Status: Verified Environment</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 px-5 py-2.5 rounded-xl text-xs font-bold tracking-wide shadow transition-all">
                    Disconnect Session
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 glass-card rounded-2xl p-6 space-y-4">
                <h3 class="text-lg font-bold text-gray-200 border-b border-white/10 pb-2">Active Infrastructure Nodes</h3>
                <p class="text-sm text-gray-400">All modules running inside verified isolation containers. Two-Factor architecture currently forces encryption policies across global pipeline clusters.</p>
                <div class="bg-slate-900/50 border border-emerald-500/20 p-4 rounded-xl text-xs text-emerald-400 font-mono">
                    ● CORE LAYER STATUS: OPERATIONAL STABLE
                </div>
            </div>

            <div class="glass-card rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-amber-400 mb-3">Backup Passcodes</h3>
                    <div class="grid grid-cols-1 gap-1 font-mono text-xs text-gray-300">
                        @foreach($recoveryCodes as $code)
                            <div class="bg-slate-950/60 p-2 rounded border border-white/5 flex items-center justify-between">
                                <span>🔑 {{ $code }}</span>
                                <span class="text-[9px] text-gray-500 uppercase font-bold">Valid</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-white/10">
                    <a href="{{ route('2fa.recovery.download') }}" class="w-full block text-center bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold py-2 rounded-xl transition-all">
                        Download Codes
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>