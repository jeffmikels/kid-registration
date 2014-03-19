<?php
// LOGIC
include "lib.php";
global $rooms;

if (! isset($_GET['household'] )) Header('Location: index.php');
$household_id = $_GET['household'];
$household = get_households_and_children($household_id);
if (! $household) $err = "ERROR: I could't find the family you requested.";

if (isset($_GET['final']))
{
	// if we got here, a new family has registered and everything is ready to go.
	// now we want to notify them with their new information via text message

	$text = 'Thank you for registering your children at KIDOPOLIS.';
	foreach ($household['children'] as $child)
	{
		$text .= ' ' . $child['first_name'] . ' should go to ' . $rooms[$child['last_room']]['name'] . '.';
	}
	notify_parent($child['id'], $text, '', true, true);

}
//debug($household);

?>
<?php
// OUTPUT
include "header.php";
?>


<?php if (!isset($_GET['final'])): ?>
<h1>STEP THREE: How's this look?</h1>
<?php else: ?>
<h1>Excellent! Your kids can go straight to their rooms.</h1>
<?php if ($household['cell_phone']) : ?><p>I've also sent this information to your cell phone.</p><?php endif; ?>
<?php endif; ?>

<div class="notice">
<?php foreach ($household['children'] as $child) : ?>
<?php $room = $rooms[$child['last_room']]; ?>
<h2><?php print $child['first_name'];?> (<?php print $child['age']; ?> yrs.) :: <?php print $room['name']; ?></h2>
<?php print $child['first_name'];?> is set up to go to <em><?php print $room['name']; ?></em>. <?php print $room['description']; ?>.
<?php endforeach; ?>
</div>
<br />

<?php if (!isset($_GET['final'])): ?>
<div class="notice">
	<h3>Your Family Details:</h3>
	<br />
	<?php print $household['household_name']; ?><br />
	<?php print $household['address']; ?><br />
	<?php print $household['city']; ?>, <?php print $household['state']; ?> <?php print $household['zip']; ?><br />
	email: <?php print $household['email']; ?><br />
	cell: <?php print $household['cell_phone']; ?><br />
	home: <?php print $household['home_phone']; ?><br />
	<hr />
	<?php foreach ($household['children'] as $child) : ?>
	<div class="child">
	<?php print $child['first_name']; ?> <?php print $child['last_name']; ?><br />
	birthday: <?php print $child['birthday']; ?><br />
	room: <?php print $rooms[$child['last_room']]['name']; ?><br />
	allergies: <?php print $child['allergies'] ? implode(',',$child['allergies']) : 'none'; ?><br />
	special notes: <?php print $child['parent_note'] ? $child['parent_note'] : 'none'; ?>
	</div>
	<br />
	<?php endforeach; ?>
</div>

<a class="button green" href="thank-you.php?household=<?php print $household_id; ?>&final=1">LOOKS GREAT!</a>
<a class="button blue" href="household.php?id=<?php print $household_id; ?>&new_family=1">CHANGE STUFF</a>

<br />

<?php endif; ?>


<?php include "footer.php"; ?>
