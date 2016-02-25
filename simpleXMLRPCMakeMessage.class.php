<?php
# SimpleXMLRPC for PHP4
# License : GPL
# Author  : Steven Apostolou
# Email   : account@dds.nl

# Version 0.7


////
//! Class that creates ab XMLRPC message
// PRE  : 
// POST : 
class SimpleXMLRPCMakeMessage {

    var $xmlrpcString;
    var $tagStack;




    ////
    //! Constructor
    // PRE  : 
    // POST : Class members are initialized
    function SimpleXMLRPCMakeMessage () {
        $this->xmlrpcString = '';
        $this->tagStack     = Array ();
    }





    ////
    //! Function to unshift array onto the tagStack
    // PRE  : 
    // POST : Tag is unshifted onto the tagStack
    function AddTag ($tagName, $tagCData = '') {
        $this->xmlrpcString .= "\n<$tagName>$tagCData";
        array_unshift ($this->tagStack, $tagName);
    }





    ////
    //! Function to shift array off the beginning of the tagStack
    // PRE  : 
    // POST : Tag is shifted off the tagstack
    function CloseTag () {
        $closeTag = array_shift ($this->tagStack);
        $this->xmlrpcString .= "</$closeTag>\n";
    }





    ////
    //! Function to unshift array on the tagStack
    // PRE  : The name of the tag and possible CDATA
    // POST : Array is unshifted on the tagStack
    function GetMessageXML () {
        foreach ($this->tagStack as $closeTag) {
            $this->CloseTag ();
        }

        return $this->xmlrpcString;
    }





    ////
    //! Function that 'creates' an XMLRPC message
    // PRE  : The methodName of the method that the XMLRPC message wants to call
    // POST : XMLRPC message header is set
    function CreateXMLRPCRequestMessage ($method) {
        if ( !preg_match ('/^[\w\.:\/]+$/', $method) ) {
            trigger_error ("Illegal values in the method name: '$method'.\n", E_USER_ERROR);
        }

        $methodName = utf8_encode($method);

        $this->xmlrpcString = '';

        $this->AddTag ('methodCall');
        $this->AddTag ('methodName', $methodName);
        $this->CloseTag ();
        $this->AddTag ('params');
    }





    ////
    //! Function that 'creates' an XMLRPC message
    // PRE  : The methodName of the method that the XMLRPC message wants to call
    // POST : XMLRPC message header is set
    function CreateXMLRPCResponseMessage () {
        $this->xmlrpcString = '';

        $this->AddTag ('methodResponse');
        $this->AddTag ('params');
    }





    ////
    //! Function to set an array in the XMLPRC message
    // PRE  : 
    // POST : Passed array is put in XMLRPC message
    function AddArray (&$array) {
        $this->AddParamNode ();
        $this->AddValueNode ();

        $this->_ProcessArray ($array);

        $this->CloseTag ();
        $this->CloseTag ();
    }





    ////
    //! Function to set an array in the XMLPRC message
    // PRE  : 
    // POST : Passed array is put in XMLRPC message
    function AddValue ($value, $type = '') {
        $this->AddParamNode ();
        $this->AddValueNode ();

        # In case no type is been given
        if ($type == '') {
            switch ( gettype ($value) ) {
                case 'boolean' : $type = 'boolean'; break;
                case 'integer' : $type = 'i4'     ; break;
                case 'double'  : $type = 'double' ; break;
                case 'string'  : $type = 'string' ; break;
                case 'base64'  : $type = 'base64' ; break;
            }
        }

        $this->AddSingleValueType ($value, $type);

        $this->CloseTag ();
        $this->CloseTag ();
    }





    ////
    //! Function that walks through an array to produce an XMLRPC call
    // PRE  : 
    // POST : XMLRPC call
    function _ProcessArray ($array, $new_array = 0, $is_sub = 0, $in_struct = 0) {
        if ( is_array ($array) ) {
            # Check if we're dealing with an 'normal' array or an associative array
            # Because an array is actually a map in PHP all arrays are associative
            # To make a distinction between a normally called 'normal' array and an
            # associative array I check if all the keys are of type integer and are 
            # increasing in value with 1. If not then it's assumed that we're dealing
            # with an associative array
            # THE 'NORMAL' ARRAY HAS TO BE FRESH (NOT MODIFIED) OTHERWISE THE INDICES
            # MIGHT NOT BE INCREASING WITH 1
            $is_hash = false;
            $arrayKeys = array_keys ($array);

            for ($i = 0; $i < count ($arrayKeys); $i++ ) {
                if ( gettype ($arrayKeys[$i]) == 'string' or $i != $arrayKeys[$i]) {
                    $is_hash = true;
                    break 1;
                }
            }
            # ##################################################################### #

            # Shift first element of array
            reset($array);
            $temp = each($array);

            # Got nothing back -> toedeledoki!
            if (!is_array($temp)) return 1;

            # Get the values of the current shift
            $key   = $temp['key'];
            $value = $temp['value'];

            # Delete the shift so we can go to the next record in the array
            unset ( $array[$temp['key']] );
        } else {
            return 1;
        }


        # Check if the array is an associative array
        if ($is_hash) {
            if ($is_sub) {
                if (!$new_array) {
                    # Add structure (within value node)
                    $this->AddStructNode ();
                    $this->AddStructMemberNode ($key);
                    $this->AddValueNode ();
                    $new_array = 1;

                    if ( gettype ($value) == 'array' ) {
                        $this->_ProcessArray ($value, 0, 1, 1);
                    } else {
                        $this->AddSingleValue ($value);
                        $this->CloseTag ();
                        $this->CloseTag ();
                    }

                    $this->_ProcessArray ($array, $new_array, $is_sub, 1);

                    if ($in_struct) {
                        $this->CloseTag ();
                    }

                    $this->CloseTag ();
                    $this->CloseTag ();
                } else {
                    # Add member mode in structure
                    $this->AddStructMemberNode ($key);
                    $this->AddValueNode ();

                    if ( gettype ($value) == 'array' ) {
                        $this->_ProcessArray ($value, 0, 1, 1);
                    } else {
                        $this->AddSingleValue ($value);
                        $this->CloseTag ();
                        $this->CloseTag ();
                    }

                    $this->_ProcessArray ($array, $new_array, $is_sub, 1);
                }
            } else {
                # Add new structure
                $this->AddStructNode ();
                $this->AddStructMemberNode ($key);
                $this->AddValueNode ();

                if ( gettype ($value) == 'array' ) {
                    $this->_ProcessArray ($value, 0, 1, 1);
                } else {
                    $this->AddSingleValue ($value);
                    $this->CloseTag ();
                    $this->CloseTag ();
                }

                $new_array = 1;
                $is_sub = 1;
                $this->_ProcessArray ($array, $new_array, $is_sub, 1);

                if ($in_struct) {
                    $this->CloseTag ();
                }

                $this->CloseTag ();
            }
        } else {
            # Check for the first element of the array
            if ($is_sub) {
                if (!$new_array) {
                    # Add new array (within value node)
                    $this->AddArrayNode ();
                    $this->AddValueNode ();
                    $new_array = 1;

                    if ( gettype ($value) == 'array' ) {
                        $this->_ProcessArray ($value, 0, 1, 0);
                    } else {
                        $this->AddSingleValue ($value);
                        $this->CloseTag (); # close tag value
                    }

                    $array = $this->RenewArray ($array);
                    $this->_ProcessArray ($array, $new_array, $is_sub, 0);

                    if ($in_struct) {
                        $this->CloseTag ();
                    }

                    $this->CloseTag ();
                    $this->CloseTag ();
                    $this->CloseTag ();
                } else {
                    $this->AddValueNode ();

                    # Add value node
                    if ( gettype ($value) == 'array' ) {
                        $this->_ProcessArray ($value, 0, 1, 0);
                    } else {
                        $this->AddSingleValue ($value);
                        $this->CloseTag ();
                    }

                    $array = $this->RenewArray ($array);
                    $this->_ProcessArray ($array, $new_array, $is_sub, 0);
                }
            } else {
                # Add new array (with value node)
                $this->AddArrayNode ();
                $this->AddValueNode ();

                if ( gettype ($value) == 'array' ) {
                    $this->_ProcessArray ($value, 0, 1, 0);
                } else {
                    $this->AddSingleValue ($value);
                    $this->CloseTag ();
                }

                $new_array = 1;
                $is_sub = 1;
                $array = $this->RenewArray ($array);
                $this->_ProcessArray ($array, $new_array, $is_sub, 0);

                if ($in_struct) {
                    $this->CloseTag ();
                }

                $this->CloseTag ();
                $this->CloseTag ();
            }
        }

        return 1;
    }





    ////
    //! Function to 'line-up' the records in an array
    // PRE  : 
    // POST : Array has nice order without 'a hole'
    function RenewArray ($array) {
            $tempArray = array ();
            foreach ($array as $value) {
                $tempArray[] = $value;
            }

            $array = $tempArray;
            $tempArray = NULL;

        return $array;
    }





    ////
    //! Function to add a param-tag
    // PRE  : 
    // POST : Param-tag is child of given $params_node
    function AddParamNode () {
        $this->AddTag ('param');
    }





    ////
    //! Function to add an array-tag
    // PRE  : 
    // POST : Array and data tag are added to the xml output string
    function AddArrayNode () {
        $this->AddTag ('array');
        $this->AddTag ('data');
    }





    ////
    //! Function to add a struct-tag
    // PRE  : 
    // POST : Struct tag is added to the xml output string
    function AddStructNode () {
        $this->AddTag ('struct');
    }





    ////
    //! Function to add a member-tag with given name
    // PRE  : 
    // POST : Member and name tag are added to the xml output string
    function AddStructMemberNode ($name) {
        $this->AddTag ('member');
        $this->AddTag ('name', $name);
        $this->CloseTag ();
    }





    ////
    //! Function to add a value-tag
    // PRE  : 
    // POST : Value tag is added to the xml output string
    function AddValueNode () {
        $this->AddTag ('value');
    }





    ////
    //! Function to add a 'singleValue' tag
    // PRE  : 
    // POST : 'singleValue' tag is added to the xml output string
    function AddSingleValue ($value) {
        switch ( gettype ($value) ) {
            case 'boolean' : 
                $this->AddTag ('boolean', ($value?'1':'0')); 
                $this->CloseTag (); # close tag boolean
            break;
            case 'integer' : 
                $this->AddTag ('int', (!$value?'0':$value)); 
                $this->CloseTag (); # close tag int
            break;
            case 'double'  : 
                $this->AddTag ('double', (!$value?'0':$value)); 
                $this->CloseTag (); # close tag double
            break;
            case 'string'  : 
                $this->AddTag ('string', utf8_encode ( htmlspecialchars ($value) )); 
                $this->CloseTag (); # close tag string
            break;
            default : 
                $this->AddTag ('string', utf8_encode ( htmlspecialchars ($value) )); 
                $this->CloseTag (); # close tag string
            break;
        }
    }





    ////
    //! Function to add a 'singleValue' tag
    // PRE  : 
    // POST : 'singleValue' tag is added to the xml output string
    function AddSingleValueType ($value, $type) {
        switch ($type) {
            case 'i4'               : $this->AddTag ('i4', (!$value?'0':$value)); $this->CloseTag (); break;
            case 'int'              : $this->AddTag ('int', (!$value?'0':$value)); $this->CloseTag (); break;
            case 'boolean'          : $this->AddTag ('boolean', ($value?'1':'0')); $this->CloseTag (); break;
            case 'double'           : $this->AddTag ('double', (!$value?'0':$value)); $this->CloseTag (); break;
            case 'datetime.iso8601' :
                if ( !preg_match ('/^\d{8}T\d{2}:\d{2}:\d{2}$/', $value) )
                    trigger_error ("DateTime value hasn't got the right format (YYYYMMDDTHH:MM:SS).\n", E_USER_ERROR);

                    $this->AddTag ('datetime.iso8601', utf8_encode ( htmlspecialchars ($value) ) ); $this->CloseTag ();
                break;
            case 'base64'           : $this->AddTag ('base64', base64_encode ($value)); $this->CloseTag (); break;
            default : $this->AddTag ('string', utf8_encode ( htmlspecialchars ($value) )); $this->CloseTag (); break;
        }
    }
}
?>