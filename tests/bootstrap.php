<?php

namespace GoogleAuthenticatorTest;

use RuntimeException;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

class bootstrap
{
    public static function init()
    {

        static::initAutoloader();
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (is_readable($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
        } else {
            throw new RuntimeException('Unable to setup the composer autoload. Run `php composer.phar install`.');
        }
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) return false;
            $previousDir = $dir;
        }

        return $dir . '/' . $path;
    }
}

Bootstrap::init();
