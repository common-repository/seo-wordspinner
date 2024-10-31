<?php
/*
Plugin Name: SEO WordSpinner
Plugin URI: http://www.seodenver.com/seo-wordspinner/
Description: SEO WordSpinner is an SEO plugin for WordPress that can be used to 'spin' the content in your blog. Spinning content allows you to create SEO-friendly variations of articles. The idea is to avoid content such as excerpts or page titles being seen as duplicate content when seen on various archive pages. Includes a SEO WordSpinner text widget.
Version: 2.0.4
Author: Katz Web Services, Inc.
Author URI: http://www.katzwebservices.com

--------------------------------------------------
 
Copyright 2010  Katz Web Services, Inc.  (email : info@katzwebservices.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
*/

global $seo_spin_options;

register_activation_hook( __FILE__, 'seo_wspin_defaults' );

function seo_wspin_defaults() {
	// We're going to merge the previous settings into one single setting array to reduce DB calls
	
	// Get previous settings
	$previous = array(
		'splitchar' => get_option('seo_wspin_splitchar'),
		'leftchar' => get_option('seo_wspin_leftchar'),
		'rightchar' => get_option('seo_wspin_rightchar'),
		'predictable' => get_option('seo_wspin_predictable'),
		'obstart' => get_option('seo_spin_obstart'),
		'excludetags' => get_option('seo_wspin_excludetags'),
		'meta' => get_option('seo_wspin_meta'),
		'cats' => get_option('seo_spin_cats'),
		'blogname' => get_option('seo_wspin_blogname')
	);
	
	// Set default plugin settings
	$defaults = array(
		'splitchar' =>"|", 
		'leftchar' => "{",
		'rightchar' => "}",
		'predictable' => true,
		'obstart' => true,
		'excludetags' => 'pre|code|script|style|object|param|applet',
		'meta' => false,
		'cats' => false,
		'blogname' => false,
		'levels' => 2
	);

	$options = shortcode_atts($previous,$defaults);
	
	update_option('seo_wspin', $options);
	
	if(get_option('seo_wspin')) {
		delete_option('seo_wspin_splitchar');
		delete_option('seo_wspin_leftchar');
		delete_option('seo_wspin_rightchar');
		delete_option('seo_wspin_predictable');
		delete_option('seo_spin_obstart');
		delete_option('seo_wspin_meta');
		delete_option('seo_spin_cats');
		delete_option('seo_wspin_blogname');
	}
	return $options;
}


function seo_spin_init() {
	global $post,$aioseop_options,$seo_spin_options,$seospinchars;
	
	if(empty($seo_spin_options)) { $seo_spin_options = get_option('seo_wspin'); }
	if(empty($seospinchars)) { $seospinchars = get_seo_spin_chars(); }
	
	if(!is_admin()) {
		seo_spin_configuration();
	}
}
add_action('plugins_loaded', 'seo_spin_init');

function seo_spin_configuration() {
	global $post,$aioseop_options,$seo_spin_options,$seospinchars;
	
	if($seo_spin_options['obstart']) {
		ob_start('get_seo_spin');
		// We have to do this for trackbacks & pingbacks, even with ob_start enabled
		seo_spin_content();
		add_filter('the_title','get_seo_spin');
	} else {
		seo_spin_content();
	}
	
	if($seo_spin_options['meta']) {
		add_action('init', 'seo_spin_meta');
	}
	
	if($seo_spin_options['cats']) {
		add_action('init', 'seo_spin_cats');
	}
	
	if($seo_spin_options['blogname']) {
		if(!is_admin()) {
			add_action('option_blogname', 'get_seo_spin');
		}
	}
}

function seo_spin_content() {
	add_filter('the_content','get_seo_spin');
	add_filter('the_exerpt','get_seo_spin');
}

function seo_spin_cats(){
	add_filter('single_cat_title', 'get_seo_spin');
	add_filter('wp_list_pages', 'get_seo_spin');
	add_filter('list_cats', 'get_seo_spin');
	add_filter('single_tag_title', 'get_seo_spin');
	add_filter('the_category', 'get_seo_spin');
	add_filter('category_description', 'get_seo_spin');
}

function seo_spin_meta() {
	global $post;
	add_filter('the_title','get_seo_spin');
	add_filter('wp_title','get_seo_spin');
	add_filter('aioseop_title_single','get_seo_spin');
	add_filter('aioseop_title_page','get_seo_spin');
	add_filter('aioseop_home_page_title','get_seo_spin');
	add_filter('aioseop_description','get_seo_spin');
	add_filter('aioseop_keywords','get_seo_spin');
	$post->post_title = get_seo_spin($post->post_title);
}

add_action( 'widgets_init', 'seo_wordspinner_load_widgets' );
/* Function that registers our widget. */
function seo_wordspinner_load_widgets() {
	register_widget('SEOWordSpinnerWidget');
}


// Hook for adding admin menus
add_action('admin_menu', 'seo_wspin_add_pages');
// action function for above hook
function seo_wspin_add_pages() {
	global $plugin_page;

	// Add a new submenu under Themes:
    add_options_page("SEO WordSpinner Config", "SEO WordSpinner", 'manage_options', __FILE__, "seo_wspin_admin");

	// Add the javascript
    if(strpos($plugin_page, 'wordspinner.php')) {
    	add_action( "admin_print_scripts", 'seo_wspin_javascript');
    }
}
function seo_wspin_javascript() {
	// Add the script that allows us to hide the checkboxes if we're spinning the whole page.
	wp_enqueue_script('seo-wordspinner-admin', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)). '/js/seo-wordspinner-admin.js', array('jquery'));
}

function seo_wspin_check_settings($options) {
	if(
		empty($options['splitchar']) ||
		empty($options['leftchar']) ||
		empty($options['rightchar'])
	) { return false; }
	return true;
}

function seo_wspin_admin() {
	global $plugin_page,$seo_spin_options;
	
	if(!seo_wspin_check_settings($seo_spin_options)) {
		$seo_spin_options = seo_wspin_defaults();
	}
	
	// Check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names 
    $field1 = 'split';
	$field2 = 'left';
	$field3 = 'right';
	$field4 = 'predictable';
	$field5 = 'meta';
	$field6 = 'blogname';
	$field7 = 'cats';
	$field8 = 'obstart';
	$field9 = 'excludetags';
	$field10 = 'levels';
	$hidden_field_name = 'hidden';
	
    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
		
		if(!seo_spin_check_chars($_POST[$field1], $_POST[$field2], $_POST[$field3])) {
			// Put an options updated message on the screen ?>
			<div class="error"><p><strong>Options Not Saved</strong></p><p>The start, end, and split characters must not contain each other's characters, and must be unique.</p></div><?php			
		} else {
		
			$update_options['splitchar'] = $_POST[ $field1 ];
			$update_options['leftchar'] = $_POST[ $field2 ];
			$update_options['rightchar'] = $_POST[ $field3 ];
			$update_options['predictable'] = $_POST[ $field4 ];
			$update_options['meta'] = $_POST[ $field5 ];
			$update_options['blogname'] = $_POST[ $field6 ];
			$update_options['cats'] = $_POST[ $field7 ];
			$update_options['obstart'] = $_POST[ $field8 ];
			$update_options['excludetags'] = $_POST[ $field9 ];
			$update_options['levels'] = $_POST[ $field10 ];
	        
	        update_option( 'seo_wspin', $update_options );
	
	       	$seo_spin_options = $update_options;
	       	
			// Put an options updated message on the screen ?>
			<div class="updated"><p><strong>Options Saved</strong></p></div><?php
		}
	}
?>
<div class="wrap">
	<h2>SEO WordSpinner</h2>
	<div class="postbox-container" style="width:65%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<div class="postbox">
			<div class="handlediv"><br /></div>
			<h3 class="hndle"><span>SEO WordSpinner Options</span></h3>
			<div class="inside">				
				<table class="form-table" width="100%">
					<tr>
						<th valign="top" scope="row" style="width:60%">
							<label for="<?php echo $field1; ?>" style="font-weight:bold;">Split Character:</label>
							<p><small>Separate spin variations with this character.</small></p>
						</th>
						<td valign="top" style="padding-left:5%">
							<input type="text" name="<?php echo $field1; ?>" id="<?php echo $field1; ?>" class="code" value="<?php echo $seo_spin_options['splitchar']; ?>" size="3" maxlength="1" style="font-size:125%;"/>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" style="width:60%">
							<label for="<?php echo $field2; ?>" style="font-weight:bold;">Start Character(s):</label>
							<p><small><strong>Tip:</strong> make sure you won't use this in your normal content (if you use <code>{</code> in day-to-day content, use <code>{{</code> instead).</small></p>
						</th>
						<td valign="top" style="padding-left:5%">
							<input type="text" name="<?php echo $field2; ?>" id="<?php echo $field2; ?>" class="code"  value="<?php echo $seo_spin_options['leftchar']; ?>" size="3" maxlength="2" style="font-size:125%;"/>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" style="width:60%">
							<label for="<?php echo $field3; ?>" style="font-weight:bold;">End Character(s):</label>
							<p><small><strong>Tip:</strong> make sure you won't use this in your normal content (<code>}</code> is used in day-to-day content, so instead use <code>}}</code>).</small></p>
						</th>
						<td valign="top" style="padding-left:5%">
							<input type="text" name="<?php echo $field3; ?>" id="<?php echo $field3; ?>" class="code"  value="<?php echo $seo_spin_options['rightchar']; ?>" size="3" maxlength="2" style="font-size:125%;"/>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" style="width:60%">
							<label for="<?php echo $field4; ?>" style="font-weight:bold;">Make Spins Predictable by Default:</label>
							<p><small>Make permutation the same on a per page basis; refreshing will not change text on the same URL. If you view a post in tag/category/search/single views, they will still show variations. If you refresh any one of those pages, however, the content will not change.</small></p>
							<p><small>If you want to alter predictability per post or page, add a Custom Field named <code>SEOSpinPredictable</code> and give it a value of "true" or "false".</small></p>
						</th>
						<td valign="top" style="padding-left:5%">
							<input type="checkbox" name="<?php echo $field4; ?>" id="<?php echo $field4; ?>" value="1" <?php if($seo_spin_options['predictable'] == true) { ?>checked="checked"<?php }?> />
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" style="width:60%">
							<label for="<?php echo $field9; ?>" style="font-weight:bold;">Exclude tags from spinning:</label>
							<p><small>Disable spinning inside specific tags. Separate with <code>|</code> (pipe bar; type <code>shift</code> + <code>backslash</code>).</small></p>
						</th>
						<td valign="top" style="padding-left:5%">
							<input type="text" name="<?php echo $field9; ?>" id="<?php echo $field9; ?>" class="widefat code" value="<?php echo esc_attr($seo_spin_options['excludetags']); ?>" /><span class="howto"><?php _e('It is recommended to have at least', 'seowordspinner'); ?> <code>pre|code|script|style</code></span>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" style="width:60%">
							<label for="<?php echo $field10; ?>" style="font-weight:bold;"><?php _e('Support # of Nested Spin Levels', 'seowordspinner'); ?>:</label>
							<p><small><?php _e('How many levels do you want to spin? (if you want to spin inside spins...inside spins)', 'seowordspinner'); ?></small></p>
						</th>
						<td valign="top" style="padding-left:5%">
							<select name="<?php echo $field10; ?>" id="<?php echo $field10; ?>">
								<option value="1"<?php selected(1, $seo_spin_options['levels']); ?>>1 - <?php _e('Basic', 'seowordspinner'); ?></option>
								<option value="2"<?php selected(2, $seo_spin_options['levels']); ?>>2 - <?php _e('(Recommended)', 'seowordspinner'); ?></option>
								<option value="3"<?php selected(3, $seo_spin_options['levels']); ?>>3 - <?php _e('Impressive', 'seowordspinner'); ?></option>
								<option value="4"<?php selected(4, $seo_spin_options['levels']); ?>>4 - <?php _e('Crazy', 'seowordspinner'); ?></option>
								<option value="5"<?php selected(5, $seo_spin_options['levels']); ?>>5 - <?php _e('Insane!', 'seowordspinner'); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" style="width:60%">
							<label for="<?php echo $field8; ?>" style="font-weight:bold;">Spin Entire Output:</label>
							<p><small>Enables spinning for the entire page, including all plugin-generated content. May affect site performance.</small></p>
						</th>
						<td valign="top" style="padding-left:5%">
							<input type="checkbox" name="<?php echo $field8; ?>" id="<?php echo $field8; ?>" value="1" <?php if($seo_spin_options['obstart'] == true) { ?>checked="checked"<?php }?> />
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" style="width:60%">
							<label for="<?php echo $field5; ?>" style="font-weight:bold;">Spin Meta Tags &amp; Post Titles:</label>
							<p><small>Enables spinning the title, description, and meta keywords. Works with All in One SEO Pack. Very helpful for avoiding duplicate content issues across archive pages.</small></p>
						</th>
						<td valign="top" style="padding-left:5%">
							<input type="checkbox" name="<?php echo $field5; ?>" id="<?php echo $field5; ?>" value="1" <?php if($seo_spin_options['meta'] == true) { ?>checked="checked"<?php }?> />
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" style="width:60%">
							<label for="<?php echo $field7; ?>" style="font-weight:bold;">Spin Category Titles:</label>
							<p><small>Enables spinning the title and description of categories. (<strong>Warning: Still in BETA, may not work properly!</strong>)</small></p>
						</th>
						<td valign="top" style="padding-left:5%">
							<input type="checkbox" name="<?php echo $field7; ?>" id="<?php echo $field7; ?>" value="1" <?php if($seo_spin_options['cats'] == true) { ?>checked="checked"<?php }?> />
						</td>
					</tr>
					<tr>
						<th valign="top" scope="row" style="width:60%">
							<label for="<?php echo $field6; ?>" style="font-weight:bold;">Spin Blog Name:</label>
							<p><small>A great way to shake up your title tags is to use spun blog names; this will work with any plugin or theme <code>&lt;title&gt;</code> configuration.</small></p>
						</th>
						<td valign="top" style="padding-left:5%">
							<input type="checkbox" name="<?php echo $field6; ?>" id="<?php echo $field6; ?>" value="1" <?php if($seo_spin_options['blogname'] == true) { ?>checked="checked"<?php }?> />
						</td>
					</tr>
				</table>
										<p class="submit" style="margin:0; padding-top:.5em; padding-left:10px;">
										<input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes') ?>" />
										</p>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="postbox-container" style="width:34%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<div class="postbox">
								<div class="handlediv"><br /></div>
								<h3 class="hndle"><span>Using the SEO WordSpinner Plugin</span></h3>
								<div class="inside" style="padding:10px; padding-top:0;">
								<h4>Using the following settings:</h4>
								<ul style="list-style:disc outside; margin-left:2em;">
									<li>Split Character: <strong style="font-size:125%; line-height:87%;">|</strong></li>
									<li>Start Character(s): <strong style="font-size:125%; line-height:87%;">{</strong></li>
									<li>End Character(s): <strong style="font-size:125%; line-height:87%;">}</strong></li>
								</ul>
								<h4>Escaping the start &amp; end characters</h4>
								<p>To use the the start or end characters in your content, you must "escape" the character with a forward slash (<code>\</code>).</p>
								<h4>An example content spin:</h4>
								<p><code style="font-size:110%;">This is &lt;em&gt;<strong>{</strong>an example<strong>|</strong>a demonstration<strong>|</strong>a demo<strong>|</strong>a test<strong>}</strong>&lt;/em&gt; of the powers of content spinning, and of escaping text: \{this|won't|be|spun\}</code></p>
								<h4>Could produce the following spun variations:</h4>
								<ul style="list-style:disc outside; margin-left:2em;">
									<li>This is an <em>example</em> of the powers of content spinning, and of escaping text: {this|won't|be|spun}</li>
									<li>This is an <em>demonstration</em> of the powers of content spinning, and of escaping text: {this|won't|be|spun}</li>
									<li>This is an <em>demo</em> of the powers of content spinning, and of escaping text: {this|won't|be|spun}</li>
									<li>This is an <em>test</em> of the powers of content spinning, and of escaping text: {this|won't|be|spun}</li>
								</ul>
								<p>Because the start and end characters were preceded by a <code>\</code>, they were not spun.</p>
							</div>
						</div>
					</div>
				</div>

<?php
 
} //wspin_admin

function get_seo_spin_predictable() {
	global $post,$seo_spin_options;

	$seedPageName = $seo_spin_options['predictable'];
	
	if(!empty($post->ID)) {
		// On a per-post basis, you can turn on and off predictability by adding a SEOSpinPredictable custom field
		$post_custom = strtolower(get_post_meta($post->ID, 'SEOSpinPredictable', true));
		if(!empty($post_custom)) {
			if(!$post_custom || $post_custom == 'false' || $post_custom == '0' || $post_custom == 'no' || $post_custom == 'off') {
				$seedPageName = false;
			} elseif($post_custom == 'true' || $post_custom == '1' || $post_custom == 'yes' || $post_custom == 'on') {
				$seedPageName = true;
			}
		}
	}
	return apply_filters('get_seo_spin_predictable', apply_filters('get_seo_spin_predictable_'.get_the_id(), $seedPageName));
}

function get_seo_spin_enabled() {
	global $post,$seo_spin_options,$wp_query;
	
	$enabled = true;
	if(is_singular() && isset($wp_query->post) && isset($wp_query->post->ID) && $wp_query->post->ID != 0) {
		$post_custom = strtolower(get_post_meta($post->ID, 'SEOSpinEnabled', true));
		if(!empty($post_custom)) {
			if($post_custom == 'false' || $post_custom == '0' || $post_custom == 'no' || $post_custom == 'off') {
				$enabled = false;
			} elseif(!$post_custom || $post_custom == 'true' || $post_custom == '1' || $post_custom == 'yes' || $post_custom == 'on') {
				$enabled = true;
			}
		}
	}
	if(!empty($wp_query->post->ID)) {
		return apply_filters('get_seo_spin_enabled', apply_filters('get_seo_spin_enabled_'.$wp_query->post->ID, $enabled));
	} 
	return apply_filters('get_seo_spin_enabled', $enabled);
}

//Escape a string to be used as a regular expression pattern
//Ex: escape_string_for_regex('http://www.example.com/s?q=php.net+docs')
// returns http:\/\/www\.example\.com\/s\?q=php\.net\+docs
if(!function_exists('escape_string_for_regex')) {
	function escape_string_for_regex($str)
	{
	        //All regex special chars (according to arkani at iol dot pt below):
	        // \ ^ . $ | ( ) [ ]
	        // * + ? { } ,
	        
	        $patterns = array('/\//', '/\^/', '/\./', '/\$/', '/\|/',
	 '/\(/', '/\)/', '/\[/', '/\]/', '/\*/', '/\+/', 
	'/\?/', '/\{/', '/\}/', '/\,/', '/\ /');
	        $replace = array('\/', '\^', '\.', '\$', '\|', '\(', '\)', 
	'\[', '\]', '\*', '\+', '\?', '\{', '\}', '\,', '\ ');
	        
	        return preg_replace($patterns,$replace, $str);
	}
}

if(!function_exists('seo_spin_html_entities')) {
	function seo_spin_html_entities($matches) {
		return seo_spin_replace_excluded($matches[0], 'add', false);
	}
}

if(!function_exists('seo_spin_replace_excluded')) {
	function seo_spin_replace_excluded($s, $type = 'add', $escaped = true) {
		global $seospinchars;
		if(empty($seospinchars)) { $seospinchars = get_seo_spin_chars(); }
		$lb = (preg_match('/\[/ism',$seospinchars[1]['l'])) ? '{' : '[';
		$rb = (preg_match('/\]/ism',$seospinchars[1]['r'])) ? '}' : ']';
		if($type == 'add') {
			if($escaped) {
				$s = preg_replace('/'.$seospinchars[1]['l'].'+/s', $lb.'spl'.$rb, $s);
				$s = preg_replace('/'.$seospinchars[1]['r'].'+/s', $lb.'spr'.$rb, $s);
			} else {
				$s = preg_replace('/'.preg_quote($seospinchars[0]['l']).'+/s', $lb.'spl'.$rb, $s);
				$s = str_replace('/'.preg_quote($seospinchars[0]['r']).'+/s', $lb.'spr'.$rb, $s);
			}
		}	else {
			$s = preg_replace('/'.preg_quote($lb.'spl'.$rb).'/s', $seospinchars[0]['l'], $s);
			$s = preg_replace('/'.preg_quote($lb.'spr'.$rb).'/s', $seospinchars[0]['r'], $s);
		}
		return $s;
	}
}

if(!function_exists('seo_spin_replace_brackets')) {
	function seo_spin_replace_brackets($s, $type = 'add', $escaped = true) {
		global $seospinchars;
		if(empty($seospinchars)) { $seospinchars = get_seo_spin_chars(); }
		if($type == 'add') {
			if($escaped) {
				$s = preg_replace('/('.$seospinchars[1]['l'].')/s', '[seowordspin]', $s);
				$s = preg_replace('/('.$seospinchars[1]['r'].')/s', '[/seowordspin]', $s);
			} else {
				$s = preg_replace('/('.preg_quote($seospinchars[0]['l']).')/s', '[seowordspin]', $s);
				$s = str_replace('/('.preg_quote($seospinchars[0]['r']).')/s', '[/seowordspin]', $s);
			}
		}	else {
			$s = preg_replace('/(\[seowordspin\])/s', $seospinchars[0]['l'], $s);
			$s = preg_replace('/(\[\/seowordspin\])/s', $seospinchars[0]['r'], $s);
		}
		return $s;
	}
}

function seo_spin_check_chars($v1, $v2, $v3) {
	if($v1 == $v2 || $v1 == $v3 || $v2 == $v3) { return false; }
	return true;
}

function get_seo_spin_chars() {
	global $seo_spin_options;
	$l = $seo_spin_options['leftchar'];
	$r = $seo_spin_options['rightchar'];	
	$split = $seo_spin_options['splitchar'];
	$array = array(array('l'=>$l, 'r' => $r, 'split' => $split),array('l'=>preg_quote($l), 'r' => preg_quote($r), 'split' => preg_quote($split)));
	return $array;
}


## WOrking, but not nested: 
function do_seo_spin($atts, $s = null) {
	global $seospinchars, $seo_spin_options;
	$c = $seospinchars;

	if(preg_match('/\[seowordspin\](.*)?(?:\[\/seowordspin\])?/ism', $s, $m)) {
		$s = do_seo_spin(array(), $m[1]);
		$s = '[seowordspin]'.$s;
	}

	## Begin magic
		// Break up the string into an array of options using the split character
		$parts = explode($c[0]['split'], $s);
		
		// If there's no split character, we're done here.
		if(empty($parts)) { return $s; }

		// Replace the spin string with the spun choice
		$s = $parts[mt_rand(0, count($parts)-1)];
	## End magic
	
	// Do it again to see if there are more spins.
	return $s;
}


add_shortcode('seowordspin', 'do_seo_spin');

if(!function_exists('get_seo_spin')) {
	function get_seo_spin($s){
		global $post,$seospinchars,$seo_spin_options,$wp_query;
		
		if(!get_seo_spin_enabled()) { return $s; }
				
		if(empty($seospinchars)) { $seospinchars = $c = get_seo_spin_chars(); } else { $c = $seospinchars; }
		if(empty($seo_spin_options)) { $seo_spin_options = get_option('seo_wspin'); }
		
		// The start, end, and split characters mustn't be the same
		if(!seo_spin_check_chars($c[0]['l'],$c[0]['r'],$c[0]['split'])) { return $s; }
	
		// First we do_shortcode, incase the chars are [ or ]
		$s = do_shortcode($s);
		
		// If predictable, always show the same combination for the same URL
		if(get_seo_spin_predictable()) { mt_srand(crc32($_SERVER['SERVER_NAME'].'/'.$_SERVER['REQUEST_URI'])); } else { mt_srand(); }
		
		// Then we process code blocks
		if(!empty($seo_spin_options['excludetags'])) {
			$seo_spin_options['excludetags'] = apply_filters('seo_wspin_excludetags', $seo_spin_options['excludetags']);
			$pattern = '/<(?:(?:'.$seo_spin_options['excludetags'].').*?)>(.*?)<\/(?:'.$seo_spin_options['excludetags'].')>/ism';
			$s = preg_replace_callback($pattern,'seo_spin_html_entities', $s);
		}
		// What remains is the text we want to use.
		// So we replace escaped characters (\{) with a kind of placeholder
		// That, when finished, we swap back out.
		$s = seo_spin_replace_brackets($s);

		// I can't figure it out with a recursive function...
		// so for now, I'm calling it thrice for three levels of nesting.
		if(!isset($seo_spin_options['levels'])) { $seo_spin_options['levels'] = 2; } $i = 0;
		while($seo_spin_options['levels'] > $i) {
			$s = do_shortcode($s);
			$i++;
		}
		
		$s = seo_spin_replace_brackets($s, 'remove');
		$s = seo_spin_replace_excluded($s, 'remove');
		
		mt_srand(); // Make random random again
		
		return $s;
	}
}

if(!function_exists('seo_spin')) {
	function seo_spin($string)
	{
		echo get_seo_spin($string);
	}
}




class SEOWordSpinnerWidget extends WP_Widget {

	function SEOWordSpinnerWidget() {
		$widget_ops = array('classname' => 'widget_text_spinner', 'description' => __('Spinnable text(s), with opening and closing chars'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('seotextspinner', __('Spin Text'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		$title = apply_filters('widget_title', get_seo_spin($title));
		$text = apply_filters( 'widget_text', get_seo_spin($instance['text']) );
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
			<div class="textwidget"><?php echo $instance['filter'] ? wpautop($text) : $text; ?></div>
		<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = wp_filter_post_kses( $new_instance['text'] );
		$instance['filter'] = isset($new_instance['filter']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
		$text = format_to_edit($instance['text']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

		<p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked($instance['filter']); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs.'); ?></label></p>
<?php
	}
	
}


?>