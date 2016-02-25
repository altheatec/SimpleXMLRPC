<?php
# SimpleXMLRPC for PHP4
# License : GPL
# Author  : Steven Apostolou
# Email   : account@dds.nl

# Version 0.7




////
//! Class that logs the simpleXMLRPC data trafic
// PRE  : 
// POST : 
class SimpleXMLRPCLoggingServer {

    var $logdir;
    var $logfile;

    var $logPeriod;

    var $dateTimeString; # string containing date/time info
    var $logDelimiter;
    var $remoteAddress;



    ////
    //! Constructor
    // PRE  : 
    // POST : Class members are initialized
    function SimpleXMLRPCLoggingServer () {
        # Fill in the directory where the logs are to be placed
        $this->logdir            = '';

        # Extra string to be placed at the end of the filename of the log
        $this->logFileNameExtra  = 'XMLRPC.log';
        
        # Period that a new log file will be made
        # Can be: day 
        #         week  (Starting at the first day of a week (with the first Sunday as the first day of the first week))
        #         month (Starting at the first day of a month)
        #         year  (Starting at the first day of a year)
        #         Default is month
        $this->logPeriod         = 'month';

        # In case you choose true for every server that requests a logfile is made
        # (The name/ip of the server is set in front of the log file)
        $this->logPerAddress     = true;

        # The delimeter that comes between every log
        $this->logDelimiter      = '--------------------------------------------------------------------';

        # date time string that's outputted with every log
        $this->dateTimeString    = date ("l dS of F Y H:i:s");

        # ...
        $this->remoteAddress     = $_SERVER["REMOTE_ADDR"];

        $this->SetFileName ();
    }





    ////
    //! Function to log the received data via POST
    // PRE  : 
    // POST : Class members are initialized
    function LogReceivedData ($data) {
        $prepend = $this->logDelimiter."\nRECEIVED on : ".$this->dateTimeString."\n\n";
        $data    = $prepend.$data."\n";

        $fp = fopen ($this->logfile, "a");
        if (!$fp) {
            print ("Unable to open or create file : {$this->logfile}");
        } else {
            fputs ($fp, $data);
            
            if (!fclose ($fp)) {
                print ("Unable to close file : {$this->logfile}");
            }
        }
    }





    ////
    //! Function to log the responsed data
    // PRE  : 
    // POST : Class members are initialized
    function LogResponseData ($data) {
        $prepend = "\nRESPONDED on : ".$this->dateTimeString."\n\n";
        $data    = $prepend.$data."\n".$this->logDelimiter;

        $fp = fopen ($this->logfile, "a");
        if (!$fp) {
            print ("Unable to open or create file : {$this->logfile}");
        } else {
            fputs ($fp, $data);
            
            if (!fclose ($fp)) {
                print ("Unable to close file : {$this->logfile}");
            }
        }
    }





    ////
    //! Function to log the responsed data
    // PRE  : 
    // POST : Class members are initialized
    function SetFileName () {
        $currentDate = time ();
        $filename = $this->logdir.'/SimpleXMLRPC_SERVER_';

        if ($this->logPerAddress) {
            if ($this->remoteAddress != '') {
                $hostname = gethostbyaddr ($this->remoteAddress);
                $underscoreName = preg_replace ('/\./', '_', $hostname);
                $filename .= $underscoreName."_";
            } else {
                $filename .= "unknown_";
            }
        }

        # Check per period-option what file to write to
        switch ($this->logPeriod) {
            case 'day' :
               $filename .= date ('Y_m_d_').$this->logFileNameExtra;
            break;
            case 'week' :
               $filename .= strftime ('%Y_week_%U_').$this->logFileNameExtra;
            break;
            case 'month' :
               $filename .= date ('Y_m_').$this->logFileNameExtra;
            break;
            case 'year' :
               $filename .= date ('Y').$this->logFileNameExtra;
            break;
            default:
               $filename .= date ('Y_m_').$this->logFileNameExtra;
            break;
        }

        $this->logfile = $filename;
    }
}
?>