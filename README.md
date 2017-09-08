# Tempus Project Core

Tempus project core is intended as the core code used by [The Tempus Project](https://github.com/joeyk4816/thetempusproject) which is a rapid prototyping framework. 

The core functionality utilizes the MVC architecture in addition to a custom templating engine designed to make building web applications simple.

## Installation and Use
This code is available as is and is no way guaranteed. This code is not created or intended to be used outside of The Tempus Project so has not been written to function without a folder structure resembling that in TheTempusProject as well as a .htaccess file that rewrites all traffic into a root index file.

I may modify this package to be more friendly in the future as the code base expands, but it is currently not planned. 

The easiest way to use TPC in your own application is to install and initialize it via composer.

```
"require": {

    "joeyk4816/tempus-project-core": "*",

},

"autoload": {

    "psr-4": {

        "TempusProjectCore\": "vendor/joeyk4816/tempus-project-core"

    }

}
```

If you prefer to handle auto-loading via other means, you can simply clone this repository wherever you need it. Please note, you will need to install and load the [firephp](https://github.com/firephp/firephp) library in order to utilize the debug to console options.