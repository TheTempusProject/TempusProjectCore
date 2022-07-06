<?php
/**
 * core/template.php
 *
 * This class is responsible for all visual output for the application.
 * This class also contains all the functions for parsing data outputs
 * into HTML, including: bbcodes, the data replacement structure, the
 * filters, and other variables used to display application content.
 *
 * @todo    centralize storage of the filters and patterns.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore;

use TempusProjectCore\Functions\{
    Debug, Routes, Token, Session
};
use TempusProjectCore\Classes\{
    Config, CustomException
};
use TempusProjectCore\Template\ {
    Components, Forms, Filters, Pagination
};
use \DateTime;

class Template
{
    /////////////////////////
    // Meta-data Variables //
    /////////////////////////

    private static $follow = true;
    private static $index = true;
    private static $additionalLocations = array();
    private static $templateLocation = null;
    protected static $content = null;

    /**
     * The constructor automatically sets a few $values and variables
     * the template will need.
     */
    public function __construct() {
        Debug::group('Template Constructor', 1);
        Components::set('TITLE', '');
        Components::set('PAGE_DESCRIPTION', '');
        Components::set('TOKEN', Token::generate());
        Components::set('BASE', Routes::getAddress());
        $this->buildRobot();
        Debug::gend();
    }

    public static function addTemplateLocation($location) {
        self::$additionalLocations[] = $location;
        return;
    }
    public static function getLocation() {
        return self::$templateLocation;
    }

    /**
     * This function sets the '<template>.tpl' to be used for the rendering of the
     * application. It also calls the template include file via the Template::loadTemplate
     * function and stores the keys to the $values array for the template to use later.
     *
     * @param string $name   - The name of the template you are trying to use.
     *                       ('.', and '_' are valid delimiters and the
     *                       '.tpl' or '.inc.php' are not required.)
     * @todo - Add a check for proper filename.
     */
    public static function setTemplate($name) {
        Debug::log("Setting template: $name");
        $name = strtolower(str_replace('.', '_', $name));
        $location = TEMPLATE_DIRECTORY . $name . DIRECTORY_SEPARATOR;
        $docLocation = $location . $name . '.tpl';
        if (file_exists($docLocation)) {
            self::$templateLocation = $docLocation;
            return self::loadTemplate( $location, $name );
        }
        foreach (self::$additionalLocations as $key => $location) {
            $docLocation = $location . $name . '.tpl';
            if (file_exists($docLocation)) {
                self::$templateLocation = $docLocation;
                return self::loadTemplate( $location, $name );
            }
        }
        new CustomException('template', $docLocation);
    }

    /**
     * Checks for, requires, and instantiates the template include file
     * and constructor for the specified template. Uses the class templateName
     * if none is provided.
     *
     * @param  string $name - A custom template name to load the include for.
     * @return array - Returns the values object from the loader file,
     *                 or an empty array.
     * @todo - Add a check for proper filename.
     */
    private static function loadTemplate($path, $name) {
        Debug::group('Template Loader', 1);
        $fullPath = $path . $name . '.inc.php';
        $className = APP_SPACE . '\\Templates\\' . ucfirst($name) . 'Loader';
        if (!file_exists($fullPath)) {
            new CustomException('templateLoader', $fullPath);
        } else {
            Debug::log('Requiring template loader: ' . $name);
            require_once $fullPath;
            $loaderNameFull = $className;
            Debug::log('Calling loader: ' . $className);
            $loader = new $className;
        }
        Debug::gend();
    }

    /**
     * Sets the current page as noFollow and rebuilds the robots meta tag.
     *
     * @param  boolean $status - The desired state for noFollow.
     */
    public static function noFollow($status = false) {
        self::$follow = (bool) $status;
        self::buildRobot();
    }

    /**
     * Sets the current page as noIndex and rebuilds the robots meta tag.
     *
     * @param  boolean $status - The desired state for noIndex.
     */
    public static function noIndex($status = false) {
        self::$index = (bool) $status;
        self::buildRobot();
    }

    /**
     * Updates the component for ROBOT.
     */
    public static function buildRobot() {
        if (!self::$index && !self::$follow) {
            Components::set('ROBOT', '<meta name="robots" content="noindex,nofollow">');
        } elseif (!self::$index) {
            Components::set('ROBOT', '<meta name="robots" content="noindex">');
        } elseif (!self::$follow) {
            Components::set('ROBOT', '<meta name="robots" content="nofollow">');
        } else {
            Components::set('ROBOT', '');
        }
    }

    /**
     * Prints the parsed and fully rendered page using the specified template from
     * templateLocation.
     * NOTE: This should be the only echo in the system.
     */
    public static function render() {
        Components::set('CONTENT', self::$content);
        if (empty(self::$templateLocation)) {
            self::setTemplate(Config::get('main/template'));
        }
        if (!Debug::status('render')) {
            return;
        }
        echo self::parse(file_get_contents(self::$templateLocation));
    }

    /**
     * The loop function for the template engine's {loop}{/loop} tag.
     *
     * @param string $template The string being checked for a loop
     * @param array  $data     the data being looped through
     * @return string the filtered and completed LOOP
     */
    public static function buildLoop($template, $data = null) {
        $header = null;
        $footer = null;
        $final = null;
        $loopAlternative = null;

        $loop = '#.*{LOOP}(.*?){/LOOP}.*#is';
        $loopTemplate = preg_replace($loop, '$1', $template);
        if ($loopTemplate != $template) {
            //Separate off the header if it exists.
            $header = trim(preg_replace('#^(.*)?{LOOP}.*$#is', '$1', $template));
            if ($header === $template) {
                $header = null;
            }

            //Separate off the footer if it exists.
            $footer = trim(preg_replace('#^.*?{/LOOP}(.*)$#is', '$1', $template));
            if ($footer === $template) {
                $footer = null;
            }

            if (!empty($footer)) {
                //Separate off the alternative to the loop if it exists.
                $alt = '#{ALT}(.*?){/ALT}#is';
                $loopAlternative = trim(preg_replace($alt, '$1', $footer));
                if ($loopAlternative === $footer) {
                    $loopAlternative = null;
                } else {
                    $footer = trim(preg_replace('#^.*?{/ALT}(.*)$#is', '$1', $footer));
                }
            }
        }

        // Paginate
        if (strpos($template, '{PAGINATION}') !== false) {
            Pagination::paginate();
        }

        if (!empty($data)) {
            //iterate through the data as instances.
            foreach ($data as $instance) {
                $x = 0;
                //reset the template for every iteration of $data.
                $modifiedTemplate = $loopTemplate;

                if (!is_object($instance)) {
                    $instance = $data;
                    $end = 1;
                }
                //loop the template as many times as we have data for.
                foreach ($instance as $key => $value) {
                    if (!is_object($value)) {
                        $tagPattern = "~{($key)}~i";
                        if (is_array($value)) {
                            $value = '';
                        }
                        $modifiedTemplate = preg_replace($tagPattern, $value, $modifiedTemplate);
                    }
                }

                //since this loop may have a header, and/or footer, we have to define the final output of the loop.
                $final .= $modifiedTemplate;

                if ($x === 0) {
                    $singlePattern = '#{SINGLE}(.*?){/SINGLE}#is';
                    //If there is a {SINGLE}{/SINGLE} tag, we will replace it on the first iteration.
                    $final = preg_replace($singlePattern, '$1', $final);

                    //Same practice, but for the entry template.
                    $loopTemplate = preg_replace($singlePattern, null, $loopTemplate);
                    ++$x;
                }

                //Since $data is only for a single data set, we break the loop.
                if (isset($end)) {
                    unset($end);
                    $output = $header . $final . $footer;
                    break;
                }
            }
            $output = $header . $final . $footer;
        } else {
            if (!empty($loopAlternative)) {
                $output = $header . $loopAlternative;
            } else {
                $output = $header . $loopTemplate . $footer;
            }
        }
        return $output;
    }

    /**
     * This is the main function of the template engine.
     * this function parses the given view and replaces
     * all of the necessary components with their processed
     * counterparts.
     *
     * @param  string       $template   - The html that needs to be parsed.
     * @param  array|object $data       - An associative array or object that will
     *                                  be used as components for the provided html.
     * @return string - The fully parsed html output.
     */
    public static function parse($template, $data = null, $flags = null) {
        //Check for a {LOOP}{/LOOP} tag.
        $template = self::buildLoop($template, $data);

        //Run through our full list of generated keys.
        foreach (Components::$components as $key => $value) {
            $tagPattern = "~{($key)}~i";
            $template = preg_replace($tagPattern, $value, $template);
        }

        if (strpos($template, '{OPTION=') !== false) {
            foreach (Forms::$options as $key => $value) {
                $template = preg_replace($key, $value, $template, 1);
            }
            $template = preg_replace('#\{OPTION\=(.*?)\}#is', '', $template);
        }

        //Convert any dates into preferred Date/Time format. User preference will be applied her in the future.
        $dtc = '#{DTC(.*?)}(.*?){/DTC}#is';
        $template = preg_replace_callback(
            $dtc,
            function ($data) {
                if ($data[2] == '' || $data[2] == 'null') {
                    return '';
                }
                return $data[2];
                // removed because prefs is no longer part of core
                // if (stripos($data[1], 'date')) {
                //     $dateFormat = self::$activePrefs->dateFormat;
                // } elseif (stripos($data[1], 'time')) {
                //     $dateFormat = self::$activePrefs->timeFormat;
                // } else {
                //     $dateFormat = self::$activePrefs->dateFormat . ' ' . self::$activePrefs->timeFormat;
                // }
                // $time = $data[2] + 0;
                // $dt = new DateTime(self::$activePrefs->timezone);
                // $dt->setTimestamp($time);
                // return $dt->format($dateFormat);
            },
            $template
        );

        //Run through our full list of generated filters.
        $template = Filters::apply($template);

        return $template;
    }
}
