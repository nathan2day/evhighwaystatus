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
    protected $signature = 'tweet:status {provider} {--production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send out a status tweet for the given provider';

    private $chargers;
    private $tweeter;

    /**
     * Create a new command instance.
     *
     * @param Tweeter $tweeter
     * @param ChargerRepository $chargers
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
        if (app()->environment() !== 'production')
        {
            $this->info('Oops! Tweets can only be sent during production.');
            return;
        }

	    $tweet = $this->chargers->statusTweetFor(
	        $this->argument('provider')
        );

        $success = $this->option('production') ? $this->tweeter->post($tweet) : true;

        if ($success) {
            $this->info('Tweet successfully posted:');
            $this->info($tweet);
            return;
        }

        $this->error('Error posting tweet.');
    }
}
