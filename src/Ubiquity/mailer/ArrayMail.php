<?php
namespace Ubiquity\mailer;

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
	public function setArrayInfos(array $arrayInfos) {
		$this->arrayInfos = $arrayInfos;
		$this->attachments = $this->arrayInfos['attachments'] ?? [];
		$this->rawAttachments = $this->arrayInfos['rawAttachments'] ?? [];
	}

	public function addArrayInfos(array $arrayInfos) {
		if (isset($arrayInfos['attachments'])) {
			$this->attachments = $this->arrayInfos['attachments'];
			unset($arrayInfos['attachments']);
		}
		if (isset($arrayInfos['rawAttachments'])) {
			$this->attachments = $this->arrayInfos['rawAttachments'];
			unset($arrayInfos['rawAttachments']);
		}
		foreach ($arrayInfos as $k => $v) {
			$this->arrayInfos[$k] = $v;
		}
	}

	public function body() {
		return $this->arrayInfos['body'] ?? '';
	}

	public function bodyText() {
		return $this->arrayInfos['bodyText'] ?? '';
	}

	public function getSubject() {
		return $this->arrayInfos['subject'] ?? '';
	}

	protected function getMailPropertyValues($property) {
		return $this->arrayInfos[$property] ?? null;
	}

	public static function copyFrom(AbstractMail $mail) {
		$newMail = new ArrayMail();
		$newMail->setArrayInfos([
			'from' => $mail->from,
			'to' => $mail->to,
			'cc' => $mail->cc,
			'bcc' => $mail->bcc,
			'subject' => $mail->getSubject(),
			'body' => $mail->body(),
			'bodyText' => $mail->bodyText(),
			'attachments' => $mail->attachments,
			'rawAttachments' => $mail->rawAttachments,
			'replyTo' => $mail->replyTo
		]);
		return $newMail;
	}
}

