<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FileType extends Enum
{
    const XLSX =   0;
    const ZIP =   1;
    const LIST = 2;
    const EXAM = 3;
}
