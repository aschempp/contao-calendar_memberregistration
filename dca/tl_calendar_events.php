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
 * Operations
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['members'] = array
(
    'label'                => &$GLOBALS['TL_LANG']['tl_calendar_events']['members'],
    'href'                => 'table=tl_calendar_memberregistration',
    'icon'                => 'member.gif',
    'button_callback'    => array('tl_calendar_events_memberregistration', 'registrationButton'),
);


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['__selector__'][] = 'register';
$GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['default'] .= ';{register_legend},register';
$GLOBALS['TL_DCA']['tl_calendar_events']['subpalettes']['register'] = 'register_until,register_limit,registered_message,register_jumpTo';

            
/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['register'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_calendar_events']['register'],
    'exclude'                => true,
    'inputType'                => 'checkbox',
    'filter'                => true,
    'eval'                    => array('submitOnChange'=>true),
);

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['register_until'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_calendar_events']['register_until'],
    'exclude'                => true,
    'inputType'                => 'text',
    'eval'                    => array('maxlength'=>10, 'rgxp'=>'date', 'tl_class'=>'w50 wizard', 'datepicker'=>$this->getDatePickerString(16)),
);

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['register_limit'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_calendar_events']['register_limit'],
    'exclude'                => true,
    'inputType'                => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>10, 'tl_class'=>'w50', 'rgxp'=>'digit'),
);
        
$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['registered_message'] = array
(
    'label'                    => &$GLOBALS['TL_LANG']['tl_calendar_events']['registered_message'],
    'exclude'                => true,
    'inputType'                => 'textarea',
    'eval'                    => array('rte'=>'tinyMCE', 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['register_jumpTo'] = array
(
    'label'            => &$GLOBALS['TL_LANG']['tl_calendar_events']['register_jumpTo'],
    'exclude'        => true,
    'inputType'        => 'pageTree',
    'eval'            => array('fieldType'=>'radio'),
);


class tl_calendar_events_memberregistration extends Backend
{
    
    public function registrationButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (!$row['register'])
            return '';
        
        if ($this->Database->execute("SELECT COUNT(*) AS total FROM tl_calendar_memberregistration WHERE pid=".$row['id'])->total == 0)
        {
            $icon = str_replace('.gif', '_.gif', $icon);
        }
            
        return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }

}

