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

if ($playlist) {
    $logic_operator = $playlist->logic_operator;
} else {
    $logic_operator = $_REQUEST['operator'];
}
$logic_operator = strtolower($logic_operator);
?>
<script type="text/javascript" src="<?php echo AmpConfig::get('web_path'); ?>/lib/javascript/search.js"></script>
<script type="text/javascript" src="<?php echo AmpConfig::get('web_path'); ?>/lib/javascript/search-data.php?type=<?php echo $_REQUEST['type'] ? scrub_out($_REQUEST['type']) : 'song'; ?>"></script>

<?php UI::show_box_top(T_('Rules') . "...", 'box box_rules'); ?>
<table class="tabledata" cellpadding="3" cellspacing="0">
<tbody id="searchtable">
    <tr id="rules_operator">
    <td><?php echo T_('Match'); ?></td>
        <td>
                <select name="operator">
                        <option value="and" <?php if($logic_operator == 'and') echo 'selected="selected"'?>><?php echo T_('all rules'); ?></option>
                        <option value="or"  <?php if($logic_operator == 'or') echo 'selected="selected"'?>><?php echo T_('any rule'); ?></option>
                </select>
        </td>
        </tr>
    <tr id="rules_addrowbutton">
    <td>
        <a id="addrowbutton" href="javascript:void(0)">
            <?php echo UI::get_icon('add'); ?>
        <?php echo T_('Add Another Rule'); ?>
        </a>
        <script type="text/javascript">$('#addrowbutton').on('click', SearchRow.add);</script>
    </td>
    </tr>
</tbody>
</table>
<?php UI::show_box_bottom(); ?>

<?php
if ($playlist) {
    $out = $playlist->to_js();
} else {
    $mysearch = new Search($_REQUEST['type']);
    $mysearch->parse_rules(Search::clean_request($_REQUEST));
    $out = $mysearch->to_js();
}
if ($out) {
    echo $out;
} else {
    echo '<script type="text/javascript">SearchRow.add();</script>';
}
?>
