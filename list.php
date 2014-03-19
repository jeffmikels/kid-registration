<?php
// LOGIC
include "lib.php";
$registrants = get_households_and_children();

// DATABASE CLEANING
// if we deleted a child, a household might exist with no children registered household has no children registered, delete that household too.
$sql = "DELETE FROM households WHERE households.id NOT IN (SELECT household_id FROM household2child);";
$db->query($sql);

?>
<?php
// OUTPUT
include "header.php";
?>

	<?php //debug($registrants); ?>

		<table class="admin">
			<tr>
				<th>Household</th>
				<th>Phone</th>
				<th>Kids</th>
			</tr>

			<?php $even_or_odd = "even"; ?>
			<?php foreach ($registrants as $family) : ?>

			<?php
				if ($even_or_odd == "even") $even_or_odd = "odd";
				else $even_or_odd = "even";
			?>

			<tr class="<?php print $even_or_odd;?>">
				<td>
					<a href="household.php?id=<?php print $family['household_id']; ?>"><?php print $family['household_name']; ?></a>	<br />
					<?php print $family['address']; ?><br />
					<?php print $family['city'] . ', ' . $family['state'] . ' ' . $family['zip']; ?><br />
					<a href="mailto:<?php print $famiy['email']; ?>"><?php print $famiy['email']; ?></a>
				</td>
				<td>
					cell: <?php print $family['cell_phone']; ?><br />
					home: <?php print $family['home_phone']; ?>
				</td>
				<td>
					<table>
						<tr>
							<th>Name</th>
							<th>Age</th>
							<th>Allergies</th>
						</tr>

						<?php foreach ($family['children'] as $child): ?>

						<tr>
							<td><?php print $child['first_name']; ?></td>
							<td><?php print $child['age']; ?></td>
							<td><?php print $child['allergies']; ?></td>
						</tr>

						<?php endforeach; ?>

					</table>
				</td>
			</tr>

			<?php endforeach; ?>

		</table>
		</pre>

	</div>
</body>
</html>
