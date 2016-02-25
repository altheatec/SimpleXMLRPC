<?php
# SimpleXMLRPC for PHP4
# License : GPL
# Author  : Steven Apostolou
# Email   : account@dds.nl

# Version 0.7


////
//! Class that reads out an XMLRPC message
// PRE  : 
// POST : 
class SimpleXMLRPCReadMessage {

    var $parser;
    var $responseArray;

    var $getCDATA;

    var $refStack;
    var $fillValue;

    var $substruct;
    var $boolBase64;
    var $cdata;
    var $xml;



    ////
    //! Constructor
    // PRE  : 
    // POST : Class members are initialized
    function SimpleXMLRPCReadMessage () {
        $this->responseArray = Array ();

        $this->refStack      = Array ( Array (NULL, 'singleData') );
        $this->fillValue     = NULL;
        $this->substruct     = 0;

        $this->boolBase64    = 0;
        $this->cdata         = '';

        $this->parser = xml_parser_create ();
        xml_set_object ($this->parser, $this);
    	xml_parser_set_option ($this->parser, XML_OPTION_CASE_FOLDING, true);
    	xml_parser_set_option ($this->parser, XML_OPTION_SKIP_WHITE, true);
        xml_set_element_handler ($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler ($this->parser, "cdata");
    }





    ////
    //! Function that parses the given XMLRPC message
    // PRE  : 
    // POST : 
    function parse ($data) { 
        xml_parse ($this->parser, $data);
    }





    ////
    //! Function that handles the begin (open) tag when parsing XML
    // PRE  : 
    // POST : 
    function tag_open ($parser, $tag, $attributes) { 
        switch ($tag) {
            case 'METHODNAME':
                $ref = &$this->responseArray;
                $this->fillValue = &$ref[]['methodName'];

                # Unshift reference on the refStack
                Array_unshift ($this->refStack, Array (&$ref, 'methodName'));
                $this->getCDATA = true;
                break;
            case 'VALUE' :
                if ($this->refStack[0][1] == 'singleData') {
                    # Unshift reference on the refStack
                    Array_unshift ($this->refStack, Array (&$this->responseArray, 'singleData'));
                }
                break;
            case 'STRUCT' :
                # Get first element from the refStack
                $refTopOfStack = &$this->refStack[0];

                $keys = NULL;
                # Check if the previous tag was a struct
                if ( is_array ($refTopOfStack[0]) ) {
                    $keys = array_keys ($refTopOfStack[0]);

                    # Check if we're dealing with an 'normal' array or an associative array
                    # Because an array is actually a map in PHP all arrays are associative
                    # To make a distinction between a normally called 'normal' array and an
                    # associative array I check if all the keys are of type integer and are 
                    # increasing in value with 1. If not then it's assumed that we're dealing
                    # with an associative array
                    # THE 'NORMAL' ARRAY HAS TO BE FRESH (NOT MODIFIED) OTHERWISE THE INDICES
                    # MIGHT NOT BE INCREASING WITH 1
                    $is_hash = false;

                    for ($i = 0; $i < count ($keys); $i++ ) {
                        if ( gettype ($keys[$i]) == 'string' or $i != $keys[$i]) {
                            $is_hash = true;
                            break 1;
                        }
                    }
                    # ##################################################################### #
                }

                # Create a new reference that's referencing to a new made array
                # if we didn't get any keys back
                # In case the previous tag was an array we also have to set the
                # reference to a new made array
                # In case the previous tag was a struct the reference has to be
                # refering to the key of the struct and not to a new array
                if ( count($keys) == 0)
                    $ref = &$refTopOfStack[0][];
                elseif (!$is_hash)
                    $ref = &$refTopOfStack[0][];
                else
                    $ref = &$refTopOfStack[0][$keys[count($keys) - 1]];

                # Unshift the new reference on the stack
                Array_unshift ($this->refStack, Array (&$ref, 'array'));

                $this->substruct++;
                break;
            case 'MEMBER' :
                # Bovenste Array van de stack halen
                $refTopOfStack = &$this->refStack[0];

                # De nieuwe reference die we gaat gebruiken bij het verwerken van de struct
                # verwijst naar de ref die is aangemaakt bij de value-tag
                $ref = &$refTopOfStack[0];

                # Unshift de nieuwe reference op de stack zodat ie wordt gebruikt
                Array_unshift ($this->refStack, Array (&$ref, 'structKey'));
                break;
            case 'ARRAY' :
                # Get the reference of the value tag of the stack
                $refTopOfStack = &$this->refStack[0];

                $keys = NULL;
                # Check if the previous tag was a struct
                if ( is_array ($refTopOfStack[0]) )
                    $keys = array_keys ($refTopOfStack[0]);

                # Create a new reference that's referencing to a new made array
                # if we didn't get any keys back
                # In case the previous tag was an array we also have to set the
                # reference to a new made array
                # In case the previous tag was a struct the reference has to be
                # refering to the key of the struct and not to a new array
                if ( count($keys) == 0)
                    $ref = &$refTopOfStack[0][];
                elseif ( gettype ($keys[ count ($keys) - 1]) == 'integer' )
                    $ref = &$refTopOfStack[0][];
                else
                    $ref = &$refTopOfStack[0][$keys[count($keys) - 1]];

                # Unshift the new reference on the stack
                Array_unshift ($this->refStack, Array (&$ref, 'array'));
                break;
            case 'NAME' :
                $this->getCDATA = true;
                break;
            case 'I4':
            case 'INT':
            case 'BOOLEAN':
            case 'STRING':
            case 'DOUBLE':
            case 'DATETIME.ISO8601':
                $this->getCDATA = true;
                break;
            case 'BASE64':
                $this->boolBase64 = 1;
                $this->getCDATA = true;
                break;
        }
        
        if ($tag == 'value');
    }





    ////
    //! Function that sets the received CDATA
    // PRE  : 
    // POST : 
    function setCDATA ($cdata) {
        $refArray = &$this->refStack[0];
        switch ($refArray[1]) {
            case 'singleData'  :
            case 'array'       :
                if ($this->boolBase64) {
                    $refArray[0][] = base64_decode ($cdata);
                    $this->boolBase64 = 0;
                } else {
                    $refArray[0][] = utf8_decode ($cdata);
                }
                break;
            case 'methodName'  :
                $this->fillValue = utf8_decode ($cdata);
                break;
            case 'structKey'   :
                $this->fillValue = &$refArray[0][$cdata];
                $refArray[1] = 'structValue';
                break;
            case 'structValue' :
                $this->fillValue = utf8_decode ($cdata);
                $refArray[1] = 'structKey';
                break;
        }
    }





    ////
    //! Function that handles the cdata when parsing XML
    // PRE  : 
    // POST : 
    function cdata ($parser, $cdata) {
        if ($this->getCDATA) {
            $this->cdata .= $cdata;
        }
    }





    ////
    //! Function that handles the end tag when parsing XML
    // PRE  : 
    // POST : 
    function tag_close ($parser, $tag) {
        $this->cdata != '' ? $this->setCDATA ($this->cdata) : FALSE;
        $this->cdata = '';
        $this->getCDATA = false;

        switch ($tag) {
            case 'VALUE' :
                if ($this->refStack[0][1] == 'singleData') {
                    array_shift ($this->refStack);
                }
                break;
            case 'STRUCT' :
                $this->substruct--;
                array_shift ($this->refStack);
                break;
            case 'MEMBER' :
                array_shift ($this->refStack);
                break;
            case 'ARRAY' :
                array_shift ($this->refStack);
                break;
            case 'METHODNAME' :
                array_shift ($this->refStack);
                break;
        }
    }
}

?>