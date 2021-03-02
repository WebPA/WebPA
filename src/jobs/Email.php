<?php

namespace WebPA\jobs;

use WebPA\tutors\assessments\email\ClosingReminder;
use WebPA\tutors\assessments\email\TriggerReminder;

require __DIR__ . '/../includes/inc_global.php';

// execute email closing reminders
$closingReminder = new ClosingReminder($DB);

$closingReminder->send();

// execute email trigger reminder

$triggerReminder = new TriggerReminder($DB);

$triggerReminder->send();
