<?php
/**
 * Classes/Image.php.
 *
 * This class is used for manipulation of Images used by the application.
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

class Image
{
    public static $last_upload = null;
    public static function upload_image($data, $user)
    {
        $uploaddir = Config::get('main/location') . 'Images/Uploads/' . $user . '/';
        if(!Check::image_upload($data)) {
            Debug::warn('Image Check Failed');
            return false;
        }
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir, 0777, true);
            Debug::Info('Creating Directory because it doesn\'t exist');
        }
        $uploadfile = $uploaddir . basename($_FILES[$data]['name']);
        Self::$last_upload = $_FILES[$data]['name'];
        if (move_uploaded_file($_FILES[$data]['tmp_name'], $uploadfile)) {
            return true;
        } else {
            Debug::error('failed to move the file.');
            return false;
        }
    }
    public static function last($username=null)
    {
        return Self::$last_upload;
    }
}