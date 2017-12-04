<?php
/**
 * Classes/Hash.php
 *
 * This class is used to salt, hash, and check passwords.
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

class Hash
{
    /**
     * Uses php native hashing scheme to make a password hash.
     *
     * @param string $password - Validated password input.
     *
     * @return string - salted/hashed and ready to use password hash.
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
