<?php

/* 
 * Copyright (C) 2016 luc <luc@def-shop.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

use GreyLogException\GreyLogException;
use GreyLogException\GreyLogExceptionConfig;

GreyLogExceptionConfig::$sApplicationNameToLog = "SampleApplicationName";
GreyLogExceptionConfig::$sGreyLogServerIp = "192.168.178.32";

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
