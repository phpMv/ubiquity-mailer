<?php
namespace Ubiquity\mailer;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Ubiquity\mailer$MailerManager
 * This class is part of Ubiquity
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class MailerManager {
	
	/**
	 * @var PHPMailer
	 */
	private static $mailer;
	
	private static $config;
	
	/**
	 * Start the mailer manager.
	 */
	public static function start(){
		self::$mailer=new PHPMailer();
		self::loadConfig();
	}
	
	public static function initConfig(){
		
	}
	
	public static function loadConfig(){
		self::$config=include \ROOT.\DS.'config'.\DS.'mailer.php';
	}
}

