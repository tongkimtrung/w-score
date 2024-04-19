<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class StyleType extends Enum
{
    const PARAGRAPH =   0;
    const TEXT_BOX = 1;

    const NORMAL = 'Normal';
    const NO_SPACING = 3;

    const HEADING_1 = 'Heading1';
    const HEADING_2 = 5;
    const TITLE = 'Title';
    const SUB_TITLE = 7;
    const SUBTLE_EMPHASIS = 8;
    const EMPHASIS = 'Emphasis';
    const INTENSE_EMPHASIS = 10;
    const STRONG = 11;
    const QUOTE = 12;
    const INTENSE_QUOTE = 13;
    const SUBTLE_REFERENCE = 14;
    const INTENSE_REFERENCE = 15;
    const BOOK_TITLE = 16;
    const LIST_PARAGRAPH = 17;
    const OTHER = 18;
}
