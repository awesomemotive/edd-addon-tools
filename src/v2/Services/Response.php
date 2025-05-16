<?php
/**
 * The response from the EDD Services API.
 *
 * @package   EDD\ExtensionUtils\v2\Services
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.1.0
 */

namespace EDD\ExtensionUtils\v2\Services;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Response class.
 *
 * @since 1.1.0
 */
class Response {

	use Traits\Errors;

	/**
	 * The response code from the API request.
	 *
	 * @since 1.1.0
	 * @var int
	 */
	public $response_code;

	/**
	 * Whether the request was successful.
	 *
	 * @since 1.1.0
	 * @var bool
	 */
	public $success;

	/**
	 * The response from the API request.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	public $data = array();

	/**
	 * The error message from the API request.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	public $error = '';

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 * @param RemoteRequest $response The request object.
	 * @return void
	 */
	public function __construct( $response ) {
		$this->response_code = $this->get_code( $response );
		$this->data          = wp_remote_retrieve_body( $response );

		if ( is_wp_error( $response ) ) {
			$this->success = false;

			if ( isset( $this->data['error'] ) ) {
				$this->error = $this->data['error'];
			} else {
				$this->error = 'generic_error';
			}

			return;
		}

		$this->success = true;
	}

	/**
	 * Get the response code from the API request.
	 *
	 * @since 1.1.0
	 * @param RemoteRequest $response The request object.
	 * @return int
	 */
	private function get_code( $response ) {
		if ( ! $response || is_wp_error( $response ) ) {
			return 404;
		}

		return wp_remote_retrieve_response_code( $response );
	}
}
