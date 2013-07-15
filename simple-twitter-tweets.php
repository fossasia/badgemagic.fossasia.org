<?php
/*
Plugin Name: Simple Twitter Tweets
Plugin URI: http://www.planet-interactive.co.uk/simple-twitter-tweets
Description: Display last x number tweets from Twitter API stream, store locally in dataabse to present past tweets when failure to access Twitters restrictive API occurs
Author: Ashley Sheinwald
Version: 1.3.3
Author URI: http://www.planet-interactive.co.uk/
*/

/*  Copyright 2013  Ashley Sheinwald  (email : ashley@planet-interactive.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// USED FOR DEBUG
// include 'console.php';

// if(!class_exists('helpers')) {
// 	require 'libs/helpers.php';
// }



// TODO
// HELPER FUNCTIONS - Where and how
// Precess Links function for regex
// Assess truncated links use



class PI_SimpleTwitterTweets extends WP_Widget{

	function PI_SimpleTwitterTweets()  {
		$widget_ops = array('classname' => 'PI_SimpleTwitterTweets', 'description' => 'Displays the most recent tweets from your Twitter Stream' );
		//$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'Not-required-this-time' );
		$this->WP_Widget('PI_SimpleTwitterTweets', 'Simple Twitter Tweets', $widget_ops);

		// Load (enqueue) some JS in Admin ONLY on widgets page
		add_action('admin_enqueue_scripts', array(&$this, 'PI_load_admin_scripts'));
	}

	// Lets load some JS to aid widget display in Appearance->Widgets
	function PI_load_admin_scripts($hook) {
		if( $hook != 'widgets.php' )
			return;

		wp_enqueue_script('PI_stt_js', plugins_url( '/simple-twitter-tweets/js/sttAdmin.min.js' , dirname(__FILE__) ), array('jquery'));
	}

	function process_links($tweet) {

		// Is the Tweet a ReTweet - then grab the full text of the original Tweet
		if(isset($tweet->retweeted_status)) {
			// Split it so indices count correctly for @mentions etc.
			$rt_section = current(explode(":", $tweet->text));
			$text = $rt_section.": ";
			// Get Text
			$text .= $tweet->retweeted_status->text;
		} else {
			// Not a retweet - get Tweet
			$text = $tweet->text;
		}

/*
		// GRRRRR Retweet links entity reference for making links
		// Look into further when get a chance

		// Grab the Tweet entities for URLs etc.
	    $entities = $tweet->entities;

	    // STart replacing pertinent content to create required linkages
	    $replacements = array();
	    foreach ($entities->hashtags as $hashtag) {
	        list ($start, $end) = $hashtag->indices;
	        $replacements[$start] = array($start, $end-$start,
	            "<a href=\"https://twitter.com/search?q={$hashtag->text}\" target=\"_blank\">#{$hashtag->text}</a>");
	    }
	    foreach ($entities->urls as $url) {
	        list ($start, $end) = $url->indices;
	        // Was using $url->display_url but is verbose
	        $replacements[$start] = array($start, $end-$start,
	            "<a href=\"{$url->url}\" target=\"_blank\">{$url->url}</a>");
	    }
	    foreach ($entities->user_mentions as $mention) {
	        list ($start, $end) = $mention->indices;
	        $replacements[$start] = array($start, $end-$start,
	            "<a href=\"https://twitter.com/{$mention->screen_name}\" target=\"_blank\">@{$mention->screen_name}</a>");
	    }
	    foreach ($entities->media as $media) {
	        list ($start, $end) = $media->indices;
	        // Was using $media->display_url but is verbose - trying to overcome limits
	        $replacements[$start] = array($start, $end-$start,
	            "<a href=\"{$media->url}\" target=\"_blank\">{$media->url}</a>");
	    }

	    // sort in reverse order by start location
	    krsort($replacements);

	    foreach ($replacements as $replace_data) {
	        list ($start, $length, $replace_text) = $replace_data;
	        $text = substr_replace($text, $replace_text, $start, $length);
	    }
	    return $text;
*/

		// NEW Link Creation from clickable items in the text
		$text = preg_replace('/((http)+(s)?:\/\/[^<>\s]+)/i', '<a href="$0" target="_blank" rel="nofollow">$0</a>', $text );
		// Clickable Twitter names
		$text = preg_replace('/[@]+([A-Za-z0-9-_]+)/', '<a href="http://twitter.com/$1" target="_blank" rel="nofollow">@$1</a>', $text );
		// Clickable Twitter hash tags
		$text = preg_replace('/[#]+([A-Za-z0-9-_]+)/', '<a href="http://twitter.com/search?q=%23$1" target="_blank" rel="nofollow">$0</a>', $text );
		// END TWEET CONTENT REGEX
		return $text;

	}
	// END PROCESS LINKS - Using Entities


	function form($instance){

		//Set up some default widget settings.
		$defaults = array(
			  'title' 				=> __('Recent Tweets', 'pi-tweet')
			, 'name' 				=> __('iPlanetUK', 'pi-tweet')
			, 'numTweets' 			=> __(4, 'pi-tweet') // How many to display
			, 'cacheTime' 			=> __(5, 'pi-tweet') // Time in minutes between updates
			, 'consumerKey' 		=> __('xxxxxxxxxxxx', 'pi-tweet') // Consumer key
			, 'consumerSecret' 		=> __('xxxxxxxxxxxx', 'pi-tweet') // Consumer secret
			, 'accessToken' 		=> __('xxxxxxxxxxxx', 'pi-tweet') // Access token
			, 'accessTokenSecret'	=> __('xxxxxxxxxxxx', 'pi-tweet') // Access token secret
			, 'exclude_replies'		=> true
			, 'twitterFollow'		=> false
			, 'dataShowCount'		=> false
			, 'dataShowScreenName'	=> false
			, 'dataLang'			=> __('en', 'pi-tweet') // Language reference
		);
		$instance 			= wp_parse_args( (array) $instance, $defaults );
		$title 				= $instance['title'];
		$name 				= $instance['name'];
		$numTweets 			= $instance['numTweets'];
		$cacheTime 			= $instance['cacheTime'];
		$consumerKey 		= $instance['consumerKey'];
		$consumerSecret 	= $instance['consumerSecret'];
		$accessToken 		= $instance['accessToken'];
		$accessTokenSecret 	= $instance['accessTokenSecret'];
		$exclude_replies 	= $instance['exclude_replies'];
		$twitterFollow 		= $instance['twitterFollow'];
		$dataShowCount 		= $instance['dataShowCount'];
		$dataShowScreenName = $instance['dataShowScreenName'];
		$dataLang 			= $instance['dataLang'];
?>

		<?php
			// Show error if cURL not installed - extension required for Twitter API calls
			if (!in_array('curl', get_loaded_extensions())) {
	            echo '<p style="background-color:pink;padding:10px;border:1px solid red;"><strong>You do not have cURL installed! This is a required PHP extension to use the Twitter API: <a href="http://curl.haxx.se/docs/install.html" taget="_blank">cURL install</a></strong></p>';
	        }
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('name'); ?>">Twitter Name (without @ symbol): <input class="widefat" id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" type="text" value="<?php echo esc_attr($name); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('numTweets'); ?>">Number of Tweets: <input class="widefat" id="<?php echo $this->get_field_id('numTweets'); ?>" name="<?php echo $this->get_field_name('numTweets'); ?>" type="text" value="<?php echo esc_attr($numTweets); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cacheTime'); ?>">Time in Minutes between updates: <input class="widefat" id="<?php echo $this->get_field_id('cacheTime'); ?>" name="<?php echo $this->get_field_name('cacheTime'); ?>" type="text" value="<?php echo esc_attr($cacheTime); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('consumerKey'); ?>">Consumer Key: <input class="widefat" id="<?php echo $this->get_field_id('consumerKey'); ?>" name="<?php echo $this->get_field_name('consumerKey'); ?>" type="text" value="<?php echo esc_attr($consumerKey); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('consumerSecret'); ?>">Consumer Secret: <input class="widefat" id="<?php echo $this->get_field_id('consumerSecret'); ?>" name="<?php echo $this->get_field_name('consumerSecret'); ?>" type="text" value="<?php echo esc_attr($consumerSecret); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('accessToken'); ?>">Access Token: <input class="widefat" id="<?php echo $this->get_field_id('accessToken'); ?>" name="<?php echo $this->get_field_name('accessToken'); ?>" type="text" value="<?php echo esc_attr($accessToken); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('accessTokenSecret'); ?>">Access Token Secret: <input class="widefat" id="<?php echo $this->get_field_id('accessTokenSecret'); ?>" name="<?php echo $this->get_field_name('accessTokenSecret'); ?>" type="text" value="<?php echo esc_attr($accessTokenSecret); ?>" /></label>
		</p>
		<p>
		    <input class="checkbox" type="checkbox" <?php checked( isset( $instance['exclude_replies']), true ); ?> id="<?php echo $this->get_field_id( 'exclude_replies' ); ?>" name="<?php echo $this->get_field_name( 'exclude_replies' ); ?>" />
		    <label for="<?php echo $this->get_field_id( 'exclude_replies' ); ?>"><?php _e('Exclude_@replies', 'pi-tweet'); ?></label>
		</p>

		<!-- <p style="text-align:right;"><button class="button-secondary" style="background:red; color:#ffffff; text-shadow:none;" title="Only click if you have changed a setting esp. the number of Tweets to display - this deletes your cache which will be restored the next time your site successfully connects to Twitter!">Delete Cache</button></p> -->

		<div class="twitterFollow" style="background:#d6eef9;">
			<h4 class="button-secondary" style="width:100%; text-align:center;">Twitter Follow Button <span style="font-size:75%;">&#9660;</span></h4>
			<div style="padding:10px;">
				<p>
				    <input class="checkbox" type="checkbox" <?php checked( (isset( $instance['twitterFollow']) && ($instance['twitterFollow'] == "on") ), true ); ?> id="<?php echo $this->get_field_id( 'twitterFollow' ); ?>" name="<?php echo $this->get_field_name( 'twitterFollow' ); ?>" />
				    <label for="<?php echo $this->get_field_id( 'twitterFollow' ); ?>"><?php _e('Show Twitter Follow Button', 'pi-tweet'); ?></label>
				</p>
				<p>
				    <input class="checkbox" type="checkbox" <?php checked( (isset( $instance['dataShowScreenName']) && ($instance['dataShowScreenName'] == "true") ), true ); ?> id="<?php echo $this->get_field_id( 'dataShowScreenName' ); ?>" name="<?php echo $this->get_field_name( 'dataShowScreenName' ); ?>" value="true" />
				    <label for="<?php echo $this->get_field_id( 'dataShowScreenName' ); ?>"><?php _e('Show Twitter Screen Name', 'pi-tweet'); ?></label>
				</p>
				<p>
				    <input class="checkbox" type="checkbox" <?php checked( (isset( $instance['dataShowCount']) && ($instance['dataShowCount'] == "true") ), true ); ?> id="<?php echo $this->get_field_id( 'dataShowCount' ); ?>" name="<?php echo $this->get_field_name( 'dataShowCount' ); ?>" value="true" />
				    <label for="<?php echo $this->get_field_id( 'dataShowCount' ); ?>"><?php _e('Show Twitter Followers Count', 'pi-tweet'); ?></label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('dataLang'); ?>">Language: <input class="widefat" id="<?php echo $this->get_field_id('dataLang'); ?>" name="<?php echo $this->get_field_name('dataLang'); ?>" type="text" value="<?php echo esc_attr($dataLang); ?>" /></label>
				</p>
			</div>
		</div>
	<?php
	}

	function update($new_instance, $old_instance){
		$instance = $old_instance;

	    //Strip tags from title and name to remove HTML
	    $instance['title'] 				= strip_tags( $new_instance['title'] );
	    $instance['name'] 				= strip_tags( $new_instance['name'] );
	    $instance['numTweets'] 			= $new_instance['numTweets'];
	    $instance['cacheTime'] 			= $new_instance['cacheTime'];
	    $instance['consumerKey'] 		= $new_instance['consumerKey'];
	    $instance['consumerSecret'] 	= $new_instance['consumerSecret'];
	    $instance['accessToken'] 		= $new_instance['accessToken'];
	    $instance['accessTokenSecret'] 	= $new_instance['accessTokenSecret'];
	    $instance['exclude_replies'] 	= $new_instance['exclude_replies'];
	    $instance['twitterFollow'] 		= $new_instance['twitterFollow'];
		$instance['dataShowCount']		= $new_instance['dataShowCount'];
		$instance['dataShowScreenName']	= $new_instance['dataShowScreenName'];
		$instance['dataLang']			= $new_instance['dataLang'];

		return $instance;
	}

	function widget($args, $instance){
		extract($args, EXTR_SKIP);

		echo $before_widget;

		//Our variables from the widget settings.
		$PI_title 				= empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$PI_name 				= $instance['name'];
		$PI_numTweets 			= $instance['numTweets'];
		$PI_cacheTime 			= $instance['cacheTime'];

		//Setup Twitter API OAuth tokens
		$PI_consumerKey 		= $instance['consumerKey'];
		$PI_consumerSecret 		= $instance['consumerSecret'];
		$PI_accessToken 		= $instance['accessToken'];
		$PI_accessTokenSecret 	= $instance['accessTokenSecret'];

		$PI_exclude_replies 	= isset( $instance['exclude_replies'] ) ? $instance['exclude_replies'] : false;
		$PI_twitterFollow 		= isset( $instance['twitterFollow'] ) ? $instance['twitterFollow'] : false;

		$PI_dataShowCount 		= isset( $instance['dataShowCount'] ) ? $instance['dataShowCount'] : false;
		$PI_dataShowScreenName 	= isset( $instance['dataShowScreenName'] ) ? $instance['dataShowScreenName'] : false;
		$PI_dataLang 			= $instance['dataLang'];

		if (!empty($PI_title))
			echo $before_title . $PI_title . $after_title;;

			// START WIDGET CODE HERE
			?>

			<ul class="tweets">
			<?php
			/*
	 		 * Uses:
			 * Twitter API call:
			 *     http://dev.twitter.com/doc/get/statuses/user_timeline
			 * WP transient API ref.
			 *		http://www.problogdesign.com/wordpress/use-the-transients-api-to-list-the-latest-commenter/
			 * Plugin Development and Script enhancement
			 *    http://www.planet-interactive.co.uk
			 */

			// Configuration.
			$numTweets 			= $PI_numTweets; 		// Num tweets to show
			$name 				= $PI_name;				// Twitter UserName
			$cacheTime 			= $PI_cacheTime; 		// Time in minutes between updates.

			// Get from https://dev.twitter.com/
			// Login - Create New Application, fill in details and use required data below
			$consumerKey 		= $PI_consumerKey;		// OAuth Key
			$consumerSecret 	= $PI_consumerSecret;	// OAuth Secret
			$accessToken 		= $PI_accessToken;		// OAuth Access Token
			$accessTokenSecret 	= $PI_accessTokenSecret;// OAuth Token Secret

			$exclude_replies 	= $PI_exclude_replies; 	// Leave out @replies?
			$twitterFollow 		= $PI_twitterFollow; 	// Whether to show Twitter Follow button

			$dataShowCount 		= ($PI_dataShowCount != "true") ? "false" : "true"; // Whether to show Twitter Follower Count
			$dataShowScreenName	= ($PI_dataShowScreenName != "true") ? "false" : "true"; // Whether to show Twitter Screen Name
			$dataLang 			= $PI_dataLang; // Tell Twitter what Language is being used

			// COMMUNITY REQUEST !!!!!! (1)
			$transName = 'list-tweets-'.$name; // Name of value in database. [added $name for multiple account use]
			$backupName = $transName . '-backup'; // Name of backup value in database.

			// Do we already have saved tweet data? If not, lets get it.
			if(false === ($tweets = get_transient($transName) ) ) :

			// Get the tweets from Twitter.
			include 'twitteroauth/twitteroauth.php';

			$connection = new TwitterOAuth(
				$consumerKey,   		// Consumer key
				$consumerSecret,   	// Consumer secret
				$accessToken,   		// Access token
				$accessTokenSecret	// Access token secret
			);

			// If excluding replies, we need to fetch more than requested as the
			// total is fetched first, and then replies removed.
			$totalToFetch = ($exclude_replies) ? max(50, $numTweets * 3) : $numTweets;

			$fetchedTweets = $connection->get(
				'statuses/user_timeline',
				array(
					'screen_name'     => $name,
					'count'           => $totalToFetch,
					'exclude_replies' => $exclude_replies
				)
			);

			// Did the fetch fail?
			if($connection->http_code != 200) :
				$tweets = get_option($backupName); // False if there has never been data saved.

			else :
				// Fetch succeeded.
				// Now update the array to store just what we need.
				// (Done here instead of PHP doing this for every page load)
				$limitToDisplay = min($numTweets, count($fetchedTweets));

				for($i = 0; $i < $limitToDisplay; $i++) :
					$tweet = $fetchedTweets[$i];



			    	// Core info.
			    	$name = $tweet->user->name;

					// COMMUNITY REQUEST !!!!!! (2)
			    	$screen_name = $tweet->user->screen_name;

			    	$permalink = 'http://twitter.com/'. $name .'/status/'. $tweet->id_str;

			    	/* Alternative image sizes method: http://dev.twitter.com/doc/get/users/profile_image/:screen_name */
			    	$image = $tweet->user->profile_image_url;

					// Process Tweets - Use Twitter entities for correct URL, hash and mentions
					$text = $this->process_links($tweet);


			    	// Need to get time in Unix format.
			    	$time = $tweet->created_at;
			    	$time = date_parse($time);
			    	$uTime = mktime($time['hour'], $time['minute'], $time['second'], $time['month'], $time['day'], $time['year']);

			    	// Now make the new array.
			    	$tweets[] = array(
			    		'text' => $text,
			    		'name' => $name,
			    		'permalink' => $permalink,
			    		'image' => $image,
			    		'time' => $uTime
			    		);
				endfor;

				// Save our new transient, and update the backup.
				set_transient($transName, $tweets, 60 * $cacheTime);
				update_option($backupName, $tweets);
				endif;
			endif;

			// Now display the tweets, if we can.
			if($tweets) : ?>
			    <?php foreach($tweets as $t) : ?>
			        <li><?php echo $t['text']; ?>
			            <br/><em>
			            <?php if(!isset($screen_name)){ $screen_name = $name; }?>
						<a href="http://www.twitter.com/<?php echo $screen_name; ?>" target="_blank" title="Follow <?php echo $name; ?> on Twitter [Opens new window]"><?php echo human_time_diff($t['time'], current_time('timestamp')); ?> ago</a>
			            </em>
			        </li>
			    <?php endforeach; ?>

			<?php else : ?>
			    <li>Waiting for Twitter... Once Twitter is ready they will display my Tweets again.</li>
			<?php endif; ?>
			</ul>

			<?php
			 	// ADD Twitter follow button - to increase engagement
				// Make it an options choice though
				if($PI_twitterFollow){
			?>
				<a href="https://twitter.com/<?php echo $PI_name; ?>" class="twitter-follow-button" data-show-count="<?php echo $dataShowCount; ?>" data-show-screen-name="<?php echo $dataShowScreenName; ?>" data-lang="<?php echo $dataLang; ?>">Follow @<?php echo $PI_name; ?></a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
			<?php
				}
			// END OF WIDGET CODE HERE
			echo $after_widget;
		}

}
add_action( 'widgets_init', create_function('', 'return register_widget("PI_SimpleTwitterTweets");') );
?>