<?php

require_once DOL_ROOT . '/comm/action/class/actioncomm.class.php';

use PHPUnit\Framework\TestCase;

class EventCreator extends TestCase
{

	public function __construct()
	{
		global $db, $user;
		$this->db = $db;
		$this->user = $user;
	}

	public function createEvent(array $event): int
	{
		$newEvent = new ActionComm($this->db);

		$newEvent->datep = $event['start_date'];
		$newEvent->label = $event['label'];
		$newEvent->userownerid = 1;
		$newEvent->type_code = 1;

		$this->setPostValues($event);

		$res = $newEvent->create($this->user);

		if ($res < 0) {
			throw new Exception(
				'error ' . $res . ' : ' . $newEvent->error ?? $newEvent->errors[0] ?? 'Unknown error',
				0,
				new Exception($newEvent->db->error(), 500, new Exception($newEvent->db->lastquery(), 500))
			);
		}

		return $newEvent->id;
	}

	public function setPostValues(array $event)
	{
		$_POST['is_recurrent'] = 1;
		$_POST['frequency'] = $event['recurrence_interval'];
		$_POST['frequency_unit'] = $event['recurrence'];
		$_POST['end_type'] = $event['recurrence_type'];
		$_POST['end_date'] = $event['end_date'] ?? null;
		$_POST['end_occurrence'] = $event['occurrences'] ?? null;
		$_POST['weekday_repeat'] = $event['weekday_repeat'] ?? [];
	}
}
