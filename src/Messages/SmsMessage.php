<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/notifications.
 *
 * @link     https://github.com/hyperf-ext/notifications
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/notifications/blob/master/LICENSE
 */
namespace HyperfExt\Notifications\Messages;

use HyperfExt\Sms\Smsable;

class SmsMessage extends Smsable
{
    public function build(): void
    {
    }
}
