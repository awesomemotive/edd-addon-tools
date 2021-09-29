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

namespace EDD\ExtensionUtils;

class ExtensionLoader {

	/**
	 * @var \Closure Function/method to execute if all requirements are met.
	 */
	private $callback;

	/**
	 * @var RequirementsChecker
	 */
	private $requirementsChecker;

	/**
	 * Constructor.
	 *
	 * @param \Closure $callback     Callback to execute if requirements are met.
	 * @param array    $requirements Requirements to pass to the checker class.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( $callback, $requirements ) {
		if ( ! is_callable( $callback ) ) {
			throw new \InvalidArgumentException( 'Invalid callable.' );
		}

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
			// @todo print errors somewhere
		}
	}

	public static function loadOrQuit( $callback, $requirements ) {
		$loader = new self( $callback, $requirements );
		$loader->init();
	}

}
