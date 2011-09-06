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


$GLOBALS['TL_CSS'][] = 'system/modules/calendar_memberregistration/html/style.css';


/**
 * Table tl_calendar_memberregistration
 */
$GLOBALS['TL_DCA']['tl_calendar_memberregistration'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'enableVersioning'				=> true,
		'ptable'						=> 'tl_calendar_events',
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 4,
			'fields'					=> array('registered'),
			'flag'						=> 1,
			'panelLayout'				=> 'filter,limit',
			'headerFields'				=> array('title', 'startDate', 'startTime'),
			'child_record_callback'		=> array('tl_calendar_memberregistration', 'listRows'),
			'disableGrouping'			=> true,
		),
		'global_operations' => array
		(
			'csv' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_calendar_memberregistration']['csv'],
				'href'					=> 'key=exportmembers',
				'class'					=> 'header_exportmembers',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
		),
		'operations' => array
		(
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_calendar_memberregistration']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
		)
	),
	
	// Palettes
	'palettes' => array
	(
		'default'			=> '{member_legend},member;{status_legend:hide},registered,disable,participated',
	),
	
	// Fields
	'fields' => array
	(
		'member' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_calendar_memberregistration']['member'],
			'inputType'		=> 'tableLookup',
//			'foreignKey'	=> "tl_member.CONCAT(firstname, ' ', lastname, ', ', city, ' (', username, ')')",
			'eval' => array
			(
				'mandatory'				=> true,
				'tl_class'				=> 'clr',
				'foreignTable'			=> 'tl_member',
				'fieldType'				=> 'radio',
				'listFields'			=> array('firstname', 'lastname', 'city', 'email', 'available'=>("( IF((SELECT id FROM tl_calendar_memberregistration WHERE member=tl_member.id AND pid=" . ($this->Input->get('table')=='tl_calendar_memberregistration' ? "(SELECT pid FROM tl_calendar_memberregistration WHERE id=".(int)$this->Input->get('id').")" : (int)$this->Input->get('id')." AND disable=''") . "), '" . $GLOBALS['TL_LANG']['MSC']['no'] . "', '" . $GLOBALS['TL_LANG']['MSC']['yes'] . "'))")),
				'searchFields'			=> array('firstname', 'lastname', 'email'),
				'searchLabel'			=> 'Search members',
			),
			'save_callback' => array
			(
				array('tl_calendar_memberregistration', 'preventDuplicateMember'),
			),
		),
		'registered' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_calendar_memberregistration']['registered'],
			'inputType'		=> 'text',
			'default'		=> time(),
			'eval'			=> array('rgxp'=>'datim', 'datepicker'=>(method_exists($this, 'getDatePickerString') ? true : $this->getDatePickerString()), 'tl_class'=>'w50 wizard'),
		),
		'disable' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_calendar_memberregistration']['disable'],
			'filter'		=> true,
			'inputType'		=> 'checkbox',
			'eval'			=> array('tl_class'=>'w50'),
		),
		'participated' => array
		(
			'label'			=> &$GLOBALS['TL_LANG']['tl_calendar_memberregistration']['participated'],
			'filter'		=> true,
			'inputType'		=> 'checkbox',
			'eval'			=> array('tl_class'=>'w50'),
		),
	),
);


class tl_calendar_memberregistration extends Backend
{

	/**
	 * Add an image to each record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function listRows($row)
	{
		$objMember = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")->execute($row['member']);

		return sprintf('<span style="color:#b3b3b3; display:inline-block; width:20px;">%s:</span> %s %s, %s%s',
						++$GLOBALS['MEMBER_REGISTRATION_COUNT'],
						$objMember->lastname,
						$objMember->firstname,
						$objMember->city,
						($row['disable'] ? (' <span style="color:#b3b3b3; padding-left:3px;">['.$GLOBALS['TL_LANG']['tl_calendar_memberregistration']['disable'][0].']</span>') : ''));
	}
	
	
	public function exportCSV($dc)
	{
		$this->import('String');
		
		$strQuery = "SELECT r.disable AS status_disabled, r.tstamp AS register_date, (SELECT title FROM tl_calendar_events WHERE id=r.pid) AS event_title, m.* FROM " . $dc->table . " r LEFT JOIN tl_member m ON r.member=m.id WHERE pid=?";
		
		if (isset($_SESSION['BE_DATA']['filter'][$dc->table . '_' . $dc->id]['disable']))
		{
			$strQuery .= " AND r.disable=?";
		}
		
		if ($_SESSION['BE_DATA']['filter'][$dc->table . '_' . $dc->id]['limit'] && $_SESSION['BE_DATA']['filter'][$dc->table . '_' . $dc->id]['limit'] != 'all')
		{
			$strQuery .= ' LIMIT '.$_SESSION['BE_DATA']['filter'][$dc->table . '_' . $dc->id]['limit'];
		}
		
		$objRegistrations = $this->Database->prepare($strQuery)->execute($dc->id, $_SESSION['BE_DATA']['filter'][$dc->table . '_' . $dc->id]['disable']);
		
		
		// CSV ausgeben
		$strCSV = "\"Registered\"\t\"Member ID\"\t\"Lastname\"\t\"Firstname\"\t\"Company\"\t\"Street\"\t\"Postal\"\t\"City\"\t\"Function\"\t\"Email\"\t\"Status\"\n";
		
		while( $objRegistrations->next() )
		{
			// setze Text "abgemeldet" wenn Status == 1
			$disabled = '1' == $objRegistrations->status_disabled ? "abgemeldet" : "";
			
			$strCSV .= sprintf("\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\t\"%s\"\n",
								$this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objRegistrations->register_date),
								$objRegistrations->username,
								$objRegistrations->lastname,
								$objRegistrations->firstname,
								$objRegistrations->company,
								$objRegistrations->street,
                $objRegistrations->postal,
                $objRegistrations->city,
								$objRegistrations->ext_function,
								$objRegistrations->email,
								$disabled);
		}
		
		header('Content-Type: text/plain, charset=UTF-8; encoding=UTF-8');
		header("Content-Disposition: attachment; filename=" . $this->String->decodeEntities($objRegistrations->event_title) . ".csv");
		echo chr(255).chr(254).mb_convert_encoding($strCSV, 'UTF-16LE', 'UTF-8');
		
		exit;
	}
	
	
	public function preventDuplicateMember($varValue, $dc)
	{
		$objResult = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_calendar_memberregistration WHERE member=? AND pid=? AND id!=?")->executeUncached($varValue, $dc->activeRecord->pid, $dc->id);
		
		if ($objResult->total > 0)
		{
			throw new Exception($GLOBALS['TL_LANG']['MSC']['memberRegistrationDuplicate']);
		}
		
		return $varValue;
	}
}

