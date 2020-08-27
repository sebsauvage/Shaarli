<?php

declare(strict_types=1);

namespace Shaarli\Front\Exception;

class ResourcePermissionException extends ShaarliFrontException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 500);
    }
}
