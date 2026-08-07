<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'gender',
        'phone',
        'address',
        'photo',
        'classroom_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function verifiedAttendances()
    {
        return $this->hasMany(
            Attendance::class,
            'verified_by'
        );
    }
}
