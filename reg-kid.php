<?php
// LOGIC
/*
STEPS
	If household session variable is not set, relocate to index
	If children POST data is found insert children into database and relocate to thank you page.
	If household session variable is set, show household information with form to enter children.
*/
include "lib.php";

if (! isset($_GET['household']) ) Header('Location: index.php');
$household_id = $_GET['household'];
$household = get_household_only($household_id);
if ( $_POST['submit'] )
{
	foreach ($_POST['children'] as $child) if($child['first_name']) save_child($child, $household);
	$_SESSION['msg'] = "Thank you for registering. Please double-check your information.";
	Header("Location: thank-you.php?household=$household_id");
	//Header("Location: household.php?id=$household_id");
}
?>
<?php
// OUTPUT
include "header.php";
$all_allergies = get_allergies();
?>

<script id="child_template" type="x-template">
	<table>
				<tr><td colspan=2 class="header"><span id="child_name_{{index}}">{{child_name}}</span></td></tr>

				<tr>
					<td class="legend">First Name</td>
					<td class="input"><input type="text" id="child_{{index}}_first_name" name="children[{{index}}][first_name]" onfocus="scroll_here(this);this.select();" onkeyup="$('#child_name_{{index}}').html(this.value);" /></td>
				</tr>
				<tr>
					<td class="legend">Last Name</td>
					<td class="input"><input type="text" name="children[{{index}}][last_name]" onfocus="scroll_here(this);this.select();" /></td>
				</tr>
				<tr>
					<td class="legend">Birthday</td>
					<td class="input"><input type="text" name="children[{{index}}][birthday]" class="birthdate" id="birthday{{index}}" onfocus="scroll_here(this);this.select();" placeholder="01/11/2013" /><br /><small>Please use MM/DD/YYYY format. Example: 01/11/2013</td>
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
										name="children[{{index}}][allergies][<?php print $allergy_id; ?>]"
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
					<td class="input"><input type="text" name="children[{{index}}][parent_note]" value="" onfocus="scroll_here(this);this.select();" /><br /><small>Does this child have any special needs or is there anything our teachers should be aware of?</td>
				</tr>
		</table>
		<div class="center">
		<button class="smallbutton orange" onclick="add_child();return false;">Add Another Child</button>
		</div>
</script>


		<h1>STEP TWO: Enter your children's details.</h1>

		<form method="POST">
			<div id="child_details"></div>
			<table>
				<tr><td colspan=2 class="header">&nbsp;</td></tr>
				<tr><td colspan=2><center><input type="submit" class="button green" name="submit" value="Continue" /></center></td></tr>

			</table>

		</form>

<script type="text/javascript">
function my_template(template_id, context)
{
	template = $('#' + template_id).html();
	for (key in context)
	{
		search = '{{'+key+'}}';
		search_string = new RegExp(search, 'g');
		replace_string = context[key];
		template = template.replace(search_string, replace_string);
	}
	return template;
}

function add_child()
{
	if (typeof(add_child.counter) == 'undefined') add_child.counter = 0;
	else add_child.counter += 1;

	context = {index: add_child.counter, child_name: ''};
	html = my_template('child_template', context);
	$('#child_details').append(html);
	$('#child_'+add_child.counter+'_first_name')[0].focus();
	$('.birthdate').datepicker( {changeMonth: true, changeYear: true, dateFormat: "mm/dd/yy"} );
	$('input').off('change');
	$('input[type=text]').change(function()
	{
		val = $(this).val();
		$(this).val(val.toUpperCase());
	});
}

add_child();


</script>
<?php include "footer.php"; ?>
