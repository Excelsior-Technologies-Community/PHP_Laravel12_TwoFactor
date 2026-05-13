<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 min-h-screen text-white">

    <!-- Navbar -->
    <nav class="backdrop-blur-lg bg-white/10 border-b border-white/10 shadow-lg">

        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

            <div>
                <h1 class="text-3xl font-bold tracking-wide">
                    🔐 Two-Factor Dashboard
                </h1>

                <p class="text-slate-300 text-sm mt-1">
                    Secure Laravel 12 Authentication System
                </p>
            </div>

            <form method="POST" action="/logout">
                @csrf

                <button
                    class="bg-red-500 hover:bg-red-600 transition px-5 py-2 rounded-xl font-semibold shadow-lg"
                >
                    Logout
                </button>
            </form>

        </div>

    </nav>

    <div class="max-w-7xl mx-auto px-6 py-10">

        <!-- Alert -->
        @if(session('success'))

            <div class="mb-6 bg-green-500/20 border border-green-400/30 text-green-300 px-5 py-4 rounded-2xl backdrop-blur-lg">
                {{ session('success') }}
            </div>

        @endif

        <!-- Welcome Cards -->
        <div class="grid md:grid-cols-3 gap-6 mb-10">

            <!-- Welcome -->
            <div class="md:col-span-2 bg-white/10 backdrop-blur-xl rounded-3xl p-8 border border-white/10 shadow-2xl">

                <h2 class="text-4xl font-bold mb-3">
                    Welcome,
                    <span class="text-indigo-300">
                        {{ auth()->user()->name }}
                    </span>
                    👋
                </h2>

                <p class="text-slate-300 text-lg">
                    You are successfully logged in using
                    Two-Factor Authentication.
                </p>

                <div class="mt-6 flex gap-4">

                    <div class="bg-indigo-500/20 px-5 py-3 rounded-2xl border border-indigo-400/20">
                        <p class="text-sm text-indigo-200">
                            Security Status
                        </p>

                        <h3 class="text-2xl font-bold text-green-400">
                            Active
                        </h3>
                    </div>

                    <div class="bg-cyan-500/20 px-5 py-3 rounded-2xl border border-cyan-400/20">
                        <p class="text-sm text-cyan-200">
                            Session
                        </p>

                        <h3 class="text-2xl font-bold">
                            Protected
                        </h3>
                    </div>

                </div>

            </div>

            <!-- Stats -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl p-8 shadow-2xl">

                <p class="uppercase tracking-widest text-sm text-indigo-100">
                    Total Users
                </p>

                <h2 class="text-6xl font-extrabold mt-4">
                    {{ $totalUsers }}
                </h2>

                <p class="mt-3 text-indigo-100">
                    Registered accounts in system
                </p>

            </div>

        </div>

        <!-- Users Table -->
        <div class="bg-white/10 backdrop-blur-xl rounded-3xl border border-white/10 shadow-2xl overflow-hidden">

            <!-- Header -->
            <div class="p-6 border-b border-white/10 flex flex-col md:flex-row justify-between items-center gap-4">

                <div>
                    <h2 class="text-2xl font-bold">
                        👥 User Management
                    </h2>

                    <p class="text-slate-300 text-sm mt-1">
                        Search and manage authenticated users
                    </p>
                </div>

                <!-- Search -->
                <form method="GET" class="flex gap-3">

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search users..."
                        class="bg-slate-900/70 border border-slate-700 text-white px-5 py-3 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 w-72"
                    >

                    <button
                        class="bg-indigo-600 hover:bg-indigo-700 px-5 py-3 rounded-xl font-semibold transition"
                    >
                        Search
                    </button>

                </form>

            </div>

            <!-- Table -->
            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-white/5 text-slate-300 uppercase text-sm tracking-wider">

                        <tr>
                            <th class="px-6 py-4 text-left">ID</th>
                            <th class="px-6 py-4 text-left">Name</th>
                            <th class="px-6 py-4 text-left">Email</th>
                            <th class="px-6 py-4 text-left">Created</th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($users as $user)

                            <tr class="border-b border-white/5 hover:bg-white/5 transition">

                                <td class="px-6 py-5 font-semibold">
                                    #{{ $user->id }}
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">

                                        <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>

                                        <span class="font-medium">
                                            {{ $user->name }}
                                        </span>

                                    </div>
                                </td>

                                <td class="px-6 py-5 text-slate-300">
                                    {{ $user->email }}
                                </td>

                                <td class="px-6 py-5 text-slate-400">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>

                                <td class="px-6 py-5 text-center">

                                    <form
                                        action="/users/{{ $user->id }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this user?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="bg-red-500 hover:bg-red-600 transition px-4 py-2 rounded-xl text-sm font-semibold shadow"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="text-center py-10 text-slate-400">

                                    <div class="text-5xl mb-3">
                                        😔
                                    </div>

                                    No users found

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <!-- Pagination -->
            <div class="p-6 border-t border-white/10">
                {{ $users->links() }}
            </div>

        </div>

    </div>

</body>

</html>