<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class advent_WT_Module extends WT_Module implements WT_Module_Block {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Advent calendar');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “HTML” module */ WT_I18N::translate('Add your own advent calendar to your Home page.');
	}

	// Implement class WT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class WT_Module_Block
	public function isUserBlock() {
		return false;
	}

	// Implement class WT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	public function configureBlock($block_id) {
		return false;
	}

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $controller;

		$id		 = $this->getName() . $block_id;
		$title	 = '';
		$class	 = '';
		$content = '';
		$year	 = '2015'; // set this for each year

		/* Hidden tips */
		/* CHANGE THESE by replacing the text parts with whatever you prefer, in your own language */
		$texts = array(
		  '1' =>  'Always cite your sources, always.',
		  '2' =>  'Never, ever assume another researcher\'s informations is 100% correct.',
		  '3' =>  'Remember that every name has variations. Check them all.',
		  '4' =>  'Never copy another person\'s tree. use it only as a helpful aid.',
		  '5' =>  'Share freely with others and they will share with you.',
		  '6' =>  'Don\'t trust your assumptions, only sources.',
		  '7' =>  'Check and double check your facts before recording or sharing.',
		  '8' =>  'Take a second look at records. Your answer may be right in front of you.',
		  '9' =>  'Don\'t limit yourself to only one or two research sites. Look for options.',
		  '10' => 'Go offline or order hard copies to expand your research.',
		  '11' => 'Back up your files monthly. Keep at least two copies of your research.',
		  '12' => 'Never give up. You may not find what you are looking for but the journey is always worth the effort.',
		  '13' => 'Always ask your family for details first. Someone in your family is likely to know more.',
		  '14' => 'Family tree research is one giant step backwards and one giant step forward. Uusually at the same time.',
		  '15' => 'Genealogy is like a magic mirror. Look into it, and pretty soon, interesting faces appear.',
		  '16' => 'Genealogy is sometimes about proving that bad family traits came from the other side of the tree!',
		  '17' => 'Remember that just because information is on computer or in print, it ain\'t necessarily fact!',
		  '18' => 'Family traditions of close connections to famous people are usually false, but there may be a more obscure link to check.',
		  '19' => 'Death certificates are rarely filled in by the person who died.',
		  '20' => 'The Internet does NOT have all records online, not by a long way. But we all know this anyway, don\'t we!',
		  '21' => 'Verify EVERYTHING. As the saying goes, "genealogy without documentation is mythology".',
		  '22' => 'Click to vew the <a href="http://www.our-families.info/module.php?mod=simpl_pages&mod_action=show&pages_id=977" target=_blank"><u>Family Tree Rhapsody video</u></a> - in case you havn\'t seen it.</a>',
		  '23' => 'Click to vew the <a href="http://www.our-families.info/module.php?mod=simpl_pages&mod_action=show&pages_id=973" target=_blank"><u>I\'m My Own Grandpa video</u></a> - in case you havn\'t seen it.</a>',
		  '24' => 'Genealogy is not just a pastime, it’s a passion! Happy Xmas, and a New Year that brings successful research.'
		);

		$content = '<link type="text/css" href ="' . WT_STATIC_URL . WT_MODULES_DIR . $this->getName() . '/css/advent.css" rel="stylesheet">';

		$controller
			->addExternalJavascript (WT_STATIC_URL . WT_MODULES_DIR . $this->getName() . '/js/advent.js')
			->addExternalJavascript (WT_STATIC_URL . WT_MODULES_DIR . $this->getName() . '/js/moment.js')
			->addInlineJavascript ('
				var today	= moment();
				var advent	= moment("2015-12-1");
		        for ( var i = 0; i < 24; i++) {
					var attr = moment(advent).add(i, "days");
					if ( attr.isAfter(today) ) {
		                jQuery("time[datetime=" + attr.format("YYYY-M-D") + "]").parent().removeClass("available");
						jQuery("time[datetime=" + attr.format("YYYY-M-D") + "]" ).next("div").children().remove();
		            }
		        };
			');

		$content .=
			'<div id="advent_block">
				<div id="advent_wrapper">
					<h1>Advent ' . $year . ' Genealogy Tips</h1>
					<p>
						Welcome to our advent calendar. Every day in December' . $year . ' (until the 24th) we will post new tips and ideas to help you.
					</p>
		            <ul class="doors">';
		              for( $i = 1 ; $i < 25 ; $i++ ) {
		                $content .=  '
		                  <li class="available">
		                    <time class="door" datetime="' . $year . '-12-' . $i . '"></time>
		                    <div class="doorway">';
								/* random text colors */
								$rand	  = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
								$color	  = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
		                    	$content .= '<span class="text" style="color:' . $color . ';">' . $texts[$i] . '</span>
		                    </div>
		                  </a>
		                </li>';
		              }
		            $content .= '</ul>
				</div>
			</div>';

		if ($template) {
			require WT_THEME_DIR.'templates/block_main_temp.php';
		} else {
			return $content;
		}
	}
}
