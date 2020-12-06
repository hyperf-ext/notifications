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
use HyperfExt\Notifications\Channels\DatabaseChannel;
use HyperfExt\Notifications\Contracts\Notification;
use HyperfExt\Notifications\Contracts\NotificationDispatcherInterface;
use InvalidArgumentException;

class AnonymousNotifiable
{
    /**
     * All of the notification routing information.
     *
     * @var array
     */
    public $routes = [];

    /**
     * Add routing information to the target.
     *
     * @param mixed $route
     * @return $this
     */
    public function route(string $channel, $route)
    {
        if ($channel === DatabaseChannel::class) {
            throw new InvalidArgumentException('The database channel does not support on-demand notifications.');
        }

        $this->routes[$channel] = $route;

        return $this;
    }

    /**
     * Send the given notification.
     */
    public function notify(Notification $notification): void
    {
        ApplicationContext::getContainer()->get(NotificationDispatcherInterface::class)->send($this, $notification);
    }

    /**
     * Send the given notification immediately.
     */
    public function notifyNow(Notification $notification): void
    {
        ApplicationContext::getContainer()->get(NotificationDispatcherInterface::class)->sendNow($this, $notification);
    }

    /**
     * Get the notification routing information for the given driver.
     *
     * @return mixed
     */
    public function routeNotificationFor(string $driver)
    {
        return $this->routes[$driver] ?? null;
    }

    /**
     * Get the value of the notifiable's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
    }
}
