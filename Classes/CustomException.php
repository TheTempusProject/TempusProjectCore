<?php
/**
 * Classes/Custom_Exception.php.
 *
 * This class is used exclusively when throwing predefined exceptions.
 * It will intercept framework thrown exceptions and deal with them how
 * we choose, in most cases by logging them and taking appropriate responses
 * such as redirecting to error pages.
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

class CustomException extends \Exception
{
    private $exceptionName = null;
    private $originFunction = null;
    private $originClass = null;
    private $log = null;

    /**
     * This is a function that basically just allows the application to deal 
     * with errors in a very dynamic way through this single class.
     *
     * @param string $type - The name of the exception you are calling.
     * @param string $data - Any additional data being passed with the exception.
     * 
     * @example  throw new Custom_Exception('exception_model') calls the model missing member function
     */
    public function __construct($type, $data = null)
    {
        $this->originFunction = debug_backtrace()[1]['function'];
        $this->originClass = debug_backtrace()[1]['class'];
        $this->exceptionName = $type;
        switch ($type) {
            case 'messages_reply_update':
                //Self::$_log->error(500, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Message Reply Update Failure: ' . $data);
            break;
            case 'messages_reply_send':
                //Self::$_log->error(500, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Message Reply Send Failure: ' . $data);
            break;
            case 'message_send':
                //Self::$_log->error(500, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Message Send Failure: ' . $data);
            break;
            case 'register':
                //Self::$_log->error(500, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Register exception: ' . $data);
            break;
            case 'log_login':
                Debug::error('Log Error: Login: ' . $data);
            break;
            case 'log_error':
                Debug::error('Log Error: Error: ' . $data);
            break;
            case 'log_feedback':
                Debug::error('Log Error: Feedback: ' . $data);
            break;
            case 'log_bug_report':
                Debug::error('Log Error: Bug Report: ' . $data);
            break;
            case 'model':
                //Self::$_log->error(404, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Model not found: ' . $data);
            break;
            case 'DB_connection':
                //Self::$_log->error(500, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Error Connecting to the database: ' . $data);
            break;
            case 'DB':
                //@todo rethink errors related to the DB.
                //Self::$_log->error(500, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Unspecified database error: ' . $data);
            break;
            case 'view':
                //Self::$_log->error(404, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('View not found: ' . $data);
            break;
            case 'class':
                //Self::$_log->error(404, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Class not found: ' . $data);
            break;
            case 'controller':
                //Self::$_log->error(404, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Controller not found: ' . $data);
            break;
            case 'default_controller':
                //Self::$_log->error(404, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('DEFAULT Controller not found: ' . $data);
                Redirect::to(404);
            break;
            case 'method':
                //Self::$_log->error(404, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Method not found: ' . $data);
            break;
            case 'standard_view':
                //Self::$_log->error(404, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('View not found: ' . $data);
                if (Debug::status()) {
                    Issue::Error('Missing View: ' . $data);
                }
                //Redirect::to(404);
            break;
            case 'default_method':
                //Self::$_log->error(404, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('DEFAULT Method not found: ' . $data);
                Redirect::to(404);
            break;
            default:
                //Self::$_log->error(500, $this->originClass, $this->originFunction, $this->exceptionName, $data);
                Debug::error('Default exception: ' . $data);
            break;
        }
    }
}
