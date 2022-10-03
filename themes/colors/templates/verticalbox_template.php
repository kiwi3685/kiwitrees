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

echo '<div id="out-', $boxID ,'" ', $outBoxAdd, '>
		<div class="compact_view">', $thumbnail, '</div>
		<div class="name">
			<span id="namedef-',$boxID, '" class="name',$style,' ',$classfacts,'">', $shortname, '</span>
		</div>
		<div style="height:20px; text-align:center;">', $person->getLifeSpan(), '</div>
	';
	//	details for zoom view
	echo '<div id="fontdef-',$boxID,'" class="details',$style,'">
			<div class="exp_thumb">', $thumbnail, '</div>
			<a href="individual.php?pid=', $pid, '&amp;ged=', rawurlencode((string) $GEDCOM), '">
				<div id="namedef-',$boxID, '" class="name',$style,' ',$classfacts,'">', $name.$addname, '</div>
			</a>
			<div class="name',$style,'">',$genderImage,'</div>
			<div>',$BirthDeath, '</div>
			<hr>
			<div class="icon">', $icons, '</div>
		</div>';
	// end of zoom view
echo '</div>';
