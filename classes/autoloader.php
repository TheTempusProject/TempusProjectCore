<?php
/**
 * classes/autoloader.php
 *
 * This should provide a simple way of adding autoloading.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Classes;

class Autoloader
{
    protected static $namespaces = array();
    protected $rootFolder = '';

    /**
     * Sets the root folder for file paths.
     *
     * @param {string} [$folder]
     */
    public function setRootFolder( $folder )
    {
        $this->rootFolder = rtrim( $folder, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
    }

    /**
     * Registers a new autoloader that should serve the currently defined namespaces.
     */
    public function register()
    {
        spl_autoload_register( array( $this, 'loadClass' ) );
    }

    /**
     * Automaticly requires all the files within a given directory.
     *
     * @param {string} [$directory]
     * @param {bool} [$includeRoot]
     */
    public function includeFolder( $directory = '', $includeRoot = true )
    {
        $base_dir = str_replace( '\\', DIRECTORY_SEPARATOR, $directory );
        $base_dir = str_replace( '/', DIRECTORY_SEPARATOR, $base_dir );
        $base_dir = rtrim( $base_dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
        // should require all the files in the specific folder
        if ( $includeRoot ) {
            $base_dir = $this->rootFolder . $base_dir;
        }
        if ( ! is_dir( $base_dir ) ) {
            Debug::warn("Autoload folder is missing: $base_dir");
            return false;
        }
        $files = scandir($base_dir);
        array_shift($files);
        array_shift($files);
        foreach ($files as $key => $value) {
            if (stripos($value, '.php')) {
                include_once $base_dir . $value;
            }
        }
        return true;
    }

    /**
     * Adds a namespace and corresponding directory to the autoload list.
     *
     * @param {string} [$namespace]
     * @param {string} [$directory]
     * @param {bool} [$includeRoot]
     */
    public function addNamespace( $namespace, $directory = '', $includeRoot = true )
    {
        // normalize namespace prefix
        $prefix = trim( $namespace, '\\' ) . '\\';

        // normalize directory
        $base_dir = str_replace( '\\', DIRECTORY_SEPARATOR, $directory );
        $base_dir = str_replace( '/', DIRECTORY_SEPARATOR, $base_dir );
        $base_dir = rtrim( $base_dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

        if ($base_dir === DIRECTORY_SEPARATOR) {
            $base_dir = '';
        }

        if ( empty( self::$namespaces[ $prefix ] ) ) {
            self::$namespaces[ $prefix ] = array();
        }

        // retain the base directory for the namespace prefix
        if ( $includeRoot ) {
            $base_dir = $this->rootFolder . $base_dir;
        }
        array_push( self::$namespaces[ $prefix ], $base_dir );
    }

    /**
     * This is the main method for the autoloader. It will cycle through
     * possible locations and load the first available file.
     *
     * @param {string} [$class]
     */
    public function loadClass( $class )
    {
        $class = trim( $class, '\\' );
        $namespace_array = explode( '\\', $class );
        $class_name = array_pop( $namespace_array );
        $namespace = implode( '\\', $namespace_array ) . '\\';

        if ( empty( self::$namespaces[ $namespace ] ) ) {
            return false;
        }

        $file = lcfirst( $class_name ) . '.php';
        $ucSplit = preg_split('/(?=[A-Z])/',$file);
        $file_underscore = strtolower( implode('_', $ucSplit) );
        $possible_locations = array();

        foreach ( self::$namespaces[ $namespace ] as $key => $folder ) {
            if ( file_exists ( $folder . $file ) ) {
                $possible_locations[] = $folder . $file;
            } elseif ( file_exists ( $folder . $file_underscore ) ) {
                $possible_locations[] = $folder . $file_underscore;
            }
        }

        // foreach ( $possible_locations as $location ) {
        //     // report the locations
        // }
        if ( !empty($possible_locations)) {
            require_once $possible_locations[0];
        }
    }

    public function getNamespaces() {
        return self::$namespaces;
    }
    public function getRootFolder() {
        return $this->rootFolder;
    }
    public static function testLoad( $class ) {
        $class = trim( $class, '\\' );
        $namespace_array = explode( '\\', $class );
        $class_name = array_pop( $namespace_array );
        $namespace = implode( '\\', $namespace_array ) . '\\';

        if ( empty( self::$namespaces[ $namespace ] ) ) {
            return false;
        }

        $file = lcfirst( $class_name ) . '.php';
        $ucSplit = preg_split('/(?=[A-Z])/',$file);
        $file_underscore = strtolower( implode('_', $ucSplit) );
        $possible_locations = array();

        foreach ( self::$namespaces[ $namespace ] as $key => $folder ) {
            if ( file_exists ( $folder . $file ) ) {
                $possible_locations[] = $folder . $file;
            } elseif ( file_exists ( $folder . $file_underscore ) ) {
                $possible_locations[] = $folder . $file_underscore;
            }
        }

        if ( !empty($possible_locations)) {
            return true;;
        }
        return false;
    }
}
