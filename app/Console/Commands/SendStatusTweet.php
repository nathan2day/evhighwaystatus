<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Handlers\Tweeter;
use App\Repositories\ChargerRepository;

class SendStatusTweet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tweet:status {provider} {--production=true}';

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
    public function __construct(Tweeter $tweeter,ChargerRepository $chargers)
    {
        parent::__construct();
	$this->tweeter = $tweeter;
	$this->chargers = $chargers;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	$tweet = $this->chargers->statusTweetFor($this->argument('provider'));

	if ($this->option('production') == 'false') {
		$success = true;
	} else {
        	$success = $this->tweeter->post($tweet);
	}

	if ($success) {
        	$this->info($tweet);
		$this->info('Tweet successfully posted.');
	} else {
		$this->error('Error posting tweet.');
	}
    }
}
