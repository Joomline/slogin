<?php
/**
 * Social Login
 *
 * @version 	1.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// защита от прямого доступа
defined('_JEXEC') or die('@-_-@');

?>

<!DOCTYPE html>
<html>
<head>
<script type="text/javascript">

if (window.opener) {
	window.close();
	window.opener.location = '<?php echo $this->url; ?>'
} else {
	window.location = '<?php echo $this->url; ?>'
}

</script>
</head>
<body>
<h2 id="title" style="display:none;">Redirecting back to the application...</h2>
<h3 id="link"><a href="<?php echo $this->url; ?>">Click here to return to the application.</a></h3>
<script type="text/javascript">
document.getElementById('title').style.display = '';
document.getElementById('link').style.display = 'none';
</script>
</body>
</html>


