<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class drentenalle_plugin extends research_base_plugin {
	static function getName() {
		return 'Drenthe Alle Drenten';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://alledrenten.nl/zoeken?f%5Bs%5D%5B35%5D=1&f%5Bs%5D%5B36%5D=1&f%5Bs%5D%5B53%5D=1&f%5Bs%5D%5B48%5D=1&f%5Bs%5D%5B52%5D=1&f%5Bs%5D%5B41%5D=1&f%5Bs%5D%5B51%5D=1&f%5Bs%5D%5B50%5D=1&f%5Bs%5D%5B37%5D=1&f%5Bs%5D%5B42%5D=1&f%5Bs%5D%5B45%5D=1&f%5Bs%5D%5B49%5D=1&f%5Bs%5D%5B47%5D=1&f%5Bs%5D%5B38%5D=1&f%5Bsf%5D%5B30%5D%5Bt%5D=&f%5Bsf%5D%5B32%5D%5Bt%5D=' . $surn . '&f%5Bsf%5D%5B31%5D%5Bt%5D=' . $givn . '&f%5Bsf%5D%5B27%5D%5Bt%5D=&f%5Bsf%5D%5B36%5D%5Bf%5D=1600&f%5Bsf%5D%5B36%5D%5Bu%5D=1962';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
