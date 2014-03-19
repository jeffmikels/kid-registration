<?php
include "lib.php";

Header('Content: text/json');
$action = $_GET['action'];
$object = $_GET['object'];
$args = $_GET['args'];

/*
	objects:
		children, households, attendance, notes, all
	actions:
		get (args: id or '' for all)
		search (args: field, expression)
		save (args: object)
*/

if (is_array($args)) $result = call_user_func_array($action . '_' . $object, $args);
else $result = call_user_func($action . '_' . $object, $args);
if (isset($_GET['debug']))
{
	debug($_SESSION);
	debug ($result);
}

// if this is a pre-registration, we only allow household name searches through ajax
if ($_SESSION['logged_in'] === 'public' and count($result) > 0)
{
	$new_results = Array();
	foreach ($result as $household)
	{
		$new_results[] = array('household_name' => $household['household_name']) ;
		$result = $new_results;
	}
}

print json_encode($result);
?>
