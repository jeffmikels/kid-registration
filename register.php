<?php
/*
	TODO:
		if "room" request variable is set
			show kids who have been in that room
			show kids who are within one year of being in that room

		display kids in list sorted by active status & first name
			show first name, last name, parent's cell phone, room suggestion
			EVENTUALLY CHANGE THIS TO A JQUERY SEARCH BOX

*/

// LOGIC
include "lib.php";

$household = '';
if (isset($_GET['id']))
{
	$household = get_imported_households($_GET['id']);
	if (isset($household[0])) $household = $household[0];
	else $household = '';
}

if (isset($_POST['submit']))
{
	// enter person details into database
	$household = Array();
	$keys = explode('|', 'household_name|address|city|state|zip|email|home_phone|cell_phone|civicrm_id');
	foreach ($keys as $key) $household[$key] = $_POST[$key];
	$id = save_household($household);
	if ( ! $id )
	{
		$msg = 'I encountered an error entering your information. Most likely, the family name was blank or already exists in our database. Please notify our registration attendant.';
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
						object: 'households_and_imports',
						args: request.term
					},
					success: function( data ) {
						// returns registered and unregistered households and children
						response_list = new Array();
						household_name = $("#household_name").val();
						response_list.push({label: 'NEW REGISTRATION: ' + household_name, value: household_name, data:{type: 'unregistered'}});
						civicrm_ids = new Array();
						if (data.households.length > 0 ) response_list.push('------------------------------------');
						for (i in data.households)
						{
							household = data.households[i];
							label = '[REGISTERED] ' + household.household_name;
							value = household.household_name;
							household.type = 'household';
							if (household.civicrm_id != '') civicrm_ids.push(household.civicrm_id);
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
					document.location.href = 'household.php?id=' + obj.id;
					//msgBox('It looks like you are already registered in our system. Thanks!<ul><li><a href="check-in.php">Click here to Check In.</a></li><li><a href="household.php?id=' + obj.id + '">Click here to edit your information</a></li></ul>');
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
					setTimeout("$('#address').select();", 300);
				}

				//setTimeout("$('#search_box').val('');", 200);
			},
			open: function() {
				$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
			},
			close: function() {
				$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
				$("#household_name").trigger('change');
			}
		}); // $("#search_box")
	});
	</script>



		<form action="" method="POST">
		<div id="errors" class="hidden error" ></div>
		<div id="msgBox" class="hidden message" ></div>

			<input type="hidden" id="household_id" name="household_id" value="<?php print $household['id']; ?>" />
			<input type="hidden" id="civicrm_id" name="civicrm_id" value="<?php print $household['id']; ?>" />
			<table>
				<tr>
					<td colspan=2 class="header">
						STEP ONE: Enter your family details.
					</td>
				</tr>

				<tr>
					<td class="legend">Household Name</td>
					<td class="input"><input type="text" id="household_name" name="household_name" value="<?php print $household['household_name']; ?>" onfocus="scroll_here(this);this.select();" /><br /><small>eg: "John and Jane Smith"</small></td>
				</tr>
				<tr>
					<td class="legend">Mailing Address</td>
					<td class="input"><input type="text" id="address" name="address" value="<?php print $household['address']; ?>" onfocus="scroll_here(this);this.select();"  /></td>
				</tr>
				<tr>
					<td class="legend">City</td>
					<td class="input"><input type="text" id="city" name="city" value="<?php print $household['city']; ?>"  onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">State</td>
					<td class="input"><input type="text" id="state" name="state" value="<?php print $household['state']; ?>" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">ZIP</td>
					<td class="input"><input type="text" id="zip" name="zip" value="<?php print $household['zip']; ?>" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">email</td>
					<td class="input"><input type="text" id="email" name="email" value="<?php print $household['email']; ?>" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">Cell Phone</td>
					<td class="input"><input class="phone" type="text" id="cell_phone" name="cell_phone" value="<?php print $household['cell_phone']; ?>" onfocus="scroll_here(this);this.select();" /><br /><small>Please turn your cell phone ON and set it to vibrate during the service.</small></td>
				</tr>
				<tr>
					<td class="legend">Home Phone</td>
					<td class="input"><input class="phone" type="text" id="home_phone" name="home_phone" value="<?php print $household['home_phone']; ?>" onfocus="scroll_here(this);this.select();" /></td>
				</tr>

				<tr><td colspan=2 class="header">&nbsp;</td></tr>
				<tr><td colspan=2><center><input type="submit" class="button green" name="submit" value="Save" /></center></td></tr>

			</table>


		</form>

<?php include "footer.php"; ?>
<?php if(isset($_GET['id'])): ?>
<script>
setTimeout("$('#household_name').select()", 400);
</script>
<?php endif; ?>