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

use Hyperf\AsyncQueue\Job;
use Hyperf\Database\Model\Collection as ModelCollection;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Collection;
use HyperfExt\Notifications\Contracts\Notification;
use Throwable;

class SendQueuedNotifications extends Job
{
    /**
     * The notifiable entities that should receive the notification.
     *
     * @var \Hyperf\Utils\Collection
     */
    public $notifiables;

    /**
     * The notification to be sent.
     *
     * @var \HyperfExt\Notifications\Contracts\Notification
     */
    public $notification;

    /**
     * All of the channels to send the notification to.
     *
     * @var array
     */
    public $channels;

    /**
     * Create a new job instance.
     *
     * @param \Hyperf\Utils\Collection|\HyperfExt\Notifications\Notifiable $notifiables
     * @param string[] $channels
     */
    public function __construct($notifiables, Notification $notification, array $channels = null)
    {
        $this->channels = $channels;
        $this->notification = $notification;
        $this->notifiables = $this->wrapNotifiables($notifiables);
        $this->maxAttempts = property_exists($notification, 'tries') ? $notification->tries : null;
    }

    /**
     * Prepare the instance for cloning.
     */
    public function __clone()
    {
        $this->notifiables = clone $this->notifiables;
        $this->notification = clone $this->notification;
    }

    /**
     * Send the notifications.
     */
    public function handle()
    {
        try {
            ApplicationContext::getContainer()
                ->get(ChannelManager::class)
                ->sendNow($this->notifiables, $this->notification, $this->channels);
        } catch (Throwable $e) {
            if (method_exists($this->notification, 'failed')) {
                $this->notification->failed($e);
            }
            throw $e;
        }
    }

    /**
     * Wrap the notifiable(s) in a collection.
     *
     * @param \Hyperf\Utils\Collection|\HyperfExt\Notifications\Notifiable $notifiables
     * @return \Hyperf\Utils\Collection
     */
    protected function wrapNotifiables($notifiables)
    {
        if ($notifiables instanceof Collection) {
            return $notifiables;
        }
        if ($notifiables instanceof Model) {
            return ModelCollection::wrap($notifiables);
        }

        return Collection::wrap($notifiables);
    }
}
