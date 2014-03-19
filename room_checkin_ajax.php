<?php
// LOGIC
include "lib.php";

// THIS SENDS NOTIFICATIONS IMMEDIATELY
if (isset($_GET['do_notify']) and $_GET['notifications'] == 'ON')
{
	$child_id = $_GET['child_id'];
	$message = $_GET['msg'];
	$attendance_id = $_GET['attendance_id'];
	print json_encode( notify_parent($child_id, $message, $attendance_id, TRUE) );
}


// process url requests to check in students
if (isset($_GET['child_id']) and isset($_GET['room_id']))
{
	$attendance = array();
	$service_timestamp = isset($_GET['service_timestamp']) ? $_GET['service_timestamp'] : $service_timestamp;
	$attendance['date'] = $service_timestamp;
	$attendance['child_id'] = $_GET['child_id'];
	$attendance['room_id'] = $_GET['room_id'];
	$notifications = $_GET['notifications'];
	if ($notifications == 'OFF') $notify = false;
	else $notify = true;
	$result = toggle_attendance($attendance, $notify);
	$attendance['save_result'] = $result;
	if ($result == 1) $attendance['result'] = 'added';
	elseif ($result == 2) $attendance['result'] = 'deleted';
	else $attendance['result'] = 'failed';
	print json_encode( $attendance );
}

elseif (isset($_GET['room_id']) and isset($_GET['service_timestamp']))
{
	$room_id = $_GET['room_id'];
	$children = get_children_by_room($room_id);
	$service_timestamp_forced = $_GET['service_timestamp'];

	// get all child ids checked in to this room today
	// return an attendance id for the checked_in field if they have been checked in today
	// now, get all child ids checked in to this room for this service
	$sql = sprintf (
		"SELECT id, child_id FROM attendance WHERE date='%s' AND room_id='%s'", $db->escapeString($service_timestamp_forced),
		$db->escapeString($room_id)
	);

	$results = my_query($sql);
	$child_ids = Array();
	$attendance_ids = Array();
	foreach ($results as $row)
	{
		$child_ids[] = $row['child_id'];
		$attendance_ids[$row['child_id']] = $row['id'];
	}

	foreach ($children as $key=>$child)
	{
		if ($child['status'] != 'active') continue;
		$children[$key]['checked_in'] = 0;
		if ( in_array($child['id'], $child_ids)) $children[$key]['checked_in'] = $attendance_ids[$child['id']];
	}
	print json_encode($children);
}


elseif (isset($_GET['room_id']))
{
	$room_id = $_GET['room_id'];
	$children = get_children_by_room($room_id);

	// now, get all child ids checked in to this room today
	$sql = sprintf("SELECT child_id FROM attendance WHERE date='%s' AND room_id='%s'", $db->escapeString($service_timestamp),
		$db->escapeString($room_id));
	$results = my_query($sql);
	$child_ids = Array();
	foreach ($results as $row)
	{
		$child_ids[] = $row['child_id'];
	}

	foreach ($children as $key=>$child)
	{
		$children[$key]['checked_in'] = FALSE;
		if ( in_array($child['id'], $child_ids)) $children[$key]['checked_in'] = TRUE;
	}
	print json_encode($children);
}