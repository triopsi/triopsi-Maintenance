<?php
/**
 * Webinterface for Triopsi Hosting.
 * Copyright (C) 2023 Triopsi Hosting - All Rights Reserved
 *
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2023, Triopsi Hosting
 * @package triopsi/maintenance
 */

namespace Maintenance;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\RouteBuilder;
use Maintenance\Middleware\MaintenanceMiddleware;
use Maintenance\Command\MaintenanceModeCommand;

/**
 * Plugin for Maintenance
 */
class Plugin extends BasePlugin {
	/**
	 * Load all the plugin configuration and bootstrap logic.
	 *
	 * The host application is provided as an argument. This allows you to load
	 * additional plugin dependencies, or attach events.
	 *
	 * @param \Cake\Core\PluginApplicationInterface $app The host application.
	 * @return void
	 */
	public function bootstrap( PluginApplicationInterface $app): void {
	}

	/**
	 * Add routes for the plugin.
	 *
	 * If your plugin has many routes and you would like to isolate them into a separate file,
	 * you can create `$plugin/config/routes.php` and delete this method.
	 *
	 * @param \Cake\Routing\RouteBuilder $routes The route builder to update.
	 * @return void
	 */
	public function routes( RouteBuilder $routes): void {
		$routes->plugin(
			'Maintenance',
			array('path' => '/maintenance'),
			function ( RouteBuilder $builder) {
				$builder->fallbacks();
			}
		);
		parent::routes( $routes );
	}

	/**
	 * Add middleware for the plugin.
	 *
	 * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to update.
	 * @return \Cake\Http\MiddlewareQueue
	 */
	public function middleware( MiddlewareQueue $middlewareQueue): MiddlewareQueue {
		$middlewareQueue->insertAt( 0, new MaintenanceMiddleware() );
		return $middlewareQueue;
	}

	/**
	 * Add commands for the plugin.
	 *
	 * @param \Cake\Console\CommandCollection $commands The command collection to update.
	 * @return \Cake\Console\CommandCollection
	 */
	public function console( CommandCollection $commands): CommandCollection {
		return $commands->add( 'maintenance_mode', MaintenanceModeCommand::class );
	}

	/**
	 * Register application container services.
	 *
	 * @param \Cake\Core\ContainerInterface $container The Container to update.
	 * @return void
	 * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
	 */
	public function services( ContainerInterface $container): void {
		// Add your services here.
	}
}
