<?php
# SimpleXMLRPC for PHP4
# License : GPL
# Author  : Steven Apostolou
# Email   : account@dds.nl

# Version 0.7
require_once ('simpleXMLRPCMakeMessage.class.php');
require_once ('simpleXMLRPCReadMessage.class.php');




////
//! Class that serves as an XMLRPC server
// PRE  : 
// POST : 
class SimpleXMLRPCServer {

    var $rawXMLRPCMessage;
    var $simpleXMLRPCResponse;
    var $methods;

    var $requestArray;
    var $methodToExecute;
    var $response;
    var $xmlrpcResponseString;

    var $serverVersion;
    var $logging;




    ////
    //! Constructor
    // PRE  : 
    // POST : Class members are initialized
    function SimpleXMLRPCServer () {
        global $HTTP_RAW_POST_DATA;

        $this->serverVersion = '0.7';
        $this->logging       = false;

        if ($HTTP_RAW_POST_DATA == '') {
            print 'Nothing received.';
        } else {
            ob_start();
            if ($this->logging) {
                require_once ('simpleXMLRPCLoggingServer.class.php');
            }

            $this->methods = Array ();
            $this->rawXMLRPCMessage = $HTTP_RAW_POST_DATA;

            if ($this->logging) {
                $log = new SimpleXMLRPCLoggingServer ();
                $log->LogReceivedData ($this->rawXMLRPCMessage);
            }

            $this->GetSimpleXMLRPCServerClasses ();
            $this->MakeArrayOfRecievedData ();
            $this->CheckAccess ();
            $this->ExecuteXMLRPCRequest ();

            $this->GiveResponseBack ();

            if ($this->logging) {
                $log = new SimpleXMLRPCLoggingServer ();
                $log->LogResponseData ($this->xmlrpcResponseString);
            }

            $this->OuputMessage ();
        }
    }





    ////
    //! Function that checks if the client is allowed to connect
    // PRE  : 
    // POST : Access is checked
    function CheckAccess () {
        if ( isset ($this->methods[$this->methodToExecute]) ) {
            if ( isset ($this->methods[$this->methodToExecute]->simpleXMLRPCAllowedIP) ) {
                $boolAllowed = false;

                if ( is_array ($this->methods[$this->methodToExecute]->simpleXMLRPCAllowedIP) ) {
                    foreach ($this->methods[$this->methodToExecute]->simpleXMLRPCAllowedIP as $allowedIP) {
                        if ($allowedIP == $_SERVER["REMOTE_ADDR"]) {
                            $boolAllowed = true;
                        }
                    }
                } elseif ($this->methods[$this->methodToExecute]->simpleXMLRPCAllowedIP == $_SERVER["REMOTE_ADDR"]) {
                    $boolAllowed = true;
                }

                if (!$boolAllowed) {
                    $this->ResponseFaultCode (1);
                    exit();
                }
            }
        } else {
            $this->ResponseFaultCode (3);
            exit();
        }
    }





    ////
    //! Function that checks if the passed arguments are expected
    // PRE  : 
    // POST : 
    function CheckForValidParameters ($simpleXMLRPCMethodArguments) {
        $requestArray         = $this->requestArray;
        $argumentsCount       = count ($simpleXMLRPCMethodArguments);
        $countOfReceivedArray = count ($requestArray);

        if ($argumentsCount != $countOfReceivedArray) {
            $this->ResponseFaultCode (2);
            exit ();
        }

        for ($i = 0; $i < $argumentsCount; $i++) {
            switch ( gettype ($requestArray[$i]) ) {
                case 'array' :
                    $temp = each ($requestArray[$i]);

                    # Check if we're dealing with an 'normal' array or an associative array
                    # Because an array is actually a map in PHP all arrays are associative
                    # To make a distinction between a normally called 'normal' array and an
                    # associative array I check if all the keys are of type integer and are 
                    # increasing in value with 1. If not then it's assumed that we're dealing
                    # with an associative array
                    # THE 'NORMAL' ARRAY HAS TO BE FRESH (NOT MODIFIED) OTHERWISE THE INDICES
                    # MIGHT NOT BE INCREASING WITH 1
                    $is_hash = false;
                    $arrayKeys = array_keys ($requestArray[$i]);

                    for ($i = 0; $i < count ($arrayKeys); $i++ ) {
                        if ( gettype ($arrayKeys[$i]) == 'string' or $i != $arrayKeys[$i]) {
                            $is_hash = true;
                            break 1;
                        }
                    }
                    # ##################################################################### #

                    if ($is_hash) {
                        if ($simpleXMLRPCMethodArguments[$i] != 'struct')
                            $this->ResponseFaultCode (2);
                    } else {
                        if ($simpleXMLRPCMethodArguments[$i] != 'array')
                            $this->ResponseFaultCode (2);
                    }
                    break;
                default :
                    if ( !preg_match ('/^i4|int|double|boolean|datetime\.iso8601|base64|string$/i', $simpleXMLRPCMethodArguments[$i]) ) {
                        $this->ResponseFaultCode (2);
                    }
                    break;
            }
        }
    }





    ////
    //! Create a response
    // PRE  : 
    // POST : response is been given back
    function GiveResponseBack () {
        if ( is_array ($this->response) ) {
            $this->simpleXMLRPCResponse = new SimpleXMLRPCMakeMessage ();
            $this->simpleXMLRPCResponse->CreateXMLRPCResponseMessage ();

            foreach ($this->response as $param) {
                if ( gettype ($param) == 'array') {
                    $this->simpleXMLRPCResponse->AddArray ($param);
                } else {
                    $this->simpleXMLRPCResponse->AddValue ($param);
                }
            }

            $this->xmlrpcResponseString = $this->simpleXMLRPCResponse->GetMessageXML ();
        } elseif ( is_object ($this->response) ) {
            $this->response = $this->response->GetMessageXML ();
        }

        # Check for ouput that is not XMLRPC
        $size = ob_get_length();

        if ($size < 1) {
            ob_end_clean();
        } else {
            $this->ResponseFaultCode (10);
           exit();
        }
    }





    ////
    //! Execute the request
    // PRE  : 
    // POST : The request is been processed
    function ExecuteXMLRPCRequest () {
        if ( isset ($this->methods[$this->methodToExecute]) ) {
            if ( isset ($this->methods[$this->methodToExecute]->simpleXMLRPCMethodArguments) ) {
                $this->CheckForValidParameters ( $this->methods[$this->methodToExecute]->simpleXMLRPCMethodArguments );
            }
            $this->methods[$this->methodToExecute]->SimpleXMLRPCServerReceive  ($this->requestArray);
            $this->response = $this->methods[$this->methodToExecute]->SimpleXMLRPCServerResponse ();
        } else {
            $this->ResponseFaultCode (3);
            exit();
        }
    }





    ////
    //! Make an array of the received XMLRPC data
    // PRE  : 
    // POST : Array is made of XMLRPC
    function MakeArrayOfRecievedData () {
        $xml_parser = new simpleXMLRPCReadMessage ();
        $xml_parser->parse ($this->rawXMLRPCMessage);

        $this->methodToExecute = $xml_parser->responseArray[0]['methodName'];

        for ($i = 1; $i < count ($xml_parser->responseArray); $i++) {
            $this->requestArray[$i-1] = $xml_parser->responseArray[$i];
        }
    }





    ////
    //! Check what SimpleXMLRPC classes are defined
    // PRE  : 
    // POST : Classes are checked that are defined
    function GetSimpleXMLRPCServerClasses () {
        $methodInfo = Array ();
        $declaredClasses = get_declared_classes ();

        # Check what declared classes have SimpleXMLRPCServers functionality
        foreach ($declaredClasses as $class) {
            $classVariables = get_class_vars ($class);

            # Class becomes a SimpleXMLRPC class if the variable 'simpleXMLRPCMethodName' is defined in the class
            foreach (array_keys ($classVariables) as $classVariable) {

                if ($classVariable == 'simpleXMLRPCMethodName') {

                    if ( !method_exists ($class, 'SimpleXMLRPCServerReceive') )
                        trigger_error ("'SimpleXMLRPCServerReceive' class function was not found in 'SimpleXMLRPCClass \"$class\"'", E_USER_WARNING);

                    if ( !method_exists ($class, 'SimpleXMLRPCServerResponse') )
                        trigger_error ("'SimpleXMLRPCServerResponse' class function was not found in 'SimpleXMLRPCClass \"$class\"'", E_USER_WARNING);

                    $classInstance = new $class();

                    if ($classInstance->simpleXMLRPCMethodName != '') {
                        if ( isset ($methodInfo[ $classInstance->simpleXMLRPCMethodName ]) ) {
                            trigger_error ("Found two 'SimpleXMLRPCClasses' that have the same 'simpleXMLRPCMethodName : \"{$classInstance->simpleXMLRPCMethodName}\"'\nNow using class $class.", E_USER_WARNING);
                        }

                        $methodInfo[ $classInstance->simpleXMLRPCMethodName ] = $classInstance;
                    } else {
                        trigger_error ("Found a 'SimpleXMLRPCClass' that doesn't declare the MethodName of the XMLRPC call. (Class: '$class')", E_USER_WARNING);
                    }
                }
            }
        }

        $this->methods = &$methodInfo;
    }





    ////
    //! Give a XMLRPC as response
    // PRE  : 
    // POST : XMLRPC faultmessage is created and send
    function ResponseFaultCode ($faultCode) {
        $faultString[1] = "IP: `{$_SERVER['REMOTE_ADDR']}` not allowed for method '{$this->methodToExecute}'";

        if ( isset ($this->methods[$this->methodToExecute]->simpleXMLRPCMethodArguments) ) {
            $faultString[2] = "Incorrect arguments passed to method (Expecting ".count ($this->methods[$this->methodToExecute]->simpleXMLRPCMethodArguments)." arguments of type: '".join ("', '", $this->methods[$this->methodToExecute]->simpleXMLRPCMethodArguments)."')";
        }

        $faultString[3] = "Method '{$this->methodToExecute}' unknown";
        $faultString[10] = ob_get_contents ();
        ob_clean ();

        $faultString = utf8_encode ( htmlentities ($faultString[$faultCode]) );

        $this->xmlrpcResponseString = $this->GetXMLRPCFaultCode ($faultCode, $faultString);

        if ($this->logging) {
            $log = new SimpleXMLRPCLoggingServer ();
            $log->LogResponseData ($this->xmlrpcResponseString);
        }

        ob_end_clean();

        $this->OuputMessage ();
    }





    ////
    //! Get the XMLRPC fault XML
    // PRE  : 
    // POST : XMLRPC is returned
    function GetXMLRPCFaultCode ($faultCode, $faultString) {
        return $xmlrpcFaultResponse = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <methodResponse>
               <fault>
                  <value>
                     <struct>
                        <member>
                           <name>faultCode</name>
                           <value><int>$faultCode</int></value>
                           </member>
                        <member>
                           <name>faultString</name>
                           <value><string>$faultString</string></value>
                           </member>
                        </struct>
                     </value>
                  </fault>
               </methodResponse>
        ";
    }





    ////
    //! Output of the XMLPC that must be send back
    // PRE  : 
    // POST : Output of XMLRPC
    function OuputMessage () {
        header('Pragma: no-cache');
        header('Connection: close');
        header('Content-Type: text/xml; charset=UTF-8');
        header('XMLRPC-server: SimpleXMLRPC/'.$this->serverVersion);

        $xmlLength = strlen ($this->xmlrpcResponseString);
        header("Content-Length: $xmlLength");

        print $this->xmlrpcResponseString;
    }
}
?>