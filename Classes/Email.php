<?php
/**
 * Classes/Email.php.
 *
 * This is our class for constructing and sending various kinds of emails.
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

use TempusProjectCore\Core\Template as Template;

class Email
{
    private static $header = null;
    private static $subject = null;
    private static $title = null;
    private static $message = null;
    private static $unsub = false;
    private static $useTemplate = false;
    private static $footer = null;
    /**
     * THIS IS STRICLY FOR DEVELOPMENT PURPOSES ONLY AND SHOULD REMAIN COMMENTED
    private static $debug = true;
     */

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
                                Self::$useTemplate = true;
                            }
                            break;
                        case 'unsubscribe':
                            if ($value == true) {
                                Self::$unsub = true;
                            }
                            break;
                        /**
                         * THIS IS STRICLY FOR DEVELOPMENT PURPOSES ONLY AND SHOULD REMAIN COMMENTED
                        case 'debug':
                            if ($value == true) {
                                Self::$debug = true;
                            }
                            break;
                         */
                        default:
                            
                            break;
                    }
                }
            }
        }
        Self::build();
        switch ($type) {
            case 'debug':
                Self::$subject = 'Please Confirm your email at {SITENAME}';
                Self::$title   = 'Almost Done';
                Self::$message = 'Please click or copy-paste this link to confirm your registration: <a href="{BASE}register/confirm/{PARAMS}">Confirm Your Email</a>';
            break;

            case 'confirmation':
                Self::$subject = 'Please Confirm your email at {SITENAME}';
                Self::$title   = 'Almost Done';
                Self::$message = 'Please click or copy-paste this link to confirm your registration: <a href="{BASE}register/confirm/{PARAMS}">Confirm Your Email</a>';
            break;

            case 'install':
                Self::$subject = 'Notification from {SITENAME}';
                Self::$title = 'Installation Success';
                Self::$message = 'This is just a simple email to notify you that you have successfully installed The Tempus Project framework!';
            break;

            case 'password_change':
                Self::$subject = 'Security Notice from {SITENAME}';
                Self::$title = 'Password Successfully Changed';
                Self::$message = 'Recently your password on {SITENAME} was changed. If you are the one who changed the password, please ignore this email.';
            break;

            case 'email_change_notice':
                Self::$subject = 'Account Update from {SITENAME}';
                Self::$title = 'Email Updated';
                Self::$message = 'This is a simple notification to let you know your email has been changed at {SITENAME}.';
            break;

            case 'email_change':
                Self::$subject = 'Account Update from {SITENAME}';
                Self::$title = 'Confirm your E-mail';
                Self::$message = 'Please click or copy-paste this link to confirm your new Email: <a href="{BASE}register/confirm/{PARAMS}">Confirm Your Email</a>';
            break;

            case 'email_notify':
                Self::$subject = 'Account Update from {SITENAME}';
                Self::$title = 'Email Updated';
                Self::$message = 'You recently changed your email address on {SITENAME}.';
            break;

            case 'forgot_password':
                Self::$subject = 'Reset Instructions for {SITENAME}';
                Self::$title = 'Reset your Password';
                Self::$message = 'You recently requested information to change your password at {SITENAME}. Please click or copy-paste this link to reset your password: <a href="{BASE}register/reset/{PARAMS}">Password Reset</a>';
            break;

            case 'forgot_username':
                Self::$subject = 'Account Update from {SITENAME}';
                Self::$title = 'Account Details';
                Self::$message = 'Your username for {SITENAME} is {PARAMS}.';
            break;

            case 'subscribe':
                Self::$subject = 'Thanks for Subscribing';
                Self::$title = 'Thanks for Subscribing!';
                Self::$message = 'Thank you for subscribing to updates from {SITENAME}. If you no longer wish to receive these emails, you can un-subscribe using the link below.';
                Self::$unsub = true;
            break;

            case 'unsubInstructions':
                Self::$subject = 'Unsubscribe Instructions';
                Self::$title = 'We are sad to see you go';
                Self::$message = 'If you would like to be un-subscribed from future emails from {SITENAME} simply click the link below.<br><br><a href="{BASE}home/unsubscribe/{EMAIL}/{PARAMS}">Click here to unsubscribe</a>';
                Self::$unsub = true;
            break;

            case 'unsubscribe':
                Self::$subject = 'Unsubscribed';
                Self::$title = 'We are sad to see you go';
                Self::$message = 'This is just a notification that you have successfully been un-subscribed from future emails from {SITENAME}.';
            break;

            case 'contact':
                Self::$subject = $params['subject'];
                Self::$title = $params['title'];
                Self::$message = $params['message'];
            break;

            default:
            
                return false;
            break;
        }
        if (Self::$useTemplate) {
            $data = new \stdClass();
            if (Self::$unsub) {
                $data->UNSUB = Template::standard_view('mail.default.unsub');
            } else {
                $data->UNSUB = '';
            }
            $data->LOGO = Config::get('main/logo');
            $data->SITENAME = Config::get('main/name');
            $data->EMAIL = $email;
            if ($type !== 'contact') {
                $data->PARAMS = $params;
            } else {
                $data->PARAMS = $params['confirmation_code'];
            }
            //$data->MAIL_HEAD = Template::standard_view('mail.default.head');
            $data->MAIL_FOOT = Template::standard_view('mail.default.foot');
            $data->MAIL_TITLE = Self::$title;
            $data->MAIL_BODY = Template::parse(Self::$message, $data);
            $subject = Template::parse(Self::$subject, $data);
            $body = Template::standard_view('mail.default.template', $data);
        } else {
            $subject = Self::$subject;
            $body = '<h1>' . Self::$title . '</h1>' . Self::$message;
        }
        if (is_object($email)) {
            foreach ($email as $data) {
                mail($data->email, $subject, $body, Self::$header);
            }
        } else {
            mail($email, $subject, $body, Self::$header);
        }
        Debug::info("Email sent: $type.");

        return true;
    }

    /**
     * Constructor for the header.
     */
    public static function build()
    {
        if (!Self::$header) {
            Self::$header = 'From: '.Config::get('main/name').' <noreply@'.$_SERVER['HTTP_HOST'].">\r\n";
            Self::$header .= "MIME-Version: 1.0\r\n";
            Self::$header .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $url = parse_url(Config::get('main/base'), PHP_URL_HOST);
            $parts = explode(".", $url);
            $count = count($parts);
            if ($count > 2) {
                $host = $parts[$count-2] . "." . $parts[$count-1];    
            } else {
                $host = $url;
            }
            /**
             * THIS IS STRICLY FOR DEVELOPMENT PURPOSES ONLY AND SHOULD REMAIN COMMENTED
            if (Self::$debug) {
                Self::$header .= "CC: test@localohost.com\r\n";
            }
             */
        }
    }
}
