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


class ModuleCalendarRegistrations extends Events
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_calendar_registrations';
    
    
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### EVENT REGISTRATIONS ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = $this->Environment->script.'?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }
        
        $this->cal_calendar = $this->sortOutProtected(deserialize($this->cal_calendar, true));
        
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
        $this->import('String');
        $this->arrEvents = array();
        $arrCalendars = array();
        
        $arrUnregister = array();
        $blnReload = false;
        if ($this->Input->post('FORM_SUBMIT') == 'tl_calendar_registrations' && is_array($this->Input->post('events')))
        {
            $this->import('CalendarRegistration');
            $arrUnregister = $this->Input->post('events');
            $blnReload = true;
        }
        
        $objEvents = $this->Database->execute("SELECT e.*, r.id AS reg_id FROM tl_calendar_memberregistration r LEFT JOIN tl_calendar_events e ON r.pid=e.id WHERE r.disable='' AND r.member={$this->User->id} AND e.pid IN (" . implode(',', $this->cal_calendar) . ")" . ($this->cal_pastEvents ? '' : " AND e.startTime > ".time()));
        
        while( $objEvents->next() )
        {
            if (in_array($objEvents->id, $arrUnregister))
            {
                $this->Database->query("UPDATE tl_calendar_memberregistration SET disable='1' WHERE id=".$objEvents->reg_id);
            }
            else
            {
                $arrCalendars[] = $objEvents->pid;
                $this->addEvent($objEvents, $objEvents->startTime, $objEvents->endTime, ''/*$strUrl*/, $objEvents->startDate/*$intStart*/, $objEvents->endDate/*$intEnd*/, $objEvents->pid);
            }
        }
        
        if ($blnReload)
        {
            $this->reload();
        }
        
        // HOOK: modify result set
        if (isset($GLOBALS['TL_HOOKS']['getAllEvents']) && is_array($GLOBALS['TL_HOOKS']['getAllEvents']))
        {
            foreach ($GLOBALS['TL_HOOKS']['getAllEvents'] as $callback)
            {
                $this->import($callback[0]);
                $this->arrEvents = $this->$callback[0]->$callback[1]($this->arrEvents, array_unique($arrCalendars), 0, 0, $this);
            }
        }
        
        $this->Template->events = $this->arrEvents;
        $this->Template->action = ampersand($this->Environment->request);
        $this->Template->formSubmit = 'tl_calendar_registrations';
    }
}

