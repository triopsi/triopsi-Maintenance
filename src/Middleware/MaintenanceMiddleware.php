<?php
/**
 * Webinterface for Triopsi Hosting.
 * Copyright (C) 2023 Triopsi Hosting - All Rights Reserved
 *
 * @author Daniel Drevermann <info@triopsi.com>
 * @copyright Copyright (c) 2023, Triopsi Hosting
 * @package triopsi/maintenance
 */

namespace Maintenance\Middleware;

use Cake\Core\InstanceConfigTrait;
use Cake\Http\ServerRequestFactory;
use Cake\Utility\Inflector;
use Cake\View\View;
use Cake\View\ViewBuilder;
use Laminas\Diactoros\CallbackStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Maintenance\Maintenance\Maintenance;

/**
 * Maintenance Middleware.
 */
class MaintenanceMiddleware implements MiddlewareInterface {

	use InstanceConfigTrait;

	/**
	 * Default Configs.
	 *
	 * ### Options
	 *
	 * - `className`: Sets the view classname.Accepts either a short name (Ajax) a plugin name (MyPlugin.Ajax) or a fully namespaced name (App\View\AppView) or null to use the View class provided by CakePHP.
	 * - `templatePath`: Sets path for template file. e.g. /template/Error.
	 * - `statusCode`: HTTP Response Code for the http header.
	 * - `templateLayout`: Layoutname or false for use default layout.
	 * - `templateFileName`: Teamplet name for maintenance mode.
	 * - `templateExtension`: View template extension.
	 * - `contentType`: Response Type. The MIME type of the resource or the data.
	 * - `api_prefix`: API Url Suffix. Maintenance Mode are disable for this prefix. Type false for disable exceptions.
	 *
	 * @var array
	 */
	protected $_defaultConfig = array(
		'className' => View::class,
		'templatePath' => 'Error',
		'statusCode' => 503,
		'templateLayout' => 'maintenance',
		'templateFileName' => 'maintenance',
		'templateExtension' => '.php',
		'contentType' => 'text/html',
		'api_prefix' => '/api/'
	);

	/**
	 * Constructor.
	 *
	 * @param array $config Configuration.
	 */
	public function __construct( array $config = array()) {
		$this->setConfig( $config );
	}

	/**
	 * Process Method for Middleware.
	 *
	 * @param \Cake\Http\ServerRequest|\Psr\Http\Message\ServerRequestInterface $request The request.
	 * @param \Psr\Http\Server\RequestHandlerInterface                          $handler The request handler.
	 * @return \Psr\Http\Message\ResponseInterface A response.
	 */
	public function process( ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

		// API Friendly.
		if ($this->getConfig( 'api_prefix' )) {
			$path = $request->getPath();
			if (strpos( $path, $this->getConfig( 'api_prefix' ) ) === 0) {
				return $handler->handle( $request );
			}
		}

		// Get Client IP Address from request.
		$ip = $request->clientIp();

		// Start maintenance object.
		$maintenance = new Maintenance();

		// Calling $handler->handle() delegates control to the *next* middleware
		// In your application's queue.
		$response = $handler->handle( $request );

		// Check Maintenance Mode with whitelist ip.
		if (!$maintenance->isMaintenanceMode( $ip )) {
			return $response;
		}

		// Build maintenance mode view.
		$response = $this->build( $response );

		// Response with view.
		return $response;
	}

	/**
	 * Create View and Content and send to the client.
	 *
	 * @param \Psr\Http\Message\ResponseInterface $response Response Object.
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	protected function build( ResponseInterface $response) {
		$cakeRequest = ServerRequestFactory::fromGlobals();
		$builder     = new ViewBuilder();

		$templateName = $this->getConfig( 'templateFileName' );
		$templatePath = $this->getConfig( 'templatePath' );

		$builder->setClassName( $this->getConfig( 'className' ) )->setTemplatePath( Inflector::camelize( $templatePath ) );
		if (!$this->getConfig( 'templateLayout' )) {
			$builder->disableAutoLayout();
		} else {
			$builder->setLayout( $this->getConfig( 'templateLayout' ) );
		}

		$view = $builder
			->build( array(), $cakeRequest )
			->setConfig( '_ext', $this->getConfig( 'templateExtension' ) );

		$bodyString = $view->render( $templateName );

		$response = $response->withHeader( 'Retry-After', (string) HOUR )
			->withHeader( 'Content-Type', $this->getConfig( 'contentType' ) )
			->withStatus( $this->getConfig( 'statusCode' ) );

		$body                = new CallbackStream(
			function () use ( $bodyString) {
				return $bodyString;
			}
		);
		$maintenanceResponse = $response->withBody( $body );

		return $maintenanceResponse;
	}

}
