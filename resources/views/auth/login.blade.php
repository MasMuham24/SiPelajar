@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="bg-white rounded-2xl shadow-2xl p-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-graduation-cap text-3xl text-white"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">SiPelajar</h1>
        <p class="text-gray-500 mt-2">Sistem Informasi Pelajar</p>
    </div>

    <!-- Alerts -->
    @if ($errors->any())
        <x-alert type="error" :message="$errors->first()" />
    @endif

    @if (session('error'))
        <x-alert type="error" :message="session('error')" />
    @endif

    <!-- Login Form -->
    <form action="{{ route('login.post') }}" method="POST">
        @csrf
        
        <div class="space-y-5">
            <x-input
                name="username"
                placeholder="Username"
                :value="old('username')"
                :required="true"
                :error="$errors->first('username')"
            />

            <x-input
                name="password"
                placeholder="Password"
                type="password"
                :required="true"
                :error="$errors->first('password')"
            />

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                </label>
            </div>

            <x-button text="Masuk" class="w-full" type="submit" />
        </div>
    </form>

    <!-- Footer -->
    <div class="mt-6 text-center text-sm text-gray-500">
        <p>&copy; {{ date('Y') }} SiPelajar. All rights reserved.</p>
    </div>
</div>
@endsection