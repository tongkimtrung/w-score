<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FormMode extends Enum
{
    const INSERT = 0;
    const UPDATE = 1;
    const DELETE = 2;
}
