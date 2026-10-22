<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['listing_document_id', 'reviewer_id', 'action', 'notes', 'snapshot'])]
class DocumentReview extends Model
{
    protected function casts(): array
    {
        return ['snapshot' => 'array'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ListingDocument::class, 'listing_document_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
