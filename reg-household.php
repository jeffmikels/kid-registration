<?php
// LOGIC
/*
STEP ONE:
	enter household into database
	if household exists, display error with link to household edit form
	if household enters properly, set $_SESSION variable and relocate to reg-kid.php
*/
include "lib.php";
//debug($_POST);
if (isset($_POST['submit']))
{
	// enter person details into database
	$household = Array();
	$keys = explode('|', 'household_name|address|city|state|zip|email|home_phone|cell_phone|civicrm_id');
	foreach ($keys as $key) $household[$key] = $_POST[$key];
	$id = save_household($household);
	if ( ! $id )
	{
		$err = 1;
		$msg = 'I encountered an error entering your information. Most likely, the family name already exists in our database. Please notify our registration attendant.';
	}
	else
	{
		$household['id'] = $id;
		$_SESSION['household'] = $household;
		$h = get_households($id);
		Header('Location: reg-kid.php?household=' . $id);
	}
}
?>
<?php
// OUTPUT
include "header.php";
?>
		<form method="POST">
			<table>
				<tr>
					<td colspan=2 class="header">STEP 1: Household Information<br /><small>Please tell us about yourself first.</small></td>
				</tr>
				<tr>
					<td class="legend">Household Name</td>
					<td class="input"><input name="household_name" /><br /><small>example: Jeff and Jennifer Mikels</small></td>
				</tr>
				<tr>
					<td class="legend">Mailing Address</td>
					<td class="input"><input name="address" /></td>
				</tr>
				<tr>
					<td class="legend">City</td>
					<td class="input"><input name="city" /></td>
				</tr>
				<tr>
					<td class="legend">State</td>
					<td class="input"><input name="state" value="IN" /></td>
				</tr>
				<tr>
					<td class="legend">ZIP</td>
					<td class="input"><input name="zip" /></td>
				</tr>
				<tr>
					<td class="legend">email</td>
					<td class="input"><input name="email" /></td>
				</tr>
				<tr>
					<td class="legend">Cell Phone</td>
					<td class="input"><input name="cell_phone" /><br /><small>Please turn your cell phone ON and set it to vibrate during the service.</small></td>
				</tr>
				<tr>
					<td class="legend">Home Phone</td>
					<td class="input"><input name="home_phone" /></td>
				</tr>
			</table>
			<hr />
			<center>
				<input type="submit" name="submit" value="Continue" />
			</center>
		</form>
<?php include "footer.php"; ?>
