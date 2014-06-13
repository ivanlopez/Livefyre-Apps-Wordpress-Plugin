<?php
require_once 'Utils/JWT.php';
require_once 'Utils/IDNA.php';
require_once 'Core/Network.php';
require_once 'Core/Site.php';
require_once 'Requests/library/Requests.php';

class Livefyre { 
	public static function getNetwork($networkName, $networkKey) {
		return new Network($networkName, $networkKey);
	} 
}
