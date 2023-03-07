<?php
/**
 * Webinterface for Triopsi Hosting.
 * Copyright (C) 2023 Triopsi Hosting - All Rights Reserved
 *
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2023, Triopsi Hosting
 * @package triopsi/maintenance
 */

namespace Maintenance\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Validation\Validation;
use Maintenance\Maintenance\Maintenance;

/**
 * Activate or deactivate the maintenance mode for the application on console.
 */
class MaintenanceModeCommand extends Command {

	/**
	 * The console io.
	 *
	 * @var \Cake\Console\ConsoleIo
	 */
	protected $io;

	/**
	 * Maintenance Object.
	 *
	 * @var \Setup\Maintenance\Maintenance
	 */
	public $Maintenance;

	/**
	 * Get the current maintanence mode state.
	 */
	public function status() {
		$isMaintenanceMode = $this->Maintenance->isMaintenanceMode();
		if ($isMaintenanceMode) {
			$this->io->out( 'Maintenance mode active!' );
		} else {
			$this->io->out( 'Maintenance mode not active' );
		}
	}

	/**
	 * Deactivate maintenance mode.
	 */
	public function deactivate() {
		$this->Maintenance->setMaintenanceMode( false );
		$this->io->out( 'Maintenance mode deactivated ...' );
	}

	/**
	 * Activate maintenance mode with an optional timeout setting.
	 *
	 * @param integer $duration Timeout in minutes.
	 */
	public function activate( $duration) {
		$this->Maintenance->setMaintenanceMode( $duration );
		$this->io->out( 'Maintenance mode activated ...' );
	}

	/**
	 * Whitelist specific IPs.
	 * Not passing any argument will output the current whitelist.
	 *
	 * @param boolean $remove Remove Ip address from Whitelist.
	 * @param array   $ips Array of ip addresses.
	 */
	public function whitelist( $remove, $ips) {
		if ($remove && empty( $ips )) {
			$this->Maintenance->clearWhitelist();
		}
		if (!empty( $ips )) {
			foreach ($ips as $ip) {
				if (!Validation::ip( $ip )) {
					$this->io->error( $ip . ' is not a valid IP address.' );
					$this->abort();
				}
			}
			if ($remove) {
				$this->Maintenance->clearWhitelist( $ips );
			} else {
				$this->Maintenance->whitelist( $ips );
			}
			$this->io->out( 'Done!', 2 );
		}

		$this->io->out( 'Current whitelist:' );
		$ips = $this->Maintenance->whitelist();
		if (!$ips) {
			$this->io->out( 'n/a' );
		} else {
			$this->io->out( $ips );
		}
	}

	/**
	 * Reset Maintanence Mode.
	 */
	public function reset() {
		$this->Maintenance->setMaintenanceMode( false );
		$this->Maintenance->clearWhitelist();
		$this->io->out( 'Reset Done' );
	}


	/**
	 * Execute Command.
	 *
	 * @param Arguments $args Args.
	 * @param ConsoleIo $io IO Object.
	 * @return int|null
	 */
	public function execute( Arguments $args, ConsoleIo $io): ?int {

		$this->io          = $io;
		$this->Maintenance = new Maintenance();

		$activity = $args->getArgument( 'activity' );
		$duration = (int) $args->getOption( 'duration' );

		switch ($activity) {
			case 'status':
				$this->status();
				break;
			case 'activate':
				$this->activate( $duration );
				break;
			case 'deactivate':
				$this->deactivate();
				break;
			case 'reset':
				$this->reset();
				break;
			case 'whitelist':
				$remove = (bool) $args->getOption( 'remove' );
				$ips    = $args->getArgument( 'ip_addresses' );
				if (!empty( $ips )) {
					$ips = explode( ',', $ips );
				}
				$this->whitelist( $remove, $ips );
				break;
		}

		return static::CODE_SUCCESS;
	}

	/**
	 * Description for cake help page.
	 *
	 * @return string
	 */
	public static function getDescription(): string {
		return 'A shell to put the whole site into maintenance mode';
	}

	/**
	 * Build Arguments and Options.
	 *
	 * @param ConsoleOptionParser $parser Parser.
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	protected function buildOptionParser( ConsoleOptionParser $parser): ConsoleOptionParser {

		$parser->setDescription(
			'A shell to put the whole site into maintenance mode'
		)
		->addArgument(
			'activity',
			array(
			'help' => 'See the current status',
			'required' => true,
			'choices' => array(
				'status',
				'activate',
				'deactivate',
				'reset',
				'whitelist'
			)
			)
		)
		->addArgument(
			'ip_addresses',
			array(
			'help' => 'A comma separated list of ip addresses for the whitelist.',
			'required' => false
			)
		)
		->addOption(
			'duration',
			array(
			'default' => 0,
			'short' => 'd',
			'help' => 'Duration in minutes - optional.',
			)
		)
		->addOption(
			'remove',
			array(
			'default' => false,
			'boolean' => true,
			'short' => 'r',
			'help' => 'Remove IP Addresses from whitelist.',
			)
		)
		->setEpilog(
			'Example Use: `cake maintenance_mode -d 10 activtate'
		);

		return $parser;
	}

}
