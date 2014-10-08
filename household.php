<?php
// LOGIC
/*
STEPS
	CHECK POSTED DATA AND UPDATE DATABASE
	OTHERWISE
		grab household id from request url
		pull household data from database
		display data in a form for editing or deleting
*/
include "lib.php";

global $rooms;

if (! isset($_GET['id'] )) Header('Location: index.php');

// if this is a pre-registration user, make sure they only try to access their own household data.
if ( $_SESSION['logged_in'] === 'public' and $_GET['id'] <> $_SESSION['pre_reg_household_id']) Header('Location: pre-register.php');

$household_id = $_GET['id'];

if ( $_POST['submit'] )
{
	//debug($_POST);
	// enter HOUSEHOLD details into database
	$household = Array();
	$keys = explode('|', 'household_name|address|city|state|zip|email|home_phone|cell_phone|household_id|delete');
	foreach ($keys as $key) $household[$key] = $_POST[$key];
	$household['id'] = $household['household_id'];
	$id = save_household($household);
	if ( ! $id )
	{
		$err = 1;
		$msg = 'I encountered an error editing the information.';
	}
	elseif ($household['delete'] && $id === TRUE)
	{
		$_SESSION['msg'] = 'Successfully deleted.';
		Header('Location: register.php');
	}
	else
	{
		// enter CHILDREN details
		foreach ($_POST['children'] as $child)
			if($child['first_name'] OR $child['id'])
				save_child($child, $household);

		// enter NOTES
		if (isset($_POST['notes']))
			foreach($_POST['notes'] as $note) save_note($note, $household);

		if (isset($_GET['new_family']))
		{
			Header('Location: thank-you.php?household=' . $household_id);
			exit();
		}
		else
			$msg = "Successfully edited";
	}
}
$household = get_households_and_children($household_id);
if (! $household) $err = "ERROR: I could't find the family you requested, <a href=\"index.php\">Click here to start over</a>.";


$all_allergies = get_allergies();

?>
<?php
// OUTPUT
include "header.php";
?>



<?php if ($household) : ?>

<div class="notice">
<?php foreach ($household['children'] as $child) : ?>
<?php $class='info'; ?>
<?php
	$last_room = $rooms[$child['last_room']];
	$suggested_room = $rooms[$child['suggested_room']];
	
	$class='info';
	$room_notice = '. '.$last_room['description'];
	if ($child['last_room'] <> $child['suggested_room'])
	{
		$class = 'alert';
		$room_notice = ', but ' . $suggested_room['name'] . ' might be a better fit. Here\'s a comparison of the two rooms: <ul><li>' . $last_room['description'] . '</li><li>' . $suggested_room['description'] . '</li></ul>Please specify your room preference below';
	}
?>
<?php $room = $rooms[$child['last_room']]; ?>
<h2>
	<?php print $child['first_name'];?> (<?php print $child['age']; ?> yrs.) :: Registered for <?php print $room['name']; ?>
</h2>
<div class="<?php print $class; ?>">
	<?php print $child['first_name'];?> is set up to go to <em><?php print $room['name']; ?></em><?php print $room_notice; ?>.
</div>
<?php endforeach; ?>
</div>


		<form method="POST">
			<input type="hidden" name="household_id" value="<?php print $household['household_id']; ?>" />
			<table>
				<tr>
					<td colspan=2 class="header">
						Household Details:
					</td>
				</tr>
				<tr><td colspan=2 class="break">&nbsp;</td></tr>
				</tr>

				<tr>
					<td class="legend">Household Name</td>
					<td class="input"><input type="text" name="household_name" value="<?php print $household['household_name']; ?>" onfocus="scroll_here(this);this.select();" /><br /><small>example: Jeff and Jennifer Mikels</small></td>
				</tr>
				<tr>
					<td class="legend">Mailing Address</td>
					<td class="input"><input type="text" name="address" value="<?php print $household['address']; ?>" onfocus="scroll_here(this);this.select();"  /></td>
				</tr>
				<tr>
					<td class="legend">City</td>
					<td class="input"><input type="text" name="city" value="<?php print $household['city']; ?>"  onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">State</td>
					<td class="input"><input type="text" name="state" value="<?php print $household['state']; ?>" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">ZIP</td>
					<td class="input"><input type="text" name="zip" value="<?php print $household['zip']; ?>" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">email</td>
					<td class="input"><input type="email" name="email" value="<?php print $household['email']; ?>" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">Cell Phone</td>
					<td class="input"><input type="phone" class="phone" name="cell_phone" value="<?php print $household['cell_phone']; ?>" onfocus="scroll_here(this);this.select();" /><br /><small>Please turn your cell phone ON and set it to vibrate during the service.</small></td>
				</tr>
				<tr>
					<td class="legend">Home Phone</td>
					<td class="input"><input type="phone" class="phone" name="home_phone" value="<?php print $household['home_phone']; ?>" onfocus="scroll_here(this);this.select();" /></td>
				</tr>

				<tr>
					<td class="legend">DELETE</td>
					<td class="input">
						<div class="alert">
							<input type="checkbox" name="delete" value="YES" onclick="if ( $(this).is(':checked') ) return confirm('THIS WILL DELETE THE ENTIRE HOUSEHOLD RECORD AND ALL THE CHILDREN. ARE YOU SURE YOU WANT TO DO THIS?');"/>
							CHECK THIS BOX TO DELETE THIS HOUSEHOLD
						</div>
					</td>
				</tr>


				<?php foreach($household['children'] as $child) : ?>

				<tr>
					<td colspan=2 class="header">
						<?php print $child['first_name'];?>
						<input type="hidden" name="children[<?php print $child['id']; ?>][id]" value="<?php print $child['id']; ?>" />
					</td>

				</tr>

				<tr>
					<td class="legend">First Name</td>
					<td class="input"><input type="text" name="children[<?php print $child['id']; ?>][first_name]" value="<?php print $child['first_name'];?>" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">Last Name</td>
					<td class="input"><input type="text" name="children[<?php print $child['id']; ?>][last_name]" value="<?php print $child['last_name'];?>" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">Birthday</td>
					<td class="input"><input type="text" name="children[<?php print $child['id']; ?>][birthday]" class="birthdate" id="birthday<?php print $child['id']; ?>" value="<?php print $child['birthday'];?>" onfocus="scroll_here(this);this.select();" /><br /><small>Please use MM/DD/YYYY format. Example: 01/11/2008</td>
				</tr>
				<tr>
					<td class="legend">Kidopolis Room</td>
					<td class="input">
						<?php foreach ($rooms as $room) : ?>
						<div class="radiobutton">
						<input type="radio" id="radio_<?php print $child['id']; ?>_<?php print $room['room_id']; ?>" name="children[<?php print $child['id']; ?>][last_room]" onfocus="scroll_here(this);this.select();" value="<?php print $room['room_id']; ?>"<?php if ($room['room_id'] == $child['last_room']) print ' checked="checked"'; ?> />
						<label for="radio_<?php print $child['id']; ?>_<?php print $room['room_id']; ?>"><?php print $room['name']; ?></label>
						</div>
						<?php endforeach; ?>
					</td>
				</tr>
				<tr>
					<td class="legend">Allergies</td>
					<td class="input">

						<?php
							foreach ($all_allergies as $allergy_id => $allergy)
							{
								$checked = "";
								if (isset($child['allergies'][$allergy_id])) $checked="checked='checked'";
								?>

								<div class="checkbox_grid">
									<input
										name="children[<?php print $child['id']; ?>][allergies][<?php print $allergy_id; ?>]"
										value="<?php print $allergy;?>"
										type="checkbox" <?php print $checked; ?> /><?php print $allergy; ?>
								</div>

								<?php
							}
						?>

					</td>
				</tr>
				<tr>
					<td class="legend">Special Concerns</td>
					<td class="input"><input name="children[<?php print $child['id']; ?>][parent_note]" value="<?php print $child['parent_note']; ?>" onfocus="scroll_here(this);this.select();" /><br /><small>Does this child have any special needs or is there anything our teachers should be aware of?</td>
				</tr>

				<tr>
					<td class="legend">DELETE</td>
					<td class="input">
						<div class="alert">
							<input type="checkbox" name="children[<?php print $child['id']; ?>][delete]" value="YES" onclick="if ( $(this).is(':checked') ) return confirm('Are you sure you want to delete this child from the database?');"/>
							CHECK THIS BOX TO DELETE THIS CHILD RECORD
						</div>
					</td>
				</tr>

				<?php endforeach; ?>

				<tr><td colspan=2 class="header">Add Child </td></tr>

				<tr>
					<td class="legend">First Name</td>
					<td class="input"><input type="text" name="children[0][first_name]" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">Last Name</td>
					<td class="input"><input type="text" name="children[0][last_name]" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">Birthday</td>
					<td class="input"><input type="date" name="children[0][birthday]" class="birthdate" id="birthday0" onfocus="scroll_here(this);this.select();" /><br /><small>Please use MM/DD/YYYY format. Example: 01/11/2008</td>
				</tr>
				<tr>
					<td class="legend">Allergies</td>
					<td class="input">

						<?php
							foreach ($all_allergies as $allergy_id => $allergy)
							{
								$checked = "";
								?>

								<div class="checkbox_grid">
									<input
										name="children[0][allergies][<?php print $allergy_id; ?>]"
										value="<?php print $allergy;?>"
										type="checkbox" <?php print $checked; ?> /><?php print $allergy; ?>
								</div>

								<?php
							}
						?>

					</td>
				</tr>
				<tr>
					<td class="legend">Special Concerns</td>
					<td class="input"><input name="children[0][parent_note]" value="" onfocus="scroll_here(this);this.select();" /><br /><small>Does this child have any special needs or is there anything our teachers should be aware of?</td>

				</tr>


				<?php if (isset($_GET['admin'])) : ?>

				<tr>
					<td colspan=2 class="header">Notes: (most recent at the top)</td>
				</tr>

				<?php $notes = get_notes($household_id); ?>
				<?php foreach ($notes as $note) : ?>

				<tr>
					<td class="legend"><?php print $note['date']; ?></td>
					<td class="input">
						<input type="hidden" name="notes[<?php print $note['id']; ?>][id]" value="<?php print $note['id']; ?>" />
						<input type="hidden" name="notes[<?php print $note['id']; ?>][date]" value="<?php print $note['date']; ?>" />
						<textarea name="notes[<?php print $note['id']; ?>][note]"><?php print $note['note']; ?></textarea>
					</td>
				</tr>

				<?php endforeach; ?>

				<tr>
					<td class="legend">New Note</td>
					<input type="hidden" name="notes[0][date]" value="" />
					<td class="input"><textarea name="notes[0][note]"></textarea></td>
				</tr>

				<?php endif; ?>



				<tr><td colspan=2 class="header">&nbsp;</td></tr>
				<tr><td colspan=2><center><input class="button green" type="submit" name="submit" value="Save" /></center></td></tr>

			</table>

		</form>

<script>
setTimeout(function() { $('#message_box').slideUp('slow'); }, 3000);
</script>


<?php endif; ?>

<?php include "footer.php"; ?>
