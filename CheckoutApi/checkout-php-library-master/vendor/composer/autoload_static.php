<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit392df0ba389326b82c3cddb185dd2f7d
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'com\\checkout\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'com\\checkout\\' => 
        array (
            0 => __DIR__ . '/../..' . '/com/checkout',
        ),
    );

    public static $prefixesPsr0 = array (
        'C' => 
        array (
            'CheckoutApi_' => 
            array (
                0 => __DIR__ . '/../..' . '/com/checkout/packages',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit392df0ba389326b82c3cddb185dd2f7d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit392df0ba389326b82c3cddb185dd2f7d::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit392df0ba389326b82c3cddb185dd2f7d::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
