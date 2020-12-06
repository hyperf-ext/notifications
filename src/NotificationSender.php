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

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Database\Model\Collection as ModelCollection;
use Hyperf\Database\Model\Model;
use Hyperf\Event\EventDispatcher;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Coroutine;
use HyperfExt\Contract\HasLocalePreference;
use HyperfExt\Contract\ShouldQueue;
use HyperfExt\Notifications\Channels\DatabaseChannel;
use HyperfExt\Notifications\Contracts\Notification;
use HyperfExt\Notifications\Events\NotificationSending;
use HyperfExt\Notifications\Events\NotificationSent;
use Ramsey\Uuid\Uuid;

class NotificationSender
{
    /**
     * The notification manager instance.
     *
     * @var \HyperfExt\Notifications\ChannelManager
     */
    protected $manager;

    /**
     * The event dispatcher.
     *
     * @var \Psr\EventDispatcher\EventDispatcherInterface
     */
    protected $events;

    /**
     * @var \Hyperf\AsyncQueue\Driver\DriverFactory
     */
    protected $queues;

    /**
     * The locale to be used when sending notifications.
     *
     * @var null|string
     */
    protected $locale;

    /**
     * Create a new notification sender instance.
     *
     * @param null|string $locale
     */
    public function __construct(
        ChannelManager $manager,
        EventDispatcher $events,
        DriverFactory $queues,
        $locale = null
    ) {
        $this->manager = $manager;
        $this->events = $events;
        $this->queues = $queues;
        $this->locale = $locale;
    }

    /**
     * Send the given notification to the given notifiable entities.
     *
     * @param array|\Hyperf\Utils\Collection|mixed $notifiables
     */
    public function send($notifiables, Notification $notification): void
    {
        $notifiables = $this->formatNotifiables($notifiables);

        $notification instanceof ShouldQueue
            ? $this->queueNotification($notifiables, $notification)
            : $this->sendNow($notifiables, $notification);
    }

    /**
     * Send the given notification immediately.
     *
     * @param array|\Hyperf\Utils\Collection|mixed $notifiables
     * @param string[] $channels
     */
    public function sendNow($notifiables, Notification $notification, ?array $channels = null): void
    {
        $notifiables = $this->formatNotifiables($notifiables);

        $original = clone $notification;

        foreach ($notifiables as $notifiable) {
            if (empty($viaChannels = $channels ?: $notification->via($notifiable))) {
                continue;
            }

            Coroutine::create(function () use ($viaChannels, $notifiable, $original) {
                if (! empty($locale = $this->getPreferredLocale($notifiable, $original))) {
                    ApplicationContext::getContainer()->get(TranslatorInterface::class)->setLocale($locale);
                }

                $notificationId = Uuid::uuid4()->toString();

                foreach ((array) $viaChannels as $channel) {
                    if (! ($notifiable instanceof AnonymousNotifiable && $channel === DatabaseChannel::class)) {
                        $this->sendToNotifiable($notifiable, $notificationId, clone $original, $channel);
                    }
                }
            });
        }
    }

    /**
     * Get the notifiable's preferred locale for the notification.
     *
     * @param mixed $notifiable
     * @param mixed $notification
     */
    protected function getPreferredLocale($notifiable, Notification $notification): ?string
    {
        return $notification->locale ?? $this->locale ?? value(function () use ($notifiable) {
            if ($notifiable instanceof HasLocalePreference) {
                return $notifiable->getPreferredLocale();
            }
            return null;
        });
    }

    /**
     * Send the given notification to the given notifiable via a channel.
     *
     * @param mixed $notifiable
     */
    protected function sendToNotifiable($notifiable, string $id, Notification $notification, string $channel)
    {
        if (! $notification->id) {
            $notification->id = $id;
        }

        $this->events->dispatch(
            new NotificationSending($notifiable, $notification, $channel)
        );

        $response = $this->manager->get($channel)->send($notifiable, $notification);

        $this->events->dispatch(
            new NotificationSent($notifiable, $notification, $channel, $response)
        );
    }

    /**
     * Queue the given notification instances.
     *
     * @param mixed $notifiables
     * @param \HyperfExt\Contract\ShouldQueue|\HyperfExt\Notifications\Contracts\Notification
     */
    protected function queueNotification($notifiables, Notification $notification)
    {
        $notifiables = $this->formatNotifiables($notifiables);

        $original = clone $notification;

        foreach ($notifiables as $notifiable) {
            $notificationId = Uuid::uuid4()->toString();

            foreach ((array) $original->via($notifiable) as $channel) {
                $notification = clone $original;

                $notification->id = $notificationId;

                if (! is_null($this->locale)) {
                    $notification->locale = $this->locale;
                }

                $queue = $notification->queue;

                if (method_exists($notification, 'viaQueues')) {
                    $queue = $notification->viaQueues()[$channel] ?? null;
                }

                $this->queues->get($queue)->push(
                    new SendQueuedNotifications($notifiable, $notification, [$channel]),
                    $notification->delay
                );
            }
        }
    }

    /**
     * Format the notifiables into a Collection / array if necessary.
     *
     * @param mixed $notifiables
     * @return array|\Hyperf\Database\Model\Collection
     */
    protected function formatNotifiables($notifiables)
    {
        if (! $notifiables instanceof Collection && ! is_array($notifiables)) {
            return $notifiables instanceof Model ? new ModelCollection([$notifiables]) : [$notifiables];
        }

        return $notifiables;
    }
}
