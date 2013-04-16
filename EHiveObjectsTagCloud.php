<?php
/*
	Plugin Name: eHive Objects Tag Cloud
	Plugin URI: http://developers.ehive.com/wordpress-plugins/
	Description: Displays a tag cloud for eHive objects. The <a href="http://developers.ehive.com/wordpress-plugins#ehiveaccess" target="_blank">eHiveAccess plugin</a> must be installed.
	Author: Vernon Systems limited
	Version: 2.1.0
	Author URI: http://vernonsystems.com
	License: GPL2+
*/
/*
	Copyright (C) 2012 Vernon Systems Limited

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
if (in_array('eHiveAccess/EHiveAccess.php', (array) get_option('active_plugins', array()))) {

    class EHiveObjectsTagCloud {
    	
        function __construct() {
        	
        	add_action("admin_init", array(&$this, "ehive_objects_tag_cloud_admin_init"));        	 
        	add_action("admin_menu", array(&$this, "ehive_objects_tag_cloud_admin_menu"));
        	         	
        	add_action( 'wp_print_styles', array(&$this,'enqueue_styles'));
        	
            add_shortcode('ehive_objects_tag_cloud', array(&$this, 'ehive_objects_tag_cloud_shortcode'));
        }

        function ehive_objects_tag_cloud_admin_init() {
        
        	register_setting('ehive_objects_tag_cloud_options', 'ehive_objects_tag_cloud_options', array(&$this, 'plugin_options_validate') );
        	 
        	add_settings_section('comment_section', '', array(&$this, 'comment_section_fn'), __FILE__);
        
        	add_settings_section('object_tag_cloud_section', 'Objects Tag Cloud', array(&$this, 'object_tag_cloud_section_fn'), __FILE__);
        	 
        	add_settings_section('css_section', 'CSS - stylesheet', array(&$this, 'css_section_fn'), __FILE__);
        }
        
        /*
         * Validation
         */
        function plugin_options_validate($input) {
        	add_settings_error('ehive_objects_tag_cloud_options', 'updated', 'eHive Objects Tag Cloud settings saved.', 'updated');
        	return $input;
        }
        
        /*
         * Plugin options content
         */
        function comment_section_fn() {
        	echo "<p><em>An overview of the plugin and shortcode documentation is available in the help.</em></p>";
        }
        
        function object_tag_cloud_section_fn() {
        	add_settings_field('limit', 'Number of tags', array(&$this, 'limit_fn'), __FILE__, 'object_tag_cloud_section');
        }
        
        function css_section_fn() {
        	add_settings_field('css_class', 'Custom class selector', array(&$this, 'css_class_fn'), __FILE__, 'css_section');
        	add_settings_field('plugin_css_enabled', 'Enable plugin stylesheet', array(&$this, 'plugin_css_enabled_fn'), __FILE__, 'css_section');
        }
        
        
        /*********************
         * TAG CLOUD SECTION *
         *********************/
        function limit_fn() {
        	$options = get_option('ehive_objects_tag_cloud_options');
        	echo "<input id='limit' name='ehive_objects_tag_cloud_options[limit]' class='small-text' type='number' value='{$options['limit']}' />";
        }
        
        /***************
         * CSS SECTION *
         ***************/
		function plugin_css_enabled_fn() {
			$options = get_option('ehive_objects_tag_cloud_options');
			if($options['plugin_css_enabled']) {
				$checked = ' checked="checked" ';
			}
        	echo "<input ".$checked." id='plugin_css_enabled' name='ehive_objects_tag_cloud_options[plugin_css_enabled]' type='checkbox' />";
        }
        
        function css_class_fn() {
        	$options = get_option('ehive_objects_tag_cloud_options');
        	echo "<input id='css_class' name='ehive_objects_tag_cloud_options[css_class]' class='regular-text' type='text' value='{$options['css_class']}' />";
        	echo '<p>Adds a class name to the ehive-tag-cloud div.';
        }
        
        /*
         * Admin menu setup
         */
        function ehive_objects_tag_cloud_admin_menu() {
        
        	global $ehive_objects_tag_cloud_options_page;
        
        	$ehive_objects_tag_cloud_options_page = add_submenu_page('ehive_access', 'eHive Objects Tag Cloud', 'Objects Tag Cloud', 'manage_options', 'ehive_objects_tag_cloud', array(&$this, 'ehive_objects_tag_cloud_options_page'));
        
        	add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'ehive_objects_tag_cloud_plugin_action_links'), 10, 2);
        	 
        	add_action("admin_print_styles-" . $ehive_objects_tag_cloud_options_page, array(&$this, "ehive_objects_tag_cloud_admin_enqueue_styles") );
        
        	add_action("load-$ehive_objects_tag_cloud_options_page",array(&$this, "ehive_objects_tag_cloud_options_help"));
        }
        
        /*
         * Admin page link
         */
		function ehive_objects_tag_cloud_plugin_action_links($links, $file) {
			$settings_link = '<a href="admin.php?page=ehive_objects_tag_cloud">' . __('Settings') . '</a>';
			array_unshift($links, $settings_link);
			return $links;
		}
                
        /*
         * Add amin stylesheet
         */
        function ehive_objects_tag_cloud_admin_enqueue_styles() {
        	wp_enqueue_style('eHiveAdminCSS');
        }
                 
        /*
         * Plugin options help
         */
        function ehive_objects_tag_cloud_options_help() {
        	global $ehive_objects_tag_cloud_options_page;
        
        	$screen = get_current_screen();
        	if ($screen->id != $ehive_objects_tag_cloud_options_page) {
        		return;
        	}
        
        	$screen->add_help_tab( array('id'		=> 'ehive-objects-tag-cloud-overview',
                                         'title'	=> 'Overview',
                                         'content'	=> "<p>This plugin displays a tag cloud of eHive object tags.</p>",
        						  ));
        	
        	$htmlShortcode = "<p><strong>Shortcode</strong> [ehive_objects_tag_cloud]</p>";
        	$htmlShortcode.= "<p><strong>Attributes:</strong></p>";
        	$htmlShortcode.= "<ul>";
        	 
        	$htmlShortcode.= '<li><strong>css_class</strong> - Adds a custom class selector to the plugin markup.</li>';
        	$htmlShortcode.= '<li><strong>limit</strong> - The maximum number of tags to display. Defaults to the options setting Number of tags.</li>';
        	 
        	$htmlShortcode.= '<p><strong>Examples:</strong></p>';
        	$htmlShortcode.= '<p>[ehive_objects_tag_cloud]<br/>Shortcode with no attributes. Attributes default to the options settings.</p>';
        	$htmlShortcode.= '<p>[ehive_objects_tag_cloud  limit="75"]<br/>A tag cloud that displays a maximum of 75 tags.</p>';
        	$htmlShortcode.= '<p>[ehive_objects_tag_cloud  css_class="my-class" limit="50"]<br/>A tag cloud with a css selector "my-class" that displays a maximum of 50 tags.</p>';
        	 
        	$htmlShortcode.= "</ul>";
        	
        	$screen->add_help_tab( array('id'		=> 'ehive-objects-tag-cloud-shortcode',
        								 'title'	=> 'Shortcode',
        								 'content'	=> $htmlShortcode
        						 ));    
        	
        	$screen->set_help_sidebar('<p><strong>For more information:</strong></p><p><a href="http://developers.ehive.com/wordpress-plugins#ehiveobjectstagcloud" target="_blank">Documentation for eHive plugins</a></p>'); 
        }
                
        /*
         * Options page setup
         */
		function ehive_objects_tag_cloud_options_page() {
		?>
			<div class="wrap">
				<div class="icon32" id="icon-options-ehive"><br></div>
				<h2>eHive Objects Tag Cloud Settings</h2>   
				<?php settings_errors();?>      		
				<form action="options.php" method="post">
					<?php settings_fields('ehive_objects_tag_cloud_options'); ?>
					<?php do_settings_sections(__FILE__); ?>
					<p class="submit">
						<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
					</p>
				</form>
			</div>
		<?php
		}
		                
		/*
		 * Add plugin stylesheet
		 */
		public function enqueue_styles() {
		
			$options = get_option('ehive_objects_tag_cloud_options');
				
			if ($options[plugin_css_enabled] == 'on') {
				wp_register_style($handle = 'eHiveObjectsTagCloudCSS', $src = plugins_url('eHiveObjectsTagCloud.css', '/eHiveObjectsTagCloud/css/eHiveObjectsTagCloud.css'), $deps = array(), $ver = '0.0.1', $media = 'all');
				wp_enqueue_style( 'eHiveObjectsTagCloudCSS');
			}
		}
		               
		/*
		 * Add plugin scripts
		 */
        public function ehive_objects_tag_cloud_shortcode($atts) {
        	global $eHiveAccess, $eHiveSearch;
        	
        	$options = get_option('ehive_objects_tag_cloud_options');

        	$siteType = $eHiveAccess->getSiteType();
        	$accountId = $eHiveAccess->getAccountId();
        	$communityId = $eHiveAccess->getCommunityId();
        	 
        	extract(shortcode_atts(array('limit' => array_key_exists('limit', $options) ? $options['limit'] : '50',
        								 'css_class' => array_key_exists('css_class', $options) ? $options['css_class'] : ''), $atts));
        	 
        	$searchOptions = $eHiveSearch->getSearchOptions();

            $eHiveApi = $eHiveAccess->eHiveApi();
            try {
                     
	            switch($siteType) {
	            	case 'Account':
	            		$tagCloud = $eHiveApi->getTagCloudInAccount($accountId, $limit);
	            		break;
	            	case 'Community':
	            		$tagCloud = $eHiveApi->getTagCloudInCommunity($communityId, $limit);
	            		break;
	            	default:
	            		$tagCloud = $eHiveApi->getTagCloudInEHive($limit);
	            		break;
	            }
        	
            } catch (Exception $exception) {
            	error_log('EHive tag cloud plugin returned and error while accessing the eHive API: ' . $exception->getMessage());
            	$eHiveApiErrorMessage = " ";
            	if ($eHiveAccess->getIsErrorNotificationEnabled()) {
            		$eHiveApiErrorMessage = $eHiveAccess->getErrorMessage();
            	}
            }
            
        	$templateToFind = 'eHiveObjectsTagCloud.php';
        	
        	$template = locate_template(array($templateToFind));
        	if ('' == $template) {
        		$template = "templates/$templateToFind";
        	}
        	
        	ob_start();
        	require($template);
        	return apply_filters('ehive_objects_tag_cloud', ob_get_clean());
        }
        
        /*
         * On plugin activate
         */
        public function activate() {

        	$arr = array("plugin_css_enabled"=>"on",
						 "css_class"=>"",
        				 "limit"=>"50");
        	
        	update_option('ehive_objects_tag_cloud_options', $arr);        	 
        }

        /*
         * On plugin deactivate
         */
        public function deactivate() {
        	delete_option('ehive_objects_tag_cloud_options');
        }
    }

    $eHiveObjectsTagCloud = new EHiveObjectsTagCloud();
  
    add_action('activate_eHiveObjectsTagCloud/EHiveObjectsTagCloud.php', array(&$eHiveObjectsTagCloud, 'activate'));
	add_action('deactivate_eHiveObjectsTagCloud/EHiveObjectsTagCloud.php', array(&$eHiveObjectsTagCloud, 'deactivate'));	
}?>