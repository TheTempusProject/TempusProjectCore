<?php
/**
 * Classes/Hash.php.
 *
 * This class is used to salt, hash, and check our passwords.
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

class Hash
{
    /**
     * Uses php native hashing scheme to make a password hash.
     *
     * @param string $password - Validated password input.
     *
     * @return string - Salted/hashed and ready to use password hash.
     */
    public static function make($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Uses php native password support to verify the given password.
     *
     * @param string $password - Password being verified.
     * @param string $hash     - Saved password hash.
     *
     * @return bool
     */
    public static function check($password, $hash)
    {
        $result = password_verify($password, $hash);
        if ($result) {
            return true;
        }
        Debug::info('Hash::check: Failed to verify password match.');

        return false;
    }
}
