<?php
# SimpleXMLRPC for PHP4
# License : GPL
# Author  : Steven Apostolou
# Email   : account@dds.nl

# Version 0.7


# Require the server class
require_once ('simpleXMLRPCServer.class.php');

# Require other SimpleXMLRPC classes
require_once ('testServer.class.php');



# Create a new instance of the SimpleXMLRPCServer
# The rest goes automagically -> It's a simple...
$simpleXMLRPCServer = new SimpleXMLRPCServer ();
?>