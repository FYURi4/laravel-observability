<?php

declare(strict_types=1);

namespace Furushka\Observability\Console;

use Furushka\Observability\Storage\StorageInterface;
use Illuminate\Console\Command;

class PruneCommand extends Command
{
    protected $signature = 'observability:prune {--days= : Сколько дней хранить (по умолчанию из конфига)}';

    protected $description = 'Удаляет старые трейсы и спаны';

    public function handle(StorageInterface $storage): int
    {
        $days = (int) ($this->option('days') ?: config('observability.retention.days', 7));

        $this->info("Удаляю записи старше {$days} дней...");

        $deleted = $storage->prune($days);

        $this->info("Удалено записей: {$deleted}");

        return self::SUCCESS;
    }
}
