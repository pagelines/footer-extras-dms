<?php
/*
Plugin Name: Footer Extras DMS
Plugin URI: http://dms.elsue.com/footer-extras/
Description: Puts a dynamic copyright notice, Business NAP formatted with rich snippets for local seo ranking and an extra line of text
Version: 1.0
Author: Ellen Moore
Author URI: http://dms.elsue.com
Demo: http://footer-extras-dms.ellenjanemoore.com/footer-extras/
PageLines: true
Tags: extension
*/



add_action('footer-extras-dms_setup' , 'footer_extras_dms_check');

	function footer_extras_dms_check() {
		if( !function_exists('pl_setting') )
		return;
	}
	

class Footer_Extras_DMS {
	
	function __construct() {
		
		$this->base_url = sprintf( '%s/%s', WP_PLUGIN_URL,  basename(dirname( __FILE__ )));
		
		$this->icon = $this->base_url . '/icon.png';
		
		$this->base_dir = sprintf( '%s/%s', WP_PLUGIN_DIR,  basename(dirname( __FILE__ )));
		
		$this->base_file = sprintf( '%s/%s/%s', WP_PLUGIN_DIR,  basename(dirname( __FILE__ )), basename( __FILE__ ));
		$this->plugin_hooks();
		
	}

	


	function plugin_hooks(){
	
		// Always run 
		
		add_filter('pl_settings_array', array(&$this, 'options'));
		add_filter( 'pagelines_lesscode', array( &$this, 'get_less' ), 10, 1 );
		add_action( 'pagelines_after_footer', array( &$this, 'footer_extras_dms_template' ));
		add_filter ( 'pagelines_settings_whitelist', 'footer_extras_dms_whitelist' );
		
	
	}


	function footer_extras_dms_whitelist($whitelist) {
		// Included fields that may contain link or special characters
		$footer_extras_dms_text = array('footer_extras_dms_copyright_text', 'nap_name', 'nap_street', 'nap_city' , 'footer_extras_dms_additional_text');
		return array_merge( $whitelist, $footer_extras_dms_text );
		}

	

	function get_less( $less ){
	
		$less .= pl_file_get_contents( $this->base_dir.'/style.less' );

		return $less;
		
	}


	function footer_extras_dms_template(){
		global $wpdb;
		global $post;
		global $post_id;

		$footer_align = (pl_setting('footer_extras_dms_align')) ? pl_setting('footer_extras_dms_align') : 'left';
		$nap_name = (pl_setting('nap_name')) ? pl_setting('nap_name') : '';
		$nap_street = (pl_setting('nap_street')) ? pl_setting('nap_street') : '';
		$nap_city = (pl_setting('nap_city')) ? pl_setting('nap_city') : '';
		$nap_state = (pl_setting('nap_state')) ? pl_setting('nap_state') : '';
		$nap_zip = (pl_setting('nap_zip')) ? pl_setting('nap_zip') : '';
		$nap_phone = (pl_setting('nap_phone')) ? pl_setting('nap_phone') : '';
		$nap_latitude = (pl_setting('nap_latitude')) ? pl_setting('nap_latitude') : '';
		$nap_longitude = (pl_setting('nap_longitude')) ? pl_setting('nap_longitude') : '';
		$footer_extras_dms_additional = (pl_setting('footer_extras_dms_additional_text')) ? pl_setting('footer_extras_dms_additional_text') : '';
		$copyright_text = (pl_setting('footer_extras_dms_copyright_text')) ? pl_setting('footer_extras_dms_copyright_text') : '';
		$copyright_year = (pl_setting('footer_extras_dms_copyright_year')) ? pl_setting('footer_extras_dms_copyright_year') : null;
		
		$copyright_dates = $wpdb->get_results("
			SELECT
			YEAR(min(post_date_gmt)) AS firstdate,
			YEAR(max(post_date_gmt)) AS lastdate
			FROM
			$wpdb->posts
			WHERE
			post_status = 'publish'
			");
		$output = ' ';
		printf('<div class="footer-extras" style="text-align: %s;">', $footer_align);
				
			if(pl_setting('footer_extras_dms_copyright')) {
				
					if($copyright_text) {
						$additional_copyright =  $copyright_text . '  </div>';	
					} else {
						$additional_copyright = '</div>';
					}
					if($copyright_year) {
						$copyright = '<div class="copyright"> &copy; ' . $copyright_year . '  ';
					
					} else {
						$copyright = '<div class="copyright"> &copy; ' . $copyright_dates[0]->firstdate . '  ';
					
						}
					if($copyright_year  or ($copyright_dates[0]->firstdate != $copyright_dates[0]->lastdate)) {
					$copyright .= '- ' . $copyright_dates[0]->lastdate . ' ' . $additional_copyright;
					} else {
						$copyright .= ' ' . $additional_copyright;
					
					}
					echo $copyright;
			 }			
			 
				if($nap_name) {
					$name_output = '<span itemscope="" itemtype="http://schema.org/LocalBusiness"><span itemprop="name">'. $nap_name . '</span>';
				} else {
					$name_output = '<span itemscope="" itemtype="http://schema.org/LocalBusiness">';
				}

				if($nap_street){
					$street_output = ' • <span itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress"><span itemprop="streetAddress">' .$nap_street . '</span>';

				} else {
					$street_output = '<span itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">';
				}
				
				if($nap_city){
					$city_output = ' • <span itemprop="addressLocality">'. $nap_city . '</span>';

				} else {
					$city_output = null;
				}

				if($nap_state){
					$state_output = ', <span itemprop="addressRegion">' .$nap_state . '</span>';

				} else {
					$state_output = null;
				}

				if($nap_zip){
					$zip_output = '  <span itemprop="postalCode">' . $nap_zip .'</span></span>';

				} else {
					$zip_output = '</span>';
				}

				if($nap_phone){
					$phone_output = ' • <span itemprop="telephone">' . $nap_phone . '</span></span>';

				} else {
					$phone_output = '</span>';
				}


				if(pl_setting('footer_extras_dms_nap')) {
					?>
					<div class="nap">
						<?php

					$nap = $name_output. ''  .$street_output . '' .$city_output . '' .$state_output . '' . $zip_output . '' . $phone_output;
					echo $nap;
					
					
					if ($nap_latitude&&$nap_longitude) {
					?>
					<span itemtype="http://schema.org/GeoCoordinates" itemscope="" itemprop="geo">
  					<?php
  					printf('<meta content="%s" itemprop="latitude">' , $nap_latitude);
  					printf('<meta content="%s" itemprop="longitude">' , $nap_longitude);
  					?>
				</span>
				<?php
				}
			 	?>	
			 	</div>



			 	<?php
			 	}	

			 	if(pl_setting('footer_extras_dms_additional')) {
			 		printf('<div class="additional">%s</div>' , $footer_extras_dms_additional);

			 	}
			 
			 	echo '</div>';

		
	
	}

	function options( $settings ){

		
        $settings[ 'footer-extras-dms'] = array(
                'name'  => 'Footer Extras DMS',
                'icon'  => 'icon-download-alt',
                'pos'   => 5,
                'opts'  => $this->global_opts()
        );
        return $settings; 

    }

   
    
    // DMS Options

	function global_opts(){


		$global_opts = array(
			
			array(
				'key'		=>	'footer_extras_dms_show_copyright',
				'type'		=> 'multi', 
				'help'		=> __('Copyright dates are picked up dynamically from date of first published post and date of last published post.', 'footer-extra-dms'),
				'title'		=> __('Show Copyright Dates', 'footer-extras-dms'), 
				'opts'	=> array(
					
					array(
						'key'	=>	'footer_extras_dms_copyright',
						'type' 			=> 'check',			
						'label'	=> 'Show Copyright Notice?',
					),
					array(
						'key'			=>	'footer_extras_dms_copyright_text',
						'type' 			=> 'text',			
						'label'	=> 'Additional copyright text (Business NAP shows after copyright so not necessary to put Business Name here if displaying NAP too, just additional text.)'
					),
					array(
						'key'			=>	'footer_extras_dms_copyright_year',
						'type' 			=> 'text',			
						'label'	=> 'Start Year (Optional) (To override start year enter it here. Leave empty to pick up year from earliest post/page.)'
					),
				),
			),
			array(
				'key'		=>	'footer_extras_dms_nap_setup',
				'type'		=> 'multi', 
				'span'		=>	'2',
				'help'	=> __('Setup Business Name, Address and Phone formatted with rich text snippets for local seo. If you have a Google+ Local page or your business is listed in other directories make sure that the NAP is the same, otherwise you will not get the full value of the NAP. <br /><br />You can also enter your latitude and longitude (coordinates are not displayed, just coded for geolocation). Find your coordinates at http://geocoder.us.', 'footer-extras-dms'),
				'title'		=> __('Business NAP (Name, Address, Phone)', 'footer-extras-dms'), 
				'opts'	=> array(
					
					array(
						'key'			=>	'footer_extras_dms_nap',
						'type' 			=> 'check',			
						'label'	=> __('Show The Business NAP (Name, Address, Phone)?', 'footer-extras-dms')
					),
					array(
						'key'			=>	'nap_name',
						'type' 			=> 'text',			
						'label'	=> __('Business Name', 'footer-extras-dms')
					),
					array(
						'key'			=>	'nap_street',
						'type' 			=> 'text',			
						'label'	=> __('Business Street Address', 'footer-extras-dms')
					),
					array(
						'key'			=>	'nap_city',
						'type' 			=> 'text',			
						'label'	=> __('Business City', 'footer-extras-dms')
					),
					array(
						'key'			=>	'nap_state',
						'type' 			=> 'text',			
						'label'	=> __('Business State', 'footer-extras-dms')
					),
					array(
						'key'			=>	'nap_zip',
						'type' 			=> 'text',			
						'label'	=> __('Business Zip', 'footer-extras-dms')
					),
					array(
						'key'			=>	'nap_phone',
						'type' 			=> 'text',			
						'label'	=> __('Business Phone Number', 'footer-extras-dms')
					),
					array(
						'key'			=>	'nap_longtiude',
						'type' 			=> 'text',			
						'label'	=> __('Address Longitude', 'footer-extras-dms')
					),
					array(
						'key'			=>	'nap_latitude',
						'type' 			=> 'text',			
						'label'	=> __('Address Latitude', 'footer-extras-dms')
					),
					
				),
					
			),
			array(
				'key'		=>	'footer_extras_dms_show_additional',
				'type'		=> 'multi', 
				'title'		=> __('Show Additional Line of Text', 'footer-extras-dms'), 
				'opts'	=> array(
					
					array(
						'key'			=>	'footer_extras_dms_additional',
						'type' 			=> 'check',			
						'label'	=> 'Show Additional Line of Text?'
					),
					array(
						'key'			=>	'footer_extras_dms_additional_text',
						'type' 			=> 'text',			
						'label'	=> 'Additional text (Great place to have "Website Designed by" credit and link)'
					),
				),
			),

			 array(
			 		'key'		=>	'footer_extras_dms_align',
					'type' 		=> 'select',
					'title'		=> __('Alignment of Footer Extras', 'footer-extras-dms'), 
					'label' 	=> __( 'Footer Extras Align (Default: left)', 'footer-extras-dms'),
					'opts'	=> array(
							'left'		=> array('name' => 'Left'),
							'right'		=> array('name' => 'Right'),
							'center'	=> array('name' => 'Center'),
					)
			), 		
				
		);
		
		
		

		return array_merge($global_opts);

	}



}

new Footer_Extras_DMS;