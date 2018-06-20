# Tempus Project Core
#### Framework Core Library
###### Developer(s): Joey Kimsey

TempusProjectCore is the core functionality used by [The Tempus Project](https://github.com/TheTempusProject/TheTempusProject) a rapid prototyping framework. This Library can be utilized outside of the TempusProject, but the functionality has not been tested well as a stand alone library.

This library utilizes the MVC architecture in addition to a custom templating engine designed to make building web applications simple. 

**Notice: This Library is provided as is, please use at your own risk.**

## Installation and Use
The easiest way to use TPC in your own application is to install and initialize it via composer.

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