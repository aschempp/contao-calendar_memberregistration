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


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'cal_listParticipants';
$GLOBALS['TL_DCA']['tl_module']['palettes']['calendar_memberregistration'] = '{title_legend},name,headline,type;{config_legend},cal_calendar,cal_anonymous,cal_listParticipants;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Subpalettes
 */
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['cal_listParticipants'] = 'editable';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['cal_listParticipants'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_module']['cal_listParticipants'],
	'exclude'			=> true,
	'inputType'			=> 'checkbox',
	'eval'				=> array('submitOnChange'=>true),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cal_anonymous'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_module']['cal_anonymous'],
	'exclude'			=> true,
	'inputType'			=> 'checkbox',
	'eval'				=> array(),
);

