<?php
/**
 * The request to the EDD Services API.
 *
 * @package   EDD\ExtensionUtils\v2\Services
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.1.0
 */

namespace EDD\ExtensionUtils\v2\Services;

class Request {

	/**
	 * The API URL.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	private const API_URL = 'https://services.easydigitaldownloads.com/';

	/**
	 * The endpoint to request.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	private $endpoint;

	/**
	 * The license key.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	private $license_key;

	/**
	 * The response from the API request.
	 *
	 * @since 1.1.0
	 * @var Response
	 */
	private $response;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 * @param string $endpoint The endpoint to request.
	 * @return void
	 */
	public function __construct( string $endpoint, string $license_key = '' ) {
		$this->endpoint    = $endpoint;
		$this->license_key = $license_key;
	}

	/**
	 * Make the API request.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function make_request() {
		$request = wp_remote_request(
			esc_url_raw( $this->build_request_url() ),
			$this->get_request_args()
		);

		$this->response = new Response( $request );
	}

	/**
	 * Get the response from the API request.
	 *
	 * @since <next-version>
	 * @return Response
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 * Build the request URL.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	private function build_request_url() {
		return trailingslashit( self::API_URL ) . $this->endpoint;
	}

	/**
	 * Build the request headers.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	private function get_request_args() {
		return array(
			'headers'            => array(
				'X-License-Key' => $this->get_site_license_key(),
			),
			'timeout'            => 15,
			'sslverify'          => true,
			'user-agent'         => $this->get_user_agent(),
			'reject_unsafe_urls' => true,
		);
	}

	/**
	 * Get the site license key.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	private function get_site_license_key() {
		$license_key = $this->license_key ? $this->license_key : trim( get_site_option( 'edd_pro_license_key' ) );

		return sanitize_text_field( $license_key );
	}

	/**
	 * Gets the default user agent string.
	 *
	 * @since 1.1.0
	 * @return string
	 */
	private function get_user_agent() {
		$edd        = edd_is_pro() ? 'EDDPro/' : 'EDD/';
		$user_agent = array(
			'WordPress/' . get_bloginfo( 'version' ),
			$edd . EDD_VERSION,
			get_bloginfo( 'url' ),
		);

		return implode( '; ', $user_agent );
	}
}
