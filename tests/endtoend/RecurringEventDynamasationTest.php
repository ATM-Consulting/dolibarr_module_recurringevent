<?php

namespace endtoend;

use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Use Case: User creates a recurring event
 *
 */
class RecurringEventDynamisationTest extends TestCase
{
    public function testRecurringEventCardUI_Path1()
    {
        $today = new DateTime();
        $nextMonday = new DateTime('next monday');
        $nextFriday = new DateTime('next friday');

        $this->openCard();

        $this->fillDate($nextMonday);
        $this->setEventAsRecurring();

        $nextMondayCheckbox = $this->nextMondayCheckbox();

        $this->assertIsSelected($nextMondayCheckbox);

        $this->fillDate($nextFriday);
        $this->assertIsSelected($nextFridayCheckbox);
    }

    public function testRecurringEventCardUI_Path2()
    {
        $today = new DateTime();
        $nextMonday = new DateTime('next monday');
        $nextFriday = new DateTime('next friday');

        $this->openCard();
        $this->setEventAsRecurring();

        $this->fillDate($nextMonday);

        $nextMondayCheckbox = $this->nextMondayCheckbox();

        $this->assertIsSelected($nextMondayCheckbox);

        $this->fillDate($nextFriday);
        $this->assertIsSelected($nextFridayCheckbox);
    }

    public function testRecurringEventCardUI_Path3()
    {
        $today = new DateTime();
        $nextMonday = new DateTime('next monday');

        $this->openCard();
        $this->setEventAsRecurring();
        $this->checkNextWednesdayCheckbox();

        $this->fillDate($nextMonday);

        $nextWednesdayCheckbox = $this->nextWednesdayCheckbox();

        $this->assertIsSelected($nextWednesdayCheckbox);
    }

    private function openCard()
    {
        // Open the card
    }

    private function fillDate($date)
    {
        // Fill the date
    }

    private function setEventAsRecurring()
    {
        // Set the event as recurring
    }

    private function nextMondayCheckbox()
    {
        // Get the next Monday checkbox
    }

    private function nextWednesdayCheckbox()
    {
        // Get the next Wednesday checkbox
    }

    private function nextFridayCheckbox()
    {
        // Get the next Friday checkbox
    }

    private function assertIsSelected($checkbox)
    {
        // Assert that the checkbox is selected
    }

    private function checkNextWednesdayCheckbox()
    {
        // Check the next Wednesday checkbox
    }
}