<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Notifications\AttendanceCodeGenerated;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'registration_date',
        'status',
        'attendance_code',
        'qr_code_path',
        'checked_in_at',
        'checked_in_by',
        'check_in_notes'
    ];

    protected $casts = [
        'registration_date' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    /**
     * Boot method to generate attendance code when registration is created
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            $registration->attendance_code = static::generateUniqueAttendanceCode();
        });

        static::created(function ($registration) {
            // Send attendance code notification to user
            $registration->user->notify(new AttendanceCodeGenerated($registration));
        });
    }

    /**
     * Generate a unique 8-character attendance code
     */
    public static function generateUniqueAttendanceCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('attendance_code', $code)->exists());

        return $code;
    }

    /**
     * Check if user is checked in
     */
    public function isCheckedIn(): bool
    {
        return !is_null($this->checked_in_at);
    }

    /**
     * Mark as checked in
     */
    public function checkIn(string $checkedInBy = null, string $notes = null): void
    {
        $this->update([
            'checked_in_at' => now(),
            'checked_in_by' => $checkedInBy,
            'check_in_notes' => $notes,
            'status' => 'attended'
        ]);
    }

    /**
     * Get the event that owns the registration.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that owns the registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
