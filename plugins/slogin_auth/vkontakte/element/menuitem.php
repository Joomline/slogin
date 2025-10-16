<?php
/**
 * SLogin
 *
 * @version 	2.9.1
 * @author		SmokerMan, Arkadiy, Joomline
 * @copyright	© 2012-2023. All rights reserved.
 * @license 	GNU/GPL v.3 or later.
 */

// защита от прямого доступа
defined('_JEXEC') or die('@-_-@');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\MenuitemField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Form Field class for menu item selection with preview
 */
class JFormFieldMenuitem extends MenuitemField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'Menuitem';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     * @since   1.6
     */
    protected function getInput()
    {
        $html = parent::getInput();

        // Add preview button to show the callback URL
        $html .= '<div class="mt-2">';
        $html .= '<button type="button" class="btn btn-outline-primary" id="slogin-preview-callback-url">' . Text::_('PLG_SLOGIN_AUTH_VKONTAKTE_PREVIEW_URL') . '</button>';
        $html .= '<div id="slogin-callback-url-preview" class="alert alert-info mt-2" style="display:none;"></div>';
        $html .= '</div>';

        // Add JavaScript to handle the preview button
        $script = "
            document.addEventListener('DOMContentLoaded', function() {
                var previewBtn = document.getElementById('slogin-preview-callback-url');
                var previewDiv = document.getElementById('slogin-callback-url-preview');
                
                if (previewBtn && previewDiv) {
                    previewBtn.addEventListener('click', function() {
                        var menuItemId = document.getElementById('" . $this->id . "').value;
                        if (menuItemId) {
                            // AJAX request to get the URL
                            var xhr = new XMLHttpRequest();
                            xhr.open('GET', 'index.php?option=com_ajax&plugin=slogin_auth_vkontakte&format=json&menuItemId=' + menuItemId);
                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.success && response.data) {
                                        previewDiv.textContent = response.data;
                                        previewDiv.style.display = 'block';
                                    } else {
                                        previewDiv.textContent = '" . Text::_('PLG_SLOGIN_AUTH_VKONTAKTE_ERROR_GETTING_URL') . "';
                                        previewDiv.style.display = 'block';
                                    }
                                } else {
                                    previewDiv.textContent = '" . Text::_('PLG_SLOGIN_AUTH_VKONTAKTE_ERROR_GETTING_URL') . "';
                                    previewDiv.style.display = 'block';
                                }
                            };
                            xhr.send();
                        } else {
                            previewDiv.textContent = '" . Text::_('PLG_SLOGIN_AUTH_VKONTAKTE_SELECT_MENU_ITEM') . "';
                            previewDiv.style.display = 'block';
                        }
                    });
                }
            });
        ";

        Factory::getDocument()->addScriptDeclaration($script);

        return $html;
    }
}
