<?php
/**
 * Classes/Check.php
 *
 * This class is used to test various inputs for a variety of purposes.
 * In this class we verify emails, inputs, passwords, and even entire forms.
 *
 * @version 1.0
 *
 * @author  Joey Kimsey <joeyk4816@gmail.com>
 *
 * @link    https://github.com/JoeyK4816/tempus-project-core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Classes;

class Check
{
    private static $_db = null;
    private static $forms = null;
    private static $_error = null;
    private static $_token = array();

    /**
     * Creates a new DB connection, but only if we need it.
     */
    public static function connect()
    {
        if (!empty(Self::$_db)) {
            return;
        }
        Self::$_db = DB::getInstance();
    }

    /**
     * Checks an uploaded file for proper size, formatting, and lack of erro in upload
     * 
     * @param  string $data the name of the uplad field
     * 
     * @return bool
     */
    public static function image_upload($data)
    {
        if (!isset($_FILES[$data])) {
            Self::add_error('File not found.', $_FILES[$data]);

            return false;
        }

        if ($_FILES[$data]['error'] != 0) {
            Self::add_error($_FILES[$data]['error']);

            return false;
        }

        if ($_FILES[$data]['size'] > 500000) {
            Self::add_error("File size too large.");

            return false;
        }
        $whitelist = array(".jpg",".jpeg",".gif",".png");
        $file_type = strrchr($_FILES[$data]['name'], '.');
        if (!(in_array($file_type, $whitelist))) {
            Self::add_error("invalid file type.");

            return false;
        }

        return true;
    }

    /**
     * Checks a string for bool value.
     *
     * @param string $data - The string being tested.
     *
     * @return bool
     */
    public static function tf($data)
    {
        if ($data == 'true') {
            return true;
        }
        if ($data == 'false') {
            return true;
        }
        Self::add_error('Invalid true-false.', $data);

        return false;
    }

    /**
     * Parses out spaces then checks for alpha-numeric type.
     *
     * @param string    $data   - Data being checked.
     *
     * @return bool
     */
    public static function alnum($data)
    {
        if (ctype_alpha($data)) {
            return true;
        }
        Self::add_error('Invalid Alpha-numeric.', $data);

        return false;
    }

    /**
     * Checks if PHP's Safe mode is enabled.
     *
     * @return bool
     */
    public static function form($name)
    {
        if (empty(Self::$forms)) {
            $path = Config::get('main/location') . 'Resources/forms.php';
            if (!file_exists($path)) {
                return false;
            }
            require_once $path;
            Self::$forms = new Forms;
        }
        return Self::$forms->$name();
    }

    /**
     * Checks an input for accepted characters.
     * 
     * @param  string $data - the data being checked
     * 
     * @return bool
     */
    public static function nospace($data)
    {
        if (!stripos($data, ' ')) {
            return true;
        }
        Self::add_error('Invalid no-space input.', $data);

        return false;
    }

    /**
     * Checks the data to see if it is a digit.
     *
     * @param   mixed     $data   - Data being checked.
     *
     * @return  bool
     */
    public static function ID($data)
    {
        if (is_numeric($data)) {
            return true;
        }
        Self::add_error('Invalid ID.', $data);

        return false;
    }
    
    /**
     * Checks the data to see if it is a valid data string. It can 
     * only contain letters, numbers, space, underscore, and dashes.
     *
     * @param   mixed     $data   - Data being checked.
     *
     * @return  bool
     */
    public static function data_title($data)
    {
        if (preg_match('#^[a-z 0-9\-\_ ]+$#mi', $data)) {
            return true;
        }
        Self::add_error('Invalid data string.', $data);

        return false;
    }

    /**
     * Checks the data to see if there are any illegal characters
     * in the filename.
     *
     * @param   string     $data   - Data being checked.
     *
     * @return  bool
     */
    public static function path()
    {
        if (!preg_match('#^[^/?*:;{}\\]+$#mi', $data)) {
            return true;
        }
        Self::add_error('Invalid path.', $data);

        return false;
    }

    /**
     * Checks the form token.
     *
     * @param   string|null     $data   - String to check for the token. (Post Token assumed)
     *
     * @return  bool
     */
    public static function token($data = null)
    {
        if (!isset($data)) {
            $data = Input::post('token');
        }
        Self::$_token = Token::check($data);

        return Self::$_token;
    }

    /**
     * Checks the DB for an email after verifying $data is a valid email.
     *
     * @param   string      $data   - The email being tested.
     *
     * @return  bool
     */
    public static function no_email_exists($data)
    {
        Self::connect();
        if (Self::email($data)) {
            $data_2 = Self::$_db->get('users', array('email', '=', $data));
            if ($data_2->count() == 0) {
                return true;
            }
        }
        Self::add_error("Email is already in use.\n", $data);

        return false;
    }

    /**
     * Checks for proper url formatting.
     * 
     * @param  String $data The input being checked
     * 
     * @return bool
     */
    public static function url($data) 
    {
        $url = filter_var($data, FILTER_SANITIZE_URL);
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            Self::add_error('Invalid Url');

            return false;
        }
        return true;
    }

    /**
     * Checks email formatting.
     *
     * Requirements:
     * - valid email format
     *
     * @param string $data - The string being tested.
     *
     * @return bool
     */
    public static function email($data)
    {
        $email = filter_var($data, FILTER_SANITIZE_EMAIL);    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Self::add_error("Email is not properly formatted.\n", $data);
            return false;
        }
        return true;
    }

    /**
     * Checks password formatting.
     *
     * Requirements:
     * - 6 - 20 characters long
     * - must only contain numbers and letters: [A - Z] , [a - z], [0 - 9]
     *
     * @param string $data - The string being tested.
     * @param string $data2 - The string it is being compared to.
     *
     * @return bool
     */
    public static function password($data, $data2 = null)
    {
        if ((strlen($data) >= 6) && (strlen($data) <= 20)) {
            if (!isset($data2)) {
                return true;
            }
            if ($data === $data2) {
                return true;
            }
        }
        Self::add_error("Password is not properly formatted.\n", $data);

        return false;
    }

    /**
     * Checks the DB for a username after verifying $data is a valid username.
     *
     * @param string $data - The string being tested
     *
     * @return bool
     */
    public static function username_exists($data)
    {
        Self::connect();
        if (Self::username($data)) {
            $data_2 = Self::$_db->get('users', array('username', '=', $data));
            if ($data_2->count()) {
                return true;
            }
        }
        Self::add_error("No user exists in the DB.\n", $data);

        return false;
    }

    /**
     * Checks username formatting.
     *
     * Requirements:
     * - 4 - 16 characters long
     * - must only contain numbers and letters: [A - Z] , [a - z], [0 - 9]
     *
     * @param string $data - The string being tested.
     *
     * @return bool
     */
    public static function username($data)
    {
        if ((strlen($data) <= 16) && (strlen($data) >= 4) && (ctype_alnum($data))) {
            return true;
        }
        Self::add_error("Username must be be 4 to 16 numbers or letters.\n", $data);

        return false;
    }

    /**
     * Checks name formatting.
     *
     * Requirements:
     * - 2 - 20 characters long
     * - must only contain letters: [A-Z] , [a-z]
     *
     * @param string $data - The string being tested.
     *
     * @return bool
     */
    public static function name($data)
    {
        if ((strlen($data) <= 20) && (strlen($data) >= 2) && (ctype_alpha(str_replace(' ', '', $data)))) {
            return true;
        }
        Self::add_error("Name is not properly formatted.\n", $data);

        return false;
    }

    /**
     * Checks the PHP version.
     *
     * Requirements:
     * - version 5.6 or higher
     *
     * @return bool
     */
    public static function php()
    {
        $php_version = phpversion();
        if ($php_version >= 5.6) {
            return true;
        }
        Self::add_error("PHP version is $php_version - too old!\n");

        return false;
    }

    /**
     * Checks PHP's mail function.
     *
     * @return bool
     */
    public static function mail()
    {
        if (function_exists('mail')) {
            return true;
        }
        Self::add_error("PHP Mail function is not enabled!\n");

        return false;
    }

    /**
     * Checks if PHP's Safe mode is enabled.
     *
     * @return bool
     */
    public static function safe()
    {
        if (!ini_get('safe_mode')) {
            return true;
        }
        Self::add_error("Please switch off PHP Safe Mode, it could interfere with this application's designed operation.\n");

        return false;
    }

    /**
     * Checks to make sure sessions are working properly.
     *
     * @return bool
     */
    public static function sessions()
    {
        $_SESSION['session_test'] = 1;
        if (!empty($_SESSION['session_test'])) {
            return true;
        }
        Self::add_error("Please enable Sessions before continuing installation.\n");

        return false;
    }

    /**
     * Checks the current database in the configuration file for version verification.
     *
     * @return bool
     *
     * @todo  Update this function to be more effective.
     */
    public static function mysql()
    {
        Self::connect();
        $test = Self::$_db->version();
        preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $test, $version);
        if (!version_compare($version[0], '10.0.0', '>')) {
            Self::add_error("Mysql Version is too low! Current version is $version[0]. 10.0.0 or higher is required. \n");
            return false;
        }
        
        return true;
    }

    /**
     * Checks to see if cookies are enabled.
     *
     * @return bool
     */
    public static function cookies()
    {
        Cookie::put('test', 'test');
        if (count($_COOKIE) > 0) {
            return true;
        }
        Self::add_error("Cookies are not enabled.\n", $Exception);

        return false;
    }

    /**
     * Checks the DB connection with the provided information.
     *
     * @param string $host - Database Host.
     * @param string $db   - Database Name.
     * @param string $user - Database Username.
     * @param string $pass - Database Password.
     *
     * @return bool
     */
    public static function db($host, $db, $user, $pass)
    {
        try {
            $test = new \PDO('mysql:host='.$host.';dbname='.$db, $user, $pass);
        } catch (\PDOException $Exception) {
            Self::add_error("Error connecting to DB.\n", $Exception);

            return false;
        }

        return true;
    }

    /**
     * Function to properly document and handle any errors we encounter in the check.
     *
     * @param string $info - The error information to be added to the list, and used in debug info.
     * @param string|array $data - Any additional variables or information.
     */
    public static function add_error($info, $data = null)
    {
        Debug::info("Check Error: $info");
        if (!empty($data)) {
            Debug::info("Additional information:");
            Debug::v($data);
        }
        Self::$_error = $info . "<br>";
    }

    /**
     * Function for returning the error array.
     *
     * @return array - Returns an Array of all the failed checks up until this point.
     */
    public static function errors()
    {
        return Self::$_error;
    }
}
