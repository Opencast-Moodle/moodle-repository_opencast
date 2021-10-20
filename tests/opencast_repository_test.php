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
 * Test case for opencast repository.
 * @package    repository_opencast
 * @copyright  2018 Andreas Wagner, SYNERGY LEARNING
 * @author     Andreas Wagner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Class repository_opencast_testcase
 */
class repository_opencast_testcase extends advanced_testcase {

    public function test_add_video_published_data() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/repository/opencast/lib.php');

        $this->resetAfterTest(true);
        $this->setAdminUser();

        $publications = json_decode(file_get_contents($CFG->dirroot . '/repository/opencast/tests/fixtures/testdata.js'));
        $repository = $DB->get_record_sql(
            "SELECT i.id
             FROM {repository_instances} i
             JOIN {repository} r on r.id = i.typeid AND r.type = ?", ['opencast']
        );
        $repository = new repository_opencast($repository->id);

        $video = new \stdClass();
        $video->title = 'Test';

        // Checking without setup repository.
        $video = $repository->phpu_adapter_test_listing($publications, $video);
        $this->assertTrue(!isset($video->thumbnail));

        $publications[0]->channel = 'api';
        $video = $repository->phpu_adapter_test_listing($publications, $video);
        $this->assertEquals($video->url, 'h264-540p.mp4');
        $this->assertEquals($video->title, 'Test.mp4');
        $this->assertEquals($video->thumbnail, 'tn_presentation_search_preview_1.png');

        // Setup the repository type.
        set_config('opencast_channelid', 'switchcast-player', 'opencast');
        set_config('opencast_thumbnailflavor', 'presenter/search+preview', 'opencast');
        set_config('opencast_thumbnailflavorfallback', 'presentation/search+preview', 'opencast');
        set_config('opencast_videoflavor', 'delivery/h264-720p', 'opencast');

        $publications[0]->channel = 'switchcast-player';

        $video = new \stdClass();
        $video->title = 'Test';

        $video = $repository->phpu_adapter_test_listing($publications, $video);
        $this->assertEquals($video->url, 'h264-720p.mp4');
        $this->assertEquals($video->title, 'Test.mp4');
        $this->assertEquals($video->thumbnail, 'tn_presenter_search_preview_1.png');

        set_config('opencast_channelid', 'switchcast-player', 'opencast');
        set_config('opencast_thumbnailflavor', 'notvalid', 'opencast');
        set_config('opencast_thumbnailflavorfallback', 'presentation/search+preview', 'opencast');
        set_config('opencast_videoflavor', 'delivery/h264-720p', 'opencast');

        $publications[0]->channel = 'switchcast-player';

        $video = new \stdClass();
        $video->title = 'Test';

        $video = $repository->phpu_adapter_test_listing($publications, $video);
        $this->assertEquals($video->url, 'h264-720p.mp4');
        $this->assertEquals($video->title, 'Test.mp4');
        $this->assertEquals($video->thumbnail, 'tn_presentation_search_preview_1.png');
    }

}
