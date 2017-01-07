<?php
if (! empty('UT_LOCALE')) {
    setlocale(LC_ALL, getenv('UT_LOCALE'));
}

require_once 'vendor/autoload.php';

