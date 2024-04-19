<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyAlias;

/**
 * @method static insert(array $attribute)
 */
class Exam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_code',
        'exam_name',
        'start_date',
        'end_date',
        'note',
    ];

    /**
     * @return HasManyAlias
     */
    public function examShifts(): HasManyAlias
    {
        return $this->hasMany(ExamShift::class);
    }
}
