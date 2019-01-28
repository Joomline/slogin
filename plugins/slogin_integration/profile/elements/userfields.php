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

jimport('joomla.form.formfield');

class JFormFieldUserFields extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'UserFields';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id as value, title as text')
		      ->from('#__fields')
		      ->where('context = '.$db->quote('com_users.user'))
		      ->where('type IN('.$db->quote('text').', '.$db->quote('media').', '.$db->quote('url').')')
		;
		$options = $db->setQuery($query)->loadObjectList();

		$fieldOptions = array(JHtml::_('select.option', '', JText::_('JSELECT')));
		$fieldOptions = array_merge($fieldOptions, $options);

		$query = 'SHOW COLUMNS FROM #__plg_slogin_profile';
		$profileFields = $db->setQuery($query)->loadObjectList();

		$disabledFields = array('id', 'user_id', 'slogin_id', 'provider', 'current_profile');

		$html = '<table>';
		foreach ( $profileFields as $profileField )
		{
			if(in_array($profileField->Field, $disabledFields)){
				continue;
			}

			$value = isset($this->value[$profileField->Field]) ? $this->value[$profileField->Field] : '';

			$html .= '
			<tr>
				<td>'.JText::_('PLG_SLOGIN_PROFILE_FIELD_'.strtoupper($profileField->Field)).'</td>
				<td>'.JHTML::_('select.genericlist', $fieldOptions, $this->name.'['.$profileField->Field.']', ' class="inputbox"', 'value', 'text', $value).'</td>
			</tr>';
		}
		$html .= '</table>';

		return $html;
	}
}