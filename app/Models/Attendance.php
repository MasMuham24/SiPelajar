<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_date',
        'check_in',
        'check_out',
        'latitude',
        'longitude',
        'status',
        'late_minutes',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in' => 'datetime:H:i:s',
            'check_out' => 'datetime:H:i:s',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
