<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class FontType extends Enum
{
    const CAMBRIA = 'Cambria';
    const CALIBRI = 'Calibri';
    const ARIAL =  'Arial';
    const TIME_NEW_ROMAN = 'Times New Roman';
    const TAHOMA = 'Tahoma';
}
