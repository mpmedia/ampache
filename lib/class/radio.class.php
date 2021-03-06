<?php
/* vim:set softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * LICENSE: GNU General Public License, version 2 (GPLv2)
 * Copyright 2001 - 2013 Ampache.org
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2
 * of the License.
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
 * Radio Class
 *
 * This handles the internet radio stuff, that is inserted into live_stream
 * this can include podcasts or what-have-you
 *
 */
class Radio extends database_object implements media
{
    /* DB based variables */
    public $id;
    public $name;
    public $site_url;
    public $url;
    public $genre;
    public $codec;
    public $catalog;

    /**
     * Constructor
     * This takes a flagged.id and then pulls in the information for said flag entry
     */
    public function __construct($id = null)
    {
        if (!$id) { return false; }

        $info = $this->get_info($id, 'live_stream');

        // Set the vars
        foreach ($info as $key=>$value) {
            $this->$key = $value;
        }

    } // constructor

    /**
     * format
     * This takes the normal data from the database and makes it pretty
     * for the users, the new variables are put in f_??? and f_???_link
     */
    public function format()
    {
        // Default link used on the rightbar
        $this->f_link        = "<a href=\"$this->url\">$this->name</a>";
        $this->f_name_link    = "<a target=\"_blank\" href=\"$this->site_url\">$this->name</a>";
        $this->f_url_link    = "<a target=\"_blank\" href=\"$this->url\">$this->url</a>";

        return true;

    } // format

    /**
     * update
     * This is a static function that takes a key'd array for input
     * it depends on a ID element to determine which radio element it
     * should be updating
     */
    public static function update($data)
    {
        // Verify the incoming data
        if (!$data['id']) {
            Error::add('general', T_('Missing ID'));
        }

        if (!$data['name']) {
            Error::add('general', T_('Name Required'));
        }

        $allowed_array = array('https','http','mms','mmsh','mmsu','mmst','rtsp','rtmp');

        $elements = explode(":",$data['url']);

        if (!in_array($elements['0'],$allowed_array)) {
            Error::add('general', T_('Invalid URL must be mms:// , https:// or http://'));
        }

        if (Error::occurred()) {
            return false;
        }

        $sql = "UPDATE `live_stream` SET `name` = ?,`site_url` = ?,`url` = ?, codec = ? WHERE `id` = ?";
        $db_results = Dba::write($sql, array($data['name'], $data['site_url'], $data['url'], $data['codec'], $data['id']));

        return $db_results;

    } // update

    /**
     * create
     * This is a static function that takes a key'd array for input
     * and if everything is good creates the object.
     */
    public static function create($data)
    {
        // Make sure we've got a name
        if (!strlen($data['name'])) {
            Error::add('name', T_('Name Required'));
        }

        $allowed_array = array('https','http','mms','mmsh','mmsu','mmst','rtsp','rtmp');

        $elements = explode(":", $data['url']);

        if (!in_array($elements['0'],$allowed_array)) {
            Error::add('url', T_('Invalid URL must be http:// or https://'));
        }

        // Make sure it's a real catalog
        $catalog = Catalog::create_from_id($data['catalog']);
        if (!$catalog->name) {
            Error::add('catalog', T_('Invalid Catalog'));
        }

        if (Error::occurred()) { return false; }

        // If we've made it this far everything must be ok... I hope
        $sql = "INSERT INTO `live_stream` (`name`,`site_url`,`url`,`catalog`,`codec`) " .
            "VALUES (?, ?, ?, ?, ?)";
        $db_results = Dba::write($sql, array($data['name'], $data['site_url'], $data['url'], $catalog->id, $data['codec']));

        return $db_results;

    } // create

    /**
     * delete
     * This deletes the current object from the database
     */
    public function delete()
    {
        $sql = "DELETE FROM `live_stream` WHERE `id` = ?";
        $db_results = Dba::write($sql, array($this->id));

        return true;

    } // delete

    /**
     * get_stream_types
     * This is needed by the media interface
     */
    public function get_stream_types()
    {
        return array('foreign');
    } // native_stream

    /**
     * play_url
     * This is needed by the media interface
     */
    public static function play_url($oid, $additional_params='',$sid='',$force_http='')
    {
        $radio = new Radio($oid);

        return $radio->url . $additional_params;

    } // play_url

    /**
     * get_transcode_settings
     *
     * This will probably never be implemented
     */
    public function get_transcode_settings($target = null)
    {
        return false;
    }

    public static function get_all_radios($catalog = null)
    {
        $sql = "SELECT `live_stream`.`id` FROM `live_stream` JOIN `catalog` ON `catalog`.`id` = `live_stream`.`catalog` ";
        if (AmpConfig::get('catalog_disable')) {
            $sql .= "WHERE `catalog`.`enabled` = '1' ";
        }
        $params = array();
        if ($catalog) {
            if (AmpConfig::get('catalog_disable')) {
                $sql .= "AND ";
            }
            $sql .= "`catalog`.`id` = ?";
            $params[] = $catalog;
        }
        $db_results = Dba::read($sql, $params);
        $radios = array();

        while ($results = Dba::featch_assoc($db_results)) {
            $radios[] = $results['id'];
        }

        return $radios;
    }

} //end of radio class
