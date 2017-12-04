# Tempus Project Core
#### Framework Core Library
###### Developer(s): Joey Kimsey

TempusProjectCore is the foundational functionality used by [The Tempus Project](https://github.com/TheTempusProject/TheTempusProject) a rapid prototyping framework. There has been no clear effort to ensure this Library will function in stand-alone environments and there are no plans to do so at this time.

This library utilizes the MVC architecture in addition to a custom templating engine designed to make building web applications simple. 

**Notice: This code is in _still_ not production ready. This Library is provided as is, please use at your own risk.**

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

If you prefer to handle auto-loading via other means, you can simply clone this repository wherever you need it. Please note, you will need to install and load the [firephp](https://github.com/firephp/firephp) library in order to utilize the debug to console options.