<?php
// Module system for adding features to phpGedView.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team
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

define('WT_SCRIPT_NAME', 'module.php');
require './includes/session.php';

$all_modules = WT_Module::getActiveModules();
$mod = safe_REQUEST($_REQUEST, 'mod', array_keys($all_modules));
if ($mod) {
	$module = $all_modules[$mod];
	$module->modAction(safe_REQUEST($_REQUEST, 'mod_action'));
} else {
	header('Location: '. WT_SERVER_NAME . WT_SCRIPT_PATH);
}
