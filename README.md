# Tools for Easy Digital Downloads Extensions

## Installation and set up

The extension in question needs to have a `composer.json` file, specifically with the following:

```json 
"require": {
    "easydigitaldownloads/edd-addon-tools": "*"
},
"repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/awesomemotive/edd-addon-tools"
    }
]
```

Once set up, run `composer install --no-dev`. This should create a new `vendors/` folder with `easydigitaldownloads/edd-addon-tools/` inside.

## Using the extension loader

The `ExtensionLoader` class can be used to conditionally load an extension if all the requirements are met. Here's an example:

```php 
require_once dirname( __FILE__ ) . '/vendor/autoload.php';
\EDD\ExtensionUtils\v1\ExtensionLoader::loadOrQuit( __FILE__, 'EDD_Content_Restriction_load', array(
	'php'                    => '5.5',
	'easy-digital-downloads' => '2.9',
) );
```

- The `vendor/autoload.php` file needs to be included. 
- Call `\EDD\ExtensionUtils\v1\ExtensionLoader::loadOrQuit()`. This function takes three parameters:
    1. The path to the main plugin file (`__FILE__` assuming this is included in the main plugin file).
    2. The function/closure/callback you want to execute if all requirements are met. In this example that's a function called `EDD_Content_Restriction_load()`.
    3. An array of requirements that must be met in order to load the plugin.

If the requirements are all met, the callback function is triggered on the `plugins_loaded` hook.
If the requirements are not met, then the callback is not triggered, and instead a warning is printed in the plugin's row in the admin table.

### Custom requirements

You can also set up custom/arbitrary requirements. Here's an example for where an extension could arbitrarily require a version of any plugin (in this case, MailPoet):

```php 
\EDD\ExtensionUtils\v1\ExtensionLoader::loadOrQuit( __FILE__, 'edd_mailpoet_load', array(
	'php'                    => '5.5',
	'easy-digital-downloads' => '2.9',
	'mailpoet'               => array(
		'minimum' => '2.0',
		'name'    => 'MailPoet',
		'exists'  => static function () {
			return defined( 'MAILPOET_VERSION' ) || class_exists( 'WYSIJA_object' );
		},
		'current' => static function () {
			if ( defined( 'MAILPOET_VERSION' ) ) {
				return MAILPOET_VERSION;
			} elseif ( class_exists( 'WYSIJA_object' ) ) {
				return WYSIJA_object::$version;
			} else {
				return false;
			}
		},
	),
) );
```

Note the use of closures. This ensures the constants are only evaluated on `plugins_loaded` instead of immediately. This is to ensure the dependencies are loaded before we check them.
