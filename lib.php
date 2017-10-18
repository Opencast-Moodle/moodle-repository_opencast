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

use \repository_opencast\local\api;

/**
 *  repository_opencast class is used to browse opencast files
 *
 * @package    repository_opencast
 * @copyright  2017 Andreas Wagner, SYNERGY LEARNING
 * @author     Andreas Wagner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_opencast extends repository {

    /**
     * Add Instance settings input to moodle form.
     *
     * @param moodleform $mform
     */
    public static function instance_config_form($mform) {

        $mform->addElement('text', 'opencast_apiurl', get_string('opencastapiurl', 'repository_opencast'), array('size' => 100));
        $mform->setType('opencast_apiurl', PARAM_URL);

        $strrequired = get_string('required');
        $mform->addRule('opencast_apiurl', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'opencast_apiuser', get_string('opencastapiuser', 'repository_opencast'));
        $mform->setType('opencast_apiuser', PARAM_TEXT);

        $strrequired = get_string('required');
        $mform->addRule('opencast_apiuser', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'opencast_apipassword', get_string('opencastapipassword', 'repository_opencast'));
        $mform->setType('opencast_apipassword', PARAM_TEXT);

        $strrequired = get_string('required');
        $mform->addRule('opencast_apipassword', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'opencast_author', get_string('opencastauthor', 'repository_opencast'));
        $mform->setType('opencast_author', PARAM_TEXT);
    }

    /**
     * Return all the valid instance option names.
     *
     * @return array
     */
    public static function get_instance_option_names() {
        return array(
            'opencast_apiurl',
            'opencast_apiuser',
            'opencast_apipassword',
            'opencast_author'
        );
    }

    /**
     * Do an api GET call and decode the response.
     *
     * @param string $query query parameter of API call
     * @param array $withroles restrict result to that roles.
     * @return array result array or empty.
     */
    private function api_get($query, $withroles = array()) {

        $url = $this->get_option('opencast_apiurl') . $query;
        $user = $this->get_option('opencast_apiuser');
        $pass = $this->get_option('opencast_apipassword');

        $api = new api($user, $pass);
        $result = $api->oc_get($url, $withroles);
        return json_decode($result);
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

        $query = '/api/events/' . $video->identifier . '/publications/';
        $publications = $this->api_get($query);

        if (empty($publications)) {
            return false;
        }

        foreach ($publications as $publication) {

            if ($publication->channel == 'api') {
                foreach ($publication->attachments as $attachment) {
                    if (!empty($attachment->url)) {
                        $video->thumbnail = $attachment->url;
                    }
                }
            }
            if ($publication->media) {
                foreach ($publication->media as $media) {
                    if (!empty($media->has_video)) {
                        $video->url = $media->url;
                        // Check mimetype needed for embedding in moodle.
                        $ending = pathinfo($media->url, PATHINFO_EXTENSION);
                        $video->title .= '.' . $ending;
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

        $query = '/api/events?sign=1&withmetadata=1&withpublications=1';
        $videos = $this->api_get($query, array(api::get_course_acl_role($courseid)));

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
