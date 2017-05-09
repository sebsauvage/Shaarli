<?php

require_once 'vendor/autoload.php';

$conf = new \Shaarli\Config\ConfigManager('tests/utils/config/configJson');
new \Shaarli\Languages('en', $conf);
