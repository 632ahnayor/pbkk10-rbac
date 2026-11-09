<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Portal Akademik
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <section class="mb-6 rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-medium text-gray-900">Portofolio Project Agentic AI</h3>
                @forelse ($projects as $project)
                    <article class="mt-4 rounded-md border border-gray-200 p-4">
                        <h4 class="font-semibold text-gray-900">{{ $project->judul }}</h4>
                        @if ($project->deskripsi)
                            <p class="mt-2 text-sm text-gray-600">{{ $project->deskripsi }}</p>
                        @endif
                        <p class="mt-2 text-sm text-gray-600">Tema agent: {{ $project->tema_agent }}</p>
                    </article>
                @empty
                    <p class="mt-4 text-sm text-gray-600">Belum ada project dalam portofolio Anda.</p>
                @endforelse
            </section>

            @role('mahasiswa')
                <section class="mb-6 rounded-lg bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Proposal Proyek AI</h3>
                            <p class="mt-1 text-sm text-gray-600">Kelola usulan proyek yang Anda buat.</p>
                        </div>
                        <a class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700" href="{{ route('proposals.create') }}">
                            Buat Proposal
                        </a>
                    </div>
                    @forelse ($proposals as $proposal)
                        <article class="mt-4 rounded-md border border-gray-200 p-4">
                            <h4 class="font-semibold text-gray-900">{{ $proposal->title }}</h4>
                            @if ($proposal->description)
                                <p class="mt-2 text-sm text-gray-600">{{ $proposal->description }}</p>
                            @endif
                            <p class="mt-2 text-sm text-gray-600">
                                Nilai: {{ $proposal->grade === null ? 'Belum dinilai' : $proposal->grade }}
                            </p>
                            @can('update', $proposal)
                                <a class="mt-3 inline-block text-sm text-indigo-700 underline" href="{{ route('proposals.edit', $proposal) }}">
                                    Ubah judul
                                </a>
                            @endcan
                        </article>
                    @empty
                        <p class="mt-4 text-sm text-gray-600">Anda belum mengajukan proposal.</p>
                    @endforelse
                </section>
            @else
                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900">Selamat datang, {{ Auth::user()->name }}</h3>
                    <p class="mt-1 text-sm text-gray-600">Peran: {{ Auth::user()->getRoleNames()->join(', ') }}</p>
                    @hasanyrole(App\Models\User::PANEL_ROLES)
                        <a class="mt-4 inline-block text-sm text-indigo-700 underline" href="{{ route('admin.dashboard') }}">
                            Buka panel admin
                        </a>
                    @endhasanyrole
                </section>
            @endrole
        </div>
    </div>
</x-app-layout>
