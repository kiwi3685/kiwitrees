<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

class KT_Menu {
	var $label			= ' ';
	var $labelpos		= 'right';
	var $link			= '#';
	var $onclick		= null;
	var $flyout			= 'down';
	var $class			= '';
	var $id				= null;
	var $submenuclass	= '';
	var $iconclass		= '';
	var $target			= null;
	var $parentmenu		= null;
	var $submenus;

	/**
	* Constructor for the menu class
	* @param string $label the label for the menu item (usually a kt_lang variable)
	* @param string $link The link that the user should be taken to when clicking on the menuitem
	* @param string $pos The position of the label relative to the icon (right, left, top, bottom)
	* @param string $flyout The direction where any submenus should appear relative to the menu item (right, down)
	*/
	function __construct($label = ' ', $link = '#', $id = null, $labelpos = 'right', $flyout = 'down')
	{
		$this->label	= $label;
		$this->labelpos	= $labelpos;
		$this->link		= $link;
		$this->id		= $id;
		$this->flyout	= $flyout;
		$this->submenus	= array();
	}

	function addLabel($label = null, $pos ='right')
	{
		if ($label) $this->label = $label;
		$this->labelpos = $pos;
	}

	function addLink($link='#')
	{
		$this->link = $link;
	}

	function addOnclick($onclick)
	{
		$this->onclick = $onclick;
	}

	function addFlyout($flyout = 'down')
	{
		$this->flyout	= $flyout;
	}

	function addClass($class, $submenuclass = '', $iconclass = 'icon_general')
	{
		$this->class		= $class;
		$this->submenuclass	= $submenuclass;
		$this->iconclass	= $iconclass;
	}

	function addTarget($target)
	{
		$this->target = $target;
	}

	function addSubMenu($obj)
	{
		$this->submenus[] = $obj;
	}

	//
	public function __toString() {
		return $this->getMenuAsList();
	}

	// Get the menu as a simple list - for accessible interfaces, search engines and CSS menus
	function getMenuAsList() {
		$link = '';
		if ($this->link && strpos($this->label, "<a href") !== 0) {
			if ($this->target !== null) {
				$link .= ' target="' . $this->target . '"';
			}
			if ($this->link == '#') {
				if ($this->onclick !== null) {
					$link .= ' onclick="' . $this->onclick . '"';
				}
				$html = '<a class="' . $this->iconclass . '" href="' . $this->link . '"' . $link . '>' . $this->label . '</a>';
			} else {
				$html = '<a class="' . $this->iconclass . '" href="' . $this->link . '"' . $link . '>' . $this->label . '</a>';
			}
		} else {
			$html = $this->label;
		}
		if ($this->submenus) {
			$html .= '<ul class="f-dropdown hover1">';
			foreach ($this->submenus as $submenu) {
				if ($submenu) {
					if ($submenu->submenus) {
						$submenu->iconclass .= ' icon_arrow';
					}
					$html .= $submenu->getMenuAsList();
				}
			}
			$html .= '</ul>';
		}
		if ($this->id) {
			return '<li id="' . $this->id . '">' . $html . '</li>';
		} else {
			return '<li>' . $html . '</li>';
		}
	}

	// Get the menu as a simple list - for accessible interfaces, search engines and CSS menus
	function getOtherMenuAsList() {
		$link = '';
		if ($this->link) {
			if ($this->target !== null) {
				$link .= ' target="' . $this->target . '"';
			}
			if ($this->link == '#') {
				if ($this->onclick !== null) {
					$link .= ' onclick="' . $this->onclick . '"';
				}
				if ($this->label !== null) {
					$html = '<a class="' . $this->iconclass . '" href="' . $this->link . '"' . $link . '>' . $this->label . '</a>';
				}
			} else {
				$html = '<a class="' . $this->iconclass . '" href="' . $this->link . '"' . $link . '>' . $this->label . '</a>';
			}
		} else {
			$html = $this->label;
		}
		if ($this->submenus) {
			$html .= '<ul class="dropdown">';
			foreach ($this->submenus as $submenu) {
				if ($submenu) {
					if ($submenu->submenus) {
						$submenu->iconclass .= ' icon_arrow';
					}
					$html .= $submenu->getMenuAsList();
				}
			}
			$html .= '</ul>';
		}
		if ($this->id) {
			return '<li class="has-dropdown" id="' . $this->id . '">' . $html . '</li>';
		} else {
			return '<li class="has-dropdown">' . $html . '</li>';
		}
	}

	function getMenu() {
		global $menucount, $TEXT_DIRECTION, $KT_IMAGES;

		if (!isset($menucount)) {
			$menucount = 0;
		} else {
			$menucount++;
		}
		$id = $menucount . rand();
		$c = count($this->submenus);
		$output = '<div id="menu' . $id . '" class="' . $this->class . '">';

		$id = $menucount.rand();
		$c = count($this->submenus);
		$output = "<div id=\"menu{$id}\" class=\"{$this->class}\">";
			$link = '<a href="' . $this->link . '" onmouseover="';
				if ($c >= 0) {
					$link .= 'show_submenu(\'menu' . $id . '_subs\', \'menu' . $id . '\', \'' . $this->flyout . '\');';
				}
				$link .= '" onmouseout="';
				if ($c >= 0) {
					$link .= 'timeout_submenu(\'menu' . $id . '_subs\');';
				}
				if ($this->onclick !== null) {
					$link .= '" onclick="' . $this->onclick . '"';
				}
				if ($this->target !== null) {
					$link .= '" target="'.$this->target;
				}
			$link .= '">';
				$output .= $link;
				$output .= $this->label;
			$output .= '</a>';

			if ($c > 0) {
				$submenuid = 'menu' . $id . '_subs';
				if ($TEXT_DIRECTION == 'ltr') {
					$output .= '<div style="text-align: left;">';
				} else {
					$output .= '<div style="text-align: right;">';
				}
					$output .= '<div id="menu' . $id . '_subs" class="' . $this->submenuclass . '" style="position: absolute; visibility: hidden; z-index: 100;';
					if ($this->flyout == 'right') {
						if ($TEXT_DIRECTION == 'ltr') {
							$output .= ' left: 80px;';
						} else {
							$output .= ' right: 50px;';
						}
					}
					$output .= '" onmouseover="show_submenu(\'' . $this->parentmenu . '\'); show_submenu(\'' . $submenuid . '\');" onmouseout="timeout_submenu(\'menu' . $id . '_subs\');">';
					foreach ($this->submenus as $submenu) {
						$submenu->parentmenu = $submenuid;
						$output .= $submenu->getMenu();
					}
					$output .= '</div>
				</div>';
			}
		$output .= '</div>';
		return $output;
	}

	/**
	* returns the number of submenu's in this menu
	* @return int
	*/
	function subCount() {
		return count($this->submenus);
	}

	// Get the menu as a simple list - for accessible interfaces, search engines and CSS menus
	function getTopMenuList() {
		$html = $this->label;
		if ($this->id) {
			return '<li id="' . $this->id . '">' . $html . '</li>';
		} else {
			return '<li>' . $html . '</li>';
		}

	}

	// Get the menu as a drop-down list for small screen sizes
	function getResponsiveMenu() {
		$option_link = '
			<ul>
				<li>
					<a href="'.$this->link.'">' . $this->label . '</a>';
					if ($this->submenus) {
						$option_link .= '<ul>';
						foreach ($this->submenus as $submenu) {
							if ($submenu) {
								if ($this->onclick !== null) {
									$submenu->link .= "\" onclick=\"{$this->onclick}";
								}
								$option_link .= '
									<li>
										<a href="' . $submenu->link . '">' . $submenu->label . '</a>
									</li>
								';
							}
						}
						$option_link .= '</ul>';
					}
				$option_link .= '</li>
			</ul>
		';
		return $option_link;
	}

	// Get the menu as a simple list - for accessible interfaces, search engines and CSS menus
	function getFoundationMenu() {
		$link = '';
		if ($this->link) {
			if ($this->target !== null) {
				$link .= ' target="'.$this->target.'"';
			}
			if ($this->link == '#') {
				if ($this->onclick !== null) {
					$link .= ' onclick="'.$this->onclick.'"';
				}
				$html='<a class="item '.$this->iconclass.'" href="'.$this->link.'"'.$link.'><label>'.$this->label.'</label></a>';
			} else {
				$html='<a class="item '.$this->iconclass.'" href="'.$this->link.'"'.$link.'><label>'.$this->label.'</label></a>';
			}
		} else {
			$html=$this->label;
		}
		if ($this->submenus) {
			$html .= '<ul class="f-dropdown"  id="hover1">';
			foreach ($this->submenus as $submenu) {
				if ($submenu) {
					if ($submenu->submenus) {
						$submenu->iconclass .= ' icon_arrow';
					}
					$html .= $submenu->getMenuAsList();
				}
			}
			$html .= '</ul>';
		}
			return $html;
	}



}
