<?php
/**
 * config.php
 * 
 * Application settings
 *
 * @author Scott Ferguson <scott _ at _ toledocpu.com>
 */

//-----------------------------------------|
// appointment-plus.com API                |
//-----------------------------------------|
$apApiUrl = "https://ws.appointment-plus.com/";
$apSiteId = "appointplusXXX/XXX";
$apApiKey = "xXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxXxX";
$apC_id = "XXX"; // Appointment Plus location ID
/**
 *Variables starting with "allowed" define the behaviour of who receives a message.
 *Include bays in this list only if you want the customers scheduled in these
 *bays to receive messages.
 */
$apAllowedBays = array("Bay 1", "Bay 2");
/**
 *Canceled appointments will still show up in the list when pulled from the appointment
 *plus API; only include appt_status_description(s) that should be sent a message.
 */
$apAllowedStatusDescs = array("Scheduled", "Confirmed");


//-----------------------------------------|
// eztexting.com API                       |
//-----------------------------------------|
$ezApiUrl = "https://app.eztexting.com/";
$ezUser = "xXxXxXxXxX";
$ezPasswd = "xXxXxXxXxXxXxXxXx";
$ezMessage_type = 1; //1 = express delivery


//-----------------------------------------|
// Site vars                               |
//-----------------------------------------| 
//These variables are used to build the text message.
$greeting = "Hi ";
$baseMsg1 = ",\nThis is COMPANY NAME reminding you of your appointment for ";
$baseMsg2 = " on ";
$baseMsg3 = " at ";
$closer = "";

$timeZone = "America/New_York";
$theDay = date("Ymd", strtotime("-1 days")); //The day we are reminding people about.
$nl = " <br>\r\n"; //How a new line should be represented in the logging output

date_default_timezone_set($timeZone);
?>
