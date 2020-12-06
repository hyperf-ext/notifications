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

use Hyperf\Database\Model\Collection;

class DatabaseNotificationCollection extends Collection
{
    /**
     * Mark all notifications as read.
     */
    public function markAsRead()
    {
        $this->each->markAsRead();
    }

    /**
     * Mark all notifications as unread.
     */
    public function markAsUnread()
    {
        $this->each->markAsUnread();
    }
}
