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

/* global TextFollow, OptionsNumBlocks, RootID, ModuleName */

// ADMIN JS
	// descendants version
    jQuery(".ftv_tree").change(function(){
        // get the config page for the selected tree
        var ged = jQuery(this).find("option:selected").data("ged");
        window.location = "module.php?mod=fancy_treeview&mod_action=admin_config&ged=" + ged;
    });
	// ancestors version
	jQuery(".ftvp_tree").change(function(){
        // get the config page for the selected tree
        var ged = jQuery(this).find("option:selected").data("ged");
        window.location = "module.php?mod=fancy_treeview_pedigree&mod_action=admin_config&ged=" + ged;
    });

    // make sure not both the surname and the root_id can not be set at the same time.
    jQuery("input.root_id").prop("disabled", true);
    var find_indi = jQuery(".icon-button_indi:first").attr("onclick");
    jQuery(".icon-button_indi:first").removeAttr("onclick").css("cursor", "default");
    jQuery("input[name=unlock_field]").click(function(){
        if(jQuery(this).prop("checked")) {
            jQuery("input.surname").prop("disabled", true).val("");
            jQuery("input.root_id").prop("disabled", false).focus();
            jQuery(".icon-button_indi:first").attr("onclick", find_indi).css("cursor", "pointer");
        }
        else {
            jQuery("input.root_id").prop("disabled", true).val("");
            jQuery("input.surname").prop("disabled", false).focus();
            jQuery(".icon-button_indi:first").removeAttr("onclick").css("cursor", "default");
        }
    });

    // click on a surname to get an input textfield to change the surname to a more appropriate name. This can not be used if \'Use fullname in menu\' is checked.
    var handler = function(){
        jQuery(this).hide();
        jQuery(this).next(".editname").show();
    };

    jQuery(".showname").click(handler);

    jQuery(".fullname input[type=checkbox]").click(function() {
        if (jQuery(this).prop("checked")) {
            jQuery(".showname").show().css("cursor", "move");
            jQuery(".editname").hide();
            jQuery(".showname").off("click", handler);
        }
        else {
            jQuery(".showname").on("click", handler).css("cursor", "text");
        }
    });

    // make the table sortable
    jQuery("#fancy_treeview-table").sortable({items: ".sortme", forceHelperSize: true, forcePlaceholderSize: true, opacity: 0.7, cursor: "move", axis: "y"});

    //-- update the order numbers after drag-n-drop sorting is complete
    jQuery("#fancy_treeview-table").bind("sortupdate", function(event, ui) {
        jQuery("#"+jQuery(this).attr("id")+" input[type=hidden]").each(
            function (index, value) {
                value.value = index+1;
            }
        );
    });

    jQuery("#ftv-options-form").on("click", "#show_imgs input[type=radio]", function () {
        var field = jQuery("#ftv-options-form").find("#images");
        jQuery(this).val() === "1" ? field.fadeIn() : field.fadeOut();
    });

	jQuery("#ftv-options-form").on("click", "#resize_thumbs input[type=radio]", function () {
        var field = jQuery("#ftv-options-form").find("#thumb_size, #square_thumbs");
        jQuery(this).val() === "1" ? field.fadeIn() : field.fadeOut();
    });

    jQuery("#ftv-options-form").on("click", "#ftv_places input[type=radio]", function () {
    	var field1 = jQuery("#ftv-options-form").find("#gedcom_places");
    	var field2 = jQuery("#ftv-options-form").find("#country_list");
    	if (jQuery(this).val() === "1") {
    		field1.fadeIn();
    		if (field1.find("input[type=radio]:checked").val() === "0"){field2.fadeIn(); }
    	} else {
    		field1.fadeOut();
    		field2.fadeOut();
    	}
    });

    jQuery("#ftv-options-form").on("click", "#gedcom_places input[type=radio]", function () {
    	var field = jQuery("#ftv-options-form").find("#country_list");
    	jQuery(this).val() === "0" ? field.fadeIn() : field.fadeOut();
    });

    if (jQuery("#gedcom_places input[type=checkbox]").is(":checked")) jQuery("#country_list select").prop("disabled", true);
    else jQuery("#country_list select").prop("disabled", false);
    jQuery("#gedcom_places input[type=checkbox]").click(function(){
        if (this.checked) jQuery("#country_list select").prop("disabled", true);
        else jQuery("#country_list select").prop("disabled", false);
    });

	// descendants version
    jQuery("button.ftv_reset").click(function(e){
        jQuery("#dialog-confirm").dialog({
            resizable: false,
            width: 400,
            modal: true,
            buttons : {
                [TextOk] : function() {
                    window.location.href= "module.php?mod=fancy_treeview&mod_action=admin_reset";
                    jQuery(this).dialog("close");
                },
                [TextCancel] : function() {
                    jQuery(this).dialog("close");
                }
            }
        });
    });

	// descendants version
    jQuery("button.ftvp_reset").click(function(e){
        jQuery("#dialog-confirm").dialog({
            resizable: false,
            width: 400,
            modal: true,
            buttons : {
                [TextOk] : function() {
                    window.location.href= "module.php?mod=fancy_treeview_pedigree&mod_action=admin_reset";
                    jQuery(this).dialog("close");
                },
                [TextCancel] : function() {
                    jQuery(this).dialog("close");
                }
            }
        });
    });

//PAGE JS
    // setup numbers for scroll reference
    function addScrollNumbers() {
        jQuery(".generation-block:visible").each(function() {
            jQuery(this).find("a.scroll").each(function() {
                if(jQuery(this).text() === "" || jQuery(this).hasClass("add_num")) {
                    var id = jQuery(this).attr("href");
                    var fam_id = jQuery(id);
                    var fam_id_index = fam_id.index() + 1;
                    var gen_id_index = fam_id.parents(".generation-block").data("gen");
                    if (fam_id.length > 0) {
    					jQuery(this).text(TextFollow + " " + gen_id_index + "." + fam_id_index).removeClass("add_num");
    				} else { // fam to follow is in a generation block after the last hidden block.
    					jQuery(this).text(TextFollow).addClass("add_num");
    				}
                }
            });
        });
        if (jQuery(".generation-block.hidden").length > 0) { // there are next generations so prepare the links
            jQuery(".generation-block.hidden").prev().find("a.scroll").not(".header-link").addClass("link_next").removeClass("scroll");
        }
    }

    function scrollToTarget(id) {
    	var offset = 60;
    	var target = jQuery(id).offset().top - offset;
    	jQuery("html, body").animate({
    		scrollTop: target
    	}, 1000);
    	return false;
    }

    // remove button if there are no more generations to catch
    function btnRemove() {
        if (jQuery(".generation-block.hidden").length == 0) { // if there is no hidden block there is no next generation.
            jQuery("#btn_next").remove();
        }
    }

    // set style dynamically on parents blocks with an image
    function setImageBlock() {
        jQuery(".parents").each(function(){
            if(jQuery(this).find(".gallery").length > 0) {
                var height = jQuery(this).find(".gallery img").height() + 10 + "px";
                jQuery(this).css({"min-height" : height});
            }
        });
    }

    // Hide last generation block (only needed in the DOM for scroll reference. Must be set before calling addScrollNumbers function.)
    var lastBlock = jQuery(".generation-block:last");
    if (OptionsNumBlocks > 0 && lastBlock.data("gen") > OptionsNumBlocks) {
    	lastBlock.addClass("hidden").hide();
    }

    // add scroll numbers to visible generation blocks when page is loaded
    addScrollNumbers();

    // Remove button if there are no more generations to catch
    btnRemove();

    // Set css class on parents blocks with an image
    setImageBlock();

    // remove the empty hyphen on childrens lifespan if death date is unknown.
    jQuery("li.child .lifespan").html(function(index, html){
        return html.replace("–<span title=\"&nbsp;\"></span>", "");
    });

    // prevent duplicate id\'s
    jQuery("li.family[id]").each(function(){
        var family = jQuery("[id="+this.id+"]");
        if(family.length>1){
            i = 1;
            family.each(function(){
                famID = jQuery(this).attr("id");
                anchor = jQuery("#fancy_treeview a.scroll[href$="+this.id+"]:first");
                anchor.attr("href", "#" + famID + "_" + i);
                jQuery(this).attr("id", famID + "_" + i);
                i++;
            });
        }
    });

    // Print extra information about the non-married spouse (the father/mother of the children) in a tooltip
    jQuery(".tooltip").each(function(){
        var text = jQuery(this).next(".tooltip-text").html();
        jQuery(this).tooltip({
           items: "[title]",
           content: function() {
             return text;
           }
        });
    });

    // scroll to anchors
    jQuery("#fancy_treeview-page").on("click", ".scroll", function (event) {
    	var id = jQuery(this).attr("href");
    	if (jQuery(id).is(":hidden") || jQuery(id).length === 0) {
    		jQuery(this).addClass("link_next").trigger("click");
    		return false;
    	}
    	scrollToTarget(id);
    });

    //button or link to retrieve next generations
    jQuery("#fancy_treeview-page").on("click", "#btn_next button, .link_next", function(){
        if(jQuery(this).hasClass("link_next")) { // prepare for scrolling after new blocks are loaded
            var id = jQuery(this).attr("href");
            scroll = true;
        }

        // remove the last hidden block to retrieve the correct data from the previous last block
        jQuery(".generation-block.hidden").remove();

        var numBlocks = OptionsNumBlocks;
        var lastBlock = jQuery(".generation-block:last");
        var pids = lastBlock.data("pids");
        var gen  = lastBlock.data("gen");
        var url = jQuery(location).attr("pathname") + "?mod=" + ModuleName + "&mod_action=show&rootid=" + RootID + "&gen=" + gen + "&pids=" + pids;
        lastBlock.find("a.link_next").addClass("scroll").removeClass("link_next");
        lastBlock.after("<div class=\"loading-image\">");
        jQuery("#btn_next").hide();

        jQuery.get(url, function(data){
                var blocks = jQuery(".generation-block", data);
                jQuery(lastBlock).after(blocks);
                // hidden block must be set before calling addScrollNumbers function.
        		if (blocks.length === numBlocks + 1) {
        			jQuery(".generation-block:last").addClass("hidden").hide();
        		}

                // scroll
                addScrollNumbers();
                if (scroll === true) {
                    scrollToTarget(id);
                }

                jQuery(".loading-image").remove();
                jQuery("#btn_next").show();

                // check if button has to be removed
                btnRemove();

                // check for parents blocks with images
                setImageBlock();
            }
        );
    });
