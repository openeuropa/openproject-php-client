<?php

declare(strict_types=1);

namespace OpenEuropa\OpenProjectClient\Model;

/**
 * Formats of a formattable text's raw source.
 */
enum TextFormat: string
{
    case PLAIN = 'plain';
    case MARKDOWN = 'markdown';
    case CUSTOM = 'custom';
}
