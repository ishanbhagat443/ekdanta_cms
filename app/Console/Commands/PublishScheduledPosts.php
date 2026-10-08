<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BlogService;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    protected $signature = 'gsm:publish-scheduled-posts';

    protected $description = 'Publish blog posts whose scheduled time has arrived';

    public function handle(BlogService $blog): int
    {
        $count = $blog->publishDuePosts();

        $this->info("Published {$count} scheduled post(s).");

        return self::SUCCESS;
    }
}
