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
 * \file    class/actions_recurringevent.class.php
 * \ingroup recurringevent
 * \brief   This file is an example hook overload class file.
 * Description of the ActionsRecurringEvent class.
 */

/**
 * Class ActionsRecurringEvent
 */
class ActionsRecurringEvent
{
	/**
	 * @var DoliDb        Database handler (result of a new DoliDB)
	 */
	public $db;

	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse.
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return.
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 * @param DoliDB $db Database connector
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Overloading the doActions function: replacing the parent's function with the one below.
	 *
	 * @param array        $parameters     Hook metadatas (context, etc...)
	 * @param CommonObject $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string       $action         Current action (if set). Generally create, edit or null.
	 * @param HookManager  $hookmanager    Hook manager propagated to allow calling another hook.
	 * @return  int                        < 0 on error, 0 on success, 1 to replace standard code.
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		return 0;
	}

	/**
	 * Overloading the formObjectOptions function: replacing the parent's function with the one below.
	 *
	 * @param array        $parameters     Hook metadatas (context, etc...)
	 * @param CommonObject $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string       $action         Current action (if set). Generally create, edit or null.
	 * @param HookManager  $hookmanager    Hook manager propagated to allow calling another hook.
	 * @return  int                        < 0 on error, 0 on success, 1 to replace standard code.
	 */
	public function formObjectOptions($parameters, &$object, &$action, $hookmanager)
	{
		switch ($parameters['currentcontext']) {
			case 'externalaccesspage':
				return $this->formObjectExternalAccess($object);
			case 'actioncard':
				return $this->formObjectActionCard($object, $action);
		}

		return 0;
	}

	/**
	 * @param CommonObject $object
	 * @return void
	 */
	private function addJsToUpdateCheckedBoxes(CommonObject $object)
	{
		$isModified = !empty($object->id) ? 'true' : 'false';

		$this->resprints .= '<script type="text/javascript">';

		$this->resprints .= "let isModified = $isModified;";
		$this->resprints .= '</script>';
		$this->resprints .= '<script type="text/javascript" src="' . dol_buildpath('/recurringevent/js/select-checkbox.js.php?force_use_js=1', 1) . '"></script>';
	}

	/**
	 * Displays the recurrence form within a Dolibarr action card context.
	 *
	 * @param CommonObject $object The current object
	 * @param string       $action The current action ('create', 'edit')
	 * @return int 0 on success
	 */
	public function formObjectActionCard(CommonObject &$object, string $action): int
	{
		global $langs;

		// 1. Load recurrence data
				$recurringEvent = $this->_loadRecurringEventData($object);
				if (is_null($recurringEvent)) {
					return -1; // Error is already logged in the _loadRecurringEventData method
				}

		// 2. Handle context-specific JS (message for SCRUM)
				if ($action === 'edit') {
					$msgScrum = empty($recurringEvent->fk_actioncomm_master) ? $langs->trans("RecMasterUpdate") : $langs->trans("RecSlaveUpdate");
					print '<script type="text/javascript">';
					print "$(document).ready(function() {";
					print "  var message_div = '<tr><td colspan=\"4\"><div class=\"infobox\">" . addslashes($msgScrum) . "</div></td></tr>';";
					print "  $(\".titlefieldcreate:first\").closest('tr').before(message_div);";
					print "});";
					print '</script>';
				}

		// 3. Prepare data for the view
				$viewData = $this->_buildRecurringEventViewData($recurringEvent, $langs, [
					'is_edit'      => ($action === 'edit'),
					'layout_type'  => 'card'
				]);

		// 4. Render the HTML using the appropriate layout template
				$this->resprints = $this->_renderCardLayout($viewData);


		 $this->addJsToUpdateCheckedBoxes($recurringEvent);

		return 0;
	}
	/**
	 * Displays the recurrence form for an external access page.
	 *
	 * @param CommonObject $object The current object
	 * @return int 0 on success
	 */
	public function formObjectExternalAccess(CommonObject &$object): int
	{
		global $langs;
		$context = Context::getInstance();

		// Ensure we are in the correct context to display the form
		if ($context->controller !== 'agefodd_event_other') {
			return 0;
		}

		// 1. Load data
		$recurringEvent = $this->_loadRecurringEventData($object);
		if (is_null($recurringEvent)) {
			return -1;
		}

		// 2. Prepare data for the view
		$viewData = $this->_buildRecurringEventViewData($recurringEvent, $langs, [
			'is_edit'      => false, // The external form is never in 'disabled' mode
			'layout_type'  => 'external'
		]);

		// 3. Render the HTML
		$this->resprints = $this->_renderExternalLayout($viewData);

		return 0;
	}

	// ===================================================================
	//   HELPER METHODS)
	// ===================================================================

	/**
	 * Loads the RecurringEvent object associated with a Dolibarr object.
	 *
	 * @param CommonObject $object The parent object (e.g., ActionComm)
	 * @return RecurringEvent|null The RecurringEvent object or null on error.
	 */
	private function _loadRecurringEventData(CommonObject &$object): ?RecurringEvent
	{
		global $langs, $db;

		$langs->load('recurringevent@recurringevent');
		if (!defined('INC_FROM_DOLIBARR')) {
			define('INC_FROM_DOLIBARR', 1);
		}
		require_once __DIR__ . '/recurringevent.class.php';

		$recurringEvent = new RecurringEvent($db);
		if ($recurringEvent->fetchBy($object->id, 'fk_actioncomm') < 0) {
			dol_syslog('Error while fetching recurring event', LOG_ERR);
			$this->error = $recurringEvent->error;
			$this->errors = $recurringEvent->errors;
			return null;
		}

		// Ensure weekday_repeat is an array
		if (!is_array($recurringEvent->weekday_repeat)) {
			$recurringEvent->weekday_repeat = (array) unserialize((string) $recurringEvent->weekday_repeat);
		}

		return $recurringEvent;
	}

	/**
	 * Prepares the necessary data for rendering the forms.
	 *
	 * @param RecurringEvent $recurringEvent The recurrence object
	 * @param Translate      $langs          The translation object
	 * @param array          $options        Options like is_edit, layout_type
	 * @return array An array of data for the view
	 */
	private function _buildRecurringEventViewData(RecurringEvent $recurringEvent, Translate $langs, array $options): array
	{
		$isEdit = $options['is_edit'] ?? false;
		$layoutType = $options['layout_type'] ?? 'card';

		$data = [
			'recurringEvent' => $recurringEvent,
			'langs'          => $langs,
			'disabled'       => '',
			'hiddenFields'   => '',
			'optionsClass'   => !empty($recurringEvent->id) ? '' : ($layoutType === 'card' ? 'hideobject' : 'd-none'),
			'weekToggleJs'   => $layoutType === 'card'
				? 'if (this.value !== \'week\') { $(\'#recurring-day-of-week\').addClass(\'menuhider\'); } else { $(\'#recurring-day-of-week\').removeClass(\'menuhider\'); }'
				: 'if (this.value !== \'week\') { $(\'#recurring-day-of-week\').addClass(\'d-none\'); } else { $(\'#recurring-day-of-week\').removeClass(\'d-none\'); }'
		];

		if ($isEdit) {
			$data['disabled'] = 'disabled';
			if (!empty($recurringEvent->id)) {
				$hiddenFields = '<input type="hidden" name="is_recurrent" value="on">';
				$hiddenFields .= '<input type="hidden" name="frequency" value="' . $recurringEvent->frequency . '">';
				$hiddenFields .= '<input type="hidden" name="frequency_unit" value="' . $recurringEvent->frequency_unit . '">';
				if (!empty($recurringEvent->weekday_repeat)) {
					foreach ($recurringEvent->weekday_repeat as $dayValue) {
						$hiddenFields .= '<input type="hidden" name="weekday_repeat[]" value="' . $dayValue . '">';
					}
				}
				$hiddenFields .= '<input type="hidden" name="end_type" value="' . $recurringEvent->end_type . '">';
				if (!empty($recurringEvent->end_date)) {
					$hiddenFields .= '<input type="hidden" name="end_date" value="' . date('Y-m-d', $recurringEvent->end_date) . '">';
				}
				if (!empty($recurringEvent->end_occurrence)) {
					$hiddenFields .= '<input type="hidden" name="end_occurrence" value="' . $recurringEvent->end_occurrence . '">';
				}
				$data['hiddenFields'] = $hiddenFields;
			}
		}

		return $data;
	}

	/**
	 * Renders the form's HTML using a "table" layout.
	 *
	 * @param array $data View data
	 * @return string The form's HTML
	 */
	private function _renderCardLayout(array $data): string
	{
		/** @var RecurringEvent $recurringEvent */
		$recurringEvent = $data['recurringEvent'];
		$langs = $data['langs'];
		$disabled = $data['disabled'];
		$hiddenFields = $data['hiddenFields'];
		$optionsClass = $data['optionsClass'];

		$daysOfWeek = [
			1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday'
		];
		$daysCheckboxes = '';
		foreach ($daysOfWeek as $value => $day) {
			$checked = in_array($value, $recurringEvent->weekday_repeat) ? 'checked' : '';
			$id = 'customCheck' . substr($day, 0, 3);
			$daysCheckboxes .= '
           <div class="form-check custom-control custom-checkbox">
               <input type="checkbox" ' . $checked . ' class="custom-control-input" id="' . $id . '" name="weekday_repeat[]" value="' . $value . '" ' . $disabled . '>
               <label class="custom-control-label" for="' . $id . '">' . $langs->trans('RecurringEvent' . $day . 'Short') . '</label>
           </div>';
			if ($value == 4) { // Split into two columns
				$daysCheckboxes .= '</div><div class="pull-left minwidth100">';
			}
		}

		return '
		   <tr class="trextrafieldseparator trextrafieldseparator_recurringevent_start"><td colspan="2"><strong>' . $langs->trans('RecurringEventSeparatorStart') . '</strong></td></tr>
		   <tr class="recurringevent">
			   <td><b>' . $langs->trans('RecurringEventDefineEventAsRecurrent') . '</b></td>
			   <td colspan="3">
				   <input onchange="$(\'.recurring-options\').toggleClass(\'hideobject\')" name="is_recurrent" type="checkbox" class="custom-control-input" ' . (!empty($recurringEvent->id) ? 'checked' : '') . ' ' . $disabled . '>
				   ' . $hiddenFields . '
			   </td>
		   </tr>
		   <tr class="recurringevent recurring-options ' . $optionsClass . '">
			   <td>' . $langs->trans('RecurringEventRepeatEventEach') . '</td>
			   <td colspan="3">
				   <input type="number" class="form-control maxwidth50" value="' . (!empty($recurringEvent->id) ? $recurringEvent->frequency : 1) . '" name="frequency" size="4" ' . $disabled . ' />
				   <select name="frequency_unit" class="custom-select d-block w-100" onchange="' . $data['weekToggleJs'] . '" ' . $disabled . '>
					   <option value="day" ' . (($recurringEvent->frequency_unit ?? '') === 'day' ? 'selected' : '') . '>' . $langs->trans('RecurringEventRepeatEventEachDay') . '</option>
					   <option value="week" ' . (empty($recurringEvent->id) || ($recurringEvent->frequency_unit ?? '') === 'week' ? 'selected' : '') . '>' . $langs->trans('RecurringEventRepeatEventEachWeek') . '</option>
					   <option value="month" ' . (($recurringEvent->frequency_unit ?? '') === 'month' ? 'selected' : '') . '>' . $langs->trans('RecurringEventRepeatEventEachMonth') . '</option>
					   <option value="year" ' . (($recurringEvent->frequency_unit ?? '') === 'year' ? 'selected' : '') . '>' . $langs->trans('RecurringEventRepeatEventEachYear') . '</option>
				   </select>
			   </td>
		   </tr>
		   <tr id="recurring-day-of-week" class="recurringevent recurring-options ' . $optionsClass . '">
			   <td>' . $langs->trans('RecurringEventRepeatThe') . '</td>
			   <td colspan="3">
				   <div class="pull-left minwidth100">' . $daysCheckboxes . '</div>
			   </td>
		   </tr>
		   <tr class="recurringevent recurring-options ' . $optionsClass . '">
			   <td>' . $langs->trans('RecurringEventFinishAt') . '</td>
			   <td colspan="3">
				   <div class="col-sm-10">
					   <div class="form-inline mb-3">
						   <input class="form-check-input" type="radio" name="end_type" id="end_type_date" value="date" ' . (empty($recurringEvent->id) || ($recurringEvent->end_type ?? '') === 'date' ? 'checked' : '') . ' ' . $disabled . '>
						   <label class="form-check-label" for="end_type_date">' . $langs->trans('RecurringEventThe') . '</label>
						   <input type="date" class="form-control ml-2" name="end_date" value="' . (!empty($recurringEvent->end_date) ? date('Y-m-d', $recurringEvent->end_date) : '') . '" onchange="$(\'#end_type_date\').prop(\'checked\', true)" ' . $disabled . ' />
					   </div>
					   <div class="form-inline">
						   <input class="form-check-input" type="radio" name="end_type" id="end_type_occurrence" value="occurrence" ' . (($recurringEvent->end_type ?? '') === 'occurrence' ? 'checked' : '') . ' ' . $disabled . '>
						   <label class="form-check-label" for="end_type_occurrence">' . $langs->trans('RecurringEventAfter') . '</label>
						   <input type="number" class="form-control mx-2 col-2 maxwidth50" size="2" name="end_occurrence" value="' . ($recurringEvent->end_occurrence ?? '') . '" onchange="$(\'#end_type_occurrence\').prop(\'checked\', true)" ' . $disabled . ' />
						   ' . $langs->trans('RecurringEventoccurrences') . '
					   </div>
				   </div>
			   </td>
		   </tr>
		   <tr class="trextrafieldseparator trextrafieldseparator_recurringevent_end"><td colspan="2"></td></tr>
		   ';
	}

	/**
	 * Renders the form's HTML using a "div" (Bootstrap) layout.
	 *
	 * @param array $data View data
	 * @return string The form's HTML
	 */
	private function _renderExternalLayout(array $data): string
	{
		/** @var RecurringEvent $recurringEvent */
		$recurringEvent = $data['recurringEvent'];
		$langs = $data['langs'];
		$optionsClass = $data['optionsClass'];

		$daysOfWeek = [
			1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday'
		];
		$daysCheckboxesCol1 = '';
		$daysCheckboxesCol2 = '';
		foreach ($daysOfWeek as $value => $day) {
			$checked = in_array($value, $recurringEvent->weekday_repeat) ? 'checked' : '';
			$id = 'customCheck' . substr($day, 0, 3);
			$checkboxHtml = '
           <div class="form-check custom-control custom-checkbox">
               <input type="checkbox" ' . $checked . ' class="custom-control-input" id="' . $id . '" name="weekday_repeat[]" value="' . $value . '">
               <label class="custom-control-label" for="' . $id . '">' . $langs->trans('RecurringEvent' . $day . 'Short') . '</label>
           </div>';
			if ($value <= 4) {
				$daysCheckboxesCol1 .= $checkboxHtml;
			} else {
				$daysCheckboxesCol2 .= $checkboxHtml;
			}
		}

		return '
	   <div class="form-row my-3">
		   <div class="custom-control custom-checkbox">
			   <input onchange="$(\'#recurring-options\').toggleClass(\'d-none\')" id="toggle-recurrence" name="is_recurrent" type="checkbox" class="custom-control-input" ' . (!empty($recurringEvent->id) ? 'checked' : '') . '>
			   <label class="custom-control-label" for="toggle-recurrence">' . $langs->trans('RecurringEventDefineEventAsRecurrent') . '</label>
		   </div>
	   </div>
	   <div id="recurring-options" class="my-3 ' . $optionsClass . '">
		   <div class="form-row my-3 pl-4 align-items-center">
			   <div class="col-auto"><label>' . $langs->trans('RecurringEventRepeatEventEach') . '</label></div>
			   <div class="col-2"><input type="number" class="form-control" value="' . (!empty($recurringEvent->id) ? $recurringEvent->frequency : 1) . '" name="frequency" size="4" /></div>
			   <div class="col-auto">
				   <select name="frequency_unit" class="custom-select d-block w-100" onchange="' . $data['weekToggleJs'] . '">
					   <option value="day" ' . (($recurringEvent->frequency_unit ?? '') === 'day' ? 'selected' : '') . '>' . $langs->trans('RecurringEventRepeatEventEachDay') . '</option>
					   <option value="week" ' . (empty($recurringEvent->id) || ($recurringEvent->frequency_unit ?? '') === 'week' ? 'selected' : '') . '>' . $langs->trans('RecurringEventRepeatEventEachWeek') . '</option>
					   <option value="month" ' . (($recurringEvent->frequency_unit ?? '') === 'month' ? 'selected' : '') . '>' . $langs->trans('RecurringEventRepeatEventEachMonth') . '</option>
					   <option value="year" ' . (($recurringEvent->frequency_unit ?? '') === 'year' ? 'selected' : '') . '>' . $langs->trans('RecurringEventRepeatEventEachYear') . '</option>
				   </select>
			   </div>
		   </div>
		   <fieldset id="recurring-day-of-week" class="form-group pl-4">
			   <div class="row">
				   <legend class="col-form-label col-sm-2 pt-0">' . $langs->trans('RecurringEventRepeatThe') . '</legend>
				   <div class="col-sm-3">' . $daysCheckboxesCol1 . '</div>
				   <div class="col-sm-3">' . $daysCheckboxesCol2 . '</div>
			   </div>
		   </fieldset>
		   <fieldset class="form-group pl-4">
			   <div class="row">
				   <legend class="col-form-label col-sm-2">' . $langs->trans('RecurringEventFinishAt') . '</legend>
				   <div class="col-sm-10">
					   <div class="form-inline mb-3">
						   <input class="form-check-input" type="radio" name="end_type" id="end_type_date" value="date" ' . (empty($recurringEvent->id) || ($recurringEvent->end_type ?? '') === 'date' ? 'checked' : '') . '>
						   <label class="form-check-label" for="end_type_date">' . $langs->trans('RecurringEventThe') . '</label>
						   <input type="date" class="form-control ml-2" name="end_date" value="' . (!empty($recurringEvent->end_date) ? date('Y-m-d', $recurringEvent->end_date) : '') . '" onchange="$(\'#end_type_date\').prop(\'checked\', true)" />
					   </div>
					   <div class="form-inline">
						   <input class="form-check-input" type="radio" name="end_type" id="end_type_occurrence" value="occurrence" ' . (($recurringEvent->end_type ?? '') === 'occurrence' ? 'checked' : '') . '>
						   <label class="form-check-label" for="end_type_occurrence">' . $langs->trans('RecurringEventAfter') . '</label>
						   <input type="number" class="form-control mx-2 col-2" size="2" name="end_occurrence" value="' . ($recurringEvent->end_occurrence ?? '') . '" onchange="$(\'#end_type_occurrence\').prop(\'checked\', true)" />
						   ' . $langs->trans('RecurringEventoccurrences') . '
					   </div>
				   </div>
			   </div>
		   </fieldset>
	   </div>
   ';
	}
}
