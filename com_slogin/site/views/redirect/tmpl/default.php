<?php
/**
 * Social Login
 *
 * @version 	2.8.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2019. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// защита от прямого доступа
defined('_JEXEC') or die('@-_-@');

// for debug purposes: popup window shows all seance output.
// set this to false, prevent it fron autoclose and redirect,
// and allow to see slogin output
$autoclose = true;

?>

<!DOCTYPE html>
<html>
<head>

<?php if ($autoclose) { ?>
<script type="text/javascript">

if (window.opener) {
	window.close();
	window.opener.location = '<?php echo $this->url; ?>'
} else {
	window.location = '<?php echo $this->url; ?>'
}

</script>
<?php } ?>


</head>
<body>
<h2 id="title" style="display:none;">Redirecting back to the application...</h2>
<h3 id="link"><a href="<?php echo $this->url; ?>">Click here to return to the application.</a></h3>

<?php if ($autoclose) { ?>
<script type="text/javascript">
document.getElementById('title').style.display = '';
document.getElementById('link').style.display = 'none';
</script>
<?php } ?>
</body>
</html>


