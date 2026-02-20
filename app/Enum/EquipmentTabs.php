<?php

namespace App\Enum;

enum EquipmentTabs: string
{
    use EnumToArray;

    case ENABLED = 'enabled';
    case DISABLED = 'disabled';
    case EMPTY = 'empty';
    case ALL = 'all';
}