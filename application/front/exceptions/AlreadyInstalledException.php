<?php

declare(strict_types=1);

namespace Shaarli\Front\Exception;

class AlreadyInstalledException extends ShaarliFrontException
{
    public function __construct()
    {
        $message = t('Shaarli has already been installed. Login to edit the configuration.');

        parent::__construct($message, 401);
    }
}
