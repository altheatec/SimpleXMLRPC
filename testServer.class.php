<?php
# SimpleXMLRPC for PHP4
# License : GPL
# Author  : Steven Apostolou
# Email   : account@dds.nl

# Version 0.7


class Validator1 {

    public $simpleXMLRPCMethodName;
    public $simpleXMLRPCMethodArguments;
    public $simpleXMLRPCMethodDocumentation;



    ////
    //! Constructor
    // PRE  : 
    // POST : Class members are initialized
    function Validator1 () {
        # Setting the name of the method
        $this->simpleXMLRPCMethodName          = ' ';
        $this->simpleXMLRPCMethodArguments     = ' ';
        $this->simpleXMLRPCMethodDocumentation = ' ';
    }





    ////
    //! SimpleXMLRPCServerReceive is a function used to receive data from an XMLRPC request
    // PRE  : 
    // POST : 
    function SimpleXMLRPCServerReceive ($receivedDataArray) {
    }





    ////
    //! SimpleXMLRPCServerResponse is a function that returns a XMLRPC repsonse
    // PRE  : 
    // POST : XMLRPC response
    function SimpleXMLRPCServerResponse () {
    }
}





class ArrayOfStructsTest extends Validator1 {

    public $result;
    
    function ArrayOfStructsTest () {
        $this->result = 0;

        $this->simpleXMLRPCMethodName          = 'validator1.arrayOfStructsTest';
        $this->simpleXMLRPCMethodArguments     = Array ('array');
        $this->simpleXMLRPCMethodDocumentation = 'validator1.arrayOfStructsTest (array) returns number';
    }





    ////
    //! SimpleXMLRPCServerReceive is a function used to receive data from an XMLRPC request
    // PRE  : 
    // POST : 
    function SimpleXMLRPCServerReceive ($receivedDataArray) {
        $arrayOfStructs = $receivedDataArray[0];

        foreach ($arrayOfStructs as $struct) {
            $temp = each ($struct);

            if ($temp['key'] == 'curly') {
                $this->result += $temp['value'];
            }
        }
    }





    ////
    //! SimpleXMLRPCServerResponse is a function that returns a XMLRPC repsonse
    // PRE  : 
    // POST : XMLRPC response
    function SimpleXMLRPCServerResponse () {
        $returnArray = Array ();
        $returnArray[0] = $this->result;
        return $returnArray;
    }
}





class CountTheEntities extends Validator1 {

    public $returnStruct;
    
    function CountTheEntities () {
        $this->returnStruct['ctLeftAngleBrackets']  = 0;
        $this->returnStruct['ctRightAngleBrackets'] = 0;
        $this->returnStruct['ctAmpersands']         = 0;
        $this->returnStruct['ctApostrophes']        = 0;
        $this->returnStruct['ctQuotes']             = 0;

        $this->simpleXMLRPCMethodName          = 'validator1.countTheEntities';
        $this->simpleXMLRPCMethodArguments     = Array ('string');
        $this->simpleXMLRPCMethodDocumentation = 'validator1.countTheEntities (string) returns struct';
    }





    ////
    //! SimpleXMLRPCServerReceive is a function used to receive data from an XMLRPC request
    // PRE  : 
    // POST : 
    function SimpleXMLRPCServerReceive ($receivedDataArray) {
        $stringWithCharacters = $receivedDataArray[0];

        for ($i = 0; $i < strlen ($stringWithCharacters); $i++) {
            switch ($stringWithCharacters[$i]) {
                case '<' : $this->returnStruct['ctLeftAngleBrackets']++;  break;
                case '>' : $this->returnStruct['ctRightAngleBrackets']++; break;
                case '&' : $this->returnStruct['ctAmpersands']++;         break;
                case "'" : $this->returnStruct['ctApostrophes']++;        break;
                case '"' : $this->returnStruct['ctQuotes']++;             break;
            }
        }
    }





    ////
    //! SimpleXMLRPCServerResponse is a function that returns a XMLRPC repsonse
    // PRE  : 
    // POST : XMLRPC response
    function SimpleXMLRPCServerResponse () {
        $returnArray = Array ();
        $returnArray[0] = $this->returnStruct;
        return $returnArray;
    }
}





class EasyStructTest extends Validator1 {

    public $result;
    
    function EasyStructTest () {
        $this->result = 0;

        $this->simpleXMLRPCMethodName          = 'validator1.easyStructTest';
        $this->simpleXMLRPCMethodArguments     = Array ('struct');
        $this->simpleXMLRPCMethodDocumentation = 'validator1.easyStructTest (struct) returns number';
    }





    ////
    //! SimpleXMLRPCServerReceive is a function used to receive data from an XMLRPC request
    // PRE  : 
    // POST : 
    function SimpleXMLRPCServerReceive ($receivedDataArray) {
        $struct = $receivedDataArray[0];

        foreach ($struct as $name => $value) {
             switch ($name) {
                case 'moe'   : $this->result += $value; break;
                case 'larry' : $this->result += $value; break;
                case 'curly' : $this->result += $value; break;
             }
        }
    }





    ////
    //! SimpleXMLRPCServerResponse is a function that returns a XMLRPC repsonse
    // PRE  : 
    // POST : XMLRPC response
    function SimpleXMLRPCServerResponse () {
        $returnArray = Array ();
        $returnArray[0] = $this->result;
        return $returnArray;
    }
}





class EchoStructTest extends Validator1 {

    public $receivedStruct;
    
    function EchoStructTest () {
        $this->receivedStruct = NULL;

        $this->simpleXMLRPCMethodName          = 'validator1.echoStructTest';
        $this->simpleXMLRPCMethodArguments     = Array ('struct');
        $this->simpleXMLRPCMethodDocumentation = 'validator1.echoStructTest (struct) returns struct';
    }





    ////
    //! SimpleXMLRPCServerReceive is a function used to receive data from an XMLRPC request
    // PRE  : 
    // POST : 
    function SimpleXMLRPCServerReceive ($receivedDataArray) {
        $this->receivedStruct = $receivedDataArray[0];
    }





    ////
    //! SimpleXMLRPCServerResponse is a function that returns a XMLRPC repsonse
    // PRE  : 
    // POST : XMLRPC response
    function SimpleXMLRPCServerResponse () {
        $returnArray = Array ();
        $returnArray[0] = $this->receivedStruct;
        return $returnArray;
    }
}





class ManyTypesTest extends Validator1 {

    public $recievedValues;
    
    function ManyTypesTest () {
        $this->recievedValues = Array ();

        $this->simpleXMLRPCMethodName          = 'validator1.manyTypesTest';
        $this->simpleXMLRPCMethodArguments     = Array ('int', 'boolean', 'string', 'double', 'dateTime.iso8601', 'base64');
        $this->simpleXMLRPCMethodDocumentation = 'validator1.manyTypesTest (number, boolean, string, double, dateTime, base64) returns array';
    }





    ////
    //! SimpleXMLRPCServerReceive is a function used to receive data from an XMLRPC request
    // PRE  : 
    // POST : 
    function SimpleXMLRPCServerReceive ($receivedDataArray) {
        $this->recievedValues[] = $receivedDataArray[0];
        $this->recievedValues[] = $receivedDataArray[1];
        $this->recievedValues[] = $receivedDataArray[2];
        $this->recievedValues[] = $receivedDataArray[3];
        $this->recievedValues[] = $receivedDataArray[4];
        $this->recievedValues[] = $receivedDataArray[5];
    }





    ////
    //! SimpleXMLRPCServerResponse is a function that returns a XMLRPC repsonse
    // PRE  : 
    // POST : XMLRPC response
    function SimpleXMLRPCServerResponse () {
        $response = new SimpleXMLRPCMakeMessage ();
        $response->CreateXMLRPCResponseMessage ();

        $response->AddValue ($this->recievedValues[0], 'int');
        $response->AddValue ($this->recievedValues[1], 'boolean');
        $response->AddValue ($this->recievedValues[2], 'string');
        $response->AddValue ($this->recievedValues[3], 'double');
        $response->AddValue ($this->recievedValues[4], 'dateTime');
        $response->AddValue ($this->recievedValues[5], 'base64');
        
        return $response;
    }
}





class ModerateSizeArrayCheck extends Validator1 {

    public $returnString;
    
    function ModerateSizeArrayCheck () {
        $this->returnString = '';

        $this->simpleXMLRPCMethodName          = 'validator1.moderateSizeArrayCheck';
        $this->simpleXMLRPCMethodArguments     = Array ('array');
        $this->simpleXMLRPCMethodDocumentation = 'validator1.moderateSizeArrayCheck (array) returns string';
    }





    ////
    //! SimpleXMLRPCServerReceive is a function used to receive data from an XMLRPC request
    // PRE  : 
    // POST : 
    function SimpleXMLRPCServerReceive ($receivedDataArray) {
        $receivedArray = $receivedDataArray[0];

        foreach ($receivedArray as $string) {
            $this->returnString .= $string;
        }
    }





    ////
    //! SimpleXMLRPCServerResponse is a function that returns a XMLRPC repsonse
    // PRE  : 
    // POST : XMLRPC response
    function SimpleXMLRPCServerResponse () {
        $returnArray = Array ();
        $returnArray[0] = $this->returnString;
        return $returnArray;
    }
}





class NestedStructTest extends Validator1 {

    public $result;
    
    function NestedStructTest () {
        $this->returnString = '';

        $this->simpleXMLRPCMethodName          = 'validator1.nestedStructTest';
        $this->simpleXMLRPCMethodArguments     = Array ('struct');
        $this->simpleXMLRPCMethodDocumentation = 'validator1.nestedStructTest (struct) returns number';
    }





    ////
    //! SimpleXMLRPCServerReceive is a function used to receive data from an XMLRPC request
    // PRE  : 
    // POST : 
    function SimpleXMLRPCServerReceive ($receivedDataArray) {
        $receivedStruct = $receivedDataArray[0];

        $aprilFourth = $receivedStruct['2000']['04']['01'];
        $this->result = $aprilFourth['moe'] + $aprilFourth['larry'] + $aprilFourth['curly'];

    }





    ////
    //! SimpleXMLRPCServerResponse is a function that returns a XMLRPC repsonse
    // PRE  : 
    // POST : XMLRPC response
    function SimpleXMLRPCServerResponse () {
        $returnArray = Array ();
        $returnArray[0] = $this->result;
        return $returnArray;
    }
}





class SimpleStructReturnTest extends Validator1 {

    public $receivedNumber;
    
    function SimpleStructReturnTest () {
        $this->returnString = '';

        $this->simpleXMLRPCMethodName          = 'validator1.simpleStructReturnTest';
        $this->simpleXMLRPCMethodArguments     = Array ('int');
        $this->simpleXMLRPCMethodDocumentation = 'validator1.simpleStructReturnTest (number) returns struct';
    }





    ////
    //! SimpleXMLRPCServerReceive is a function used to receive data from an XMLRPC request
    // PRE  : 
    // POST : 
    function SimpleXMLRPCServerReceive ($receivedDataArray) {
        $this->receivedNumber = $receivedDataArray[0];
    }





    ////
    //! SimpleXMLRPCServerResponse is a function that returns a XMLRPC repsonse
    // PRE  : 
    // POST : XMLRPC response
    function SimpleXMLRPCServerResponse () {

        $returnArray = Array ();
        $returnArray[0]['times10']   = $this->receivedNumber * 10;
        $returnArray[0]['times100']  = $this->receivedNumber * 100;
        $returnArray[0]['times1000'] = $this->receivedNumber * 1000;
        return $returnArray;
    }
}
?>