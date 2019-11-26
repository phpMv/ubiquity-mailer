<?php
namespace Ubiquity\mailer;

use PHPMailer\PHPMailer\PHPMailer;
use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\cache\ClassUtils;

/**
 * Ubiquity\mailer$MailerManager
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class MailerManager {

	/**
	 *
	 * @var PHPMailer
	 */
	private static $mailer;

	/**
	 *
	 * @var MailerQueue
	 */
	private static $queue;

	private static $config;

	private static $dConfig = [
		'host' => '127.0.0.1',
		'port' => 587,
		'auth' => false,
		'user' => '',
		'password' => '',
		'protocol' => 'smtp',
		'ns' => 'mail'
	];

	private static function getConfigPath() {
		return \ROOT . \DS . 'config' . \DS . 'mailer.php';
	}

	/**
	 * Start the mailer manager.
	 */
	public static function start() {
		$mailer = new PHPMailer();
		$config = self::loadConfig();
		$mailer->Host = $config['host'];
		$mailer->Port = $config['port'];
		$mailer->Mailer = $config['protocol'];
		if ($config[protocol] === 'smtp') {
			if ($config['auth']) {
				$mailer->Password = $config['password'];
				$mailer->Username = $config['user'];
			}
		}
		self::$mailer = $mailer;
		self::$queue = new MailerQueue();
	}

	public static function initConfig() {
		self::saveConfig(self::$dConfig);
	}

	public static function saveConfig($config) {
		$content = "<?php\nreturn " . UArray::asPhpArray($config, 'array') . ';';
		$path = self::getConfigPath();
		if (UFileSystem::safeMkdir($path)) {
			if (@\file_put_contents($path, $content, LOCK_EX) === false) {
				throw new \Exception("Unable to write mailer config file: {$path}");
			}
		} else {
			throw new \Exception("Unable to create folder : {$path}");
		}
	}

	public static function loadConfig() {
		return self::$config = \array_merge(include self::getConfigPath(), self::$dConfig);
	}

	public static function send(AbstractMail $mail): bool {
		$mail->build(self::$mailer);
		return self::$mailer->send();
	}

	public static function getNamespace() {
		return self::$config['ns'] ?? 'mail';
	}

	/**
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public static function getMailer() {
		return MailerManager::$mailer;
	}

	public static function sendQueue($limit = NULL): int {
		$mails = self::$queue->toSend();
		$i = 0;
		foreach ($mails as $mailInfos) {
			if (isset($limit) && $limit > $i) {
				$mailClass = $mailInfos['class'];
				$mail = new $mailClass();
				if (self::send($mail)) {
					self::$queue->sent($mailInfos['index']);
				}
				$i ++;
			} else {
				break;
			}
		}
		return $i;
	}

	/**
	 * Returns an array of mail files
	 *
	 * @param boolean $silent
	 * @return array
	 */
	protected static function _getFiles($silent = false) {
		$typeDir = \ROOT . \DS . \str_replace("\\", \DS, self::getNamespace());
		if (! $silent) {
			echo 'Mail directory is ' . $typeDir . "\n";
		}
		return UFileSystem::glob_recursive($typeDir . \DS . '*.php');
	}

	/**
	 * Returns an array of the mail class names
	 *
	 * @param boolean $silent
	 * @return string[]
	 */
	public static function getMailClasses($silent = false) {
		$result = [];
		$files = self::_getFiles($silent);
		foreach ($files as $file) {
			$result[] = ClassUtils::getClassFullNameFromFile($file);
		}
		return $result;
	}
}

