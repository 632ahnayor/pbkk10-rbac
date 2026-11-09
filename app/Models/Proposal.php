<?php

namespace App\Models;

use Database\Factories\ProposalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'title', 'description', 'grade'])]
class Proposal extends Model
{
    /** @use HasFactory<ProposalFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
