<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/notifications.
 *
 * @link     https://github.com/hyperf-ext/notifications
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/notifications/blob/master/LICENSE
 */
namespace HyperfExt\Notifications\Channels;

use HyperfExt\Notifications\Contracts\ChannelInterface;
use HyperfExt\Notifications\Contracts\Notification;
use RuntimeException;

class DatabaseChannel implements ChannelInterface
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @return \Hyperf\Database\Model\Model
     */
    public function send($notifiable, Notification $notification)
    {
        return $notifiable->routeNotificationFor(static::class, $notification)->create(
            $this->buildPayload($notifiable, $notification)
        );
    }

    /**
     * Get the data for the notification.
     *
     * @param mixed $notifiable
     * @throws \RuntimeException
     */
    protected function getData($notifiable, Notification $notification): array
    {
        if (method_exists($notification, 'toDatabase')) {
            return is_array($data = $notification->toDatabase($notifiable)) ? $data : $data->data;
        }

        if (method_exists($notification, 'toArray')) {
            return $notification->toArray($notifiable);
        }

        throw new RuntimeException('Notification is missing toDatabase / toArray method.');
    }

    /**
     * Build an array payload for the DatabaseNotification Model.
     *
     * @param mixed $notifiable
     */
    protected function buildPayload($notifiable, Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => get_class($notification),
            'data' => $this->getData($notifiable, $notification),
            'read_at' => null,
        ];
    }
}
