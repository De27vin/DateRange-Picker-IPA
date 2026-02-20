<?php
namespace App\DTO;

class NotificationDTO
{
    public function __construct(
        public readonly NotificationType $type = NotificationType::INFO,
        public readonly string $message = ''
    ) {}
}