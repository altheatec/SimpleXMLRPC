<?php
# SimpleXMLRPC for PHP4
# License : GPL
# Author  : Steven Apostolou
# Email   : account@dds.nl

# Version 0.7


require_once ('simpleXMLRPCClient.class.php');


$server_path = '/index.php';
$server_hostname = 'localhost';
$sendHTTPS = 0;
$serverPort = 8000;


$xmlrpc = new SimpleXMLRPCClient ();
$xmlrpc->debug = 1;
$xmlrpc->SetXMLRPCServer ($server_hostname, $server_path, $serverPort, $sendHTTPS);


/* UNCOMMENT to test desired code to test SimpleXMLRPC */


/*
$arrayOfStructs = Array ( Array ('curly' => 3),
                          Array ('curly' => 2)
                        );
$xmlrpc->CreateXMLRPCMessage ('validator1.arrayOfStructsTest');
$xmlrpc->AddArray ($arrayOfStructs);
*/


/*
$inputString = "<J<&>>'&\"\"''<>";
$xmlrpc->CreateXMLRPCMessage ('validator1.countTheEntities');
$xmlrpc->AddValue ($inputString);
*/


/*
$struct = Array (
            'moe'   => 2,
            'larry' => 3,
            'curly' => 1
          );
$xmlrpc->CreateXMLRPCMessage ('validator1.easyStructTest');
$xmlrpc->AddArray ($struct);
*/


/*
$struct = Array (
            'moe'   => 2,
            'larry' => 3,
            'curly' => 1
          );
$xmlrpc->CreateXMLRPCMessage ('validator1.echoStructTest');
$xmlrpc->AddArray ($struct);
*/


/*
$xmlrpc->CreateXMLRPCMessage ('validator1.manyTypesTest');
$xmlrpc->AddValue (3, 'int');
$xmlrpc->AddValue (true, 'boolean');
$xmlrpc->AddValue ('hello', 'string');
$xmlrpc->AddValue (1.23, 'double');
$xmlrpc->AddValue ('19981212T14:08:55', 'datetime.iso8601');
$xmlrpc->AddValue ('base64 encoded', 'base64');
*/


/*
$array = Array ();
$totalStrings = rand (100,200);

print $totalStrings;
for ($i = 0; $i < $totalStrings; $i++) {
    $string = "$i-";
    array_push ($array, $string);
}
print count($array);
$xmlrpc->CreateXMLRPCMessage ('validator1.moderateSizeArrayCheck');
$xmlrpc->AddArray ($array);
*/


/*
$struct = Array ('2000' => 
                Array ("04" => 
                    Array ("01" => 
                        Array (
                            'moe'   => 2,
                            'larry' => 3,
                            'curly' => 1
                        )
                    )
                )
          );
$xmlrpc->CreateXMLRPCMessage ('validator1.nestedStructTest');
$xmlrpc->AddArray ($struct);
*/


/*
$number = 3;
$xmlrpc->CreateXMLRPCMessage ('validator1.simpleStructReturnTest');
$xmlrpc->AddValue ($number);
*/


$array = $xmlrpc->SendXMLRPC ();

# print out the returned array
print_r($array);

?>