<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Style extends Enum
{
    const NORMAL = 'Normal';

    const HEADING_1 = 'Heading1';

    const TITLE = 'Title';

    const EMPHASIS = 'Emphasis';

    const OTHER = 'Other';
}
