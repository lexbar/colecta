<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__.'/../vendor/autoload.php';

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/stubs/functions.php';

    $loader->add('', __DIR__.'/stubs');
}

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader; 
