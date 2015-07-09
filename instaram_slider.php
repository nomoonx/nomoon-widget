<?php
/*
Plugin Name: noMoon Instagram Slider Widget
Plugin URI: https://github.com/nomoonx/nomoon-widget
Version: 1.0
Description: Instagram Slider Widget is a responsive slider widget that shows 20 latest images from a public instagram user.
Author: noMoon
License: GPLv2 or later
*/

/**
 * On widgets Init register Widget
 */
add_action( 'widgets_init', array( 'noMoon_InstagramSlider', 'register_widget' ) );

/**
 * JR_InstagramSlider Class
 */
class JR_InstagramSlider extends WP_Widget {
	
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @var     string
	 */
	const VERSION = '1.0';
	
	/**
	 * Initialize the plugin by registering widget and loading public scripts
	 *
	 */
	public function __construct() {
		
		// Widget ID and Class Setup
		parent::__construct( 'nm_insta_slider', __( 'Instagram Slider', 'nminstaslider' ), array(
				'classname' => 'nm-insta-slider',
				'description' => __( 'A widget that displays a slider with instagram images ', 'nminstaslider' ) 
			) 
		);
		
		// Instgram Action to display images
		add_action( 'jr_instagram', array( $this, 'instagram_images' ) );

		// Enqueue Plugin Styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this,	'public_enqueue' ) );
		
		// Enqueue Plugin Styles and scripts for admin pages
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		
		// Action when attachments are deleted
		// add_action( 'delete_attachment', array( $this, 'delete_wp_attachment' ) );
		
		// Ajax action to unblock images from widget 
		// add_action( 'wp_ajax_jr_unblock_images', array( $this, 'unblock_images' ) );

		// Add new attachment field desctiptions
		// add_filter( 'attachment_fields_to_edit', array( $this, 'insta_attachment_fields' ) , 10, 2 );
	}

	/**
	 * Register widget on windgets init
	 */
	public static function register_widget() {
		register_widget( __CLASS__ );
	}
	
	/**
	 * Enqueue public-facing Scripts and style sheet.
	 */
	public function public_enqueue() {
		
		wp_enqueue_style( 'instag-slider', plugins_url( 'assets/css/instag-slider.css', __FILE__ ), array(), self::VERSION );
		
		wp_enqueue_script( 'jquery-pllexi-slider', plugins_url( 'assets/js/jquery.flexslider-min.js', __FILE__ ), array( 'jquery' ), '2.2', false );
	}
	
	/**
	 * Enqueue admin side scripts and styles
	 * 
	 * @param  string $hook
	 */
	public function admin_enqueue( $hook ) {
		
		if ( 'widgets.php' != $hook ) {
			return;
		}
		
		wp_enqueue_style( 'jr-insta-admin-styles', plugins_url( 'assets/css/jr-insta-admin.css', __FILE__ ), array(), self::VERSION );

		wp_enqueue_script( 'jr-insta-admin-script', plugins_url( 'assets/js/jr-insta-admin.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
				
	}
	
	/**
	 * The Public view of the Widget  
	 *
	 * @return mixed
	 */
	public function widget( $args, $instance ) {
		
		extract( $args );
		
		//Our variables from the widget settings.
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		echo $before_widget;
		
		// Display the widget title 
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		do_action( 'jr_instagram', $instance );
		
		echo $after_widget;
	}
	
	/**
	 * Update the widget settings 
	 *
	 * @param    array    $new_instance    New instance values
	 * @param    array    $old_instance    Old instance values	 
	 *
	 * @return array
	 */
	public function update( $new_instance, $instance ) {
				
		$instance['title']            = strip_tags( $new_instance['title'] );
		$instance['search_for']       = $new_instance['search_for'];
		$instance['username']         = $new_instance['username'];
		$instance['hashtag']          = $new_instance['hashtag'];
		$instance['template']         = $new_instance['template'];
		$instance['images_number']    = $new_instance['images_number'];
		$instance['controls']         = $new_instance['controls'];
		$instance['animation']        = $new_instance['animation'];
		$instance['caption_words']    = $new_instance['caption_words'];
		$instance['slidespeed']       = $new_instance['slidespeed'];
		$instance['description']      = $new_instance['description'];
		
		return $instance;
	}
	
	
	/**
	 * Widget Settings Form
	 *
	 * @return mixed
	 */
	public function form( $instance ) {

		$defaults = array(
			'title'            => __('Instagram Slider', 'nminstaslider'),
			'search_for'       => 'username',
			'username'         => '',
			'hashtag'          => '',
			'template'         => 'slider',
			'images_number'    => 5,
			'controls'		   => 'prev_next',
			'animation'        => 'slide',
			'caption_words'    => 100,
			'slidespeed'       => 7000,
			'description'      => array( 'username', 'time','caption' )
		);
		
		$instance = wp_parse_args( (array) $instance, $defaults );
			
		?>
		<div class="jr-container">
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><strong><?php _e('Title:', 'nminstaslider'); ?></strong></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
			</p>
			<p>
				<strong><?php _e( 'Search Instagram for:', 'nminstaslider' ); ?></strong>
				<span class="jr-search-for-container"><label class="jr-seach-for"><input type="radio" id="<?php echo $this->get_field_id( 'search_for' ); ?>" name="<?php echo $this->get_field_name( 'search_for' ); ?>" value="username" <?php checked( 'username', $instance['search_for'] ); ?> /> <?php _e( 'Username:', 'nminstaslider' ); ?></label> <input id="<?php echo $this->get_field_id( 'username' ); ?>" class="inline-field-text" name="<?php echo $this->get_field_name( 'username' ); ?>" value="<?php echo $instance['username']; ?>" /></span>
				<span class="jr-search-for-container"><label class="jr-seach-for"><input type="radio" id="<?php echo $this->get_field_id( 'search_for' ); ?>" name="<?php echo $this->get_field_name( 'search_for' ); ?>" value="hashtag" <?php checked( 'hashtag', $instance['search_for'] ); ?> /> <?php _e( 'Hashtag:', 'nminstaslider' ); ?></label> <input id="<?php echo $this->get_field_id( 'hashtag' ); ?>" class="inline-field-text" name="<?php echo $this->get_field_name( 'hashtag' ); ?>" value="<?php echo $instance['hashtag']; ?>" /> <small><?php _e('without # sign', 'nminstaslider'); ?></small></span>
			</p>
			<p>
				<label  for="<?php echo $this->get_field_id( 'images_number' ); ?>"><strong><?php _e( 'Number of images to show:', 'nminstaslider' ); ?></strong>
					<input  class="small-text" id="<?php echo $this->get_field_id( 'images_number' ); ?>" name="<?php echo $this->get_field_name( 'images_number' ); ?>" value="<?php echo $instance['images_number']; ?>" />
				</label>
			</p>		
			
			<div class="jr-advanced-input <?php echo $advanced_class; ?>">

				<div class="jr-slider-options <?php if ( 'thumbs' == $instance['template'] || 'thumbs-no-border' == $instance['template'] ) echo 'hidden'; ?>">
					<h4 class="jr-advanced-title"><?php _e( 'Advanced Slider Options', 'nminstaslider'); ?></h4>
					<p>
						<?php _e( 'Slider Navigation Controls:', 'nminstaslider' ); ?><br>
						<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'controls' ); ?>" name="<?php echo $this->get_field_name( 'controls' ); ?>" value="prev_next" <?php checked( 'prev_next', $instance['controls'] ); ?> /> <?php _e( 'Prev & Next', 'nminstaslider' ); ?></label>  
						<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'controls' ); ?>" name="<?php echo $this->get_field_name( 'controls' ); ?>" value="numberless" <?php checked( 'numberless', $instance['controls'] ); ?> /> <?php _e( 'Dotted', 'nminstaslider' ); ?></label>
						<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'controls' ); ?>" name="<?php echo $this->get_field_name( 'controls' ); ?>" value="none" <?php checked( 'none', $instance['controls'] ); ?> /> <?php _e( 'No Navigation', 'nminstaslider' ); ?></label>
					</p>
					<p>
						<?php _e( 'Slider Animation:', 'nminstaslider' ); ?><br>
						<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'animation' ); ?>" name="<?php echo $this->get_field_name( 'animation' ); ?>" value="slide" <?php checked( 'slide', $instance['animation'] ); ?> /> <?php _e( 'Slide', 'nminstaslider' ); ?></label>  
						<label class="jr-radio"><input type="radio" id="<?php echo $this->get_field_id( 'animation' ); ?>" name="<?php echo $this->get_field_name( 'animation' ); ?>" value="fade" <?php checked( 'fade', $instance['animation'] ); ?> /> <?php _e( 'Fade', 'nminstaslider' ); ?></label>
					</p>
					<p>
						<label  for="<?php echo $this->get_field_id( 'caption_words' ); ?>"><?php _e( 'Number of words in caption:', 'nminstaslider' ); ?>
							<input class="small-text" id="<?php echo $this->get_field_id( 'caption_words' ); ?>" name="<?php echo $this->get_field_name( 'caption_words' ); ?>" value="<?php echo $instance['caption_words']; ?>" />
						</label>
					</p>					
					<p>
						<label  for="<?php echo $this->get_field_id( 'slidespeed' ); ?>"><?php _e( 'Slide Speed:', 'nminstaslider' ); ?>
							<input class="small-text" id="<?php echo $this->get_field_id( 'slidespeed' ); ?>" name="<?php echo $this->get_field_name( 'slidespeed' ); ?>" value="<?php echo $instance['slidespeed']; ?>" />
							<span><?php _e('milliseconds', 'nminstaslider'); ?></span>
							<span class='jr-description'><?php _e('1000 milliseconds = 1 second', 'nminstaslider'); ?></span>
						</label>
					</p>					
					<p>
						<label for="<?php echo $this->get_field_id('description'); ?>"><?php _e( 'Slider Text Description:', 'nminstaslider' ); ?></label>
						<select size=3 class='widefat' id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>[]" multiple="multiple">
							<option class="<?php if ( 'hashtag' == $instance['search_for'] ) echo 'hidden'; ?>" value='username' <?php $this->selected( $instance['description'], 'username' ); ?>><?php _e( 'Username', 'nminstaslider'); ?></option>
							<option value='time'<?php $this->selected( $instance['description'], 'time' ); ?>><?php _e( 'Time', 'nminstaslider'); ?></option> 
							<option value='caption'<?php $this->selected( $instance['description'], 'caption' ); ?>><?php _e( 'Caption', 'nminstaslider'); ?></option> 
						</select>
						<span class="jr-description"><?php _e( 'Hold ctrl and click the fields you want to show/hide on your slider. Leave all unselected to hide them all. Default all selected.', 'nminstaslider') ?></span>
					</p>					
				</div>
			</div>
			<?php $widget_id = preg_replace( '/[^0-9]/', '', $this->id ); if ( $widget_id != '' ) : ?>
			<p>
				<label for="jr_insta_shortcode"><?php _e('Shortcode of this Widget:', 'nminstaslider'); ?></label>
				<input id="jr_insta_shortcode" onclick="this.setSelectionRange(0, this.value.length)" type="text" class="widefat" value="[jr_instagram id=&quot;<?php echo $widget_id ?>&quot;]" readonly="readonly" style="border:none; color:black; font-family:monospace;">
				<span class="jr-description"><?php _e( 'Use this shortcode in any page or post to display images with this widget configuration!', 'nminstaslider') ?></span>
			</p>
			<?php endif; ?>
			<a target="_blank" title="Donate To Keep This Plugin Alive!" href="http://goo.gl/RZiu34"><p class="donate"><span></span>Donate To Keep This Plugin Alive!</p></a>        
		</div>
		<?php
	}

	/**
	 * Selected array function echoes selected if in array
	 * 
	 * @param  array $haystack The array to search in
	 * @param  string $current  The string value to search in array;
	 * 
	 * @return string
	 */
	public function selected( $haystack, $current ) {
		
		if( is_array( $haystack ) && in_array( $current, $haystack ) ) {
			selected( 1, 1, true );
		}
	}

	/**
	 * Echoes the Display Instagram Images method
	 * 
	 * @param  array $args
	 * 
	 * @return void
	 */
	public function instagram_images( $args ) {
		echo $this->display_images( $args );
	}

	/**
	 * Runs the query for images and returns the html
	 * 
	 * @param  array  $args 
	 * 
	 * @return string       
	 */
	private function display_images( $args ) {
		
		$username         = isset( $args['username'] ) && !empty( $args['username'] ) ? $args['username'] : false;
		$images_number    = isset( $args['images_number'] ) ? absint( $args['images_number'] ) : 5;
		$controls         = isset( $args['controls'] ) ? $args['controls'] : 'prev_next';
		$animation        = isset( $args['animation'] ) ? $args['animation'] : 'slide';
		$caption_words    = isset( $args['caption_words'] ) ? $args['caption_words'] : 100;
		$slidespeed       = isset( $args['slidespeed'] ) ? $args['slidespeed'] : 7000;
		$description      = isset( $args['description'] ) ? $args['description'] : array();
		$widget_id        = isset( $args['widget_id'] ) ? $args['widget_id'] : preg_replace( '/[^0-9]/', '', $this->id );

		$images_div_class = 'jr-insta-thumb';


			
			$template_args['description'] = $description;
			$direction_nav = ( $controls == 'prev_next' ) ? 'true' : 'false';
			$control_nav   = ( $controls == 'numberless' ) ? 'true': 'false';
			$ul_class      = 'instaslides';


				$images_div_class = 'pllexislider pllexislider-normal instaslider-nr-'. $widget_id;
				$slider_script =
				"<script type='text/javascript'>" . "\n" .
				"	jQuery(document).ready(function($) {" . "\n" .
                "       var instagramUsername={$username};". "\n" .
				"	console.log(instagramUsername);"."\n" .
				"	var searchUserIdUrl='https:\/\/api.instagram.com\/v1\/users\/search?q='+instagramUsername+'&client_id=44a3704cf42b4a2eb8329cba1054b450';"."\n" .
				"	console.log(searchUserIdUrl);"."\n" .
				"	$.ajax({url: searchUserIdUrl, dataType: 'jsonp',success: function(searchUserResult){"."\n" .
                "       console.log('in');"."\n" .
                "       console.log(searchUserResult.data);"."\n" .
                "       var userId=null;"."\n" .
                "       for (var i = searchUserResult.data.length - 1; i >= 0; i--) {"."\n".
                "           var user=searchUserResult.data[i];"."\n".
	            "			console.log(user);"."\n".
				"			if(user.username==instagramUsername){"."\n"."
                                userId=user.id;"."\n"."
                                break;"."\n"."
                            }"."\n"."
						};"."\n"."
						console.log(userId);"."\n"."
						var userResentMediaUrl='https:\/\/api.instagram.com\/v1\/users\/'+userId+'\/media\/recent?client_id=44a3704cf42b4a2eb8329cba1054b450&count='+{$images_number};"."\n"."
						$.ajax({"."\n"."
							url:userResentMediaUrl,"."\n"."
							dataType: 'jsonp',"."\n"."
							success: function(searchMediaResult){"."\n"."
                                console.log('media searched');"."\n"."
                                console.log(searchMediaResult);"."\n"."
                                var pictureList=searchMediaResult.data;"."\n"."
                                console.log(pictureList);"."\n"."
                                for (var i = pictureList.length - 1; i >= 0; i--) {"."\n"."
                                    var picture=pictureList[i];"."\n"."
									console.log(picture);"."\n"."
									var element=\"<li><a href='\"+picture.link+\"'><img src='\"+picture.images.low_resolution.url+\"'/></a></li>\";"."\n"."
									$('.instaslides').append(element);"."\n"."
								};"."\n"."
								$('.instaslider-nr-{$widget_id}').pllexislider({" . "\n" .
                "			        animation: '{$animation}'," . "\n" .
                "			        slideshowSpeed: {$slidespeed}," . "\n" .
                "			        directionNav: {$direction_nav}," . "\n" .
                "			        controlNav: {$control_nav}," . "\n" .
                "			        prevText: ''," . "\n" .
                "			        nextText: ''," . "\n" .
                "                   selector:'.instaslides > li'". "\n" ."
					            });"."\n"."
							}"."\n"."
						});"."\n"."
					}});"."\n".

				"</script>" . "\n";



		$images_div = "<div class='{$images_div_class}'>\n";
		$images_ul  = "<ul class='no-bullet {$ul_class}'>\n";



				$output = $slider_script . $images_div . $images_ul;



				$output .= "</ul>\n</div>";

		
		return $output;
		
	}

	/**
	 * Function to display Templates styles
	 *
	 * @param    string    $template
	 * @param    array	   $args	    
	 *
	 * return mixed
	 */
	private function get_template( $template, $args ) {

		$link_to   = isset( $args['link_to'] ) ? $args['link_to'] : false;
		
		if ( ( $args['search_for'] == 'user' && $args['attachment'] !== true && $args['source'] == 'instagram' ) || $args['search_for'] == 'hashtag' ) {
			$caption   = $args['caption'];
			$time      = $args['timestamp'];
			$username  = $args['username'];
			$image_url = $args['image'];
		} else {
			$attach_id = get_the_id();
			$caption   = get_the_excerpt();
			$time      = get_post_meta( $attach_id, 'jr_insta_timestamp', true );
			$username  = get_post_meta( $attach_id, 'jr_insta_username', true );
			$image_url = wp_get_attachment_image_src( $attach_id, $args['image_size'] );
			$image_url = $image_url[0];
		}

		$short_caption = wp_trim_words( $caption, 10 );
		$short_caption = preg_replace("/[^A-Za-z0-9?! ]/","", $short_caption);
		$caption       = wp_trim_words( $caption, $args['caption_words'], $more = null );

		$image_src = '<img src="' . $image_url . '" alt="' . $short_caption . '" title="' . $short_caption . '" />';
		$image_output  = $image_src;

		if ( $link_to ) {
			$image_output  = '<a href="' . $link_to . '" target="_blank"';

			if ( ! empty( $args['link_rel'] ) ) {
				$image_output .= ' rel="' . $args['link_rel'] . '"';
			}

			if ( ! empty( $args['link_class'] ) ) {
				$image_output .= ' class="' . $args['link_class'] . '"';
			}
			$image_output .= ' title="' . $short_caption . '">' . $image_src . '</a>';
		}		

		$output = '';
		
		// Template : Normal Slider
		if ( $template == 'slider' ) {
			
			$output .= "<li>";

				$output .= $image_output;

				if ( is_array( $args['description'] ) && count( $args['description'] ) >= 1 ) { 
					
					$output .= "<div class='jr-insta-datacontainer'>\n";
				
						if ( $time && in_array( 'time', $args['description'] ) ) {
							$time = human_time_diff( $time );
							$output .= "<span class='jr-insta-time'>{$time} ago</span>\n";
						}
						if ( in_array( 'username', $args['description'] ) && $username ) {
							$output .= "<span class='jr-insta-username'>by <a rel='nofollow' href='http://instagram.com/{$username}' target='_blank'>{$username}</a></span>\n";
						}

						if ( $caption != '' && in_array( 'caption', $args['description'] ) ) {
							$caption   = preg_replace( '/@([a-z0-9_]+)/i', '&nbsp;<a href="http://instagram.com/$1" rel="nofollow" target="_blank">@$1</a>&nbsp;', $caption );
							$caption = preg_replace( '/\#([a-zA-Z0-9_-]+)/i', '&nbsp;<a href="https://instagram.com/explore/tags/$1" rel="nofollow" target="_blank">$0</a>&nbsp;', $caption);						
							$output   .= "<span class='jr-insta-caption'>{$caption}</span>\n";
						}

					$output .= "</div>\n";
				}

			$output .= "</li>";
		
		// Template : Slider with text Overlay on mouse over
		} elseif ( $template == 'slider-overlay' ) {
			
			$output .= "<li>";
			
				$output .= $image_output;
			
				if ( is_array( $args['description'] ) && count( $args['description'] ) >= 1 ) {
					
					$output .= "<div class='jr-insta-wrap'>\n";

						$output .= "<div class='jr-insta-datacontainer'>\n";

							if ( $time && in_array( 'time', $args['description'] ) ) {
								$time = human_time_diff( $time );
								$output .= "<span class='jr-insta-time'>{$time} ago</span>\n";
							}
							
							if ( in_array( 'username', $args['description'] ) && $username ) {
								$output .= "<span class='jr-insta-username'>by <a rel='nofollow' target='_blank' href='http://instagram.com/{$username}'>{$username}</a></span>\n";
							}

							if ( $caption != '' && in_array( 'caption', $args['description'] ) ) {
								$caption = preg_replace( '/@([a-z0-9_]+)/i', '&nbsp;<a href="http://instagram.com/$1" rel="nofollow" target="_blank">@$1</a>&nbsp;', $caption );
								$caption = preg_replace( '/\#([a-zA-Z0-9_-]+)/i', '&nbsp;<a href="https://instagram.com/explore/tags/$1" rel="nofollow" target="_blank">$0</a>&nbsp;', $caption);								
								$output .= "<span class='jr-insta-caption'>{$caption}</span>\n";
							}

						$output .= "</div>\n";

					$output .= "</div>\n";
				}
			
			$output .= "</li>";
		
		// Template : Thumbnails no text	
		} elseif ( $template == 'thumbs' || $template == 'thumbs-no-border' ) {

			$output .= "<li>";
			$output .= $image_output;
			$output .= "</li>";

		} else {

			$output .= 'This template does not exist!';
		}

		return $output;
	}	

	
} // end of class JR_InstagramSlider