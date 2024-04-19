<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FooterType extends Enum
{
    const BLANK = 'Blank';
    const DEFAULT = 'default';
    const WHISP = 'Whisp';
    const GRID = 'Grid';
}
