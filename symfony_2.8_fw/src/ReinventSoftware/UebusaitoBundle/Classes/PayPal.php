<?php
namespace ReinventSoftware\UebusaitoBundle\Classes;

class PayPal {
    // Vars
    private $debug;
    private $sandbox;
    
    private $elements;
    
    // Properties
    public function getElements() {
        return $this->elements;
    }
    
    // Functions public
    public function __construct($debug, $sandbox) {
        $this->debug = $debug;
        $this->sandbox = $sandbox;
        
        $this->elements = Array();
    }
    
    public function ipn() {
        $content = file_get_contents("php://input");
        $contentExplode = explode("&", $content);
        
        foreach ($contentExplode as $value) {
            $valueExplode = explode("=", $value);

            if (count($valueExplode) == 2)
                $this->elements[$valueExplode[0]] = urldecode($valueExplode[1]);
        }
        
        $postFields = "cmd=_notify-validate";
        
        if (function_exists("get_magic_quotes_gpc") == true)
            $getMagicQuotesExists = true;
        
        foreach ($this->elements as $key => $value) {
            if ($getMagicQuotesExists == true && get_magic_quotes_gpc() == 1)
                $value = urlencode(stripslashes($value));
            else
                $value = urlencode($value);
            
            $postFields .= "&$key=$value";
        }
        
        if ($this->sandbox == true)
            $payPalUrl = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        else
            $payPalUrl = "https://www.paypal.com/cgi-bin/webscr";
        
        $curl = curl_init($payPalUrl);
        
        if ($curl == FALSE)
            return false;
        
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        
        if($this->debug == true) {
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
        }
        
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTPHEADER, Array('Connection: Close'));
        
        $curlResponse = curl_exec($curl);
        
        if (curl_errno($curl) != 0) {
            if($this->debug == true)
                error_log(date("[Y-m-d H:i e] ") . "Can't connect to PayPal to validate IPN message: " . curl_error($curl) . PHP_EOL);
            
            curl_close($curl);
            
            exit;
        }
        else {
            if ($this->debug == true) {
                error_log(date("[Y-m-d H:i e] ") . "HTTP request of validation request: " . curl_getinfo($curl, CURLINFO_HEADER_OUT) . " for IPN payload: $postFields " . PHP_EOL);
                error_log(date("[Y-m-d H:i e] ") . "HTTP response of validation request: $curlResponse " . PHP_EOL);
            }
            
            curl_close($curl);
        }
        
        $tokens = explode("\r\n\r\n", trim($curlResponse));
        $curlResponse = trim(end($tokens));
        
        if (strcmp($curlResponse, "VERIFIED") == 0) {
            if ($this->debug == true)
                error_log(date("[Y-m-d H:i e] ") . "Verified IPN: $postFields " . PHP_EOL);
            
            return true;
        }
        else if (strcmp ($curlResponse, "INVALID") == 0) {
            if($this->debug == true)
                error_log(date("[Y-m-d H:i e] ") . "Invalid IPN: $postFields " . PHP_EOL);
            
            return false;
        }
        
        return false;
    }

    // Functions private
}