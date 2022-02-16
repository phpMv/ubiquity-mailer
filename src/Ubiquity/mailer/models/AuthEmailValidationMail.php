<?php
namespace Ubiquity\mailer\models;

/**
  * Mailer AuthEmailValidationMail
  */
class AuthEmailValidationMail extends \Ubiquity\mailer\AbstractMail {

	private string $url='';

	private $expire;

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\mailer\AbstractMail::bodyText()
	 */
	public function bodyText() {
		return sprintf('Click the below link to confirm your email. The link will expire in %s: %s',$this->expire,$this->url);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\mailer\AbstractMail::initialize()
	 */
	protected function initialize(){
		$this->subject = 'Account creation';
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\mailer\AbstractMail::body()
	 */
	public function body() {
		return \sprintf('Click the below link to confirm your email. The link will expire in %s.<hr><a href="%s">Confirm your email</a>',$this->expire,$this->url);
	}

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
