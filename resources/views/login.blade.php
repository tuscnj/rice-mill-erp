<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Atik Auto Rice Mills</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen p-4 font-sans">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-slate-800 p-8 text-center border-b-4 border-blue-500">
            <h1 class="text-4xl mb-2">🌾</h1>
            <h2 class="text-2xl font-bold text-white tracking-wide">Atik Auto Rice</h2>
            <p class="text-slate-400 text-sm mt-1">Authorized Personnel Only</p>
        </div>

        <form action="/run-login" method="POST" class="p-8 space-y-6">
            @csrf

            @if($errors->any())
                <div class="bg-red-100 text-red-600 p-3 rounded-lg text-sm font-bold text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500" placeholder="admin@atikrice.com">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full p-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500" placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold text-lg py-3 rounded-lg shadow-lg hover:bg-blue-700 transition mt-4">
                Access System
            </button>
        </form>
    </div>

</body>
</html>