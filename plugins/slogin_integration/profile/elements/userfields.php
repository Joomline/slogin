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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldUserFields extends FormField
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
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id as value, title as text')
		      ->from('#__fields')
		      ->where('context = '.$db->quote('com_users.user'))
		      ->where('type IN('.$db->quote('text').', '.$db->quote('media').', '.$db->quote('url').')')
		;
		$options = $db->setQuery($query)->loadObjectList();

		$fieldOptions = array(HTMLHelper::_('select.option', '', Text::_('JSELECT')));
		$fieldOptions = array_merge($fieldOptions, $options);

		$query = 'SHOW COLUMNS FROM #__plg_slogin_profile';
		$profileFields = $db->setQuery($query)->loadObjectList();

		$disabledFields = array('id', 'user_id', 'slogin_id', 'provider', 'current_profile');

		$html = '<div class="table-responsive">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th class="col-md-6">'.Text::_('PLG_SLOGIN_PROFILE_FIELD_LABEL').'</th>
						<th class="col-md-6">'.Text::_('PLG_SLOGIN_PROFILE_FIELD_MAPPING').'</th>
					</tr>
				</thead>
				<tbody>';
		foreach ( $profileFields as $profileField )
		{
			if(in_array($profileField->Field, $disabledFields)){
				continue;
			}

			$value = isset($this->value[$profileField->Field]) ? $this->value[$profileField->Field] : '';

			$html .= '
				<tr>
					<td class="col-md-6">
						<label for="'.$this->name.'['.$profileField->Field.']" class="form-label">
							'.Text::_('PLG_SLOGIN_PROFILE_FIELD_'.strtoupper($profileField->Field)).'
						</label>
					</td>
					<td class="col-md-6">
						'.HTMLHelper::_('select.genericlist', $fieldOptions, $this->name.'['.$profileField->Field.']', 'class="form-select"', 'value', 'text', $value).'
					</td>
				</tr>';
		}
		$html .= '
				</tbody>
			</table>
		</div>';

		return $html;
	}
}