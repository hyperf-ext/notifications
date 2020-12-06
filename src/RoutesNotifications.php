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

use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use HyperfExt\Notifications\Channels\DatabaseChannel;
use HyperfExt\Notifications\Channels\MailChannel;
use HyperfExt\Notifications\Contracts\Notification;
use HyperfExt\Notifications\Contracts\NotificationDispatcherInterface;

trait RoutesNotifications
{
    /**
     * Send the given notification.
     */
    public function notify(Notification $instance)
    {
        ApplicationContext::getContainer()->get(NotificationDispatcherInterface::class)->send($this, $instance);
    }

    /**
     * Send the given notification immediately.
     */
    public function notifyNow(Notification $instance, array $channels = null)
    {
        ApplicationContext::getContainer()->get(NotificationDispatcherInterface::class)->sendNow($this, $instance, $channels);
    }

    /**
     * Get the notification routing information for the given driver.
     *
     * @return mixed|void
     */
    public function routeNotificationFor(string $driver, ?Notification $notification = null)
    {
        if (method_exists($this, $method = 'routeNotificationFor' . Arr::last(explode('\\', $driver)))) {
            return $this->{$method}($notification);
        }

        switch ($driver) {
            case DatabaseChannel::class:
                return $this->notifications();
            case MailChannel::class:
                return $this->email;
        }
    }
}
