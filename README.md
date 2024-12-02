# moodle-repository_opencast

The repository plugin serves as a way to embed videos of your Opencast system into a Moodle course.
It is one of multiple plugins integrating Opencast with Moodle.

This file is part of the repository_opencast plugin for Moodle - <http://moodle.org/>

*Maintainer:*    Thomas Niedermaier (Universität Münster), Farbod Zamani (Elan e.V.)

*Copyright:* 2017 Andreas Wagner, SYNERGY LEARNING, 2024 Thomas Niedermaier, UNIVERSITÄT MÜNSTER

*License:*   [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)


Description
-----------

The plugin allows teachers, to embed Opencast videos into a course. The repository displays all videos, that belong to the Opencast series, that are connected to the course. The repository is only available within a text editor and will paste the URL to the resource into the content. This URL will later be replaced via a Moodle filter, to show an embedded player. The repository works best in combination with the Opencast filter plugin. The filter will replace the link with an iFrame, which displays the Paella player.


Requirements
------------

* tool_opencast
* Recommended: block_opencast
* Optional: filter_opencast


Installation
------------

* Copy the module code directly to the repository/opencast directory.

* Log into Moodle as administrator.

* Open the administration area (http://your-moodle-site/admin) to start the installation
  automatically.


Admin Settings
--------------

As an administrator you can set the default values instance-wide on the settings page for
administrators in the opencast repository module:

* Name: Name of the repository instance.
* Opencast instance: The Opencast instance, from which the repository retrieves videos.
* Opencast default author: The default author, that is displayed for a video, if the creator is not given. Empty by default.
* Opencast channelid:: The channel id of the publication in Opencast. With a default Opencast installation you can use engage-player.
* Preferred flavor to get thumbnail:: The flavor for the thumbnail. Leave empty for the Opencast default.
* Fallback flavor to get thumbnail: Fallback flavor for the thumbnail, if there is no thumbnail for the preferred flavor.
* Embedd URL to player instead of media file: Whether the URL to the Opencast player should be embedded. If this box is not checked, a direct link to a video file will be embedded.
* Flavor to get video: The flavor of the video. Leave empty, to use the Opencast default.


Documentation
-------------

The full documentation of the plugin can be found [here](https://moodle.docs.opencast.org/#repository/about/).


Bug Reports / Support
---------------------

We try our best to deliver bug-free plugins, but we can not test the plugin for every platform,
database, PHP and Moodle version. If you find any bug please report it on
[GitHub](https://github.com/Opencast-Moodle/moodle-repository_opencast/issues). Please
provide a detailed bug description, including the plugin and Moodle version and, if applicable, a
screenshot.

You may also file a request for enhancement on GitHub. 


License
-------

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.


Good luck and have fun!
