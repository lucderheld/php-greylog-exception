# php-greylog-exception
Is a very usefull state of the art exception class and handler for PHP 7
[![Latest Stable Version](https://img.shields.io/packagist/v/lucderheld/php-greylog-exception.svg?style=flat-square)](https://packagist.org/packages/lucderheld/php-greylog-exception) [![Total Downloads](https://img.shields.io/packagist/dt/lucderheld/php-greylog-exception.svg?style=flat-square)](https://packagist.org/packages/lucderheld/php-greylog-exception) [![Build Status](https://img.shields.io/travis/lucderheld/php-greylog-exception.svg?style=flat-square)](https://travis-ci.org/lucderheld/php-greylog-exception) [![Build Status2](https://scrutinizer-ci.com/g/lucderheld/php-greylog-exception/badges/build.png?b=master)](https://scrutinizer-ci.com/g/lucderheld/php-greylog-exception/build-status/master) [![Code Coverage](https://scrutinizer-ci.com/g/lucderheld/php-greylog-exception/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lucderheld/php-greylog-exception/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lucderheld/php-greylog-exception/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lucderheld/php-greylog-exception/?branch=master)
========

Usage / Installation
-----

### Recommended installation via composer:

Add php-greylog-exception to `composer.json` either by running `composer require lucderheld/php-greylog-exception` or by defining it manually:

    "require": {
       // ...
       "lucderheld/php-greylog-exception": "1.0"
       // ...
    }

Reinstall dependencies: `composer install`

### Install GrayLog2-Server:

How GrayLog2 can be installed is well documented at http://docs.graylog.org/en/2.1/pages/installation.html 
The easiest way to test GreyLog is to run it as a Virtual-Machine:

[Install GrayLog2-Server as VM](http://docs.graylog.org/en/2.1/pages/installation/virtual_machine_appliances.html)

1. Usage Example
-----

```php

<?php
    
require 'vendor/autoload.php';

use GreyLogException\GreyLogException;
use GreyLogException\GreyLogExceptionConfig;

GreyLogExceptionConfig::$sApplicationNameToLog = "SampleApplicationName";
GreyLogExceptionConfig::$sGreyLogServerIp = "127.0.0.1";

class KernelException extends GreyLogException {

    const SAMPLE_EXCEPTION = [10000001, GreyLogException::WARNING, "This is the exception error text with a variable '%s'"];

}

class Kernel {

    public static $bBooted = false;

    public function __construct() {
        try {
            if (!Kernel::$bBooted) {
                throw new KernelException(KernelException::SAMPLE_EXCEPTION, "someValue");
            }
        } catch (KernelException $e) {
            echo "Exception " . $e->getCode() . " was sent to GreyLog-Server " . GreyLogExceptionConfig::$sGreyLogServerIp;
        }
    }

}

new Kernel();
``` 

### GreyLog output:

![Example output](https://github.com/lucderheld/php-greylog-exception/blob/master/samples/greylog-output.png)

2. Combination with own functions
-----

php-greylog-exception can be combined with user defined exception-functions. The functions are triggered before the exception is logged to GreyLog.
To define a exception-function, just create a static-function and name it the same as the actual exception.

### Example:

```php

//...

class KernelException extends GreyLogException {

    const NOT_BOOTED = [10000002, GreyLogException::NOTICE, "This exceptions fires the function KernelException::NOT_BOOTED() before logging the exception to GrayLog"];

    public static function NOT_BOOTED(){
        Kernel::$bBooted = true;
        echo "The function ".__FUNCTION__." was called!";
    }
}

class Kernel {

    public static $bBooted = false;

    public function __construct() {
        try {
            if (!Kernel::$bBooted) {
                throw new KernelException(KernelException::NOT_BOOTED);
            }
        } catch (KernelException $e) {
            echo "Exception " . $e->getCode() . " was sent to GreyLog-Server " . GreyLogExceptionConfig::$sGreyLogServerIp;
        }
    }

}

new Kernel();
``` 

### PHP-Output:

`The function NOT_BOOTED was called!Exception 10000002 was sent to GreyLog-Server 127.0.0.1`

3. Parameter logging
-----

When an exception occours it is important to have as many informations as possible. The php-greylog-exception-class collects all the parameters that where called and saves them as serialized strings.

### Example:

```php
//...

class KernelException extends GreyLogException {

    const PARAMETER_SAMPLE = [10000003, GreyLogException::ERROR, "This exception is a sample exception for showing variables"];

}

class Kernel {

    public static $bBooted = false;

    public function __construct(array $_aSampleArray) {
        if (!Kernel::$bBooted) {
            throw new KernelException(KernelException::PARAMETER_SAMPLE);
        }
    }

}

class SampleClass {}

new Kernel(array(1, "two", new SampleClass()), 'NotNeededParameter');
``` 

### GreyLog output:

![Example parameter output](https://github.com/lucderheld/php-greylog-exception/blob/master/samples/greylog-parameter-logging.png)

License
-----

The library is licensed under the GPL3 license. For details check out the LICENSE file.

Development & Contributing
-----

You are welcome to modify, extend and bugfix as much as you like! :-) If you have any questions/proposals/etc. you are welcome to contact me via email.
