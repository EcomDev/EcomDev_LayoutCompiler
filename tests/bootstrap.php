<?php


$baseDirectory = dirname(__DIR__);

include $baseDirectory . '/vendor/autoload.php';


set_include_path(
    implode(PATH_SEPARATOR, 
        array(
            $baseDirectory . '/src/lib',
            $baseDirectory . '/tests/lib',
            get_include_path()
        )
    )
);

spl_autoload_register(function ($className) {
    $filePath = strtr($className, array(
        '_' => DIRECTORY_SEPARATOR,
        '\\' => DIRECTORY_SEPARATOR
    )) . '.php';

    $filePath = stream_resolve_include_path($filePath);

    if (!$filePath) {
        return;
    }

    include $filePath;
});