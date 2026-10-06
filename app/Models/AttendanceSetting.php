<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'school_start_time',
        'school_end_time',
    ];

    /**
     * Ambil pengaturan absensi aktif (singleton).
     */
    public static function getSettings(): self
    {
        try {
            return static::firstOrCreate(
                [],
                [
                    'school_start_time' => '07:00:00',
                    'school_end_time' => '15:00:00',
                ]
            );
        } catch (\Throwable $e) {
            return new static([
                'school_start_time' => '07:00:00',
                'school_end_time' => '15:00:00',
            ]);
        }
    }

    /**
     * Dapatkan batas waktu jam masuk untuk tanggal tertentu.
     */
    public function getStartLimit(CarbonInterface $date): CarbonInterface
    {
        [$hour, $minute, $second] = array_pad(explode(':', $this->school_start_time ?? '07:00:00'), 3, 0);
        return $date->copy()->setTime((int) $hour, (int) $minute, (int) $second);
    }

    /**
     * Dapatkan batas waktu awal checkout untuk tanggal tertentu.
     */
    public function getEndLimit(CarbonInterface $date): CarbonInterface
    {
        [$hour, $minute, $second] = array_pad(explode(':', $this->school_end_time ?? '15:00:00'), 3, 0);
        return $date->copy()->setTime((int) $hour, (int) $minute, (int) $second);
    }

    /**
     * Format jam masuk dalam H:i.
     */
    public function getFormattedStartTime(): string
    {
        return substr($this->school_start_time ?? '07:00:00', 0, 5);
    }

    /**
     * Format jam pulang dalam H:i.
     */
    public function getFormattedEndTime(): string
    {
        return substr($this->school_end_time ?? '15:00:00', 0, 5);
    }
}
