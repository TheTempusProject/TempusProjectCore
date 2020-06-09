<?php
/**
 * Classes/Image.php.
 *
 * This class is used for manipulation of Images used by the application.
 *
 * @todo  - Add the config switches.
 *          Create a generic uploads class
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

use TempusProjectCore\Functions\Routes as Routes;

class Image
{
    public static $lastUpload = null;
    public static $lastUploadLocation = null;

    /**
     * This function verifies a valid image upload, creates any
     * necessary directories, moves, and saves, the image.
     *
     * @param  string $fieldname - The name of the input field for the upload.
     * @param  string $folder - The sub-folder to store the uploaded image.
     *
     * @return boolean
     */
    public static function upload($fieldname, $folder)
    {
        $uploaddir = Routes::getLocation('imageUploadFolder', $folder)->fullPath;
        if (!Check::imageUpload($fieldname)) {
            Debug::error(Check::systemErrors());
            return false;
        }
        // @todo Let's try and avoid 777 if possible
        // Try catch here for better error handling
        if (!file_exists($uploaddir)) {
            Debug::Info('Creating Directory because it does not exist');
            mkdir($uploaddir, 0777, true);
        }
        self::$lastUpload = basename($_FILES[$fieldname]['name']);
        self::$lastUploadLocation = $uploaddir . self::$lastUpload;
        if (move_uploaded_file($_FILES[$fieldname]['tmp_name'], self::$lastUploadLocation)) {
            return true;
        } else {
            Debug::error('failed to move the file.');
            return false;
        }
    }

    /**
     * Returns the file location of the most recent
     * uploaded image if one exists.
     *
     * @return string - The file location of the most recent uploaded image.
     */
    public static function lastLocation()
    {
        return self::$lastUploadLocation;
    }
    
    /**
     * Returns the name of the most recent
     * uploaded image if one exists.
     *
     * @return string - The filename of the most recent uploaded image.
     */
    public static function last()
    {
        return self::$lastUpload;
    }
}
