{{-- Layout personnalisé Filament --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PharmApp') }}</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @filamentStyles
</head>
<body class="filament-body">
    <script>console.log('Filament layout loaded');</script>
    @filamentContent
    @filamentScripts
</body>
</html>
