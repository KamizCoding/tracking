<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Link Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-6xl mb-4">🔗</div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Tracking Link Not Found</h1>
            <p class="text-gray-600 mb-6">
                The tracking link you're looking for doesn't exist or has been deleted.
            </p>
            <a href="{{ url('/') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                Go to Homepage
            </a>
        </div>
    </div>
</body>
</html>
