<?php
/**
 * Classes/CustomException.php
 *
 * This class is used exclusively when throwing predefined exceptions.
 * It will intercept framework thrown exceptions and deal with them however
 * you choose; in most cases by logging them and taking appropriate responses
 * such as redirecting to error pages.
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

use \Exception as Exception;

class CustomException extends Exception
{
    private $exceptionName = null;
    private $originFunction = null;
    private $originClass = null;

    /**
     * This function allows the application to deal with errors
     * in a dynamic way by letting you customize the response
     *
     * @param string $type - The type of the exception being called/thrown.
     * @param string $data - Any additional data being passed with the exception.
     *
     * @example   - throw new CustomException('model'); - Calls the model-missing exception
     */
    public function __construct($type, $data = null)
    {
        $this->originFunction = debug_backtrace()[1]['function'];
        $this->originClass = debug_backtrace()[1]['class'];
        $this->exceptionName = $type;
        switch ($type) {
            case 'model':
                Debug::error('Model not found: ' . $data);
                break;

            case 'dbConnection':
                Debug::error('Error Connecting to the database: ' . $data);
                break;

            case 'DB':
                Debug::error('Unspecified database error: ' . $data);
                break;

            case 'view':
                Debug::error('View not found: ' . $data);
                break;

            case 'controller':
                Debug::error('Controller not found: ' . $data);
                Redirect::to(404);
                break;

            case 'defaultController':
                Debug::error('DEFAULT Controller not found: ' . $data);
                Redirect::to(404);
                break;

            case 'method':
                Debug::error('Method not found: ' . $data);
                Redirect::to(404);
                break;

            case 'standardView':
                Debug::error('View not found: ' . $data);
                if (Debug::status()) {
                    Issue::error('Missing View: ' . $data);
                }
                Redirect::to(404);
                break;

            case 'defaultMethod':
                Debug::error('DEFAULT Method not found: ' . $data);
                Redirect::to(404);
                break;

            default:
                Debug::error('Default exception: ' . $data);
                break;
        }
    }
}
