<?php
/**
 * Webinterface for Triopsi Hosting.
 * Copyright (C) 2023 Triopsi Hosting - All Rights Reserved
 *
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2023, Triopsi Hosting
 * @package triopsi/maintenance
 */

namespace Maintenance\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\EventInterface;
use Maintenance\Maintenance\Maintenance;
use Cake\Validation\Validation;
use Exception;
use Cake\Core\Configure;

/**
 * The Maintenance Component.
 */
class MaintenanceComponent extends Component {

	/**
	 * Load Flash Component.
	 *
	 * @var array
	 */
	protected $components = array('Flash');

	/**
	 * Before Filter.
	 *
	 * @param \Cake\Event\EventInterface $event The Controller.beforeRender event.
	 * @throws \Exception Exception.
	 * @return \Cake\Http\Response|null|void
	 */
	public function beforeFilter( EventInterface $event ) {
		if (Configure::read( 'maintenance.flash', true )) {
			if (!in_array( 'Flash', $this->components )) {
				throw new Exception( 'Flash component missing in AppController setup.' );
			}
			// Is the maintenance mode on?
			$maintenance       = new Maintenance();
			$isMaintenanceMode = $maintenance->isMaintenanceMode();
			if ($isMaintenanceMode) {
				// $this->Flash->warning( __d( 'maintenance', 'Maintenance mode active - your IP is in the whitelist.' ) );
				$controller = $this->getController();
				$controller->set( 'maintenance_mode', true );
			}
		}
	}

	/**
	 * Is the maintenance mode on?.
	 *
	 * @return boolean
	 */
	public function isMaintenanceMode() {
		// Is the maintenance mode on?
		$maintenance = new Maintenance();
		return $maintenance->isMaintenanceMode();
	}

	/**
	 * Activate Maintenance Mode.
	 *
	 * @param integer $duration Timeout in minutes.
	 */
	public function activateMaintenanceMode( $duration ) {
		$maintenance = new Maintenance();
		$maintenance->setMaintenanceMode( $duration );
	}

	/**
	 * Deactivate Maintenance Mode.
	 */
	public function deactivateMaintenanceMode() {
		$maintenance = new Maintenance();
		$maintenance->setMaintenanceMode( false );
	}

	/**
	 * Reset Maintenance Mode.
	 */
	public function resetMaintenanceMode() {
		$maintenance = new Maintenance();
		$maintenance->setMaintenanceMode( false );
		$maintenance->clearWhitelist();
	}

	/**
	 * Get all ip addresses on the whitelist.
	 *
	 * @return array
	 */
	public function getWhitelists() {
		$maintenance = new Maintenance();
		return $maintenance->whitelist();
	}

	/**
	 * Add IP Addresses in the whitelist.
	 *
	 * @param array $ips Array of Ip Addresses.
	 * @return boolean|array
	 */
	public function addIpAddressWhitelist( $ips ) {
		$maintenance = new Maintenance();
		$error       = array();
		foreach ($ips as $ip) {
			if (!Validation::ip( $ip )) {
				$error[] = __( '{0} is not a valid IP address.', $ip );
				continue;
			}
		}
		if (empty( $error )) {
			$maintenance->clearWhitelist( $ips );
			$maintenance->whitelist( $ips );
			return true;
		} else {
			return $error;
		}
	}

	/**
	 * Remove IP Addresses from the Whitelist.
	 *
	 * @param array $ips Array of Ip Addresses.
	 * @return boolean|array
	 */
	public function removeIpAddressesWhitelist( $ips ) {
		$maintenance = new Maintenance();
		$error       = array();
		foreach ($ips as $ip) {
			if (!Validation::ip( $ip )) {
				$error[] = __( '{0} is not a valid IP address.', $ip );
				continue;
			}
		}
		if (empty( $error )) {
			$maintenance->clearWhitelist( $ips );
			return true;
		} else {
			return $error;
		}
	}



}
