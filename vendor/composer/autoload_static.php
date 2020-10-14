<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf943f3fd656b93dbda9aba46b07ef7e5
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PagoFacilCore\\' => 14,
        ),
        'J' => 
        array (
            'Jopin172\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PagoFacilCore\\' => 
        array (
            0 => __DIR__ . '/..' . '/pstpagofacil/pagofacil-core-php/src',
        ),
        'Jopin172\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Jopin172',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf943f3fd656b93dbda9aba46b07ef7e5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf943f3fd656b93dbda9aba46b07ef7e5::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
