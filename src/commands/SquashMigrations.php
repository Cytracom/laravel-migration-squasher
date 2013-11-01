<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SquashMigrations extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'migrate:squash';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 */
	public function fire()
	{
        (new Cytracom\Squasher\MigrationSquasher($this->argument('path')))->squash($this->argument('output'));
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
            array('path', null, InputOption::VALUE_OPTIONAL, 'The path to the migrations folder', app_path() . '/database/migrations'),
            array('output', null, InputOption::VALUE_OPTIONAL, 'The path to the output folder of squashes', app_path() . '/database/migrations')
		);
	}

}