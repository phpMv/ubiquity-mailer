<?php
namespace Ubiquity\mailer;

use PHPMailer\PHPMailer\PHPMailer;

class ArrayMail extends AbstractMail {

	/**
	 *
	 * @var array
	 */
	private $arrayInfos;

	/**
	 *
	 * @return array
	 */
	public function getArrayInfos() {
		return $this->arrayInfos;
	}

	/**
	 * This array sets the main values (to, from, cc, bcc, subject, body, bodyText, attachments, rawAttachments)
	 *
	 * @param array $arrayInfos
	 */
	public function setArrayInfos($arrayInfos) {
		$this->arrayInfos = $arrayInfos;
	}

	public function body() {}

	public function build(PHPMailer $mailer) {
		foreach ($this->swapMethods as $property => $method) {
			$values = $this->arrayInfos[$property];
			if (! isset($values['email'])) {
				foreach ($values as $value) {
					$mailer->{$method}($value['address'], $value['name'] ?? null);
				}
			} else {
				$mailer->{$method}($values['address'], $values['name'] ?? null);
			}
		}
		$mailer->Subject = $this->arrayInfos['subject'];
		$mailer->Body = $this->arrayInfos['body'];
		$mailer->AltBody = $this->arrayInfos['bodyText'];
		$this->attachments = $this->arrayInfos['attachments'];
		$this->buildAttachments($mailer);
		$this->rawAttachments = $this->arrayInfos['rawAttachments'];
		$this->buildRowAttachments($mailer);
		if (isset($this->callback)) {
			$mailer->action_function = $this->callback;
		}
	}
}

