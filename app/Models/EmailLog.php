<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'subject',
        'body',
        'sent_at',
        'sent_by',
    ];

    // ✅ Add this so sent_at becomes a Carbon date
    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // (Optional) relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
