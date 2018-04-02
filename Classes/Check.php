<?php
/**
 * Classes/Check.php
 *
 * This class is used to test various inputs.
 *
 * @version 1.0
 *
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 *
 * @link    https://TheTempusProject.com/Core
 *
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */

namespace TempusProjectCore\Classes;

use TempusProjectCore\Functions\Docroot as Docroot;

class Check
{
    private static $db = null;
    private static $formValidator = null;
    private static $errorLog = [];
    private static $errorLogFull = [];
    private static $errorLogUser = [];

    /**
     * Creates a new DB connection, but only if we need it.
     */
    public static function connect()
    {
        if (!empty(self::$db)) {
            return;
        }
        self::$db = DB::getInstance();
    }

    /**
     * Function to properly document and handle any errors we encounter in the check.
     *
     * @param string $error - The error information to be added to the list, and used in debug info.
     * @param string|array $data - Any additional variables or information.
     */
    public static function addError($error, $data = null)
    {
        /**
         * If an array is provided for $error, it is split into
         * 2 separate errors for the logging.
         */
        if (is_array($error)) {
            $userError = $error[1];
            $error = $error[0];
        }

        Debug::info("Check error: $error");
        if (!empty($data)) {
            Debug::info("Additional error information:");
            Debug::v($data);
        }
        self::$errorLog[] = ['errorInfo' => $error, 'errorData' => $data];
        if (isset($userError)) {
            self::$errorLogUser[] = $userError;
        }
    }

    /**
     * This function only logs an error for the user-error-log.
     * Primarily used for providing generalized form feedback
     *
     * @param string $error - The error information to be added to the list.
     */
    public static function addUserError($error)
    {
        self::$errorLogUser[] = ['errorInfo' => $error];
    }

    /**
     * Function for reseting the current error logs and adding the old log
     * to the complete error log.
     */
    public static function errorReset()
    {
        self::$errorLog = [];
        self::$errorLogUser = [];
        self::$errorLogFull = array_merge(self::$errorLogFull, self::$errorLog);
    }

    /**
     * Function for returning the system error array.
     *
     * @param  $full - Flag for returning the full error log.
     *
     * @return array - Returns an Array of all the failed checks up until this point.
     */
    public static function systemErrors($full = false)
    {
        if ($full) {
            return self::$errorLogFull;
        }
        return self::$errorLog;
    }

    /**
     * Function for returning the user error array.
     *
     * @return array - Returns an Array of all the recorded user errors.
     */
    public static function userErrors()
    {
        return self::$errorLogUser;
    }

    /**
     * Checks a form as defined in App/form.php.
     *
     * @param  $name - The name of the method to be used for checking the form.
     *
     * @return boolean
     */
    public static function form($name)
    {
        if (empty(self::$formValidator)) {
            $docLocation = Docroot::getLocation('formChecks');
            if ($docLocation->error) {
                self::addError($docLocation->errorString);

                return false;
            }
            require_once $docLocation->fullPath;
            self::$formValidator = new $docLocation->className;
        }
        self::errorReset();
        return self::$formValidator->$name();
    }

    /**
     * Checks an uploaded image for proper size, formatting, and lack of errors in the upload.
     *
     * @param  string $data - The name of the upload field.
     *
     * @return boolean
     */
    public static function imageUpload($imageName)
    {
        if (!Config::get('uploads/enabled') || !Config::get('uploads/images')) {
            self::addError('Image uploads are disabled.');

            return false;
        }

        if (!isset($_FILES[$imageName])) {
            self::addError('File not found.', $imageName);

            return false;
        }

        if ($_FILES[$imageName]['error'] != 0) {
            self::addError('File error:' . $_FILES[$imageName]['error']);

            return false;
        }

        if ($_FILES[$imageName]['size'] > Config::get('uploads/maxImageSize')) {
            self::addError("Image is too large.");

            return false;
        }

        $whitelist = [".jpg",".jpeg",".gif",".png"];
        $fileType = strrchr($_FILES[$imageName]['name'], '.');
        if (!(in_array($fileType, $whitelist))) {
            self::addError("Invalid image type", $fileType);

            return false;
        }
        return true;
    }

    /**
     * Checks a string for a boolean string value.
     *
     * @param  string $data - The data being checked.
     *
     * @return boolean
     */
    public static function tf($data)
    {
        if ($data == 'true') {
            return true;
        }

        if ($data == 'false') {
            return true;
        }

        self::addError('Invalid true-false: ', $data);

        return false;
    }

    /**
     * Checks for alpha-numeric type.
     *
     * @param  string $data - The data being checked.
     *
     * @return boolean
     */
    public static function alnum($data)
    {
        if (ctype_alpha($data)) {
            return true;
        }
        self::addError('Invalid alpha-numeric.', $data);

        return false;
    }

    /**
     * Checks for alpha-numeric type.
     *
     * @param  string $data - The data being checked.
     *
     * @return boolean
     */
    public static function uploads()
    {
        if (ini_get('file_uploads') == 1) {
            return true;
        }
        self::addError('Uploads are disabled.');

        return false;
    }


    /**
     * Checks an input for spaces.
     *
     * @param  string $data - The data being checked.
     *
     * @return boolean
     */
    public static function nospace($data)
    {
        if (!stripos($data, ' ')) {
            return true;
        }
        self::addError('Invalid no-space input.', $data);

        return false;
    }

    /**
     * Checks the data to see if it is a digit.
     *
     * @param   mixed     $data   - Data being checked.
     *
     * @return  bool
     */
    public static function id($data)
    {
        if (is_numeric($data)) {
            return true;
        }
        self::addError('Invalid ID.', $data);

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
    public static function dataTitle($data)
    {
        if (preg_match('#^[a-z 0-9\-\_ ]+$#mi', $data)) {
            return true;
        }
        self::addError('Invalid data string.', $data);

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
    public static function path($data)
    {
        if (!preg_match('#^[^/?*:;\\{}]+$#mi', $data)) {
            return true;
        }
        self::addError('Invalid path.', $data);

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
        if (empty($data)) {
            $data = Input::post('token');
        }

        return Token::check($data);
    }

    /**
     * Checks the DB for an email after verifying $data is a valid email.
     *
     * @param   string      $email   - The email being tested.
     *
     * @return  bool
     */
    public static function noEmailExists($email)
    {
        self::connect();
        if (self::email($email)) {
            $emailQuery = self::$db->get('users', ['email', '=', $email]);
            if ($emailQuery->count() == 0) {
                return true;
            }
        }
        self::addError("Email is already in use.\n", $email);

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
            self::addError('Invalid Url');

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
        $sanitizedEmail = filter_var($data, FILTER_SANITIZE_EMAIL);
        if (!filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL)) {
            self::addError("Email is not properly formatted.");

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
     *
     * @todo  - Refine these requirements
     */
    public static function password($data, $data2 = null)
    {
        if (strlen($data) < 6) {
            self::addError("Password is not properly formatted.");
            return false;
        }
        if (strlen($data) > 20) {
            self::addError("Password is not properly formatted.");
            return false;
        }
        if (isset($data2) && $data !== $data2) {
            self::addError("Passwords do not match.");
            return false;
        }

        return true;
    }

    /**
     * Checks the DB for a username after verifying $data is a valid username.
     *
     * @param string $data - The string being tested
     *
     * @return bool
     *
     * @todo  - this cannot rely on the user model
     */
    public static function usernameExists($data)
    {
        self::connect();
        if (self::username($data)) {
            $usernameResults = self::$db->get('users', ['username', '=', $data]);
            if ($usernameResults->count()) {
                return true;
            }
        }
        self::addError("No user exists in the DB.", $data);

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
     * @return boolean
     */
    public static function username($data)
    {
        if (strlen($data) > 16) {
            self::addError("Username must be be 4 to 16 numbers or letters.", $data);

            return false;
        }
        if (strlen($data) < 4) {
            self::addError("Username must be be 4 to 16 numbers or letters.", $data);

            return false;
        }
        if (!ctype_alnum($data)) {
            self::addError("Username must be be 4 to 16 numbers or letters.", $data);

            return false;
        }

        return true;
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
     * @return boolean
     */
    public static function name($data)
    {
        if (strlen($data) > 20) {
            self::addError("Name is too long.", $data);

            return false;
        }
        if (strlen($data) < 2) {
            self::addError("Name is too short.", $data);

            return false;
        }
        if (!ctype_alpha(str_replace(' ', '', $data))) {
            self::addError("Name is not properly formatted.", $data);

            return false;
        }
        return true;
    }

    /**
     * Checks the PHP version.
     *
     * Requirements:
     * - version 5.6 or higher
     *
     * @return boolean
     *
     * @todo  - use version compare here.
     */
    public static function php()
    {
        $phpVersion = phpversion();
        if ($phpVersion >= 5.6) {
            return true;
        }
        self::addError("PHP version is too old.", $phpVersion);

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
        self::addError("PHP Mail function is not enabled.");

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
        self::addError("PHP Safe Mode is enabled.");

        return false;
    }

    /**
     * Checks to make sure sessions are working properly.
     *
     * @return bool
     *
     * @todo  - add unset.
     */
    public static function sessions()
    {
        $_SESSION['sessionTest'] = 1;
        if (!empty($_SESSION['sessionTest'])) {
            return true;
        }
        self::addError("There is an error with saving sessions.");

        return false;
    }

    /**
     * Checks the current database in the configuration file for version verification.
     *
     * @return boolean
     *
     * @todo  - Update this function to be more effective.
     */
    public static function mysql()
    {
        self::connect();
        $dbVersion = self::$db->version();
        preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $dbVersion, $version);
        if (version_compare($version[0], '10.0.0', '>')) {
            return true;
        }
        self::addError("Mysql Version is too low! Current version is $version[0]. Version 10.0.0 or higher is required.");
        
        return false;
    }

    /**
     * Checks to see if cookies are enabled.
     *
     * @return boolean
     *
     * @todo  - Come back to this, if for no other reason than to
     *          unset the cookie and use the application cookie name.
     */
    public static function cookies()
    {
        Cookie::put('test', 'test');
        if (count($_COOKIE) > 0) {
            return true;
        }
        self::addError("Cookies are not enabled.");

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
    public static function db($host = null, $db = null, $user = null, $pass = null)
    {
        if (empty($host) || empty($db) || empty($user) || empty($pass)) {
            self::addError("check::db: one or more parameters are missing.");
            
            return false;
        }
        try {
            Debug::log('Attempting to connect to DB with supplied credentials.');
            $test = new \PDO('mysql:host='.$host.';dbname='.$db, $user, $pass);
        } catch (\PDOException $Exception) {
            self::addError("Cannot connect to DB with provided credentials.", $Exception->getMessage());

            return false;
        }

        return true;
    }
}
