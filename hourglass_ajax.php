<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'hourglass_ajax.php');
require './includes/session.php';

/*
 * The purpose of this page is to build the left half of the Hourglass chart via Ajax.
 * This page only produces a husband and wife with the connecting lines to unite and
 * label the pair as a pair.
 */

$controller = new KT_Controller_Hourglass();

header('Content-type: text/html; charset=UTF-8');

Zend_Session::writeClose();

// -- print html header information
if (isset($_REQUEST['type']) && $_REQUEST['type']=='desc')
	$controller->print_descendency(KT_Person::getInstance($controller->pid), 1, false);
else
	$controller->print_person_pedigree(KT_Person::getInstance($controller->pid), 0);
