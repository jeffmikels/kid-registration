<?php
// LOGIC


// STAGE ONE, NOTHING IS SET, USER IS NOT LOGGED IN, LET THEM KNOW THE PASSWORD.
if (! isset ($_SESSION['logged_in']) and ! isset($_GET['stage']))
{
include "header.php";

?>

<h1>Kidopolis Pre-Registration</h1>
<p>Thanks for pre-registering your child!

<p>This system is protected by a password you'll need to enter on the next page.
<p>The password is :: <span class="smallbutton red">register</span>
<p>Click the big button when you are ready to proceed.

<center>
<a href="pre-register.php?stage=2" class="button green">Start Registration!</a>
</center>

<?php

include "footer.php";
exit();
}


include "lib.php";


// STAGE 2 :: HOUSEHOLD REGISTRATION
if ($_GET['stage'] == '2')
{

	// first check for valid post data and redirect if necessary
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
			$_SESSION['pre_reg_household_id'] = $id;
			//$h = get_households($id);
			Header('Location: pre-register.php?stage=3&household=' . $id);
			exit();
		}
	}
	if (isset($_GET['household'])) $household = get_households($_GET['household']);

	// OUTPUT
	include "header.php";
	?>

	<script type="text/javascript">
	$(function() {
			/* FUNCTIONS */
			function msgBox(s)
			{
				$("#msgBox").html(s).removeClass("hidden");
			}


			/* ACTIONS */
			$("#household_name").select();
			$( "#household_name" ).autocomplete({
				source: function( request, response ) {
					$.ajax({
						url: "ajax.php",
						dataType: "json",
						data: {
							action: 'search',
							object: 'households',
							args: request.term
						},
						success: function( data ) {
							// returns registered and unregistered households and children
							response_list = new Array();
							civicrm_ids = new Array();
							if (data.length > 0 ) response_list.push('-- REGISTERED HOUSEHOLDS ----');
							for (i in data)
							{
								household = data[i];
								label = '[REGISTERED] ' + household.household_name;
								value = household.household_name;
								household.type = 'household';
								response_list.push( {
									label: label,
									value: value,
									data: household
								} );
							}
							response(response_list);
						}
					});
				},
				minLength: 2,
				select: function( event, ui ) {
					obj = ui.item.data;
					if (obj.type == 'household')
					{
						//document.location.href = 'household.php?id=' + obj.id;
						//msgBox('It looks like you are already registered in our system. Thanks!');
						$('#msgBox').html('It looks like you are already registered in our system. If you need to edit your details, please do so at the Kidopolis registration desk.');
						$('#msgBox').attr('title', 'A family by this name is already registered');
						$('#msgBox').dialog({
							height: 'auto',
							width: '80%',
							modal: true,
							buttons: { Ok: function() { $( this ).dialog( "close" ); } },
							close: function() {document.location.href = "pre-register.php"; }
						});
					}
					if (obj.type == 'unregistered')
					{
						household = obj;
						$("#household_name").val(household.household_name);
						$("#email").val(household.email);
						$("#home_phone").val(household.home_phone);
						$("#cell_phone").val(household.cell_phone);
						$("#address").val(household.address);
						$("#city").val(household.city);
						$("#state").val(household.state);
						$("#zip").val(household.zip);
						$("#civicrm_id").val(household.id);
						setTimeout("$('#household_name').select();", 300);
					}
					//setTimeout("$('#search_box').val('');", 200);
				},
				open: function() {
					$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
				},
				close: function() {
					$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
				}
			}); // $("#search_box")

		});
		</script>

			<div id="msgBox"><?php if (isset($msg)) print $msg; ?></div>

			<form id="reg_form" action="pre-register.php?stage=2" method="POST">

				<input type="hidden" id="household_id" name="household_id" value="<?php print $household['id']; ?>" />
				<input type="hidden" id="civicrm_id" name="civicrm_id" value="<?php print $household['id']; ?>" />
				<table>
					<tr>
						<td colspan=2 class="header">
							Family details for <u><?php print $household['household_name']; ?></u>
						</td>
					</tr>
					<tr><td colspan=2 class="break">&nbsp;</td></tr>
					</tr>

					<tr>
						<td class="legend">Household Name</td>
						<td class="input"><input id="household_name" name="household_name" value="<?php print $household['household_name']; ?>" onfocus="scroll_here(this);this.select();" /><br /><small>example: Jeff and Jennifer Mikels</small></td>
					</tr>
					<tr>
						<td class="legend">Mailing Address</td>
						<td class="input"><input id="address" name="address" value="<?php print $household['address']; ?>" onfocus="scroll_here(this);this.select();"  /></td>
					</tr>
					<tr>
						<td class="legend">City</td>
						<td class="input"><input id="city" name="city" value="<?php print $household['city']; ?>"  onfocus="scroll_here(this);this.select();" /></td>
					</tr>
					<tr>
						<td class="legend">State</td>
						<td class="input"><input id="state" name="state" value="<?php print $household['state']; ?>" onfocus="scroll_here(this);this.select();" /></td>
					</tr>
					<tr>
						<td class="legend">ZIP</td>
						<td class="input"><input id="zip" name="zip" value="<?php print $household['zip']; ?>" onfocus="scroll_here(this);this.select();" /></td>
					</tr>
					<tr>
						<td class="legend">email</td>
						<td class="input"><input id="email" name="email" value="<?php print $household['email']; ?>" onfocus="scroll_here(this);this.select();" /></td>
					</tr>
					<tr>
						<td class="legend">Notification Phone</td>
						<td class="input"><input id="cell_phone" name="cell_phone" value="<?php print $household['cell_phone']; ?>" onfocus="scroll_here(this);this.select();" /><br /><small>Please turn your cell phone ON and set it to vibrate during the service.</small></td>
					</tr>
					<tr>
						<td class="legend">Other Phone</td>
						<td class="input"><input id="home_phone" name="home_phone" value="<?php print $household['home_phone']; ?>" onfocus="scroll_here(this);this.select();" /></td>
					</tr>

					<tr><td colspan=2 class="header">&nbsp;</td></tr>
					<tr><td colspan=2><center><input type="submit" class="button green" name="submit" value="Save" /></center></td></tr>

				</table>


			</form>

	<?php

	include 'footer.php';
	exit();
}

// STAGE 3 :: Children Registration
if ($_GET['stage'] == '3' and isset($_GET['household'] ) )
{
	// first check for post data
	// now show child entry form
	$household_id = $_GET['household'];
	$household = get_household_only($household_id);
	if ( $_POST['submit'] )
	{
		foreach ($_POST['children'] as $child) if($child['first_name']) save_child($child, $household);
		Header("Location: pre-register.php?stage=4&household=$household_id");
		exit();
	}
	?>
	<?php
	// OUTPUT
	include "header.php";
	$all_allergies = get_allergies();
	?>

			<form method="POST">
				<table>
					<tr>
						<td colspan=2 class="header">
							Entering Children for the Household of <u><?php print $household['household_name']; ?></u>
							<div class="small">
								<br /><?php print $household['address']; ?>
								<br /><?php print $household['city']; ?>, <?php print $household['state']; ?> <?php print $household['zip']; ?>
								<br /><?php print $household['email']; ?>
								<br />cell: <?php print $household['cell_phone']; ?>
								<br />home: <?php print $household['home_phone']; ?>
							</div>
						</td>
					</tr>
					<tr><td colspan=2 class="break">&nbsp;</td></tr>
					</tr>

					<?php for ($i = 0; $i < 8; $i++): ?>

					<tr><td colspan=2 class="header">Child #<?php print ($i + 1); ?></td></tr>

					<tr>
						<td class="legend">First Name</td>
						<td class="input"><input name="children[<?php print $i; ?>][first_name]" onfocus="scroll_here(this);this.select();" /></td>
					</tr>
					<tr>
						<td class="legend">Last Name</td>
						<td class="input"><input name="children[<?php print $i; ?>][last_name]" onfocus="scroll_here(this);this.select();" /></td>
					</tr>
					<tr>
						<td class="legend">Birthday</td>
						<td class="input"><input name="children[<?php print $i; ?>][birthday]" class="birthdate" id="birthday<?php print $i; ?>" onfocus="scroll_here(this);this.select();" /><br /><small>Please use MM/DD/YYYY format. Example: 01/11/2008</td>
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
											name="children[<?php print $i; ?>][allergies][<?php print $allergy_id; ?>]"
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
						<td class="input"><input name="children[<?php print $i; ?>][parent_note]" value="" onfocus="scroll_here(this);this.select();" /><br /><small>Does this child have any special needs or is there anything our teachers should be aware of?</td>
					</tr>

					<?php endfor; ?>

					<tr><td colspan=2 class="header">&nbsp;</td></tr>
					<tr><td colspan=2><center><input type="submit" class="button green" name="submit" value="Continue" /></center></td></tr>

				</table>

			</form>

	<?php

	include "footer.php";

}

// STAGE 4 :: SHOW DETAILS AND THANK YOU, ALSO ALLOW EDITS
if ($_GET['stage'] == '4' and isset($_GET['household'] ) )
{
	global $rooms;
	$household = get_households($_GET['household']);

	include "header.php";
	?>

	<p> Thank you for pre-registering your family with us. When you arrive at our next Worship Gathering, you can feel free to bypass the registration desk and go straight to the relevant rooms for your children.

	<h2>Your Family Details</h2>
	<div class="shadow details" style="border:1px solid ivory; border-radius: 5px; background-color: ivory; color: navy; padding: 20px;margin-bottom:20px;">
		<h3>Name:</h3>
		<?php print $household['household_name']; ?>
		<h3>Address:</h3>
		<?php print $household['address']; ?>
		<br><?php print $household['city'] . ', ' . $household['state'] . '  ' . $household['zip']; ?>

		<h3>Notification Phone:</h3>
		<?php print $household['cell_phone']; ?>
		<h3>Other Phone:</h3>
		<?php print $household['home_phone']; ?>
		<h3>Email:</h3>
		<?php print $household['email']; ?>

		<br>To edit your details click this button: <a class="smallbutton blue" href="household.php?id=<?php print $household['id']; ?>">edit</a>
	</div>

	<h2>Your Children</h2>
	<?php foreach ($household['children'] as $child) : ?>
	<?php $room = $rooms[$child['room_id']]; ?>

	<div class="shadow details" style="border:1px solid ivory; border-radius: 5px; background-color: ivory; color: navy; padding: 20px;margin-bottom:20px;">
		<h3><?php print $child['first_name'] . ' ' . $child['last_name']; ?></h3>

		<h3>Birthday:</h3>
		<?php print $child['birthday']; ?>
		<h3>Allergies:</h3>

		<?php
		if (count($child['allergies']) == 0)
		{
			print "None";
		}
		else
		{
			print implode(',', $child['allergies']);
			/*
			foreach ($child['allergies'] as $allergy)
			{
				print "$allergy";
			}
			*/
		}
		?>

		<h3>Notes:</h3>
		<?php print $child['parent_note']; ?>
		<h3>Suggested Room: <?php print $room['name'] . " ( $room[min_age] - $room[max_age] )"; ?></h3>
		<p><small><?php print $room['description']; ?></small>
		<p><small>If the room assignment doesn't seem right, it's because this website has computed something wrong. You'll have to check-in at least once at the Kidopolis Registration desk to fix it for the future.</small>

		<p>To edit these details click this button: <a class="smallbutton blue" href="household.php?id=<?php print $household['id']; ?>">edit</a>

	</div>

	<?php endforeach; ?>

	<h2>Kidopolis Room Descriptions</h2>

	<?php foreach ($rooms as $room) : ?>

	<div class="shadow" style="border:1px solid ivory; border-radius: 5px; background-color: ivory; color: navy; padding: 20px;margin-bottom:20px;">
	<h2><?php print $room['name']; ?></h2>
	<p>Ages: <?php print "$room[min_age] - $room[max_age]"; ?>
	<p><?php print $room['description'];; ?>
	</div>

	<?php endforeach; ?>

	<?php
	include "footer.php";
}


?>