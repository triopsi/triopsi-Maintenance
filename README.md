# Maintenance plugin for CakePHP

> Warning: This tool should not be used if the DB connection or your application goes down completely due to upgrades.
There it would fail hard. It should only be used for soft maintenance work.

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require triopsi/maintenance
```

## Load Plugin via bin/bake
Via the Load task you are able to load plugins in your application.php. You can do this by running:
```
bin/cake plugin load Maintenance
```

### Manually Installing
If the plugin you want to install is not available on packagist.org, you can clone or copy the plugin code into your plugins directory. *plugin/*

Put this in the application.php in the bootstrap method:
```php
$this->addPlugin('Maintenance');
```
If we were installing the plugin manually you would need to modify our application’s composer.json file to contain the following information:
```
 "autoload": {
        "psr-4": {
            ...            
            "Maintenance\\": "plugins/Maintenance/src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            ...
            "Maintenance\\Test\\": "plugins/Maintenance/tests/"
        }
    },
```
Additionally, you will need to tell Composer to refresh its autoloading cache:
```
php composer.phar dumpautoload
```

## Customizing
Make sure you have a template file in `'templates' . DS . 'Error' . DS` named `maintenance.php`.

Configs:
- `className`: Sets the view classname.Accepts either a short name (Ajax) a plugin name (MyPlugin.Ajax) or a fully namespaced name (App\View\AppView) or null to use the View class provided by CakePHP.
- `templatePath`: Sets path for template file. e.g. /template/Error.
- `statusCode`: HTTP Response Code for the http header.
- `templateLayout`: Layoutname or false for use default layout.
- `templateFileName`: Teamplet name for maintenance mode.
- `templateExtension`: View template extension.
- `contentType`: Response Type. The MIME type of the resource or the data.
- `api_prefix`: API Url Suffix. Maintenance Mode are disable for this prefix. Type false for disable exceptions.

Those can be used to adjust the content of the maintenance mode page.

## Maintenance Component
This component adds functionality on top:
- A flash message shows you if you are currently whitelisted in case maintenance mode is active (and you just
  don't see it due to the whitelisting).
### How to setup
```php
// In your App Controller Class (src/Controller/AppController)
public function initialize() {
    ...
    $this->loadComponent( 'Maintenance.Maintenance' );
}
```

## MaintenanceMode Commands
This should be the preferred way of enabling and disabling the maintenance mode for your application.

Commands
- status
- activate
- deactivate
- whitelist
- reset

### Help Page
```
Usage:
cake maintenance_mode [-d 0] [-h] [-q] [-r] [-v] <status|activate|deactivate|reset|whitelist> [<ip_addresses>]

Options:

--duration, -d  Duration in minutes - optional.
--help, -h      Display this help.
--quiet, -q     Enable quiet output.
--remove, -r    Remove IP Addresses from whitelist.
--verbose, -v   Enable verbose output.

Arguments:

activity      See the current status (choices: status|activate|deactivate|reset|whitelist)
ip_addresses  A comma separated list of ip addresses for the whitelist.
(optional)
```

### Examples

Example for activating the maintenance mode:
```
./bin/cake maintenance_mode activate
```
Or with Timout (5 minutes)
```
./bin/cake maintenance_mode -d 5 activate
```
Disable maintenance mode
```
./bin/cake maintenance_mode deativate
```
Or Reset with Whitelited Ip Address
```
./bin/cake maintenance_mode reset
```