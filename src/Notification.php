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
use HyperfExt\Notifications\Contracts\NotificationDispatcherInterface;

/**
 * @method static \HyperfExt\Notifications\PendingNotification locale(string $locale)
 * @method static \HyperfExt\Notifications\Contracts\ChannelInterface channel(null|string $name = null)
 * @method static void send(array|\Hyperf\Utils\Collection|mixed $notifiables, $notification)
 * @method static void sendNow(array|\Hyperf\Utils\Collection|mixed $notifiables, $notification)
 *
 * @see \HyperfExt\Notifications\ChannelManager
 */
class Notification
{
    public static function __callStatic(string $name, array $arguments)
    {
        $instance = static::getChannelManager();

        return call_user_func_array([$instance, $name], $arguments);
    }

    /**
     * Begin sending a notification to an anonymous notifiable.
     *
     * @param mixed $route
     */
    public static function route(string $channel, $route): AnonymousNotifiable
    {
        return (new AnonymousNotifiable())->route($channel, $route);
    }

    protected static function getChannelManager()
    {
        return ApplicationContext::getContainer()->get(NotificationDispatcherInterface::class);
    }
}
