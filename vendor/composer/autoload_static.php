<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita536e92fab31ef53eef8262dd2d4adf8
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'TagGallery\\' => 11,
        ),
        'I' => 
        array (
            'Iframous\\' => 9,
        ),
        'D' => 
        array (
            'DiDom\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'TagGallery\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/TagGallery',
        ),
        'Iframous\\' => 
        array (
            0 => __DIR__ . '/..' . '/joannesource/iframous/src/Iframous',
        ),
        'DiDom\\' => 
        array (
            0 => __DIR__ . '/..' . '/imangazaliev/didom/src/DiDom',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PHPThumb\\Tests' => 
            array (
                0 => __DIR__ . '/..' . '/masterexploder/phpthumb/tests',
            ),
            'PHPThumb' => 
            array (
                0 => __DIR__ . '/..' . '/masterexploder/phpthumb/src',
            ),
        ),
        'B' => 
        array (
            'Bramus' => 
            array (
                0 => __DIR__ . '/..' . '/bramus/router/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita536e92fab31ef53eef8262dd2d4adf8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita536e92fab31ef53eef8262dd2d4adf8::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInita536e92fab31ef53eef8262dd2d4adf8::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
