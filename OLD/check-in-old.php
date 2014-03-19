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
$checkins = get_checkins($today_timestamp);


if ($_POST['submit'])
{
	$date = $_POST['date'];
	foreach ($_POST['children'] as $child_id => $data)
	{
		$data['child_id'] = $child_id;
		$data['date'] = strtotime($date);
		save_attendance($data);
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

		function json_encode(arr) {
			var parts = [];
			var is_list = (Object.prototype.toString.apply(arr) === '[object Array]');

			for(var key in arr) {
				var value = arr[key];
				if(typeof value == "object") { //Custom handling for arrays
					if(is_list) parts.push(json_encode(value)); /* :RECURSION: */
					else parts[key] = json_encode(value); /* :RECURSION: */
				} else {
					var str = "";
					if(!is_list) str = '"' + key + '":';

					//Custom handling for multiple data types
					if(typeof value == "number") str += value; //Numbers
					else if(value === false) str += 'false'; //The booleans
					else if(value === true) str += 'true';
					else str += '"' + value + '"'; //All other things
					// :TODO: Is there any more datatype we should be in the lookout for? (Functions?)

					parts.push(str);
				}
			}
			var json = parts.join(",");

			if(is_list) return '[' + json + ']';//Return numerical JSON
			return '{' + json + '}';//Return associative JSON
		}

		function log( message ) {
			$( "<div/>" ).text( message ).prependTo( "#log" );
			$( "#log" ).scrollTop( 0 );
		}

		function make_form ( child ) {
			if (child.room_id >=4) {
				msg = "<p>" + child.first_name + ' ' + child.last_name + ' is too old to check-in with this system. Please talk to someone at the registration desk if you ned to check ' + child.first_name + ' in.</p>';
				//alert(msg);
				$('#errors').html(msg);
				$('#errors').removeClass('hidden');
				return;
			}
			if (checkins[child.child_id]) {
				msg = "<p>" + child.first_name + ' ' + child.last_name + ' is already checked in for ' + $('#date').val() + '. Please talk to someone at the registration desk if you ned to check ' + child.first_name + ' in.</p>';
				$('#errors').html(msg);
				$('#errors').removeClass('hidden');
				return;
			}
			$('#errors').html('');
			$('#errors').addClass('hidden');
			rooms = ['The Backyard (infants)', 'The Park (toddlers)', 'The Village (Pre & K)', 'The City (Elementary)', '4G'];
			new_html = '<tr id="children_' + child.id + '_room_id_box"><td class="legend" id="children_' + child.id + '_room_id_label">Select ' + child.first_name + '\'s room:<br /><a class="oops" href="javascript:$(\'#children_' + child.id + '_room_id_box\').detach();"><small>OOPS! (clear this line)</small></a></td><td class="input" id="children_' + child.id + '_room_id_input">';
			new_html = new_html + '<select name="children[' + child.id + '][room_id]">';
			for (i = 0; i < 4; i++) {
				if (child.room_id == i) {
					new_html = new_html + '<option selected="selected" value="' + i + '">' + rooms[i] + '</option>';
				}
				else {
					new_html = new_html + '<option value="' + i + '">' + rooms[i] + '</option>';
				}
			}
			new_html = new_html + '</select></td></tr>';
			//html = $('#results').html();
			//$('#results').html(html + new_html);
			$('#results_table').append(new_html);
		}

		function clear_input (e) {
			alert(e);
			$(e).select();
		}

		/* ACTIONS */
		$("#child_search").focus();
		$("#child_search").select();
		$( "#child_search" ).autocomplete({
			source: function( request, response ) {
				$.ajax({
					url: "ajax.php",
					dataType: "json",
					data: {
						action: 'search',
						object: 'children',
						args: request.term
					},
					success: function( data ) {
						response( $.map( data, function( item ) {
							return {
								label: item.first_name + ' ' + item.last_name,
								value: item.first_name + ' ' + item.last_name,
								data: item
							}
						}));
					}
				});
			},
			minLength: 2,
			select: function( event, ui ) {
				/*
				log( ui.item ?
					"Selected: " + ui.item.label :
					"Nothing selected, input was " + this.value);
				*/
				make_form (ui.item.data);
				setTimeout("$('#child_search').val('').select();", 200);
			},
			open: function() {
				$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
			},
			close: function() {
				$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
			}
		});
		$( "#household_search" ).autocomplete({
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
						//alert(json_encode(data));
						response( $.map(data, function( household ) {
							return {
								label: household.household_name + ' (cell: ' + household.cell_phone + ')',
								value: household.household_name + ' (cell: ' + household.cell_phone + ')',
								data: household
							};
						}));
					}
				});
			},
			minLength: 2,
			select: function( event, ui ) {
				/*
				log( ui.item ?
					"Selected: " + ui.item.label :
					"Nothing selected, input was " + this.value);
				*/
				household = ui.item.data
				for (index in household.children) {
					child = household.children[index];
					make_form (child);
				}
				//make_form (ui.item.data);
				setTimeout("$('#household_search').val('').select();", 200);
			},
			open: function() {
				$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
			},
			close: function() {
				$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
			}
		});
	});
	</script>



		<form method="POST">
		<h2>Check-in:</h2>
		<small>Simply start typing a name in one of these boxes and select from the drop-down list.</small>
		<br />
		<div id="errors" class="hidden error" ></div>
		<table id="results_table">
			<tr>
				<td class="legend">
					Date:
				</td>
				<td class="input">
					<input class="date" id="date" name="date" value="<?php print $today; ?>" />
				</td>
			</tr>
			<tr>
				<td class="legend">
					Search for child:
				</td>
				<td class="input">
					<input id="child_search" value="First or Last Name" />
				</td>
			</tr>
			<tr>
				<td class="legend">
					Search for household:
				</td>
				<td class="input">
					<input id="household_search" value="Name or Phone Number" />
				</td>
			</tr>
			<tr>
				<td colspan=2 class="header">&nbsp;</td>
			</tr>
		</table>
		<center><input name="submit" type="submit" value="Check In" /></center>
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
