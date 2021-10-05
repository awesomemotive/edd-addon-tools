<?php
/**
 * Extension Loader
 *
 * Boots the extension, provided that all requirements are met.
 *
 * @package   edd-addon-tools
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     1.0
 */

namespace EDD\ExtensionUtils\v1;

class ExtensionLoader {

	/**
	 * @var string Path to the plugin file.
	 */
	private $pluginFile;

	/**
	 * @var \Closure|\Callable|mixed Function/method to execute if all requirements are met.
	 */
	private $callback;

	/**
	 * @var RequirementsChecker
	 */
	private $requirementsChecker;

	/**
	 * Constructor.
	 *
	 * @param string $pluginFile       Path to the plugin file ( __FILE__ from main plugin file.)
	 * @param \Closure|\Callable|mixed Callback to execute if requirements are met.
	 * @param array $requirements      Requirements to pass to the checker class.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $pluginFile, $callback, $requirements ) {
		if ( ! is_callable( $callback ) ) {
			throw new \InvalidArgumentException( 'Invalid callable.' );
		}

		$this->pluginFile          = $pluginFile;
		$this->callback            = $callback;
		$this->requirementsChecker = new RequirementsChecker( $requirements );
	}

	/**
	 * Sets up the hook to check requirements and execute the callback.
	 */
	public function init() {
		add_action( 'plugins_loaded', array( $this, 'maybeExecuteCallback' ), 98 );
	}

	/**
	 * Checks requirements and executes the callback if they are met.
	 * Otherwise, shows error messages.
	 */
	public function maybeExecuteCallback() {
		if ( $this->requirementsChecker->met() ) {
			call_user_func( $this->callback );
		} else {
			add_action( 'admin_notices', array( $this, 'printLoadingErrors' ) );
		}
	}

	/**
	 * Displays errors about unmet requirements.
	 */
	public function printLoadingErrors() {
		$pluginData = get_plugin_data( $this->pluginFile );
		?>
		<div class="notice notice-error">
			<p>
				<?php
				printf(
				/* Translators: %1$s name of the plugin */
					esc_html__( '%1$s is not fully active. The following requirements have not been met:', 'easy-digital-downloads' ),
					'<strong>' . esc_html( $pluginData['Name'] ) . '</strong>'
				);
				?>
			</p>
			<?php
			foreach ( $this->requirementsChecker->getErrors()->get_error_messages() as $message ) {
				echo wpautop( wp_kses_post( $message ) );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Loads the plugin if requirements are met.
	 *
	 * @param string $pluginFile       Path to the plugin file ( __FILE__ from main plugin file.)
	 * @param \Closure|\Callable|mixed Callback to execute if requirements are met.
	 * @param array  $requirements     Requirements to launch the plugin.
	 */
	public static function loadOrQuit( $pluginFile, $callback, $requirements ) {
		$loader = new self( $pluginFile, $callback, $requirements );
		$loader->init();
	}

}
