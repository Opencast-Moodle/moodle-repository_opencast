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
 *  repository_opencast class is used to browse opencast files
 *
 * @package    repository_opencast
 * @copyright  2017 Andreas Wagner, SYNERGY LEARNING
 * @author     Andreas Wagner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/repository/lib.php');

use \tool_opencast\local\api;

/**
 *  repository_opencast class is used to browse opencast files
 *
 * @package    repository_opencast
 * @copyright  2017 Andreas Wagner, SYNERGY LEARNING
 * @author     Andreas Wagner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_opencast extends repository {

    private $api;

    /**
     * Add Instance settings input to moodle form.
     *
     * @param moodleform $mform
     * @param string $classname repository class name
     */
    public static function type_config_form($mform, $classname = 'repository') {
        parent::type_config_form($mform);

        $mform->addElement('text', 'opencast_author', get_string('opencastauthor', 'repository_opencast'));
        $mform->setType('opencast_author', PARAM_TEXT);
    }

    /**
     * Return all the valid instance option names.
     *
     * @return array
     */
    public static function get_type_option_names() {
        $typeoptions = parent::get_type_option_names();
        $typeoptions [] = 'opencast_author';
        return $typeoptions;
    }

    /**
     * Add data from opencast to the list items.
     *
     * @param object $video
     * @return boolean true, when it is a valid video published for external api.
     */
    private function add_video_published_data($video) {

        $published = (count($video->publication_status) > 0 && (in_array('api', $video->publication_status)));

        if (!$published) {
            return false;
        }

        $api = new api();

        $query = '/api/events/' . $video->identifier . '/publications/';
        $result = $api->oc_get($query);
        $publications = json_decode($result);

        if (empty($publications)) {
            return false;
        }

        foreach ($publications as $publication) {

            if ($publication->channel == 'api') {
                foreach ($publication->attachments as $attachment) {
                    if ($attachment->flavor === 'presenter/search+preview' && !empty($attachment->url)) {
                        $video->thumbnail = $attachment->url;
                    }
                }

                if ($publication->media) {
                    foreach ($publication->media as $media) {
                        if (!empty($media->has_video)) {
                            $video->url = $media->url;
                            // Check mimetype needed for embedding in moodle.
                            $ending = pathinfo($media->url, PATHINFO_EXTENSION);
                            $existingending = pathinfo($video->title, PATHINFO_EXTENSION);
                            if ($ending !== $existingending) {
                                $video->title .= '.' . $ending;
                            }
                            return true;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get all the videos published via API for this course
     *
     * @param int $courseid
     * @return array video object suitable for repository listing.
     */
    private function get_course_videos($courseid) {

        $mapping = \tool_opencast\seriesmapping::get_record(array('courseid' => $courseid));

        if (!$mapping || !($seriesid = $mapping->get('series'))) {
            return array();
        }
        $seriesfilter = "series:" . $seriesid;

        $query = '/api/events?sign=1&withmetadata=1&withpublications=1&filter='. urlencode($seriesfilter);
        try {
            $api = new api();
            $videos = $api->oc_get($query);
            $videos = json_decode($videos);
        } catch (\moodle_exception $e) {
            return array();
        }

        if (empty($videos)) {
            return array();
        }

        $publishedvideos = [];
        foreach ($videos as $video) {

            if ($this->add_video_published_data($video)) {
                $publishedvideos[] = $video;
            }
        }
        return $publishedvideos;
    }

    /**
     * Get file listing
     *
     * @param string $encodedpath
     * @param string $page no paging is used in repository_opencast
     * @return mixed
     */
    public function get_listing($encodedpath = '', $page = '') {
        global $CFG;

        require_once($CFG->dirroot . '/lib/accesslib.php');
        list($context, $course, $cm) = get_context_info_array($this->context->id);

        $videos = $this->get_course_videos($course->id);

        $ret = array();
        $ret['dynload'] = false;
        $ret['nosearch'] = true;
        $ret['nologin'] = true;
        $ret['list'] = array();

        foreach ($videos as $video) {
            $listitem = array();
            $listitem['title'] = $video->title;
            $listitem['date'] = strtotime($video->start);
            $listitem['thumbnail'] = $video->thumbnail;
            $listitem['url'] = $video->url;
            $listitem['source'] = $video->url;
            $listitem['author'] = (!empty($video->creator)) ? $video->creator : $this->get_option('opencast_author');
            $ret['list'][] = $listitem;
        }

        return $ret;
    }

    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }

    public function supported_filetypes() {
        return array('video');
    }

}
