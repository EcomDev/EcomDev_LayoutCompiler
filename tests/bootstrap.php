<?php


$baseDirectory = dirname(__DIR__);

include $baseDirectory . '/vendor/autoload.php';


set_include_path(
    implode(PATH_SEPARATOR, 
        array(
            'build/lib',
            'build/app/code/local',
            'build/app/code/community',
            'build/app/code/core',
            get_include_path()
        )
    )
);

spl_autoload_register(function ($className) {
    $filePath = strtr($className, array(
        '_' => DIRECTORY_SEPARATOR,
        '\\' => DIRECTORY_SEPARATOR
    )) . '.php';
    
    if (!stream_resolve_include_path($filePath)) {
        return;
    }

    include $filePath;
});