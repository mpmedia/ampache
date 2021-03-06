<?php
/* vim:set softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * This contains functions that are generic, and display information
 * things like a confirmation box, etc and so forth
 *
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

/**
 * show_confirmation
 *
 * shows a confirmation of an action
 *
 * @param    string    $title    The Title of the message
 * @param    string    $text    The details of the message
 * @param    string    $next_url    Where to go next
 * @param    integer    $cancel    T/F show a cancel button that uses return_referrer()
 * @return    void
 */
function show_confirmation($title,$text,$next_url,$cancel=0,$form_name='confirmation')
{
    if (substr_count($next_url,AmpConfig::get('web_path'))) {
        $path = $next_url;
    } else {
        $path = AmpConfig::get('web_path') . "/$next_url";
    }

    require AmpConfig::get('prefix') . '/templates/show_confirmation.inc.php';
} // show_confirmation

/**
 * return_referer
 * returns the script part of the referer address passed by the web browser
 * this is not %100 accurate. Also because this is not passed by us we need
 * to clean it up, take the filename then check for a /admin/ and dump the rest
 */
function return_referer()
{
    $referer = $_SERVER['HTTP_REFERER'];
    if (substr($referer, -1)=='/') {
        $file = 'index.php';
    } else {
        $file = basename($referer);
        /* Strip off the filename */
        $referer = substr($referer,0,strlen($referer)-strlen($file));
    }

    if (substr($referer,strlen($referer)-6,6) == 'admin/') {
        $file = 'admin/' . $file;
    }

    return $file;

} // return_referer

/**
 * get_location
 * This function gets the information about a person's current location.
 * This is used for A) sidebar highlighting & submenu showing and B) titlebar
 * information. It returns an array of information about what they are currently
 * doing.
 * Possible array elements
 * ['title']    Text name for the page
 * ['page']    actual page name
 * ['section']    name of the section we are in, admin, browse etc (submenu)
 */
function get_location()
{
    $location = array();

    if (strlen($_SERVER['PHP_SELF'])) {
        $source = $_SERVER['PHP_SELF'];
    } else {
        $source = $_SERVER['REQUEST_URI'];
    }

    /* Sanatize the $_SERVER['PHP_SELF'] variable */
    $source = str_replace(AmpConfig::get('raw_web_path'), "", $source);
    $location['page'] = preg_replace("/^\/(.+\.php)\/?.*/","$1",$source);

    switch ($location['page']) {
        case 'index.php':
            $location['title']     = T_('Home');
            break;
        case 'upload.php':
            $location['title']     = T_('Upload');
            break;
        case 'localplay.php':
            $location['title']     = T_('Local Play');
            break;
        case 'randomplay.php':
            $location['title']     = T_('Random Play');
            break;
        case 'playlist.php':
            $location['title']     = T_('Playlist');
            break;
        case 'search.php':
            $location['title']     = T_('Search');
            break;
        case 'preferences.php':
            $location['title']     = T_('Preferences');
            break;
        case 'admin/index.php':
            $location['title']     = T_('Admin-Catalog');
            $location['section']    = 'admin';
            break;
        case 'admin/catalog.php':
            $location['title']     = T_('Admin-Catalog');
            $location['section']    = 'admin';
            break;
        case 'admin/users.php':
            $location['title']    = T_('Admin-User Management');
            $location['section']    = 'admin';
            break;
        case 'admin/mail.php':
            $location['title']    = T_('Admin-Mail Users');
            $location['section']    = 'admin';
            break;
        case 'admin/access.php':
            $location['title']    = T_('Admin-Manage Access Lists');
            $location['section']    = 'admin';
            break;
        case 'admin/preferences.php':
            $location['title']    = T_('Admin-Site Preferences');
            $location['section']    = 'admin';
            break;
        case 'admin/modules.php':
            $location['title']    = T_('Admin-Manage Modules');
            $location['section']    = 'admin';
            break;
        case 'browse.php':
            $location['title']    = T_('Browse Music');
            $location['section']    = 'browse';
            break;
        case 'albums.php':
            $location['title']    = T_('Albums');
            $location['section']    = 'browse';
            break;
        case 'artists.php':
            $location['title']    = T_('Artists');
            $location['section']    = 'browse';
            break;
        case 'stats.php':
            $location['title']    = T_('Statistics');
            break;
        default:
            $location['title'] = '';
            break;
    } // switch on raw page location

    return $location;

} // get_location

/**
 * show_preference_box
 * This shows the preference box for the preferences pages.
 */
function show_preference_box($preferences)
{
    require AmpConfig::get('prefix') . '/templates/show_preference_box.inc.php';

} // show_preference_box

static $album_id_cnt = 0;

/**
 * show_album_select
 * This displays a select of every album that we've got in Ampache (which can be
 * hella long). It's used by the Edit page and takes a $name and a $album_id
 */
function show_album_select($name='album',$album_id=0,$allow_add=0,$song_id=0)
{
    // Generate key to use for HTML element ID
    if ($song_id) {
        $key = "album_select_$song_id";
    } else {
        $key = "album_select_c" . ++$album_id_cnt;
    }

    // Added ID field so we can easily observe this element
    echo "<select name=\"$name\" id=\"$key\">\n";

    $sql = "SELECT `id`, `name`, `prefix`, `disk` FROM `album` ORDER BY `name`";
    $db_results = Dba::read($sql);

    while ($r = Dba::fetch_assoc($db_results)) {
        $selected = '';
        $album_name = trim($r['prefix'] . " " . $r['name']);
        if ($r['disk'] >= 1) {
            $album_name .= ' [Disk ' . $r['disk'] . ']';
        }
        if ($r['id'] == $album_id) {
            $selected = "selected=\"selected\"";
        }

        echo "\t<option value=\"" . $r['id'] . "\" $selected>" . scrub_out($album_name) . "</option>\n";

    } // end while

    if ($allow_add) {
        // Append additional option to the end with value=-1
        echo "\t<option value=\"-1\">" . T_('Add New') . "...</option>\n";
    }

    echo "</select>\n";

} // show_album_select

static $artist_id_cnt = 0;

/**
 * show_artist_select
 * This is the same as show_album_select except it's *gasp* for artists! How
 * inventive!
 */
function show_artist_select($name='artist', $artist_id=0, $allow_add=0, $song_id=0)
{
    // Generate key to use for HTML element ID
    if ($song_id) {
        $key = "artist_select_$song_id";
    } else {
        $key = "artist_select_c" . ++$artist_id_cnt;
    }

    echo "<select name=\"$name\" id=\"$key\">\n";

    $sql = "SELECT `id`, `name`, `prefix` FROM `artist` ORDER BY `name`";
    $db_results = Dba::read($sql);

    while ($r = Dba::fetch_assoc($db_results)) {
        $selected = '';
        $artist_name = trim($r['prefix'] . " " . $r['name']);
        if ($r['id'] == $artist_id) {
            $selected = "selected=\"selected\"";
        }

        echo "\t<option value=\"" . $r['id'] . "\" $selected>" . scrub_out($artist_name) . "</option>\n";

    } // end while

    if ($allow_add) {
        // Append additional option to the end with value=-1
        echo "\t<option value=\"-1\">Add New...</option>\n";
    }

    echo "</select>\n";

} // show_artist_select

/**
 * show_catalog_select
 * Yet another one of these buggers. this shows a drop down of all of your
 * catalogs.
 */
function show_catalog_select($name='catalog',$catalog_id=0,$style='')
{
    echo "<select name=\"$name\" style=\"$style\">\n";

    $sql = "SELECT `id`, `name` FROM `catalog` ORDER BY `name`";
    $db_results = Dba::read($sql);

    while ($r = Dba::fetch_assoc($db_results)) {
        $selected = '';
        if ($r['id'] == $catalog_id) {
            $selected = "selected=\"selected\"";
        }

        echo "\t<option value=\"" . $r['id'] . "\" $selected>" . scrub_out($r['name']) . "</option>\n";

    } // end while

    echo "</select>\n";

} // show_catalog_select

/**
 * show_user_select
 * This one is for users! shows a select/option statement so you can pick a user
 * to blame
 */
function show_user_select($name,$selected='',$style='')
{
    echo "<select name=\"$name\" style=\"$style\">\n";
    echo "\t<option value=\"\">" . T_('All') . "</option>\n";

    $sql = "SELECT `id`,`username`,`fullname` FROM `user` ORDER BY `fullname`";
    $db_results = Dba::read($sql);

    while ($row = Dba::fetch_assoc($db_results)) {
        $select_txt = '';
        if ($row['id'] == $selected) {
            $select_txt = 'selected="selected"';
        }
        // If they don't have a full name, revert to the username
        $row['fullname'] = $row['fullname'] ? $row['fullname'] : $row['username'];

        echo "\t<option value=\"" . $row['id'] . "\" $select_txt>" . scrub_out($row['fullname']) . "</option>\n";
    } // end while users

    echo "</select>\n";

} // show_user_select

/**
 * show_playlist_select
 * This one is for playlists!
 */
function show_playlist_select($name,$selected='',$style='')
{
    echo "<select name=\"$name\" style=\"$style\">\n";
    echo "\t<option value=\"\">" . T_('None') . "</option>\n";

    $sql = "SELECT `id`,`name` FROM `playlist` ORDER BY `name`";
    $db_results = Dba::read($sql);
    $nb_items = Dba::num_rows($db_results);
    $index = 1;
    $already_selected = false;

    while ($row = Dba::fetch_assoc($db_results)) {
        $select_txt = '';
        if (!$already_selected && ($row['id'] == $selected || $index == $nb_items)) {
            $select_txt = 'selected="selected"';
            $already_selected = true;
        }

        echo "\t<option value=\"" . $row['id'] . "\" $select_txt>" . scrub_out($row['name']) . "</option>\n";
        ++$index;
    } // end while users

    echo "</select>\n";

} // show_playlist_select

function xoutput_headers()
{
    $output = isset($_REQUEST['xoutput']) ? $_REQUEST['xoutput'] : 'xml';
    if ($output == 'xml') {
        header("Content-type: text/xml; charset=" . AmpConfig::get('site_charset'));
        header("Content-Disposition: attachment; filename=ajax.xml");
    } else {
        header("Content-type: application/json; charset=" . AmpConfig::get('site_charset'));
    }

    header("Expires: Tuesday, 27 Mar 1984 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
}

function xoutput_from_array($array, $callback = false, $type = '')
{
    $output = isset($_REQUEST['xoutput']) ? $_REQUEST['xoutput'] : 'xml';
    if ($output == 'xml') {
        return xml_from_array($array, $callback, $type);
    } elseif ($output == 'raw') {
        $outputnode = $_REQUEST['xoutputnode'];
        return $array[$outputnode];
    } else {
        return json_from_array($array, $callback, $type);
    }
}

// FIXME: This should probably go in XML_Data
/**
 * xml_from_array
 * This takes a one dimensional array and creates a XML document from it. For
 * use primarily by the ajax mojo.
 */
function xml_from_array($array, $callback = false, $type = '')
{
    $string = '';

    // If we weren't passed an array then return
    if (!is_array($array)) { return $string; }

    // The type is used for the different XML docs we pass
    switch ($type) {
    case 'itunes':
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $value = xoutput_from_array($value,1,$type);
                $string .= "\t\t<$key>\n$value\t\t</$key>\n";
            } else {
                if ($key == "key") {
                $string .= "\t\t<$key>$value</$key>\n";
                } elseif (is_int($value)) {
                $string .= "\t\t\t<key>$key</key><integer>$value</integer>\n";
                } elseif ($key == "Date Added") {
                $string .= "\t\t\t<key>$key</key><date>$value</date>\n";
                } elseif (is_string($value)) {
                /* We need to escape the value */
                $string .= "\t\t\t<key>$key</key><string><![CDATA[$value]]></string>\n";
                }
            }

        } // end foreach

        return $string;
    break;
    case 'xspf':
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $value = xoutput_from_array($value,1,$type);
                $string .= "\t\t<$key>\n$value\t\t</$key>\n";
            } else {
                if ($key == "key") {
                $string .= "\t\t<$key>$value</$key>\n";
                } elseif (is_numeric($value)) {
                $string .= "\t\t\t<$key>$value</$key>\n";
                } elseif (is_string($value)) {
                /* We need to escape the value */
                $string .= "\t\t\t<$key><![CDATA[$value]]></$key>\n";
                }
            }

        } // end foreach

        return $string;
    break;
    default:
        foreach ($array as $key => $value) {
            // No numeric keys
            if (is_numeric($key)) {
                $key = 'item';
            }

            if (is_array($value)) {
                // Call ourself
                $value = xoutput_from_array($value, true);
                $string .= "\t<content div=\"$key\">$value</content>\n";
            } else {
                /* We need to escape the value */
                $string .= "\t<content div=\"$key\"><![CDATA[$value]]></content>\n";
            }
        // end foreach elements
        }
        if (!$callback) {
            $string = '<?xml version="1.0" encoding="utf-8" ?>' .
                "\n<root>\n" . $string . "</root>\n";
        }

        return UI::clean_utf8($string);
    break;
    }
} // xml_from_array

function json_from_array($array, $callback = false, $type = '')
{
    return json_encode($array);
}

/**
 * xml_get_header
 * This takes the type and returns the correct xml header
 */
function xml_get_header($type)
{
    switch ($type) {
    case 'itunes':
        $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
        "<!DOCTYPE plist PUBLIC \"-//Apple Computer//DTD PLIST 1.0//EN\"\n" .
        "\"http://www.apple.com/DTDs/PropertyList-1.0.dtd\">\n" .
        "<plist version=\"1.0\">\n" .
        "<dict>\n" .
        "       <key>Major Version</key><integer>1</integer>\n" .
        "       <key>Minor Version</key><integer>1</integer>\n" .
        "       <key>Application Version</key><string>7.0.2</string>\n" .
        "       <key>Features</key><integer>1</integer>\n" .
        "       <key>Show Content Ratings</key><true/>\n" .
        "       <key>Tracks</key>\n" .
        "       <dict>\n";
        return $header;
    break;
    case 'xspf':
        $header = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" .
            "<!-- XML Generated by Ampache v." .  AmpConfig::get('version') . " -->";
            "<playlist version = \"1\" xmlns=\"http://xspf.org/ns/0/\">\n ".
            "<title>Ampache XSPF Playlist</title>\n" .
            "<creator>" . AmpConfig::get('site_title') . "</creator>\n" .
            "<annotation>" . AmpConfig::get('site_title') . "</annotation>\n" .
            "<info>". AmpConfig::get('web_path') ."</info>\n" .
            "<trackList>\n\n\n\n";
        return $header;
    break;
    default:
        $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        return $header;
    break;
    }
} //xml_get_header

/**
 * xml_get_footer
 * This takes the type and returns the correct xml footer
 */
function xml_get_footer($type)
{
    switch ($type) {
    case 'itunes':
        $footer = "      </dict>\n" .
        "</dict>\n" .
        "</plist>\n";
        return $footer;
    break;
    case 'xspf':
        $footer = "      </trackList>\n" .
              "</playlist>\n";
        return $footer;
    break;
    default:

    break;
    }
} // xml_get_footer

/**
 * toggle_visible
 * This is identical to the javascript command that it actually calls
 */
function toggle_visible($element)
{
    echo '<script type="text/javascript">';
    echo "toggleVisible('$element');";
    echo "</script>\n";

} // toggle_visible

/**
 * print_bool
 * This function takes a boolean value and then prints out a friendly text
 * message.
 */
function print_bool($value)
{
    if ($value) {
        $string = '<span class="item_on">' . T_('On') . '</span>';
    } else {
        $string = '<span class="item_off">' . T_('Off') . '</span>';
    }

    return $string;

} // print_bool

/**
 * show_now_playing
 * This shows the now playing templates and does some garbage collecion
 * this should really be somewhere else
 */
function show_now_playing()
{
    Session::gc();
    Stream::gc_now_playing();

    $web_path = AmpConfig::get('web_path');
    $results = Stream::get_now_playing();
    require_once AmpConfig::get('prefix') . '/templates/show_now_playing.inc.php';

} // show_now_playing
