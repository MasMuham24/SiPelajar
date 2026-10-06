<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\Office;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'teacher_id',
        'classroom_id',
        'date',
        'check_in',
        'check_out',
        'latitude',
        'longitude',
        'distance',
        'status',
        'late_minutes',
        'note',

        'verified_by',
        'verified_at',
        'teacher_note',
    ];

    protected $casts = [
        'date' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function student()
    {

        return $this->belongsTo(Student::class);

    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function getCheckInAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function getCheckOutAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function getLateMinutesAttribute($value)
    {
        return abs((int) $value);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
