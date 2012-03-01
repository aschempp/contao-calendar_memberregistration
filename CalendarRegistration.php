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


class CalendarRegistration extends Frontend
{
	
	/**
	 * Add registration links to event templates
	 *
	 * @param	array
	 * @param	array
	 * @param	int
	 * @param	int
	 * @return	array
	 * @link	http://www.contao.org/hooks.html#getAllEvents
	 */
	public function getAllEvents($arrEvents, $arrCalendars, $intStart, $intEnd)
	{
		foreach( $arrEvents as $k => $v )
		{
			foreach( $v as $kk => $vv )
			{
				foreach( $vv as $kkk => $arrEvent )
				{
					if ($arrEvent['register'])
					{
						if ($arrEvent['register_jumpTo'] > 0)
						{
							$arrJump = $this->Database->execute("SELECT * FROM tl_page WHERE id=".$arrEvent['register_jumpTo'])->fetchAssoc();
							$arrEvent['register_href'] = $this->generateFrontendUrl($arrJump, '/events/'.$arrEvent['alias']);
						}
						
						$arrEvents[$k][$kk][$kkk] = $arrEvent;
					}
				}
			}
		}
		
		return $arrEvents;
	}
	
	
	/**
	 * Sign up member to an event when creating account
	 *
	 * @param	int
	 * @param	array
	 * @return	void
	 * @link	http://www.contao.org/hooks.html#createNewUser
	 */
	public function createNewUser($intId, $arrData)
	{
		$this->registerMember($intId, $this->Input->get('events'), $GLOBALS['EVENT_REGISTRATION']);
		
		// Unset postLogin Hook if autoregistration is installed
		unset($GLOBALS['TL_HOOKS']['postLogin']['calendar_memberregistration']);
	}
	
	
	/**
	 * Sign up user to an event when logging in
	 *
	 * @param	object
	 * @return	void
	 * @link	http://www.contao.org/hooks.html#postLogin
	 */
	public function postLogin($objUser)
	{
		$this->registerMember($objUser->id, $this->Input->get('events'), $GLOBALS['EVENT_REGISTRATION']);
	}
	
	
	/**
	 * Register member to an event
	 *
	 * @param	int
	 * @return	void
	 */
	public function registerMember($intMember, $varEvent, $arrModule, $blnToggle=false)
	{
		if (!is_array($arrModule['cal_calendar']) || !count($arrModule['cal_calendar']))
		{
			return;
		}

		$time = time();

		$objEvent = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE pid IN(" . implode(',', $arrModule['cal_calendar']) . ") AND (id=? OR alias=?)" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : ""))
								   ->limit(1)
								   ->execute((is_numeric($varEvent) ? $varEvent : 0), $varEvent);
		
		if ($objEvent->numRows && $objEvent->register)
		{
			// Check seat limits
			if ($objEvent->register_limit > 0)
			{
				$objRegistrations = $this->Database->execute("SELECT COUNT(*) AS total FROM tl_calendar_memberregistration WHERE disable='' AND pid=".$objEvent->id);
				
				if ($objRegistrations->total >= $objEvent->register_limit)
				{
					return false;
				}
			}
			
			// Check member already registered
			$objRegistered = $this->Database->execute("SELECT * FROM tl_calendar_memberregistration WHERE pid={$objEvent->id} AND member=".(int)$intMember);
			if ($objRegistered->numRows && $objRegistered->disable == '')
			{
				if ($blnToggle)
				{
					$blnActivate = true;
				}
				else
				{
					return false;
				}
			}
			elseif ($objRegistered->numRows && $objRegistered->disable == '1')
			{
				$blnActivate = true;
			}
			else
			{
				$blnActivate = false;
			}
			
			if (is_array($GLOBALS['TL_HOOKS']['calendarRegistration']) && count($GLOBALS['TL_HOOKS']['calendarRegistration']))
			{
				foreach( $GLOBALS['TL_HOOKS']['calendarRegistration'] as $callback )
				{
					$this->import($callback[0]);
					
					if ($this->$callback[0]->$callback[1]($objEvent->row(), $intMember, $blnActivate) === false)
					{
						return false;
					}
				}
			}
            
            // Build simple tokens to use in email templates (member data starts with "member_", event data with "event_")
            $arrSimpleTokens = array();
            $arrMember = $this->Database->execute('SELECT * FROM tl_member WHERE id='.(int)$intMember)->row();
            if (is_array($arrMember) && count($arrMember))
            {
                $this->loadDataContainer('tl_member');
                foreach ($arrMember as $k => $v)
                {
                    $arrPrepared = $this->prepareForWidget($GLOBALS['TL_DCA']['tl_member']['fields'][$k], $k, $v);
                    $arrSimpleTokens['member_' . $k] = $arrPrepared['value'];
                }
            }
            
            $arrEvent = $objEvent->row();
            $this->loadDataContainer('tl_calendar_events');
            foreach ($arrEvent as $k => $v)
            {
                $arrPrepared = $this->prepareForWidget($GLOBALS['TL_DCA']['tl_calendar_events']['fields'][$k], $k, $v);
                $arrSimpleTokens['event_' . $k] = $arrPrepared['value'];
            }    
            
			if ($blnActivate)
			{
				$this->Database->query("UPDATE tl_calendar_memberregistration SET tstamp=$time, registered=$time, disable='" . ($objRegistered->disable == '1' ? '' : '1') . "' WHERE pid=".(int)$objEvent->id." AND member=".(int)$intMember."");
			    
			    // register or unregister a member and send confirmation emails
			    $intMailTempalte = ($objRegistered->disable == '') ? $arrModule['mail_eventDeregistered'] : $arrModule['mail_eventRegistered'];
			    $objEmail = new EmailTemplate($intMailTempalte);
                $objEmail->simpleTokens = $arrSimpleTokens;
                $objEmail->sendTo($arrMember['email']);
			}
			else
			{
				$this->Database->query("INSERT INTO tl_calendar_memberregistration (tstamp,registered,pid,member) VALUES ($time,$time,".(int)$objEvent->id.",".(int)$intMember.")");
                $objEmail = new EmailTemplate($arrModule['mail_eventRegistered']);
                $objEmail->simpleTokens = $arrSimpleTokens;
                $objEmail->sendTo($arrMember['email']);			
            }
			
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Check if an event does accept registrations
	 *
	 * @param	int
	 * @return	void
	 */
	public function allowRegistrations($varEvent)
	{
		$time = time();
		$objEvent = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE id=? OR alias=?" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : ""))
								   ->limit(1)
								   ->execute((is_numeric($varEvent) ? $varEvent : 0), $varEvent);
		
		if ($objEvent->numRows && $objEvent->register)
		{
			// Check seat limits
			if ($objEvent->register_limit > 0)
			{
				$objRegistrations = $this->Database->execute("SELECT COUNT(*) AS total FROM tl_calendar_memberregistration WHERE disable='' AND pid=".$objEvent->id);
				
				if ($objRegistrations->total >= $objEvent->register_limit)
				{
					return false;
				}
			}
			
			if (is_array($GLOBALS['TL_HOOKS']['calendarRegistration']) && count($GLOBALS['TL_HOOKS']['calendarRegistration']))
			{
				foreach( $GLOBALS['TL_HOOKS']['calendarRegistration'] as $callback )
				{
					$this->import($callback[0]);
					
					if ($this->$callback[0]->$callback[1]($objEvent->row(), $intMember, $blnActivate) === false)
					{
						return false;
					}
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Delete all registration for a member when it is deleted
	 *
	 * @param	DataContainer
	 * @return	void
	 * @link	http://www.contao.org/callbacks.html#ondelete_callback
	 */
	public function deleteMember($dc)
	{
		$this->Database->query("DELETE FROM tl_calendar_memberregistration WHERE member=".$dc->id);
	}
}

