<?php

// Require needed files
require_once DOL_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_ROOT . '/core/modules/modAgenda.class.php';
require_once MODULE_ROOT . '/core/modules/modRecurringEvent.class.php';

use PHPUnit\Framework\TestCase;

class RecurringEventsCreationTest extends TestCase
{
	protected function setUp(): void
	{
		global $db, $user;
		$this->db = $db;
		$this->user = $user;

		$agendaModule = new modAgenda($this->db);
		$agendaModule->init();

		$recurringEventModule = new modRecurringEvent($this->db);
		$recurringEventModule->init();

		$this->event = [
			'label' => 'Event',
			'start_date' => date('Y-m-d H:i:s'),
		];

		$this->recurrences_types = [
			'day',
			'week',
//			'month'
		];

		$this->sampleEventWithEndDate = [
			'label' => $this->event['label'],
			'start_date' => $this->event['start_date'],
			'recurrence' => 'week',
			'recurrence_type' => 'date',
			'recurrence_interval' => 2,
			'end_date' => date('Y-m-d', strtotime($this->event['start_date'] . '+1 days'))
		];

		$this->sampleEventWithOccurences = [
			'label' => $this->event['label'],
			'start_date' => $this->event['start_date'],
			'recurrence' => 'week',
			'recurrence_type' => 'occurrences',
			'recurrence_interval' => 2,
			'occurrences' => 3
		];
	}

	public function testCreatingEventsWithEndDate()
	{
		foreach ($this->recurrences_types as $recurrence) {
			$this->createEventWithEndDate([
				'recurrence' => $recurrence,
				'recurrence_type' => 'date'
			]);
		}
	}

	private function createEventWithEndDate(array $event)
	{
		foreach ($this->getIntervals() as $interval) {
			foreach ($this->getOccurencesQty() as $occurences) {
				$this->createEvent([
					'label' => $this->event['label'] . '-' . uniqid(),
					'start_date' => date('Y-m-d', strtotime('tomorrow')),
					'recurrence' => $event['recurrence'],
					'recurrence_type' => $event['recurrence_type'],
					'recurrence_interval' => $interval,
					'weekday_repeat' => [2, 3, 4],
					'end_date' => date(
						'Y-m-d',
						strtotime(
							$this->event['start_date'] . '+' . $occurences * $interval . ' ' . $event['recurrence'] . 's'
						)
					)
				]);
			}
		}
	}

	private function getIntervals(): array
	{
		return [3];
	}

	private function getOccurencesQty(): array
	{
		return [3];
	}

	private function createEvent(array $recurringEvent)
	{
		echo 'start : ' . $recurringEvent['start_date'] . PHP_EOL;
		echo 'day of week : ' . date('w', strtotime($recurringEvent['start_date'])) . PHP_EOL;
		echo 'end : ' . ($recurringEvent['end_date'] ?? '') . PHP_EOL;
		echo 'occurences : ' . ($recurringEvent['occurrences'] ?? '') . PHP_EOL . PHP_EOL;
		$this->createRecurringEvent($recurringEvent);

		// We control that every event has been created
		$this->assertAllEventsExists($recurringEvent);
	}

	private function createRecurringEvent(array $event)
	{
		$newEvent = new ActionComm($this->db);
		$newEvent->datep = $event['start_date'];
		$newEvent->label = $event['label'];
		$newEvent->userownerid = 1;
		$newEvent->type_code = 1;

		$_POST['is_recurrent'] = 1;
		$_POST['frequency'] = $event['recurrence_interval'];
		$_POST['frequency_unit'] = $event['recurrence'];
		$_POST['end_type'] = $event['recurrence_type'];
		$_POST['end_date'] = $event['end_date'] ?? null;
		$_POST['end_occurrence'] = $event['occurrences'] ?? null;
		$_POST['weekday_repeat'] = $event['weekday_repeat'];


		$res = $newEvent->create($this->user);

<<<<<<< HEAD
=======
		$_POST = [];

>>>>>>> 3fd79b8 (fix: recurring event creation)
		if ($res < 0) {
			throw new Exception(
				'error ' . $res . ' : ' . $newEvent->error ?? $newEvent->errors[0] ?? 'Unknown error',
				0,
				new Exception($newEvent->db->error(), 500, new Exception($newEvent->db->lastquery(), 500))
			);
		}
<<<<<<< HEAD

		return $newEvent;
=======
>>>>>>> 3fd79b8 (fix: recurring event creation)
	}

	private function assertAllEventsExists(array $event)
	{
		if ($event['recurrence_type'] === 'date') {
			$occurrences = $this->countOccurrences($event);
		}

		if ($event['recurrence_type'] === 'occurrences') {
			$occurrences = $event['occurrences'] ?? 1;
		}

		if (in_array($event['recurrence_interval'], $this->getWrongIntervals())) {
			$this->assertEventExists([
				'label' => $event['label'],
				'recurrence' => $event['recurrence'],
				'recurrence_interval' => $event['recurrence_interval'],
				'date' => date(
					'Y-m-d',
					strtotime(
						$event['start_date']
					)
				)
			]);

			$this->assertEventNotExist([
				'label' => $event['label'],
				'recurrence' => $event['recurrence'],
				'recurrence_interval' => $event['recurrence_interval'],
				'date' => date(
					'Y-m-d',
					strtotime(
						$event['start_date'] . '+1 ' . $event['recurrence']
					)
				)
			]);
			return;
		}

		for ($o = 0; $o <= $occurrences; $o++) {
			if ($event['recurrence'] === 'week' && !empty($event['weekday_repeat'])) {
				foreach ([1, 2, 3, 4, 5, 6, 0] as $weekday) {
					$base_date = strtotime('last sunday', strtotime($event['start_date']));
					$event_day = date(
						'Y-m-d',
						strtotime(
							date(
								'Y-m-d',
								$base_date
							) . '+' . $o * $event['recurrence_interval'] . ' ' . $event['recurrence']
							. ' + ' . $weekday . ' days'
						)
					);

					echo 'Test if event exists on ' . $event_day . PHP_EOL;
					$event_start_day = date('Y-m-d', strtotime($event['start_date']));
					echo $event_start_day . PHP_EOL;

					if ($event_day < $event_start_day) {
						echo 'Event day should not exist before start date' . PHP_EOL;
						$this->assertEventNotExist([
							'label' => $event['label'],
							'recurrence' => $event['recurrence'],
							'recurrence_interval' => $event['recurrence_interval'],
							'date' => $event_day
						]);
						continue;
					}

					if ($event['recurrence_type'] == 'date' && $event_day > $event['end_date']) {
						echo 'Event day should not exist after end date' . PHP_EOL;
						$this->assertEventNotExist([
							'label' => $event['label'],
							'recurrence' => $event['recurrence'],
							'recurrence_interval' => $event['recurrence_interval'],
							'date' => $event_day
						]);
						continue;
					}


					if ((!in_array($weekday, $event['weekday_repeat']) && $event_day != $event_start_day)
					) {
						echo 'Event should not exist if weekday is not in weekday_repeat unless it is the start date' . PHP_EOL;
						$this->assertEventNotExist([
							'label' => $event['label'],
							'recurrence' => $event['recurrence'],
							'recurrence_interval' => $event['recurrence_interval'],
							'date' => $event_day
						]);
						continue;
					}

					if ($o == $occurrences && $event['recurrence_type'] === 'occurrences') {
						echo 'Event should not exist after last occurence' . PHP_EOL;
						$this->assertEventNotExist([
							'label' => $event['label'],
							'recurrence' => $event['recurrence'],
							'recurrence_interval' => $event['recurrence_interval'],
							'date' => date(
								'Y-m-d',
								strtotime(
									date(
										'Y-m-d',
										$base_date
									) . '+' . ($occurrences + 1) * $event['recurrence_interval'] . ' ' . $event['recurrence']
									. ' + ' . $weekday . ' days'
								)
							)
						]);
						continue;
					}

					echo 'Event should exist' . PHP_EOL;
					$this->assertEventExists([
						'label' => $event['label'],
						'recurrence' => $event['recurrence'],
						'recurrence_interval' => $event['recurrence_interval'],
						'date' => $event_day
					]);
				}
			}

			if ($event['recurrence'] !== 'week' || empty($event['weekday_repeat'])) {
				$event_day = date(
					'Y-m-d',
					strtotime(
						$event['start_date'] . '+' . $o * $event['recurrence_interval'] . ' ' . $event['recurrence']
					)
				);
				echo 'Test if event exists on ' . $event_day . PHP_EOL;

				if ($o == $occurrences && $event['recurrence_type'] === 'occurrences') {
					echo 'Event should not exist after last occurence' . PHP_EOL;
					$this->assertEventNotExist([
						'label' => $event['label'],
						'recurrence' => $event['recurrence'],
						'recurrence_interval' => $event['recurrence_interval'],
						'date' => $event_day
					]);
					continue;
				}

				if ($event['recurrence_type'] == 'date' && $event_day > $event['end_date']) {
					echo 'Event should not exist after end date' . PHP_EOL;
					$this->assertEventNotExist([
						'label' => $event['label'],
						'recurrence' => $event['recurrence'],
						'recurrence_interval' => $event['recurrence_interval'],
						'date' => $event_day
					]);
					continue;
				}

				echo 'Event should exist' . PHP_EOL;
				$this->assertEventExists([
					'label' => $event['label'],
					'recurrence' => $event['recurrence'],
					'recurrence_interval' => $event['recurrence_interval'],
					'date' => $event_day
				]);
			}
		}
	}

	private function countOccurrences(array $event)
	{
		// Count occurrences using to start_date end_date, recurrence and recurrence_interval
		$startDate = new DateTime($event['start_date']);
		$endDate = new DateTime($event['end_date']);
		$interval = $event['recurrence_interval'];
		$recurrence = $event['recurrence'];

		$occurrences = 0;

		switch ($recurrence) {
			case 'day':
				$diff = $startDate->diff($endDate)->days;
				$occurrences = floor($diff / $interval) + 1;
				break;
			case 'week':
				$diff = $startDate->diff($endDate)->days;
				$occurrences = floor($diff / (7 * $interval));
				break;
			case 'month':
				$diff = $startDate->diff($endDate)->m + ($startDate->diff($endDate)->y * 12);
				$occurrences = floor($diff / $interval);
				break;
			case 'year':
				$diff = $startDate->diff($endDate)->y;
				$occurrences = floor($diff / $interval);
				break;
		}

		return $occurrences;
	}

	private function getWrongIntervals(): array
	{
		return [0, -1, 1.2, 'a', '', null];
	}

	private function assertEventExists(array $event)
	{
		// Check if the event exists
		$sql = "SELECT COUNT(*) FROM " . MAIN_DB_PREFIX . "actioncomm";
		$sql .= " WHERE `label` = '" . $event['label'] . "'";
		$sql .= " AND `datep` LIKE '" . $event['date'] . "%'";


		$resql = $this->db->query($sql);
		$count = $this->db->fetch_row($resql)[0];

		echo $sql . PHP_EOL;
		$this->assertEquals(
			1,
			$count,
			'Event not created on date ' . $event['date'] . ' from recurring event ' . $event['label'] . ' every ' . $event['recurrence_interval'] . ' ' . $event['recurrence']
		);
	}

	private function assertEventNotExist(array $event)
	{
		// Check if the event exists
		$sql = "SELECT COUNT(*) FROM " . MAIN_DB_PREFIX . "actioncomm";
		$sql .= " WHERE `label` = '" . $event['label'] . "'";
		$sql .= " AND `datep` LIKE '" . $event['date'] . "%'";

		$resql = $this->db->query($sql);
		$count = $this->db->fetch_row($resql)[0];

		echo $sql . PHP_EOL;
		$this->assertEquals(
			0,
			$count,
			'Event created on date ' . $event['date'] . ' from recurring event ' . $event['label'] . ' every ' . $event['recurrence_interval'] . ' ' . $event['recurrence']
		);
	}

	public function testCreatingEventsWithOccurences()
	{
		foreach ($this->recurrences_types as $recurrence) {
			$this->createEventWithOccurences([
				'recurrence' => $recurrence,
				'recurrence_type' => 'occurrences'
			]);
		}
	}

	private function createEventWithOccurences(array $event)
	{
		foreach ($this->getIntervals() as $interval) {
			foreach ($this->getOccurencesQty() as $occurences) {
				$this->createEvent([
					'label' => $this->event['label'] . '-' . uniqid(),
					'start_date' => $this->event['start_date'],
					'recurrence' => $event['recurrence'],
					'weekday_repeat' => [2, 3, 4],
					'recurrence_type' => $event['recurrence_type'],
					'recurrence_interval' => $interval,
					'occurrences' => $occurences
				]);
			}
		}
	}

	public function testCreatingWithWrongInterval()
	{
		foreach ($this->getWrongIntervals() as $interval) {
			$this->createEvent([
				'date' => $this->event['start_date'],
				'label' => $this->event['label'] . '-' . uniqid(),
				'start_date' => $this->sampleEventWithOccurences['start_date'],
				'recurrence' => $this->sampleEventWithOccurences['recurrence'],
				'recurrence_type' => $this->sampleEventWithOccurences['recurrence_type'],
				'weekday_repeat' => [2, 3, 4],
				'recurrence_interval' => $interval,
			]);
		}
	}

	private function getWrongDurations(): array
	{
		return [0, -1, 1.2, 'a', '', null];
	}
}
