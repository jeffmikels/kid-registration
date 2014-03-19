<?php global $body_class; ?>
<?php if (!$body_class) $body_class = 'main'; ?>
<!DOCTYPE html>
<html>
<head>
	<title><?php print "$displayname"; ?> Registration</title>

	<?php if (isset($_GET['force_mobile'])) { ?><meta name="viewport" content="width=480" /><?php }?>

	<!-- STYLESHEETS -->
	<link href='http://fonts.googleapis.com/css?family=Rambla:400,700|Archivo+Narrow:400,700' rel='stylesheet' type='text/css'>

	<link rel="stylesheet" href="anytimec.css" type="text/css" media="all" />
	<link rel="stylesheet" type="text/css" href="css/excite-bike/jquery-ui-1.8.16.custom.css" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="stylesheet" href="handheld.css" media="handheld"/>
	<link rel="stylesheet" href="handheld.css" media="only screen and (max-device width:480px)"/>
	<?php if (isset($_GET['force_mobile'])) {?><link rel="stylesheet" href="handheld.css" media="all" /><?php }?>


	<!-- SCRIPTS -->
	<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script> -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui-1.8.16.custom.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui-timepicker-addon.js" type="text/javascript"></script>
	<script type='text/javascript' src='js/jquery.scrollTo-min.js'></script>
	<script type='text/javascript' src='js/jquery_init.js'></script>

	<script src="js/anytimec.js" type="text/javascript"></script>

	<style type="text/css">
		body { background-image: url(<?php print $page_background; ?>); }
	</style>
	<script type="text/javascript">

	function findPos(obj)
	{
		var curleft = curtop = 0;
		if (obj.offsetParent)
		{
			do
			{
					curleft += obj.offsetLeft;
					curtop += obj.offsetTop;
			}
			while (obj = obj.offsetParent);
			return [curleft,curtop];
		}
	}

	function scroll_here(obj)
	{
		return;

		pos = findPos(obj);
		//window.scrollTo(0,pos[1]-150);
		$.scrollTo(pos[1]-300, 0);
		//$.scrollTo(obj);
		setTimeout(function(){obj.select()}, 100);
	}
	</script>

</head>
<body class="<?php print $body_class;?>">
<div id="nav">
<img class="navlogo" src="<?php print $site_logo; ?>" />
<a class="admin_link" href="admin.php">&nbsp;</a>
<a class="menu-item homelink" href="index.php">Start Over</a>
</div>
<div id="page">
	<div id="main">
		<h1><a href="index.php"><img class="logo" src="<?php print $site_logo; ?>" style="vertical-align: bottom;" /></a></h1>

		<?php if ($err) : ?>
		<div class="error" id="error_box"><?php print ($err); ?></div>
		<?php endif; ?>

		<?php if ($msg) : ?>
		<div class="message" id="message_box"><?php print ($msg); ?></div>
		<?php endif; ?>
