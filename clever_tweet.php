<?php
/*
Plugin Name: Clever Tweet
Plugin URI: http://www.cleverweb.nl/projects/clever-tweet/
Description: Clever Tweet is a widget that will integrate your latest tweets in your sidebar with a cool jQuery animation!
Version: 2.0.0
Author: P. Prins
Author URI: http://www.cleverweb.nl
License: GPL2
*/
global $g_sErrorMessage;

/*
 * CTactivate is being called when activating the plugin. 
 * It will attempt to create a directory /cache in wp-content
*/
define("CT_CACHE_DIR", WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'cache');

function CTactivate() {
	CTsysCheck();
}
/*
 * Do some things when activating our plugin		
 * we call CTactivate() here. 
*/		
register_activation_hook(__FILE__, 'CTactivate');

/*
 * CTdeactivate will be called when de-activating our plugin.
 *
*/
function CTdeactivate() {
	
	delete_option('clever_tweet');
	
}
register_deactivation_hook(__FILE__, 'CTdeactivate');

/*
 * Add settings link in plugin overview
*/
function CTpluginActionLinks($links, $file) {
	$l_sPluginFile = basename(__FILE__);
	if(basename($file) == $l_sPluginFile) {
		$l_sSettingsLink = '<a href="options-general.php?page=' . $l_sPluginFile . '">' . __('Settings', 'clever-tweet') . '</a>';
		array_unshift($links, $l_sSettingsLink);
	}
	return $links;
}

add_filter('plugin_action_links', 'CTpluginActionLinks', 10, 2);

/*
 * Do some checks on our cache directory.
*/
function CTsysCheck() {
	/*
	 * Check if directory cache exists in wp-content		 
	 *
	*/		
	if(!is_dir(CT_CACHE_DIR)) {
		if(!mkdir(CT_CACHE_DIR, 0755)) {			
			return false;
		}
	}	
	else {
		$l_iFilePerms = substr(decoct(fileperms(CT_CACHE_DIR)),2);
		
		if($l_iFilePerms !== '755') {
			return false;
		}
		else {
			return true;
		}
	}	
}

/*
 * Initialize our Widget
*/
function CTinit() {
	
	/*
	 * Woah, we need jQuery
	*/
	wp_enqueue_script('jquery');

	/*
	 * Add styling and Javascript... inline. Say what? WHAT?
	 * Somehow I tried the following: wp_enqueue_script('clever-tweet.js.php' etc. (see the .php extension).
	 * Unfortunately WordPress doesn't allow me to inlcude things withing my dynamic CSS or JS.
	 *
	 * It might be an ID-10-T error, so if you have a better solution, let me know! 
	 * You will be rewarded with eternal glory. 
	*/
	add_action('wp_head', 'CTaddCss');
	add_action('wp_head', 'CTaddJavaScript');		

	register_sidebar_widget('Clever Tweet', 'CTwidget');
	register_widget_control('Clever Tweet', 'CTwidgetControl');
}
	
/*
 * Initialize our plugin / widget
*/
add_action("widgets_init", 'CTinit');


/*
 * Settings page for our plugin
 *
*/
function CTsettingsMenu(){
	add_options_page('Clever Tweet settings', 'Clever Tweet', 'manage_options', 'clever_tweet.php', 'CTcontrol');
}
add_action('admin_menu', 'CTsettingsMenu');


/*
 * CTcontrol
 * 	 
 * Create the control admin page of our widget
*/	
function CTcontrol() {
	?>
	<style type="text/css">
		label,input, select {
			display: block;	
			float: left;	
		}

		label {			
			text-align: right;
			font-weight: bold;
			width: 300px;
			padding-right: 20px;
			padding-top: 3px;
			font: 14px/16px tahoma, arial, helvetica, sans-serif;	
			color:#000;
		}
		
	</style>
	<?php
		/*
		 * If $_POST, update the plugin settings.		 
		 *
		*/		
		if (isset($_POST['clever_tweet_username'])){
			CTcontrolUpdate();
		}
		
		/*
		 * If our plugin check fails (cache dir) 
		 * Print an error message
		 *
		*/		
		if(!CTsysCheck()) {
			print '<p><b>Error:</b> The cache directory is not setup properly. Please create the following directory: ' .CT_CACHE_DIR.' and set the follwing directory permissions: 0755 <br />See this well documented page: <a href="http://codex.wordpress.org/Changing_File_Permissions">WordPress Codex - File Permissions</a>.</p>';
			return;
		}

	$data = get_option('clever_tweet');
	?>
	<p>&nbsp;</p>
	<div class="metabox-holder">
		<div class="postbox">
			<h3 class="global-settings">Clever Tweet settings</h3>
			<?php
				/*
				 * If there is an error present, print the message
				 *
				*/	
				if(CTgetError() != false) { print '<p style="color: red; padding: 2px;">'.CTgetError().'</p>';  } 

				/*
				 * Check for safe_mode
				*/
				if( ini_get('safe_mode') ){
					print '<p style="color: red; padding: 2px;">PHP safe_mode is turned on. This might prevent Clever Tweet from creating your cache directory. If you have problems with caching see the <a href="http://www.cleverweb.nl/projects/clever-tweet/faq#cache" target="_blank">FAQ</a> on how to fix this </p>';
				}
				/*				 
				 * Check for json_decode				 
				*/
				if(!function_exists('json_decode')) {
					print '<p style="color: red; padding: 2px;">I am sorry, your webhost doesn\'t support the json_decode function. Therefore this plugin will not work on your site.</p>';
				}
				
			?>
			 <form method="post" action="">	 
			 <p>
				<label>Title:</label><input name="clever_tweet_title" type="text" value="<?php echo $data['title']; ?>" /><br />
			</p>
			 <p>
				<label>Twitter username:</label><input name="clever_tweet_username" type="text" value="<?php echo $data['username']; ?>" /><br />
			</p>
			<p>
				<label>Shown tweets:</label><input name="clever_tweet_shown" type="text" value="<?php echo $data['shown']; ?>" /><br />
			</p>	
			<p>
				<label>Max tweets:</label><input name="clever_tweet_max" type="text" value="<?php echo $data['max']; ?>" /><br />
			</p>	
			<p>
				<label>Cache refresh time:</label><input name="clever_tweet_cache" type="text" value="<?php echo $data['cache']; ?>" /> (default 300 = 5 minutes)<br />
			</p>
			<p>
				<label>Do not show mentions (@user)</label><input name="clever_tweet_mentions" type="checkbox" <?php ($data["mentions"] == 'on' ? print 'checked="checked"' : ''); ?> /><br />
			</p>
			<p>
				<label>Do not show retweets (RT)</label><input name="clever_tweet_retweets" type="checkbox" <?php ($data["retweets"] == 'on' ? print 'checked="checked"' : ''); ?> /><br />
			</p>
			<p>
				<label>Create url's from hashtags (#topic)</label><input name="clever_tweet_parsehashtags" type="checkbox" <?php ($data["parsehashtags"] == 'on' ? print 'checked="checked"' : ''); ?> /><br />
			</p>
			<p>
				<label>Create clickable urls from (http://bit.ly)</label><input name="clever_tweet_parseurls" type="checkbox" <?php ($data["parseurls"] == 'on' ? print 'checked="checked"' : ''); ?> /><br />
			</p>
			<p>
				<label>Scroll interval (ms)</label><input name="clever_tweet_interval" type="text" value="<?php echo $data['interval']; ?>" />(1500 = 1.5 second which is the minimal interval.)<br />
			</p>
			<p>
				<label>Tweet block height (see <a href="http://www.cleverweb.nl/projects/clever-tweet/faq/">help</a>!)</label><input name="clever_tweet_height" type="text" value="<?php echo $data['height']; ?>" /><br />
			</p>
			<p>
				<label>Add an extra few pixels to the widget</label><input name="clever_tweet_heighttuneup" type="text" value="<?php echo $data['heighttuneup']; ?>" /><br />
			</p>
			<p>
				<label>Tweet max character length</label><input name="clever_tweet_length" type="text" value="<?php echo $data['length']; ?>" /><br />
			</p>
			<p>
				<label>Add tail after trimmed tweet</label><input name="clever_tweet_tail" type="text" value="<?php echo $data['tail']; ?>" /><br />
			</p>
			<p>
				<label>Tweet date format (see <a href="http://www.php.net/manual/en/function.date.php">PHP manual</a>, leave empty to ommit date)</label><input name="clever_tweet_date_format" type="text" value="<?php echo $data['date_format']; ?>" />example "D M d, Y" gives <?php echo date("D M d, Y") ?><br />
			</p>
			<p>&nbsp;</p>
			<label>Save settings:</label><input type="submit" value="Update" /><br /><br />
			</form>
		</div>
	</div>

	<?php	
}

/*
 * Simple Widget control form because we now use a main settings page
 *
 * Updates Title of the widget
*/
function CTwidgetControl() {
	$data = get_option('clever_tweet');
	?>
	 <p>
		<label>Title: </label><input name="clever_tweet_title" type="text" value="<?php echo $data['title']; ?>" /><br />
	</p>
	<?php
	
	if (isset($_POST['clever_tweet_title'])){
		$data['title'] = attribute_escape($_POST['clever_tweet_title']);
		update_option('clever_tweet', $data);	
	}
}


/*
 * CTcontrolUpdate
 * 
 * update our widget control form
*/	
function CTcontrolUpdate() {	
	
	if(preg_match('/^\d+$/', $_POST['clever_tweet_cache']) < 1) {
		CTsetError('Cache time must be numeric!');
		return false;
	}

	if(preg_match('/^\d+$/', $_POST['clever_tweet_max']) < 1) {
		CTsetError('Max tweets must be numeric (0-9)');
		return false;
	}

	if(preg_match('/^\d+$/', $_POST['clever_tweet_shown']) < 1) {
		CTsetError('Shown tweets must be numeric (0-9)');
		return false;
	}

	if(preg_match('/^\d+$/', $_POST['clever_tweet_height']) < 1) {
		CTsetError('Height must be numeric (0-9)');
		return false;
	}

	if(preg_match('/^\d+$/', $_POST['clever_tweet_heighttuneup']) < 1) {
		CTsetError('Height tuneup must be numeric (0-9)');
		return false;
	}

	if(preg_match('/^\d+$/', $_POST['clever_tweet_length']) < 1) {
		CTsetError('Max length must be numeric (0-9)');
		return false;
	}

	if(preg_match('/^\d+$/', $_POST['clever_tweet_interval']) < 1) {
		CTsetError('Interval must be numeric (0-9)');
		return false;
	}

	if($_POST['clever_tweet_interval'] < 1500) {
		CTsetError('Interval must have a value of 1500 or higher.');
		return false;
	}
		
	$data['username'] = attribute_escape($_POST['clever_tweet_username']);
	$data['shown'] = attribute_escape($_POST['clever_tweet_shown']);
	$data['max'] = attribute_escape($_POST['clever_tweet_max']);
	$data['title'] = attribute_escape($_POST['clever_tweet_title']);
	$data['mentions'] = attribute_escape($_POST['clever_tweet_mentions']);
	$data['retweets'] = attribute_escape($_POST['clever_tweet_retweets']);
	$data['interval'] = attribute_escape($_POST['clever_tweet_interval']);
	$data['height'] = attribute_escape($_POST['clever_tweet_height']);
	$data['heighttuneup'] = attribute_escape($_POST['clever_tweet_heighttuneup']);
	$data['length'] = attribute_escape($_POST['clever_tweet_length']);
	$data['tail'] = attribute_escape($_POST['clever_tweet_tail']);
	$data['parsehashtags'] = attribute_escape($_POST['clever_tweet_parsehashtags']);
	$data['parseurls'] = attribute_escape($_POST['clever_tweet_parseurls']);
	$data['cache'] = attribute_escape($_POST['clever_tweet_cache']);
	$data['date_format'] = attribute_escape($_POST['clever_tweet_date_format']);
	
	update_option('clever_tweet', $data);		 
}

/*
 * CTsetError
 * 
 * Set an error message 
 * @p_sMsg; string, the error message
*/
function CTsetError($p_sMsg) {
	global $g_sErrorMessage;

	$g_sErrorMessage = $p_sMsg;
}

/*
 * CTgetError
 * 
 * Get the error message if there is one. 
*/
function CTgetError() {
	global $g_sErrorMessage;
	if($g_sErrorMessage !== '') {
		return $g_sErrorMessage;
	}
	else {
		return false;
	}
}

/*
 * CTfetchTweets
 * 
 * Fetch the tweets, parse and show it. 
*/	
function CTfetchTweets() {
	$data = get_option('clever_tweet');
	
	if(!$data['cache']) {
		$data['cache'] = 300;
	}

	/*
	 * Set cachefile	
	*/
	$l_sCacheFile = CT_CACHE_DIR . '/cache.dmp';
	
	/*
	 * Check for cache file existence or age of the cache file	 
	*/	
	if(!file_exists($l_sCacheFile) or (time() - filemtime($l_sCacheFile)) > $data['cache']) {
		
		/*
		 * Get feed and write it to cache		
		*/
		if($data['username'] != '') {
			
			/*				 
			 * Check for file_get_contents
			 * Check if it is possible to load the url with file_get_contents()
			*/
			if(!function_exists('file_get_contents')) {
				$l_sFeedContent = @file_get_contents('http://twitter.com/statuses/user_timeline/' . $data['username'] . '.json'); 
			}
			else {
					
				/*
				 * Well, the file_get_contents is not working, perhaps find a better webhost provider ;-)
				 * Will try to open it with default fopen()
				*/
				if($l_rFile = @fopen('http://twitter.com/statuses/user_timeline/' . $data['username'] . '.json', 'r')) {
					$l_sFeedContent = '';
					while (!@feof($l_rFile)) {
						$l_sFeedContent .= fread($l_rFile, 8192);
					}
					@fclose($l_rFile);
				}					
			}
			/*
			 * File is still empty, we can't load it :(
			 * Abort here and print message.
			*/
			if($l_sFeedContent == '') {
				print '<p>Error: Can\'t fetch Twitter feeds. Perhaps Twitter is down or your webhost doesn\'t support the loading of external feeds.</p>';
				return false;
			}								
			
			/*
			 * Open cache file and write the contents of our stream to it.
			*/
			if(!$fp = @fopen($l_sCacheFile, 'w')) {
				print 'Error: Can\'t open cache file for writing';
				return false;
			}
			if(!@fwrite($fp, $l_sFeedContent)) {
				print 'Error: Can\'t write cache file!';
				return false;
			}
			@fclose($fp);			
		}
		else {
			print 'Error: Can\'t fetch your Twitter RSS feed, perhaps Twitter is busy or down (try refresh) or you forgot to enter a username in the admin section?';
			return false;
		}
	}	

	/*
	 * Open cache file and read content in a string then use json to parse it.
	 * in stead of the simple_xml object.
	*/
	$l_rCacheFile = @fopen($l_sCacheFile, 'r');
	$l_sTweets = @fread($l_rCacheFile, @filesize($l_sCacheFile));
	$l_aTweets = json_decode($l_sTweets);
	
	print '<div id="clever-list">';
	print '<ul id="clever-ul">';
	$i=1;
	
	/*			 
	 * For each item parse it, and print it. 
	*/	
	foreach($l_aTweets as $tweet) {		
		
		/*
		 * Parse tweet. Method will check for mentions, retweets and whitespace.
		*/
		$l_sMsg = "";
		if(strlen($data['date_format'])>0)
			$l_sStatusLink = '<span class="clever-tweet-date"><a href="http://www.twitter.com/'.$data['username'].'/status/' . $tweet->id . '" target="_blank" rel="nofollow"> ' . CTparseDate($tweet->created_at, $data['date_format']) . ']</a></span> ';
		

		$l_sMsg .= CTparseTweet($tweet->text);			
		
		/*
		 * If tweet is a mention or retweet and we dont want to show them the parse method
		 * will return false
		*/
		if($l_sMsg != false) {					

				print '<li class="clever-tweet-item">' . trim($l_sMsg) . ' '.$l_sStatusLink.'</li>';
				$i++;
			}			
		
		/*
		 * If we reached max displayed tweets break
		*/
		if($i > $data['max']) {
			break;
		}
	
	}
	print '</ul>';
	print '</div>';	
}

/*
 * CTparseDate
 */
function CTparseDate($arg, $dateformat) {
	$argtime = strtotime($arg);
	if (function_exists(date_i18n))
		return date_i18n($dateformat, $argtime);
	else
		return date("D M d, Y", $argtime);
}

/*
 * CTparseTweet
 * 
 * Parse a tweet, it will check for whitespace, check if it's a mention (@username) or a retweet (RT).
 * Depending on the widget control values it will show mentions and retweets.
 *
 * @args: string : a tweet description
*/	
function CTparseTweet($args) {

	$data = get_option('clever_tweet');
	
	/*
	 * The tweet always contain 'username:', skip that. 
	*/
	$l_sTweet = str_replace($data['username'].':', '', $args);

	/*
	 * Cut the tweet if it's too large
	*/
	$l_sTweet = CTcut($l_sTweet);

	/*
	 * Check for mentions, delete them as well.
	*/		
	if($data['mentions'] == 'on') {
		if(preg_match('/^\@/', trim($l_sTweet), $matches)) {
			return false;
		}			
	}
	/*
	 *Check for RT (Retweets).
	*/
	if($data['retweets'] == 'on') {
		if(preg_match('/^RT \@/', trim($l_sTweet), $matches)) {
			return false;
		}
	}

	/*
	 * Parse urls (http://bit.ly/xxxxx)		 
	*/		
	if($data['parseurls'] == 'on') {
		$l_sTweet = preg_replace_callback('/\b(https?|ftp|file|scp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[A-Z0-9+&@#\/%=~_|]/i', 'CTparseUrl', $l_sTweet);
	}

	/*
	 * Parse hash tags (#topic)
	*/		
	if($data['parsehashtags'] == 'on') {
		$l_sTweet = preg_replace_callback('/ ?#.+? /i', 'CTparseHashTag', $l_sTweet);
	}	
	
	
	return $l_sTweet;
}	

/*
 * CTCut
 * This will check tweets for their length and cut them
 * on the given length. 
 * optional it will add a tail, like: ... or [..]
 */	
function CTcut($p_sText) {
	$data = get_option('clever_tweet');
	$l_iLength = $data['length'];
	if(!$data['tail']) {
		$l_sTail = '[..]';
	}
	else {
		$l_sTail = $data['tail'];
	}

	$l_iStringLength = 0;
	$a=0;

	if(strlen($p_sText) < $l_iLength) {
		return $p_sText;
	}

	$aWords = explode(" ", $p_sText); 
	if(!empty($aWords)) {
		foreach($aWords as $sWord) {
			$l_iStringLength = ($l_iStringLength+strlen($sWord));
			
			if($l_iStringLength > $l_iLength) {
				$l_iWordCap = $a;
				break;
			}
			$l_sText .= $sWord . ' ';
			$a++;
		}
	}

	return trim($l_sText) . $l_sTail;
}

/*
 * CTwidget
 * 
 * The actual widget	
*/
function CTwidget($args) {
	$data = get_option('clever_tweet');

	echo $args['before_widget'];
	echo $args['before_title'] . $data['title'] . $args['after_title'];		
	
	CTfetchTweets();

	echo $args['after_widget'];
}

/*
 * Somehow I tried to enqueue a css file with a php extension 
 * to add dynamic CSS, but Wordpress doesn't allow this or I don't know how to do it ;-)
 *
 * Any solution is most welcome!
 *
*/	
function CTaddCss() {
	$data = get_option('clever_tweet');

	/*
	 * Check for some values, will cause unexpected behaviour of jQuery
	 * when ommitted. 
	*/	
	if(!$data['shown']) {
		$data['shown'] = 3;
	}
	if(!$data['max']) {
		$data['max'] = 5;
	}
	if(!$data['height']) {
		$data['height'] = 50;
	}
	if(!$data['heighttuneup']) {
		$data['heighttuneup'] = 0;
	}


	$l_iBlockHeight = ($data['shown']*$data['height']) + $data['heighttuneup'];
	
	$l_sCss = '
	#clever-tweet { 
		display: block;
		height: '. $l_iBlockHeight . 'px;
		min-height: ' . $l_iBlockHeight .'px;
		position: relative;
	}

	#clever-list { 
		display: block;
		height: ' . $l_iBlockHeight . 'px;
		min-height: ' . $l_iBlockHeight .'px;
		position: relative;
	}

	#clever-list li {
		min-height: ' . $data['height'] . 'px;
		height: ' . $data['height'] . 'px;
		overflow: hidden;
		padding: 0 0 1px 15px;
		color: black;
	}

	#clever-list li a { 	
		color: #777;
		text-decoration: underline;
		padding: 0;
		display: inline;
	}

	.clever-tweet-link {
			padding: 8px;
			margin: 8px;			
			background: url(' . WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) . 'link.png) center left no-repeat; 
		}	
	
	.clever-tweet-date {
		font-weight: bold;
		font-size: 10px;
	}
	
	.clever-tweet-item {			
		margin-bottom: 3px;
	}
	';

	

	print '<style type="text/css">
		' . $l_sCss . '
	</style>';
}

/*
 * Somehow I tried to enqueue a js file with a php extension 
 * to add dynamic Javascript, but Wordpress doesn't allow this or I don't know how to do it ;-)
 *
 * Any solution is most welcome!
 *
*/
function CTaddJavaScript() {
	$data = get_option('clever_tweet');
	/*
	 * Check for some values, will cause unexpected behaviour of jQuery
	 * when ommitted. 
	*/	
	if(!$data['shown']) {
		$data['shown'] = 3;
	}
	if(!$data['max']) {
		$data['max'] = 5;
	}
	if(!$data['height']) {
		$data['height'] = 50;
	}
	if(!$data['interval'] || $data['interval'] < 1500) {
		$data['interval'] = 1500;
	}

	$l_sJs = '<script type="text/javascript">	
	
jQuery(document).ready(function() {

max = ' . $data['max'] . ';
shown = ' . $data['shown'] . ';
total = jQuery("#clever-ul").children().length;		
tweetHeight = ' . $data['height'] . ';
tweetMargin = 0;
tweetInterval = '. $data['interval'] . ';

// Hide childs
for(var i = 0; i < total; i++) {
if(i < shown)
	jQuery(jQuery("#clever-ul").children()[i]).css("display", "block");
else {
	jQuery(jQuery("#clever-ul").children()[i]).css("opacity", "1");
	jQuery(jQuery("#clever-ul").children()[i]).css("display", "none");
}
}

var interval = setInterval(CTrotate, tweetInterval);

jQuery("#clever-ul").mouseover(function() { clearInterval(interval) });
jQuery("#clever-ul").mouseout(function() { interval = setInterval(CTrotate, tweetInterval); });

function CTrotate() {

	jQuery(jQuery("#clever-ul").children()[0]).animate({ opacity: 0 }, 500, "linear", function() {
	
	jQuery(jQuery("#clever-ul").children()[0]).animate({ marginTop: -tweetHeight }, 500, "linear", function() {
		jQuery(jQuery("#clever-ul").children()[0]).css("display", "none");
	
		jQuery(jQuery("#clever-ul").children()[0]).css("margin", tweetMargin);
	
		jQuery("#clever-ul").append(jQuery(jQuery("#clever-ul").children()[0]));

		jQuery(jQuery("#clever-ul").children()[shown-1]).css("display", "block");
		jQuery(jQuery("#clever-ul").children()[shown-1]).animate({ opacity: 1 }, 900);
	});
});
}
});
</script>';

print $l_sJs;
}	


/*
 * CTparseHashTag
 * 
 * If matched the regexp from 'CTparseTweet' create clickable hashtags 
 * in our tweet.
*/
function CTparseHashTag($arg) {	
	
	$l = ' <a href="http://twitter.com/#search?q=' . trim(str_replace('#', '', $arg[0])) . '" target="_blank">'.  trim($arg[0]) .'</a> ';
	return $l;
}

/*
 * CTparseUrl
 * 
 * If matched the regexp from 'CTparseTweet' create clickable urls 
 * in our tweet.
*/
function CTparseUrl($arg) {
	
	return '<a href="' . $arg[0] . '" target="_blank">' . $arg[0] . '</a>';
}

?>