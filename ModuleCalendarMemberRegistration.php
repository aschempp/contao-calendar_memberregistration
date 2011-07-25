<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class ModuleCalendarMemberRegistration extends Events
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_calendar_memberregistration';
	
	
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### EVENT MEMBER REGISTRATION ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		$this->cal_calendar = $this->sortOutProtected(deserialize($this->cal_calendar, true));

		// Register hook for anonymous registration
		if (!FE_USER_LOGGED_IN && $this->cal_anonymous)
		{
			array_insert($GLOBALS['TL_HOOKS']['createNewUser'], 0, array(array('CalendarRegistration', 'createNewUser')));
			$GLOBALS['TL_HOOKS']['postLogin']['calendar_memberregistration'] = array('CalendarRegistration', 'postLogin');
		}

		// Return if there are no calendars or no user logged in
		if (!FE_USER_LOGGED_IN || !is_array($this->cal_calendar) || count($this->cal_calendar) < 1)
		{
			return '';
		}
			
		$this->import('FrontendUser', 'User');
		
		return parent::generate();
	}
	
	
	protected function compile()
	{
		if (!strlen($this->Input->get('events')))
		{
			return;
		}
		
		$time = time();
		
		$objEvent = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE pid IN(" . implode(',', $this->cal_calendar) . ") AND (id=? OR alias=?)" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<?) AND (stop='' OR stop>?) AND published=1" : ""))
								   ->limit(1)
								   ->execute((is_numeric($this->Input->get('events')) ? $this->Input->get('events') : 0), $this->Input->get('events'), $time, $time);
		
		if (!$objEvent->numRows || !$objEvent->register)
		{
			$this->Template = new FrontendTemplate('mod_message');
			return;
		}
		
		if ($this->Input->post('FORM_SUBMIT') == 'tl_memberregistration_'.$this->id)
		{
			$objRegistration = $this->Database->prepare("SELECT * FROM tl_calendar_memberregistration WHERE pid=? AND member=?")->execute($objEvent->id, $this->User->id);
			
			if ($objRegistration->numRows)
			{
				$this->Database->prepare("UPDATE tl_calendar_memberregistration SET tstamp=?, disable=? WHERE pid=? AND member=?")->execute($time, ($objRegistration->disable ? '' : '1'), $objEvent->id, $this->User->id);
			}
			else
			{
				$this->Database->prepare("INSERT INTO tl_calendar_memberregistration (pid,tstamp,member) VALUES (?,?,?)")->execute($objEvent->id, $time, $this->User->id);
			}
			
			$this->reload();
		}
		
		$c = 0;
		$blnRegistered = false;
		$arrParticipants = array();
		$objParticipants = $this->Database->prepare("SELECT m.*, r.tstamp FROM tl_calendar_memberregistration r LEFT JOIN tl_member m ON r.member=m.id WHERE r.pid=? AND r.disable='' ORDER BY m.lastname")->execute($objEvent->id);
		
		while( $objParticipants->next() )
		{
			if ($objParticipants->id == $this->User->id)
			{
				$blnRegistered = true;
			}
			
			$arrParticipants[] = array_merge($objParticipants->row(), array
			(
				'rowclass'		=> (($c%2 ? 'even' : 'odd') . ($c==0 ? ' row_first' : '')),
				'registerDate'	=> $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objParticipant->tstamp),
				'registerTime'	=> $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $objParticipant->tstamp),
			));
			
			$c++;
		}
		
		if (count($arrParticipants))
		{
			$arrParticipants[count($arrParticipants)-1]['rowclass'] .= ' row_last';
		}
		
		$blnRegister = true;
		if (!$blnRegistered && $objEvent->register_limit > 0 && $objEvent->register_limit <= count($arrParticipants))
		{
			$blnRegister = false;
		}
		elseif (strtotime('+1 day', ($objEvent->register_until ? $objEvent->register_until : $objEvent->startDate)) < $time)
		{
			$blnRegister = false;
		}
		
		$this->loadLanguageFile('tl_member');
		$this->Template->listParticipants = $this->cal_listParticipants ? true : false;
		$this->Template->editable = deserialize($this->editable, true);
		$this->Template->register = $blnRegister;
		$this->Template->participants = $arrParticipants;
		$this->Template->registered = $blnRegistered;
		$this->Template->registered_message = $objEvent->registered_message;
		$this->Template->register_limit = $objEvent->register_limit ? sprintf('<p class="limit">Max. %s members allowed.</p>', $objEvent->register_limit) : '';
		$this->Template->action = ampersand($this->Environment->request);
		$this->Template->formSubmit = 'tl_memberregistration_'.$this->id;
		$this->Template->id = $this->id;
		
		$GLOBALS['TL_CSS'][] = 'plugins/tablesort/css/tablesort.css';
		$GLOBALS['TL_MOOTOOLS'][] = '
<script type="text/javascript" src="plugins/tablesort/js/tablesort.js"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent(\'domready\', function() {
  new TableSort(\'memberregistration_' . $this->id . '\', \',\', \'.\');
});
//--><!]]>
</script>';
	}
}

