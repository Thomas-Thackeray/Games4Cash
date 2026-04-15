<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $fillable = ['name', 'email', 'contact_number', 'message', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markRead(): void
    {
        if (! $this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }
}
