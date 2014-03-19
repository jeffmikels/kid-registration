<?php
//mail('jeffmikels@gmail.com','testing this email','are you getting these?');


$day_of_week = idate("w");
$today = strtotime(strftime("%m/%d/%Y", time()));
$sunday = $today - ($day_of_week * 60 * 60 * 24);
$sunday_display = strftime("%m/%d/%Y", $sunday);

$html = file_get_contents('http://lafayettecc.org/kidopolis/report.php?login_override=1');

$to = "jeffmikels@gmail.com";
$subject = "LCC Kidopolis Report for $sunday_display";


// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'To: grace2-8@hotmail.com, jeffmikels@gmail.com' . "\r\n";
//$headers .= 'From: Jeff Mikels <jeffmikels@gmail.com>' . "\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion();

// Mail it
mail($to, $subject, $html, $headers);
print "email sent";
/*
print "<hr />";
print "<textarea style=\"height:400px;width:100%;\">";
print $headers;
print "</textarea>";
print $html;
*/

?>
