<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ProposalController extends Controller implements HasMiddleware
{
    /**
     * Pengganti `$this->middleware()` di constructor pada Laravel 11+.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:lihat-proposal', only: ['index']),
            new Middleware('permission:hapus-proposal', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $status = $request->input('status');

        $proposals = Proposal::query()
            ->with('user')
            ->when($request->input('search'), function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status === 'dinilai', fn ($query) => $query->whereNotNull('grade'))
            ->when($status === 'belum', fn ($query) => $query->whereNull('grade'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.proposals.index', [
            'proposals' => $proposals,
        ]);
    }

    public function destroy(Proposal $proposal): RedirectResponse
    {
        $proposal->delete();

        return redirect()->back()->with('status', "Proposal \"{$proposal->title}\" berhasil dihapus.");
    }
}
