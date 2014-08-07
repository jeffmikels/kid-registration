<?php
// LOGIC
include "lib.php";

$body_class = 'tablet';


// process url requests to check in students
if (isset($_GET['child_id']) and isset($_GET['room_id']))
{
	$attendance = array();
	$attendance['date'] = $service_timestamp;
	$attendance['child_id'] = $_GET['child_id'];
	$attendance['room_id'] = $_GET['room_id'];
	toggle_attendance($attendance);
	Header("Location: room_checkin.php?room_id=" . $_GET['room_id']);
}

$room_id='';
if (isset($_GET['room_id'])) $room_id=$_GET['room_id'];

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $displayname; ?> Fast Room Check-in</title>

	<meta name="viewport" content="width=480" />

	<!-- JQUERY -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />

	<!-- STYLESHEETS -->
	<link href='http://fonts.googleapis.com/css?family=Rambla:400,700|Archivo+Narrow:400,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="css/newstyle.css" />

</head>
<body class="<?php print $body_class;?>">
<div id="page">
	<div id="main">
		<div id="header">
			<a href="room_checkin.php"><img class="logo" src="<?php echo $site_logo; ?>" style="vertical-align: bottom;" /></a>
		</div>

		<?php if ($err) : ?>
		<div class="error" id="error_box"><?php print ($err); ?></div>
		<?php endif; ?>

		<?php if ($msg) : ?>
		<div class="message" id="message_box"><?php print ($msg); ?></div>
		<?php endif; ?>

	<!-- first we check to see if a room_id has been selected. If so, only show selections for that room. -->

	<?php if( isset($_GET['room_id'])) : ?>
		
		<?php $room = $rooms[$_GET['room_id']]; ?>
		<div id="room_details" class="content">
			<h2>Welcome to <?php print $room['name']; ?> @ <?php print $service_time; ?>!<br />Today is <?php print $sunday; ?></h2>
			<br />
			<div id="allergy_alert" class="allergy_alert"></div>
			<div id="birthday_alert" class="birthday_alert"></div>
			<div id="options_links">
				
				<?php
				if (isset($_GET['teacher_view']) && $_GET['teacher_view'])
					{$teacher_color = 'red';$normal_color='gray';}
				else
					{$teacher_color = 'gray';$normal_color='red';}
				?>
				<a class="smallbutton <?php echo $teacher_color; ?>"
					href="room_checkin.php?room_id=<?php print $room_id; ?>&teacher_view=1" >
					Teacher View
				</a>
				<a class="smallbutton <?php echo $normal_color; ?>"
					href="room_checkin.php?room_id=<?php print $room_id; ?>" >
					Normal View
				</a>
								
				<?php
				foreach ($service_times as $time=>$timestamp)
				{
					if ( $service_time == $time ) $color = 'orange';
					else $color = 'gray';
					print "<a class=\"smallbutton $color\" href=\"room_checkin.php?service_time=" . urlencode($time) . "&room_id=" . $room_id . "\">" . $time . "</a>";
				}
				?>
				
			</div>
		</div>

		<ul class="check_in_list" id="check_in_list">
			<img src="images/ajax-loader.gif" style="margin:auto;"/>
		</ul>

		<div class="content" style="margin: 600px 0 100px;font-size: 1.2em;">
			<?php

			foreach ($service_times as $time=>$timestamp)
			{
				if ( $service_time == $time ) $color = 'red';
				else $color = 'gray';
				print "<a class=\"smallbutton $color\" href=\"room_checkin.php?service_time=" . urlencode($time) . "&room_id=" . $room_id . "\">" . $time . "</a>";
			}
			?>

		</div>

	<?php else: ?>

		<div class="content">
			<h2>Select Service Time</h2>

			<?php

			foreach ($service_times as $time=>$timestamp)
			{
				if ( $service_time == $time ) $color = 'red';
				else $color = 'gray';
				print "<a class=\"smallbutton $color\" href=\"room_checkin.php?service_time=" . urlencode($time) . "\">" . $time . "</a>";
			}
			?>
		</div>
		<br />
		<div class="content">
			<h2>Select Room for Check-in</h2>
		</div>

		<ul class="button_list">

		<?php foreach ($rooms as $room) : ?>
		<?php if ($room['ignore_for_checkin']) continue; ?>
		<li><a href="room_checkin.php?room_id=<?php print $room['room_id']; ?>"><?php print $room['name']; ?></a></li>
		<?php endforeach; ?>

		</ul>

	<?php endif; ?>

	</div>
</div>
<!--
<div id="alert_box">
	<audio id="alert_sound" preload="true">
		<source src="audio/glass.mp3" type="audio/mpeg"></source>
	</audio>
</div>
-->
<div class="nav" style="margin: 600px auto 0px; font-size: 1.2em;width:100%;position:relative;">
	<div style="text-align: center;position:relative;">
	<a class="smallbutton gray" href="index.php">Main Interface</a>
	<a class="smallbutton gray" id="notifications_toggle" href="" onClick="toggle_notifications();return false;">SMS Parent Notifications are <?php echo $notifications; ?></a>
	</div>
</div>

<div id="sms_prompt_dialog" style="display:none;">
	<p id="sms_prompt_text"></p>
	<textarea id="sms_prompt_response"></textarea>
	<input id="sms_prompt_child_id" type="hidden" />
	<input id="sms_prompt_attendance_id" type="hidden" />
</div>

</body>
<script>

var notifications = '<?php echo $notifications; ?>';

function toggle_notifications()
{
	if (notifications == 'ON') notifications = 'OFF';
	else notifications = 'ON';
	$('#notifications_toggle').html('SMS Notifications are ' + notifications);
	$('.notification-button').toggle();
}

function play_alert()
{
	audio = $('#alert_sound')[0];
	audio.play();
	audio.src = 'audio/glass.mp3';
	audio.load();
}
</script>

<?php if( $room_id != '' ) : ?>
<script>
//setTimeout("location.reload()",10000);
sunday_timestamp=<?php print $sunday_timestamp; ?> * 1000;
today = new Date();
year = today.getFullYear();

refresh_timer = 0;

var children;


function update_check_in_list(data)
{
	children = data;
	parent_notes = 0;
	allergy_list = new Array();
	next_week_birthday_list = new Array();
	last_week_birthday_list = new Array();
	show_all = <?php if (isset($_GET['teacher_view'])) print "0"; else print "1"; ?>;
	html = '';
	for (index in data)
	{

		child = data[index];

		if (! child.checked_in && show_all == 0) continue;

		list_class = '';

		// convert child.allergies to array for later use
		child_allergies = new Array();
		for (id in child.allergies)
		{
			allergy = child.allergies[id]
			child_allergies.push(allergy);
		}

		// convert child_allergies array to string for later use
		allergies = child_allergies.join('&nbsp;|&nbsp;');


		if (child.checked_in)
		{
			// prepare to show the checked_in html class
			list_class = 'checked_in';

			// add unique allergies to the global allergy list
			for (id in child_allergies)
			{
				allergy = child_allergies[id];
				if (allergy_list.indexOf(allergy) == -1) allergy_list.push(allergy);
			}

			// check to see if there are any other alerts;
			if (child.parent_note) parent_notes = 1;

			// prepare to show birthday alert information
			birthday = new Date(child.birthday_timestamp * 1000);
			birthday.setFullYear(year);
			birthday_timestamp = birthday.getTime();
			birthday_string = (birthday.getMonth() + 1) + '/' + birthday.getDate();
			if ( (birthday_timestamp >= (sunday_timestamp)) && (birthday_timestamp <= (sunday_timestamp + 7*24*60*60*1000) ) )
				next_week_birthday_list.push( child.first_name + '&nbsp;' + child.last_name + '&nbsp;(' + birthday_string + ')');
			if ( (birthday_timestamp >= (sunday_timestamp - 7*24*60*60*1000)) && (birthday_timestamp < (sunday_timestamp) ) )
				last_week_birthday_list.push( child.first_name + '&nbsp;' + child.last_name + '&nbsp;(' + birthday_string + ')');
		}

		html += '<li id="child_' + child.id + '" class="' + list_class + '">';
		html += '<img id="progress_' + child.id + '" src="images/ajax-loader.gif" style="float:right;display:none;"/>';
		html += '<a class="check_in_link" href="room_checkin.php?service_timestamp=' + service_timestamp + '&room_id=' + room_id + '&child_id=' + child.id + '" onclick="toggle_check_in('+ child.id +','+ room_id + ',' + service_timestamp + ');return false;">' + child.first_name + '&nbsp;' + child.last_name + '</a>';

		<?php if ($_GET['teacher_view'] == 1): ?>

		if (notifications == 'OFF') display='none';
		else display='inline';
		html += '<div class="child_meta">household: ' + child.household_name + '<br />contact: ' + child.cell_phone + '<br />allergies: ' + allergies + '<br />birthday: ' + child.birthday + '<br />parent notes: ' + child.parent_note + '<p><a class="smallbutton red" href="household.php?id=' + child.household_id + '">Edit Family Details</a>';
		if (child.checked_in)
		{
			html += '<button class="smallbutton orange notification-button" onclick="do_notify(' + index + ');return false;" style="display:' + display + ';">Notify Parent</a></div>';
		}

		<?php endif; ?>

		html += '</li>\n';
	}

	$("#check_in_list").html(html);
	if (allergy_list.length > 0) $("#allergy_alert").html("TODAY'S ALLERGY ALERTS: " + allergy_list.join('&nbsp;|&nbsp;'));
	else $("#allergy_alert").html('');

	if ((next_week_birthday_list.length > 0) || (last_week_birthday_list.length > 0))
	{
		birthday_html = "<hr />UPCOMING BIRTHDAYS: " + next_week_birthday_list.join(' ') + "<hr />LAST WEEK'S BIRTHDAYS: " + last_week_birthday_list.join(' ');
		$("#birthday_alert").html(birthday_html);
	}
	else $("#birthday_alert").html('');
	if (parent_notes) $("#allergy_alert").append('<br/>PARENT NOTES ARE AVAILABLE');

}

function toggle_check_in(child_id, room_id, service_timestamp)
{
	$("#progress_" + child_id).show();
	$("#child_" + child_id).toggleClass('checked_in');
	$("#child_" + child_id).addClass('pressed');
	retval = '';
	$.getJSON(
		"room_checkin_ajax.php?service_timestamp=" + service_timestamp + "&child_id=" + child_id + "&room_id=" + room_id,
		function(data)
		{
			$("#allergy_alert").html(data.SUCCESS);
			refreshChildren(room_id, service_timestamp);
		}
	);
	return retval;
}

function refreshChildren ( room_id, service_timestamp )
{
	//alert("room_checkin_ajax.php?room_id=" + room_id + "&service_timestamp=" + service_timestamp);
	$.getJSON("room_checkin_ajax.php?room_id=" + room_id + "&service_timestamp=" + service_timestamp, function(data){
		update_check_in_list(data);
	});
	if (refresh_timer) clearTimeout(refresh_timer);
	refresh_timer = setTimeout("refreshChildren(" + room_id + ", " + service_timestamp + ")", 10000);
}

function do_notify ( child_index )
{
	child = children[child_index];
	attendance_id = child.checked_in;
	if ( ! child.cell_phone ) alert('There is no notification cell phone on file for this child.');
	else
	{
		default_message = 'There is a problem with ' + child.first_name + ' in "' + child.room + '." Please come see us at the registration desk.'
		sms_prompt_text = "What do you want to say to the parent(s) of " + child.first_name + " " + child.last_name + "?"
		sms_prompt_title = "Parent Notification"
		$("#sms_prompt_dialog").dialog({title: sms_prompt_title})
		$("#sms_prompt_text").html(sms_prompt_text)
		$("#sms_prompt_response").val(default_message)
		$("#sms_prompt_child_id").val(child.id)
		$("#sms_prompt_attendance_id").val(attendance_id)
		$("#sms_prompt_dialog").dialog('open')
		my_prompt(sms_prompt_title, sms_prompt_text, default_message);
		if ( ! msg ) return;
	}
}

function do_notify_callback ( )
{
	child_id = $("#sms_prompt_child_id").val()
	attendance_id = $("#sms_prompt_attendance_id").val();
	msg = $("#sms_prompt_response").val()
	if ( ! msg ) return;
	//alert("room_checkin_ajax.php?notifications=" + notifications + "&do_notify=1&child_id=" + child_id + "&attendance_id=" + attendance_id + "&msg=" + msg);
	$.getJSON(
		"room_checkin_ajax.php?notifications=" + notifications + "&do_notify=1&child_id=" + child_id + "&attendance_id=" + attendance_id + "&msg=" + msg,
		function(data){
			$("#allergy_alert").html(data.SUCCESS);
		}
	);
}

function my_prompt ( title, text, default_prompt )
{
	default_prompt = ((a != null) ? a : '');
	$("#sms_prompt_dialog").dialog('open');

}

$(document).ready( function()
	{
		room_id = <?php print $room_id; ?>;
		service_time = "<?php print $service_time; ?>";
		service_timestamp = <?php print $service_timestamp; ?>;
		refreshChildren( room_id, service_timestamp );
		$.ajaxSetup({ cache: false });

		$("#sms_prompt_dialog").dialog(
		{
			autoOpen: false,
			height: 400,
			width: 350,
			modal: true,
			buttons:
			{
				"Send Notification": function()
				{
					do_notify_callback ();
					$( this ).dialog('close');
				},
				"Cancel": function()
				{
					$( this ).dialog('close');
				}
			}
		});
	}
);

</script>
<?php endif; ?>
</html>
