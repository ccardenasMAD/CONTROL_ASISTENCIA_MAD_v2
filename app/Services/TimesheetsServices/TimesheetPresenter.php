<?php

namespace App\Services\TimesheetsServices;

use App\Enums\TimesheetState;

class TimesheetPresenter
{
    public function color(TimesheetState $state): string
    {
        return match ($state) {
            TimesheetState::NON_WORKING_DAY => 'bg-slate-800/60 text-slate-400',
            TimesheetState::NONE => 'bg-slate-700 text-slate-300',
            TimesheetState::PRESENT => 'bg-green-500 text-white',
            TimesheetState::LATE => 'bg-yellow-400 text-black',
            TimesheetState::EARLY_EXIT => 'bg-blue-400 text-white',
            TimesheetState::INCOMPLETE => 'bg-gray-400 text-black',
            TimesheetState::ABSENT => 'bg-red-500 text-white',
    
            TimesheetState::VACATION => 'bg-pink-500 text-white',
    
            default => 'bg-gray-600 text-white',
        };
    }
}