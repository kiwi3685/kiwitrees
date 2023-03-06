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
// Convert a menu into our theme-specific format
function getMenuAsCustomList($menu) {
		// Create an inert menu - to use as a label
		$tmp = new KT_Menu(strip_tags($menu->label), '');
		// Insert the label into the submenu
		if (is_array($menu->submenus)) {
			array_unshift($menu->submenus, $tmp);
		} else {
			$menu->addSubmenu($tmp);
		}
		// Neutralise the top-level menu
		$menu->label = '';
		$menu->onclick = '';
		$menu->iconclass = '';
		return $menu->getMenuAsList();
}

// REDUNDANT FUNCTION
//-- print color theme sub type change dropdown box
//function color_theme_dropdown() {
//	global $COLOR_THEME_LIST, $KT_SESSION, $subColor;
//	$menu=new KT_Menu(/* I18N: A colour scheme */ KT_I18N::translate('Colors palette'), '#', 'menu-color');
//	uasort($COLOR_THEME_LIST, 'utf8_strcasecmp');
//	foreach ($COLOR_THEME_LIST as $colorChoice =>$colorName) {
//		$submenu = new KT_Menu($colorName, get_query_url(array('themecolor'=>$colorChoice), '&amp;'), 'menu-color-'.$colorChoice);
//		if (isset($KT_SESSION->subColor)) {
//			if ($KT_SESSION->subColor == $colorChoice) {
//				$submenu->addClass('','','theme-active');
//			}
//		} elseif  (KT_Site::preference('DEFAULT_COLOR_PALETTE') == $colorChoice) { /* here when visitor changes palette from default */
//			$submenu->addClass('','','theme-active');
//		} elseif ($subColor=='ash') { /* here when site has different theme as default and user switches to colors */
//			if ($subColor == $colorChoice) {
//				$submenu->addClass('','','theme-active');
//			}
//		}
//		$menu->addSubMenu($submenu);
//	}
//	return $menu->getMenuAsList();
//}

function color_palette() {
	global $COLOR_THEME_LIST, $KT_SESSION, $subColor;
	uasort($COLOR_THEME_LIST, 'utf8_strcasecmp');

	$html = '<ul id="colors_palette">
		<h3>' . KT_I18N::translate('Colors palette') . '</h3>';
		foreach ($COLOR_THEME_LIST as $colorChoice => $colorName) {
			$html .= '
				<li id="menu-color-' . $colorChoice . '">
					<input type="radio" id="palette_' . $colorChoice . '" name="NEW_COLOR_PALETTE" value="' . $colorChoice . '" ' . ($subColor == $colorChoice ? ' checked="checked"' : '') . '/>
					<label for="palette_' . $colorChoice . '">' . $colorName . '</label>
				</li>';
		}
	$html .= '</ul>';

	return $html;
}

/**
 *  Define the default palette to be used.  Set $subColor
 *  to one of the collowing values to determine the default:
 *
 */
$COLOR_THEME_LIST=array(
	'aquamarine'      => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Aqua Marine'),
	'ash'             => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Ash'),
	'belgianchocolate'=> /* I18N: The name of a colour-scheme */ KT_I18N::translate('Belgian Chocolate'),
	'bluelagoon'      => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Blue Lagoon'),
	'bluemarine'      => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Blue Marine'),
	'coffeeandcream'  => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Coffee and Cream'),
	'coldday'         => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Cold Day'),
	'greenbeam'       => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Green Beam'),
	'mediterranio'    => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Mediterranio'),
	'mercury'         => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Mercury'),
	'nocturnal'       => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Nocturnal'),
	'olivia'          => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Olivia'),
	'pinkplastic'     => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Pink Plastic'),
	'sage'            => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Sage'),
	'shinytomato'     => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Shiny Tomato'),
	'tealtop'         => /* I18N: The name of a colour-scheme */ KT_I18N::translate('Teal Top'),
);

/*
 * Set the color palette
 *
*/
$subColor = get_gedcom_setting(KT_GED_ID, 'COLOR_PALETTE');
// Make sure our selected palette is set and actually exists
if (!$subColor || !array_key_exists($subColor, $COLOR_THEME_LIST)) {
	$subColor = 'ash';
}


// Theme name - this needs double quotes, as file is scanned/parsed by script
$theme_name = "colors"; /* I18N: Name of a theme. */ KT_I18N::translate('colors');

$footerfile = KT_THEME_DIR . 'footer.php';
$headerfile = KT_THEME_DIR . 'header.php';

$KT_IMAGES = array(
	// used to draw charts
	'dline'		=>KT_THEME_URL.'images/dline.png',
	'dline2'	=>KT_THEME_URL.'images/dline2.png',
	'hline'		=>KT_THEME_URL.'images/hline.png',
	'spacer'	=>KT_THEME_URL.'images/spacer.png',
	'vline'		=>KT_THEME_URL.'images/vline.png',

	// used in button images and javascript
	'add'			=>KT_THEME_URL.'images/add.png',
	'button_family'	=>KT_THEME_URL.'images/buttons/family.png',
	'minus'			=>KT_THEME_URL.'images/minus.png',
	'plus'			=>KT_THEME_URL.'images/plus.png',
	'remove'		=>KT_THEME_URL.'images/delete.png',
	'search'		=>KT_THEME_URL.'images/go.png',

	// need different sizes before moving to CSS
	'default_image_F'=>KT_THEME_URL.'images/silhouette_female.png',
	'default_image_M'=>KT_THEME_URL.'images/silhouette_male.png',
	'default_image_U'=>KT_THEME_URL.'images/silhouette_unknown.png',
);

//-- This section defines variables for the charts
$bwidth = 250; // -- width of boxes on all person-box based charts
$bheight = 80; // -- height of boxes on all person-box based chart
$baseyoffset = 10; // -- position the timeline chart relative to the top of the page
$basexoffset = 10; // -- position the pedigree and timeline charts relative to the left of the page
$bxspacing = 4; // -- horizontal spacing between boxes on the pedigree chart
$byspacing = 5; // -- vertical spacing between boxes on the pedigree chart
$brborder = 1; // -- pedigree chart box right border thickness
$linewidth = 1.5;			// width of joining lines
$shadowcolor = "";		// shadow color for joining lines
$shadowblur = 0;			// shadow blur for joining lines
$shadowoffsetX = 0;		// shadowOffsetX for joining lines
$shadowoffsetY = 0;		// shadowOffsetY for joining lines

//-- Other settings that should not be touched
$Dbxspacing = 5; // -- position vertical line between boxes in relationship chart
$Dbyspacing = 10; // -- position vertical spacing between boxes in relationship chart
$Dbwidth = 250; // -- horizontal spacing between boxes in all charts
$Dbheight = 80; // -- horizontal spacing between boxes in all charts
$Dindent = 15; // -- width to indent ancestry and descendancy charts boxes
$Darrowwidth = 300; // -- not used that I can see ***

// -- Dimensions for compact version of chart displays
$cbwidth = 240;
$cbheight = 50;

// --  The largest possible area for charts is 300,000 pixels. As the maximum height or width is 1000 pixels
$KT_STATS_S_CHART_X = 550;
$KT_STATS_S_CHART_Y = 200;
$KT_STATS_L_CHART_X = 900;
// --  For map charts, the maximum size is 440 pixels wide by 220 pixels high
$KT_STATS_MAP_X = 440;
$KT_STATS_MAP_Y = 220;

$KT_STATS_CHART_COLOR1 = "#b1cff0";
$KT_STATS_CHART_COLOR2 = "#e9daf1";
$KT_STATS_CHART_COLOR3 = "#cccccc";

//-- Variables for the Fanchart
$fanChart = array(
	'color' => '#000000',
	'bgColor' => '#eeeeee',
	'bgMColor' => '#b1cff0',
	'bgFColor' => '#e9daf1'
);

if (file_exists(KT_THEME_URL . 'mytheme.php')) {
	include 'mytheme.php';
}
