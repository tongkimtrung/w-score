<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static insert(array $attribute)
 */
class ExamBank extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_bank_name',
        'exam_bank_code',
    ];

    public function criterias(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Criteria::class, 'exam_bank_id')->orderBy('priority', 'DESC');
    }

    public function departments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'exam_shift_details', 'exam_bank_id', 'department_id')->distinct();
    }

}
