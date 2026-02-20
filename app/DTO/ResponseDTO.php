<?php
namespace App\DTO;
class ResponseDTO
{
    public function __construct(
        public readonly array $errors = [],
        public readonly array $notifications = [], // array of NotificationDTO
        public readonly bool $success = false,
        public readonly array $settings = []
    ) {}
}