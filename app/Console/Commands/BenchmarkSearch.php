<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:benchmark-search')]
#[Description('Benchmark LIKE vs Scout user search')]
class BenchmarkSearch extends Command
{
    public function handle(): int
    {
        $query = $this->ask('Enter benchmark search term', 'John');

        $this->line('Running LIKE benchmark...');
        DB::flushQueryLog();
        DB::enableQueryLog();
        $startLike = microtime(true);
        $memoryBeforeLike = memory_get_usage(true);
        User::query()->where('name', 'LIKE', "%{$query}%")->limit(20)->get();
        $likeTime = (microtime(true) - $startLike) * 1000;
        $likeMemory = memory_get_usage(true) - $memoryBeforeLike;
        $likeQueries = count(DB::getQueryLog());

        $this->line('Running Scout benchmark...');
        DB::flushQueryLog();
        DB::enableQueryLog();
        $startScout = microtime(true);
        $memoryBeforeScout = memory_get_usage(true);
        User::search($query)->take(20)->get();
        $scoutTime = (microtime(true) - $startScout) * 1000;
        $scoutMemory = memory_get_usage(true) - $memoryBeforeScout;
        $scoutQueries = count(DB::getQueryLog());

        $this->table(
            ['Method', 'Time (ms)', 'Query Count', 'Memory (KB)'],
            [
                ['LIKE', number_format($likeTime, 2), $likeQueries, number_format($likeMemory / 1024, 2)],
                ['Scout', number_format($scoutTime, 2), $scoutQueries, number_format($scoutMemory / 1024, 2)],
            ]
        );

        $this->info('Complexity note: LIKE scans rows (approximately O(n)); fulltext-backed Scout improves lookup (approximately O(log n)).');

        return self::SUCCESS;
    }
}
