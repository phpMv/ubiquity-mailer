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
		'replyTo' => 'addReplyTo'
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
	 * The callbacks for the message.
	 *
	 * @var array
	 */
	public $callbacks = [];

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

	abstract protected function body();

	protected function bodyText() {}

	public function send(PHPMailer $mailer) {
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
		$mailer->Subject = $this->subject ?? \get_class();
		$mailer->Body = $this->body();
		$mailer->AltBody = $this->bodyText();
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
	 * @param boolean $asString
	 *        	If true, the view is not displayed but returned as a string (usable in a variable)
	 * @throws \Exception
	 * @return string null or the view content if **$asString** parameter is true
	 */
	protected function loadView($viewName, $pData = NULL, $asString = false) {
		if (isset($pData)) {
			$this->view->setVars($pData);
		}
		return $this->view->render($viewName, $asString);
	}
}

