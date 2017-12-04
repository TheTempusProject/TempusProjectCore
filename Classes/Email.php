<?php
/**
 * Classes/Email.php
 *
 * This is our class for constructing and sending various kinds of emails.
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

use TempusProjectCore\Core\Template as Template;
use TempusProjectCore\Functions\Docroot as Docroot;

class Email
{
    private static $header = null;
    private static $subject = null;
    private static $title = null;
    private static $message = null;
    private static $unsub = false;
    private static $useTemplate = false;
    private static $footer = null;
    private static $debug = false;

    /**
     * Sends pre-constructed email templates. Useful for modifying the
     * entire theme or layout of the system generated emails.
     *
     * @param string       $email  - The email you are sending to.
     * @param string       $type   - The template you wish to send.
     * @param string|array $params - Any special parameters that may be required from your individual email template.
     *
     * @return bool
     */
    public static function send($email, $type, $params = null, $flags = null)
    {
        if (!empty($flags)) {
            if (is_array($flags)) {
                foreach ($flags as $key => $value) {
                    switch ($key) {
                        case 'template':
                            if ($value == true) {
                                self::$useTemplate = true;
                            }
                            break;
                        case 'unsubscribe':
                            if ($value == true) {
                                self::$unsub = true;
                            }
                            break;
                        case 'debug':
                            if ($value == true) {
                                self::$debug = false;
                            }
                            break;
                    }
                }
            }
        }
        self::build();
        switch ($type) {
            case 'debug':
                self::$subject = 'Please Confirm your email at {SITENAME}';
                self::$title   = 'Almost Done';
                self::$message = 'Please click or copy-paste this link to confirm your registration: <a href="{BASE}register/confirm/{PARAMS}">Confirm Your Email</a>';
                break;

            case 'confirmation':
                self::$subject = 'Please Confirm your email at {SITENAME}';
                self::$title   = 'Almost Done';
                self::$message = 'Please click or copy-paste this link to confirm your registration: <a href="{BASE}register/confirm/{PARAMS}">Confirm Your Email</a>';
                break;

            case 'install':
                self::$subject = 'Notification from {SITENAME}';
                self::$title = 'Installation Success';
                self::$message = 'This is just a simple email to notify you that you have successfully installed The Tempus Project framework!';
                break;

            case 'passwordChange':
                self::$subject = 'Security Notice from {SITENAME}';
                self::$title = 'Password Successfully Changed';
                self::$message = 'Recently your password on {SITENAME} was changed. If you are the one who changed the password, please ignore this email.';
                break;

            case 'emailChangeNotice':
                self::$subject = 'Account Update from {SITENAME}';
                self::$title = 'Email Updated';
                self::$message = 'This is a simple notification to let you know your email has been changed at {SITENAME}.';
                break;

            case 'emailChange':
                self::$subject = 'Account Update from {SITENAME}';
                self::$title = 'Confirm your E-mail';
                self::$message = 'Please click or copy-paste this link to confirm your new Email: <a href="{BASE}register/confirm/{PARAMS}">Confirm Your Email</a>';
                break;

            case 'emailNotify':
                self::$subject = 'Account Update from {SITENAME}';
                self::$title = 'Email Updated';
                self::$message = 'You recently changed your email address on {SITENAME}.';
                break;

            case 'forgotPassword':
                self::$subject = 'Reset Instructions for {SITENAME}';
                self::$title = 'Reset your Password';
                self::$message = 'You recently requested information to change your password at {SITENAME}.<br>Your password reset code is: {PARAMS}<br> Please click or copy-paste this link to reset your password: <a href="{BASE}register/reset/{PARAMS}">Password Reset</a>';
                break;

            case 'forgotUsername':
                self::$subject = 'Account Update from {SITENAME}';
                self::$title = 'Account Details';
                self::$message = 'Your username for {SITENAME} is {PARAMS}.';
                break;

            case 'subscribe':
                self::$subject = 'Thanks for Subscribing';
                self::$title = 'Thanks for Subscribing!';
                self::$message = 'Thank you for subscribing to updates from {SITENAME}. If you no longer wish to receive these emails, you can un-subscribe using the link below.';
                self::$unsub = true;
                break;

            case 'unsubInstructions':
                self::$subject = 'Unsubscribe Instructions';
                self::$title = 'We are sad to see you go';
                self::$message = 'If you would like to be un-subscribed from future emails from {SITENAME} simply click the link below.<br><br><a href="{BASE}home/unsubscribe/{EMAIL}/{PARAMS}">Click here to unsubscribe</a>';
                self::$unsub = true;
                break;

            case 'unsubscribe':
                self::$subject = 'Unsubscribed';
                self::$title = 'We are sad to see you go';
                self::$message = 'This is just a notification that you have successfully been unsubscribed from future emails from {SITENAME}.';
                break;

            case 'contact':
                self::$subject = $params['subject'];
                self::$title = $params['title'];
                self::$message = $params['message'];
                break;

            default:
                return false;
                break;
        }
        if (self::$useTemplate) {
            $data = new \stdClass();
            if (self::$unsub) {
                $data->UNSUB = Template::standardView('mail.default.unsub');
            } else {
                $data->UNSUB = '';
            }
            $data->LOGO = Config::get('main/logo');
            $data->SITENAME = Config::get('main/name');
            $data->EMAIL = $email;
            if (!is_array($params)) {
                $data->PARAMS = $params;
            } else {
                foreach ($params as $key => $value) {
                    $data->$key = $value;
                }
            }
            $data->MAIL_FOOT = Template::standardView('mail.default.foot');
            $data->MAIL_TITLE = self::$title;
            $data->MAIL_BODY = Template::parse(self::$message, $data);
            $subject = Template::parse(self::$subject, $data);
            $body = Template::standardView('mail.default.template', $data);
        } else {
            $subject = self::$subject;
            $body = '<h1>' . self::$title . '</h1>' . self::$message;
        }
        if (is_object($email)) {
            foreach ($email as $data) {
                mail($data->email, $subject, $body, self::$header);
            }
        } else {
            mail($email, $subject, $body, self::$header);
        }
        Debug::info("Email sent: $type.");

        return true;
    }

    /**
     * Constructor for the header.
     */
    public static function build()
    {
        if (!self::$header) {
            self::$header = 'From: ' . Config::get('main/name') . ' <noreply@' . $_SERVER['HTTP_HOST'] . ">\r\n";
            self::$header .= "MIME-Version: 1.0\r\n";
            self::$header .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $url = parse_url(Docroot::getAddress(), PHP_URL_HOST);
            $parts = explode(".", $url);
            $count = count($parts);
            if ($count > 2) {
                $host = $parts[$count - 2] . "." . $parts[$count - 1];
            } else {
                $host = $url;
            }
            if (self::$debug) {
                self::$header .= "CC: webmaster@localohost.com\r\n";
            }
        }
    }
}
