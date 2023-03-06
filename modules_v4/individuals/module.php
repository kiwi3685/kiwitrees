<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class individuals_KT_Module extends KT_Module implements KT_Module_Sidebar {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Individual list');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Individuals” module */ KT_I18N::translate('A sidebar showing an alphabetic list of all the individuals in the family tree.');
	}

	// Implement KT_Module
	public function modAction($modAction) {
		switch ($modAction) {
		case 'ajax':
			Zend_Session::writeClose();
			header('Content-Type: text/html; charset=UTF-8');
			echo $this->getSidebarAjaxContent();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
			break;
		}
		exit;
	}

	// Implement KT_Module_Sidebar
	public function defaultSidebarOrder() {
		return 60;
	}

	// Implement KT_Module_Sidebar
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Sidebar
	public function hasSidebarContent() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER;
	}

	// Implement KT_Module_Sidebar
	public function getSidebarAjaxContent() {
		$alpha   =safe_GET('alpha'); // All surnames beginning with this letter where "@"=unknown and ","=none
		$surname =safe_GET('surname', '[^<>&%{};]*'); // All indis with this surname.  NB - allow ' and "
		$search   =safe_GET('search');

		if ($search) {
			return $this->search($search);
		} elseif ($alpha=='@' || $alpha==',' || $surname) {
			return $this->getSurnameIndis($alpha, $surname);
		} elseif ($alpha) {
			return $this->getAlphaSurnames($alpha, $surname);
		} else {
			return '';
		}
	}

	// Implement KT_Module_Sidebar
	public function getSidebarContent() {
		global $KT_IMAGES, $UNKNOWN_NN, $controller;

		// Fetch a list of the initial letters of all surnames in the database
		$initials = KT_Query_Name::surnameAlpha(true, false, KT_GED_ID, false);

		$controller->addInlineJavascript('
			var loadedNames = new Array();

			function isearchQ() {
				var query = jQuery("#sb_indi_name").val();
				if (query.length>1) {
					jQuery("#sb_indi_content").load("module.php?mod='.$this->getName().'&mod_action=ajax&sb_action=individuals&search="+query);
				}
			}

			var timerid = null;
			jQuery("#sb_indi_name").keyup(function(e) {
				if (timerid) window.clearTimeout(timerid);
				timerid = window.setTimeout("isearchQ()", 500);
			});
			jQuery("#sb_content_individuals").on("click", ".sb_indi_letter", function() {
				jQuery("#sb_indi_content").load(this.href);
				return false;
			});
			jQuery("#sb_content_individuals").on("click", ".sb_indi_surname", function() {
				var surname = jQuery(this).attr("title");
				var alpha = jQuery(this).attr("alt");

				if (!loadedNames[surname]) {
					jQuery.ajax({
					  url: "module.php?mod='.$this->getName().'&mod_action=ajax&sb_action=individuals&alpha="+alpha+"&surname="+surname,
					  cache: false,
					  success: function(html) {
					    jQuery("#sb_indi_"+surname+" div").html(html);
					    jQuery("#sb_indi_"+surname+" div").show("fast");
					    jQuery("#sb_indi_"+surname).css("list-style-image", "url('.$KT_IMAGES['minus'].')");
					    loadedNames[surname]=2;
					  }
					});
				}
				else if (loadedNames[surname]==1) {
					loadedNames[surname]=2;
					jQuery("#sb_indi_"+surname+" div").show("fast");
					jQuery("#sb_indi_"+surname).css("list-style-image", "url('.$KT_IMAGES['minus'].')");
				}
				else {
					loadedNames[surname]=1;
					jQuery("#sb_indi_"+surname+" div").hide("fast");
					jQuery("#sb_indi_"+surname).css("list-style-image", "url('.$KT_IMAGES['plus'].')");
				}
				return false;
			});
		');


		$out='<form method="post" action="module.php?mod='.$this->getName().'&amp;mod_action=ajax" onsubmit="return false;"><input type="search" name="sb_indi_name" id="sb_indi_name" placeholder="'.KT_I18N::translate('Search').'"><p>';
		foreach ($initials as $letter=>$count) {
			switch ($letter) {
				case '@':
					$html=$UNKNOWN_NN;
					break;
				case ',':
					$html=KT_I18N::translate('None');
					break;
				case ' ':
					$html='&nbsp;';
					break;
				default:
					$html=$letter;
					break;
			}
			$html='<a href="module.php?mod='.$this->getName().'&amp;mod_action=ajax&amp;sb_action=individuals&amp;alpha='.urlencode($letter).'" class="sb_indi_letter" rel="nofollow">'.$html.'</a>';
			$out .= $html." ";
		}

		$out .= '</p>';
		$out .= '<div id="sb_indi_content">';
		$out .= '</div></form>';
		return $out;
	}

	public function getAlphaSurnames($alpha, $surname1='') {
		$surns=KT_Query_Name::surnames('', $alpha, true, false, KT_GED_ID);
		$out = '<ul>';
		foreach ($surns as $surname=>$surns) {
			$out .= '<li id="sb_indi_'.$surname.'" class="sb_indi_surname_li"><a href="'.$surname.'" title="'.$surname.'" alt="'.$alpha.'" class="sb_indi_surname">'.$surname.'</a>';
			if (!empty($surname1) && $surname1==$surname) {
				$out .= '<div class="name_tree_div_visible">';
				$out .= $this->getSurnameIndis($alpha, $surname1);
				$out .= '</div>';
			} else {
				$out .= '<div class="name_tree_div"></div>';
			}
			$out .= '</li>';
		}
		$out .= '</ul>';
		return $out;
	}

	public function getSurnameIndis($alpha, $surname) {
		$indis=KT_Query_Name::individuals($surname, $alpha, '', true, false, KT_GED_ID);
		$out = '<ul>';
		foreach ($indis as $person) {
			if ($person->canDisplayName()) {
				$out .= '<li><a href="'.$person->getHtmlUrl().'">'.$person->getSexImage().' '.$person->getFullName().' ';
				if ($person->canDisplayDetails()) {
					$bd = $person->getLifeSpan();
					if (!empty($bd)) {
						$out .= ' ('.$bd.')';
					}
				}
				$out .= '</a></li>';
			}
		}
		$out .= '</ul>';
		return $out;
	}

	public function search($query) {
		if (strlen($query)<2) {
			return '';
		}
		$rows=
			KT_DB::prepare("
				SELECT ? AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec
				 FROM `##individuals`, `##name`
				 WHERE (i_id LIKE ? OR n_sort LIKE ?)
				 AND i_id=n_id AND i_file=n_file AND i_file=?
				 ORDER BY n_sort COLLATE '" . KT_I18N::$collation . "'
				 LIMIT 50
			")
			->execute(array('INDI', "%{$query}%", "%{$query}%", KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		$out = '<ul>';
		foreach ($rows as $row) {
			$person=KT_Person::getInstance($row);
			if ($person->canDisplayName()) {
				$out .= '<li><a href="'.$person->getHtmlUrl().'">'.$person->getSexImage().' '.$person->getFullName().' ';
				if ($person->canDisplayDetails()) {
					$bd = $person->getLifeSpan();
					if (!empty($bd)) $out .= ' ('.$bd.')';
				}
				$out .= '</a></li>';
			}
		}
		$out .= '</ul>';
		return $out;
	}
}
