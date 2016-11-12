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

/**
 * This class contains all exceptions that can be thrown in the class "Kernel".
 * All exceptions have to be defined in format: 
 * [ExceptionCode (int), ExceptionLevel (LfException::Level), ExceptionMessage (String)]
 * If you want to have special code that is fired on a specific exception,
 * just add an public static function with the same name like the constant to the class.
 */
class KernelException extends GreyLogException{
    
    /**
     * A testexception that can be thrown for testing.
     * The first array-element is the exception code.
     * The second array-element is the exception level. Use one of the defined PSR-Log-Levels.
     * The third array-element is the exception message. You can use parameters in it.
     * @return String Error was thrown to test an KernelExceptionError.
     */
    const TEST_ERROR = [10001, GreyLogException::ERROR, "Error was thrown to test an KernelExceptionError"];
    
    /**
     * Another example for a warning exception.
     * @param string %s Variable1, first variable to replace.
     * @param string %s Variable2, second variable to replace.
     * @param int %d Variable3, last variable to replace.
     * @return Warning! '%s' was called. And '%s' was doing it %d times
     */
    const TEST_WARNING = [10002, GreyLogException::WARNING, "Warning! '%s' was called. And '%s' was doing it %d times"];
    
    /**
     * In this example it is not only logged an error. Before the error is logged, the function "TEST_ERROR_WITH_USER_FUNCTION" is called.
     * @return This logged alert did call a user defined class mehtod 'TEST_ERROR_WITH_USER_FUNCTION' first
     */
    const TEST_ERROR_WITH_USER_FUNCTION = [10003, GreyLogException::ALERT, "This logged alert did call a user defined class mehtod 'TEST_ERROR_WITH_USER_FUNCTION' first"];

    public static function TEST_ERROR_WITH_USER_FUNCTION(){
        print_r(PHP_EOL."FUNCTION ".__METHOD__." was called!".PHP_EOL);
    }
    
}

/**
 * Sample class
 */
class Kernel{
    public function __constuct(){
        echo PHP_EOL."Initialized successfully!".PHP_EOL;
    }
    
    public function doSomeThingWorse(){
        try{
            if("A" != "B"){
                throw new GreyLogException(KernelException::TEST_ERROR);
            }
        } catch (Exception $ex) {
            echo "Exception was logged and thrown:";
            echo "<pre>";
            print_r($ex);
        }
    }
    
    public function doSomeThingWorser(){
        try{
            if("A" !== "B"){
                throw new GreyLogException(KernelException::TEST_WARNING, __FUNCTION__, __CLASS__, rand(1, 100));
            }
        } catch (Exception $ex) {
            echo "Exception was logged and thrown:";
            echo "<pre>";
            print_r($ex);
        }
    }
    
    public function doSomethingSpecialWorse(){
        try{
            if("SpecialA" !== "B"){
                throw new GreyLogException(KernelException::TEST_ERROR_WITH_USER_FUNCTION);
            }
        } catch (Exception $ex) {
            echo "Exception was logged and thrown:";
            echo "<pre>";
            print_r($ex);
        }
    }
}

$Kernel = new Kernel;

$Kernel->doSomeThingWorse();

$Kernel->doSomeThingWorser();

$Kernel->doSomethingSpecialWorse();