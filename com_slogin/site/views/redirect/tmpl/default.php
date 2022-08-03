<?php
/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2020. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// защита от прямого доступа
defined('_JEXEC') or die('@-_-@');

?>

<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2 id="title" style="display:none;">Redirecting back to the application...</h2>
<a  id="link"  href="#" onclick="window.close();">Click here to close page</a>
<script type="text/javascript">
document.getElementById('title').style.display = '';
if (window.opener) {
    window.close();
    window.opener.location = '<?php echo $this->url; ?>'
} else {
    localStorage.setItem('sloginAuth', '<?php echo $this->url; ?>');
    let btn = document.getElementById('link');
    btn.click();
}
</script>
</body>
</html>


