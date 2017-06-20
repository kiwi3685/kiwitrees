<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class bmd_nz_plugin extends research_base_plugin {
	static function getName() {
		return 'NZ Births, Marriages, Deaths';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NZL';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		// This is a post form, so it will be sent with Javascript
		$url_1 	= 'https://www.bdmhistoricalrecords.dia.govt.nz/Search/Search?Path=querySubmit.m%3fReportName%3d';
		$url_2	= 'Search%26recordsPP%3d30#SearchResults';

		$birth_year == '' ? $birth_date = '' : $birth_date = DateTime::createFromFormat('d/m/Y', '01/01/' . ($birth_year - 5))->format('d/m/Y');
		$death_year == '' ? $death_date = '' : $death_date = DateTime::createFromFormat('d/m/Y', '31/12/' . ($death_year + 5))->format('d/m/Y');

		// Earliest date possible
		$earliest		= '1/1/1840';
		$earliest_date	= max($birth_date, DateTime::createFromFormat('d/m/Y', $earliest)->format('d/m/Y'));

		/**
		 * You can search for:
		 * 	Births that occurred at least 100 years ago
		 * 	Stillbirths if registered at least 50 years ago.
		*/
		$current_date	= new DateTime('now');
		$birt_limit		= $current_date->modify('-100 year');
		$latest_birt	= min($death_date, $birt_limit->format('d/m/Y'));

		/**
		 * You can search for:
		 * 	Marriages that occurred 80 years ago.
		*/
		$current_date	= new DateTime('now');
		$marr_limit		= $current_date->modify('-80 year');
		$earliest_marr	= max($earliest_date, $marr_limit->format('d/m/Y'));
		$latest_marr	= min($death_date, $marr_limit->format('d/m/Y'));

		/**
		 * You can search fordeath records if:
		 * 	the death occurred at least 50 years ago, or
		 * 	the deceased's date of birth was at least 80 years ago.
		*/
		$current_date	= new DateTime('now');
		$deat_limit1	= $current_date->modify('-50 year');
		$current_date	= new DateTime('now');
		$deat_limit2	= $current_date->modify('-80 year');
		$latest_deat	= max($death_date, $deat_limit1->format('d/m/Y'), $deat_limit2->format('d/m/Y'));

		$params_birth = array(
			'csur'			=> $surn,
			'cfirst'		=> $first,
			'mfirst'		=> '',
			'cdate_lower'	=> $earliest_date,
			'cdate_upper'	=> $latest_birt,
			'natno'			=> '',
			'current_tab'	=> 'tab1',
			'service'		=> '',
			'addrow'		=> '',
			'remrow'		=> '',
			'setrepeat'		=> '',
			'switch_tab'	=> 'Submit',
		);

		$params_marr = array(
			'brsur'			=> $gender == 'F' ? $surn : '',
			'brfirst'		=> $gender == 'F' ? $first : '',
			'bgsur'			=> $gender == 'M' ? $surn : '',
			'bgfirst'		=> $gender == 'M' ? $first : '',
			'wdate_lower'	=> $earliest_marr,
			'wdate_upper'	=> $latest_marr,
			'natno'			=> '',
			'current_tab'	=> 'tab1',
			'service'		=> '',
			'addrow'		=> '',
			'remrow'		=> '',
			'setrepeat'		=> '',
			'switch_tab'	=> 'Submit',

		);

		$params_death = array(
			'dsur'			=> $surn,
			'dfirst'		=> $first,
			'ddate_lower'	=> $earliest_date,
			'ddate_upper'	=> $latest_deat,
			'natno'			=> '',
			'current_tab'	=> 'tab1',
			'service'		=> '',
			'addrow'		=> '',
			'remrow'		=> '',
			'setrepeat'		=> '',
			'switch_tab'	=> 'Submit',
		);

		$collection = array(
			"Birth"		=> json_encode($params_birth),
			"Death"		=> json_encode($params_death),
			"Marriage"	=> json_encode($params_marr),
		);

		foreach($collection as $key => $value) {
			$url = $url_1 . $key . $url_2;
			$link[] = array(
				'title' => WT_I18N::translate($key),
				'link'  => "postresearchform('" . $url . "'," . $value . ")"
			);
		}

		return  $link;
	}

	static function createLinkOnly() {
		return false;
	}

	static function createSubLinksOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}

}
