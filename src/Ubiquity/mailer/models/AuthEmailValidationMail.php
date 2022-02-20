<?php
namespace Ubiquity\mailer\models;

/**
  * Mailer AuthEmailValidationMail
  */
class AuthEmailValidationMail extends AbstractAuthUrlExpireMail {

	/**
	 *
	 * {@inheritdoc}
	 * @see \Ubiquity\mailer\AbstractMail::bodyText()
	 */
	public function bodyText():string {
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
	public function body():string {
		return \sprintf('Click the below link to confirm your email. The link will expire in %s.<hr><a href="%s">Confirm your email</a>',$this->expire,$this->url);
	}

}
