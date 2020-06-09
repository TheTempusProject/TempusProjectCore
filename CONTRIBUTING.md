# Contribution Guidelines for TempusProjectCore
Contributing to TempusProjectCore is completely voluntary and should follow all of the guidelines listed here in order to ensure the highest probability of acceptance. It is highly recommended to use a php linter to automate more of this process. The project is maintained on github and all contributions need to be submitted via pull request to their specific repository under the `dev` branch. In order to contribute, simply follow the instructions for [creating a pull request](#creating-a-pull-request) below.

## Pull Request Requirements
- All revisions must follow TTP naming conventions (see [Naming Conventions](#naming-conventions) Section)
- Include a clear and concise explanation of the features or changes included in your revision listed by file.
- All code must follow [PSR 2](http://www.php-fig.org/psr/psr-2/) standards
- prefer the use of [] for arrays over array()
- All functions must be documented with the exception of controller methods (see [Documentation](#documentation) Section)
- Controller methods may be doc-blocked when necessary for clarity (see [Documentation](#documentation) Section)
- All new Classes must include a class level doc-block (see [Documentation](#documentation) Section)
- Any new dependencies will have a longer validation process and should be accompanied by the required information (see [Dependencies](#dependencies) Section)

## Naming Conventions
- File names are to be lower case
- All class names must be upper case
- Any data being stored as a file must be saved in the app directory (with the exception of config which should be stored unde config/)
- Controllers must have a constructor and destructor using the constructor and destructor methods found in resources/
- Views must be named using lowerCamelCase

## Dependencies
Whenever a dependency is updated or added, pull requests must include a section that answers the following questions.
- Why is this dependency required
- Could this be reasonably accomplished within the app by implementing new features in a later version? explain.
- What is the latest stable version that can be used
- What features are absolutely necessary for your feature or modification to work

## Documentation
### Classes

New classes must be prefaced with a doc-block following this style:
```
/**
 * Controllers/admin.php
 *
 * This is the admin controller.
 *
 * @version 2.0
 * @author  Joey Kimsey <JoeyKimsey@thetempusproject.com>
 * @link    https://TheTempusProject.com
 * @license https://opensource.org/licenses/MIT [MIT LICENSE]
 */
```

From top to bottom:
- Filename on the second line
- A description for the file
- The TTP version this file was built for
`@version 1.0`
- The Authors name or alias and email
`@author  first last <email@link.com>`
- A copy of the MIT license
`@license https://opensource.org/licenses/MIT [MIT LICENSE]`
- May include a link for more information
`@link    http://link.com`

### Functions
Functions must be prefaced with a doc-block following this style:
```
/**
 * Intended as a self-destruct session. If the specified session does not
 * exist, it is created. If the specified session does exist, it will be
 * destroyed and returned.
 *
 * @param string $name   - Session name to be created or checked
 * @param string $string - The string to be used if session needs to be
 *                         created. (optional)
 *
 * @return bool|string   - Returns bool if creating, and a string if the
 *                         check is successful.
 */
```

From top to bottom:
- There must be a description of the functions intended usage on the second line
- All parameters should be documented like this
`@param [type] $name - description`
- Any function with a return statement must also be documented as such
`@return [type] - description`

## Creating a Pull Request
This is a simple explanation of how to create a pull request for changes to TempusProjectCore. You can find a detailed walk-through on how to [create a pull request](https://help.github.com/articles/creating-a-pull-request/) on github.

1. First ensure you have followed all the contributing guidelines
2. Squash your merge into a single revision. This will make it easier to view the changes as a whole.
3. You can submit a pull request [here](https://github.com/TheTempusProject/TempusProjectCore/compare)
4. Please submit all pull requests to the dev branch or they will be ignored.