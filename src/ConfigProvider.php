<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/notifications.
 *
 * @link     https://github.com/hyperf-ext/notifications
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/notifications/blob/master/LICENSE
 */
namespace HyperfExt\Notifications;

use HyperfExt\Notifications\Commands\GenNotificationCommand;
use HyperfExt\Notifications\Commands\NotificationTableCommand;
use HyperfExt\Notifications\Contracts\NotificationDispatcherInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                NotificationDispatcherInterface::class => ChannelManager::class,
            ],
            'commands' => [
                NotificationTableCommand::class,
                GenNotificationCommand::class,
            ],
        ];
    }
}
