<?php
namespace Ubiquity\mailer\models;

/**
  * Mailer AbstractAuthUrlExpireMail
  */
abstract class AbstractAuthUrlExpireMail extends \Ubiquity\mailer\AbstractMail {

	protected string $url='';

	protected $expire;

	/**
	 * @param string $url
	 */
	public function setUrl(string $url): void {
		$this->url = $url;
	}

	/**
	 * @return mixed
	 */
	public function getExpire() {
		return $this->expire;
	}

	/**
	 * @param mixed $expire
	 */
	public function setExpire($expire): void {
		$this->expire = $expire;
	}

}
