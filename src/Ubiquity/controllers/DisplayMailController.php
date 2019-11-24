<?php
namespace Ubiquity\controllers;

use Ubiquity\mailer\MailerManager;

class DisplayMailController extends Controller {

	public function index() {}

	public function display($mailClass) {
		$mailClass = MailerManager::getNamespace() . '\\' . $mailClass;
		$mail = new $mailClass();
	}
}

