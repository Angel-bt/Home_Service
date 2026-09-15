<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" media="screen">
    <script>
        function togglePasswordVisibility(id) {
            const input = document.getElementById(id);
            const icon = document.getElementById(`toggle-${id}`);
            if (input.type === "password") {
                input.type = "text";
                icon.textContent = "🙈"; // Icono para "ocultar"
            } else {
                input.type = "password";
                icon.textContent = "👁️"; // Icono para "mostrar"
            }
        }
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
<div class="glass-card">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-semibold text-gray-800 text-center mb-4">Reset Password</h2>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input id="email" name="email" type="email"
                       class="neomorph-card"
                       value="{{ old('email', $request->email) }}" placeholder="example@example.com" required autofocus>
            </div>

            <div class="relative">
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input id="password" name="password" type="password"
                       class="neomorph-card w-full pr-10"
                       required autocomplete="new-password">
                <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500"
                        onclick="togglePasswordVisibility('password')">👁️</button>
            </div>

            <div class="relative">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       class="neomorph-card w-full pr-10"
                       required autocomplete="new-password">
                <button type="button" id="toggle-password_confirmation" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500"
                        onclick="togglePasswordVisibility('password_confirmation')">👁️</button>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Reset Password
                </button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                Back to login
            </a>
        </div>
    </div>
</body>
</html>
