<?php
// System for generating menus.

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}
function getNewLanguageMenu() {
	global $SEARCH_SPIDER;
	$uti_languages = array('cs','de','en_US');
	if ($SEARCH_SPIDER) {
		return null;
	} else {
		$menu=new WT_Menu(WT_I18N::translate('Language'), '#', 'menu-language');
		foreach (WT_I18N::installed_languages() as $lang=>$name) {
		if (in_array($lang, $uti_languages)) {
			$submenu=new WT_Menu($name, get_query_url(array('lang'=>$lang), '&amp;'), 'menu-language-'.$lang);
				if (WT_LOCALE == $lang) {$submenu->addClass('','','lang-active');}
				$menu->addSubMenu($submenu);
			}
		}
		if (count($menu->submenus)>1) {
			return $menu;
		} else {
			return null;
		}
	}
}

function getLanguageFlags() {
		$menu=getNewLanguageMenu();
		$user_id = getUserID();
		$user_lang = get_user_setting($user_id, 'language');	
		if ($menu->submenus) {
			$output ='<span id="lang-menu"><ul>';
			foreach ($menu->submenus as $submenu) {
				if ($submenu) {
					$link = '';
					if ($submenu->link) {
						if ($submenu->target !== null) {
							$link .= ' target="'.$submenu->target.'"';
						}
						if ($submenu->link=='#' && $submenu->onclick !== null) {
								$link .= ' onclick="'.$submenu->onclick.'"';
						}
						$lang_code = str_replace('menu-language-', '', $submenu->id);
						$lang_code == $user_lang ? $output .= '<li id="'.$submenu->id.'" title="'.$submenu->label.'" class="lang-active">' : $output .= '<li id="'.$submenu->id.'" title="'.$submenu->label.'">';
						$output .= '<a class="'.$submenu->iconclass.'" href="'.$submenu->link.'"'.$link.'></a></li>';
					}	
				}
			}
			$output .='</ul></span>';
		}			
		return $output;
}
