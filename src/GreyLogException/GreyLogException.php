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
namespace GreyLogException;
/**
 * This is the main class for the GreyLogException module.
 * @author luc <luc@def-shop.com>
 * @version 0.1
 */
class GreyLogException extends \Exception {
    
    /**
     * The application name that is logging the exception (facility in GrayLog).
     */
    const Application = "SampleApplicationName";
    /**
     * The IP the GrayLog2-Server listens to.
     */
    const LogServerIp = "192.168.1.39";
    
    /**
     * PSR LogLevel 8
     */
    const EMERGENCY = \Psr\Log\LogLevel::EMERGENCY;
    /**
     * PSR LogLevel 7
     */
    const ALERT = \Psr\Log\LogLevel::ALERT;
    /**
     * PSR LogLevel 6
     */
    const CRITICAL = \Psr\Log\LogLevel::CRITICAL;
    /**
     * PSR LogLevel 5
     */
    const ERROR = \Psr\Log\LogLevel::ERROR;
    /**
     * PSR LogLevel 4
     */
    const WARNING = \Psr\Log\LogLevel::WARNING;
    /**
     * PSR LogLevel 3
     */
    const NOTICE = \Psr\Log\LogLevel::NOTICE;
    /**
     * PSR LogLevel 2
     */
    const INFO = \Psr\Log\LogLevel::INFO;
    /**
     * PSR LogLevel 1
     */
    const DEBUG = \Psr\Log\LogLevel::DEBUG;
    /**
     * The publisher object for GreyLog2-Server.
     * @var \Gelf\Publisher $publisher Holds the publisher object. 
     */
    private $publisher;
    
    /**
     * The message object to send to GreyLog2-Server.
     * @var \Gelf\Message $message Holds the message object.
     */
    private $gelfMessage; 
    /**
     * The actual exception message.
     * @var String  $sExceptionMessage  The message for the exception.
     */
    public $sExceptionMessage;
    /**
     * The actual exception code.
     * @var Integer $iExceptionCode Holds the exception code.
     */
    public $iExceptionCode;
    /**
     * The actual exception identifier/shortMessage.
     * @var String  $sExceptionShortMessage Holds the exceptionShortMessage. 
     */
    public $sExceptionShortMessage;
    /**
     * The actual exception level. Possible values:
     * EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG
     * @var String  $sExceptionLevel    Holds the exceptionLevel.
     */
    private $sExceptionLevel;
    /**
     * The actual class where the exception was thrown.
     * @var String  $sExceptionClass    Holds the exceptionClass.
     */
    private $sExceptionClass;
    /**
     * The actual function/method where the exception was thrown.
     * @var String  $sExceptionFunction Holds the exceptionFunction.
     */
    private $sExceptionFunction;
    /**
     * The actual exception class the exception came from.
     * @var String  $sModuleName    Holds the moduleName.
     */
    private $sModuleName;
    /**
     * Sends a new exception with the help of an exceptionDetails array and aditional parameters.
     * @param Array $_aExceptionDetails The exceptionDetails in form: "[int ExceptionCode, lfException::LOGLEVEL, string ExceptionMessage]".
     * @param Mixed $_aAdditionalInformations The array of all other given parameters for setting them in the exceptionMessage.
     */
    public function __construct(Array $_aExceptionDetails, ...$_aAdditionalInformations) {
        try{
            //call user-code for exception if there is any
            $oClass = new \ReflectionClass(get_called_class());
            $sExceptionArrayKey = array_search($_aExceptionDetails, $oClass->getConstants());
            if($sExceptionArrayKey !== false && method_exists(get_called_class(), $sExceptionArrayKey)){
                $sClassToCall = get_called_class();
                $sClassToCall::$sExceptionArrayKey();
            }
            
            //prepare transport objects to GreyLog2
            $oTransportObject = new \Gelf\Transport\UdpTransport(self::LogServerIp, 12201, \Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN);
            $this->publisher = new \Gelf\Publisher();
            $this->publisher->addTransport($oTransportObject);
            
            //validate exception details
            $this->validateExceptionDetails($_aExceptionDetails);
            //format/create the exception message
            $this->sExceptionMessage = $this->formatErrorMessage($_aAdditionalInformations);
            //create standard exception
            parent::__construct($this->sExceptionMessage, $this->iExceptionCode);
            //set additional exception details
            $this->sExceptionShortMessage = $this->getExceptionCode($_aExceptionDetails, get_called_class());
            $this->sExceptionClass = $this->getTrace()[0]['class'];
            $this->sExceptionFunction = $this->getTrace()[0]['function'];
            $this->sModuleName = get_called_class();
            //log exception
            $sFunctionToCall = $this->sExceptionLevel;
            $this->$sFunctionToCall();
        } catch (Exception $ex) {
            //use your own code in loggerPanic to do something when message cant be logged
            $this->loggerPanic($this->sExceptionShortMessage);
        }
    }
    /**
     * Checks the exceptionArray for valid contents and sets its values.
     * Sets a standard value if details are missing in array.
     * Sends a notice exception if there are missing details.
     * @param array $_aExceptionDetails The exceptionDetails in form: "[int ExceptionCode, lfException::LOGLEVEL, string ExceptionMessage]".
     */
    private function validateExceptionDetails(Array $_aExceptionDetails) {
        if (isset($_aExceptionDetails[0]) && is_numeric($_aExceptionDetails[0])) {
            $this->iExceptionCode = $_aExceptionDetails[0];
            if (isset($_aExceptionDetails[1]) && method_exists($this, $_aExceptionDetails[1])) {
                $this->sExceptionLevel = $_aExceptionDetails[1];
                if (isset($_aExceptionDetails[2]) && is_string($_aExceptionDetails[2])) {
                    $this->sExceptionMessage = $_aExceptionDetails[2];
                } else {
                    $this->sExceptionMessage = "__ERR_NOT_FOUND__";
                    $this->innerNotice("WrongConfig", "The exeption thrown, has no exceptionMessage.");
                }
            } else {
                $this->sExceptionLevel = self::ERROR;
                $this->innerNotice("WrongConfig", "The exeption thrown, has no or invalid exceptionLevel, using ERROR instead.");
            }
        } else {
            $this->iExceptionCode = 000000;
            $this->innerNotice("WrongConfig", "The exeption thrown, has no exceptionCode. Using '000000' instead.");
        }
    }
    /**
     * Formats the errorMessage and fills the variables with content if there are any. 
     * @param array $_aAdditionalInformations   An array containing all values for variables that has to be replaced in errorMessage.
     * @return string   The formated errorMessage.
     */
    public function formatErrorMessage(array $_aAdditionalInformations) {
        if (count($_aAdditionalInformations) > 0) {
            $sFormatedExceptionMessage = vsprintf($this->sExceptionMessage, $_aAdditionalInformations);
            $sFormatedExceptionMessage .= "." . PHP_EOL . "Call-Stack:" . PHP_EOL . $this->getTraceAsString();
        } else {
            $sFormatedExceptionMessage = $this->sExceptionMessage . "." . PHP_EOL . "Call-Stack:" . PHP_EOL . $this->getTraceAsString();
        }
        return $sFormatedExceptionMessage;
    }
    /**
     * Gets the number of variables that can be replaces by sprintf function in a string.
     * @param string $_sReplaceableString The string to search for replacable variables.
     * @return int Returns the number of valriables that can be relaced via sprintf.
     */
    public function getNumberOfVariables(string $_sReplaceableString) {
        if (preg_match_all("~%(?:(\d+)[$])?[-+]?(?:[ 0]|['].)?(?:[-]?\d+)?(?:[.]\d+)?[%bcdeEufFgGosxX]~", $_sReplaceableString, $iNumberOfVariables) > 0) {
            return count($iNumberOfVariables[1]);
        }
        return 0;
    }
    /**
     * Seaches the key of the given class constant value.
     * @param array $_aExceptionDetails The value of an exception constant.
     * @param string $_sExceptionClass The class the exception constant has to be searched.
     * @return mixed the key for <i>needle</i> if it is found in the array, <b>FALSE</b> otherwise.
     * @todo Could be cached with storing all exceptions in opCache
     */
    public function getExceptionCode($_aExceptionDetails, $_sExceptionClass) {
        $oClass = new \ReflectionClass($_sExceptionClass);
        return (array_search($_aExceptionDetails, $oClass->getConstants()));
    }
    /**
     * Sends an notice to GreyLog-Server before sending the actual exception.
     * @param string $sShortMessage The notice identifier.
     * @param string $sMessage  The message for the notice.
     */
    private function innerNotice(string $sShortMessage, string $sMessage) {
        $message = new \Gelf\Message();
        $message->setShortMessage($sShortMessage . "_" . $this->iExceptionCode . ":" . $this->sExceptionShortMessage)
                ->setLevel(self::NOTICE)
                ->setFullMessage($sMessage)
                ->setFacility(self::Application)
                ->setAdditional('ModuleName', $this->sModuleName)
                ->setAdditional('ExceptionClassName', $this->sExceptionClass)
                ->setAdditional('ExceptionFunctionName', $this->sExceptionFunction)
                ->setAdditional('ExceptionCode', $this->code)
                ->setAdditional('ExceptionFileName', $this->file)
                ->setAdditional('ExceptionLineNumber', $this->line);
        $this->publisher->publish($message);
    }
    /**
     * Sends an execption of type "ERROR" to configured GreyLog2-Server.
     */
    private function error() {
        $this->gelfMessage = new \Gelf\Message();
        $this->setShortMessage();
        $this->setMessage();
        $this->setLevel();
        $this->setFacility();
        $this->setAdditionals();
        $this->addParameters();
        //send message
        $this->publishMessage();
    }
    
    /**
     * Sets the ShortMessage and returns message object.
     * @return Gelf\Message Sets the ShortMessage and returns message object.
     */
    private function setShortMessage(){
        return $this->gelfMessage->setShortMessage($this->iExceptionCode . ":" . $this->sExceptionShortMessage);
    }
    
    /**
     * Sets the Message and returns message object.
     * @return Gelf\Message Sets the Message and returns message object.
     */
    private function setMessage(){
        return $this->gelfMessage->setFullMessage($this->sExceptionMessage);
    }
    
    /**
     * Sets the Level and returns message object.
     * @return Gelf\Message Sets the Level and returns message object.
     */
    private function setLevel(){
        return $this->gelfMessage->setLevel($this->sExceptionLevel);
    }
    
    /**
     * Sets the Facility and returns message object.
     * @return Gelf\Message Sets the Facility and returns message object.
     */
    private function setFacility(){
        return $this->gelfMessage->setFacility(self::Application);
    }
       
    /**
     * Sets additional details from exception object and returns message object.
     * @return Gelf\Message Sets the additional details from exception object and returns message object.
     */
    private function setAdditionals(){
        $this->gelfMessage->setAdditional('ModuleName', $this->sModuleName);
        $this->gelfMessage->setAdditional('ExceptionClassName', $this->sExceptionClass);
        $this->gelfMessage->setAdditional('ExceptionFunctionName', $this->sExceptionFunction);
        $this->gelfMessage->setAdditional('ExceptionCode', $this->code);
        $this->gelfMessage->setAdditional('ExceptionFileName', $this->file);
        $this->gelfMessage->setAdditional('ExceptionLineNumber', $this->line);
        return $this->gelfMessage;
    }
    
    /**
     * Checks if function/method parameters are equal to function declaration and logs warning if not.
     * Gets all names of function/method parameters and its values and adds it to message object as additional.
     * @return Gelf\Message Sets additional details to message object and returns the message object.
     */
    private function addParameters() {
        //get function/method parameters and parameters function was called
        $ref = new \ReflectionMethod($this->sExceptionClass, $this->sExceptionFunction);
        $aFunctionParameters = $ref->getParameters();
        $aFunctionParametersSetted = $this->getTrace()[0]['args'];
        //log notice if number of parameters does not match
        if (count($aFunctionParameters) < count($aFunctionParametersSetted)) {
            $this->innerNotice("MissUse", "Function was called with '" . count($aFunctionParametersSetted) . "' parameters, but defined on function is only '" . count($aFunctionParameters) . "'");
        }
        //add parameters and values to exception to log
        $i = 0;
        foreach ($aFunctionParametersSetted as $mParameterValue) {
            if (!isset($aFunctionParameters[$i])) {
                $this->gelfMessage->setAdditional("Param_UNKNOWN" . $i, serialize($mParameterValue));
            } else {
                $this->gelfMessage->setAdditional("Param_" . $aFunctionParameters[$i]->name, serialize($mParameterValue));
            }
            $i++;
        }
        return $this->gelfMessage;
    }
    /**
     * Publishes the message object to configured GreyLog2-Server instance.
     * @return Gelf\publisher The publisher object.
     */
    private function publishMessage(){
        $this->publisher->publish($this->gelfMessage);
    }
    
    private function loggerPanic(string $_sMessage){
        die("<html><head><title>Error</title></head><body><h1>Error!</h1><h3>Error while logging the error {$_sMessage}</h3><h4><style 'font-color:red'>Application stoped!</font></h4></body></html>");
    }
}