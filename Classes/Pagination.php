<?php
/**
 * Classes/Pagination.php
 *
 * This class is used to generate and manipulate pagination for our database interactions.
 *
 * @version 0.9
 *
 * @author  Joey Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Classes;

class Pagination
{
    //The settings that will not change
    public static $pagination_settings = array();

    //The instance for each generation
    public static $instance = null;

    //The total number of returned results.
    private $_total_results = 0;

    //The total number of pages for the results
    private $_total_pages = 1;

    /**
     * [__construct description]
     * @param [type] $start [description]
     * @param [type] $end   [description]
     * @param [type] $total [description]
     */
    private function __construct($start, $end, $total)
    {
        if (empty(Self::$pagination_settings['limit'])) {
            $this->load_settings();
        }

        //check for user settings
        if (empty(Self::$pagination_settings['per_page'])) {
            Self::$pagination_settings['per_page'] = Config::get('main/page_default');
            if ((!empty(Self::$pagination_settings['user_per_page'])) && (Self::$pagination_settings['user_per_page'] <= Self::$pagination_settings['max_per_page'])) {
                Self::$pagination_settings['per_page'] = Self::$pagination_settings['user_per_page'];
            }
        }

        // The query minimum and maximum based on current page and page limit
        if (Self::$pagination_settings['current_page'] == 1) {
            Self::$pagination_settings['min'] = 0;
            Self::$pagination_settings['max'] = Self::$pagination_settings['per_page'];
        } else {
            Self::$pagination_settings['min'] = ((Self::$pagination_settings['current_page'] - 1) * Self::$pagination_settings['per_page']);
            Self::$pagination_settings['max'] = Self::$pagination_settings['per_page'];
        }

        // The query limit based on our settings here
        Self::$pagination_settings['limit'] = array(Self::$pagination_settings['min'],Self::$pagination_settings['max']);
    }

    /**
     * [load_settings description]
     * @return [type] [description]
     */
    private static function load_settings()
    {
        Debug::log('Loading Pagination Settings.');
        // hard cap built into system for displaying results
        Self::$pagination_settings['max_per_page'] = Config::get('main/page_limit');

        // hard cap built into system retrieving results
        Self::$pagination_settings['max_query'] = Config::get('database/db_max_query');

        // Set max query to the lowest of the three settings since this will modify how many results are possible.
        if (Self::$pagination_settings['max_query'] <= Self::$pagination_settings['max_per_page']) {
            Self::$pagination_settings['max_per_page'] = Self::$pagination_settings['max_query'];
        }

        // Check for results request to set/modify the per_page setting
        if (Input::exists("results")) {
            if (Check::ID(Input::get("results"))){
                if (Input::get("results") <= Self::$pagination_settings['max_per_page']) {
                    Self::$pagination_settings['per_page'] = Input::get("results");
                }
            }
        }

        // Check for pagination in get
        if (Input::exists("page")) {
            if (Check::ID(Input::get("page"))){
                Self::$pagination_settings['current_page'] = (int) Input::get("page");
            } else {
                Self::$pagination_settings['current_page'] = 1;
            }
        } else {
            Self::$pagination_settings['current_page'] = 1;
        }

        if ((Self::$pagination_settings['current_page'] - 3) > 1) {
            Self::$pagination_settings['first_page'] = (Self::$pagination_settings['current_page'] - 2);
        } else {
            Self::$pagination_settings['first_page'] = 1;
        }
    }

    /**
     * [generate description]
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @param  [type] $total [description]
     * @return [type]        [description]
     */
    public static function generate($start = null, $end = null, $total = null)
    {
        // account for empty values here instead of inside the script.
        Debug::log('Creating new Pagination.');
        if (empty($start)) {
            $start = 0;
        }
        if (empty($end)) {
            $end = Config::get('main/page_default');
        }
        if (empty($total)) {
            $total = 0;
        }
        Debug::log('Creating new Pagination Instance.');
        Self::$instance = new Self($start, $end, $total);
        return Self::$instance;
    }

    /**
     * [update_prefs description]
     * @param  [type] $page_limit [description]
     * @return [type]             [description]
     */
    public static function update_prefs($page_limit)
    {
        if (Check::ID($page_limit)) {
            Debug::log('Pagination: Updating user pref');
            Self::$pagination_settings['user_per_page'] = $page_limit;
        } else {
            Debug::info('Pagination: User pref update failed.');
        }
    }

    /**
     * [getMin description]
     * @return [type] [description]
     */
    public static function getMin()
    {
        if (isset(Self::$pagination_settings['min'])) {
            return Self::$pagination_settings['min'];
        } else {
            Debug::info('Pagination: Min not found');
        }
    }

    /**
     * [perPage description]
     * @return [type] [description]
     */
    public static function perPage()
    {
        if (!empty(Self::$pagination_settings['per_page'])) {
            return Self::$pagination_settings['per_page'];
        }
    }

    /**
     * [getMax description]
     * @return [type] [description]
     */
    public static function getMax()
    {
        if (!empty(Self::$pagination_settings['max'])) {
            return Self::$pagination_settings['max'];
        } else {
            Debug::info('Pagination: Max not found');
        }
    }
    
    /**
     * [firstPage description]
     * @return [type] [description]
     */
    public static function firstPage()
    {
        if (!empty(Self::$pagination_settings['first_page'])) {
            return Self::$pagination_settings['first_page'];
        } else {
            Debug::info('Pagination: Max not found');
        }
    }

    /**
     * [lastPage description]
     * @return [type] [description]
     */
    public static function lastPage()
    {
        if (!empty(Self::$pagination_settings['last_page'])) {
            return Self::$pagination_settings['last_page'];
        } else {
            Debug::info('Pagination: Max not found');
        }
    }

    /**
     * [totalPages description]
     * @return [type] [description]
     */
    public static function totalPages()
    {
        if (!empty(Self::$pagination_settings['total_pages'])) {
            return Self::$pagination_settings['total_pages'];
        } else {
            Debug::info('Pagination: Max not found');
        }
    }

    /**
     * [update_results description]
     * @param  [type] $results [description]
     * @return [type]          [description]
     */
    public static function update_results($results)
    {
        if (Check::ID($results)) {
            Debug::log('Pagination: Updating results count');
            Self::$pagination_settings['results'] = $results;
            Self::$pagination_settings['total_pages'] = ceil((Self::$pagination_settings['results'] / Self::$pagination_settings['per_page']));
            if ((Self::$pagination_settings['current_page'] + 3) < Self::$pagination_settings['total_pages']) {
                Self::$pagination_settings['last_page'] = Self::$pagination_settings['current_page'] + 3;
            } else {
                Self::$pagination_settings['last_page'] = Self::$pagination_settings['total_pages'];
            }
        } else {
            Debug::info('Pagination: results update failed.');
        }
    }

    /**
     * [currentPage description]
     * @return [type] [description]
     */
    public static function currentPage()
    {
        if (!empty(Self::$pagination_settings['current_page'])) {
            return Self::$pagination_settings['current_page'];
        } else {
            Debug::info('Pagination: current_page not found');
        }
    }
}