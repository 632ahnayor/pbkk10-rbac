@extends('adminlte::page')

@section('title', 'Proposal')

@section('content_header')
    <h1>Daftar Proposal</h1>
@endsection

@section('content')
    @if (session('status'))
        <x-adminlte-alert theme="success" dismissable>{{ session('status') }}</x-adminlte-alert>
    @endif

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.proposals.index') }}" class="row g-2 align-items-end" role="search">
                <div class="col-md-6">
                    <label for="search" class="form-label">Cari judul atau nama mahasiswa</label>
                    <input id="search" name="search" type="search" value="{{ request('search') }}" class="form-control" placeholder="Contoh: Agent atau Andi">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status nilai</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="dinilai" @selected(request('status') === 'dinilai')>Sudah dinilai</option>
                        <option value="belum" @selected(request('status') === 'belum')>Belum dinilai</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search" aria-hidden="true"></i> Filter</button>
                    <a href="{{ route('admin.proposals.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Nilai</th>
                            <th scope="col">Diajukan</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($proposals as $proposal)
                            <tr>
                                <td>{{ $proposals->firstItem() + $loop->index }}</td>
                                <td>{{ $proposal->title }}</td>
                                <td>{{ $proposal->user->name }}</td>
                                <td>
                                    @if ($proposal->grade === null)
                                        <span class="badge text-bg-warning">Belum dinilai</span>
                                    @else
                                        <span class="badge text-bg-success">{{ $proposal->grade }}</span>
                                    @endif
                                </td>
                                <td>{{ $proposal->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    @can('kelola-nilai')
                                        <a href="{{ route('admin.grades.index') }}" class="btn btn-sm btn-outline-primary">Nilai</a>
                                    @endcan
                                    @can('hapus-proposal')
                                        <form method="POST" action="{{ route('admin.proposals.destroy', $proposal) }}" class="d-inline"
                                            onsubmit="return confirm('Hapus proposal ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-body-secondary py-4">Tidak ada proposal yang cocok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-body-secondary">Total {{ $proposals->total() }} proposal</small>
            {{ $proposals->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
