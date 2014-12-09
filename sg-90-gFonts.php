<?php
/**
 * Plugin Name: SG-90 - Google Fonts Extension
 * Plugin URI: http://arcctrl.com/plugins/sg-90
 * Description: Add a new section to SG-90 for Google Fonts
 * Version: 0.1
 * Author: ARC(CTRL)
 * Author URI: http://www.arcctrl.com
 * License: GPL2
 */
 

if( interface_exists( 'StyleGuideSection' ) ) {

	global $SG_Factory;
	
	define( 'SG90_GFONTS_PLUGINPATH', plugin_dir_path( __FILE__ ) );
	define( 'SG90_GFONTS_PLUGINURL', plugins_url( '', __FILE__ ) );
	
	class sg_google_fonts implements StyleGuideSection {
	
		function __construct() {
			$this->sg_title = 'Google Fonts';
			$this->sg_admin_title = 'Google Fonts';
			add_action( 'admin_enqueue_scripts', array( $this, 'adminScripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'viewScripts' ) );
		}
		
		function adminScripts() {
			
			wp_enqueue_script( 'GFontsJS', SG90_GFONTS_PLUGINURL.'/js/admin.js', array( 'jquery' ), '1.0', false );
			if( get_option( '_sg_gFont_apiKey' ) ) {
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-autocomplete' );
				wp_enqueue_style ( 'jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css', '', null, 'all' );
				wp_localize_script( 'GFontsJS', 'sgGfonts', array( 'gApi' => get_option( '_sg_gFont_apiKey' ) ) );
			}
		}
		
		function viewScripts() {
			wp_enqueue_script( 'GFontsJS_view', SG90_GFONTS_PLUGINURL.'/js/view.js', array( 'jquery' ), '1.0', false );
			if( get_option( '_sg_gFont_apiKey' ) ) {
				wp_localize_script( 'GFontsJS_view', 'sgGfonts', array( 'gApi' => get_option( '_sg_gFont_apiKey' ) ) );
			}
			wp_enqueue_style( 'sg_GFonts_css', SG90_GFONTS_PLUGINURL.'/css/_sg_gfonts.css', '', '1.0', 'all' );
		}
		
		function admin( $post ){
			if( get_option( '_sg_gFont_apiKey' ) ) {
				$this->sg_admin_title = str_replace( '_', ' ', $this->sg_admin_title );
				echo '<h2>'.$this->sg_admin_title.'</h2>';
				$this->sg_admin_title = str_replace( ' ', '_', $this->sg_admin_title );
				if( get_post_meta( $post->ID, '_sg_'.$this->sg_admin_title.'_gFont', true ) ) {
					echo '<table class="fontWrapper" id="_sg'.$this->sg_admin_title.'">';
						$fonts = get_post_meta( $post->ID, '_sg_'.$this->sg_admin_title.'_gFont', true );
						echo $this->fonts_template( $fonts );
					echo '</table>';
				} else {
					echo '<table class="fontWrapper" id="_sg'.$this->sg_admin_title.'">';
						echo '<tr>';
							echo '<td><a class="deleteFont" href="#" style="color:red;text-decoration:none;">x</a></td>';
							echo '<td>';
								echo '<select name="_sg_'.$this->sg_admin_title.'_gFont[tag][]">';
									echo '<option value="h1">H1</option>';
									echo '<option value="h2">H2</option>';
									echo '<option value="h3">H3</option>';
									echo '<option value="h4">H4</option>';
									echo '<option value="p">p</option>';
									echo '<option value="strong">strong</option>';
									echo '<option value="em">em / italics</option>';
								echo '</select>';
							echo '</td><td>';
								echo '<input data-title="'.$this->sg_admin_title.'" type="text" class="sg_Gfont" name="_sg_'.$this->sg_admin_title.'_gFont[font][]" placeholder="Font Name" />';
							echo '</td>';
						echo '</tr>';
					echo '</table>';
				}
				echo '<br/><input type="submit" class="button button-primary addFont" value="Add Font">';
			} else {
				echo '<p>No API Key Set, please set the API key in the settings page.<br/><a target="_blank" href="'.admin_url('admin.php?page=sg-google-fonts').'">Set your API key</a></p>';
			}
		}
		
		function view( $post_id ){
			$fonts = get_post_meta( $post_id, '_sg_'.$this->sg_admin_title.'_gFont', true );
			$string = '//fonts.googleapis.com/css?family=';
			foreach( $fonts['font'] as $font ) {
				$font = str_replace( ' ', '+', $font );
				$string .= $font.'|';
			}
			$template = '<link type="text/css" rel="stylesheet" href="'.$string.'" />';
			
			$i = 0;
			foreach( $fonts['font'] as $font ) {
				$template .= '<div class="fontWrapper">';
					$template .= '<span class="title">'.$fonts['tag'][$i].'</span>';
					$template .= '<'.$fonts['tag'][$i].' style="font-family:'.$font.';';
						if( $fonts['variant'][$i] !== 'regular' ) { $template .= 'font-weight:'.str_replace( 'italic', '', $fonts['variant'][$i] ).';'; }
						if( strpos( $fonts['variant'][$i], 'italic' ) !== false ) { $template .= 'font-style: italic'; }
					$template .= '">'.$font.'</'.$fonts['tag'][$i].'>';
				$template .= '</div>';
				$i++;
			}
			
			return $template;
			
		}
		
		function fonts_template( $fonts ) {
			$i = 0;
			$return = '';
			foreach( $fonts['font'] as $font ){
				$return .= '<tr>';
					$return .= '<td><a class="deleteFont" href="#" style="color:red;text-decoration:none;">x</a></td>';
					$return .= '<td>';
						$return .= '<select name="_sg_'.$this->sg_admin_title.'_gFont[tag][]">';
							$return .= '<option value="h1"';
								if( $fonts['tag'][$i] === 'h1' ) { $return .= 'selected="selected"'; }
							$return .= '>H1</option>';
							$return .= '<option value="h2"';
								if( $fonts['tag'][$i] === 'h2' ) { $return .= 'selected="selected"'; }
							$return .= '>H2</option>';
							$return .= '<option value="h3"';
								if( $fonts['tag'][$i] === 'h3' ) { $return .= 'selected="selected"'; }
							$return .= '>H3</option>';
							$return .= '<option value="h4"';
								if( $fonts['tag'][$i] === 'h4' ) { $return .= 'selected="selected"'; }
							$return .= '>H4</option>';
							$return .= '<option value="p"';
								if( $fonts['tag'][$i] === 'p' ) { $return .= 'selected="selected"'; }
							$return .= '>p</option>';
							$return .= '<option value="strong"';
								if( $fonts['tag'][$i] === 'strong' ) { $return .= 'selected="selected"'; }
							$return .= '>strong</option>';
							$return .= '<option value="em"';
								if( $fonts['tag'][$i] === 'em' ) { $return .= 'selected="selected"'; }
							$return .= '>em / italics</option>';
						$return .= '</select>';
					$return .= '</td><td>';
						$return .= '<input data-title="'.$this->sg_admin_title.'" type="text" class="sg_Gfont" name="_sg_'.$this->sg_admin_title.'_gFont[font][]" placeholder="Font Name" value="'.$font.'" />';
					$return .= '</td>';
					$return .= '<td class="variant"><input name="_sg_'.$this->sg_admin_title.'_gFont[variant][]" type="text" value="'.$fonts['variant'][$i].'" readonly /></td>';
				$return .= '</tr>';
				$i++;
			}
			return $return;
		}
		
	}
	
	$SG_Factory->register( 'sg_google_fonts' );

	class sg90GFontsSettings {
		
		function __construct() {
			add_action( 'admin_menu', array( $this, 'adminMenu' ) );
		}
		
		function adminMenu() {
			add_submenu_page( 'style-guide-main', 'SG-90 Google Fonts', 'SG-90 Google Fonts', 'delete_pages', 'sg-google-fonts', array( $this, 'sg_GFont_settings' ) );
		}
		
		function sg_GFont_settings() {
			if( isset( $_POST['_sg_gFont_apiKey'] ) ) {
				update_option( '_sg_gFont_apiKey', $_POST['_sg_gFont_apiKey'] );
			}
			$apiKey = get_option( '_sg_gFont_apiKey', '' );
			echo '<div id="poststuff">';
				echo '<div class="postbox-container">';
					echo '<div class="postbox" style="padding:20px">';
						echo '<h2>SG-90 Google Fonts Extension</h2>';
						echo '<form action="'.admin_url('admin.php?page=sg-google-fonts').'" method="post">';
							echo '<label for="_sg_gFont_apiKey">Google Api Key</label><br/>';
							echo '<input name="_sg_gFont_apiKey" value="' . $apiKey . '" placeholder="Google Fonts API Key" size="50" />';
							echo '<p>';
								echo 'If you want to load all Google Fonts put in your API key. <br/>Your API key can be found in your <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a>.';
							echo '<p>';
							echo '<input type="submit" value="Save Key" class="button button-primary" />';
						echo '</form>';
						echo '<h2>To Create a Google Fonts API key</h2>';
						echo '<ol>';
							echo '<li>Go to the <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a>.</li>';
							echo '<li>Select or Create a Project</li>';
							echo '<li>In the sidebar on the left, select APIs & auth. In the list of APIs, make sure the status is ON for the Google Fonts Developer API.</li>';
							echo '<li>In the sidebar on the left, select Credentials.</li>';
							echo '<li>Create an Public API key (Browser Key) and make sure to allow: '.get_bloginfo('wpurl').'</li>';
							echo '<li>Copy Key and paste in form above</li>';
						echo '</ol>';
					echo '</div>';
				echo '</div>';
			echo '</div>';
			
		}
		
	}
	
	new sg90GFontsSettings();


} else {
	add_action( 'admin_notices', 'sg90_undefined' );
	function sg90_undefined() {
	?>
	    <div class="error">
	        <p><?php _e( 'SG-90 undefined SG-90 Google Fonts inactive', 'my-text-domain' ); ?></p>
	    </div>
	<?php
	}
}