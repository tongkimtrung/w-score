<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class PropertyType extends Enum
{
    const FONT = 0;
    const FONT_STYLE = 1;
    const FONT_SIZE = 2;
    const FONT_COLOR = 3;
    const SPECIAL_HANGING = 4;
    const SPACING_BEFORE = 5;
    const SPACING_AFTER = 6;
    const INDENTATION_LEFT = 7;
    const IMAGE = 8;
    const NAME_IMAGE = 9;
    const WIDTH_IMAGE = 10;
    const HIGH_IMAGE = 11;
    const MODIFY_STYLE = 12;
    const STYLE = 13;
    const STYLE_ALL = 14;
    const HEADER = 15;
    const HEADER_ALL = 16;
    const FOOTER_TYPE = 17;
    const FOOTER_TYPE_ALL = 18;
    const FOOTER = 19;
    const FOOTER_ALL = 20;
    const PAGE_SIZE = 21;
    const PAGE_SIZE_ALL = 22;

    const MARGIN_LEFT_ALL = 23;
    const MARGIN_RIGHT_ALL = 24;
    const MARGIN_TOP_ALL = 25;
    const MARGIN_BOTTOM_ALL = 26;
    const MARGIN_LEFT = 27;
    const MARGIN_RIGHT = 28;
    const MARGIN_TOP = 29;
    const MARGIN_BOTTOM = 30;
    const TITLE = 32;
    const AUTHOR = 33;
    const NUMBERING = 34;
    const ALIGNMENT_LEFT = 35;
    const ALIGNMENT_CENTERD = 36;
    const ALIGNMENT_RIGHT = 37;
    const ALIGNMENT_JUSTIFITED = 38;
    const LINE_SPACING = 39;
    const ALL_CAPS = 40;
    const FOOTNOTE = 41;
    const INSERT_FOOTNOTE = 42;
    const FOOTNOTE_CONTENT = 43;
    const FOOTNOTE_TYPE = 44;
    const APPLY_STYLE = 45;
    const APPLY_STYLE_ALL = 46;
    const MODIFY_STYLE_AND_APPLY = 47;
    const FILE_NAME = 48;
    const ALIGNMENT = 49;
}
