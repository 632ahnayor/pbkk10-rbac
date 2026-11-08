<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    @if (app()->environment('local'))
        <section class="mt-6 border-t border-gray-200 pt-4" aria-labelledby="demo-login-heading">
            <h2 id="demo-login-heading" class="text-sm font-medium text-gray-700">Quick login akun demo</h2>
            <div class="mt-3 grid grid-cols-2 gap-2">
                @foreach (['andi' => 'Andi (Mahasiswa)', 'budi' => 'Budi (Mahasiswa)', 'asdos' => 'Asdos', 'dosen' => 'Dosen', 'week6-mahasiswa-01' => 'Minggu 6: Mahasiswa 01'] as $account => $label)
                    <form method="POST" action="{{ route('login.demo', ['account' => $account]) }}">
                        @csrf
                        <button class="inline-flex w-full justify-center rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500" type="submit">
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-gray-500">Hanya tersedia pada environment local untuk keperluan demo.</p>
        </section>
    @endif
</x-guest-layout>
