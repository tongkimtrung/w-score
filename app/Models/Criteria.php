<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{
    use HasFactory;

    public function examBank()
    {
        return $this->belongsTo(ExamBank::class, 'exam_bank_id');
    }
}
