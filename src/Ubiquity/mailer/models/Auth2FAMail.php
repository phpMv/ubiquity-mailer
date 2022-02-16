<?php
namespace Ubiquity\mailer\models;

use Ubiquity\utils\base\UASystem;

/**
  * Mailer Auth2FAMail
  */
class Auth2FAMail extends \Ubiquity\mailer\AbstractMail {

	private string $code='';

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\mailer\AbstractMail::bodyText()
	 */
	public function bodyText() {
		return sprintf('>2FA code : %s',$this->code);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\mailer\AbstractMail::initialize()
	 */
	protected function initialize(){
		$this->subject = '2FA verification';
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\mailer\AbstractMail::body()
	 */
	public function body() {
		return \sprintf('A sign in attempt requires further verification from %s on %s.<br>To complete the sign in, enter the verification code.<hr><h1>2FA verification code</h1><h2>%s</h2>',UASystem::getBrowserComplete(),UASystem::getPlatform(),$this->code);
	}

	/**
	 * @param string $code
	 */
	public function setCode(string $code): void {
		$this->code = $code;
	}



}
