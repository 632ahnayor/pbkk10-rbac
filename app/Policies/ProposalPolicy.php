<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function update(User $user, Proposal $proposal): bool
    {
        return $user->hasRole('mahasiswa') && $user->id === $proposal->user_id;
    }
}
