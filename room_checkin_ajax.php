<?php
Header("Access-Control-Allow-Origin: *");
Header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

// LOGIC
include "lib.php";

// AUTHENTICATION IS HANDLED BY LIB.PHP SO IF WE HAVE MADE IT THIS FAR, WE SHOULD BE
// AUTHENTICATED


// THIS SENDS NOTIFICATIONS IMMEDIATELY
if (isset($_GET['do_notify']) and $_GET['notifications'] == 'ON')
{
	$child_id = $_GET['child_id'];
	$message = $_GET['msg'];
	$attendance_id = $_GET['attendance_id'];
	print json_encode( notify_parent($child_id, $message, $attendance_id, TRUE) );
	exit();
}

// THIS SENDS AN SMS TO ANY NUMBER,
// IT IS USED TO SEND AN ALERT TO THE
// DIRECTOR OR AN ASSISTANT
if (isset($_GET['alert_director']))
{
	$child_id = $_GET['child_id'];
	$message = $_GET['msg'];
	$phone = $_POST['phone_number'];
	print json_encode( simple_sms($phone, $message) );
	exit();
}

// CHECK IN STUDENT TO A ROOM
if (isset($_GET['child_id']) and isset($_GET['room_id']))
{
	// the default is to toggle the attendance
	// if you want to only save, add a GET parameter of 'action' with value 'check_in'
	// if you want to only delete, add a GET parameter of 'action' with value 'check_out'
	// if you want to only add a note, add a GET parameter of 'action' with value 'note'
	$attendance = array();
	$service_timestamp = isset($_GET['service_timestamp']) ? $_GET['service_timestamp'] : $service_timestamp;
	$attendance['date'] = $service_timestamp;
	$attendance['child_id'] = $_GET['child_id'];
	$attendance['room_id'] = $_GET['room_id'];
	$attendance['note'] = isset($_GET['note']) ? $_GET['note'] : '';
	$notifications = $_GET['notifications'];
	if ($notifications == 'OFF') $notify = false;
	else $notify = true;
	
	if (isset($_GET['action']) && $_GET['action'] == 'check_in') $result = save_attendance($attendance, $notify);
	elseif (isset($_GET['action']) && $_GET['action'] == 'note') $result = save_attendance($attendance, False);
	elseif (isset($_GET['action']) && $_GET['action'] == 'check_out') $result = delete_attendance($attendance);
	else $result = toggle_attendance($attendance, $notify);
	
	$attendance['save_result'] = $result;
	if ($result == 1) $attendance['result'] = 'added';
	elseif ($result == 2) $attendance['result'] = 'deleted';
	else $attendance['result'] = 'failed';
	
	print json_encode( $attendance );
}

// SUBMIT BEHAVIOR REPORT
elseif (isset($_GET['behavior_report']))
{
	$report_items = $_POST['report_items'];
	foreach ($report_items as $report)
	{
		$id = $report['attendance_id'];
		if (! $id) continue;
		
		$behaviors = $report['behaviors'] or '';
		if ($behaviors != '')
			$behaviors = json_encode($report['behaviors']);
		else
			continue;
		
		update_attendance_behaviors($id, $behaviors);
	}
	print json_encode(array('success' => 'success'));
}


// GET LIST OF STUDENTS IN ROOM
elseif (isset($_GET['room_id']))
{
	$room_id = $_GET['room_id'];
	$children = get_children_by_room($room_id);
	if (isset($_GET['service_timestamp']))
		$service_timestamp_forced = $_GET['service_timestamp'];
	else
		$service_timestamp_forced = $service_timestamp;

	// get all child ids checked in to this room today
	// return an attendance id for the checked_in field if they have been checked in today
	// now, get all child ids checked in to this room for this service
	$sql = sprintf (
		"SELECT id, child_id, behaviors FROM attendance WHERE date='%s' AND room_id='%s'", $db->escapeString($service_timestamp_forced),
		$db->escapeString($room_id)
	);

	$results = my_query($sql);
	$child_ids = Array();
	$attendance_ids = Array();
	foreach ($results as $row)
	{
		$child_ids[] = $row['child_id'];
		$attendance_ids[$row['child_id']] = $row;
	}
	
	foreach ($children as $key=>$child)
	{
		// ignore inactive children
		if ($child['status'] != 'active') continue;
		
		// set up attendance properties
		$children[$key]['checked_in'] = 0;
		$children[$key]['behaviors'] = '';
		if ( in_array($child['id'], $child_ids))
		{
			$children[$key]['checked_in'] = $attendance_ids[$child['id']]['id'];
			$behaviors = $attendance_ids[$child['id']]['behaviors'];
			if (isset($attendance_ids[$child['id']]['note']))
				$children[$key]['note'] = $attendance_ids[$child['id']]['note'];
			$children[$key]['behaviors'] = json_decode($behaviors, true);
		}
	}
	
	// print_r($children);
	// exit();
	
	// to reindex by child_id
	// $retval = Array();
	// foreach ($children as $child)
	// {
	// 	$retval[$child['id']] = $child;
	// }
	
	print json_encode($children);
}

// elseif (isset($_GET['room_id']))
// {
// 	$room_id = $_GET['room_id'];
// 	$children = get_children_by_room($room_id);
//
// 	// now, get all child ids checked in to this room today
// 	$sql = sprintf("SELECT child_id FROM attendance WHERE date='%s' AND room_id='%s'", $db->escapeString($service_timestamp),
// 		$db->escapeString($room_id));
// 	$results = my_query($sql);
// 	$child_ids = Array();
// 	foreach ($results as $row)
// 	{
// 		$child_ids[] = $row['child_id'];
// 	}
//
// 	foreach ($children as $key=>$child)
// 	{
// 		$children[$key]['checked_in'] = FALSE;
// 		if ( in_array($child['id'], $child_ids)) $children[$key]['checked_in'] = TRUE;
// 	}
// 	print json_encode($children);
// }

else
{
	$service_times_retval = Array();
	foreach ($service_times as $key=>$timestamp) $service_times_retval[$key] = array('label' => $key, 'timestamp'=>$timestamp);
	print json_encode(Array('rooms'=>$rooms, 'service_times'=>$service_times, 'service_times_with_label'=>$service_times_retval));
}