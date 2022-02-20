<?php
namespace Ubiquity\mailer\models;

/**
  * Mailer AuthAccountRecoveryMail
  */
class AuthAccountRecoveryMail extends AbstractAuthUrlExpireMail {

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\mailer\AbstractMail::bodyText()
	 */
	public function bodyText():string {
		return sprintf('Click the below link for changing your account password. The link will expire in %s: %s',$this->expire,$this->url);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\mailer\AbstractMail::initialize()
	 */
	protected function initialize(){
		$this->subject = 'Account recovery';
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\mailer\AbstractMail::body()
	 */
	public function body():string {
		return \sprintf('Click the below link for changing your account password. The link will expire in %s.<hr><a href="%s">Reset and change your password</a>',$this->expire,$this->url);
	}

}
