<?php
require_once 'tests/bootstrap.php';

if (! empty(getenv('UT_LOCALE'))) {
    setlocale(LC_ALL, getenv('UT_LOCALE'));
}
