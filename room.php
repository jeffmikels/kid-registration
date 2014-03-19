<?php
// LOGIC
include "lib.php";
$registrants = get_households_and_children();
$checkins = get_checkins($sunday_timestamp);


?>
<?php
// OUTPUT
$body_class = 'report';
include "header.php";
?>

	<?php $columns = Array('first_name', 'household_name', 'allergies', 'cell_phone'); ?>

	<?php
		// first we check to see if a room_id has been selected. If so, only show selections for that room.
	?>

	<?php if( isset($_GET['room_id'])) : ?>

		<?php $room = $rooms[$_GET['room_id']]; ?>
		<a name="<?php print $room['room_id']; ?>" />
		<h3><?php print $room['name']; ?> <a href="room.php?force_mobile=1&room_id=<?php print $room['room_id']; ?>">[mobile version]</a></h3>
		<?php
			if (isset($checkins['by_room'][$room['room_id']]))
				simple_table($columns, $checkins['by_room'][$room['room_id']], "admin room" . $room['room_id']);
			else print "No children checked in";
		?>

	<?php else: ?>

		<h2>Check-ins for <?php print $sunday; ?> By Room</h2>
		Jump to: <?php foreach ($rooms as $room) : ?>
		&nbsp;|&nbsp;<a href="room.php?room_id=<?php print $room['room_id']; ?>"><?php print $room['name']; ?></a>
		<a href="room.php?force_mobile=1&room_id=<?php print $room['room_id']; ?>"> (mobile) </a>&nbsp;|&nbsp;
		<?php endforeach; ?>


		<?php foreach ($rooms as $room) : ?>
		<a name="<?php print $room['room_id']; ?>" />
		<h3><a href="room.php?room_id=<?php print $room['room_id']; ?>"><?php print $room['name']; ?></a>
		<a href="room.php?force_mobile=1&room_id=<?php print $room['room_id']; ?>"> (mobile) </a></h3>

		<?php
			if (isset($checkins['by_room'][$room['room_id']]))
				simple_table($columns, $checkins['by_room'][$room['room_id']], "admin room" . $room['room_id']);
			else print "No children checked in";
		?>

		<?php endforeach; ?>

	<?php endif; ?>

<?php include "footer.php"; ?>

<?php if( isset($_GET['room_id'])) : ?>
<script>
//setTimeout("location.reload()", 15000);
</script>
<?php endif; ?>
