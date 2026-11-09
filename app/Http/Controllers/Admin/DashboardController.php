<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalMahasiswa' => User::role('mahasiswa')->count(),
            'totalProposal' => Proposal::count(),
            'proposalBelumDinilai' => Proposal::whereNull('grade')->count(),
            'totalProject' => Project::count(),
        ]);
    }
}
