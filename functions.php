<?php
/**
 * functions.php
 * 
 * Helper functions
 *
 * @author Scott Ferguson <scott _ at _ toledocpu.com>
 */

require_once ('./config.php');

class actions {
    function htmlHeader() {
        $text = "Message Report for " . date("c");
        echo "<html><head><title>". $text ."</title></head><body><pre>\r\n";
        echo $text . "<br>\r\n";
    }
    function htmlFooter() {
        echo "</pre></body></html>\r\n";
    }
    function log($message, $phone_number = null, $txtMsg = null) {
        echo date("Y-m-d H:i:s") . " : " . $message . " " .
            $phone_number . " " . $txtMsg . $GLOBALS['nl'];
    }
}//end of actions class

class ch {
    function __construct($api, $endPoint = null) {
        global $apApiUrl, $ezApiUrl;
        if ($api === "ap") {
            $this->curlHandler = curl_init($apApiUrl . $endPoint);
            $this->apiType = "ap";
        }
        if ($api === "ez") {
            $this->curlHandler = curl_init($ezApiUrl . $endPoint);
            $this->apiType = "ez";
        }
    }

    function __destruct() {
        curl_close($this->curlHandler);
    }

    function setPostFields(Array $postFields = null) {
        if ($this->apiType === "ap") {
            global $apSiteId, $apApiKey;
            if (empty($postFields)) {
                $postFields = array("response_type" => "json");
            } else {
                $postFields["response_type"] = "json";
            }
            curl_setopt_array($this->curlHandler, array(
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_POST => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $apSiteId . ":" . $apApiKey,
            CURLOPT_POSTFIELDS => $postFields));
        }
        
        if ($this->apiType === "ez") {
            global $ezUser, $ezPasswd, $ezMessage_type;
            if (empty($postFields)) {
                $postFields = array(
                    "User" => $ezUser,
                    "Password" => $ezPasswd);
            } else {
                $postFields["User"] = $ezUser;
                $postFields["Password"] = $ezPasswd;
                $postFields["MessageType"] = $ezMessage_type;
            }
            curl_setopt_array($this->curlHandler, array(
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($postFields)));
        }
    }

    function sendRequest() {
        $this->result = curl_exec($this->curlHandler);
    }

    function getResult() {
        return $this->result;
    }
}//end of ch class

?>
