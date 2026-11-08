<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PBKK Evolution')</title>
    @fonts
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --navbar-blue: #023880;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Instrument Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            position: sticky;
            z-index: 10;
            top: 0;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 1rem 2rem;
            background-color: var(--navbar-blue);
        }

        .navbar-start {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            justify-self: end;
            gap: 0.75rem;
        }

        .dark-mode-toggle {
            padding: 0.45rem 0.7rem;
            border: 1px solid rgb(255 255 255 / 55%);
            border-radius: 6px;
            background: transparent;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        .dark-mode-toggle:hover:not(:disabled) {
            background: rgb(255 255 255 / 14%);
        }

        .dark-mode-toggle:focus-visible {
            outline: 2px solid white;
            outline-offset: 3px;
        }

        .dark-mode-toggle:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .navbar-brand {
            color: white;
            font-weight: bold;
            text-decoration: none;
        }

        .login-menu {
            position: relative;
            color: white;
        }

        .login-menu summary {
            cursor: pointer;
            list-style: none;
        }

        .login-menu summary::-webkit-details-marker {
            display: none;
        }

        .login-menu summary::after {
            margin-left: 0.4rem;
            content: "▾";
        }

        .login-menu summary:focus-visible {
            outline: 2px solid white;
            outline-offset: 4px;
        }

        .login-menu-options {
            position: absolute;
            z-index: 11;
            top: calc(100% + 0.75rem);
            left: 0;
            min-width: 9rem;
            padding: 0.35rem;
            border: 1px solid #e0e7ef;
            border-radius: 8px;
            background: white;
            box-shadow: 0 8px 24px rgb(2 56 128 / 18%);
        }

        .login-menu-options a,
        .login-menu-options button {
            display: block;
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: #023880;
            font: inherit;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }

        .login-menu-options a:hover,
        .login-menu-options button:hover {
            background: #eff6ff;
        }

        .login-menu-options a:focus-visible {
            outline: 2px solid #023880;
            outline-offset: -2px;
        }

        .navbar-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        .navbar-toggle {
            display: none;
            padding: 0.45rem 0.7rem;
            border: 1px solid rgb(255 255 255 / 55%);
            border-radius: 6px;
            background: transparent;
            color: white;
            font: inherit;
            cursor: pointer;
        }

        .navbar-links a {
            color: white;
            text-decoration: none;
            font-size: small;
        }

        .navbar-links a:hover,
        .navbar-brand:hover {
            text-decoration: underline;
        }

        .navbar-toggle:focus-visible,
        .navbar-links a:focus-visible {
            outline: 2px solid white;
            outline-offset: 4px;
        }

        main {
            flex: 1;
            padding: 2rem;
        }

        .footer {
            padding: 1rem 2rem;
            background: #023880;
            color: white;
            text-align: center;
        }

        .footer a {
            color: white;
        }

        .nav-menu {
            position: relative;
            color: white;
        }

        .nav-menu summary {
            cursor: pointer;
            list-style: none;
            color: white;
            font-size: small;
        }

        .nav-menu summary::-webkit-details-marker {
            display: none;
        }

        .nav-menu summary::after {
            margin-left: 0.4rem;
            content: "▾";
        }

        .nav-menu summary:focus-visible {
            outline: 2px solid white;
            outline-offset: 4px;
        }

        .nav-menu-options {
            position: absolute;
            z-index: 11;
            top: calc(100% + 0.5rem);
            left: 0;
            min-width: 12rem;
            padding: 0.35rem;
            border: 1px solid #e0e7ef;
            border-radius: 8px;
            background: white;
            box-shadow: 0 8px 24px rgb(2 56 128 / 18%);
        }

        .nav-menu-options a {
            display: block;
            padding: 0.6rem 0.75rem;
            border-radius: 5px;
            color: #023880;
            text-decoration: none;
        }

        .nav-menu-options a:hover {
            background: #eff6ff;
        }

        /*mobile style */
        @media (max-width: 640px) {
            .navbar {
                grid-template-columns: 1fr auto;
                padding: 0.75rem 1rem;
            }

            .navbar-actions {
                grid-column: 2;
                grid-row: 1;
            }

            .navbar-toggle {
                display: block;
            }

            .navbar-links {
                position: absolute;
                top: 100%;
                right: 0;
                left: 0;
                display: none;
                flex-direction: column;
                gap: 0;
                padding: 0.5rem 1rem 1rem;
                background-color: var(--navbar-blue);
            }

            .navbar.is-open .navbar-links {
                display: flex;
            }

            .navbar-links a {
                padding: 0.75rem 0;
            }

            /*mobile style for dropdown menu*/
            .navbar-links .nav-menu > summary {
                padding: 0.75rem 0;
            }

            .navbar-links .nav-menu-options {
                position: static;
                min-width: 0;
                margin-left: 0.75rem;
                padding: 0;
                border: 0;
                background: transparent;
                box-shadow: none;
            }

            .navbar-links .nav-menu-options a {
                padding: 0.55rem 0.75rem;
                color: white;
            }
        }
    </style>
</head>
<body>
    @php
        $supportsDarkMode = request()->routeIs('home', 'project');
        $darkModeEnabled = request()->query('mode') === 'dark';
    @endphp
    <nav class="navbar" aria-label="Main navigation">
        <div class="navbar-start">
            <a class="navbar-brand" href="{{ route('home') }}">PBKK</a>
            <details class="login-menu">
                <summary>@auth {{ Auth::user()->name }} @else Account @endauth</summary>
                <div class="login-menu-options">
                    @guest
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register') }}">Register</a>
                        <a href="{{ route('local-login') }}">Local Login Demo</a>
                    @else
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <a href="{{ route('profile.edit') }}">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Log out</button>
                        </form>
                    @endguest
                </div>
            </details>
        </div>

        <div class="navbar-links" id="navbar-links">
            <details class="nav-menu">
                <summary>Minggu 1</summary>
                <div class="nav-menu-options">
                    <a href="{{ route('about') }}">About</a>
                    <a href="{{ route('project') }}">Project Idea</a>
                    <a href="{{ route('kalkulator', ['angka1' => 6, 'angka2' => 7, 'operasi' => 'modulo']) }}">Kalkulator</a>
                </div>
            </details>
            <details class="nav-menu">
                <summary>Minggu 2</summary>
                <div class="nav-menu-options">
                    <a href="{{ route('mahasiswa.show', ['nrp' => '5025241115']) }}">Mahasiswa</a>
                    <a href="{{ route('agent', ['tema' => 'gmail']) }}">Agent: Gmail</a>
                    <a href="{{ route('hitung-ipk.result', ['ip1' => 3.34, 'ip2' => 4]) }}">Hitung IPK</a>
                </div>
            </details>
            <details class="nav-menu">
                <summary>Minggu 4</summary>
                <div class="nav-menu-options">
                    <a href="{{ route('profilMahasiswa') }}">Profil-Mahasiswa</a>
                    <a href="{{ route('ideAgent') }}">Ide-Agent</a>
                    <a href="{{ route('form.get') }}">Form Masukan</a>
                    <a href="{{ route('local-login') }}">Local Login Demo</a>
                </div>
            </details>
            <details class="nav-menu">
                <summary>Minggu 5</summary>
                <div class="nav-menu-options">
                    @auth
                        <a href="{{ route('dashboard') }}">Portal Akademik</a>
                    @else
                        <a href="{{ route('login') }}">Login Portal</a>
                        <a href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
            </details>
            <details class="nav-menu">
                <summary>Minggu 6</summary>
                <div class="nav-menu-options">
                    @auth
                        <a href="{{ route('dashboard') }}">Demo Database</a>
                    @else
                        <a href="{{ route('login') }}">Login untuk Demo Database</a>
                    @endauth
                </div>
            </details>
        </div>

        <div class="navbar-actions">
            <button
                class="dark-mode-toggle"
                type="button"
                @disabled(! $supportsDarkMode)
                @if ($supportsDarkMode) aria-pressed="{{ $darkModeEnabled ? 'true' : 'false' }}" @else title="Dark mode is not available on this page" @endif
            >
                Dark mode{{ $supportsDarkMode ? ($darkModeEnabled ? ': On' : ': Off') : '' }}
            </button>
            <button class="navbar-toggle" type="button" aria-expanded="false" aria-controls="navbar-links">
                Menu
            </button>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <p>&copy; {{date('Y')}} PBKK Evolution</p>
        <p>Made by: Royan Habibi Alfatih</p>
        <a href="{{route('home')}}">Home</a>
    </footer>

    <script>
        const navbar = document.querySelector('.navbar');
        const navbarToggle = document.querySelector('.navbar-toggle');
        const darkModeToggle = document.querySelector('.dark-mode-toggle');

        navbarToggle.addEventListener('click', () => {
            const isOpen = navbar.classList.toggle('is-open');
            navbarToggle.setAttribute('aria-expanded', String(isOpen));
        });

        navbar.querySelectorAll('.navbar-links a').forEach((link) => {
            link.addEventListener('click', () => {
                navbar.classList.remove('is-open');
                navbarToggle.setAttribute('aria-expanded', 'false');
            });
        });

        darkModeToggle.addEventListener('click', () => {
            if (darkModeToggle.disabled) return;

            const url = new URL(window.location.href);
            if (url.searchParams.get('mode') === 'dark') {
                url.searchParams.delete('mode');
            } else {
                url.searchParams.set('mode', 'dark');
            }

            window.location.assign(url);
        });
    </script>
</body>
</html>