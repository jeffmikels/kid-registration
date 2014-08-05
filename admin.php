<?php
// LOGIC
include "lib.php";
$registrants = get_households_and_children();
$checkins = Array();
foreach ($service_times as $timestamp) $checkins[$timestamp] = get_checkins($timestamp);

// DATABASE CLEANING
// if we deleted a child, a household might exist with no children registered household has no children registered, delete that household too.
$sql = "DELETE FROM households WHERE households.id NOT IN (SELECT household_id FROM household2child);";
$db->query($sql);

?>
<?php
// OUTPUT
$body_class = 'report';
include "header.php";
?>



	<table class="admin" id="reports_box" cellpadding=0 cellspacing=0>
		<tr>
			<td colspan=3 class="header">
				<center>
					<a href="allergies.php">Edit Allergies</a>
				</center>
			</td>
		</tr>
		<tr>
			<td colspan=3 class="header">
				<center>

					<a href="report.php?admin=1">CURRENT SUMMARY REPORT</a>
					<form method="GET" action="report.php">
					PREVIOUS REPORTS: <select name="date">

					<?php
					$dates = get_attendance_dates();
					foreach ($dates as $date)
					{
						$text = strftime("%m/%d/%Y (%H:%M)", $date);
						print "<option value=\"$date\">$text</option>\n";
					}
					?>
					</select>
					<input class="default" type="submit" value="go" />
					</form>

						<a class="smallbutton blue" href="mass_notify.php?type=email">Email All Families</a>
						<a class="smallbutton red" href="mass_notify.php?type=sms">SMS All Families</a>

				</center>
			</td>
		</tr>
	</table>


	<?php foreach ($service_times as $timestamp): ?>
	<h2>Check-ins for <?php print strftime("%m/%d/%Y %H:%M", $timestamp); ?></h2>
	<?php $columns = Array('first_name', 'last_name', 'household_name', 'cell_phone'); ?>
	<?php foreach ($rooms as $room) : ?>
	<a name="<?php print $room['room_id']; ?>" />
	<h3><a href="admin.php#<?php print $room['room_id']; ?>"><?php print $room['name']; ?></a></h3>
	<?php simple_table($columns, $checkins[$timestamp]['by_room'][$room['room_id']], "admin room" . $room['room_id']); ?>
	<?php endforeach; ?>
	<?php endforeach; ?>
	<h2>All households with kids who've attended in the last 6 mos.</h2>
		<table class="admin" cellpadding=0 cellspacing=0>
			<tr>
				<th>Household</th>
				<th>Phone</th>
				<th>Kids</th>
			</tr>


			<?php $even_or_odd = "even"; ?>
			<?php foreach ($registrants as $family) : ?>

			<?php if ($family['last_attended'] < (time() - (60 * 60 * 24 * 180))) continue; ?>

			<?php
				if ($even_or_odd == "even") $even_or_odd = "odd";
				else $even_or_odd = "even";
			?>

			<tr class="<?php print $even_or_odd;?>">
				<td>
					<a href="household.php?admin=1&id=<?php print $family['household_id']; ?>"><?php print $family['household_name']; ?></a>	<br />
					<?php print $family['address']; ?><br />
					<?php print $family['city'] . ', ' . $family['state'] . ' ' . $family['zip']; ?><br />
					<a href="mailto:<?php print $famiy['email']; ?>"><?php print $famiy['email']; ?></a>
				</td>
				<td>
					cell: <?php print $family['cell_phone']; ?><br />
					home: <?php print $family['home_phone']; ?>
				</td>
				<td>
					<table cellpadding=0 cellspacing=0>
						<tr>
							<th>Name</th>
							<th>Age</th>
							<th>Allergies</th>
							<th>Last Attended</th>
						</tr>

						<?php foreach ($family['children'] as $child): ?>
						<tr>
							<td><?php print $child['first_name']; ?></td>
							<td><?php print $child['age']; ?></td>
							<td><?php print implode(', ', $child['allergies']); ?></td>
							<td><?php print date('m-d-Y', $child['last_attended']); ?></td>
						</tr>

						<?php endforeach; ?>

					</table>
				</td>
			</tr>

			<?php endforeach; ?>

		</table>
		</pre>

	</div>
</body>
</html>
