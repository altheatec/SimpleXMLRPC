<?php
# SimpleXMLRPC for PHP4
# License : GPL
# Author  : Steven Apostolou
# Email   : account@dds.nl

# Version 0.7


require_once ('simpleXMLRPCMakeMessage.class.php');
require_once ('simpleXMLRPCReadMessage.class.php');



////
//! Class that serves as an XMLRPC client
// PRE  : 
// POST : 
class SimpleXMLRPCClient {

    var $server2Send2;
    var $xmlrpcMessage;

    var $debug;

    var $logging;



    ////
    //! Constructor
    // PRE  : 
    // POST : Class members are initialized
    function SimpleXMLRPCClient () {
        $this->debug         = 0;
        $this->logging       = false;

        $this->server2Send2  = Array ();
        $this->xmlrpcMessage = new SimpleXMLRPCMakeMessage ();
    }





    ////
    //! Function to set the XMLRPC server to send the message to
    // PRE  : 
    // POST : 
    function SetXMLRPCServer ($hostname, $path, $port = 0, $boolHttps = 0) {
        $this->server2Send2 = array ('hostname' => $hostname,
                                     'path'     => $path,
                                     'port'     => $port,
                                     'https'    => $boolHttps);
    }





    ////
    //! Function that 'creates' an XMLRPC message
    // PRE  : The methodName of the method that the XMLRPC message wants to call
    // POST : XMLRPC message header is set
    function CreateXMLRPCMessage ($method) {
        $this->xmlrpcMessage->CreateXMLRPCRequestMessage ($method);
    }





    ////
    //! Function that sets the method that the XMLRPC call wants to call
    // PRE  : the name of the method to be set 
    // POST : member variable methodName is set
    function AddArray ($array) {
        $this->xmlrpcMessage->AddArray ($array);
    }





    ////
    //! Function that sets the method that the XMLRPC call wants to call
    // PRE  : the name of the method to be set 
    // POST : member variable methodName is set
    function AddValue ($value, $type = '') {
        $this->xmlrpcMessage->AddValue ($value, $type);
    }





    ////
    //! Function that returns the RAW XML of the XMLRPC message
    // PRE  : 
    // POST : returns a string with the XML message
    function ShowRawXMLRPCMessage () {
        return $this->xmlrpcMessage->GetMessageXML ();
    }





    ////
    //! Function to send the XMLRPC message
    // PRE  : 
    // POST : Return a hash with the response values
    function SendXMLRPC () {
        if (count ($this->server2Send2) == 0) {
            print "There isn't specified what server to send to.\n";
            exit();
        }

        # Create URL to send to
        $this->server2Send2['https'] ? $url = 'https://' : $url = 'http://';

        $url .= $this->server2Send2['hostname'];
        $this->server2Send2['port'] > 0 ? $url .= ':'.$this->server2Send2['port'] : FALSE;
        $url .= $this->server2Send2['path'];

        $this->debug ? print $url."\n" : FALSE;

        $ch = curl_init();

        # Remove/Replace headers that we don't want
        # Standard CURL sets the Content-type to 'application/x-www-form-urlencoded' when a Post request is done
        # We don't want that so we set it to 'text/xml'.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, Array ("Content-Type: text/xml; charset=UTF-8", "Accept: ", "User-Agent: SimpleXMLRPC/0.1"));

        # Show verbose (yes/no)
        $this->debug ? curl_setopt ($ch, CURLOPT_VERBOSE, 1) : FALSE;
        curl_setopt ($ch, CURLOPT_URL, $url);
        # Also give back the headers
        $this->debug == 2 ? curl_setopt ($ch, CURLOPT_HEADER, 1) : FALSE;
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_POST, 1);
        $xml = &$this->xmlrpcMessage->GetMessageXML ();
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $xml);

        $this->debug ? print "\n\n^^^^^^\n$xml\n^^^^^^\n\n" : FALSE;

        if ($this->logging) {
            require_once ('simpleXMLRPCLoggingClient.class.php');

            $log = new SimpleXMLRPCLoggingClient ();
            $log->SetServerURL ($url);
            $log->LogSendData ($xml);
        }

        $result = curl_exec ($ch);

        $curlError = &curl_error ($ch);
        if ($curlError == '') {
            $httpCode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
            
            if ( $httpCode == 200) {
                if ($result == '') {
                    $returnValue[0] = Array (
                        'faultCode'   => 4,
                        'faultString' => "No data received from server."
                    );

                    if ($this->logging) {
                        $log->LogReceivedData ("faultCode   : ".$returnValue[0]['faultCode']."\nfaultString : ".$returnValue[0]['faultString']);
                    }
                } else {
                    if ($this->logging) {
                        $log->LogReceivedData ($result);
                    }

                    $xml_parser = new simpleXMLRPCReadMessage ();
                    $xml_parser->parse ($result);

                    $returnValue = $xml_parser->responseArray;
                }
            } else {
                $description = $this->GetHTTPCodeDescription ($httpCode);
                $returnValue[0] = Array (
                    'faultCode'   => 5,
                    'faultString' => "Didn't receive 200 OK from remote server. (Received '$httpCode $description' on `$url`)"
                );

                if ($this->logging) {
                    $log->LogReceivedData ("faultCode   : ".$returnValue[0]['faultCode']."\nfaultString : ".$returnValue[0]['faultString']);
                }
            }
        } else {
            $returnValue[0] = Array (
                'faultCode'   => 6,
                'faultString' => "CURL error. ('$curlError' on `$url`)"
            );

            if ($this->logging) {
                $log->LogReceivedData ("faultCode   : ".$returnValue[0]['faultCode']."\nfaultString : ".$returnValue[0]['faultString']);
            }
        }

        curl_close ($ch);

        return $returnValue;
    }





    ////
    //! Function to return the description of an HTTP code
    // PRE  : the HTTP code
    // POST : The description is returned
    function GetHTTPCodeDescription ($httpCode) {
        $httpCodeDescription = Array (
            '100' => 'Continue',
            '101' => 'Switching Protocols',
            '200' => 'OK',
            '201' => 'Created',
            '202' => 'Accepted',
            '203' => 'Non-Authoritative Information',
            '204' => 'No Content',
            '205' => 'Reset Content',
            '206' => 'Partial Content',
            '300' => 'Multiple Choices',
            '301' => 'Moved Permanently',
            '302' => 'Moved Temporarily',
            '303' => 'See Other',
            '304' => 'Not Modified',
            '305' => 'Use Proxy',
            '400' => 'Bad Request',
            '401' => 'Unauthorized',
            '402' => 'Payment Required',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '405' => 'Method Not Allowed',
            '406' => 'Not Acceptable',
            '407' => 'Proxy Authentication Required',
            '408' => 'Request Time-out',
            '409' => 'Conflict',
            '410' => 'Gone',
            '411' => 'Length Required',
            '412' => 'Precondition Failed',
            '413' => 'Request Entity Too Large',
            '414' => 'Request-URI Too Large',
            '415' => 'Unsupported Media Type',
            '500' => 'Internal Server Error',
            '501' => 'Not Implemented',
            '502' => 'Bad Gateway',
            '503' => 'Service Unavailable',
            '504' => 'Gateway Time-out',
            '505' => 'HTTP Version not supported'
        );

        return $httpCodeDescription[$httpCode];
    }
}

?>