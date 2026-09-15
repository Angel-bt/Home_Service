{{-- resources/views/auth/password-reset.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" >
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" media="screen">
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
<div class="glass-card">
    
    <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
        <div class="neomorph-card">
        <a href="/"><button class="neomorph-card">Home</button></a>
        <h2 class="text-2xl font-semibold text-gray-800 text-center mb-4">Reset Password</h2>

        <p class="mb-4 text-sm text-gray-600 text-center">
            Enter your email address, and we will send you a link to reset your password.
        </p>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input id="email" name="email" type="email" 
                       class="block mt-1 w-full p-2 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200" 
                       value="{{ old('email') }}" placeholder="example@example.com" required autofocus>
            </div>
            <div class="flex justify-end">
                <button
                        class="neomorph-button">
                    Send Password Reset Link
                </button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                Back to login
            </a>
            
                    
        </div>
    </div>
    </div>
    </div>
</body>
</html>
