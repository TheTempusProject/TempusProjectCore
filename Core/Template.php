<?php
/**
 * Core/Template.php.
 *
 * This is the Template class. It is responsible for all visual output for the application.
 * This class also contains all the functions for parsing data outputs into HTML, handling the
 * simple linking system that allows for @mentions #hashtags and BB Code. Oh yeah! It also 
 * loads our template.
 *
 * @version 0.9
 *
 * @author  Joey Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Core;

use TempusProjectCore\Core\Controller as Controller;
use TempusProjectCore\Classes\Debug as Debug;
use TempusProjectCore\Classes\Issue as Issue;
use TempusProjectCore\Classes\Token as Token;
use TempusProjectCore\Classes\Config as Config;
use TempusProjectCore\Classes\CustomException as CustomException;
use TempusProjectCore\Classes\Pagination as Pagination;

class Template extends Controller
{
    private static $_page_limit = null;
    private static $_page = null;
    private static $_min = null;
    private static $_max = null;
    private static $_follow = true;
    private static $_index = true;
    private static $_template_name = null;
    private static $_template_location = null;
    private static $_pattern = array();
    private static $_values = array();
    private static $_options = array();

    /**
     * The constructor automatically sets a few filter that we know the application will need.
     */
    public function __construct()
    {
        Debug::group('Template Constructor', 1);
        Self::set('TITLE', 'The Tempus Project');
        Self::set('PAGE_DESCRIPTION', '');
        Self::set('TOKEN', Token::generate());
        Self::set('BASE', Config::get('main/base'));
        Self::setRobot();
        Debug::gend();
    }

    /**
     * The function for setting and loading in a Template.
     * 
     * @param string $data - The name of the template.
     */
    public static function set_template($data)
    {
        Debug::log("Setting template: $data");
        $template_name = str_replace('.', '_', $data);
        $path = Config::get('main/location').'Templates/'.$template_name.'/'.$template_name.'.tpl';
        if (!is_file($path)) {
            $template_name = Config::get('main/template');
            Debug::error('Template not found, using default: '.$template_name.'.tpl');
        }
        $path = Config::get('main/location').'Templates/'.$template_name.'/'.$template_name.'.tpl';
        Self::$_template_location = $path;
        Self::$_template_name = $template_name;
        $load = Self::loader();
        foreach ($load as $key => $value) {
            Self::set($key, $value);
        }
    }

    public static function noFollow($data = false)
    {
        Self::$_follow = (bool) $data;
        Self::setRobot();
    }
    public static function noIndex($data = false)
    {
        Self::$_index = (bool) $data;
        Self::setRobot();
    }
    public static function setRobot()
    {
        if (!Self::$_index && !Self::$_follow) {
            Self::set('ROBOT', '<meta name="robots" content="noindex,nofollow">');
        } elseif (!Self::$_index) {
            Self::set('ROBOT', '<meta name="robots" content="noindex">');
        } elseif (!Self::$_follow) {
            Self::set('ROBOT', '<meta name="robots" content="nofollow">');
        } else {
            Self::set('ROBOT', '');
        }
    }

    /**
     * Calls and initializes the desired Template include.
     * 
     * @return array - Returns the Values array created by the Template Initializer.
     */
    private static function loader()
    {
        Debug::group('Loader', 1);
        $path = Config::get('main/location').'Templates/'.strtolower(Self::$_template_name).'/'.strtolower(Self::$_template_name).'.inc.php';
        if (is_file($path)) {
            Debug::log('Requiring loader');
            require_once $path;
            $template_function = APP_SPACE.'\Templates\template_'.strtolower(Self::$_template_name);
            Debug::log('Calling loader: '.Self::$_template_name);
            $data = new $template_function();
            Debug::gend();

            return unserialize($data->values());
        } else {
            Debug::gend();
            new CustomException('template_loader', $template_function);
        }
    }

    /**
     * Displays the fully rendered page.
     */
    public static function render()
    {
        if (empty(Self::$_template_location)) {
            Self::set_template('default');
        }
        echo Self::parse(file_get_contents(Self::$_template_location));
    }

    /**
     * Adds a $Key->$Value combination to the $_values array.
     * 
     * @param string $key   The key by which to access this value.
     * @param wild   $value The value being stored.
     */
    public static function set($key, $value)
    {
        Self::$_values[$key] = $value;
    }

    /**
     * Adds a {$name}{/$name} filter to the templating engine that can be
     * enabled or disabled (disabled by default).
     * 
     * @param string $name    - The filters name.
     * @param string $match   - The regex to look for.
     * @param bool   $enabled - Whether the filter should be enabled or disabled.
     */
    public static function add_filter($name, $match, $enabled = false)
    {
        Self::$_pattern[$name] = array(
            'name' => $name,
            'match' => $match,
            'enabled' => $enabled,
        );
    }

    /**
     * Removes a {$name}{/$name} filter from the templating engine.
     * 
     * @param string $name The filters name.
     */
    public static function remove_filter($name)
    {
        unset(Self::$_pattern[$name]);
    }

    /**
     * Enable a filter.
     * 
     * @param string $name The filters name.
     */
    public static function enable_filter($name)
    {
        Self::$_pattern[$name] = array(
            'name' => $name,
            'match' => Self::$_pattern[$name]['match'],
            'enabled' => true,
        );
    }

    /**
     * Disables a filter.
     * 
     * @param string $name The filters name.
     */
    public static function disable_filter($name)
    {
        Self::$_pattern[$name] = array(
            'name' => $name,
            'match' => Self::$_pattern[$name]['match'],
            'enabled' => false,
        );
    }

    /**
     * Returns a completely parsed view.
     *
     * NOTE: Results will contain raw HTML.
     * 
     * @param string $view The name of the view you wish to call.
     * @param var    $data Any data to be used by the view.
     * 
     * @return string HTML view.
     */
    public static function standard_view($view, $data = null)
    {
        $newView = str_replace('.', '_', $view);
        $path = Config::get('main/location') . 'Views/view_'.$newView.'.php';
        if (is_file($path)) {
            Debug::log("Calling Standard View: $newView");
            if (!empty($data)) {
                return Self::parse(file_get_contents($path), $data);
            } else {
                return Self::parse(file_get_contents($path));
            }
        } else {
            new CustomException('standard_view', $newView);
        }
    }

    /**
     * This function parses either given html or the current page content and sets 
     * the current active page to selected within an html list.
     * 
     * @param  string $menu         - The name of the view you wish to add. can be any arbitrary value if $view is provided.
     * @param  string $selectString - The string/url you are searching for, default model/controller is used if none is provided.
     * @param  string $view         - The html you want parsed, view is generated from menu name if $view is left blank
     * 
     * @return string|bool           - returns bool if the menu was added to the page content or 
     *                                 returns the parsed view if one was provided with the 
     *                                 function call.
     */
    public static function activePageSelect($menu, $selectString = null, $view = null)
    {
        if ($selectString == null) {
            $selectString = CORE_CONTROLLER.'/'.CORE_METHOD;
        }
        $regURL = Config::get('main/base') . $selectString;
        $regPage = "#\<li(.*)\>\<a(.*)href=\"$regURL\"(.*)\>(.*)\<\/li>#i";
        $regActive = "<li$1 class=\"active\"><a$2href=\"$regURL\"$3>$4</li>";
        if ($view == null) {
            //adds the nav to the main content by default
            $content = true;
            $view = Self::$_template->standard_view($menu);
        }

        if (!preg_match($regPage, $view)) {
            //if you cannot find the item requested, it will default to the base of the item provided
            $newURL = explode('/', $selectString);
            $regURL = Config::get('main/base') . $newURL[0];
            $regPage = "#\<li(.*)\>\<a(.*)href=\"$regURL\"(.*)\>(.*)\<\/li>#i";
        }

        if (isset($content)) {
            Self::$_content .= preg_replace($regPage, $regActive, $view);
            return true;
        }
        $view = preg_replace($regPage, $regActive, $view);
        return $view;
    }

    /**
     * Generates all the information we need to visually 
     * display pagination within the template.
     */
    private static function paginate()
    {
        $page_data = [];
        if (Pagination::firstPage() != 1) {
            $data[1]['ACTIVEPAGE'] = '';
            $data[1]['PAGENUMBER'] = 1;
            $data[1]['LABEL'] = 'First';
            $page_data[1] = (object) $data[1];
        }
        for ($x = Pagination::firstPage(); $x < Pagination::lastPage(); $x++) {
            if ($x == Pagination::currentPage()) {
                $active = ' class="active"';
            } else {
                $active = '';
            }
            $data[$x]['ACTIVEPAGE'] = $active;
            $data[$x]['PAGENUMBER'] = $x;
            $data[$x]['LABEL'] = $x;
            $page_data[$x] = (object) $data[$x];
        }
        if (Pagination::lastPage() <= Pagination::totalPages()) {
            $x = Pagination::totalPages();
            if ($x == Pagination::currentPage()) {
                $active = ' class="active"';
            } else {
                $active = '';
            }
            $data[$x]['ACTIVEPAGE'] = $active;
            $data[$x]['PAGENUMBER'] = $x;
            $data[$x]['LABEL'] = 'Last';
            $page_data[$x] = (object) $data[$x];
        }
        $page_data = (object) $page_data;
        if (Pagination::totalPages() <= 1) {
            Template::set('PAGINATION', '<lb>');
        } else {
            Template::set('PAGINATION', Self::standard_view('pagination',$page_data));
        }
    }

    /**
     * This automatically sets filters on the template for both true and false values on radio buttons.
     * 
     * @param  [type] $postname [description]
     * @param  [type] $name     [description]
     * @param  [type] $value    [description]
     * @return [type]           [description]
     */
    public static function select_tf($postname, $name, $value)
    {
        if ((Input::exists('submit') && (Input::post($postname) === 'true')) || (($value === true) && (!Input::exists('submit')))) {
            Self::$_template->set($name . '_T', 'checked="checked"');
            Self::$_template->set($name . '_F', '');
        } else {
            Self::$_template->set($name . '_F', 'checked="checked"');
            Self::$_template->set($name . '_T', '');
        }
    }

    /**
     * This will add an option to our selected options menu that will 
     * automatically be selected when the template is rendered.
     * 
     * @param  string  $value - The value of the option you want selected.
     */
    public static function select_option($value)
    {
        $find = "#\<option (.*?)value=\'" . $value . "\'#s";
        $replace = "<option $1value='" . $value . "' selected";
        Self::$_options[$find] = $replace;
    }
    
    /**
     * Runs a Check through the filters list on $data.
     *
     * @param string $data the string being checked for filters
     *
     * @return string The filtered $data.
     */
    private static function filters($data)
    {
        if (!empty(Self::$_pattern)) {
            foreach (Self::$_pattern as $instance) {
                if ($instance['enabled']) {
                    $replace = '$1';
                } else {
                    $replace = null;
                }
                $data = trim(preg_replace($instance['match'], $replace, $data));
            }
        }

        return $data;
    }

    /**
     * A custom filter for turning legitimate mentions into profile links.
     *
     * @param string $data the string being filtered
     *
     * @return string the filtered $data
     */
    public static function filter_mentions($data)
    {
        $comment_pattern = '/(^|\s)@(\w*[a-zA-Z_]+\w*)/';
        $output = preg_replace($comment_pattern, ' <a href="http://twitter.com/search?q=%40\2">@\2</a>', $data);

        return $output;
    }

    /**
     * A custom filter for creating links for hashtags.
     *
     * @param string $data the string being filtered
     *
     * @return string the filtered $data
     */
    public static function filter_hashtags($data)
    {
        $comment_pattern = '/(^|\s)#(\w*[a-zA-Z_]+\w*)/';
        $output = preg_replace($comment_pattern, ' <a href="http://twitter.com/search?q=%23\2">#\2</a>', $data);

        return $output;
    }

    /**
     * A custom filter for converting certain BB code into appropriate HTML entities.
     * 
     * @param string $data the string being filtered
     * 
     * @return string the filtered $data
     */
    public static function filter_bb($data)
    {
        $codes = array(
            '#\[b\](.*?)\[/b\]#is' => '<b>$1</b>',
            '#\[p\](.*?)\[/p\]#is' => '<p>$1</p>',
            '#\[i\](.*?)\[/i\]#is' => '<i>$1</i>',
            '#\[u\](.*?)\[/u\]#is' => '<u>$1</u>',
            '#\[s\](.*?)\[/s\]#is' => '<del>$1</del>',
            '#\[code\](.*?)\[/code\]#is' => '<code>$1</code>',
            '#\[color=(.*?)\](.*?)\[/color\]#is' => "<font color='$1'>$2</font>",
            '#\[img\](.*?)\[/img\]#is' => "<img src='$1'>",
            '#\(c\)#is' => '&#10004;',
            '#\(x\)#is' => '&#10006;',
            '#\(!\)#is' => '&#10069;',
            '#\(\?\)#is' => '&#10068;',
            '#\[list\](.*?)\[/list\]#is' => '<ul>$1</ul>',
            '#\(\.\)(.*)$#m' => '<li>$1</li>',
            '#\[url=(.*?)\](.*?)\[/url\]#is' => "<a href='$1'>$2</a>",
            '#\[quote=(.*?)\](.*?)\[/quote\]#is' => "<blockquote cite='$1'>$2</blockquote>",
            );
        foreach ($codes as $reg => $replace) {
            $data = preg_replace($reg, $replace, $data);
        }

        return $data;
    }

    /**
     * A custom filter for removing annotated sections and code comments.
     *
     * @param string $data the string being filtered
     *
     * @return string the filtered $data
     */
    public static function filter_comments($data)
    {
        $comment_pattern = array('#/\*.*?\*/#s', '#(?<!:)//.*#');
        $output = preg_replace($comment_pattern, null, $data);

        return $output;
    }

    /**
     * The loop function for the template engine's {loop}{/loop} tag.
     * 
     * @param string $template The string being checked for a loop
     * @param array  $data     the data being looped through
     * 
     * @return string the filtered and completed LOOP
     */
    public static function build_loop($template, $data = null)
    {
        $header = null;
        $footer = null;
        $final = null;
        $alt_loop = null;

        $loop = '#.*{LOOP}(.*?){/LOOP}.*#is';
        $loop_template = preg_replace($loop, '$1', $template);
        if ($loop_template != $template) {
            //Seperate off the header if it exists.
            $header = trim(preg_replace('#^(.*)?{LOOP}.*$#is', '$1', $template));
            if ($header === $template) {
                $header = null;
            }

            //Seperate off the footer if it exists.
            $footer = trim(preg_replace('#^.*?{/LOOP}(.*)$#is', '$1', $template));
            if ($footer === $template) {
                $footer = null;
            }

            if (!empty($footer)) {
                //Seperate off the alternative to the loop if it exists.
                $alt = '#{ALT}(.*?){/ALT}#is';
                $alt_loop = trim(preg_replace($alt, '$1', $footer));
                if ($alt_loop === $footer) {
                    $alt_loop = null;
                } else {
                    $footer = trim(preg_replace('#^.*?{/ALT}(.*)$#is', '$1', $footer));
                }
            }
        }

        // Paginate
        if (strpos($template, '{PAGINATION}') !== false) {
            Template::paginate();
        }

        if (!empty($data)) {
            //iterate through the data as instances.
            foreach ($data as $instance) {
                $x = 0;
                //reset the template for every iteration of $data.
                $modified_template = $loop_template;
                
                if (!is_object($instance)) {
                    $instance = $data;
                    $end = 1;
                }
                //loop the template as many times as we have data for.
                foreach ($instance as $key => $value) {
                    if (!is_object($value)) {
                        $tag_pattern = "~{($key)}~i";
                        $modified_template = preg_replace($tag_pattern, $value, $modified_template);
                    }
                }

                //since this loop may have a header, and/or footer, we have to define the final output of the loop.
                $final .= $modified_template;

                if ($x === 0) {
                    $single_pattern = '#{SINGLE}(.*?){/SINGLE}#is';
                    //If there is a {SINGLE}{/SINGLE} tag, we will replace it on the first iteration.
                    $final = preg_replace($single_pattern, '$1', $final);

                    //Same practice, but for the entry template.
                    $loop_template = preg_replace($single_pattern, null, $loop_template);
                    ++$x;
                }

                //Since $data is only for a single data set, we break the loop.
                if (isset($end)) {
                    unset($end);
                    $output = $header.$final.$footer;
                    break;
                }
            }
            $output = $header.$final.$footer;
        } else {
            if (!empty($alt_loop)) {
                $output = $header.$alt_loop;
            } else {
                $output = $header.$loop_template.$footer;
            }
        }
        return $output;
    }

    /**
     * This is the main function of the template engine.
     * this function parses the given view and replaces
     * all of the necessary tags with their rendered
     * counterparts.
     * 
     * @param  string       $template -The html that needs to be parsed.
     * @param  array|object $data     - an assoc array or object that will automatically 
     *                                  be used as filters for the provided html.
     * 
     * @return string - The fully parsed html output.
     */
    public static function parse($template, $data = null)
    {
        //remove all comments from the source.
        $template = Self::filter_comments($template);

        //Run through our full list of generated filters.
        $template = Self::filters($template);

        //Check for a {LOOP}{/LOOP} tag.
        $template = Self::build_loop($template, $data);

        //Run through our full list of generated keys.
        foreach (Self::$_values as $key => $value) {
            $tag_pattern = "~{($key)}~i";
            $template = preg_replace($tag_pattern, $value, $template);
        }

        if (strpos($template, '{OPTION=') !== false) {
            foreach (Self::$_options as $key => $value) {
                $template = preg_replace($key, $value, $template);
            }
            $template = preg_replace('#\{OPTION\=(.*?)\}#is', '', $template);
        }

        //Convert any dates into preferred Date/Time format. User preference will be applied her in the future.
        $dtc = '#{DTC(.*?)}(.*?){/DTC}#is';
        $template = preg_replace_callback($dtc,
                    function ($data) {
                        if (stripos($data[1], 'date')) {
                            $date_format = Self::$_active_prefs->date_format;
                        } elseif (stripos($data[1], 'time')) {
                            $date_format = Self::$_active_prefs->time_format;
                        } else {
                            $date_format = Self::$_active_prefs->date_format . ' ' . Self::$_active_prefs->time_format;
                        }
                        $timezone = Self::$_active_prefs->timezone;
                        $dt = new \DateTime();
                        $time = $data[2] + 0;
                        $dt->setTimestamp($time);
                        $dt->setTimeZone(new \DateTimeZone($timezone));
                        $out = $dt->format($date_format);
                        return $out;
                    }, $template);
        $template = Self::filter_hashtags($template);
        $template = Self::filter_bb($template);
        $template = Self::filter_mentions($template);
        return $template;
    }
}
