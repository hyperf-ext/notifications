<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/notifications.
 *
 * @link     https://github.com/hyperf-ext/notifications
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/notifications/blob/master/LICENSE
 */
namespace HyperfExt\Notifications\Commands;

use Hyperf\Command\Command;
use Hyperf\Database\Migrations\MigrationCreator;
use Hyperf\Utils\Filesystem\Filesystem;
use Throwable;

class NotificationTableCommand extends Command
{
    /**
     * The migration creator instance.
     *
     * @var \Hyperf\Database\Migrations\MigrationCreator
     */
    protected $creator;

    /**
     * The filesystem instance.
     *
     * @var \Hyperf\Utils\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new notifications table command instance.
     */
    public function __construct(MigrationCreator $creator, Filesystem $files)
    {
        parent::__construct('notifications:table');
        $this->setDescription('Generate a new migration file for the notifications table');

        $this->creator = $creator;
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $fullPath = $this->createBaseMigration();
            $this->files->put($fullPath, $this->files->get(__DIR__ . '/stubs/create_notifications_table.stub'));
            $file = pathinfo($fullPath, PATHINFO_FILENAME);
            $this->info("<info>[INFO] Created Migration:</info> {$file}");
        } catch (Throwable $e) {
            $this->error("<error>[ERROR] Created Migration:</error> {$e->getMessage()}");
        }
    }

    /**
     * Create a base migration file for the notifications.
     */
    protected function createBaseMigration(): string
    {
        $name = 'create_notifications_table';

        $path = $this->getMigrationPath();

        return $this->creator->create($name, $path);
    }

    /**
     * Get the path to the migration directory.
     */
    protected function getMigrationPath(): string
    {
        return BASE_PATH . DIRECTORY_SEPARATOR . 'migrations';
    }
}
