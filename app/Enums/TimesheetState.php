<?php

namespace App\Enums;

enum TimesheetState: string
{
    case NON_WORKING_DAY = 'non_working_day';
    case NONE = 'none';

    case PRESENT = 'present';
    case LATE = 'late';
    case EARLY_EXIT = 'early_exit';
    case INCOMPLETE = 'incomplete';
    case ABSENT = 'absent';

    case VACATION = 'vacation';
    case UNKNOWN = 'unknown';
}