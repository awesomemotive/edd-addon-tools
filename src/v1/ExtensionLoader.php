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

	const VERSION = '1.0.1';

	/**
	 * @var string Path to the plugin file.
	 */
	private $pluginFile;

	/**
	 * @var string Plugin basename.
	 */
	private $pluginBasename;

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
	 * @param string                   $pluginFile   Path to the plugin file ( __FILE__ from main plugin file.)
	 * @param \Closure|\Callable|mixed $callback     Callback to execute if requirements are met.
	 * @param array                    $requirements Requirements to pass to the checker class.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $pluginFile, $callback, $requirements ) {
		if ( ! is_callable( $callback ) ) {
			throw new \InvalidArgumentException( 'Invalid callable.' );
		}

		$this->pluginFile          = $pluginFile;
		$this->pluginBasename      = plugin_basename( $this->pluginFile );
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
			add_action( 'admin_head', array( $this, 'loadingErrorsCss' ) );
			add_action( "after_plugin_row_{$this->pluginBasename}", array( $this, 'addErrorsToPluginRow' ) );
		}
	}

	/**
	 * Adds CSS to the admin head for our "plugin not fully loaded" UI.
	 *
	 * @since 1.0
	 */
	public function loadingErrorsCss() {
		$className = sanitize_html_class( $this->pluginBasename );
		?>
		<style id="<?php echo esc_attr( $className ); ?>">
			.plugins tr[data-plugin="<?php echo esc_attr( $this->pluginBasename ); ?>"] th,
			.plugins tr[data-plugin="<?php echo esc_attr( $this->pluginBasename ); ?>"] td,
			.plugins .<?php echo esc_attr( $className ); ?>-row th,
			.plugins .<?php echo esc_attr( $className ); ?>-row td {
				background: #fff5f5;
			}

			.plugins tr[data-plugin="<?php echo esc_attr( $this->pluginBasename ); ?>"] th,
			.plugins tr[data-plugin="<?php echo esc_attr( $this->pluginBasename ); ?>"] td {
				box-shadow: none;
			}

			.plugins .<?php echo esc_attr( $className ); ?>-row th span {
				margin-left: 6px;
				color: #dc3232;
			}

			.plugins tr[data-plugin="<?php echo esc_attr( $this->pluginBasename ); ?>"] th,
			.plugins .<?php echo esc_attr( $className ); ?>-row th.check-column {
				border-left: 4px solid #dc3232 !important;
			}

			.plugins .<?php echo esc_attr( $className ); ?>-row .column-description p {
				margin: 0;
				padding: 0;
			}

			.plugins .<?php echo esc_attr( $className ); ?>-row .column-description p:not(:last-of-type) {
				margin-bottom: 8px;
			}
		</style>
		<?php
	}

	/**
	 * Displays errors about unmet requirements in this plugin's row.
	 *
	 * @since 1.0
	 */
	public function addErrorsToPluginRow() {
		$colspan = function_exists( 'wp_is_auto_update_enabled_for_type' ) && wp_is_auto_update_enabled_for_type( 'plugin' ) ? 2 : 1;
		?>
		<tr class="active <?php echo esc_attr( sanitize_html_class( $this->pluginBasename ) ); ?>-row">
			<th class="check-column">
				<span class="dashicons dashicons-warning"></span>
			</th>
			<td class="column-primary">
				<?php esc_html_e( 'This plugin is not fully active.', 'easy-digital-downloads' ) ?>
			</td>
			<td class="column-description" colspan="<?php echo esc_attr( $colspan ); ?>">
				<?php
				foreach ( $this->requirementsChecker->getErrors()->get_error_messages() as $message ) {
					echo wpautop( wp_kses_post( $message ) );
				}
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Loads the plugin if requirements are met.
	 *
	 * @param string $pluginFile   Path to the plugin file ( __FILE__ from main plugin file.)
	 * @param \Closure|\Callable|mixed Callback to execute if requirements are met.
	 * @param array  $requirements Requirements to launch the plugin.
	 */
	public static function loadOrQuit( $pluginFile, $callback, $requirements ) {
		$loader = new self( $pluginFile, $callback, $requirements );
		$loader->init();
	}

}
