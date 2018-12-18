# moodle_repository_opencast

The repository plugin serves as a way to embed videos of your Opencast system into a Moodle course.
It is one of multiple plugins integrating Opencast with Moodle.

**Requirements**

- [tool_opencast](https://github.com/unirz-tu-ilmenau/moodle-tool_opencast)
- Recommended: [block_opencast](https://github.com/unirz-tu-ilmenau/moodle-block_opencast)
- Optional: [filter_opencast](https://github.com/unirz-tu-ilmenau/moodle-filter_opencast)

**Usage**

The plugin allows teachers to embed Opencast videos into a course.
The repository displays all videos that belong to the Opencast series, which is connected to the course.

![image](https://user-images.githubusercontent.com/9437254/50089314-caadf180-0205-11e9-93f9-6a7d3f1f6726.png)

The connection, which Opencast series belongs to which Moodle course, is usually done using [block_opencast](https://github.com/unirz-tu-ilmenau/moodle-block_opencast).

The repository is only available within a text editor and will paste the URL to the resource into the content.
This URL will later be replaced via a Moodle filter to show an embedded player.
There are two different possibilities:

* **Display a video using the Moodle media filter:**

    The repository can embed a link to the media file of the Opencast event.
    This link will automatically be replaced by the Moodle media filter.
    Be aware that by default Opencast does not protect links to published files in any way.
    Thus, this link can be easily shared between students, who might not have access to the course itself.

* **Display a video using an Opencast player:**

    The repository can paste a link to the course content, which leads to a page at which an embeddable Opencast player is provided.
    For this scenario, you will need the [filter_opencast](https://github.com/unirz-tu-ilmenau/moodle-filter_opencast) plugin as well as an Opencast instance with [configured LTI](https://docs.opencast.org/develop/admin/modules/ltimodule/).
    The filter will replace the link by an iFrame, which displays the Opencast player.
    In order for this to work, the selected publication channel of the repository instance has to serve the URL to the player within the publication URL.
    Additionally, the filter will automatically authenticate the user in Opencast using LTI.
    This way, some kind of resource protection is provided.
    However, for increased protection, you would need to activate [stream security](https://docs.opencast.org/develop/admin/configuration/stream-security/) in Opencast, which is currently [broken](https://opencast.jira.com/browse/MH-12521).

**Configuration (multiple instances and publication channel)**

Opencast publishes its events in so called publication channels.
Two default publication channels are e.g. the `api` and the `engage-player` channels.
Each publication has a publication URL, which points to the page under which the video is published (e.g. an HTTP page with the Opencast player).
Additionally, publications can contain a set of media data with URLs to the media files themselves (e.g. an MP4 file).

The Moodle administrator can create multiple Opencast repository instances.
Each of these repositories has two important options:

* **Opencast channel ID:** The publication channel ID given here, determines which videos are available in the repository.
Only those videos are listed, which are published under the respective channel.
Additionally, the channel can determine, how the video is displayed. Depending on the publication URL, the video could for instance be displayed using the Paella or the Theodul player.

* **Embedd URL to player instead of media file:** This flag determines, if the repository will paste a link to the publication URL or to a media file in the text editor.

The other configuration options concerning flavors steer, which thumbnail should be used within the filepicker or which media file flavor is preferred, when media files are embedded directly.
