<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * String definitions
 *
 * @package    repository_opencast
 * @copyright  2017 Andreas Wagner, SYNERGY LEARNING
 * @author     Andreas Wagner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['configplugin'] = 'Opencast settings';
$string['opencastauthor'] = 'Opencast default author';
$string['opencastchannelid'] = 'Opencast channelid';
$string['opencastchannelid_help'] = 'Setup the channelid of the publication channel to search for retrieving url of thumbnail and video.';
$string['opencastplayerurl'] = 'Embedd URL to player instead of media file.';
$string['opencastplayerurl_help'] = 'If checked, the URL of the Opencast player is used. Otherwise the repository selects the URL to a video file of the Opencast event.';
$string['opencastthumbnailflavor'] = 'Preferred flavor to get thumbnail';
$string['opencastthumbnailflavor_help'] = 'A publication may have several attachments with different flavors.
    Setup the flavor (for example "presenter/search+preview"), that should be used to retrieve the thumbnail url. If there is no attachment with this flavor, the
    plugin will try to find an attachment with the fallback flavor.';
$string['opencastthumbnailflavorfallback'] = 'Fallback flavor to get thumbnail';
$string['opencastthumbnailflavorfallback_help'] = 'Setup the flavor, that should be used, if the there is no attachment with the preferred flavor above is available.
    If you leave all input for thumbnail search blank, the plugin will automatically try to find a thumbnail url.';
$string['opencastvideoflavor'] = 'Flavor to get video';
$string['opencastvideoflavor_help'] = 'A publication may have several attachments with different flavors. Setup a flavor that should be used to retrieve the video url.
    If you leave this blank, the first available video url found in attachments will be used.';
$string['opencast:view'] = 'View repository opencast';
$string['pluginname'] = 'Opencast Videos';
