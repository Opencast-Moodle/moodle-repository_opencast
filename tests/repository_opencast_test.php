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
namespace repository_opencast;

use advanced_testcase;
use repository_opencast;

/**
 * Class repository_opencast_testcase
 */
class repository_opencast_test extends advanced_testcase {

    public function test_add_video_published_data() {
        global $CFG;

        require_once($CFG->dirroot . '/repository/opencast/lib.php');

        $this->resetAfterTest();
        $this->setAdminUser();

        $publications = json_decode(file_get_contents($CFG->dirroot . '/repository/opencast/tests/fixtures/testdata.js'));
        $generator = $this->getDataGenerator()->get_plugin_generator('repository_opencast');
        $instance = $generator->create_instance([
            'pluginname' => 'Opencast',
            'opencast_instance' => 1,
            'opencast_author' => 'Test user',
            'opencast_channelid' => 'api',
            'opencast_thumbnailflavor' => 'presenter/search+preview',
            'opencast_thumbnailflavorfallback' => 'presentation/search+preview',
            'opencast_videoflavor' => 'delivery/h264-720p',
            'opencast_playerurl' => ''
        ]);
        $repository = new repository_opencast($instance->id);

        $video = new \stdClass();
        $video->title = 'Test';

        $publications[0]->channel = 'api';
        $video = $repository->phpu_adapter_test_listing($publications, $video);
        $this->assertEquals('h264-720p.mp4', $video->url);
        $this->assertEquals('Test.mp4', $video->title);
        $this->assertEquals('tn_presenter_search_preview_1.png', $video->thumbnail);

        // Test not valid thumbnail flavor.
        $instance = $generator->create_instance([
            'pluginname' => 'Opencast',
            'opencast_instance' => 1,
            'opencast_author' => 'Test user',
            'opencast_channelid' => 'api',
            'opencast_thumbnailflavor' => 'notvalid',
            'opencast_thumbnailflavorfallback' => 'presentation/search+preview',
            'opencast_videoflavor' => 'delivery/h264-720p',
            'opencast_playerurl' => ''
        ]);
        $repository = new repository_opencast($instance->id);

        $video = new \stdClass();
        $video->title = 'Test';

        $video = $repository->phpu_adapter_test_listing($publications, $video);
        $this->assertEquals('h264-720p.mp4', $video->url);
        $this->assertEquals('Test.mp4', $video->title);
        $this->assertEquals('tn_presentation_search_preview_1.png', $video->thumbnail);
    }
}
