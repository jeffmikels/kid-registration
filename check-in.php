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

//$children = get_children();
$checkins = get_checkins($service_timestamp);
$show_thank_you = FALSE;

if ($_POST['submit'])
{
	$date = $_POST['date'];
	foreach ($_POST['children'] as $child_id => $data)
	{
		$data['child_id'] = $child_id;
		$data['date'] = strtotime($date);
		save_attendance($data, $notify = true);
		$show_thank_you = TRUE;
	}
}


?>
<?php
// OUTPUT
include "header.php";

$suggested_room_id = 0;
?>

<script type="text/javascript">
$(function() {
		/* FUNCTIONS */

		/**
		 * Converts the given data structure to a JSON string.
		 * Argument: arr - The data structure that must be converted to JSON
		 * Example: var json_string = json_encode(['e', {pluribus: 'unum'}]);
		 * 			var json = json_encode({"success":"Sweet","failure":false,"empty_array":[],"numbers":[1,2,3],"info":{"name":"Binny","site":"http:\/\/www.openjs.com\/"}});
		 * http://www.openjs.com/scripts/data/json_encode.php
		 */

		 // convert php $checkins to javascript array
		 var checkins = new Array();

		 <?php if (count($checkins) > 0) foreach ($checkins['by_children'] as $child_id=>$child) print "\t\tcheckins[ $child_id ] = " . json_encode ($child) . ";\n"; ?>
		 var rooms = <?php print json_encode(get_rooms()); ?>

		function msgBox(s)
		{
			$("#msgBox").html(s).removeClass("hidden");
		}

		// FUNCTION make_form IS CALLED WHENEVER AN AUTOCOMPLETE LIST ITEM IS SELECTED
		function make_form ( child ) {
			if (child.room_id >=4) {
				msg = "<p>" + child.first_name + ' ' + child.last_name + ' is too old to check-in with this system. Please talk to someone at the registration desk if you need to check ' + child.first_name + ' in.</p>';
				//alert(msg);
				$('#errors').append(msg);
				$('#errors').removeClass('hidden');
				return;
			}
			if (checkins[child.child_id]) {
				msg = "<p>" + child.first_name + ' ' + child.last_name + ' is already checked in for ' + $('#date').val() + '. Please talk to someone at the registration desk if you need to check ' + child.first_name + ' in.</p>';
				$('#errors').append(msg);
				$('#errors').removeClass('hidden');
				return;
			}
			$('#errors').html('');
			$('#errors').addClass('hidden');
			new_html = '<tr id="children_' + child.id + '_room_id_box"><td class="legend check-in" id="children_' + child.id + '_room_id_label"><h2>' + child.first_name + ' ' + child.last_name + '</h2>Select room:</h3></td><td class="input" id="children_' + child.id + '_room_id_input">';
			new_html = new_html + '<select name="children[' + child.id + '][room_id]">';
			for (i = 0; i < 4; i++) {
				if (child.room_id == i) {
					new_html = new_html + '<option selected="selected" value="' + i + '">' + rooms[i].name + '</option>';
				}
				else {
					new_html = new_html + '<option value="' + i + '">' + rooms[i].name + '</option>';
				}
			}
			new_html = new_html + '</select><p><a class="smallbutton blue" href="javascript:$(\'#children_' + child.id + '_room_id_box\').detach();"><small>OOPS! (clear this line)</small></a>&nbsp;<a class="smallbutton blue" href="household.php?id=' + child.household_id + '"><small>Go to Edit Form</small></a></td></tr>';
			//html = $('#results').html();
			//$('#results').html(html + new_html);
			$('#results_table').append(new_html);
		}

		function clear_input (e) {
			alert(e);
			$(e).select();
		}

		/* ACTIONS */
		$("#search_box").select();
		$( "#search_box" ).autocomplete({
			source: function( request, response ) {
				$.ajax({
					url: "ajax.php",
					dataType: "json",
					data: {
						action: 'search',
						object: 'all',
						args: request.term
					},
					success: function( data ) {
						// returns registered and unregistered households and children
						response_list = new Array();
						civicrm_ids = new Array();
						if (data.households.length > 0 ) response_list.push('-- HOUSEHOLDS ----');
						for (i in data.households)
						{
							household = data.households[i];
							if (household.civicrm_id != '') civicrm_ids.push(household.civicrm_id);
							label = '[HOUSEHOLD] ' + household.household_name;
							value = household.household_name;
							household.type = 'household';
							response_list.push( {
								label: label,
								value: value,
								data: household
							} );
						}
						if (data.children.length > 0 ) response_list.push('-- CHILDREN ------');
						for (i in data.children)
						{
							child = data.children[i];
							label = '[CHILD] ' + child.first_name + ' ' + child.last_name;
							value = child.first_name + ' ' + child.last_name;
							child.type = 'child';
							response_list.push({
								label: label,
								value: value,
								data: child
							});
						}
						if (data.imported_households.length > 0 ) response_list.push('-- AVAILABLE FOR QUICK REGISTER ----');
						for (i in data.imported_households)
						{
							household = data.imported_households[i];
							if (civicrm_ids.indexOf(household.id) >= 0) continue;
							label = '[QUICK REGISTER] ' + household.household_name;
							household.type = 'unregistered';
							response_list.push({
								label: label,
								value: label,
								data: household
							});
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
					for (index in obj.children) {
						child = obj.children[index];
						make_form (child);
					}
				}
				if (obj.type == "child")
				{
					make_form(obj);
				}
				if (obj.type == 'unregistered')
				{
					//msgBox('SHOULD JUMP OVER TO REGISTRATION PAGE.');
					window.location.href = "register.php?id=" + obj.id;
				}
				setTimeout("$('#search_box').val('').select();", 200);
			},
			open: function() {
				$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
			},
			close: function() {
				$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
			}
		}); // $("#search_box")

		<?php if ($show_thank_you): ?>

		$(".thank-you").delay(2000).slideUp(600);
		setTimeout("window.location.href='index.php'", 3000);

		<?php endif; ?>

	}); // $()
	</script>

		<?php if ($show_thank_you): ?>
		<div class="thank-you notice" />
			Your check-in has successfully been recorded. Thank you!
		</div>
		<?php endif; ?>

		<form method="POST">
		<h2>Check-in:</h2>
		<p>Simply start typing a name in one of these boxes and select from the drop-down list.
		<div id="errors" class="hidden error" ></div>
		<div id="msgBox" class="hidden message" ></div>

		<table id="results_table">
			<tr>
				<td class="legend">
					Date:
				</td>
				<td class="input">
					<input <?php if( (! $is_admin) and ($today_timestamp == $sunday_timestamp)) print 'type="hidden"';?> class="date" id="date" name="date" value="<?php print $service; ?>" />
					<?php if( (! $is_admin) and ($today_timestamp == $sunday_timestamp)) print $service; ?>

				<?php
				foreach ($service_times as $time=>$timestamp)
				{
					if ( $service_time == $time ) $color = 'red';
					else $color = 'gray';
					print "<a class=\"smallbutton $color\" href=\"check-in.php?service_time=" . urlencode($time) . "\">" . $time . "</a>";
				}
				?>

				</td>
			</tr>
			<tr>
				<td class="legend">
					Search:
				</td>
				<td class="input">
					<input id="search_box" value="" />
					<br /><small>search for your child or household by typing a few letters from your name, your child's name, your email, or your phone number.</small>
				</td>
			</tr>
			<tr>
				<td colspan=2 class="header">&nbsp;</td>
			</tr>
		</table>
		<center><input class="button green" name="submit" type="submit" value="Check In" /></center>
		</form>

		<?php if ($is_admin) : ?>

		<h2><?php print $today . " Check-ins"; ?></h2>

		<?php
		if (isset($checkins['by_children'])){
			$columns = Array('first_name', 'last_name', 'room');
			simple_table($columns, $checkins['by_children'], 'admin');
		}
		else print "none yet";
		?>

		<?php endif; ?>

<?php include "footer.php"; ?>
