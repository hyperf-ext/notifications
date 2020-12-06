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
use HyperfExt\Notifications\Messages\SmsMessage;
use HyperfExt\Sms\Contracts\MobileNumberInterface;
use HyperfExt\Sms\Contracts\SmsManagerInterface;
use LogicException;

class SmsChannel implements ChannelInterface
{
    /**
     * The mailer implementation.
     *
     * @var \HyperfExt\Sms\Contracts\SmsManagerInterface
     */
    protected $smsManager;

    /**
     * Create a new mail channel instance.
     */
    public function __construct(SmsManagerInterface $smsManager)
    {
        $this->smsManager = $smsManager;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @return mixed|void
     */
    public function send($notifiable, Notification $notification)
    {
        /** @var \HyperfExt\Sms\Contracts\SmsMessageInterface $message */
        $message = $notification->toSms($notifiable);

        if (empty($recipient = $notifiable->routeNotificationFor(static::class, $notification)) && ! $message instanceof SmsMessage) {
            return;
        }

        if (! $recipient instanceof MobileNumberInterface) {
            throw new LogicException('The SMS recipient must be instance of ' . MobileNumberInterface::class);
        }

        $message->to($recipient);

        return $this->smsManager->send($message);
    }
}
