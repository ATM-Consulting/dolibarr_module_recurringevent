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
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require '../config.php';
} else {
	global $db;
}

dol_include_once('/recurringevent/class/recurringevent.class.php');

$o=new RecurringEvent($db);
$o->init_db_by_vars();

// Ensure extrafields support table exists (required by admin extrafields page).
$sqlCreateExtrafields = "CREATE TABLE IF NOT EXISTS ".$db->prefix()."recurringevent_extrafields (";
$sqlCreateExtrafields .= " rowid integer AUTO_INCREMENT PRIMARY KEY,";
$sqlCreateExtrafields .= " tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,";
$sqlCreateExtrafields .= " fk_object integer NOT NULL,";
$sqlCreateExtrafields .= " import_key varchar(14) DEFAULT NULL";
$sqlCreateExtrafields .= ") ENGINE=innodb";

$resql = $db->query($sqlCreateExtrafields);
if (!$resql) {
	dol_syslog(__FILE__ . " failed to create recurringevent_extrafields table: " . $db->lasterror(), LOG_ERR);
}
