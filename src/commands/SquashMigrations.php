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
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
        (new Cytracom\Squasher\MigrationSquasher($this->argument('path')))->squash($this->argument('output'));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('', InputArgument::REQUIRED, 'An example argument.'),
		);
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