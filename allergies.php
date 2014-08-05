<?php
// LOGIC
include "lib.php";

if (isset($_POST['submit']))
{
	// debug($_POST);
	foreach ($_POST['allergy'] as $id => $allergy)
	{
		save_allergy($allergy);
	}
	Header('Location:allergies.php');
}


// OUTPUT
$body_class = 'report';
include "header.php";
$allergies = get_allergies_with_kids();
// debug($allergies);
?>

<form method="post">
	<h2>Edit Current Allergies</h2>
	<small>Only change a label to correct spelling or otherwise keep the allergy largely the same. To totally delete an allergy from the database, simply clear the field with the allergy. To add a new allergy, type it at the bottom.</small></h2>
	<hr />
	<?php foreach ($allergies as $id => $allergy_array): ?>
		<?php $allergy = $allergy_array['label']; ?>
		<?php $children = $allergy_array['children']; ?>
		<div class="allergy-group">
			<input type="hidden" name="allergy[<?php print $id; ?>][id]" value="<?php print $id; ?>" />
			<input type="text" name="allergy[<?php print $id; ?>][label]" value="<?php print $allergy; ?>" /><br />
			<small>Kids with this allergy: <?php foreach ($children as $child) print '<span class="child">' . $child['first_name']. '&nbsp;' . $child['last_name'] . '</span>'; ?></small>
		</div>
		
	<?php endforeach; ?>
	
	<h2>Add New Allergies</h2>
	
	<?php for ($i = 1; $i<=5; $i++): ?>
		
		<div class="allergy-group">
			<input type="text" name="allergy[new_<?php print $i; ?>][label]" value="" placeholder="type new allergy here"/>
		</div>
		
	<?php endfor; ?>
	
	<div class="allergy-group">
		<input type="submit" class="smallbutton blue" name="submit" />
	</div>
</form>

</body>
</html>
