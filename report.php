<?php
// LOGIC
include "lib.php";
global $sunday_timestamp;
global $service_times;
global $db;

/*
	WEEKLY REPORT PAGE GOALS
		new kids this week
		second visits this week
		third visits this week

		birthdays this week // excluding inactive kids except for their first birthday after registering
			(( we want to recognize the child's first birthday after registration even if they only came once ))

		kids who have missed two weeks
		kids who've missed three weeks
		kids who've missed 5 weeks

		(( automatically flag as inactive kids who have missed 8 straight weeks ))

		total attendance report by rooms

	TODO
		0. The page should default to a summary report of the previous Sunday
		1. grab all attendance records sorted by child_id, then date DESC
		2. parse attendance records into arrays (indexed by child_id)
			while doing that, also count attendance by room.
			$room_attendance[$room_id] += 1;

		3. process records to create "report" arrays
			[[ foreach kid, most recent visit, number of visits, determine which list ]]
			new kids (one attendance record, and most recent was Sunday)
			second-timers (two attendance records, and most recent was Sunday)
			third-timers (three attendance records, and most recent was Sunday)
			kids whose most recent visit was two weeks ago.
			kids whose most recent visit was three weeks ago.
			kids whose most recent was five weeks ago.

		4. grab all children records
			parse children records into array indexed by child_id (for easier grabbing when report time comes);

		5. process records to create more "report" arrays
			[[foreach kid, create birthday date for this year, is Sunday < birthday < Sunday + 60*60*24*7 ]]
			birthdays this week

		6. display nice listing of "report" arrays with relevant information

*/

//		0. compute the timestamp for the previous Sunday
if ($_GET['date']) $sunday_timestamp = $_GET['date'];
$sunday = strftime("%m/%d/%Y", $sunday_timestamp);

//		1. grab all attendance records and children records
$children = get_children('all','active');
$attendance = Array();
if ($_GET['date'])
{
	$attendance = get_attendance_by_date($_GET['date']);
}
else
{
	foreach ($service_times as $key=>$timestamp)
	{
		$attendance = array_merge($attendance, get_attendance_by_date($timestamp));
	}
}


//		2. grab all children records
//			parse children records into array indexed by child_id (for easier grabbing when report time comes);
//			also create birthdays this week report
//			[[foreach kid, create birthday date for this year, is Sunday < birthday < Sunday + 60*60*24*7 ]]
//			birthdays this week

$children_by_id = Array();
$birthdays_this_week = Array();
foreach ($children as $child)
{
	if ($child['status'] != 'active') continue;
	$children_by_id[$child['id']] = $child;
	$birthday_string = strftime("%m/%d", $child['birthday_timestamp']) . '/' . strftime("%Y", time());
	$birthday_this_year = strtotime($birthday_string);
	if ($sunday_timestamp < $birthday_this_year AND $birthday_this_year <= ($sunday_timestamp + 60 * 60 * 24 * 7)) $birthdays_this_week[] = $child;
}


//		3. parse service and room data
$service_data = Array();
foreach ($service_times as $desc=>$timestamp)
{
	$service_data[$timestamp] = Array(
		'room_attendance' => Array(),
		'total' => 0
	);
}
$attendance_by_child = Array();
$total_kids = 0;
foreach ($attendance as $item)
{
	$total_kids += 1;
	$timestamp = $item['date'];
	// increment room attendance
	if (! $service_data[$timestamp]['room_attendance'][$item['room_id']] ) $service_data[$timestamp]['room_attendance'][$item['room_id']] = 1;
	$service_data[$timestamp]['room_attendance'][$item['room_id']] += 1;
	$service_data[$timestamp]['total'] += 1;
}

// clean up
foreach ($service_data as $key=>$data) if ($data['total'] == 0) unset($service_data[$key]);

//		4. process records to create "report" arrays
//			[[ foreach kid, most recent visit, number of visits, determine which visit it is ]]
//			new kids (one attendance record, and most recent was Sunday)
//			second-timers (two attendance records, and most recent was Sunday)
//			third-timers (three attendance records, and most recent was Sunday)
//			kids whose most recent visit was two weeks ago.
//			kids whose most recent visit was three weeks ago.
//			kids whose most recent was five weeks ago.

$all_attendance = get_attendance();
$attendance_by_child = Array();
foreach ($all_attendance as $item)
{
	// filter out inactive children
	if ($item['status'] != 'active') continue;

	// build array for each child
	if (!is_array($attendance_by_child[$item['child_id']])) $attendance_by_child[$item['child_id']] = Array();
	$attendance_by_child[$item['child_id']][] = $item;
}
$children_by_visits = Array();
$absentees_by_weeks = Array();
foreach ($attendance_by_child as $child_id=>$records)
{
	$visit_count = count($records);
	$most_recent = $records[0]['date'];
	$first_attendance = $records[$visit_count - 1]['date'];
	$weeks_missed = max(0, floor(($sunday_timestamp - $most_recent) / (60 * 60 * 24 * 7)));

	// if the kid was here this week, figure out how many times that kid has been here
	if (strftime("%m/%d/%Y", $most_recent) == strftime("%m/%d/%Y", $sunday_timestamp))
	{
		if (! is_array($children_by_visits[$visit_count]) ) $children_by_visits[$visit_count] = Array();
		$children_by_visits[$visit_count][] = $children_by_id[$child_id];
	}

	if ( ! is_array($absentees_by_weeks[$weeks_missed]) ) $absentees_by_weeks[$weeks_missed] = Array();
	$absentees_by_weeks[$weeks_missed][] = $children_by_id[$child_id];
}

//		5. save report data
$report_data = Array('children'=>$children, 'attendance'=>$attendance);
$report_data = serialize($report_data);
$filename = "reports/report-" . strftime("%Y-%m-%d", $sunday_timestamp) . ".data";
file_put_contents($filename, $report_data);

//		6. display nice listing of "report" arrays with relevant information

?>
<?php
// OUTPUT
$body_class = "report";

include "header.php";
?>
<h1><a href="report.php?date=<?php print $sunday_timestamp; ?>">Report for Sunday <?php print strftime("%m/%d/%Y %H:%M", $sunday_timestamp); ?></a></h1>

<!-- ROOM ATTENDANCE -->
	<h2>Room Attendance</h2>
	<table class="admin" cellpadding=0 cellspacing=0>
		<tr>
			<th>Room</th>
			<th>Count</th>
		</tr>

		<?php foreach ($service_data as $timestamp=>$data) : ?>
		<?php foreach ($data['room_attendance'] as $room_id=>$count) : ?>

		<tr>
			<td><?php print $rooms[$room_id]['name'] . " (" . strftime("%H:%M", $timestamp) . ") " ; ?></td>
			<td><?php print $count; ?></td>
		</tr>

		<?php endforeach; ?>

		<tr>
			<td><strong><?php print strftime("%H:%M", $timestamp); ?> SERVICE TOTAL</strong></td>
			<td><?php print $data['total']; ?></td>
		</tr>


		<?php endforeach; ?>

		<tr>
			<td><strong>TOTAL KID ATTENDANCE</strong></td>
			<td><?php print $total_kids; ?></td>
		</tr>
	</table>


	<hr />
	<h1>Summary Statistics (not service specific)</h1>

	<?php $columns = Array( 'room', 'first_name', 'last_name', 'household_name', 'home_phone', 'cell_phone', 'email', 'address', 'city', 'state', 'zip', 'allergies'); ?>

	<?php if (is_array($children_by_visits[1])) : ?>
	<h2>New Kids</h2>

	<?php simple_table($columns, $children_by_visits[1], $class='admin'); ?>
	<?php endif; ?>


	<?php if (is_array($children_by_visits[2])) : ?>
	<h2>Second Visit Kids</h2>

	<?php simple_table($columns, $children_by_visits[2], $class='admin'); ?>
	<?php endif; ?>


	<?php if (is_array($children_by_visits[3])) : ?>
	<h2>Third Visits</h2>

	<?php simple_table($columns, $children_by_visits[3], $class='admin'); ?>
	<?php endif; ?>


	<?php if (is_array($absentees_by_weeks[2])) : ?>
	<h2>Missed Two Weeks</h2>

	<?php simple_table($columns, $absentees_by_weeks[2], $class='admin'); ?>
	<?php endif; ?>

	<?php if (is_array($absentees_by_weeks[3])) : ?>
	<h2>Missed Three Weeks</h2>

	<?php simple_table($columns, $absentees_by_weeks[3], $class='admin'); ?>
	<?php endif; ?>

	<?php if (is_array($absentees_by_weeks[5])) : ?>
	<h2>Missed Five Weeks</h2>

	<?php simple_table($columns, $absentees_by_weeks[5], $class='admin'); ?>
	<?php endif; ?>


	<?php if (count($birthdays_this_week) > 0) : ?>
	<h2>Birthdays This Week</h2>

	<?php simple_table($columns, $birthdays_this_week, $class='admin'); ?>
	<?php endif; ?>



	<?php //debug($children_by_visits); ?>
	<?php //debug($absentees_by_weeks); ?>
	<?php //debug($birthdays_this_week); ?>

<?php include "footer.php"; ?>
<?php refresh_child_status(); ?>
