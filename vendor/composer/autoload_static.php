<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit61b0cc7ad1cec692284de25c52d37a5d
{
    public static $files = array (
        '3111be4b01bb0347208291ccec4b5e60' => __DIR__ . '/../..' . '/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'VikCal\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'VikCal\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit61b0cc7ad1cec692284de25c52d37a5d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit61b0cc7ad1cec692284de25c52d37a5d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}