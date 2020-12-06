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

use HyperfExt\Notifications\Contracts\Notification;

/**
 * @mixin \HyperfExt\Notifications\ChannelManager
 */
class PendingNotification
{
    /**
     * @var \HyperfExt\Notifications\ChannelManager
     */
    protected $manager;

    /**
     * @var null|string
     */
    protected $locale;

    public function __construct(ChannelManager $manager)
    {
        $this->manager = $manager;
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->manager, $name], $arguments);
    }

    public function locale(?string $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function send($notifiables, Notification $notification): void
    {
        $this->manager->send($notifiables, $this->fill(clone $notification));
    }

    public function sendNow($notifiables, Notification $notification, ?array $channels = null): void
    {
        $this->manager->sendNow($notifiables, $this->fill(clone $notification), $channels);
    }

    protected function fill(Notification $notification): Notification
    {
        return empty($this->locale) ? $notification : $notification->locale($this->locale);
    }
}
