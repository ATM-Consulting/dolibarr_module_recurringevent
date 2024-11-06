<?php

<<<<<<< HEAD
require_once DOL_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_ROOT . '/core/modules/modAgenda.class.php';
require_once MODULE_ROOT . '/core/modules/modRecurringEvent.class.php';

require_once __DIR__ . '/helpers/EventCreator.php';

=======
>>>>>>> 3fd79b8 (fix: recurring event creation)
use PHPUnit\Framework\TestCase;

class RecurringEventsModificationTest extends TestCase
{
<<<<<<< HEAD
	private $db;
	private $user;
	private $original_event;
	private $eventCreator;

	protected function setUp(): void
	{
		global $db, $user;
		$this->db = $db;
		$this->user = $user;

		$agendaModule = new modAgenda($this->db);
		$agendaModule->init();

		$recurringEventModule = new modRecurringEvent($this->db);
		$recurringEventModule->init();

		$this->original_event = [
			'label' => 'Tested Event',
			'start_date' => date('Y-m-d H:i:s'),
			'recurrence' => 'week',
			'recurrence_type' => 'date',
			'recurrence_interval' => 3,
			'end_date' => date('Y-m-d', strtotime('+2 weeks'))
		];

		$this->eventCreator = new EventCreator();
		$this->original_event_id = $this->eventCreator->createEvent($this->original_event);
	}

	public function testUpdatingEvent()
	{
		$event = new ActionComm($this->db);
		$event->fetch($this->original_event_id);

		$this->eventCreator->setPostValues($this->original_event);

		$_POST['weekday_repeat'] = [5];

		$event->update($this->user);

		$this->assertEventExistsOnFriday($event);
		$this->assertEventNotExistsOnWednesday($event);
	}

	private function assertEventExistsOnFriday(ActionComm $event)
	{
		$nextFriday = date('Y-m-d', strtotime('next Friday'));

		$sql = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'actioncomm';
		$sql .= ' WHERE label = \'' . $this->db->escape($event->label) . '\'';
		$sql .= ' AND datep LIKE \'%' . $this->db->escape($nextFriday) . '%\'';

		$res = $this->db->query($sql);

		$this->assertGreaterThan(0, $this->db->num_rows($res));
	}

	private function assertEventNotExistsOnWednesday(ActionComm $event)
	{
		$nextWednesday = date('Y-m-d', strtotime('next Wednesday'));

		$sql = 'SELECT id FROM ' . MAIN_DB_PREFIX . 'actioncomm';
		$sql .= ' WHERE label = \'' . $this->db->escape($event->label) . '\'';
		$sql .= ' AND datep LIKE \'%' . $this->db->escape($nextWednesday) . '%\'';

		$res = $this->db->query($sql);

		$this->assertFalse(6, $this->db->num_rows($res));
	}
=======
    // TODO: Implement tests
>>>>>>> 3fd79b8 (fix: recurring event creation)
}
