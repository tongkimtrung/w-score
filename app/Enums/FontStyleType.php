<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FontStyleType extends Enum
{
    const Regular = 'Regular';
    const Italic = 'Italic';
    const Bold = 'Bold';
    const BoldItalic = 'Bold Italic';
    const Title = 'Title';
}
