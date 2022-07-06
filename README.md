# Tempus Project Core
###### Developer(s): Joey Kimsey

TempusProjectCore is the core functionality used by [The Tempus Project](https://github.com/TheTempusProject/TheTempusProject) a rapid prototyping framework. This Library can be utilized outside of the TempusProject, but the functionality has not been tested well as a stand alone library.

This library utilizes the MVC architecture in addition to a custom templating engine designed to make building web applications fast and simple.

**Notice: This Library is provided as is, please use at your own risk.**

## Installation and Use
The easiest way to use TPC in your application is to install and initialize it via composer.

```
"require": {

    "TheTempusProject/TempusProjectCore": "*",

},

"autoload": {

    "psr-4": {

        "TempusProjectCore\": "vendor/TheTempusProject/TempusProjectCore"

    }

}
```

If you prefer to handle auto-loading via other means, you can simply clone this repository wherever you need it. Please note, you will need to install and load the [TempusDebugger](https://github.com/thetempusproject/TempusDebugger) library in order to utilize the debug to console options.

### WIP:
- [ ] Expansion of PDO to allow different database types
- [ ] Expansion of apache usability to include nginx
- [ ] some 'classes' that are entirely static have been moved to functions and need to be updated
- [ ] some 'functions' have the oposite
- [ ] some items that are static need to be converted
- [ ] template stuff should really only be called from template/controllers
- [ ] Update installer to account for updates.
- [ ] Impliment uniformity in terms of error reporting, exceptions, logging.



need the ability for the autoloader to accept specific file name associations
needs a require_all
need to re-namspace all classes and functions
some classes need to be converted to non-static
some functions need to be converted to more static

run from the command line

rewrite the phpdocblokr plugin





     * NOTE: These session names are protected and should not be used by any other aspect of app
     * - success
     * - notice
     * - error
     * - info

     * NOTE: Notices shall not interfere with execution of the
     * application.








ideas:
pretty page source -> before output, try amd fix how the html will display
page export -> this whole thing is basically a templating engine using php, build exports and imports with it
    full html/css/js websites from the engine

