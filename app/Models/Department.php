<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    public function examShifts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ExamShift::class, 'exam_shift_details', 'department_id', 'exam_shift_id');
    }

    public function examBanks(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ExamBank::class, 'exam_shift_details', 'department_id', 'exam_bank_id')->distinct();
    }


}
