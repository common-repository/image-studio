<?php 

if( !class_exists('vooSettingsClassImages') ){
class vooSettingsClassImages{
	
	var $setttings_parameters;
	var $settings_prefix;
	var $message;
	
	function __construct( $prefix ){
		$this->setttings_prefix = $prefix;	
		
		if(  wp_verify_nonce($_POST['save_settings_field'], 'save_settings_action') ){
			$options = array();
			foreach( $_POST as $key=>$value ){
				$options[$key] = $value ;
			}
			update_option( $this->setttings_prefix.'_options', $options );
			
			$this->message = '<div class="alert alert-success">Settings saved</div>';
			
		}
	}
	
	function get_setting( $setting_name ){
		$inner_option = get_option( $this->setttings_prefix.'_options');
		return $inner_option[$setting_name];
	}
	
	function create_menu( $parameters ){
		$this->setttings_parameters = $parameters;		
			
		add_action('admin_menu', array( $this, 'add_menu_item') );
		
	}
	
	 
	
	
	function add_menu_item(){
		
		foreach( $this->setttings_parameters as $single_option ){
			if( $single_option['type'] == 'menu' ){
				add_menu_page(  			 
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
			if( $single_option['type'] == 'submenu' ){
				add_submenu_page(  
				$single_option['parent_slug'],  
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
			if( $single_option['type'] == 'option' ){
				add_options_page(  				  
				$single_option['page_title'], 
				$single_option['menu_title'], 
				$single_option['capability'], 
				$single_option['menu_slug'], 
				array( $this, 'show_settings' ) 
				);
			}
		}
		 
	}
	
	function show_settings(){
		?>
		<div class="wrap tw-bs4">
		
		
		<div class="row">
		<div class="col-12">
		
		<div class="card col-12">
		<div class="card-body">
		 
		
		
		<h4 class="card-title"><?php _e('Settings', 'sc'); ?></h4>
		<hr/>
		<?php 
			echo $this->message;
		?>
		
		<form class="form-horizontal" method="post" action="">
		<?php 
		wp_nonce_field( 'save_settings_action', 'save_settings_field'  );  
		$config = get_option( $this->setttings_prefix.'_options'); 
		?>  
		<fieldset>

			<?php 
		foreach( $this->setttings_parameters as $single_page ){	
			foreach( $single_page['parameters'] as $key=>$value ){
				switch( $value['type'] ){
					case "separator":
						$out .= '
						<div class="lead">'.$value['title'].'</div> 
						';
					break;
					case "text":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							
							  <input type="text"  class="form-control '.$value['class'].'"  name="'.$value['name'].'" id="'.$value['id'].'" placeholder="'.$value['placeholder'].'" value="'.esc_html( stripslashes( $config[$value['name']] ) ).'">  
							  <p class="help-block">'.$value['sub_text'].'</p>  
							
						  </div> 
						';
					break;
					case "button":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="">&nbsp;</label>  
							
							  <a class="btn btn-success" href="'.$value['href'].'"   >'.$value['title'].'</a>  
							  
							
						</div> 
						';
					break;
					case "select":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							 
							  <select  style="'.$value['style'].'" class="form-control '.$value['class'].'" name="'.$value['name'].'" id="'.$value['id'].'">' ; 
							  if( count( $value['value'] ) > 0 )
							  foreach( $value['value'] as $k => $v ){
								  $out .= '<option value="'.$k.'" '.( $config[$value['name']]  == $k ? ' selected ' : ' ' ).' >'.$v.'</option> ';
							  }
						$out .= '		
							  </select>  
							  <p class="help-block">'.$value['sub_text'].'</p> 
							</div>  
						 
						';
					break;
					case "colorpicker":
					 
					$out .= '<div class="form-group">  
						<label class="control-label" for="input01">'.$value['title'].'</label>  
						 
							<div class="dropdown">
								<input type="text" class="form-control input-xlarge jscolor" name="'.$value['name'].'" id="'.$value['name'].'" value="'.esc_html( stripslashes( $config[$value['name']] ) ).'">
								<div class="abs_right"><img src="'.plugins_url( '/images/htmlcolors.gif', __FILE__ ).'" /></div>
							</div>
						  
					  </div> ';	
					break;
					case "checkbox":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
						
							  <label class="checkbox">  
								<input  class="'.$value['class'].'" type="checkbox" name="'.$value['name'].'" id="'.$value['id'].'" value="on" '.( $config[$value['name']] == 'on' ? ' checked ' : '' ).' > &nbsp; 
								'.$value['text'].'  
								<p class="help-block">'.$value['sub_text'].'</p> 
							  </label>  
							 
						  </div>  
						';
					break;
					case "radio":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>';
								foreach( $value['value'] as $k => $v ){
									$out .= '
									<label class="radio">  
										<input  class="'.$value['class'].'" type="radio" name="'.$value['name'].'" id="'.$value['id'].'" value="'.$k.'" '.( $config[$value['name']] == $k ? ' checked ' : '' ).' >&nbsp;  
										'.$v.'  
										<p class="help-block">'.$value['sub_text'].'</p> 
									  </label> ';
								}
							$out .= '
							
						  </div>  
						';
					break;
					case "textarea":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
						
							  <textarea style="'.$value['style'].'" class="form-control '.$value['class'].'" name="'.$value['name'].'" id="'.$value['id'].'" rows="'.$value['rows'].'">'.esc_html( stripslashes( $config[$value['name']] ) ).'</textarea>  
							  <p class="help-block">'.$value['sub_text'].'</p> 
						 
						  </div> 
						';
					break;
					case "multiselect":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
							 
							  <select  multiple="multiple" style="'.$value['style'].'" class="form-control '.$value['class'].'" name="'.$value['name'].'[]" id="'.$value['id'].'">' ; 
							  foreach( $value['value'] as $k => $v ){
								  $out .= '<option value="'.$k.'" '.( @in_array( $k, $config[$value['name']] )   ? ' selected ' : ' ' ).' >'.$v.'</option> ';
							  }
						$out .= '		
							  </select>  
							  <p class="help-block">'.$value['sub_text'].'</p> 
							 
						  </div>  
						';
					break;
					case "wide_editor":
					$out .= '<div class="form-group">  
						<label class="control-label" for="input01">'.$value['title'].'</label>
						<div class="form-control1">
						';  
						 
						ob_start();
						wp_editor( $config[$value['name']], $value['name'] );
						$editor_contents = ob_get_clean();	
					 
						$out .= $editor_contents;  
					$out .= '
						</div>
					  </div> ';	 
					 
					break;
					case "file":
						$out .= '
						<div class="form-group">  
							<label class="control-label" for="'.$value['id'].'">'.$value['title'].'</label>  
				 
							<input type="file" class="form-control-file '.$value['class'].'" name="'.$value['name'].''.( $value['multi'] ? '[]' : '' ).'" id="'.$value['id'].'" '.( $value['multi'] ? ' multiple ' : '' ).' >
							  
							  <p class="help-block">'.$value['sub_text'].'</p> 
						 
						  </div> 
						';
					break;
				}
			}
		}
			echo $out;
			?>

				
				  <div class="form-actions">  
					<button type="submit" class="btn btn-primary">Save Settings</button>  
				  </div>  
				</fieldset>  

		</form>

		</div>
		</div>
		
		</div>
		</div>

		</div>
		<?php
	}
}	
}	
 
	
	
add_Action('init',  function (){
	
	 // font size
	 $font_sizes = array();
	 for( $i=10; $i<=60; $i++ ){
		$font_sizes[$i] = $i;
	 }
	 
	 
	 // font size
	 $opacity = array();
	 for( $i=0; $i<=100; $i++ ){
		$opacity[$i] = $i;
	 }
	
	//rotation angle
	 $rotation_angle = array();
	 for( $i=0; $i<=360; $i++ ){
		$rotation_angle[$i] = $i;
	 }
	
	
	$config_big = 
	array(

		array(
			'type' => 'option',
			
			'page_title' => __('Image Studio', $locale_taro),
			'menu_title' => __('Image Studio', $locale_taro),
			'capability' => 'edit_published_posts',
			'menu_slug' => 'wig_settings',

			'parameters' => array(
			
				array(
					'type' => 'select',
					'title' => 'Use Gradient',
					'name' => 'use_gradient',
					'value' => array( 'yes' => 'Yes', 'no' => 'No' )
				),
			
				array(
					'type' => 'colorpicker',
					'title' => 'Overlay Color',
					'name' => 'overlay_color',
				),
				
				
				array(
					'type' => 'colorpicker',
					'title' => 'Overlay Color ( Gradient To )',
					'name' => 'overlay_color_to',
 
				),
				
				
				
				array(
					'type' => 'select',
					'title' => 'Opacity',
					'name' => 'opacity',
					'value' => $opacity,
				),
				
				array(
					'type' => 'select',
					'title' => 'Gradient Rotation',
					'name' => 'gradient_rotation',
					'value' => $rotation_angle,
					 
				),
				
				array(
					'type' => 'colorpicker',
					'title' => 'Font Color',
					'name' => 'font_color',
				),
				
				array(
					'type' => 'select',
					'title' => 'Font Family',
					'name' => 'font_family',
					'value' => array( 
						'arial.ttf' => 'Arial', 
						'trench100free.ttf' => 'trench100free' 
					),
				),
				array(
					'type' => 'select',
					'title' => 'Font Size',
					'name' => 'font_size',
					'value' => $font_sizes,
				),
				/*
				array(
					'type' => 'select',
					'title' => 'Font Style',
					'name' => 'font_style',
					'value' => array( 'normal' => 'Normal', 'italic' => 'Italic' ),
				),
				*/
				array(
					'type' => 'select',
					'title' => 'Text Position Vertical',
					'name' => 'text_position',
					'value' => array( 'top' => 'Top', 'center' => 'Center', 'bottom' => 'Bottom' ),
				),
				array(
					'type' => 'select',
					'title' => 'Text Position Horizontal',
					'name' => 'text_position_hor',
					'value' => array( 'left' => 'Left', 'center' => 'Center', 'right' => 'Right' ),
				),
				array(
					'type' => 'multiselect',
					'title' => 'Fields to display',
					'name' => 'fields_to_display',
					'value' => array( 'category' => 'Category', 'title' => 'Title', 'date' => 'Date' ),
				),
				array(
					'type' => 'select',
					'title' => 'Rewrite OG:IMAGE tag',
					'name' => 'rewrite_og_image',
					'value' => array( 'no' => 'No', 'yes' => 'Yes' )
				),

				array(
					'type' => 'image_preview_block',
					'title' => 'Images Preview',
				 
			 
				),
				 
			)
		)
	); 
	global $settings;

	$settings = new vooSettingsClassImages( 'wig' ); 
	$settings->create_menu(  $config_big   );
	
} );
	
 

?>