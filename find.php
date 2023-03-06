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

define('KT_SCRIPT_NAME', 'find.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';

$controller = new KT_Controller_Simple();

$type		= safe_GET('type', KT_REGEX_ALPHA, 'indi');
$filter		= safe_GET('filter');
$action		= safe_GET('action');
$callback	= safe_GET('callback', KT_REGEX_NOSCRIPT, 'paste_id');
$media		= safe_GET('media');
$all		= safe_GET_bool('all');
$subclick	= safe_GET('subclick');
$choose		= safe_GET('choose', KT_REGEX_NOSCRIPT, '0all');
$qs			= safe_GET('tags');

// Retrives the currently selected tags in the opener window (reading curTags value of the query string)
// $preselDefault will be set to the array of DEFAULT preselected tags
// $preselCustom will be set to the array of CUSTOM preselected tags
function getPreselectedTags(&$preselDefault, &$preselCustom) {
	global $qs;
	$all = strlen($qs) ? explode(',', strtoupper($qs)) : array();
	$preselDefault = array();
	$preselCustom = array();
	foreach ($all as $one) {
		if (KT_Gedcom_Tag::isTag($one)) {
			$preselDefault[] = $one;
		} else {
			$preselCustom[] = $one;
		}
	}
}

if ($subclick=='all') {
	$all=true;
}

$embed = substr($choose, 0, 1) == "1";
$chooseType = substr($choose, 1);
if ($chooseType != "media" && $chooseType!="0file") {
	$chooseType = "all";
}

// End variables for find media

switch ($type) {
case "indi":
	$controller->setPageTitle(KT_I18N::translate('Find an individual'));
	break;
case "fam":
	$controller->setPageTitle(KT_I18N::translate('Find a family'));
	break;
case "media":
	$controller->setPageTitle(KT_I18N::translate('Find a media object'));
	break;
case "place":
	$controller->setPageTitle(KT_I18N::translate('Find a place'));
	break;
case "repo":
	$controller->setPageTitle(KT_I18N::translate('Find a repository'));
	break;
case "note":
	$controller->setPageTitle(KT_I18N::translate('Find a note'));
	break;
case "source":
	$controller->setPageTitle(KT_I18N::translate('Find a source'));
	break;
case "specialchar":
	$controller->setPageTitle(KT_I18N::translate('Find a special character'));
	$language_filter = safe_GET('language_filter');
	if (KT_USER_ID) {
		// Users will probably always want the same language, so remember their setting
		if (!$language_filter) {
			$language_filter=get_user_setting(KT_USER_ID, 'default_language_filter');
		} else {
			set_user_setting(KT_USER_ID, 'default_language_filter', $language_filter);
		}
	}
	require KT_ROOT.'includes/specialchars.php';
	$action = "filter";
	break;
case "facts":
	$controller
		->setPageTitle(KT_I18N::translate('Find a fact or event'))
		->addInlineJavascript('initPickFact();');
	break;
}
$controller->pageHeader();

?>
<script>
	function pasteid(id, name, thumb) {
		if (thumb) {
			window.opener.<?php echo $callback; ?>(id, name, thumb);
			<?php echo "window.close();"; ?>
		} else {
			// census_assistant ========================
			if (window.opener.document.getElementById('addlinkQueue')) {
				window.opener.insertRowToTable(id, name);
			}
			window.opener.<?php echo $callback; ?>(id);
			if (window.opener.pastename) window.opener.pastename(name);
			<?php echo "window.close();"; ?>
		}
	}
	function checknames(frm) {
		if (document.forms[0].subclick) button = document.forms[0].subclick.value;
		else button = "";
		if (frm.filter.value.length<2&button!="all") {
			alert("<?php echo KT_I18N::translate('Please enter more than one character'); ?>");
			frm.filter.focus();
			return false;
		}
		if (button=="all") {
			frm.filter.value = "";
		}
		return true;
	}
<?php
echo '</script>';

$options = array();
$options["option"][]	= "findindi";
$options["option"][]	= "findfam";
$options["option"][]	= "findmedia";
$options["option"][]	= "findplace";
$options["option"][]	= "findrepo";
$options["option"][]	= "findnote";
$options["option"][]	= "findsource";
$options["option"][]	= "findspecialchar";
$options["option"][]	= "findfact";
$options["form"][]		= "formindi";
$options["form"][]		= "formfam";
$options["form"][]		= "formmedia";
$options["form"][]		= "formplace";
$options["form"][]		= "formrepo";
$options["form"][]		= "formnote";
$options["form"][]		= "formsource";
$options["form"][]		= "formspecialchar";

echo '<div id="find-page"><h3>', $controller->getPageTitle(), '</h3>';

// Show indi and hide the rest
if ($type == "indi") {
	echo '
		<div id="find-header">
			<form name="filterindi" method="get" onsubmit="return checknames(this);" action="find.php">
				<input type="hidden" name="callback" value="' . $callback . '">
				<input type="hidden" name="action" value="filter">
				<input type="hidden" name="type" value="indi">
				<span>', KT_I18N::translate('Name contains:'), '&nbsp;</span>
				<input type="text" name="filter" value="';
					if ($filter) {
						echo $filter;
					}
					echo '" autofocus>
				<input type="submit" value="', KT_I18N::translate('Filter'), '">
			</form>
		</div>
	';
}

// Show fam and hide the rest
if ($type == "fam") {
	echo '
		<div id="find-header">
			<form name="filterfam" method="get" onsubmit="return checknames(this);" action="find.php">
				<input type="hidden" name="callback" value="'.$callback.'">
				<input type="hidden" name="action" value="filter">
				<input type="hidden" name="type" value="fam">
				<span>', KT_I18N::translate('Name contains:'), '&nbsp;</span>
				<input type="text" name="filter" value="';
					if ($filter) echo $filter;
					echo '">
				<input type="submit" value="', KT_I18N::translate('Filter'), '">
			</form>
		</div>
	';
}

// Show media and hide the rest
if ($type == 'media') {
	echo '
		<div id="find-header">
			<form name="filtermedia" method="get" onsubmit="return checknames(this);" action="find.php">
				<input type="hidden" name="choose" value="', $choose, '">
				<input type="hidden" name="action" value="filter">
				<input type="hidden" name="type" value="media">
				<input type="hidden" name="callback" value="', $callback, '">
				<input type="hidden" name="subclick">
				<span>', KT_I18N::translate('Media contains:'), '</span>
				<input type="text" name="filter" value="';
					if ($filter) echo $filter;
				echo '">',
				help_link('simple_filter'), '
					<p>
						<input type="submit" name="search" value="', KT_I18N::translate('Filter'), '" onclick="this.form.subclick.value=this.name">&nbsp;
						<input type="submit" name="all" value="', KT_I18N::translate('Display all'), '" onclick=\"this.form.subclick.value=this.name\">
					</p>
			</form>
		</div>
	';
}

// Show place and hide the rest
if ($type == "place") {
	echo '
		<div id="find-header">
			<form name="filterplace" method="get"  onsubmit="return checknames(this);" action="find.php">
				<input type="hidden" name="action" value="filter">
				<input type="hidden" name="type" value="place">
				<input type="hidden" name="callback" value="', $callback, '">
				<input type="hidden" name="subclick">
				<span>', KT_I18N::translate('Place contains:'), '</span>
				<input type="text" name="filter" value="';
					if ($filter) echo $filter;
				echo '">
				<p>
					<input type="submit" name="search" value="', KT_I18N::translate('Filter'), '" onclick="this.form.subclick.value=this.name">&nbsp;
					<input type="submit" name="all" value="', KT_I18N::translate('Display all'), '" onclick="this.form.subclick.value=this.name">
				</p>
			</form>
		</div>
	';
}

// Show repo and hide the rest
if ($type == "repo") {
	echo '
		<div id="find-header">
			<form name="filterrepo" method="get" onsubmit="return checknames(this);" action="find.php">
				<input type="hidden" name="action" value="filter">
				<input type="hidden" name="type" value="repo">
				<input type="hidden" name="callback" value="', $callback, '">
				<input type="hidden" name="subclick">
				<span>', KT_I18N::translate('Repository contains:'), '</span>
				<input type="text" name="filter" value="';
					if ($filter) echo $filter;
				echo '">
				<p>
					<input type="submit" name="search" value="', KT_I18N::translate('Filter'), '" onclick="this.form.subclick.value=this.name">&nbsp;
					<input type="submit" name="all" value="', KT_I18N::translate('Display all'), '" onclick="this.form.subclick.value=this.name">
				</p>
			</form>
		</div>
	';
}

// Show Shared Notes and hide the rest
if ($type == "note") {
	echo '
		<div id="find-header">
			<form name="filternote" method="get" onsubmit="return checknames(this);" action="find.php">
				<input type="hidden" name="action" value="filter">
				<input type="hidden" name="type" value="note">
				<input type="hidden" name="callback" value="', $callback, '">
				<input type="hidden" name="subclick">
				<span>', KT_I18N::translate('Shared Note contains:'), '</span>
				<input type="text" name="filter" value="';
					if ($filter) echo $filter;
				echo '">
				<p>
					<input type="submit" name="search" value="', KT_I18N::translate('Filter'), '" onclick="this.form.subclick.value=this.name">&nbsp;
					<input type="submit" name="all" value="', KT_I18N::translate('Display all'), '" onclick="this.form.subclick.value=this.name">
				</p>
			</form>
		</div>
	';
}

// Show source and hide the rest
if ($type == "source") {
	echo '
		<div id="find-header">
			<form name="filtersource" method="get" onsubmit="return checknames(this);" action="find.php">
				<input type="hidden" name="action" value="filter">
				<input type="hidden" name="type" value="source">
				<input type="hidden" name="callback" value="', $callback, '">
				<input type="hidden" name="subclick">
				<span>', KT_I18N::translate('Source contains:'), '</span>
				<input type="text" name="filter" value="';
					if ($filter) echo $filter;
				echo '">
				<p>
					<input type="submit" name="search" value="', KT_I18N::translate('Filter'), '" onclick="this.form.subclick.value=this.name">&nbsp;
					<input type="submit" name="all" value="', KT_I18N::translate('Display all'), '" onclick="this.form.subclick.value=this.name">
				</p>
			</form>
		</div>
	';
}

// Show specialchar and hide the rest
if ($type == 'specialchar') {
	echo '
		<div id="find-header">
			<form name="filterspecialchar" method="get" action="find.php">
				<input type="hidden" name="action" value="filter">
				<input type="hidden" name="type" value="specialchar">
				<input type="hidden" name="callback" value="'.$callback.'">
				<p>
					<select id="language_filter" name="language_filter" onchange="submit();">
						<option value="">', KT_I18N::translate('Change language'), '</option>';
							$language_options = '';
							foreach ($specialchar_languages as $key=>$value) {
								$language_options.= '<option value="'.$key.'"';
								if ($key==$language_filter) {
									$language_options.=' selected="selected"';
								}
								$language_options.='>'.$value.'
							</option>';
							}
						echo $language_options,
					'</select>
				</p>
			</form>
		</div>
	';
}

// Show facts
if ($type == "facts") {
	echo '<div id="find-facts-header">
	<form name="filterfacts" method="get" action="find.php">
	<input type="hidden" name="type" value="facts">
	<input type="hidden" name="tags" value="', $qs, '">
	<input type="hidden" name="callback" value="', $callback, '">
	<table class="list_table width100" border="0">
	<tr><td class="list_label" style="padding: 5px; font-weight: normal; white-space: normal;">' ;
	getPreselectedTags($preselDefault, $preselCustom);
	echo '<script>'; ?>
	// A class representing a default tag
	function DefaultTag(id, name, selected) {
		this.Id=id;
		this.Name=name;
		this.LowerName=name.toLowerCase();
		this._counter=DefaultTag.prototype._newCounter++;
		this.selected=!!selected;
	}
	DefaultTag.prototype= {
		_newCounter:0
		,view:function() {
			var row=document.createElement("tr"),cell;
			row.appendChild(cell=document.createElement("td"));
			var o = document.createElement("input");
			o.id = "tag"+this._counter;
			o.type = "checkbox";
			o.checked = this.selected;
			o.DefaultTag=this;
			o.ParentRow=row;
			o.onclick=function() {
				this.DefaultTag.selected=!!this.checked;
				this.ParentRow.className=this.DefaultTag.selected?"sel":"unsel";
				Lister.recount();
			};
			cell.appendChild(o);
			row.appendChild(cell=document.createElement("th"));
			cell.appendChild(o=document.createElement("label"));
			o.htmlFor="tag"+this._counter;
			o.appendChild(document.createTextNode(this.Id));
			row.appendChild(cell=document.createElement("td"));
			cell.appendChild(document.createTextNode(this.Name));
			TheList.appendChild(row);
			row.className=this.selected?"sel":"unsel";
		}
	};
	// Some global variable
	var DefaultTags=null /*The list of the default tag*/, TheList=null /* The body of the table that will show the default tabs */;

	// A single-instance class that manage the populating of the table
	var Lister= {
		_curFilter:null
		,_timer:null
		,clear:function() {
			var n=TheList.childNodes.length;
			while (n) TheList.removeChild(TheList.childNodes[--n]);
		}
		,_clearTimer:function() {
			if (this._timer!=null) {
				clearTimeout(this._timer);
				this._timer=null;
			}
		}
		,askRefresh:function() {
			this._clearTimer();
			this._timer=setTimeout("Lister.refreshNow()",200);
		}
		,refreshNow:function(force) {
			this._clearTimer();
			var s=document.getElementById("tbxFilter").value.toLowerCase().replace(/\s+/g," ").replace(/^ | $/g,""),k;
			if (force||(typeof(this._curFilter)!="string")||(this._curFilter!=s)) {
				this._curFilter=s;
				this.clear();
				for (k=0;k<DefaultTags.length;k++) {
					if (DefaultTags[k].LowerName.indexOf(this._curFilter)>=0) DefaultTags[k].view();
				}
			}
		}
		,recount:function() {
			var k,n=0;
			for (k=0;k<DefaultTags.length;k++)
				if (DefaultTags[k].selected)
					n++;
			document.getElementById("layCurSelectedCount").innerHTML=n.toString();
		}
		,showSelected:function() {
			this._clearTimer();
			this.clear();
			for (var k=0;k<DefaultTags.length;k++) {
				if (DefaultTags[k].selected)
					DefaultTags[k].view();
			}
		}
	};

	function initPickFact() {
		var n,i,j,tmp,preselectedDefaultTags="\x01<?php foreach ($preselDefault as $p) echo addslashes($p), '\\x01'; ?>";

		DefaultTags=[<?php
		$firstFact=TRUE;
		foreach (KT_Gedcom_Tag::getPicklistFacts() as $factId => $factName) {
			if ($firstFact) $firstFact=FALSE;
			else echo ',';
			echo 'new DefaultTag("'.addslashes($factId).'","'.addslashes($factName).'",preselectedDefaultTags.indexOf("\\x01'.addslashes($factId).'\\x01")>=0)';
		}
		?>];
		TheList=document.getElementById("tbDefinedTags");
		i=document.getElementById("tbxFilter");
		i.onkeypress=i.onchange=i.onkeyup=function() {
			Lister.askRefresh();
		};
		Lister.recount();
		Lister.refreshNow();
		document.getElementById("btnOk").disabled=false;
	}
	function DoOK() {
		var result=[],k,linearResult,custom;
		for (k=0;k<DefaultTags.length;k++) {
			if (DefaultTags[k].selected) result.push(DefaultTags[k].Id);
		}
		linearResult="\x01"+result.join("\x01")+"\x01";
		custom=document.getElementById("tbxCustom").value.toUpperCase().replace(/\s/g,"").split(",");
		for (k=0;k<custom.length;k++) {
			if (linearResult.indexOf("\x01"+custom[k]+"\x01")<0) {
				linearResult+=custom[k]+"\x01";
				result.push(custom[k]);
			}
		}
		result = result.join(",")
		if (result.substring(result.length-1, result.length)==',') {
			result = result.substring(0, result.length-1);
		}
		pasteid(result);
		window.close();
		return false;
	}
	<?php echo '</script>';
	echo '<div id="layDefinedTags"><table id="tabDefinedTags">
		<thead><tr>
			<th>&nbsp;</th>
			<th>', KT_I18N::translate('Tag'), '</th>
			<th>', KT_I18N::translate('Description'), '</th>
		</tr></thead>
		<tbody id="tbDefinedTags">
		</tbody>
	</table></div>

	<table id="tabDefinedTagsShow"><tbody><tr>
		<td><a href="#" onclick="Lister.showSelected();return false">', KT_I18N::translate('Show only selected tags'), ' (<span id="layCurSelectedCount"></span>)</a></td>
		<td><a href="#" onclick="Lister.refreshNow(true);return false">', KT_I18N::translate('Show all tags'), '</a></td>
	</tr></tbody></table>

	<table id="tabFilterAndCustom"><tbody>
		<tr><td>', KT_I18N::translate('Filter'), ':</td><td><input type="text" id="tbxFilter"></td></tr>
		<tr><td>', KT_I18N::translate('Custom tags'), ':</td><td><input type="text" id="tbxCustom" value="', addslashes(implode(",", $preselCustom)), '"></td></tr>
	<td><td></tbody></table>

	<table id="tabAction"><tbody><tr>
		<td colspan="2"><button id="btnOk" disabled="disabled" onclick="if (!this.disabled)DoOK();">', KT_I18N::translate('Save'), '</button></td>
	<tr></tbody></table>
	</td></tr></table>
	</form></div>';
}

if ($action == "filter") {
	$filter = trim($filter);
	$filter_array=explode(' ', preg_replace('/ {2,}/', ' ', $filter));

	// Output Individual
	if ($type == "indi") {
		echo '<div id="find-output">';
		$myindilist=search_indis_names($filter_array, array(KT_GED_ID), 'AND');
		if ($myindilist) {
			echo '<ul>';
			usort($myindilist, array('KT_GedcomRecord', 'Compare'));
			foreach ($myindilist as $indi) {
				echo $indi->format_list('li', true);
			}
			echo '</ul>
			<p>', KT_I18N::translate('Total individuals: %s', count($myindilist)), '</p>';
		} else {
			echo '<p>', KT_I18N::translate('No results found'), '</p>';
		}
		echo '</div>';
	}

	// Output Family
	if ($type == "fam") {
		echo '<div id="find-output">';
		// Get the famrecs with hits on names from the family table
		// Get the famrecs with hits in the gedcom record from the family table
		$myfamlist = array_unique(array_merge(
			search_fams_names($filter_array, array(KT_GED_ID), 'AND'),
			search_fams($filter_array, array(KT_GED_ID), 'AND', true)
		));

		if ($myfamlist) {
			$curged = $GEDCOM;
			echo '<ul>';
			usort($myfamlist, array('KT_GedcomRecord', 'Compare'));
			foreach ($myfamlist as $family) {
				echo $family->format_list('li', true);
			}
			echo '</ul>
			<p>', KT_I18N::translate('Total families: %s', count($myfamlist)), '</p>';
		} else {
			echo '<p>', KT_I18N::translate('No results found'), '</p>';
		}
		echo '</div>';
	}

	// Output Media
	if ($type == "media") {
		global $dirs;

		$medialist = KT_Query_Media::mediaList('', 'include', 'title', $filter, '');

		echo '<div id="find-output">';

		if ($medialist) {
			foreach ($medialist as $media) {
				echo '<div class="find-media-media">';
				echo '<div class="find-media-thumb">', $media->displayImage(), '</div>';
				echo '<div class="find-media-details">', $media->getFullName(), '</div>';
				if (!$embed) {
					echo '<p><a href="#" dir="auto" onclick="pasteid(\'', addslashes($media->getXref()), '\');">', $media->getFilename(), '</a></p>';
				} else {
					echo '<p><a href="#" dir="auto" onclick="pasteid(\'', $media->getXref(), '\', \'', '\', \'', addslashes($media->getFilename()), '\');">', $media->getFilename(), '</a></p> ';
				}
				if ($media->fileExists()) {
					$imgsize = $media->getImageAttributes();
					echo KT_Gedcom_Tag::getLabelValue('__IMAGE_SIZE__', $imgsize['WxH']);
				}
				echo '<ul>';
				$found=false;
				foreach ($media->fetchLinkedIndividuals() as $indindividual) {
					echo '<li>', $indindividual->getFullName(), '</li>';
					$found=true;
				}
				foreach ($media->fetchLinkedFamilies() as $family) {
					echo '<li>', $family->getFullName(), '</li>';
					$found=true;
				}
				foreach ($media->fetchLinkedSources() as $source) {
					echo '<li>', $source->getFullName(), '</li>';
					$found=true;
				}
				foreach ($media->fetchLinkedNotes() as $note) {
					echo '<li>', $note->getFullName(), '</li>';
					$found=true;
				}
				foreach ($media->fetchLinkedRepositories() as $repository) {
					echo '<li>', $repository->getFullName(), '</li>';
					$found=true;
				}
				if (!$found) {
					echo '<li>', KT_I18N::translate('This media object is not linked to any other record.'), '</li>';
				}
				echo '</ul>';
				echo '</div>'; // close div="find-media-media"
			}
		} else {
			echo '<p>', KT_I18N::translate('No results found'), '</p>';
		}
		echo '</div>';
	}

	// Output Places
	if ($type == "place") {
		echo '<div id="find-output">';
		if (!$filter || $all) {
			$places = KT_Place::allPlaces(KT_GED_ID);
		} else {
			$places = KT_Place::findPlaces($filter, KT_GED_ID);
		}
		if ($places) {
			echo '<ul>';
			foreach ($places as $place) {
				echo '<li><a href="#" onclick="pasteid(\'', htmlspecialchars((string) $place->getGedcomName()), '\');">';
				if (!$filter || $all) {
					echo $place->getReverseName(); // When displaying all names, sort/display by the country, then region, etc.
				} else {
					echo $place->getFullName(); // When we’ve searched for a place, sort by this place
				}
				echo '</a></li>';
			}
			echo '</ul>
			<p>', KT_I18N::translate('Places found'), '&nbsp;', count($places), '</p>';
		}
		else {
			echo '<p>', KT_I18N::translate('No results found'), '</p>';
		}
		echo '</div>';
	}

	// Output Repositories
	if ($type == "repo") {
		echo '<div id="find-output">';
		if ($filter) {
			$repo_list = search_repos($filter_array, array(KT_GED_ID), 'AND', true);
		} else {
			$repo_list = get_repo_list(KT_GED_ID);
		}
		if ($repo_list) {
			usort($repo_list, array('KT_GedcomRecord', 'Compare'));
			echo '<ul>';
			foreach ($repo_list as $repo) {
				echo '<li><a href="', $repo->getHtmlUrl(), '" onclick="pasteid(\'', $repo->getXref(), '\');"><span class="list_item">', $repo->getFullName(),'</span></a></li>';
			}
			echo '</ul>
			<p>', KT_I18N::translate('Repositories found'), " ", count($repo_list), '</p>';
		}
		else {
			echo '<p>', KT_I18N::translate('No results found'), '</p>';
		}
		echo '</div>';
	}

	// Output Shared Notes
	if ($type=="note") {
		echo '<div id="find-output">';
		if ($filter) {
			$mynotelist = search_notes($filter_array, array(KT_GED_ID), 'AND', true);
		} else {
			$mynotelist = get_note_list(KT_GED_ID);
		}
		if ($mynotelist) {
			usort($mynotelist, array('KT_GedcomRecord', 'Compare'));
			echo '<ul>';
			foreach ($mynotelist as $note) {
				echo '<li><a href="', $note->getHtmlUrl(), '" onclick="pasteid(\'', $note->getXref(), '\');"><span class="list_item">', $note->getFullName(),'</span></a></li>';
			}
			echo '</ul>
			<p>', KT_I18N::translate('Shared Notes found'), ' ', count($mynotelist), '</p>';
		}
		else {
			echo '<p>', KT_I18N::translate('No results found'), '</p>';
		}
		echo '</div>';
	}

	// Output Sources
	if ($type=="source") {
		echo '<div id="find-output">';
		if ($filter) {
			$mysourcelist = search_sources($filter_array, array(KT_GED_ID), 'AND', true);
		} else {
			$mysourcelist = get_source_list(KT_GED_ID);
		}
		if ($mysourcelist) {
			usort($mysourcelist, array('KT_GedcomRecord', 'Compare'));
			echo '<ul>';
			foreach ($mysourcelist as $source) {
				echo '<li><a href="', $source->getHtmlUrl(), '" onclick="pasteid(\'', $source->getXref(), '\', \'',
					KT_Filter::escapeJs($source->getFullName()), '\');"><span class="list_item">',
					$source->getFullName(),'</span></a></li>';
			}
			echo '</ul>
			<p>', KT_I18N::translate('Total sources: %s', count($mysourcelist)), '</p>';
		}
		else {
			echo '<p>', KT_I18N::translate('No results found'), '</p>';
		}
		echo '</div>';
	}

	// Output Special Characters
	if ($type == "specialchar") {
		echo '<div id="find-output-special"><p>';
		// lower case special characters
		foreach ($lcspecialchars as $key=>$value) {
			echo '<a class="largechars" href="#" onclick="return window.opener.paste_char(\'', $value, '\');">', $key, '</a> ';
		}
		echo '</p><p>';
		//upper case special characters
		foreach ($ucspecialchars as $key=>$value) {
			echo '<a class="largechars" href="#" onclick="return window.opener.paste_char(\'', $value, '\');">', $key, '</a> ';
		}
		echo '</p><p>';
		// other special characters (not letters)
		foreach ($otherspecialchars as $key=>$value) {
			echo '<a class="largechars" href="#" onclick="return window.opener.paste_char(\'', $value, '\');">', $key, '</a> ';
		}
		echo '</p></div>';
	}
}
echo '<button class="btn btn-primary show" onclick="window.close();">
		<i class="fa fa-times"></i>' .
		KT_I18N::translate('close') . '
	</button>';

echo '</div>'; // Close div="find-page"
