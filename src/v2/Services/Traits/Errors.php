<?php
/**
 * Errors trait.
 *
 * @package   EDD\ExtensionUtils\v2\Services\Traits
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.1.0
 */

namespace EDD\ExtensionUtils\v2\Services\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Errors trait.
 *
 * @since 1.1.0
 */
trait Errors {

	/**
	 * Get the error message from the API request.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public function get_error_message() {
		return $this->get_error_message_from_code( $this->response->error );
	}

	/**
	 * Get the error message from the API request.
	 *
	 * @since 1.1.0
	 * @param string $code The error code.
	 * @return string
	 */
	private function get_error_message_from_code( $code ) {
		$errors = array(
			'missing_endpoint'      => __( 'There was an error with the endpoint.', 'easy-digital-downloads' ),
			'invalid_endpoint'      => __( 'The endpoint you are trying to access is invalid.', 'easy-digital-downloads' ),
			'no_access_to_endpoint' => __( 'You do not have access to this data.', 'easy-digital-downloads' ),
			'invalid_license_key'   => __( 'The license key you entered is invalid.', 'easy-digital-downloads' ),
			'generic_error'         => __( 'An unknown error occurred. Please try again later.', 'easy-digital-downloads' ),
		);

		return isset( $errors[ $code ] ) ? $errors[ $code ] : $errors['generic_error'];
	}
}
