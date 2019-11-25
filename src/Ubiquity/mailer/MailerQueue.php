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

	/**
	 *
	 * @var array
	 */
	private $queue;

	/**
	 *
	 * @var array
	 */
	private $dequeue;

	private $rootKey = 'mailer/';

	public function __construct() {
		$this->queue = CacheManager::$cache->fetch($this->rootKey . 'queue');
		$this->dequeue = CacheManager::$cache->fetch($this->rootKey . 'dequeue');
	}

	public function add(string $mailerClass): void {
		$this->queue[] = [
			'class' => $mailerClass
		];
	}

	public function later(string $mailerClass, \DateInterval $duration): void {
		$d = new \DateTime();
		$this->sendAt($mailerClass, $d->add($duration));
	}

	public function sendAt(string $mailerClass, \DateTime $date): void {
		$this->queue[] = [
			'class' => $mailerClass,
			'at' => $date
		];
	}

	public function sendBetween(string $mailerClass, \DateTime $startDate, \DateTime $endDate): void {
		$this->queue[] = [
			'class' => $mailerClass,
			'between' => $startDate,
			'and' => $endDate
		];
	}

	public function save(): void {
		$this->saveContent('queue');
		$this->saveContent('dequeue');
	}

	private function saveContent($part): void {
		$content = "<?php\nreturn " . UArray::asPhpArray($this->{$part}, 'array') . ';';
		CacheManager::$cache->store($this->rootKey . $part, $content);
	}

	public function toSendAt(\DateTime $date): array {
		$result = [];
		foreach ($this->queue as $index => $mail) {
			$this->toSendMailAt($result, $mail, $date, $index);
		}
		return $result;
	}

	private function toSendMailAt(array &$result, array $mail, \DateTime $date, $index): bool {
		if (isset($mail['at'])) {
			if ($mail['at'] <= $date) {
				$mail['index'] = $index;
				$result[] = $mail;
				return true;
			}
		} elseif (isset($mail['between'])) {
			if ($date >= $mail['between'] && $date <= $mail['and']) {
				$mail['index'] = $index;
				$result[] = $mail;
				return true;
			}
		}
		return false;
	}

	public function toSend(): array {
		$result = [];
		$date = new \DateTime();
		foreach ($this->queue as $index => $mail) {
			if (! isset($mail['at']) && ! isset($mail['between'])) {
				$mail['index'] = $index;
				$result[] = $mail;
			} else {
				$this->toSendMailAt($result, $mail, $date, $index);
			}
		}
		return $result;
	}

	public function all(): array {
		return $this->queue;
	}

	public function clear(): void {
		$this->queue = [];
	}

	public function remove($mailerClass): void {
		foreach ($this->queue as $index => $value) {
			if ($value['class'] === $mailerClass) {
				unset($this->queue[$index]);
			}
		}
	}

	public function removeAt(\DateTime $date, $inInterval = false): void {
		foreach ($this->queue as $index => $value) {
			if (($value['at'] ?? null) === $date) {
				unset($this->queue[$index]);
			} elseif ($inInterval && isset($value['between'])) {
				if ($date >= $value['between'] && $date <= $value['and']) {
					unset($this->queue[$index]);
				}
			}
		}
	}

	public function sent($index): bool {
		if (isset($this->queue[$index])) {
			$mail = $this->queue[$index];
			$mail['sentAt'] = new \DateTime();
			unset($this->queue[$index]);
			$this->dequeue[] = $mail;
			return true;
		}
		return false;
	}
}

