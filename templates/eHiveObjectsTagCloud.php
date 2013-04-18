<?php
/*
	Copyright (C) 2012 Vernon Systems Limited

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
if ($css_class == "") {
	echo '<div class="ehive-tag-cloud">';
} else {
	echo '<div class="ehive-tag-cloud '.$css_class.'">';
}

if (!isset($eHiveApiErrorMessage)) {
	foreach ($tagCloud->tagCloudTags as $tagCloudTag) {
		switch ($tagCloudTag->percentage) {
			case (int) $tagCloudTag->percentage > 95:
				$level ="10";
			    break;
	        case (int) $tagCloudTag->percentage > 90:
	            $level ="9";
	            break;
	        case (int)$tagCloudTag->percentage > 80:
	            $level ="8";
	            break;
	        case (int)$tagCloudTag->percentage > 70:
	            $level ="7";
				break;
	        case (int)$tagCloudTag->percentage > 60:
	            $level ="6";
	            break;
	        case (int)$tagCloudTag->percentage > 50:
	            $level ="5";
	            break;
	        case (int)$tagCloudTag->percentage > 40:
	            $level ="4";
	          	break;
	        case (int)$tagCloudTag->percentage > 30:
	            $level ="3";
	            break;
	        case (int)$tagCloudTag->percentage > 20:
	            $level ="2";
	          	break;
	        case (int)$tagCloudTag->percentage <= 20:
	          	$level ="1";
	            break;
		}
	
		if (isset($eHiveSearch)) {			
	       	$link = $eHiveAccess->getSearchPageLink( "?{$searchOptions['query_var']}=tag:{$tagCloudTag->cleanTagName}" );
		} else {
			$link = '#';
		}
	    echo  "<a class='ehive-tag-{$level}' href='{$link}'>{$tagCloudTag->cleanTagName}</a> ";
	}
} else {
	echo "<p class='ehive-error-message ehive-account-details-error'>$eHiveApiErrorMessage</p>";
}
echo "</div>"
?>
