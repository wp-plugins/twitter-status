=== Twitter Status ===
Contributors: Naatan
Donate link: http://www.naatan.com/
Tags: Twitter, Status, Twitter Status
Requires at least: 2.5
Tested up to: 2.7
Stable tag: Trunk

Twitter Status is a very simple no-fuzz plugin that gets the current Twitter message for your blog authors.

== Description ==

Twitter Status is a very simple no-fuzz plugin that gets the current Twitter message from the Twitter ID specified, when someone visits your website there will be an ajax script running in the background doing a GET request every 30 seconds, this request will trigger the Plugin to get all Author profiles with a twitter ID, and update their Twitter status, given that they haven’t been updated in at least 30 minutes and with a limit of 5 twitter profiles per request.

You can change these settings by editing the plugin file (before activating it).. the settings are easily accessible, no knowledge of PHP required.. I will incorporate a configuration page in future versions, for now I wanted to make an initial release that’s down to the complete basics.

== Installation ==

Just unzip the twitter_status.php in your plugins folder and activate in wp-admin (do not rename the plugin file and do not put it inside any folder other than the plugins folder).

== Frequently Asked Questions ==

None..

== Screenshots ==

1. A sample Twitter status, received by the plugin

== Usage ==

After installing, edit the author profiles in wp-admin that you would like to assign a Twitter profile to.

You can use the following tags in your templates:

   * &lt;?php twitter('Username') ?&gt;
   * &lt;?php twitter('twitter_id=xxxx') ?&gt;  
   * &lt;?php twitter('user_id=xxxx') ?&gt;

<?php twitter('user_id=xxxx') ?>

These functions will all echo the current status stored in the Database for the given user.

You can use the following function to get the twitter status DIRECTLY from twitter, but keep in mind that using this will cause a delay in the loadtime of the page;

   * &lt;?php echo twitter_status_get('xxx') ?&gt;


For “xxx” enter the twitter ID.
