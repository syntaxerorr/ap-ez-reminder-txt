<?php
/**
 * sendReminderTexts.php
 *
 * Send a text message to remind people of their appointments
 *
 * @author Scott Ferguson <scott _ at _ toledocpu.com>
 */

require_once ("./config.php");
require_once ("./functions.php");


$do = new actions();
$do->htmlHeader();

$do->log("START retrieve info from appointment-plus");
//Retrive appointment details for the day
$getAppointments = new ch("ap", "Appointments/GetAppointments");
$getAppointments->setPostFields(array(
    "c_id" => $apC_id,
    "date" => $theDay));
$getAppointments->sendRequest();
$appointments = json_decode($getAppointments->getResult());
unset($getAppointments);

//Remove from array if appointment status isn't what we want
$i = 0;
foreach ($appointments->data as $data) {
    if (!in_array($data->appt_status_description, $apAllowedStatusDescs)) {
        unset($appointments->data[$i]);
    }
    $i = $i + 1;
}
$appointments->data = array_values($appointments->data);

//Remove from array if bay name isn't what we want
$i = 0;
foreach ($appointments->data as $data) {
    if (!in_array($data->staff_screen_name, $apAllowedBays)) {
        unset($appointments->data[$i]);
    }
    $i = $i + 1;
}
$appointments->data = array_values($appointments->data);

//Collect customer IDs
$customersForDay = array();
foreach ($appointments->data as $data) {
    $customersForDay[] = $data->customer_id;
}
$customersForDay = array_unique($customersForDay);
$customerList = implode(',', $customersForDay);

//Retrieve customers phone number
$getCustomerInfo = new ch("ap", "Customers/GetCustomers");
$getCustomerInfo->setPostFields(array(
    "c_id" => $apC_id,
    "customer" => $customerList));
$getCustomerInfo->sendRequest();
$customerInfo = json_decode($getCustomerInfo->getResult());
unset($getCustomerInfo);

//Add phone numbers to appointment details
foreach ($appointments->data as $aData) {
    foreach ($customerInfo->data as $cData) {
        if ($aData->customer_id === $cData->customer_id) {
            $aData->day_phone = $cData->day_phone;
        }
    }
}

//Collect service IDs
$serviceIds = array();
foreach ($appointments->data as $data) {
    $serviceIds[] = $data->service_id;
}
$serviceIds = array_unique($serviceIds);
$serviceList = implode(',', $serviceIds);

//Retrieve service details
$getServiceInfo = new ch("ap", "Services/GetServices");
$getServiceInfo->setPostFields(array(
    "c_id" => $apC_id,
    "service" => $serviceList));
$getServiceInfo->sendRequest();
$serviceInfo = json_decode($getServiceInfo->getResult());
unset($getServiceInfo);

//Add service description to appointment details
foreach ($appointments->data as $aData) {
    foreach ($serviceInfo->data as $sData) {
        if ($aData->service_id === $sData->service_id) {
            $aData->service_description = $sData->description;
        }
    }
}
$do->log("END retrieve info from appointment-plus");

//Build text messages to be sent
$textMsgs = array();
foreach ($appointments->data as $data) {
    $firstName = ucfirst(strtolower($data->first_name));
    $service = strtolower($data->service_description);
    $unixTime = date("U", strtotime($data->date)) + ($data->start_time * 60);
    $date = date("D M jS", $unixTime);
    $startTime = date("g:i A", $unixTime);
    $day_phone = preg_replace('/\D+/', '', $data->day_phone);
    $textMsgs[] = array("day_phone" => $day_phone,
    "msg" => $greeting . $firstName . $baseMsg1 . $service . $baseMsg2 .
        $date . $baseMsg3 . $startTime . $closer);
}

//Send each message separately
foreach ($textMsgs as $textMsg) {
    //Check if phone number is valid
    $phone_number = trim($textMsg['day_phone']);
    if (empty($phone_number)) {
        $do->log("Phone number cannot be blank", $phone_number);
        continue;
    }
    if (strlen($phone_number) != 10) {
        $do->log("Invalid phone number:", $phone_number);
        continue;
    }
    if (!is_numeric($phone_number)) {
        $do->log("Invalid phone number:", $phone_number);
        continue;
    }

    //Check if message body is not blank and doesn't exceed allowable limit
    $message = trim($textMsg['msg']);
    if (empty($message)) {
        $do->log("Message cannot be blank:", $phone_number);
        continue;
    }
    $strlen_message = strlen($message);
    if ($ezMessage_type == 1 && ($strlen_message > 160)) {
        $do->log("Message length should not exceed 160 characters",
            $phone_number, $message);
        continue;
    } elseif ($ezMessage_type == 2 && ($strlen_message > 130)) {
        $do->log("Message length should not exceed 130 characters",
            $phone_number, $message);
        continue;
    }
    
    //Prepare data for sending
    $sendTxtMsg = new ch("ez", "sending/messages?format=json");
    $sendTxtMsg->setPostFields(array(
        "PhoneNumbers"  => array($phone_number),
        "Message"       => $message,
        "MessageTypeID" => $ezMessage_type));
    
    //Send message
    $sendTxtMsg->sendRequest();
    $txtMsg = json_decode($sendTxtMsg->getResult());
    unset($sendTxtMsg);
    switch ($txtMsg->Response->Code) {
        case 201:
            $do->log("Message Sent for", $phone_number);
            break;
        case 401:
            $do->log("Invalid user or password for", $phone_number);
            break;
        case 403:
            $errors = $txtMsg->Response->Errors;
            $do->log("The following errors occurred: " . implode('; ', $errors), $phone_number);
            break;
        case 500:
            $do->log("Service Temporarily Unavailable for", $phone_number);
            break;
        default:
            $do->log("Unknown error for", $phone_number);
    }
}
//Uncomment the next lines for some debugging info
//var_dump($textMsgs);
//var_dump($appointments);

$do->htmlFooter();

?>
