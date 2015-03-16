<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class myheritage_plugin extends research_base_plugin {
	static function getName() {
		return 'MyHeritage';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://www.myheritage.com/research?formId=master&formMode=&action=query&exactSearch=0&qname=Name+fn.' . $givn . '+ln.' . $surname;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}
	
	static function encode_plus() {
		return false;	
	}
}
