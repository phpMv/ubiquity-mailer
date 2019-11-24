<?php
namespace Ubiquity\mailer;

use Ubiquity\views\View;
use Ubiquity\exceptions\MailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Ubiquity\mailer$AbstractMail
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
abstract class AbstractMail {

	private $swapMethods = [
		'to' => 'addAdress',
		'cc' => 'addCC',
		'bcc' => 'addBCC',
		'replyTo' => 'addReplyTo',
		'from' => 'setFrom'
	];

	/**
	 * The person the message is from.
	 *
	 * @var array
	 */
	public $from = [];

	/**
	 * The "to" recipients of the message.
	 *
	 * @var array
	 */
	public $to = [];

	/**
	 * The "cc" recipients of the message.
	 *
	 * @var array
	 */
	public $cc = [];

	/**
	 * The "bcc" recipients of the message.
	 *
	 * @var array
	 */
	public $bcc = [];

	/**
	 * The "reply to" recipients of the message.
	 *
	 * @var array
	 */
	public $replyTo = [];

	/**
	 * The subject of the message.
	 *
	 * @var string
	 */
	public $subject;

	/**
	 * The attachments for the message.
	 *
	 * @var array
	 */
	public $attachments = [];

	/**
	 * The raw attachments for the message.
	 *
	 * @var array
	 */
	public $rawAttachments = [];

	/**
	 * The callback for the message.
	 *
	 * @var callable
	 */
	public $callback;

	/**
	 * Set the sender of the message.
	 *
	 * @param object|array|string $address
	 * @param string|null $name
	 * @return $this
	 */
	public function from($address, $name = null) {
		return $this->setAddress($address, $name, 'from');
	}

	/**
	 * Set the recipients of the message.
	 *
	 * @param object|array|string $address
	 * @param string|null $name
	 * @return $this
	 */
	public function to($address, $name = null) {
		return $this->setAddress($address, $name, 'to');
	}

	/**
	 * Set the recipients of the message.
	 *
	 * @param object|array|string $address
	 * @param string|null $name
	 * @return $this
	 */
	public function cc($address, $name = null) {
		return $this->setAddress($address, $name, 'cc');
	}

	/**
	 * Set the recipients of the message.
	 *
	 * @param object|array|string $address
	 * @param string|null $name
	 * @return $this
	 */
	public function bcc($address, $name = null) {
		return $this->setAddress($address, $name, 'bcc');
	}

	/**
	 * Set the "reply to" address of the message.
	 *
	 * @param object|array|string $address
	 * @param string|null $name
	 * @return $this
	 */
	public function replyTo($address, $name = null) {
		return $this->setAddress($address, $name, 'replyTo');
	}

	/**
	 * Attach a file to the message.
	 *
	 * @param string $file
	 * @param array $options
	 * @return $this
	 */
	public function attachFile($file, array $options = []) {
		$this->attachments[] = compact('file', 'options');
		return $this;
	}

	/**
	 * Attach in-memory data as an attachment.
	 *
	 * @param string $data
	 * @param string $name
	 * @param array $options
	 * @return $this
	 */
	public function attachData($data, $name, array $options = []) {
		$this->rawAttachments[] = compact('data', 'name', 'options');
		return $this;
	}

	protected function buildAttachments(PHPMailer $mailer) {
		foreach ($this->attachments as $attach) {
			$options = $attach['options'] ?? [];
			$mailer->addAttachment($attach['file'], $options['name'] ?? '', $options['encoding'] ?? 'base64', $options['type'] ?? '', $options['disposition'] ?? 'attachment');
		}
	}

	protected function buildRowAttachments(PHPMailer $mailer) {
		foreach ($this->rawAttachments as $attach) {
			$options = $attach['options'] ?? [];
			$mailer->addStringAttachment($attach['data'], $attach['name'] ?? '', $options['encoding'] ?? 'base64', $options['type'] ?? '', $options['disposition'] ?? 'attachment');
		}
	}

	public function getSubject() {
		return $this->subject ?? \get_class();
	}

	/**
	 * Set the recipients of the message.
	 *
	 * @param object|array|string $address
	 * @param string|null $name
	 * @param string $property
	 * @return $this
	 */
	protected function setAddress($address, $name = null, $property = 'to') {
		if (\is_object($address)) {
			$address = [
				$address
			];
		}
		if (\is_array($address)) {
			foreach ($address as $user) {
				$user = $this->parseUser($user);
				$this->{$property}($user->email, isset($user->name) ? $user->name : null);
			}
		} else {
			$this->{$property}[] = \compact('address', 'name');
		}
		return $this;
	}

	/**
	 * Parse the given user into an object.
	 *
	 * @param mixed $user
	 * @return object
	 */
	protected function parseUser($user) {
		if (\is_array($user)) {
			return (object) $user;
		} elseif (\is_string($user)) {
			return (object) [
				'email' => $user
			];
		} elseif (\is_object($user)) {
			if (\method_exists($user, 'getEmail')) {
				$ret = [
					'email' => $user->getEmail()
				];
				if (\method_exists($user, 'getName')) {
					$ret = [
						'name' => $user->getName()
					];
				}
				return $ret;
			} else {
				throw new MailerException('This object has no method getEmail');
			}
		}
		return $user;
	}

	/**
	 * Constructor
	 * initialize $view variable
	 */
	public function __construct() {
		$this->view = new View();
	}

	abstract public function body();

	public function bodyText() {}

	public function build(PHPMailer $mailer) {
		foreach ($this->swapMethods as $property => $method) {
			$values = $this->{$property};
			if (! isset($values['email'])) {
				foreach ($values as $value) {
					$mailer->{$method}($value['email'], $value['name'] ?? null);
				}
			} else {
				$mailer->{$method}($values['email'], $values['name'] ?? null);
			}
		}
		$mailer->Subject = $this->getSubject();
		$mailer->Body = $this->body();
		$mailer->AltBody = $this->bodyText();
		$this->buildAttachments($mailer);
		if (isset($this->callback)) {
			$mailer->action_function = $this->callback;
		}
	}

	/**
	 * Loads the view $viewName possibly passing the variables $pdata
	 *
	 * @param string $viewName
	 *        	The name of the view to load
	 * @param mixed $pData
	 *        	Variable or associative array to pass to the view
	 *        	If a variable is passed, it will have the name **$data** in the view,
	 *        	If an associative array is passed, the view retrieves variables from the table's key names
	 * @throws \Exception
	 * @return string
	 */
	protected function loadView($viewName, $pData = NULL) {
		if (isset($pData)) {
			$this->view->setVars($pData);
		}
		return $this->view->render($viewName, TRUE);
	}
}

