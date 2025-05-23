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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/repository/lib.php');

use tool_opencast\local\api;

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
     * This method adds a select form and additional information to the settings form..
     *
     * @param \moodleform $mform Moodle form (passed by reference)
     */
    public static function instance_config_form($mform) {
        if (!has_capability('moodle/site:config', context_system::instance())) {
            $mform->addElement('static', null, '', get_string('nopermissions', 'error', get_string('configplugin',
                'repository_opencast')));
            return false;
        }

        $options = [];
        foreach (\tool_opencast\local\settings_api::get_ocinstances() as $ocinstance) {
            $options[$ocinstance->id] = $ocinstance->name;
        }
        $mform->addElement('select', 'opencast_instance', get_string('opencastinstance', 'repository_opencast'), $options);
        $mform->setType('opencast_instance', PARAM_INT);

        $mform->addElement('text', 'opencast_author', get_string('opencastauthor', 'repository_opencast'));
        $mform->setType('opencast_author', PARAM_TEXT);

        $mform->addElement('text', 'opencast_channelid', get_string('opencastchannelid', 'repository_opencast'));
        $mform->setType('opencast_channelid', PARAM_TEXT);
        $mform->addHelpButton('opencast_channelid', 'opencastchannelid', 'repository_opencast');
        $mform->addRule('opencast_channelid', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'opencast_thumbnailflavor', get_string('opencastthumbnailflavor', 'repository_opencast'));
        $mform->setType('opencast_thumbnailflavor', PARAM_TEXT);
        $mform->addHelpButton('opencast_thumbnailflavor', 'opencastthumbnailflavor', 'repository_opencast');

        $mform->addElement('text', 'opencast_thumbnailflavorfallback',
            get_string('opencastthumbnailflavorfallback', 'repository_opencast'));
        $mform->setType('opencast_thumbnailflavorfallback', PARAM_TEXT);
        $mform->addHelpButton('opencast_thumbnailflavorfallback', 'opencastthumbnailflavorfallback', 'repository_opencast');

        $mform->addElement('checkbox', 'opencast_playerurl', get_string('opencastplayerurl', 'repository_opencast'));
        $mform->setType('opencast_playerurl', PARAM_BOOL);
        $mform->addHelpButton('opencast_playerurl', 'opencastplayerurl', 'repository_opencast');

        $mform->addElement('text', 'opencast_videoflavor', get_string('opencastvideoflavor', 'repository_opencast'));
        $mform->setType('opencast_videoflavor', PARAM_TEXT);
        $mform->addHelpButton('opencast_videoflavor', 'opencastvideoflavor', 'repository_opencast');
    }

    /**
     * Save settings for repository instance
     *
     * @param array $options settings
     * @return bool
     */
    public function set_option($options = []) {
        $options['opencast_instance'] = clean_param($options['opencast_instance'], PARAM_INT);
        $options['opencast_author'] = clean_param($options['opencast_author'], PARAM_TEXT);
        $options['opencast_channelid'] = clean_param($options['opencast_channelid'], PARAM_TEXT);
        $options['opencast_playerurl'] = clean_param($options['opencast_playerurl'], PARAM_BOOL);
        $options['opencast_thumbnailflavor'] = clean_param($options['opencast_thumbnailflavor'], PARAM_TEXT);
        $options['opencast_thumbnailflavorfallback'] = clean_param($options['opencast_thumbnailflavorfallback'], PARAM_TEXT);
        $options['opencast_videoflavor'] = clean_param($options['opencast_videoflavor'], PARAM_TEXT);
        $ret = parent::set_option($options);
        return $ret;
    }

    /**
     * Names of the plugin settings
     *
     * @return array
     */
    public static function get_instance_option_names() {
        $instanceoptions = [];
        $instanceoptions[] = 'opencast_instance';
        $instanceoptions[] = 'opencast_author';
        $instanceoptions[] = 'opencast_channelid';
        $instanceoptions[] = 'opencast_playerurl';
        $instanceoptions[] = 'opencast_thumbnailflavor';
        $instanceoptions[] = 'opencast_thumbnailflavorfallback';
        $instanceoptions[] = 'opencast_videoflavor';
        return $instanceoptions;
    }

    /**
     * Get channel id for this repository type.
     * @return string
     */
    private function get_channelid() {
        return self::get_option('opencast_channelid', 'api');
    }

    /**
     * Get name of author for this repository type.
     * @return string
     */
    private function get_author() {
        return self::get_option('opencast_author');
    }

    /**
     * Get Opencast instance id to which this repository belongs.
     * @return string
     */
    private function get_ocinstance() {
        return self::get_option('opencast_instance');
    }

    /**
     * Select the url for the video based on configuration of preferred flavors.
     *
     * @param object $publication
     * @param object $video
     * @return string
     */
    private function add_video_thumbnail_url($publication, $video) {
        // Try to find a thumbnail url based on configuration.
        $thumbnailflavor = self::get_option('opencast_thumbnailflavor');
        if (!empty($thumbnailflavor)) {
            foreach ($publication->attachments as $attachment) {
                if ($attachment->flavor === $thumbnailflavor) {
                    $video->thumbnail = $attachment->url;
                    return true;
                }
            }
        }

        // Try fallback.
        $thumbnailflavorfallback = self::get_option('opencast_thumbnailflavorfallback');
        if (!empty($thumbnailflavor)) {
            foreach ($publication->attachments as $attachment) {
                if ($attachment->flavor === $thumbnailflavorfallback) {
                    $video->thumbnail = $attachment->url;
                    return true;
                }
            }
        }

        // Automatically try to find the best preview image:
        // presentation/search+preview > presenter/search+preview > any other preview.
        foreach ($publication->attachments as $attachment) {
            if (!empty($attachment->url) && strpos($attachment->flavor, '+preview') > 0) {
                if (empty($video->url) || strpos($attachment->flavor, '/search+preview') > 0) {
                    $video->thumbnail = $attachment->url;
                    if ($attachment->flavor === 'presentation/search+preview') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Select the video url and title.
     *
     * @param object $publication
     * @param object $video
     * @return bool
     */
    private function add_video_url_and_title($publication, $video) {
        // Try to find a video by preferred configuration.
        $videoflavor = self::get_option('opencast_videoflavor');
        if (!empty($videoflavor)) {

            foreach ($publication->media as $media) {
                if (!empty($media->has_video) && ($media->flavor === $videoflavor)) {
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

        // Automatically find a suitable video.
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

        return false;
    }

    /**
     * Add data from opencast to the list items.
     *
     * @param int $ocinstanceid
     * @param object $video
     * @return boolean true, when it is a valid video published for external api.
     */
    private function add_video_published_data($ocinstanceid, $video) {
        $channelid = $this->get_channelid();
        $published = (count($video->publication_status) > 0 && (in_array($channelid, $video->publication_status)));

        if (!$published) {
            return false;
        }

        if (empty($video->publications)) {
            return false;
        }

        $useplayerurl = self::get_option('opencast_playerurl');

        foreach ($video->publications as $publication) {

            if ($publication->channel == $channelid) {

                // Add a suitable thumbnail url.
                $this->add_video_thumbnail_url($publication, $video);

                // Add a url to video.
                if ($useplayerurl) {
                    $video->url = $publication->url;
                    return true;
                } else {
                    return $publication->media && $this->add_video_url_and_title($publication, $video);
                }
            }
        }
        return false;
    }

    /**
     * Get all the videos published via API for this course
     *
     * @param int $courseid
     * @return array video object suitable for repository listing.
     */
    private function get_course_videos($courseid) {
        $publishedvideos = [];

        // Get all videos from all series.
        $ocinstanceid = $this->get_ocinstance();
        $videos = [];

        foreach (\tool_opencast\seriesmapping::get_records(['courseid' => $courseid,
            'ocinstanceid' => $ocinstanceid, ]) as $mapping) {
            if (!$mapping || !($seriesid = $mapping->get('series'))) {
                continue;
            }

            $params = [
                'sign' => true,
                'withmetadata' => false,
                'withpublications' => true,
            ];
            try {
                $api = api::get_instance($ocinstanceid);
                $response = $api->opencastapi->eventsApi->getBySeries($seriesid, $params);
                $code = $response['code'];
                if ($code == 200) {
                    $seriesvideos = $response['body'];
                    $videos = array_merge($videos, $seriesvideos);
                }
            } catch (\moodle_exception $e) {
                continue;
            }
        }

        foreach ($videos as $video) {
            if ($this->add_video_published_data($ocinstanceid, $video)) {
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

        $ret = [];
        $ret['dynload'] = false;
        $ret['nosearch'] = true;
        $ret['nologin'] = true;
        $ret['list'] = [];

        foreach ($videos as $video) {
            $listitem = [];
            $listitem['title'] = $video->title;
            if (!file_extension_in_typegroup($video->title, $this->supported_filetypes())) {
                $listitem['title'] = $video->title . '.mp4';
            }
            $listitem['date'] = strtotime($video->start);
            $listitem['thumbnail'] = $video->thumbnail;
            $listitem['url'] = $video->url;
            $listitem['source'] = $video->url;
            $listitem['author'] = (!empty($video->creator)) ? $video->creator : $this->get_author();
            $ret['list'][] = $listitem;
        }

        return $ret;
    }

    /**
     * Supported returntypes of the repository.
     * @return int
     */
    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }

    /**
     * Supported filetypes of the repository.
     * @return string[]
     */
    public function supported_filetypes() {
        return ['video'];
    }

    /**
     * Prepares data for the php unit test.
     * @param array $publications
     * @param object $video
     * @return mixed
     */
    public function phpu_adapter_test_listing($publications, $video) {
        $channelid = $this->get_channelid();

        foreach ($publications as $publication) {

            if ($publication->channel == $channelid) {

                // Add a suitable thumbnail url.
                $this->add_video_thumbnail_url($publication, $video);

                // Add a url to video.
                if ($publication->media) {
                    $this->add_video_url_and_title($publication, $video);
                }
            }
        }
        return $video;
    }
}
