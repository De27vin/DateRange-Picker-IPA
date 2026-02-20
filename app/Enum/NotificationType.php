<?php
namespace App\DTO;

enum NotificationType: string
{
    case ERROR = 'error';
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case INFO = 'info';
}