<?php

/**
 * Social Login
 *
 * @version 	1.0
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// No direct access.
defined('_JEXEC') or die('(@)|(@)');

?>

<div><?php echo JText::_('COM_SLOGIN_XML_DESCRIPTION'); ?></div>
<ul>
	<li><?php echo JText::sprintf('COM_SLOGIN_COMPONENT_VERSION', $this->component['version']); ?></li>
	<li><?php echo JText::sprintf('COM_SLOGIN_MODULE_VERSION', $this->module['version']); ?></li>
</ul>
<?php $date = JFactory::getDate()->format('Y') > '2012' ? '2012 - '. JFactory::getDate()->format('Y') : '2012'?>
<div>
    &copy; <?php echo $date;?> SmokerMan, Arkadiy, Joomline
    <a target="_blank" href="http://joomline.ru/stati/53-komponenty/306-sozdanie-prilozhenij-dlja-socialnoj-avtoriziacii.html">Инструкция по настройке (Setting manual)</a>
</div>
<p></p>
    <h1>Donate (Пожертвования)</h1>
<div class="donate">
    <h2>Для России и Украины</h2>
    <table class="adminlist">
        <thead>
        <tr>
            <th width="33%">Webmoney</th>
            <th width="33%">Яндекс деньги</th>
            <th>PayPal</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td align="center">
                R187492847899<br/>
                Z346259799702
            </td>
            <td align="center">
                41001531672137
            </td>
            <td align="center">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="WKKRA6YE9K6DU">
                    <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
                    <img alt="" border="0" src="https://www.paypalobjects.com/ru_RU/i/scr/pixel.gif" width="1" height="1">
                </form>
            </td>
        </tr>
        </tbody>
    </table>



    <h2>For all the world</h2>
    <p>If you use our free products, you can support us and help create and further develop free extensions, making a donation.</p>
    <h3>PayPal</h3>
    <table class="adminlist">
        <thead>
        <tr>
            <th>
                Donate 5$
            </th>
            <th>
                Donate 10$
            </th>
            <th>
                Donate 20$
            </th>
            <th>
                Donate any amount
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td align="center">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick" /> <input type="hidden" name="hosted_button_id" value="EUPK7JC2X9D9Q" /> <input type="image" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="PayPal - The safer, easier way to pay online!" /> <img src="https://www.paypalobjects.com/ru_RU/i/scr/pixel.gif" alt="" width="1" height="1" border="0" /></form>
            </td>
            <td align="center">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick" /> <input type="hidden" name="hosted_button_id" value="4PKE57MZSXA9S" /> <input type="image" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="PayPal - The safer, easier way to pay online!" /> <img src="https://www.paypalobjects.com/ru_RU/i/scr/pixel.gif" alt="" width="1" height="1" border="0" /></form>
            </td>
            <td align="center">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick" /> <input type="hidden" name="hosted_button_id" value="CFTSDVVQP4QL6" /> <input type="image" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="PayPal - The safer, easier way to pay online!" /> <img src="https://www.paypalobjects.com/ru_RU/i/scr/pixel.gif" alt="" width="1" height="1" border="0" /></form>
            </td>
            <td align="center">
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_s-xclick" /> <input type="hidden" name="hosted_button_id" value="WKKRA6YE9K6DU" /> <input type="image" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="PayPal — The safer, easier way to pay online." /> <img src="https://www.paypalobjects.com/ru_RU/i/scr/pixel.gif" alt="" width="1" height="1" border="0" /></form>
            </td>
        </tr>
        </tbody>
    </table>
    <p>We ask that you specify in the payment, contact for feedback.</p>
</div>