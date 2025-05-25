<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Login Page</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.0/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">

  <form action="{{ route('login.submit') }}" method="POST" class="bg-gray-800 p-6 rounded-lg shadow-md w-full max-w-md">
    @csrf

    <h1 class="text-2xl font-bold mb-6 text-teal-400 text-center">Giriş Yap</h1>

    @if(session('error'))
    <div class="bg-red-600 text-white p-2 mb-4 rounded text-center">
      {{ session('error') }}
    </div>
    @endif

    <div class="mb-4">
      <label class="block mb-1 text-sm" for="email">Email</label>
      <input type="email" name="email" id="email" required class="w-full px-4 py-2 bg-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-teal-400">
    </div>

    <div class="mb-4">
      <label class="block mb-1 text-sm" for="password">Şifre</label>
      <input type="password" name="password" id="password" required class="w-full px-4 py-2 bg-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-teal-400">
    </div>

    <button type="submit" class="w-full py-2 bg-teal-500 hover:bg-teal-400 rounded text-white font-bold">Giriş Yap</button>
  </form>

</body>

</html>