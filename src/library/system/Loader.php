<?php

namespace App\System;

use App\System\Pattern\Singleton;

// First, include necessary Pattern classes
require_once __DIR__ . '/pattern/Base.trait.php';
require_once __DIR__ . '/pattern/Singleton.trait.php';

class Loader
{
    use Singleton;

    public function init()
    {
        spl_autoload_register(array(__CLASS__, '_loadClasses'));
    }

    private function _loadClasses($sClass)
    {
        // Remove namespace and backslash
        $sClass = trim(str_replace('//', '/', str_replace(array(__NAMESPACE__, 'App', '\\'), '/', $sClass)), '/');

        if (is_file(__DIR__ . '/' . $sClass . '.php'))
            require_once __DIR__ . '/' . $sClass . '.php';

        if (is_file(ROOT_PATH . $sClass . '.php')) {
            require_once ROOT_PATH . $sClass . '.php';
        }
    }
}
