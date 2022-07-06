<?php
/**
 * bin/autoload.php
 *
 * Handles the initial setup like autoloading, basic functions, constants, etc.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore;

use TempusProjectCore\Classes\Autoloader;

if (!defined('TPC_ROOT_DIRECTORY')) {
    define('TPC_ROOT_DIRECTORY', dirname(__DIR__) . DIRECTORY_SEPARATOR);
}
if (!defined('TPC_CONFIG_DIRECTORY')) {
    define('TPC_CONFIG_DIRECTORY', TPC_ROOT_DIRECTORY . 'config' . DIRECTORY_SEPARATOR);
}
if (! defined('TEMPUS_CORE_CONSTANTS_LOADED')) {
    require_once TPC_CONFIG_DIRECTORY . 'constants.php';
}
if ( ! class_exists( 'TempusProjectCore\Classes\Autoloader' )) {
    if (file_exists(TPC_CLASSES_DIRECTORY . 'autoloader.php')) {
        require_once TPC_CLASSES_DIRECTORY . 'autoloader.php';
    }
}
if ( ! class_exists( 'TempusProjectCore\App' )) {
    if (file_exists(TPC_ROOT_DIRECTORY . 'app.php')) {
        require_once TPC_ROOT_DIRECTORY . 'app.php';
    }
}

// require_once TPC_FUNCTIONS_DIRECTORY . 'common.php';
$autoloader = new Autoloader;
$autoloader->setRootFolder( TPC_ROOT_DIRECTORY );
$autoloader->addNamespace(
    'TempusProjectCore',
    'core'
);
$autoloader->addNamespace(
    'TempusProjectCore\Classes',
    'classes'
);
$autoloader->addNamespace(
    'TempusProjectCore\Functions',
    'functions'
);
$autoloader->addNamespace(
    'TempusProjectCore\Template',
    'core' . DIRECTORY_SEPARATOR . 'template'
);
$autoloader->register();
define('TEMPUS_CORE_AUTOLOADED', true);