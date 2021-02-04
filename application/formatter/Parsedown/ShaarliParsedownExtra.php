<?php

declare(strict_types=1);

namespace Shaarli\Formatter\Parsedown;

/**
 * ParsedownExtra extension for Shaarli.
 *
 * Extension for both Parsedown and ParsedownExtra centralized in ShaarliParsedownTrait.
 */
class ShaarliParsedownExtra extends \ParsedownExtra
{
    use ShaarliParsedownTrait;
}
