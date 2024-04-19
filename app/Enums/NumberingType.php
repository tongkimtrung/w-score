<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class NumberingType extends Enum
{
    const NUMBER_123 = '1, 2, 3, ...';
    const NUMBER_ABC = 'a, b, c, ...';
    const LOWER_CASE_ROMAN_NUMBER = 'i, ii, iii, ...';
    const NUMBER_MATH = '*, +, ..';
}
