# ap-ez-reminder-txt
Uses data from Appointment Plus's API to send text messages through eztexting's API

Appointment-plus.com's online scheduling service requires you to ask the customer for their cell phone provider before you can send them text messages about their appointment. This PHP script will grab all the customers for the next calendar day and send them a reminder text message without knowing their provider through the eztexting.com API.

To use you only need to edit config.php following the instructions below:

For appointment plus you need to obtain your SiteId, ApiKey, and know your c_id (your location ID). There are 2 other variables you need to configure. $apAllowedBays are the names of the bays you’d like to receive a message. $apAllowedStatusDescs are the names of the appointment status descriptions that you’d like to receive a message. In the default configuration canceled appointments are not sent messages. You can create a new appointment status description on appointment-plus’s website named Do not text. If you do not include “Do not text” in $apAllowedStatusDescs they will not receive a message.

For eztexting you need to know your user name and password.

set $timeZone to your time zone.

$theDay is defaulted to + 1 day. It will send messages the day before their appointment.

The text message is formatted like this:
$greeting . $firstName . $baseMsg1 . $service . $baseMsg2 . $date . $baseMsg3 . $startTime . $closer

The default settings will create this message:
Hi NAME,
This is COMPANY NAME reminding you of your appointment for SERVICE on DATE at TIME
