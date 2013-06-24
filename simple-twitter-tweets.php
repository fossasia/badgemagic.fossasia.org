<?php
/*
Plugin Name: Simple Twitter Tweets
Plugin URI: http://www.planet-interactive.co.uk/simple-twitter-tweets
Description: Display last x number tweets from Twitter API stream, store locally in dataabse to present past tweets when failure to access Twitters restrictive API occurs
Author: Ashley Sheinwald
Version: 1.1
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

class PI_SimpleTwitterTweets extends WP_Widget{

	function PI_SimpleTwitterTweets()  {
		$widget_ops = array('classname' => 'PI_SimpleTwitterTweets', 'description' => 'Displays the most recent tweets from your Twitter Stream' );
		//$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'Not-required-this-time' );  
		$this->WP_Widget('PI_SimpleTwitterTweets', 'Simple Twitter Tweets', $widget_ops);
	}
 
	function form($instance){
		
		//Set up some default widget settings.
		$defaults = array( 
			  'title' 			=> __('Recent Tweets', 'pi-tweet')
			, 'name' 			=> __('iPlanetUK', 'pi-tweet')
			, 'numTweets' 		=> __(4, 'pi-tweet') // How many to display
			, 'cacheTime' 		=> __(5, 'pi-tweet') // Time in minutes between updates
			, 'consumerKey' 		=> __('xxxxxxxxxxxx', 'pi-tweet') // Consumer key
			, 'consumerSecret' 		=> __('xxxxxxxxxxxx', 'pi-tweet') // Consumer secret
			, 'accessToken' 		=> __('xxxxxxxxxxxx', 'pi-tweet') // Access token
			, 'accessTokenSecret'	=> __('xxxxxxxxxxxx', 'pi-tweet') // Access token secret
			, 'exclude_replies'	=> true 
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
?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('name'); ?>">Twitter Name: <input class="widefat" id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" type="text" value="<?php echo attribute_escape($name); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('numTweets'); ?>">Number of Tweets: <input class="widefat" id="<?php echo $this->get_field_id('numTweets'); ?>" name="<?php echo $this->get_field_name('numTweets'); ?>" type="text" value="<?php echo attribute_escape($numTweets); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cacheTime'); ?>">Time in Minutes between updates: <input class="widefat" id="<?php echo $this->get_field_id('cacheTime'); ?>" name="<?php echo $this->get_field_name('cacheTime'); ?>" type="text" value="<?php echo attribute_escape($cacheTime); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('consumerKey'); ?>">Consumer Key: <input class="widefat" id="<?php echo $this->get_field_id('consumerKey'); ?>" name="<?php echo $this->get_field_name('consumerKey'); ?>" type="text" value="<?php echo attribute_escape($consumerKey); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('consumerSecret'); ?>">Consumer Secret: <input class="widefat" id="<?php echo $this->get_field_id('consumerSecret'); ?>" name="<?php echo $this->get_field_name('consumerSecret'); ?>" type="text" value="<?php echo attribute_escape($consumerSecret); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('accessToken'); ?>">Access Token: <input class="widefat" id="<?php echo $this->get_field_id('accessToken'); ?>" name="<?php echo $this->get_field_name('accessToken'); ?>" type="text" value="<?php echo attribute_escape($accessToken); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('accessTokenSecret'); ?>">Access Token Secret: <input class="widefat" id="<?php echo $this->get_field_id('accessTokenSecret'); ?>" name="<?php echo $this->get_field_name('accessTokenSecret'); ?>" type="text" value="<?php echo attribute_escape($accessTokenSecret); ?>" /></label>
		</p>
		<p>  
		    <input class="checkbox" type="checkbox" <?php checked( isset( $instance['exclude_replies']), true ); ?> id="<?php echo $this->get_field_id( 'exclude_replies' ); ?>" name="<?php echo $this->get_field_name( 'exclude_replies' ); ?>" />   
		    <label for="<?php echo $this->get_field_id( 'exclude_replies' ); ?>"><?php _e('Exclude @replies', 'pi-tweet'); ?></label>  
		</p>
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
	
		return $instance;
	}
 
	function widget($args, $instance){
		extract($args, EXTR_SKIP);

		echo $before_widget;

		//Our variables from the widget settings.
		$PI_title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
		$PI_name = $instance['name'];
		$PI_numTweets = $instance['numTweets'];
		$PI_cacheTime = $instance['cacheTime'];

		//Setup Twitter API OAuth tokens
		$PI_consumerKey = $instance['consumerKey'];
		$PI_consumerSecret = $instance['consumerSecret'];
		$PI_accessToken = $instance['accessToken'];
		$PI_accessTokenSecret = $instance['accessTokenSecret'];

		$PI_exclude_replies = isset( $instance['exclude_replies'] ) ? $instance['exclude_replies'] : false;


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
			$numTweets 			= $PI_numTweets; 	// Num tweets to show
			$name 				= $PI_name;				// Twitter UserName
			$cacheTime 			= $PI_cacheTime; 		// Time in minutes between updates.

			// Get from https://dev.twitter.com/
			// Login - Create New Application, fill in details and use required data below
			$consumerKey 		= $PI_consumerKey;		// OAuth Key
			$consumerSecret 	= $PI_consumerSecret;	// OAuth Secret
			$accessToken 		= $PI_accessToken;		// OAuth Access Token
			$accessTokenSecret 	= $PI_accessTokenSecret;// OAuth Token Secret

			$exclude_replies 	= $PI_exclude_replies; // Leave out @replies?

			$transName = 'list-tweets'; // Name of value in database.
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
			$totalToFetch = ($excludeReplies) ? max(50, $numTweets * 3) : $numTweets;
			
			$fetchedTweets = $connection->get(
				'statuses/user_timeline',
				array(
					'screen_name'     => $name,
					'count'           => $totalToFetch,
					'exclude_replies' => $excludeReplies
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
			    	$permalink = 'http://twitter.com/'. $name .'/status/'. $tweet->id_str;

			    	/* Alternative image sizes method: http://dev.twitter.com/doc/get/users/profile_image/:screen_name */
			    	$image = $tweet->user->profile_image_url;

			    	// Message. Convert links to real links.
			    	$pattern = '/http:(\S)+/';
			    	$replace = '<a href="${0}" target="_blank" rel="nofollow">${0}</a>';
			    	$text = preg_replace($pattern, $replace, $tweet->text);

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
			            <a href="http://www.twitter.com/<?php echo $name; ?>" target="_blank" title="Follow <?php echo $name; ?> on Twitter [Opens new window]"><?php echo human_time_diff($t['time'], current_time('timestamp')); ?> ago</a>
			            </em>
			        </li>
			    <?php endforeach; ?>

			<?php else : ?>
			    <li>Waiting for Twitter... Once Twitter is ready they will display my Tweets again.</li>
			<?php endif; ?>
			</ul>
			<?php
			// END OF WIDGET CODE HERE
			echo $after_widget;
		}
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("PI_SimpleTwitterTweets");') );
?>