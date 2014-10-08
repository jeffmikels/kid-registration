	</div>
</div>
<?php if (!(isset($doing_pre_register) && $doing_pre_register)):?>
<div class="nav" style="margin: 600px auto 0px; font-size: 1.2em;width:100%;position:relative;">
	<div style="text-align: center;position:relative;">
	<a class="smallbutton gray" href="room_checkin.php">Room Checkin Interface</a>
	</div>
</div>
<?php endif; ?>
<div id="idle-alert-overlay">Tap any key if you need more time.</div>
</body>
<script>
	//$('input').focus(function(){scroll_here(this);});
	$('.date').datetimepicker( {changeMonth: true, changeYear: true, dateFormat: "mm/dd/yy"} );
	$('.birthdate').datepicker( {changeMonth: true, changeYear: true, dateFormat: "mm/dd/yy"} );
	$('input').attr("autocomplete", "off");
	$('input[type=text]').change(function()
	{
		val = $(this).val();
		$(this).val(val.toUpperCase());
	});

	$('.phone').change( function()
	{
		format_phone($(this).get(0));
	});
	function format_phone(e)
	{
		num = e.value;
		if (num.length == 0)
		{
			$(e).removeClass('invalid');
			return;
		}
		num = num.replace(/[^\d]/g, '');
		is_valid = 0;
		if (num.length == 7 || num.length == 10) is_valid = 1;
		if (num.length < 3) e.value = num;
		else if (num.length == 3) e.value = num + '-';
		else if (num.length < 6) e.value = num.replace(/(\d{3})(\d*)/,'$1-$2');
		else if (num.length == 6) e.value = num.replace(/(\d{3})(\d*)/,'$1-$2-');
		else if (num.length == 7) e.value = num.replace(/(\d{3})(\d*)/,'765-$1-$2');
		else if (num.length <= 10) e.value = num.replace(/(\d{3})(\d{3})(\d*)/, '$1-$2-$3');
		else e.value = num.replace(/(\d{3})(\d{3})(\d{4}).*/, '$1-$2-$3');
		if (! is_valid)
		{
			e.focus();
			$(e).addClass('invalid');
		}
		else $(e).removeClass('invalid');
	}

	function show_idle_alert()
	{
		$('#idle-alert-overlay').show();
		idle_timeout = setTimeout(go_home, 5000);
	}

	function go_home()
	{
		window.location.href = "index.php";
	}

	idle_timeout = setTimeout(show_idle_alert, 60000);

	$(document).keyup(function()
	{
		clearTimeout(idle_timeout);
		$('#idle-alert-overlay').hide();
		idle_timeout = setTimeout(show_idle_alert, 60000);
	});

</script>
</html>
