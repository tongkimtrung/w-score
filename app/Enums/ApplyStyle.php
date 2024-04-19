<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ApplyStyle extends Enum
{
    const ALL = 'Tất cả';
    const PARAGRAPH = 'Đoạn văn';
    const OTHER = 'Khác';
}
