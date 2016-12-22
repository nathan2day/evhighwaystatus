<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ProviderStatusData\Updater;

class UpdateProviderData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'providerdata:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update charger provider data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Updater $updater)
    {
        parent::__construct();
        $this->updater = $updater;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->updater->run();
    }
}
