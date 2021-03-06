<?php
/**
 * Autoloader for CImage and related class files.
 *
 */
//include __DIR__ . "/../CHttpGet.php";
//include __DIR__ . "/../CRemoteImage.php";
//include __DIR__ . "/../CImage.php";

/**
 * Autoloader for classes.
 *
 * @param string $class the fully-qualified class name.
 *
 * @return void
 */
spl_autoload_register(function ($class) {
    //$path = CIMAGE_SOURCE_PATH . "/{$class}.php";
    $path = __DIR__ . "/{$class}.php";
    if(is_file($path)) {
        require($path);
    }
});
