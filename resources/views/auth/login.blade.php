<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - SIPPAKADIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-brand-900 px-4 font-sans">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent-400 text-2xl font-bold text-brand-900 shadow-lg">
                S
            </div>
            <h1 class="text-2xl font-bold text-white">SIPPAKADIS</h1>
            <p class="mt-1 text-sm text-brand-300">Sistem Pemantauan Pajak Kendaraan Dinas</p>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-2xl">
            <h2 class="mb-6 text-lg font-bold text-slate-900">Masuk ke akun Anda</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 focus:outline-none">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Ingat saya
                </label>

                <button type="submit" class="w-full rounded-lg bg-brand-600 py-2.5 text-sm font-bold text-white transition hover:bg-brand-700 focus:ring-4 focus:ring-brand-200 focus:outline-none">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>
