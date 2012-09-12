<?php
/**
 * SMFAQ
 *
 * @package		component for Joomla 2.5+
 * @version		1.7 beta 2
 * @copyright	(C)2009 - 2011 by SmokerMan (http://joomla-code.ru)
 * @license		GNU/GPL v.3 see http://www.gnu.org/licenses/gpl.html
 */

// защита от прямого доступа
defined('_JEXEC') or die('@-_-@'); 
$url = JURI::base().'index.php?option=com_slogin&amp;task=sredirect';
?>

<!DOCTYPE html>
<html>
<head>
<script type="text/javascript">

if (window.opener) {
	window.close();
	window.opener.location = '<?php echo $url; ?>'
} else {
	window.location = '<?php echo $url; ?>'
}

</script>
</head>
<body>
<h2 id="title" style="display:none;">Redirecting back to the application...</h2>
<h3 id="link"><a href="<?php echo $url; ?>">Click here to return to the application.</a></h3>
<script type="text/javascript">
document.getElementById('title').style.display = '';
document.getElementById('link').style.display = 'none';
</script>
</body>
</html>


