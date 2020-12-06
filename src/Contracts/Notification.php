<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/notifications.
 *
 * @link     https://github.com/hyperf-ext/notifications
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/notifications/blob/master/LICENSE
 */
namespace HyperfExt\Notifications\Contracts;

use Hyperf\Contract\CompressInterface;
use Hyperf\Contract\UnCompressInterface;

abstract class Notification implements CompressInterface, UnCompressInterface
{
    /**
     * The unique identifier for the notification.
     *
     * @var string
     */
    public $id;

    /**
     * The locale to be used when sending the notification.
     *
     * @var null|string
     */
    public $locale;

    /**
     * @var string
     */
    public $queue = 'default';

    /**
     * @var int
     */
    public $delay = 0;

    /**
     * Set the locale to send this notification in.
     *
     * @return $this
     */
    public function locale(?string $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return string[]
     */
    abstract public function via($notifiable): array;

    /**
     * @return static
     */
    public function uncompress(): CompressInterface
    {
        foreach ($this as $key => $value) {
            if ($value instanceof UnCompressInterface) {
                $this->{$key} = $value->uncompress();
            }
        }

        return $this;
    }

    /**
     * @return static
     */
    public function compress(): UnCompressInterface
    {
        foreach ($this as $key => $value) {
            if ($value instanceof CompressInterface) {
                $this->{$key} = $value->compress();
            }
        }

        return $this;
    }
}
