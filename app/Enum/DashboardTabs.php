<?php

namespace App\Enum;

enum DashboardTabs: string
{
    use EnumToArray;

    case ALARMS = 'alarms';
    case OVERDUES = 'overdues';
    case ALERTS = 'alerts';
    case ALL = 'all';
}
