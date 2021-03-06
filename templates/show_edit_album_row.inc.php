<?php
/* vim:set softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * LICENSE: GNU General Public License, version 2 (GPLv2)
 * Copyright 2001 - 2013 Ampache.org
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License v2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */
?>
<div>
    <form method="post" id="edit_album_<?php echo $album->id; ?>" class="edit_dialog_content">
        <table class="tabledata" cellspacing="0" cellpadding="0">
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Name') ?></td>
                <td><input type="text" name="name" value="<?php echo scrub_out($album->full_name); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Artist') ?></td>
                <td>
                    <?php
                    /*if ($album->artist_count == '1') {*/
                        show_artist_select('artist', $album->artist_id);
                    /*} else {
                        echo T_('Various');
                    }*/
                    ?>
                </td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Year') ?></td>
                <td><input type="text" name="year" value="<?php echo scrub_out($album->year); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Disk') ?></td>
                <td><input type="text" name="disk" value="<?php echo scrub_out($album->disk); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('MusicBrainz ID') ?></td>
                <td><input type="text" name="mbid" value="<?php echo $album->mbid; ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Tags') ?></td>
                <td>
                    <input type="text" name="edit_tags" id="edit_tags" value="<?php echo Tag::get_display($album->tags); ?>" />
                </td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"></td>
                <td><input type="checkbox" name="apply_childs" value="checked" /><?php echo T_(' Apply tags to all childs (override tags for songs)') ?></td>
            </tr>
        </table>
        <input type="hidden" name="id" value="<?php echo $album->id; ?>" />
        <input type="hidden" name="type" value="album_row" />
    </form>
</div>
