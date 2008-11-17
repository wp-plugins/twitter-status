<?php

/*
Plugin Name: Twitter Status
Plugin URI: http://www.naatan.com/wordpress/plugins/
Description: Keeps track of your twitter status
Version: 1.0.1
Author: Nathan Rijksen
Author URI: http://naatan.com/
*/

if (function_exists('add_action')) {
	
	add_action('wp_head', 'twitter_status_put_ajax' );
	add_action('show_user_profile','twitter_status_add_profile_field');
	add_action('edit_user_profile','twitter_status_add_profile_field');
	add_action('profile_update','twitter_status_update_profile_field');
	register_activation_hook(__FILE__,'twitter_status_activate');
	register_deactivation_hook(__FILE__,'twitter_status_deactivate');
		
} else
	require('../../wp-config.php');
	
if (!function_exists('twitter')) {
	function twitter($id) { echo twitter_status_get_from_db($id); }
}

function twitter_status_get_from_db($id) {
	
	global $wpdb;
	
	$statement = explode('=',$id);
	
	if (count($statement)==1) {
		
		if (!$userdata = get_userdatabylogin($id))
			return;
		$clause = "twit_user_id='".$userdata->ID."'";
		$id = 'u'.$userdata->ID;
		
	} else {
		
		if ($statement[0]=='twitter_id')
			$clause = "twit_twitter_id='".$statement[1]."'";
			
		if ($statement[0]=='user_id') 
			$clause = "twit_user_id='".$statement[1]."'";
			
		$id = $statement[0]=='user_id' ? 'u'.$statement[1] : 't'.$statement[0]=='user_id';
		
	}
	
	if (empty($clause))
		return;

	return '<span class="twitter_tweet tweet_'.$id.'">'.$wpdb->get_var("SELECT twit_status FROM ".$wpdb->prefix."twitter_status WHERE ".$clause).'</span>';
	
}

function twitter_status_update_profile_field($id) {
	
	global $wpdb;
	
	if (!empty($_POST['twitter_id']) AND is_numeric($_POST['twitter_id'])) {
		
		if (!$wpdb->get_var("SELECT twit_user_id FROM ".$wpdb->prefix."twitter_status WHERE twit_user_id='".$id."'"))
			$wpdb->query("INSERT INTO ".$wpdb->prefix."twitter_status (twit_user_id,twit_twitter_id) VALUES ('".$id."','".$_POST['twitter_id']."')");	
		else
			$wpdb->query("UPDATE ".$wpdb->prefix."twitter_status SET twit_twitter_id='".$_POST['twitter_id']."' WHERE twit_user_id='".$id."'");	
	}
	
}
	
function twitter_status_add_profile_field() {
	
	global $profileuser,$wpdb, $wp_version;
	
	$twitter_id = $wpdb->get_var("SELECT twit_twitter_id FROM ".$wpdb->prefix."twitter_status WHERE twit_user_id='".$profileuser->id."'");
	
	?>
	
	<h3><?php _e('Twitter'); ?></h3>
	<table class="form-table">
		<tr>
			<th scope="row"><?php _e('Twitter ID')?></th>
			<td><label for="twitter_id"><input name="twitter_id" type="text" id="twitter_id" value="<?php echo $twitter_id ?>"></label></td>
		</tr>
	</table>
	<?php
	
}

function twitter_status_update() {

	global $wpdb;
	
	$interval = !is_numeric(get_option('twitter_update_interval')) ? '1800' : get_option('twitter_update_interval');
	$limit = !is_numeric(get_option('twitter_users_per_update')) ? '5' : get_option('twitter_users_per_update');
	
	$tweets = $wpdb->get_results("SELECT *
								 FROM ".$wpdb->prefix."twitter_status
								 WHERE twit_lastupdate < NOW() - INTERVAL ".$interval." SECOND
								 ORDER BY twit_lastupdate ASC LIMIT ".$limit);

	foreach ($tweets as $tweet) {
		
		$twitter_status = twitter_status_get($tweet->twit_twitter_id);
		if (!empty($twitter_status)) {
			$wpdb->query("UPDATE ".$wpdb->prefix."twitter_status
						 SET twit_status='".$twitter_status."', twit_lastupdate=TIMESTAMP(NOW())
						 WHERE twit_twitter_id=".$tweet->twit_twitter_id);
			echo 'jQuery(".tweet_t'.$tweet->twit_twitter_id.'").html("'.$twitter_status.'");';
			echo 'jQuery(".tweet_u'.$tweet->twit_user_id.'").html("'.$twitter_status.'");';
		}
		
	}

	
}

function twitter_status_get($id) {
	
	if ($stream = @fopen('http://twitter.com/statuses/user_timeline/'.$id.'.xml', 'r')) {
		$str = @stream_get_contents($stream);
		preg_match('/\<text\>(.*?)\<\/text\>/',$str,$matches);
	
		@fclose($stream);
	}
	
	
	if (!empty($matches[1]))
		return $matches[1];
	
}

function twitter_status_put_ajax() {
  
	wp_print_scripts(array( 'sack' ));
	
	?>
		<script type="text/javascript">
		//<![CDATA[
		function update_twitter_status() {
			var tweet = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-content/plugins/twitter_status.php" );    
		 
			tweet.execute = 1;
			tweet.method = 'GET';
			tweet.setVar( "update_twitter_status", 'true');
			tweet.runAJAX();
			
			setTimeout ( "update_twitter_status()", 30000 );
		  
			return true;

		}
		
		update_twitter_status();

		//]]>
		</script>
	<?php
	
}

function twitter_status_activate() {
	
	global $wpdb;
	
	if (!$wpdb->get_row("SHOW TABLES LIKE '".$wpdb->prefix."twitter_status'")) {
		
		$wpdb->query("
			CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."twitter_status` (
			  `twit_user_id` smallint(6) NOT NULL,
			  `twit_twitter_id` int(11) NOT NULL,
			  `twit_status` varchar(150) NOT NULL,
			  `twit_lastupdate` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP
			)
		");
		
	}
	
	add_option('twitter_update_interval','1800');
	add_option('twitter_users_per_update','5');
	
}

function twitter_status_deactivate() {
	
	global $wpdb;
	
	if ($wpdb->get_row("SHOW TABLES LIKE '".$wpdb->prefix."twitter_status'"))
		$wpdb->query("DROP TABLE `".$wpdb->prefix."twitter_status`");
	
	delete_option('twitter_update_interval');
	delete_option('twitter_users_per_update');
	
}

if (!empty($_GET) AND !empty($_GET['update_twitter_status']))
	twitter_status_update();

?>
