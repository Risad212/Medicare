<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodIssue extends Model
{
    protected $fillable = [
        'request_id',
        'patient_id',
        'blood_group_id',
        'donation_id',
        'quantity',
        'unit',
        'issue_date',
        'issued_by',
        'receiver_name',
        'receiver_phone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'quantity' => 'integer',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function bloodGroup(): BelongsTo
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(BloodDonation::class, 'donation_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
