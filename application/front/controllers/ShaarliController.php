<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller;

use Shaarli\Container\ShaarliContainer;

abstract class ShaarliController
{
    /** @var ShaarliContainer */
    protected $ci;

    /** @param ShaarliContainer $ci Slim container (extended for attribute completion). */
    public function __construct(ShaarliContainer $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Assign variables to RainTPL template through the PageBuilder.
     *
     * @param mixed $value Value to assign to the template
     */
    protected function assignView(string $name, $value): self
    {
        $this->ci->pageBuilder->assign($name, $value);

        return $this;
    }
}
