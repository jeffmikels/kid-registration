<?php
// RESET THE SESSION:
// Initialize the session.
// If you are using session_name("something"), don't forget it now!
/*

session_name('KIDOPOLIS_REGISTRATION');
session_start();

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();
*/

// LOGIC
include "lib.php";

$body_class = "home";

?>
<!DOCTYPE html>
<html>
<head>
	<!-- <base href="http://lafayettecc.org/kidopolis/" /> -->
	<title><?php echo $display_name; ?></title>

	<!-- STYLESHEETS -->
	<link href='http://fonts.googleapis.com/css?family=Rambla:400,700|Archivo+Narrow:400,700' rel='stylesheet' type='text/css'>

	<link rel="stylesheet" type="text/css" href="style.css" />


	<!-- SCRIPTS -->
	<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script> -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui-1.8.16.custom.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui-timepicker-addon.js" type="text/javascript"></script>
	<script type='text/javascript' src='js/jquery.scrollTo-min.js'></script>
	<script type='text/javascript' src='js/jquery_init.js'></script>

	<style type="text/css">
		body {
			background-image: url(<?php echo $page_background; ?>);
		}
		@media (min-width: 1440px)
		{
			body{background-position:top;}
		}
		#header-logo {background:rgba(25, 181, 194, 0);width:100%;padding:3px 10px;box-sizing:border-box;}
		.header-logo {width:200px;image-shadow:0px 0px 30px black;}
		.header-logo { -webkit-filter: drop-shadow(0px 0px 5px #19b5c2); filter: drop-shadow(0px 0px 5px #19b5c2); }
		.page-head {font-family: "Archivo Narrow";font-size:350px;color:white;text-shadow:0px 0px 50px #19b5c2;font-weight:bold;letter-spacing:-20px; position:absolute; top:100px;left:3%;}
		#get-started-button {padding:30px;height:auto;position:absolute;left:550px;top:450px;}

	</style>
</head>
<body>
<div id="header-logo">
	<img class="header-logo" src="<?php print $site_logo; ?>" />
</div>
	<h1 class="page-head">Welcome</h1>
	<a href="register.php" id="get-started-button" class="button blue">Click Here</a>
</body>
</html>