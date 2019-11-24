<?php
namespace Ubiquity\mailer;

use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UArray;

/**
 * Ubiquity\mailer$MailerQueue
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class MailerQueue {

	private static $queue;

	private static $rootKey = 'mailer/queue';

	public static function start() {
		self::$queue = CacheManager::$cache->fetch(self::$rootKey);
	}

	public static function add(string $mailerClass) {
		self::$queue[] = [
			'class' => $mailerClass
		];
	}

	public static function sendAt(string $mailerClass, \DateTime $date) {
		self::$queue[] = [
			'class' => $mailerClass,
			'at' => $date
		];
	}

	public static function sendBetween(string $mailerClass, \DateTime $startDate, \DateTime $endDate) {
		self::$queue[] = [
			'class' => $mailerClass,
			'between' => $startDate,
			'and' => $endDate
		];
	}

	public static function save() {
		$content = "<?php\nreturn " . UArray::asPhpArray(self::$queue, 'array') . ';';
		CacheManager::$cache->store(self::$rootKey, $content);
	}

	public static function toSendAt(\DateTime $date) {
		$result = [];
		foreach (self::$queue as $mail) {
			self::toSendMailAt($result, $mail, $date);
		}
		return $result;
	}

	private static function toSendMailAt(array &$result, array $mail, \DateTime $date): bool {
		if (isset($mail['at'])) {
			if ($mail['at'] <= $date) {
				$result[] = $mail;
				return true;
			}
		} elseif (isset($mail['between'])) {
			if ($date >= $mail['between'] && $date <= $mail['and']) {
				$result[] = $mail;
				return true;
			}
		}
		return false;
	}

	public static function toSend() {
		$result = [];
		$date = new \DateTime();
		foreach (self::$queue as $mail) {
			if (! isset($mail['at']) && ! isset($mail['between'])) {
				$result[] = $mail;
			} else {
				self::toSendAt($date);
			}
		}
		return $result;
	}
}

