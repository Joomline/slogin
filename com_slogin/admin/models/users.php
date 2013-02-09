<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');

class SloginModelUsers extends JModelList
{
    protected function populateState($ordering = null, $direction = null)
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        $input = new JInput();

        // Adjust the context to support modal layouts.
        if ($layout = $input->getString('layout', 'default')) {
            $this->context .= '.'.$layout;
        }

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $provider = $this->getUserStateFromRequest($this->context.'.filter.provider', 'filter_provider', 0, 'string');
        $this->setState('filter.provider', $provider);


        // List state information.
        parent::populateState('su.id', 'desc');
    }

	protected function getListQuery()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('su.*, u.username, u.name');
		$query->from('#__slogin_users as su');
        $query->leftJoin('#__users as u ON u.id = su.user_id');
        $query->group('su.id');
		$query->order('su.id DESC');

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->Quote('%'.$db->escape($search, true).'%');
            $query->where('(su.provider LIKE '.$search.' OR su.user_id LIKE '.$search.' OR u.username LIKE '.$search.' OR u.name LIKE '.$search.')');
        }

        $provider = $this->getState('filter.provider');
        if (!empty($provider)) {
            $query->where('su.provider = '.$db->quote($provider));
        }

		return $query;
	}


    public function getProviders() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Construct the query
        $query->select('DISTINCT `provider` AS value, `provider` AS text');
        $query->from('#__slogin_users');
        $query->order('value ASC');

        // Setup the query
        $db->setQuery($query->__toString());

        // Return the result
        return $db->loadObjectList();
    }


}
