# greyLogException
A very usefull state of the art exception class and handler for PHP 7
[![Latest Stable Version](https://img.shields.io/packagist/v/lucderheld/greylogexception.svg?style=flat-square)](https://packagist.org/packages/lucderheld/greylogexception) [![Total Downloads](https://img.shields.io/packagist/dt/lucderheld/greylogexception.svg?style=flat-square)](https://packagist.org/packages/lucderheld/greylogexception) 
========

Usage
-----

### Recommended installation via composer:

Add php-greylog-exception to `composer.json` either by running `composer require lucderheld/php-greylog-exception` or by defining it manually:

    "require": {
       // ...
       "graylog2/php-greylog-exception": "1.0"
       // ...
    }

Reinstall dependencies: `composer install`

### Example

```php
    require 'vendor/autoload.php';

    use lucderheld\GreyLogException\GreyLogException;

    class KernelException extends GreyLogException {

        const SAMPLE_EXCEPTION = [0000001, GreyLogException::WARNING, "This is the exception error text with a variable '%s'"];

    }

    class Kernel {

        public $bBooted = false;

        public function __construct() {
            if (!$bBooted) {
                throw new KernelException(KernelException::SAMPLE_EXCEPTION, 'SomeValue');
            }
        }

    }

    new Kernel();
``` 

GreyLog output:

       