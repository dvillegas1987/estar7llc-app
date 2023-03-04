<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$data = array(
    'slotDays' => $slotDays,
    'activeDate' => $activeDate,
    'timeSlots' => $timeSlots,
    'selectedDate' => $selectedDate,
    'pickUpBy' => $pickUpBy,
    'selectedSlot' => $selectedSlot,
);

if (empty($timeSlots)) {
    $status = applicationConstants::OFF;
}
