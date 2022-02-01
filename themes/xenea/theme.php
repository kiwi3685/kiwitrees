<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

// Theme name - this needs double quotes, as file is scanned/parsed by script
$theme_name = "xenea"; /* I18N: Name of a theme. */ KT_I18N::translate('xenea');

$headerfile = KT_THEME_DIR.'header.php';
$footerfile = KT_THEME_DIR.'footer.php';

//-- variables for image names
$KT_IMAGES = array(
	// used to draw charts
	'dline'          =>KT_THEME_URL.'images/dline.png',
	'dline2'         =>KT_THEME_URL.'images/dline2.png',
	'hline'          =>KT_THEME_URL.'images/hline.png',
	'spacer'         =>KT_THEME_URL.'images/spacer.png',
	'vline'          =>KT_THEME_URL.'images/vline.png',

	// used in button images and javascript
	'add'			=>KT_THEME_URL.'images/add.png',
	'button_family'	=>KT_THEME_URL.'images/buttons/family.png',
	'minus'			=>KT_THEME_URL.'images/minus.png',
	'plus'			=>KT_THEME_URL.'images/plus.png',
	'remove'		=>KT_THEME_URL.'images/delete.png',
	'search'		=>KT_THEME_URL.'images/search.png',

	// need different sizes before moving to CSS
	'default_image_F'=>KT_THEME_URL.'images/silhouette_female.png',
	'default_image_M'=>KT_THEME_URL.'images/silhouette_male.png',
	'default_image_U'=>KT_THEME_URL.'images/silhouette_unknown.png',
);

//-- This section defines variables for the pedigree chart
$bwidth = 250; // -- width of boxes on pedigree chart
$bheight = 80; // -- height of boxes on pedigree chart
$baseyoffset = 10; // -- position the entire pedigree tree relative to the top of the page
$basexoffset = 10; // -- position the entire pedigree tree relative to the left of the page
$bxspacing = 1; // -- horizontal spacing between boxes on the pedigree chart
$byspacing = 5; // -- vertical spacing between boxes on the pedigree chart
$brborder = 1; // -- box right border thickness
$linewidth=1.5;			// width of joining lines
$shadowcolor="";		// shadow color for joining lines
$shadowblur=0;			// shadow blur for joining lines
$shadowoffsetX=0;		// shadowOffsetX for joining lines
$shadowoffsetY=0;		// shadowOffsetY for joining lines

// -- global variables for the descendancy chart
$Dbaseyoffset = 20; // -- position the entire descendancy tree relative to the top of the page
$Dbasexoffset = 20; // -- position the entire descendancy tree relative to the left of the page
$Dbxspacing = 5; // -- horizontal spacing between boxes
$Dbyspacing = 10; // -- vertical spacing between boxes
$Dbwidth = 260; // -- width of DIV layer boxes
$Dbheight = 80; // -- height of DIV layer boxes
$Dindent = 15; // -- width to indent descendancy boxes
$Darrowwidth = 30; // -- additional width to include for the up arrows

// -- Dimensions for compact version of chart displays
$cbwidth = 240;
$cbheight = 50;

// --  The largest possible area for charts is 300,000 pixels, so the maximum height or width is 1000 pixels
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
