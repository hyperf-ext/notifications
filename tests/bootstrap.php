<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/notifications.
 *
 * @link     https://github.com/hyperf-ext/notifications
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/notifications/blob/master/LICENSE
 */
require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\ApplicationContext;
use HyperfExt\Mail\Contracts\MailManagerInterface;
use HyperfExt\Mail\MailManager;
use Mockery as m;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

$container = m::mock(ContainerInterface::class);
$container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn(m::mock(EventDispatcherInterface::class));
$container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([]));
$container->shouldReceive('get')->with(MailManagerInterface::class)->andReturn(new MailManager($container));

ApplicationContext::setContainer($container);
