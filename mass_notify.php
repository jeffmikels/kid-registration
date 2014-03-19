<?php
// LOGIC
include "lib.php";

global $testing;
$testing = true;
$testing_email = 'jeffmikels@gmail.com';
$testing_phone = '7654040807';

if (isset($_GET['type'])) $notify_type = $_GET['type'];
else Header('Location: index.php');

// PROCESS POSTED DATA
if(isset($_POST['notify_submit']))
{

	if (isset($_POST['subject'])) $subject = '[KIDOPOLIS @ LCC] ' . $_POST['subject'];
	$body = $_POST['body'];
	if (isset ($_POST['active_only']) && $_POST['active_only'] == 'yes') $active_only = true;
	else $active_only = false;

	if ($notify_type == 'email')
	{
		email_all($subject, $body, $active_only);
	}

	if ($notify_type == 'sms')
	{
		sms_all($body, $active_only);
	}
	Header('Location: ?type=done');
}


function email_all($subject, $body, $active_only = true)
{
	global $testing;
	global $testing_email;
	$households = get_households();
	$body = $body . "\n\n----------------------------------\nThis email was sent to you because your family is registered for the Kidopolis program at Lafayette Community Church. If you'd like us to remove your registration, just reply to this email and let us know.";
	foreach ($households as $household)
	{
		$do_send = true;
		if ($active_only)
		{
			$do_send = false;
			foreach ($household['children'] as $child)
			{
				if ($child['status'] == 'active') $do_send = true;
			}
		}
		if (! $do_send) continue;

		// if testing, then we hijack the first valid email change to the testing email and then quit
		if ($testing) $to = $testing_email;
		else $to = $household['email'];
		$from = 'From: LCC Kidopolis <kidopolis@lafayettecc.org>';
		mail($to, $subject, $body, $from);
		if ($testing) break;
	}
}

function sms_all($body, $active_only = true)
{
	global $testing;
	global $testing_phone;
	$households = get_households();
	foreach ($households as $household)
	{
		$do_send = true;
		if ($active_only)
		{
			$do_send = false;
			foreach ($household['children'] as $child)
			{
				if ($child['status'] == 'active') $do_send = true;
			}
		}
		if (! $do_send) continue;

		// if testing, then we hijack the first valid phone, change to the testing phone and then quit.
		if ($testing) $to = $testing_phone;
		else $to = $household['cell_phone'];
		if (! $to) continue;
		simple_sms($to, $body);
		if ($testing) break;
	}
}





// OUTPUT
$body_class = 'report';
include "header.php";
?>

<script>
	function update_sms_preview()
	{
		sms_body = $('#sms_body').val();
		sms_body = "KIDOPOLIS @ LCC: " + sms_body + " / Reply REMOVE to deregister your family.";
		chars_left = 160 - sms_body.length;
		if (chars_left >= 0)
		{
			status_msg = chars_left + ' characters left';
			e = $('#sms_submit')[0]
			e.value = 'Ready to Submit';
			e.disabled = false;
		}
		else
		{
			status_msg = chars_left + ' characters over!';
			e = $('#sms_submit')[0]
			e.value = 'Please shorten your message.';
			e.disabled = true;
		}
		$('#sms_status').html(status_msg);
		$('#sms_preview').val(sms_body);
		$('#real_body').val(sms_body);
	}
</script>

<div style="width:100%;">
	<div style="width:80%;margin:20px auto;">
		<h2>Send Mass Notification (<?php print $notify_type; ?>)</h2>

		<form action="?type=<?php print $notify_type;?>" method="POST" onsubmit="return confirm('Are you sure you want to send this out to EVERY family in the database?');">

			<?php if ($notify_type == 'done') : ?>

			I think everything went well. <a href="admin.php">Return to Admin Pages</a>


			<?php elseif ($notify_type == 'email') : ?>

			<p><input type="checkbox" name="active_only" checked="checked" value="yes" /> Send to Active Families Only</p>

			<input style="width:100%;" name="subject" placeholder="Subject" />

			<textarea style="width:100%;height:400px;" name="body" placeholder="Body"></textarea>

			<input class="smallbutton green" style="width:100%;" type="submit" name="notify_submit" />

			<?php elseif ($notify_type == 'sms'): ?>

			<p><input type="checkbox" name="active_only" checked="checked" value="yes" /> Send to Active Families Only</p>

			<textarea style="width:100%;" id="sms_body" name="entry" placeholder="Body" onkeyup="update_sms_preview()" ></textarea>
			<hr />
			SMS Preview<br />
			<textarea style="width:100%;font-size:14pt;" id="sms_preview" name="preview" disabled="disabled" placeholder="Preview"></textarea>

			<input id="real_body" type="hidden" name="body" />

			<div style="color:red;text-align:center;" id="sms_status"></div>
			<input class="smallbutton green" style="width:100%;" type="submit" id='sms_submit' name="notify_submit" />

			<?php endif; ?>


		</form>
	</div>
</div>
</body>
</html>
