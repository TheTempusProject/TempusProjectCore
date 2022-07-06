<?php
/**
 * core/template/forms.php
 *
 * This class is for managing template forms.
 *
 * @version 3.0
 * @author  Joey Kimsey <Joey@thetempusproject.com>
 * @link    https://TheTempusProject.com/Core
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
namespace TempusProjectCore\Template;

class Forms
{
    public static $options = [];

    /**
     * Sets the specified radio button with $x value to checked.
     *
     * @param {string} [$fieldName] - The name of the radio field.
     * @param {string} [$value] - The field value to be selected.
     */
    public static function selectRadio($fieldName, $value)
    {
        $selected = 'CHECKED:' . $fieldName . '=' . $value;
        Components::set($selected, 'checked="checked"');
    }

    /**
     * This will add an option to our selected options menu that will
     * automatically be selected when the template is rendered.
     *
     * @param {string} [$value] - The value of the option you want selected.
     */
    public static function selectOption($value)
    {
        $find = "#\<option (.*?)value=\'" . $value . "\'#s";
        $replace = "<option $1value='" . $value . "' selected";
        self::$options[$find] = $replace;
    }
}