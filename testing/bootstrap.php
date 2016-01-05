<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app_autoload_include = dirname(__DIR__) . '/app/config/includes/autoload.php';
if (is_readable($app_autoload_include)) {
    require($app_autoload_include);
}
