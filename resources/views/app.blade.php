<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title inertia>{{ config('app.name', 'Customer Contact List') }}</title>
    @vite(['resources/js/app.js'])
    @vite('resources/css/app.css')
</head>
<body>
    @inertia
</body>
</html>
