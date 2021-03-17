<?php
namespace Library\Org\OAuth;
use Library\Org\OAuth\BaseClient;
class Client extends BaseClient {
	/**
	 *
	 * @param string $host
	 */
	public function __construct($host = '') {
		$this->host = $host;
	}
}
