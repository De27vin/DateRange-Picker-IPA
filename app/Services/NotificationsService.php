<?php

namespace App\Services;

class NotificationsService
{
    private array $notifications = [];

    public function add(string $type, string $message): self
    {
        $this->notifications[$type][] = $message;
        return $this;
    }

    public function prepend(string $type, string $message): self
    {
        if (!isset($this->notifications[$type])) {
            $this->notifications[$type] = [];
        }
        array_unshift($this->notifications[$type], $message);
        return $this;
    }

    public function addMany(string $type, array $messages): self
    {
        foreach ($messages as $message) {
            $this->add($type, $message);
        }
        return $this;
    }

    public function prependMany(string $type, array $messages): self
    {
        foreach (array_reverse($messages) as $message) {
            $this->prepend($type, $message);
        }
        return $this;
    }

    public function get(string $type = null): array
    {
        return $type ? ($this->notifications[$type] ?? []) : $this->notifications;
    }

    public function has(string $type = null): bool
    {
        return $type ? !empty($this->notifications[$type]) : !empty($this->notifications);
    }

    public function clear(string $type = null): self
    {
        $type ? $this->notifications[$type] = [] : $this->notifications = [];
        return $this;
    }

    public function addWithContext(string $type, array|string $messages, $context = null): self
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        $context = is_callable($context) ? $context() : $context;

        foreach ($messages as $message) {
            $contextualMessage = $context ? "{$context}: {$message}" : $message;
            $this->notifications[$type][] = $contextualMessage;
        }

        return $this;
    }

    public function prependWithContext(string $type, array|string $messages, $context = null): self
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        $context = is_callable($context) ? $context() : $context;

        foreach (array_reverse($messages) as $message) {
            $contextualMessage = $context ? "{$context}: {$message}" : $message;
            $this->prepend($type, $contextualMessage);
        }

        return $this;
    }
}