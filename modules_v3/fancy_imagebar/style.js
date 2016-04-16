/*
// Inline styles for the Fancy ImageBar module
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
// Copyright (C) 2014 JustCarmen.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
*/

var $theme = WT_THEME_DIR.split("/")[1];

jQuery('#fancy_imagebar').css({"clear":"both","overflow":"hidden"});

if ($theme === 'colors') {
	jQuery('#fancy_imagebar img').css({"border-top":"1px solid #999"});
}
if ($theme === 'kiwitrees') {
	jQuery('#fancy_imagebar').css({"line-height":"0","position":"initial", "top":"auto"});
	jQuery('#fancy_imagebar img').css({"border-top":"5px solid #555"});
}
if ($theme === 'webtrees') {
	jQuery('#fancy_imagebar img').css({"border-top":"2px solid #81A9CB", "margin-top":"5px", "padding-top":"7px"});
}
if ($theme === 'xenea') {
	jQuery('#fancy_imagebar img').css({"border-top":"2px solid #0073CF", "margin-top":"15px"});
}
if ($theme === 'xenea') {
//	jQuery('#fancy_imagebar').append('<div class="divider" style="background-color:#0073CF;height:2px;margin:7px 0 15px">');
}
