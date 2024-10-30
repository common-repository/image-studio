<?php 

 
	class imageManipulator{
		
		var $locale;
		
		var $post_has_image = true;
		
		var $image_path;
		var $post_id;
		var $font_path;
		
		var $use_global;
		var $use_gradient;
		var $overlay_color;
		var $overlay_color_to;
		var $opacity;
		var $gradient_rotation;
		var $font_family;
		var $font_color;
		var $font_size;
		var $font_style;
		var $text_position;
		var $text_position_hor;
		var $fields_to_display;
		var $rewrite_og_image;
		
		var $social_sizes;
		var $banner_sizes;
		
		
		var $post_category;
		var $post_title;
		var $post_date;
		
		function __construct( $post_id  = false   ){
			global $locale;
			
			$this->locale = $locale;
			$this->post_id = $post_id;
			$this->image_path = get_attached_file( get_post_thumbnail_id( $this->post_id) );
			
			if( !$this->image_path || $this->image_path == '' ){			 
				$this->post_has_image = false;
			}
			
			$this->use_global = get_post_meta( $this->post_id, 'use_global', true );
			
			if( $this->use_global == 'no' ){
				$this->overlay_color = get_post_meta( $this->post_id, 'overlay_color', true );
				$this->use_gradient = get_post_meta( $this->post_id, 'use_gradient', true );
				$this->overlay_color_to = get_post_meta( $this->post_id, 'overlay_color_to', true );
				$this->opacity = get_post_meta( $this->post_id, 'opacity', true );
				$this->gradient_rotation = get_post_meta( $this->post_id, 'gradient_rotation', true );
				$this->font_family = get_post_meta( $this->post_id, 'font_family', true );
				$this->font_color = get_post_meta( $this->post_id, 'font_color', true );
				$this->font_size = get_post_meta( $this->post_id, 'font_size', true );
				$this->font_style = get_post_meta( $this->post_id, 'font_style', true );
				$this->text_position = get_post_meta( $this->post_id, 'text_position', true );
				$this->text_position_hor = get_post_meta( $this->post_id, 'text_position_hor', true );
				$this->fields_to_display = get_post_meta( $this->post_id, 'fields_to_display', true );
				$this->rewrite_og_image = get_post_meta( $this->post_id, 'rewrite_og_image', true );
			}
			if( $this->use_global == 'yes' ){
				$config = get_option( $this->locale.'_options');
			
				$this->overlay_color = $config['overlay_color'];
				$this->use_gradient = $config['use_gradient'];
				$this->overlay_color_to = $config['overlay_color_to'];				
				$this->opacity = $config['opacity'];
				$this->gradient_rotation = $config['gradient_rotation'];
				$this->font_family = $config['font_family'];
				$this->font_color = $config['font_color'];
				$this->font_size = $config['font_size'];
				$this->font_style = $config['font_style'];
				$this->text_position = $config['text_position'];
				$this->text_position_hor = $config['text_position_hor'];
				$this->fields_to_display = $config['fields_to_display'];
				$this->rewrite_og_image = $config['rewrite_og_image'];
			}
	 
			$this->social_sizes = array(
			'facebook' => array( 'w' => 1200, 'h' => 628, 'name' => 'Facebook' ),
			'instagram' => array( 'w' => 1080, 'h' => 1080, 'name' => 'Instagram'),
			'twitter' => array( 'w' => 1200, 'h' => 675, 'name' => 'Twitter' ),
			'linkedin' => array( 'w' => 1104, 'h' => 735, 'name' => 'LinkedIn' ),
			'pinterest' => array( 'w' => 800, 'h' => 1200, 'name' => 'Pinterest' ),
			'snapchat' => array( 'w' => 1080, 'h' => 1920, 'name' => 'SnapChat' ),
			);
			
			$this->banner_sizes = array(
			'250x250' => array( 'w' => 250, 'h' => 250 ),
			'200x200' => array( 'w' => 200, 'h' => 200),
			'468x60' => array( 'w' => 468, 'h' => 60, 'fontsize' => 11, 'bottomoffset' => 5  ),
			'728x90' => array( 'w' => 728, 'h' => 90  ),
			'300x250' => array( 'w' => 300, 'h' => 250  ),
			'336x280' => array( 'w' => 336, 'h' => 280  ),
			'120x600' => array( 'w' => 120, 'h' => 600, 'fontsize' => 15    ),
			'160x600' => array( 'w' => 160, 'h' => 600, 'fontsize' => 15     ),
			'300x600' => array( 'w' => 300, 'h' => 600   ),
			'970x90' => array( 'w' => 970, 'h' => 90    ),
			);
			
			// init output strings
			$current_post = get_post( $this->post_id );
			
			$this->post_category = '';
			$terms = wp_get_post_terms( $post_id, 'category' );
			if( count( $terms ) > 0 ){
				foreach( $terms as $single_post ){
					$this->post_category = $single_post->name;
				}
			}
			
			$this->post_title = $current_post->post_title;
			$this->post_date = date( 'm/d/Y',  strtotime( $current_post->post_date ) );
			
			
			// font init
			$this->font_path = realpath(dirname(__FILE__)).'/'.$this->font_family;
	 
			// hooks 
			add_Action('init', array( $this, 'download_zip' ));
			
		}
		
		function generate_parameters_hash(){
			return md5(
				$this->overlay_color .
				$this->use_gradient .
				$this->overlay_color_to .
				$this->opacity .
				$this->gradient_rotation .
				$this->font_family .
				$this->font_color .
				$this->font_size .
				$this->font_style .
				$this->text_position .
				$this->text_position_hor .
				$this->fields_to_display .
				$this->rewrite_og_image
			);	
		}
		
		
		function download_zip(){

			if( $_GET['image_action'] ){
				$zip = new ZipArchive;
			 
				$post_id = $_GET['id'];	
				switch( $_GET['image_action'] ){
					
					case "social_pack":
					
					// generate social images
					$image = new imageManipulator( $post_id );
					$image->generate_social_images();
					
					$zip_filename = 'social_pack.zip';
				
						$rez = $zip->open( $zip_filename, ZipArchive::CREATE);
						if ( $rez === TRUE) {
							foreach( $this->social_sizes as $name => $value ){
								$cf_name_path = 'image_path_'.$name;
			
								$image_path = get_post_meta( $post_id, $cf_name_path, true );
								$filename = explode('/', $image_path );
			
								$zip->addFile( $image_path, $filename[count($filename)-1]);
							}
							$zip->close();
				
						}
				
					break;
					case "ads_pack":
					$zip_filename = 'ads_pack.zip';
					
					// generate social images
					$image = new imageManipulator( $post_id );
					$image->generate_banner_images();
					
					if ($zip->open( $zip_filename, ZipArchive::CREATE ) === TRUE) {
						foreach( $this->banner_sizes as $name => $value ){
							$cf_name_path = 'image_path_'.$name;							
							$image_path = get_post_meta( $post_id, $cf_name_path, true );
						 
							$filename = explode('/', $image_path );
							$zip->addFile( $image_path, $filename[count($filename)-1]);
				
						}
						$zip->close();
				
					}
					
					break;
					case "all_pack":
					$zip_filename = 'all_pack.zip';
					
					// generate all images
					$image = new imageManipulator( $post_id );
					$image->generate_social_images();
					$image->generate_banner_images();
					
					if ($zip->open( $zip_filename, ZipArchive::CREATE ) === TRUE) {
							foreach( $this->social_sizes as $name => $value ){
								$cf_name_path = 'image_path_'.$name;							
								$image_path = get_post_meta( $post_id, $cf_name_path, true );
								$filename = explode('/', $image_path );
								$zip->addFile( $image_path, $filename[count($filename)-1]);
							}
							foreach( $this->banner_sizes as $name => $value ){
								$cf_name_path = 'image_path_'.$name;							
								$image_path = get_post_meta( $post_id, $cf_name_path, true );
								$filename = explode('/', $image_path );
								$zip->addFile( $image_path, $filename[count($filename)-1]);
							}
							$zip->close();
						}
					
					
					break;
					
				}
				
				header('Content-type: application/zip');
				header('Content-Disposition: attachment; filename="'.$zip_filename.'"');
				readfile( $zip_filename );
				die();
			}
			
		}
		
		// create resized background
		function resize_crop_image($max_width, $max_height, $source_file,  $quality = 80){
			$imgsize = getimagesize($source_file);
			$width = $imgsize[0];
			$height = $imgsize[1];
			$mime = $imgsize['mime'];
			
			switch($mime){
				case 'image/gif':
				$image_create = "imagecreatefromgif";
				$image = "imagegif";
				break;
				
				case 'image/png':
				$image_create = "imagecreatefrompng";
				$image = "imagepng";
				$quality = 7;
				break;
				
				case 'image/jpeg':
				$image_create = "imagecreatefromjpeg";
				$image = "imagejpeg";
				$quality = 80;
				break;
				
				default:
				return false;
				break;
			}
			
			$dst_img = imagecreatetruecolor($max_width, $max_height);
			$src_img = $image_create($source_file);
			
			$width_new = $height * $max_width / $max_height;
			$height_new = $width * $max_height / $max_width;
			//if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
			if($width_new > $width){
				//cut point by height
				$h_point = (($height - $height_new) / 2);
				//copy image
				imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
				}else{
				//cut point by width
				$w_point = (($width - $width_new) / 2);
				imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
			}
			
			return $dst_img;
		}
		
		//generate social images
		function generate_social_images(){
			if( count($this->social_sizes) > 0 )
			foreach( $this->social_sizes as $name => $value ){
				$this->generate_social_banner( $value, $name );
			}
			update_post_meta( $this->post_id, 'param_hash', $this->generate_parameters_hash() );
		}	
		
		// generate banner images
		function generate_banner_images(){
		if( count($this->banner_sizes) > 0 )
			foreach( $this->banner_sizes as $name => $value ){
				$this->generate_ads_banner( $value, $name );
			}
			update_post_meta( $this->post_id, 'param_hash', $this->generate_parameters_hash() );
		}
		
		//generate array of social images
		public function generate_social_images_array(){
			
			$out_array = array();
			
			if( count( $this->social_sizes ) > 0 )
			foreach( $this->social_sizes as $name => $value ){
				$cf_name = 'image_'.$name;
				$image_url = get_post_meta( $this->post_id, $cf_name, true );
				if( $image_url && $image_url != '' ){
					$out_array[$name] = array( 'name' => $value['name'], 'url' => $image_url  );
				}
			}
			return $out_array;
		}
		
		// generate html output for social images
		function generate_social_images_array_output(){
			$res = $this->generate_social_images_array();
			$out = '<ul>';
			foreach( $res as $single_res ){
				$out .= '<li><span class="list_name">'.$single_res['name'].'</span>
				<a target="_blank" class="btn btn-success btn-sm btn-xsm" href="'.$single_res['url'].'">View</a>
				<a target="_blank" download="download" class="btn btn-info btn-sm btn-xsm" href="'.$single_res['url'].'">Download</a>
				</li>';
			}
			$out .= '</ul>';
			return $out;
		}
		
		
		//generate ads images
		public function generate_banner_images_array(){
			
			$out_array = array();
			
			if( count( $this->banner_sizes ) > 0 )
			foreach( $this->banner_sizes as $name => $value ){
				$cf_name = 'image_'.$name;
				$image_url = get_post_meta( $this->post_id, $cf_name, true );
				if( $image_url && $image_url != '' ){
					$out_array[$name] = array( 'name' => $name, 'url' => $image_url  );
				}
			}
		 
			return $out_array;
		}
		
		// generate html output
		function generate_banner_images_array_output(){
			$res = $this->generate_banner_images_array();
			$out = '<ul>';
			foreach( $res as $single_res ){
				$out .= '<li><span class="list_name">'.$single_res['name'].'</span>
				<a target="_blank" class="btn btn-success btn-sm btn-xsm" href="'.$single_res['url'].'">View</a>
				<a target="_blank" download="download" class="btn btn-info btn-sm btn-xsm" href="'.$single_res['url'].'">Download</a>
				</li>';
			}
			$out .= '</ul>';
			return $out;
		}
		
		// fill image with gradient
		function image_gradientrect($img,$x,$y,$x1,$y1,$start,$end) {
			if($x > $x1 || $y > $y1) {
				return false;
			}
			$s = array(
				hexdec(substr($start,0,2)),
				hexdec(substr($start,2,2)),
				hexdec(substr($start,4,2))
			);
			$e = array(
				hexdec(substr($end,0,2)),
				hexdec(substr($end,2,2)),
				hexdec(substr($end,4,2))
			);
			$steps = $y1 - $y;
			for($i = 0; $i < $steps; $i++) {
				$r = $s[0] - ((($s[0]-$e[0])/$steps)*$i);
				$g = $s[1] - ((($s[1]-$e[1])/$steps)*$i);
				$b = $s[2] - ((($s[2]-$e[2])/$steps)*$i);
				
				//$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
				//imagealphablending($img, false);
				
				$color = imagecolorallocatealpha($img,$r,$g,$b, 0);
				imagealphablending($img, false);
				imagefilledrectangle($img,$x,$y+$i,$x1,$y+$i+1,$color);
				imagealphablending($img, true);
			}
			return true;
		}
		
		
		// generate banner with overlap
		function generate_social_banner( $size, $cf_name = false ){
			 
			list($width, $height) = getimagesize( $this->image_path );
			
			// placeholder for new image
			$newwidth = $size['w'];
			$newheight = $size['h'];
		 
			$thumb = imagecreatetruecolor($newwidth, $newheight);
			
			
			
			// resize image
			$thumb = $this->resize_crop_image($newwidth, $newheight, $this->image_path, 100);
			
			// make square image
			if( $newwidth > $newheight ){
				$max_size = $newwidth * 1.5;
			}else{
				$max_size = $newheight * 1.5;
			}
		 
			// create square overlap
			$square_overlap = imagecreatetruecolor( $max_size, $max_size );
			imagesavealpha($square_overlap, true);
			
		 
			
			if( $this->use_gradient == 'yes' ){
				// fill with gradient
				$this->image_gradientrect($square_overlap, 0, 0, $max_size, $max_size, $this->overlay_color, $this->overlay_color_to);
				 
				// rotate image
				$square_overlap = imagerotate($square_overlap, $this->gradient_rotation, 0, 1);
				
				
				
				// apply opacity on gradient layer
				imagealphablending($square_overlap, false); 
				imagesavealpha($square_overlap, true);  		 
				imagefilter($square_overlap, IMG_FILTER_COLORIZE, 0,0,0, (int)(( (int)$this->opacity * 127 ) / 100) );
				
				
				$after_rotation_width  = imagesx($square_overlap);
				$after_rotation_height = imagesy($square_overlap);
				
				
				$offset_left_rotated = ( $after_rotation_width / 2 ) - ( $newwidth / 2 );
				$offset_top_rotated = ( $after_rotation_height / 2 ) - ( $newheight / 2 );
			 
			 
				//create placeholder
				#$img_overlap = imagecreatetruecolor( $newwidth, $newheight );
				#imagesavealpha($img_overlap, true);
				
				imagecopyresized($thumb, $square_overlap, 0, 0, $offset_left_rotated, $offset_top_rotated, $newwidth, $newheight, $newwidth, $newheight);
			
			}else{
				$img_overlap = imagecreatetruecolor( $newwidth, $newheight );
				imagesavealpha($img_overlap, true);
				
				list($r, $g, $b) = sscanf( '#'.$this->overlay_color, "#%02x%02x%02x");
				
				$overlay_color = imagecolorallocatealpha($img_overlap, $r, $g, $b, (int)(( (int)$this->opacity * 127 ) / 100) );
				imagefill($img_overlap, 0, 0, $overlay_color);
				
				imagecopyresized($thumb, $img_overlap, 0, 0, 0, 0, $newwidth, $newheight, $newwidth, $newheight);
			
			}
			
	
			######################
			// create overlap
			$img_overlap = imagecreatetruecolor( $newwidth, $newheight );
			imagesavealpha($img_overlap, true);
			

			
			// font color
			list($r, $g, $b) = sscanf( '#'.$this->font_color, "#%02x%02x%02x");
			$text_color = imagecolorallocate( $thumb, $r, $g, $b);	
			
			
			// put text over image
			
			//calculate left / top offset
			//off top
			
			if( $this->text_position == 'top' ){
				$offset_fix = 10;
			}
			
			if( $this->text_position == 'center' ){
				$offset_fix = 30;
			}
			
			if( $this->text_position == 'bottom' ){
				$offset_fix = 60;
			}
			
			$font_size_fixed = (int)($this->font_size*0.4);
			
			##########################
			
			// calculate global block height
			if( @in_array( 'date', $this->fields_to_display ) ){
				$date_space = imagettfbbox($font_size_fixed, 0, $this->font_path, $this->post_date );
				$date_block_font_height = $date_space[1] - $date_space[7];
				$date_block_height = $date_space[1] - $date_space[7] +10;
				 
			}
			if( @in_array( 'category', $this->fields_to_display ) ){
				$category_space = imagettfbbox($font_size_fixed, 0, $this->font_path, $this->post_category );
				$category_block_font_height = $category_space[1] - $category_space[7];
				$category_block_height = $category_space[1] - $category_space[7] +10;
			 
			}
			if( @in_array( 'title', $this->fields_to_display ) ){
				// max string width
				$max_string_width = $newwidth - ( $newwidth*0.2 );
				
				for( $let_num=10; $let_num <= 200; $let_num++ ){
					$type_space = imagettfbbox($this->font_size, 0, $this->font_path, str_repeat( 'S',$let_num));
					$text_line_font_heigh = $type_space[1] - $type_space[7];
					$text_line_heigh = $type_space[1] - $type_space[7];
					$type_space = $type_space[2] - $type_space[0];
					
					$max_char_amount = $let_num;
					if( $type_space >= $max_string_width ){
						break;
					}
				}
				
				// split line to sublines
				$splited_line_array = explode('|', wordwrap( $this->post_title, $max_char_amount, '|') );	
			}
			// gloabl out  block
			$total_block_height = ( $date_block_height + $category_block_height + ( ($text_line_heigh + 10) * count( $splited_line_array ) ) );
			
			// calculate all offsets
			$vert_active_area = $newheight - $newheight*0.1 - $newheight*0.1;
			$common_block_top_offset = 0 + $newheight*0.1;
			$common_middle_top_offset = $vert_active_area - ( $total_block_height * 1.5 ) + $newheight*0.1;
			$common_bottom_top_offset = $vert_active_area - $total_block_height  + $newheight*0.1;
			
			// offset for date
			if( $this->text_position == 'top' ){
				$date_top_offset = $common_block_top_offset + $date_block_font_height;
				$category_top_offset = $common_block_top_offset + $category_block_font_height;
				if( @in_array( 'date', $this->fields_to_display ) ){
					$category_top_offset = $category_top_offset + $date_block_height;
				}
				$text_top_offset = $common_block_top_offset + $text_line_font_heigh;
				if( @in_array( 'date', $this->fields_to_display ) ){
					$text_top_offset = $text_top_offset + $date_block_height;
				}
				if( @in_array( 'category', $this->fields_to_display ) ){
					$text_top_offset = $text_top_offset + $category_block_height;
				}
			}
			
			if( $this->text_position == 'center' ){
				$date_top_offset = $common_middle_top_offset  + $date_block_font_height;
				$category_top_offset = $common_middle_top_offset+ $category_block_font_height;
				if( @in_array( 'date', $this->fields_to_display ) ){
					$category_top_offset = $category_top_offset + $date_block_height;
				}
				$text_top_offset = $common_middle_top_offset + $text_line_font_heigh;
				if( @in_array( 'date', $this->fields_to_display ) ){
					$text_top_offset = $text_top_offset + $date_block_height;
				}
				if( @in_array( 'category', $this->fields_to_display ) ){
					$text_top_offset = $text_top_offset + $category_block_height;
				}
			}
			
			if( $this->text_position == 'bottom' ){
				$date_top_offset = $common_bottom_top_offset  + $date_block_font_height;
				$category_top_offset = $common_bottom_top_offset + $category_block_font_height;
				if( @in_array( 'date', $this->fields_to_display ) ){
					$category_top_offset = $category_top_offset + $date_block_height;
				}
				$text_top_offset = $common_bottom_top_offset + $text_line_font_heigh;
				if( @in_array( 'date', $this->fields_to_display ) ){
					$text_top_offset = $text_top_offset + $date_block_height;
				}
				if( @in_array( 'category', $this->fields_to_display ) ){
					$text_top_offset = $text_top_offset + $category_block_height;
				}
				
			}
	 
		 
		
			############################################
			
			// date
			//$top_offset = $offset_fix * $newheight / 100 + $font_size_fixed + 20;		// bkp
			$top_offset = $date_top_offset;
			$date_space = imagettfbbox($font_size_fixed, 0, $this->font_path, $this->post_date );
			$date_space = $date_space[2] - $date_space[0];
			 
			// left offset
			if( $this->text_position_hor == 'left' ){
				$left_offset = 10 * $newwidth / 100;
			}
			// left offset
			if( $this->text_position_hor == 'right' ){
				$active_area = $newwidth - (10 * $newwidth / 100) ;			
				$left_offset = $active_area - $date_space;
			}
			// left offset
			if( $this->text_position_hor == 'center' ){	 
				$active_area = $newwidth - (10 * $newwidth / 100) ;					 
				$left_offset = ($active_area - $date_space) / 2 + (10 * $newwidth / 100) / 2;			 
			}
			
			if( @in_array( 'date', $this->fields_to_display ) ){
				imagettftext( $thumb, $font_size_fixed, 0, $left_offset, $top_offset, $text_color, $this->font_path, $this->post_date );
			}
			
			// category		
			// BKP $top_offset = $offset_fix * $newheight / 100 + ( $font_size_fixed  + 20 ) * 2;		
			$top_offset = $category_top_offset;		
			$category_space = imagettfbbox($font_size_fixed, 0, $this->font_path, $this->post_category );
			$category_space = $category_space[2] - $category_space[0];
			// left offset
			if( $this->text_position_hor == 'right' ){
				$active_area = $newwidth - (10 * $newwidth / 100) ;			
				$left_offset = $active_area - $category_space;
			}
			// left offset
			if( $this->text_position_hor == 'center' ){
				$active_area = $newwidth - (10 * $newwidth / 100) ;			
				$left_offset = ($active_area - $category_space)/2 + (10 * $newwidth / 100) / 2;
			}
			
			if( @in_array( 'category', $this->fields_to_display ) ){
				imagettftext( $thumb, $font_size_fixed, 0, $left_offset, $top_offset, $text_color, $this->font_path, $this->post_category );
			}
		 
			
			// calculate amount of rows
			
			// Retrieve bounding box:
			
			// max string width
			$max_string_width = $newwidth - ( $newwidth*0.2 );
			
			for( $let_num=10; $let_num <= 200; $let_num++ ){
				$type_space = imagettfbbox($this->font_size, 0, $this->font_path, str_repeat( 'S',$let_num));
				$type_space = $type_space[2] - $type_space[0];
				
				$max_char_amount = $let_num;
				if( $type_space >= $max_string_width ){
					break;
				}
			}
			
			// split line to sublines
			$splited_line_array = explode('|', wordwrap( $this->post_title, $max_char_amount, '|') );
			
			$inner_offset = 0;
			foreach( $splited_line_array as $single_line ){
				// title
				// BKP $top_offset = $offset_fix * $newheight / 100 + ( ( $font_size_fixed + 20 ) * 2 ) + $inner_offset * ($this->font_size + 20);
				$top_offset = $text_top_offset + ($text_line_heigh + 10 )*$inner_offset;
				
				
				$line_space = imagettfbbox( $this->font_size, 0, $this->font_path, $single_line );
				$line_space = $line_space[2] - $line_space[0];
				// left offset
				if( $this->text_position_hor == 'right' ){
					$active_area = $newwidth - (10 * $newwidth / 100) ;			
					$left_offset = $active_area - $line_space;
				}
				
				// left offset
				if( $this->text_position_hor == 'center' ){
					$active_area = $newwidth - (10 * $newwidth / 100) ;			
					$left_offset = ($active_area - $line_space)/2 + (10 * $newwidth / 100) / 2;
				}
				 
				if( @in_array( 'title', $this->fields_to_display ) ){
					imagettftext( $thumb, $this->font_size, 0, $left_offset, $top_offset, $text_color, $this->font_path, $single_line );
				}
				$inner_offset++;
			}
			
			
			//imagecopyresized($thumb, $img_overlap, 0, 0, 0, 0, $newwidth, $newheight, $newwidth, $newheight);
			
			
			// prepare new file name
			$new_file_name = $this->post_id.'_'.$cf_name.'_image.png';
			
			$size_name = $cf_name;
			
			$cf_name = 'image_'.$size_name;
			$cf_name_path = 'image_path_'.$size_name;
			
			$upload_dir = wp_upload_dir();
			
			wp_mkdir_p( $upload_dir['basedir'].'/social_images/'.$this->post_id.'/social/' );
			
			$new_file_url = $upload_dir['baseurl'].'/social_images/'.$this->post_id.'/social/'.$new_file_name;
			$new_file_path = $upload_dir['basedir'].'/social_images/'.$this->post_id.'/social/'.$new_file_name;
			
			update_post_meta( $this->post_id, $cf_name, $new_file_url );
			update_post_meta( $this->post_id, $cf_name_path, $new_file_path );
			$this->save_image( $thumb, $new_file_path, $this->image_path );
			
		 
		}
		
		
		// generate banner with overlap
		function generate_ads_banner( $size, $cf_name = false ){
			
			list($width, $height) = getimagesize( $this->image_path );
			
			$newwidth = $size['w'];
			$newheight = $size['h'];
			$thumb = imagecreatetruecolor($newwidth, $newheight);
			
			
			// recalculate font size
			$new_font_size = (int)($newheight / 15 ) ;
			if( $new_font_size < 15 ){
				$new_font_size = 15;
			}
			if( $new_font_size > 25 ){
				$new_font_size = 25;
			}
			
			if( $size['fontsize'] ){
				$new_font_size = $size['fontsize'];
			}
			
			
			$thumb = $this->resize_crop_image($newwidth, $newheight, $this->image_path, 100);
			
			// make square image
			if( $newwidth > $newheight ){
				$max_size = $newwidth * 1.5;
			}else{
				$max_size = $newheight * 1.5;
			}
		 
			// create square overlap
			$square_overlap = imagecreatetruecolor( $max_size, $max_size );
			imagesavealpha($square_overlap, true);
			
		 
			
			if( $this->use_gradient == 'yes' ){
				// fill with gradient
				$this->image_gradientrect($square_overlap, 0, 0, $max_size, $max_size, $this->overlay_color, $this->overlay_color_to);
				 
				// rotate image
				$square_overlap = imagerotate($square_overlap, $this->gradient_rotation, 0, 1);
				
				
				
				// apply opacity on gradient layer
				imagealphablending($square_overlap, false); 
				imagesavealpha($square_overlap, true);  		 
				imagefilter($square_overlap, IMG_FILTER_COLORIZE, 0,0,0, (int)(( (int)$this->opacity * 127 ) / 100) );
				
				
				$after_rotation_width  = imagesx($square_overlap);
				$after_rotation_height = imagesy($square_overlap);
				
				
				$offset_left_rotated = ( $after_rotation_width / 2 ) - ( $newwidth / 2 );
				$offset_top_rotated = ( $after_rotation_height / 2 ) - ( $newheight / 2 );
			 
			 
				//create placeholder
				#$img_overlap = imagecreatetruecolor( $newwidth, $newheight );
				#imagesavealpha($img_overlap, true);
				
				imagecopyresized($thumb, $square_overlap, 0, 0, $offset_left_rotated, $offset_top_rotated, $newwidth, $newheight, $newwidth, $newheight);
				
			}else{
				$img_overlap = imagecreatetruecolor( $newwidth, $newheight );
				imagesavealpha($img_overlap, true);
				
				list($r, $g, $b) = sscanf( '#'.$this->overlay_color, "#%02x%02x%02x");
				
				$overlay_color = imagecolorallocatealpha($img_overlap, $r, $g, $b, (int)(( (int)$this->opacity * 127 ) / 100) );
				imagefill($img_overlap, 0, 0, $overlay_color);
				
				imagecopyresized($thumb, $img_overlap, 0, 0, 0, 0, $newwidth, $newheight, $newwidth, $newheight);
			
			}
			
			
			// create overlap
			$img_overlap = imagecreatetruecolor( $newwidth, $newheight );
			imagesavealpha($img_overlap, true);
			/*
			// get overlap color
			list($r, $g, $b) = sscanf( '#'.$this->overlay_color, "#%02x%02x%02x");
			
			// get opacity
			$overlay_color = imagecolorallocatealpha($img_overlap, $r, $g, $b, (int)(( (int)$this->opacity * 127 ) / 100) );
			imagefill($img_overlap, 0, 0, $overlay_color);
			*/
			
			
			// font color
			list($r, $g, $b) = sscanf( '#'.$this->font_color, "#%02x%02x%02x");
			$text_color = imagecolorallocate( $img_overlap, $r, $g, $b);	
			
			
			// put text over image
			
			//calculate left / top offset
			//off top
			
			
			$offset_fix = 10;
			
			$font_size_fixed = (int)( $new_font_size*0.6);
			
			
			// bottom offset
			$bottom_offset = 10;
			if( $size['bottomoffset'] ){
				$bottom_offset = $size['bottomoffset'];
			}
			
			
			// date
			$top_offset = $offset_fix * $newheight / 100 + $font_size_fixed + $bottom_offset;
			
			$date_space = imagettfbbox($font_size_fixed, 0, $this->font_path, $this->post_date );
			$date_space = $date_space[2] - $date_space[0];
			
			// left offset
			if( $this->text_position_hor == 'left' ){
				$left_offset = 10 * $newwidth / 100;
			}
			// left offset
			if( $this->text_position_hor == 'right' ){
				$active_area = $newwidth - (10 * $newwidth / 100) ;			
				$left_offset = $active_area - $date_space;
			}
			// left offset
			if( $this->text_position_hor == 'center' ){
				$active_area = $newwidth - (10 * $newwidth / 100) ;			
				$left_offset = ($active_area - $date_space) / 2 + (10 * $newwidth / 100) / 2;
			}
			if( in_array( 'date', $this->fields_to_display ) ){
				imagettftext( $thumb, $font_size_fixed, 0, $left_offset, $top_offset, $text_color, $this->font_path, $this->post_date );
			}
			
			
			
			// category		
			$top_offset = $offset_fix * $newheight / 100 + ( $font_size_fixed  + $bottom_offset ) * 2;
			
			$category_space = imagettfbbox( $font_size_fixed, 0, $this->font_path, $this->post_category );
			 
			$category_space = $category_space[2] - $category_space[0];
	 
			// left offset
			if( $this->text_position_hor == 'right' ){
				$active_area = $newwidth - (10 * $newwidth / 100) ;			
				$left_offset = $active_area - $category_space;
			}
			// left offset
			if( $this->text_position_hor == 'center' ){
				$active_area = $newwidth - (10 * $newwidth / 100) ;			
				$left_offset = ($active_area - $category_space)/2 + (10 * $newwidth / 100) / 2;	
			}
		 
			if( in_array( 'category', $this->fields_to_display ) ){
				imagettftext( $thumb, $font_size_fixed, 0, $left_offset, $top_offset, $text_color, $this->font_path, $this->post_category );
			}
			
			
			
			// calculate amount of rows
			
			// Retrieve bounding box:
			
			// max string width
			$max_string_width = $newwidth - ( $newwidth*0.2 );
			
			for( $let_num=10; $let_num <= 200; $let_num++ ){
				$type_space = imagettfbbox($new_font_size, 0, $this->font_path, str_repeat( 'S',$let_num));
				$type_space = $type_space[2] - $type_space[0];
				
				$max_char_amount = $let_num;
				if( $type_space >= $max_string_width ){
					break;
				}
			}
			
			// split line to sublines
			$splited_line_array = explode('|', wordwrap( $this->post_title, $max_char_amount, '|') );
			
			$inner_offset = 1;
			foreach( $splited_line_array as $single_line ){
				// title
				$top_offset = $offset_fix * $newheight / 100 + ( ( $font_size_fixed + $bottom_offset ) * 2 ) + $inner_offset * ($new_font_size + $bottom_offset);
				
				$line_space = imagettfbbox( $new_font_size, 0, $this->font_path, $single_line );
				$line_space = $line_space[2] - $line_space[0];
				// left offset
				if( $this->text_position_hor == 'right' ){
					$active_area = $newwidth - (10 * $newwidth / 100) ;			
					$left_offset = $active_area - $line_space;
				}
				
				// left offset
				if( $this->text_position_hor == 'center' ){
					$active_area = $newwidth - (10 * $newwidth / 100) ;			
					$left_offset = ($active_area - $line_space)/2+ (10 * $newwidth / 100) / 2;
				}
				if( @in_array( 'title', $this->fields_to_display ) ){
					imagettftext( $thumb, $new_font_size, 0, $left_offset, $top_offset, $text_color, $this->font_path, $single_line );
				}
				$inner_offset++;
			}
			
			
			//imagecopyresized($thumb, $img_overlap, 0, 0, 0, 0, $newwidth, $newheight, $newwidth, $newheight);
			
			
			// prepare new file name
			$new_file_name = $this->post_id.'_'.$cf_name.'_image.png';
			$cf_name_url = 'image_'.$cf_name;
			$cf_name_path = 'image_path_'.$cf_name;
			
			$upload_dir = wp_upload_dir();
			wp_mkdir_p( $upload_dir['basedir'].'/social_images/'.$this->post_id.'/ads/' );
			
			$new_file_url = $upload_dir['baseurl'].'/social_images/'.$this->post_id.'/ads/'.$new_file_name;
			$new_file_path = $upload_dir['basedir'].'/social_images/'.$this->post_id.'/ads/'.$new_file_name;
			
			update_post_meta( $this->post_id, $cf_name_url, $new_file_url );
			update_post_meta( $this->post_id, $cf_name_path, $new_file_path );
			$this->save_image( $thumb, $new_file_path, $this->image_path );
			//$this->save_image( $thumb, false, $this->image_path );
		}
		
		function imagecreatefromfile( $filename ) {
			ini_set('memory_limit', '400M');
			if (!file_exists($filename)) {
				throw new InvalidArgumentException('File "'.$filename.'" not found.');
			}
			switch ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ))) {
				case 'jpeg':
				case 'jpg':
				return imagecreatefromjpeg($filename);
				break;
				
				case 'png':
				return imagecreatefrompng($filename);
				break;
				
				case 'gif':
				return imagecreatefromgif($filename);
				break;
				
				default:
				throw new InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');
				break;
			}
		}
		
		function save_image( $filename, $path = false, $source_image ) {
			ini_set('memory_limit', '400M');
			
				imagepng( $filename, $path, 0 );
				
			 
			/*
	var_dump( $source_image );
				var_dump( strtolower( pathinfo( $source_image, PATHINFO_EXTENSION )) );
 
				switch ( strtolower( pathinfo( $source_image, PATHINFO_EXTENSION ))) {
				case 'jpeg':
				case 'jpg':
				
				
				
				
				imagejpeg( $filename, $path );
				break;
				
				case 'png':
		 
				imagepng( $filename, $path, 0 );
				break;
				
				case 'gif':
				imagegif( $filename, $path );
				break;
				
				
				}
			*/
		}
		function generate_preview( $size ) {
			foreach( $this->social_sizes as $key => $value ){
				if( $size == $key ){			
					$this->generate_social_banner( $this->social_sizes[$key], $key );
				}
			}
			foreach( $this->banner_sizes as $key => $value ){
				if( $size == $key ){
					$this->generate_ads_banner( $this->banner_sizes[$key], $key );
				}
			}
		}
		
		function download_image( $size  ) {	
			header('Content-Type: image/png');
			
			$cf_name_path = 'image_path_'.$size;
	
			$filename = get_post_meta( $this->post_id, $cf_name_path, true );
			$basename = basename( $filename );
			
			//$size = filesize($filename);
			
	
			//header("Content-Length: " . $size);
			header("Content-Disposition: attachment; filename=" . $basename);
			
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			
			$fp   = fopen($filename, "rb");
			fpassthru($fp);
	 
		}
		function show_image( $size  ) {	
			header('Content-Type: image/png');
			
			$cf_name_path = 'image_path_'.$size;
			$filename = get_post_meta( $this->post_id, $cf_name_path, true );
 
			header('Content-Transfer-Encoding: binary');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			
			$fp   = fopen($filename, "rb");
			fpassthru($fp);
	 
		}
		
	}
	
// processing of image download/ preview
if( $_GET['image_action'] ){
	new imageManipulator(  );
}	

add_Action('init', 'test_image');
function test_image(){
		global $post;

	
		// generate image download
		if( $_GET['process_image'] == 'true' ){
			$image_size = sanitize_text_field( $_GET['size'] );
			$image_id = sanitize_text_field( $_GET['id'] );
			$todo = sanitize_text_field( $_GET['todo'] );
		 
			switch( $todo ){
				case 'download':
					$obj = new imageManipulator( $image_id );
					$obj->generate_preview( $image_size );
					$obj->download_image( $image_size );
					break;
				default:
					$obj = new imageManipulator( $image_id );
					$obj->generate_preview( $image_size );
					$obj->show_image( $image_size );
			}
			die();
		}
		
		
		if( $_GET['image_action'] == 'view' ){
		 
			 header('Content-Type: image/png');
			//$image = new imageManipulator( 481 );
			$image = new imageManipulator( $_GET['id'] );
			$image->generate_social_banner( $_GET['size'], $_GET['size'] );
	 
			//$image->generate_ads_banner( $image->banner_sizes['250x250'] );
			//$image->generate_social_images( );
			//$image->generate_banner_images( );
			die();
		}
		
		if( $_GET['generate_image'] == '1' ){
		 
			 header('Content-Type: image/png');
			//$image = new imageManipulator( 481 );
			$image = new imageManipulator( 5314 );
			//$image->generate_social_banner( $image->social_sizes['facebook'], 'facebook' );
			$image->generate_social_banner( $image->social_sizes['snapchat'], 'snapchat' );
	 
			//$image->generate_ads_banner( $image->banner_sizes['250x250'] );
			//$image->generate_social_images( );
			//$image->generate_banner_images( );
			die();
		}
	}
	
	
add_filter( 'wpseo_opengraph_image', 'is_change_yoast_seo_og_meta' );	
function is_change_yoast_seo_og_meta( $og_image ) {
	global $post;		
	 
	$object = new imageManipulator( $post->ID );	 
			if( $object->use_global == 'no' ){
				if( get_post_meta( $object->post_id, 'rewrite_og_image', true ) == 'yes' ){
				
					$params_hash = $object->generate_parameters_hash();
					$current_post_hash = get_post_meta( $object->post_id, 'param_hash', true );
					
					// check if hash exists if no generate and update
					if( !$current_post_hash || $current_post_hash == '' ){
						$object->generate_social_banner( $object->social_sizes['facebook'], 'facebook');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
						
					}elseif( $current_post_hash != $params_hash ){
						$object->generate_social_banner( $object->social_sizes['facebook'], 'facebook');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
					}
				
					$og_image = get_post_meta( $object->post_id, 'image_facebook', true );
					if( !$og_image || $og_image == '' ){
						$object->generate_social_banner( $object->social_sizes['facebook'], 'facebook');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
					}
				}
			}
			
			if( $object->use_global == 'yes' || !$object->use_global ){
				$config = get_option( $object->locale.'_options');
				if( $config['rewrite_og_image'] == 'yes' ){
				
					$params_hash = $object->generate_parameters_hash();
					$current_post_hash = get_post_meta( $object->post_id, 'param_hash', true );
				
					// check if hash exists if no generate and update
					if( !$current_post_hash || $current_post_hash == '' ){
						$object->generate_social_banner( $object->social_sizes['facebook'], 'facebook');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
						
					}elseif( $current_post_hash != $params_hash ){
						$object->generate_social_banner( $object->social_sizes['facebook'], 'facebook');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
					}
				
					$og_image = get_post_meta( $object->post_id, 'image_facebook', true );
					if( !$og_image || $og_image == '' ){
						$object->generate_social_banner( $object->social_sizes['facebook'], 'facebook');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
					}
				}
			}
				
		 
			
			
			return $og_image;
}
add_filter( 'wpseo_twitter_image', 'is_change_wpseo_twitter_image' );	
function is_change_wpseo_twitter_image( $og_image ) {
	 
	global $post;		
	 
	$object = new imageManipulator( $post->ID );	 
			if( $object->use_global == 'no' ){
				if( get_post_meta( $object->post_id, 'rewrite_og_image', true ) == 'yes' ){
				
					$params_hash = $object->generate_parameters_hash();
					$current_post_hash = get_post_meta( $object->post_id, 'param_hash', true );
					
					// check if hash exists if no generate and update
					if( !$current_post_hash || $current_post_hash == '' ){
						$object->generate_social_banner( $object->social_sizes['twitter'], 'twitter');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
						
					}elseif( $current_post_hash != $params_hash ){
						$object->generate_social_banner( $object->social_sizes['twitter'], 'twitter');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
					}
				
					$og_image = get_post_meta( $object->post_id, 'image_twitter', true );
					if( !$og_image || $og_image == '' ){
						$object->generate_social_banner( $object->social_sizes['twitter'], 'twitter');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
					}
				}
			}
			
			if( $object->use_global == 'yes' || !$object->use_global ){
				$config = get_option( $object->locale.'_options');
				if( $config['rewrite_og_image'] == 'yes' ){
				
					$params_hash = $object->generate_parameters_hash();
					$current_post_hash = get_post_meta( $object->post_id, 'param_hash', true );
				
					// check if hash exists if no generate and update
					if( !$current_post_hash || $current_post_hash == '' ){
						$object->generate_social_banner( $object->social_sizes['twitter'], 'twitter');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
						
					}elseif( $current_post_hash != $params_hash ){
						$object->generate_social_banner( $object->social_sizes['twitter'], 'twitter');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
					}
				
					$og_image = get_post_meta( $object->post_id, 'image_twitter', true );
					if( !$og_image || $og_image == '' ){
						$object->generate_social_banner( $object->social_sizes['twitter'], 'twitter');
						update_post_meta( $object->post_id, 'param_hash', $params_hash );
					}
				}
			}
				
	 	
			return $og_image;
}

?>