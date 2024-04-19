<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_shift_code',
        'exam_shift_name',
        'exam_id',
        'start_date',
        'end_date',
        'note',
    ];

    public function departments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'exam_shift_details', 'exam_shift_id', 'department_id')->distinct();
    }

    public function examBanks(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ExamBank::class, 'exam_shift_details', 'exam_shift_id', 'exam_bank_id')->distinct();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function examShiftDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamShiftDetail::class);
    }
}
