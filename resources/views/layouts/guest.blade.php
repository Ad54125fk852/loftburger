<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @include('layouts.scripts') {{-- Ensure this includes your compiled Tailwind CSS --}}
</head>

<body class="font-sans text-gray-900 antialiased min-h-screen flex flex-col">
    <header class="shadow-md bg-white"> {{-- Added bg-white for consistency --}}
        <nav class="container px-4 py-4 mx-auto flex flex-col md:flex-row justify-between items-center">
            {{-- Loft Title and Tagline --}}
            <div class="flex flex-col items-center md:items-start mb-4 md:mb-0 text-center md:text-left">
                <h1
                    class="text-3xl sm:text-4xl lg:text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-blue-500 hover:from-green-500 hover:to-blue-600 transition-colors duration-300">
                    Food Ease
                </h1>
                <h2 class="font-bold text-base sm:text-lg mt-2 text-gray-700">
                    Open-source Restaurant Management App <br> Built with Laravel
                </h2>
            </div>

            {{-- Edge Ease Logo --}}
            <div class="flex items-center">
                <span class="logo-text text-base sm:text-lg mr-2 text-gray-600">A product of</span>
                <img src="{{ asset('images/edge_ease_logo.png') }}" alt="Edge Ease Logo"
                    class="w-24 sm:w-32 h-auto rounded-md">
            </div>
        </nav>
    </header>

    {{-- Main Content Slot --}}
    <div class="flex-grow bg-gray-100 p-4 sm:p-6 lg:p-8"> {{-- Use flex-grow to push footer to bottom --}}
        {{ $slot }}
        
    </div>

    {{-- Footer Section --}}
    
</body>

</html>
