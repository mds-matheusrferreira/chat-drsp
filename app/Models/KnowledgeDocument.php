<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeDocument extends Model
{
    protected $fillable = [
        'title',
        'original_name',
        'stored_path',
        'mime_type',
        'extension',
        'size_bytes',
        'status',
        'chunks_count',
        'error_message',
    ];

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }
}
