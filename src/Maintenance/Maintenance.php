<?php
/**
 * Webinterface for Triopsi Hosting.
 * Copyright (C) 2023 Triopsi Hosting - All Rights Reserved
 *
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2023, Triopsi Hosting
 * @package triopsi/maintenance
 */

namespace Maintenance\Maintenance;

/**
 * Maintenance Class. Can use in middelware or command.
 */
class Maintenance {

	/**
	 * Lock file.
	 *
	 * @var string
	 */
	public $file;

	/**
	 * Default template for View.
	 *
	 * @var string
	 */
	public $template = 'maintenance.ctp';

	/**
	 * Contructor. Load default var.
	 */
	public function __construct() {
		$this->file = TMP . 'maintenance.txt';
	}

	/**
	 * Check if maintenance mode is on.
	 *
	 * @param string|null $ipAddress If passed it allows access when it matches whitelisted IPs.
	 * @return boolean
	 */
	public function isMaintenanceMode( $ipAddress = null ) {

		// If maintenance file not exists, then mode are false.
		if (!file_exists( $this->file )) {
			return false;
		}

		// Get the content of file. Is time set? If not, then false.
		$content = file_get_contents( $this->file );
		if ($content === false) {
			return false;
		}

		// If the time is over? Then return false.
		if ($content > 0 && $content < time()) {
			$this->setMaintenanceMode( false );
			return false;
		}

		// Ist the IP Address in whitelist? Then false.
		if ($ipAddress) {
			$file = TMP . 'maintenanceOverride-' . $this->_slugIp( $ipAddress ) . '.txt';
			if (file_exists( $file )) {
				return false;
			}
		}

		// Default return are true. Maintenance Mode are on.
		return true;
	}

	/**
	 * Set maintenance mode.
	 *
	 * Integer (in minutes) to activate with timeout.
	 * Using 0 it will have no timeout.
	 *
	 * @param integer|boolean $value False to deactivate, or Integer to activate.
	 * @return boolean
	 */
	public function setMaintenanceMode( $value ) {

		if ($value === false) {
			if (!file_exists( $this->file )) {
				return true;
			}

			return unlink( $this->file );
		}

		// Create with timeout.
		if ($value) {
			$value = time() + $value * MINUTE;
		}

		return (bool) file_put_contents( $this->file, $value );
	}

	/**
	 * Get the whitelist or add new IPs.
	 *
	 * @param array $newIps IP addressed to be added to the whitelist.
	 * @return array|boolean Boolean Success for adding, an array of all whitelisted IPs otherwise.
	 */
	public function whitelist( $newIps = array() ) {
		if ($newIps) {
			foreach ($newIps as $ip) {
				$this->_addToWhitelist( $ip );
			}

			return true;
		}
		$files = glob( TMP . 'maintenanceOverride-*.txt' );
		$ips   = array();
		foreach ($files as $file) {
			$ip    = pathinfo( $file, PATHINFO_FILENAME );
			$ip    = substr( $ip, strpos( $ip, '-' ) + 1 );
			$ips[] = $this->_unslugIp( $ip );
		}
		return $ips;
	}

	/**
	 * Clear whitelist. If IPs are passed, only those will be removed, otherwise all.
	 *
	 * @param array $ips Array of Ip Addresses.
	 * @return boolean
	 */
	public function clearWhitelist( $ips = array() ) {
		$files = glob( TMP . 'maintenanceOverride-*.txt' );
		foreach ($files as $file) {
			$ip = pathinfo( $file, PATHINFO_FILENAME );
			$ip = substr( $ip, strpos( $ip, '-' ) + 1 );
			if (!$ips || in_array( $this->_unslugIp( $ip ), $ips, true )) {
				if (!unlink( $file )) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Create Whitelisted IP Address File.
	 *
	 * @param string $ip Valid IP address.
	 * @return boolean
	 */
	protected function _addToWhitelist( string $ip ) {
		$file = TMP . 'maintenanceOverride-' . $this->_slugIp( $ip ) . '.txt';
		if (!file_put_contents( $file, 0 )) {
			return false;
		}

		return true;
	}

	/**
	 * Handle special chars in IPv6.
	 *
	 * @param string $ip IP Address.
	 * @return string
	 */
	protected function _slugIp( string $ip) {
		return str_replace( ':', '#', $ip );
	}

	/**
	 * Handle special chars in IPv6.
	 *
	 * @param string $ip IP Address.
	 * @return string
	 */
	protected function _unslugIp( string $ip) {
		return str_replace( '#', ':', $ip );
	}

}
