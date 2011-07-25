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
		// If the user does not have a username, generate it
		if (!$arrData['username'])
		{
			$strUsername = standardize($arrData['firstname']).'.'.standardize($arrData['lastname']);
			
			$objMember = $this->Database->prepare("SELECT MAX(SUBSTRING(username FROM ".(strlen($strUsername)+1).")) AS username FROM tl_member WHERE username=? OR username LIKE ?")->executeUncached($strUsername, $strUsername . '%');
			
			if ($objMember->numRows)
			{
				$strUsername .= ((int)$objMember->username + 1);
			}
			
			$this->Database->prepare("UPDATE tl_member SET username=? WHERE id=$intId")->executeUncached($strUsername);
			$this->Input->setPost('username', $strUsername);
		}
		
		// If the user does not have a password, generate it
		if (!$arrData['password'])
		{
			$_SESSION['FORM_DATA']['password'] = $this->generatePassword();
			$strSalt = substr(md5(uniqid(mt_rand(), true)), 0, 23);
			$strPassword = sha1($strSalt . $_SESSION['FORM_DATA']['password']) . ':' . $strSalt;

			$this->Database->query("UPDATE tl_member SET password='$strPassword' WHERE id=$intId");
			$this->Input->setPost('password', $strPassword);
		}
		
		$this->registerMember($intId);
		
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
		$this->registerMember($objUser->id);
	}
	
	
	/**
	 * Register member to an event
	 *
	 * @param	int
	 * @return	void
	 */
	private function registerMember($intMember)
	{
		$objEvent = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE alias=? OR id=?")->execute($this->Input->get('events'), (int)$this->Input->get('events'));
		
		if ($objEvent->numRows && $objEvent->register)
		{
			// Check seat limits
			if ($objEvents->register_limit > 0)
			{
				$objRegistrations = $this->Database->execute("SELECT COUNT(*) AS total FROM tl_calendar_memberregistration WHERE disable='' AND pid=".$objEvent->id);
				
				if ($objRegistrations->numRows >= $objEvents->register_limit)
					return false;
			}
			
			// Check member already registered
			$objRegistered = $this->Database->execute("SELECT * FROM tl_calendar_memberregistration WHERE pid={$objEvent->id} AND member=".(int)$intMember);
			if ($objRegistered->numRows && $objRegistered->disable == '')
			{
				return false;
			}
			elseif ($objRegistered->numRows && $objRegistered->disable == '1')
			{
				$blnActivate = true;
			}
			else
			{
				$blnActivate = false;
			}
			
			if (is_array($GLOBALS['TL_HOOKS']['registerAnonymous']) && count($GLOBALS['TL_HOOKS']['registerAnonymous']))
			{
				foreach( $GLOBALS['TL_HOOKS']['registerAnonymous'] as $callback )
				{
					$this->import($callback[0]);
					
					if ($this->$callback[0]->$callback[1]($objEvent->row(), $intMember, $blnActivate) === false)
					{
						return false;
					}
				}
			}
			
			if ($blnActivate)
			{
				$this->Database->query("UPDATE tl_calendar_memberregistration SET tstamp=".time().", disable='' WHERE pid=".(int)$objEvent->id." AND member=".(int)$intMember."");
			}
			else
			{
				$this->Database->query("INSERT INTO tl_calendar_memberregistration (tstamp,pid,member) VALUES (".time().",".(int)$objEvent->id.",".(int)$intMember.")");
			}
		}
	}
	
	
	/**
	 * Generate random password
	 */
	private function generatePassword($intLength=8)
	{
		$strPassword = '';
		$strChars = "0123456789abcdfghjkmnpqrstuvwxyz"; 
		$i = 0;
		
		if ($intLength > strlen($strChars))
		{
			$intLength = strlen($strChars);
		}
		
		// add random characters to $password until $length is reached
		while ($i < $intLength)
		{
			// pick a random character from the possible ones
			$char = substr($strChars, mt_rand(0, strlen($strChars)-1), 1);
		
			// we don't want this character if it's already in the password
			if (!strstr($strPassword, $char))
			{
				$strPassword .= $char;
				$i++;
			}
		}
		
		return $strPassword;
	}
}

