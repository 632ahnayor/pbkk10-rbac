@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard Admin</h1>
@endsection

@section('content')
    <p class="text-body-secondary">
        Selamat datang, {{ Auth::user()->name }}
        <span class="badge text-bg-secondary">{{ Auth::user()->getRoleNames()->join(', ') }}</span>
    </p>

    <div class="row">
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box :title="$totalMahasiswa" text="Total Mahasiswa" icon="bi bi-people-fill" theme="primary" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box :title="$totalProposal" text="Total Proposal" icon="bi bi-file-earmark-text-fill" theme="success"
                :url="Auth::user()->can('lihat-proposal') ? route('admin.proposals.index') : null" url-text="Lihat proposal" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box :title="$proposalBelumDinilai" text="Proposal Belum Dinilai" icon="bi bi-hourglass-split" theme="warning"
                :url="Auth::user()->can('lihat-proposal') ? route('admin.proposals.index', ['status' => 'belum']) : null" url-text="Lihat daftar" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box :title="$totalProject" text="Total Project Agentic AI" icon="bi bi-robot" theme="danger" />
        </div>
    </div>

    @role('staf')
        <div class="alert alert-info">
            Akun staf memiliki akses terbatas: dapat melihat proposal, tetapi tidak dapat menilai atau menghapus data.
        </div>
    @endrole
@endsection
