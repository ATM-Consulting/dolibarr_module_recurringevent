<?php
/* Copyright (C) 2019 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *    \file        core/triggers/interface_99_modMyodule_RecurringEventtrigger.class.php
 *    \ingroup    recurringevent
 *    \brief        Sample trigger
 *    \remarks    You can create other triggers by copying this one
 *                - File name should be either:
 *                    interface_99_modRecurringevent_Mytrigger.class.php
 *                    interface_99_all_Mytrigger.class.php
 *                - The file must stay in core/triggers
 *                - The class name must be InterfaceMytrigger
 *                - The constructor method must be named InterfaceMytrigger
 *                - The name property name must be Mytrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';

/**
 * Trigger class
 */
class InterfaceRecurringEventtrigger extends DolibarrTriggers
{
    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "demo";
        $this->description = "Triggers of this module are empty functions."
            . "They have no effect."
            . "They are provided for tutorial purpose only.";
        // 'development', 'experimental', 'dolibarr' or version
        $this->version = 'development';
        $this->picto = 'recurringevent@recurringevent';
    }

    /**
     * Trigger name
     *
     * @return        string    Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * @return        string    Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }

    /**
     * Trigger version
     *
     * @return        string    Version of trigger file
     */
    public function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') {
            return $langs->trans("Development");
        } elseif ($this->version == 'experimental') {
            return $langs->trans("Experimental");
        } elseif ($this->version == 'dolibarr') {
            return DOL_VERSION;
        } elseif ($this->version) {
            return $this->version;
        } else {
            return $langs->trans("Unknown");
        }
    }


    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "run_trigger" are triggered if file is inside directory htdocs/core/triggers
     *
     * @param string $action code
     * @param Object $object
     * @param User $user user
     * @param Translate $langs langs
     * @param conf $conf conf
     * @return int <0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function runTrigger($action, $object, $user, $langs, $conf)
    {
        //For 8.0 remove warning
        $result = $this->run_trigger($action, $object, $user, $langs, $conf);
        return $result;
    }


    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "run_trigger" are triggered if file
     * is inside directory core/triggers
     *
     * @param string $action Event action code
     * @param Object $object Object
     * @param User $user Object user
     * @param Translate $langs Object langs
     * @param conf $conf Object conf
     * @return        int                        <0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function run_trigger($action, $object, $user, $langs, $conf)
    {
        switch ($action) {
            case 'ACTION_CREATE':
            case 'ACTION_MODIFY':
                if (!empty($object->context['recurringevent_skip_trigger_create'])) {
                    break;
                }

                dol_syslog(
                    "Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id
                );

                if (GETPOSTISSET('is_recurrent')) {
                    if (!defined('INC_FROM_DOLIBARR')) {
                        define('INC_FROM_DOLIBARR', 1);
                    }
                    include_once dirname(__DIR__, 2) . '/class/recurringevent.class.php';

                    $recurringEvent = new RecurringEvent($this->db);
                    $res = $recurringEvent->fetchBy($object->id, 'fk_actioncomm');

                    if ($res < 0) {
                        $this->errors = $recurringEvent->errors;
                        return $res;
                    }

                    $recurringEvent->entity = $conf->entity;
                    $recurringEvent->fk_actioncomm = $object->id;
                    $recurringEvent->frequency = GETPOST('frequency', 'int');
                    $recurringEvent->frequency_unit = GETPOST('frequency_unit','alpha');
                    $recurringEvent->weekday_repeat = GETPOST('weekday_repeat', 'array');
                    $recurringEvent->end_type = GETPOST('end_type');
                    $recurringEvent->end_date = $this->db->jdate(GETPOST('end_date') . ' 23:59:59');
                    $recurringEvent->end_occurrence = GETPOST('end_occurrence', 'int');
                    $recurringEvent->actioncomm_datep = $object->datep;
                    $recurringEvent->actioncomm_datef = $object->datef;

                    $recurringEvent->save($user);
                } else {
                    if (!defined('INC_FROM_DOLIBARR')) {
                        define('INC_FROM_DOLIBARR', 1);
                    }
                    include_once dirname(__DIR__, 2) . '/class/recurringevent.class.php';
                    $recurringEvent = new RecurringEvent($this->db);
                    if ($recurringEvent->fetchBy($object->id, 'fk_actioncomm') > 0) {
                        $recurringEvent->delete($user);
                    }
                }
                break;
            case 'ACTION_DELETE':
                if (!defined('INC_FROM_DOLIBARR')) {
                    define('INC_FROM_DOLIBARR', 1);
                }
                include_once dirname(__DIR__, 2) . '/class/recurringevent.class.php';
                $recurringEvent = new RecurringEvent($this->db);
                if ($recurringEvent->fetchBy($object->id, 'fk_actioncomm') > 0) {
                    $recurringEvent->delete($user);
                }
                break;
            default:
                return 0;
        }

        return 0;
    }
}
