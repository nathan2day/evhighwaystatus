<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Handlers\Tweeter;

class SendStatusTweet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweet:status {provider}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send out a status tweet for the given provider';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Tweeter $tweeter)
    {
        parent::__construct();
	$this->tweeter = $tweeter;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tweet = $this->tweeter->sendStatusTweet($this->argument('provider'));
        $this->info($tweet);
    }
}
