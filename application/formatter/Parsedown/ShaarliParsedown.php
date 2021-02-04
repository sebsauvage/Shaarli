<?php

declare(strict_types=1);

namespace Shaarli\Formatter\Parsedown;

/**
 * Parsedown extension for Shaarli.
 *
 * Extension for both Parsedown and ParsedownExtra centralized in ShaarliParsedownTrait.
 */
class ShaarliParsedown extends \Parsedown
{
    use ShaarliParsedownTrait;
}
