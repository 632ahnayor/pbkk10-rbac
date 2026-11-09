@extends('adminlte::page')

@section('title', 'Input Nilai')

@section('content_header')
    <h1>Input Nilai Proposal</h1>
@endsection

@section('content')
    @if (session('status'))
        <x-adminlte-alert theme="success" dismissable>{{ session('status') }}</x-adminlte-alert>
    @endif

    <div class="card">
        <ul class="list-group list-group-flush">
            @forelse ($proposals as $proposal)
                <li class="list-group-item d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h2 class="h6 mb-1">{{ $proposal->title }}</h2>
                        <small class="text-body-secondary">{{ $proposal->user->name }} · Nilai saat ini: {{ $proposal->grade ?? 'Belum dinilai' }}</small>
                    </div>
                    @can('kelola-nilai')
                        <form method="POST" action="{{ route('admin.grades.update', $proposal) }}" class="d-flex align-items-end gap-2">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="grade-{{ $proposal->id }}" class="form-label small mb-1">Nilai (0–100)</label>
                                <input id="grade-{{ $proposal->id }}" name="grade" type="number" min="0" max="100" step="0.01" value="{{ old('grade', $proposal->grade) }}" required class="form-control form-control-sm" style="width: 8rem">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                        </form>
                    @endcan
                </li>
            @empty
                <li class="list-group-item text-body-secondary">Belum ada proposal untuk dinilai.</li>
            @endforelse
        </ul>
    </div>
@endsection
