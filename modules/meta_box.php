<?php 
if( !class_exists( 'vooMetaBoxImage' ) ){
	class vooMetaBoxImage{
		
		private $metabox_parameters = null;
		private $fields_parameters = null;
		private $locale = null;
		private $data_html = null;
		
		function __construct( $metabox_parameters , $fields_parameters, $locale = false){
			$this->metabox_parameters = $metabox_parameters;
			$this->fields_parameters = $fields_parameters;
			$this->locale = $locale;
 
			add_action( 'add_meta_boxes', array( $this, 'add_custom_box' ) );
			add_action( 'save_post', array( $this, 'save_postdata' ) );
		}
		
		function add_custom_box(){
			add_meta_box( 
				'custom_meta_editor_'.rand( 100, 999 ),
				$this->metabox_parameters['title'],
				array( $this, 'custom_meta_editor' ),
				$this->metabox_parameters['post_type'] , 
				$this->metabox_parameters['position'], 
				$this->metabox_parameters['place']
			);
		}
		function custom_meta_editor(){
			global $post;
			
			$out = '

			<div class="tw-bs4">
				<div class="form-horizontal ">';
			
			foreach( $this->fields_parameters as $single_field){
			 
				switch( $single_field['type'] ){
					
					/*
					case "shortcode": 
					$out .= '<div class="form-group '.$single_field['parent_style'].' '.$single_field['parent_style'].'">  
						<label class="control-label" for="input01">'.$single_field['title'].'</label>  
						 
						  <input type="text" class="form-control input-xlarge" name="'.$single_field['name'].'" id="'.$single_field['name'].'" 
						  value="['.$single_field['name'].' id=\''.$post->ID.'\']"
						  
						  >  
						  
					  </div> ';	
					break;
					
					
					case "textarea":
					$out .= '<div class="form-group '.$single_field['parent_style'].'">  
						<label class="control-label" for="input01">'.$single_field['title'].'</label>  
						 
						  <textarea type="text" class="form-control input-xlarge" style="'.$single_field['style'].'" name="'.$single_field['name'].'" id="'.$single_field['name'].'" >'.htmlentities( get_post_meta( $post->ID, $single_field['name'], true ) ).'</textarea>  
						  
					  </div> ';	
					break;
					case "text":
					$out .= '<div class="form-group '.$single_field['parent_style'].'">  
						<label class="control-label" for="input01">'.$single_field['title'].'</label>  
						 
						  <input type="text" class="form-control input-xlarge" name="'.$single_field['name'].'" id="'.$single_field['name'].'" value="'.get_post_meta( $post->ID, $single_field['name'], true ).'">  
						  
					  </div> ';	
					break;
					case "colorpicker":
					$out .= '<div class="form-group '.$single_field['parent_style'].'">  
						<label class="control-label" for="input01">'.$single_field['title'].'</label>  
				 
						  <div class="dropdown">
								<input type="text" class="form-control input-xlarge jscolor" name="'.$single_field['name'].'" id="'.$single_field['name'].'" value="'.( get_post_meta( $post->ID, $single_field['name'], true ) ? get_post_meta( $post->ID, $single_field['name'], true ) : $single_field['default'] ).'">
								<div class="abs_right"><img src="'.plugins_url( '/images/htmlcolors.gif', __FILE__ ).'" /></div>
							</div>
						  
						  
					  </div> ';	
					break;
					case "checkbox":
					$out .= '<div class="form-group '.$single_field['parent_style'].'">  
						<label class="control-label" for="input01">'.$single_field['title'].'</label>  
					  
						  <input type="checkbox" class="form-control "  name="'.$single_field['name'].'" id="'.$single_field['name'].'" value="on" '.( get_post_meta( $post->ID, $single_field['name'], true ) == 'on' ? ' checked ' : '' ).' >  
						  
					  </div> ';	
					break;
					case "select":
					$out .= '<div class="form-group '.$single_field['parent_style'].'">  
						<label class="control-label" for="input01">'.$single_field['title'].'</label>';

							$out .= '<select class="form-control " name="'.$single_field['name'].'">';
							
							if( get_post_meta( $post->ID, $single_field['name'], true ) ){
								$values = get_post_meta( $post->ID, $single_field['name'], true );
							}else{
								$values = $single_field['default'];
							}
							
							foreach( $single_field['value'] as $key => $value ){
								$out .= '<option '.( $values == $key ? ' selected ' : '' ).' value="'.$key.'">'.$value;
							}
							$out .= '</select>';
						 
					$out .= '
						
					  </div> ';	
					break;
					
					case "wide_editor":
					$out .= '<div class="form-group '.$single_field['parent_style'].'">  
						<label class="control-label" for="input01">'.$single_field['title'].'</label>
						<div class="form-control">
						';  
						 
						ob_start();
						wp_editor( get_post_meta( $post->ID, $single_field['name'], true ), $single_field['name'] );
						$editor_contents = ob_get_clean();	
						
						$out .= $editor_contents;  
					$out .= '
						</div>
					  </div> ';	 
					 
					break;
					case "custom_text":
					$url = str_replace( '%post_id%', $post->ID,  $single_field['text']);
					$out .= '<div class="form-group '.$single_field['parent_style'].'">  
						<label class="control-label" for="input01">&nbsp;</label>
						<div class="form-control">
						'.$url.'
						</div>
					  </div> ';	 
					 
					break;
	 
					case "file":
						$out .= '
						<div class="form-group '.$single_field['parent_style'].'">  
							<label class="control-label" for="'.$single_field['id'].'">'.$single_field['title'].'</label>  
				 
							<input type="file" class="form-control-file '.$single_field['class'].'" name="'.$single_field['name'].''.( $single_field['multi'] ? '[]' : '' ).'" id="'.$single_field['id'].'" '.( $single_field['multi'] ? ' multiple ' : '' ).' >
							  
							  <p class="help-block">'.$single_field['sub_text'].'</p> 
						 
						  </div> 
						';
					break;
					case "mediafile_single":
					
					// get attachment src
					
					$attach_url = wp_get_attachment_image_src( get_post_meta( $post->ID, $single_field['name'], true ) );
					
					$out .= '<div class="form-group '.$single_field['parent_style'].' media_upload_block">  
						<label class="control-label" for="input01">'.$single_field['title'].'</label>  
						 
						  <input type="hidden" class="form-control input-xlarge mediafile_single item_id" name="'.$single_field['name'].'" id="'.$single_field['name'].'" value="'.get_post_meta( $post->ID, $single_field['name'], true ).'"> 
						  
					 
						  <input type="button" class="btn btn-success upload_image" data-single="1" value="'.$single_field['upload_text'].'" />
						  <div class="image_preview">'.( $attach_url[0] ? '<img src="'.$attach_url[0].'" />' : '' ).'</div>
					  </div> ';	
					break;
					
					case "mediafile_multi":
					
					// get attachment src
					
					$attach_url = wp_get_attachment_image_src( get_post_meta( $post->ID, $single_field['name'], true ) );
					
					$out .= '<div class="form-group '.$single_field['parent_style'].' media_upload_block">  
						<label class="control-label" for="input01">'.$single_field['title'].'</label>  
						 
						  <input type="hidden" class="form-control input-xlarge mediafile_single item_id" name="'.$single_field['name'].'" id="'.$single_field['name'].'" value=""> 
						  
					 
						  <input type="button" class="btn btn-success upload_image" data-single="0" value="'.$single_field['upload_text'].'" />
						 
						 </div> ';	
					break;
					
					
					
					
					case "hidden":
					$out .= '
						  <input type="hidden"  name="'.$single_field['name'].'" id="'.$single_field['name'].'" value="'.get_post_meta( $post->ID, $single_field['name'], true ).'">';	
					break;
					case "checkbox":
					$out .= '<div class="form-group '.$single_field['parent_style'].'">  
						<label class="control-label" for="input01">'.$single_field['title'].'</label>  
					  
						  <input type="checkbox" class="   "  name="'.$single_field['name'].'" id="'.$single_field['name'].'" value="on" '.( get_post_meta( $post->ID, $single_field['name'], true ) == 'on' ? ' checked ' : '' ).' >  
						  
					  </div> ';	
					break;
					
					case "multiselect":
						$out .= '
						<div class="form-group '.$single_field['parent_style'].'">  
							<label class="control-label" for="'.$single_field['id'].'">'.$single_field['title'].'</label>  
							 
							  <select  multiple="multiple" style="'.$single_field['style'].'" class="form-control '.$single_field['class'].'" name="'.$single_field['name'].'[]" id="'.$single_field['id'].'">' ; 
							  
							  $values = ( get_post_meta( $post->ID, $single_field['name'], true ) ? get_post_meta( $post->ID, $single_field['name'], true ) : $single_field['default'] );
							  
							  foreach( $single_field['value'] as $k => $v ){
								  $out .= '<option value="'.$k.'" '.( @in_array( $k, $values )   ? ' selected ' : ' ' ).' >'.$v.'</option> ';
							  }
						$out .= '		
							  </select>  
							  <p class="help-block">'.$single_field['subtext'].'</p> 
							 
						  </div>  
						';
					break;
					
					*/
					
					case "big_input_block":
						$out .= '
						<div class="big_input_block container1">
							<div class="ov_hidden">
								<div class="col-6-custom">
									<div class="row  mb-3 mt-3">
										<div class=" col-md-6 col-sm-12 col_100_1100">
											<div class="row">
												<div class="col-6">
													<label>Use Global Settings</label>
												</div>
												<div class="row col-6">';
													foreach( array( 'no' => 'No', 'yes' => 'Yes'   ) as $key => $value ){
														$out .= '
														<div class="col-6">
														<label><input type="radio" name="use_global" ';
													 
														if( $key == 'no' ){
															if( get_post_meta( $post->ID, 'use_global', true ) == 'no'      ){
																$out .= ' checked ';
															}
														}
														if( $key == 'yes' ){
															if( get_post_meta( $post->ID, 'use_global', true ) != 'no'   ){														 
																$out .= ' checked ';																												 
															}
														}
														
														$out .= 'class="mr-2" value="'.$key.'">'.$value.'</label>
														</div>
														';
													}
													$out .= ' 
												</div>
											</div>
										</div>
										<div class="col-md-6 col-sm-12 col_100_1100">
											<div class="row">
												<div class="col-6">
													<label>Rewrite OG:Image Tag?</label>
												</div>
												<div class="row col-6">';
													foreach( array( 'no' => 'No', 'yes' => 'Yes' ) as $key => $value ){
														$out .= '
														<div class="col-6">
														<label><input type="radio" name="rewrite_og_image"';
														
														if( 
														get_post_meta( $post->ID, 'rewrite_og_image', true ) && 
														get_post_meta( $post->ID, 'rewrite_og_image', true ) != '' && 
														get_post_meta( $post->ID, 'rewrite_og_image', true ) == $key ){
															$out .= ' checked ';
														}elseif( $key == 'no' ) {
															$out .= ' checked ';														
														}
														
														$out .= 'class="mr-2" value="'.$key.'">'.$value.'</label>
														</div>
														';
													}
													$out .= ' 
												</div>
											</div>
										</div>
									</div>
									<div class="row gray_big_box mb-3">
										<div class=" col-md-6 col-sm-12 col_100_1100">
											<div class="row  mb-3">
												 
													<div class="col-12">
														<label>Overlay Gradient</label>
													</div>
													<div class="row col-6">';
															foreach( array( 'no' => 'No', 'yes' => 'Yes'   ) as $key => $value ){
																$out .= '
																<div class="col-6">
																<label><input type="radio"  name="use_gradient" ';
																
																if( 
																	get_post_meta( $post->ID, 'use_gradient', true ) && 
																	get_post_meta( $post->ID, 'use_gradient', true ) != '' && 
																	get_post_meta( $post->ID, 'use_gradient', true ) == $key ){
																		$out .= ' checked ';
																	}elseif( $key == 'no' ) {
																		$out .= ' checked ';														
																	}
																
																$out .= 'class="mr-2" value="'.$key.'">'.$value.'</label>
																</div>
																';
															}
														$out .= ' 
													</div>
													
												 
											</div>
											<div class="row  mb-3">
												<div class="col-6">
													<label>Color 1</label>
													<input class="form-control jscolor trace_change" name="overlay_color" value="'.( 
														get_post_meta( $post->ID, 'overlay_color', true ) && 
																	get_post_meta( $post->ID, 'overlay_color', true ) != '' ?
																	get_post_meta( $post->ID, 'overlay_color', true ) :
																	'FFFFFF'
													).'">
												</div>
												<div class="col-6" id="gradient_to_color">
													<label>Color 2</label>
													<input class="form-control jscolor trace_change" name="overlay_color_to" value="'.( 
														get_post_meta( $post->ID, 'overlay_color_to', true ) && 
																	get_post_meta( $post->ID, 'overlay_color_to', true ) != '' ?
																	get_post_meta( $post->ID, 'overlay_color_to', true ) :
																	'000000'
													).'" >
												</div>
											</div>
										</div>
										<div class="col-md-6 col-sm-12">
											<div class="row  mb-3">
												<div class="col-12">
													<label>Overlay Opacity</label>
													<div class="row ">
														<div class="col-9">
															<div id="slider_opacity" class="mt-3" ></div>
														</div>
														<div class="col-3">
															<input class="form-control text-center trace_change" name="opacity" id="slider_opacity_value" value="'.( 
														get_post_meta( $post->ID, 'opacity', true ) && 
																	get_post_meta( $post->ID, 'opacity', true ) != '' ?
																	get_post_meta( $post->ID, 'opacity', true ) :
																	'50'
													).'">
														</div>
													</div>
												</div>
											</div>
											<div class="row  mb-3">
												<div class="col-12">
													<label>Gradient Angle</label>
												 
													<div class="row ">
														<div class="col-9">
															<div id="slider_rotation"   class="mt-3" ></div>
														</div>
														<div class="col-3">
															<input class="form-control  text-center trace_change" name="gradient_rotation" id="slider_rotation_value" value="'.( 
														get_post_meta( $post->ID, 'gradient_rotation', true ) && 
																	get_post_meta( $post->ID, 'gradient_rotation', true ) != '' ?
																	get_post_meta( $post->ID, 'gradient_rotation', true ) :
																	'0'
													).'">
														</div>
													</div>
													
												</div>
											</div>
										</div>
									 
									</div>
									
									<div class="row gray_big_box mb-3">
										<div class="col-md-4 col-sm-12 col_100_1100">
											<label>Include Fields</label>
											<div class="row">
												<div class="col-12">
													<label>
													
													<input type="hidden" name="fields_to_display[]" value="" />
													<input type="checkbox" class="mr-2 trace_change" name="fields_to_display[]" ';

													if( in_array( 'category', (array)get_post_meta( $post->ID, 'fields_to_display', true ) ) ){
														$out .=  ' checked ';
													}
													if(  !get_post_meta( $post->ID, 'fields_to_display', true ) ||  get_post_meta( $post->ID, 'fields_to_display', true ) == ''  ){
														$out .=  ' checked ';
													}
													$out .= ' value="category" >Category</label>
												</div>
												<div class="col-12">
													<label><input type="checkbox" class="mr-2 trace_change" name="fields_to_display[]" ';
													if( in_array( 'date', (array)get_post_meta( $post->ID, 'fields_to_display', true ) ) ){
														$out .=  ' checked ';
													}
													
													if(  !get_post_meta( $post->ID, 'fields_to_display', true ) ||  get_post_meta( $post->ID, 'fields_to_display', true ) == ''  ){
														$out .=  ' checked ';
													}
													
													$out .= '  value="date" >Date</label>
												</div>
												<div class="col-12">
													<label><input type="checkbox" class="mr-2 trace_change" name="fields_to_display[]" ';
													if( in_array( 'title', (array)get_post_meta( $post->ID, 'fields_to_display', true ) ) ){
														$out .=  ' checked ';
													}
													if(  !get_post_meta( $post->ID, 'fields_to_display', true ) ||  get_post_meta( $post->ID, 'fields_to_display', true ) == ''  ){
														$out .=  ' checked ';
													}
													$out .= '  value="title" >Title</label>
												</div>
											</div>
										</div>
										
										<div class="col-md-8 col-sm-12 col_100_1100">
											<div class="row  mb-3">
												<div class="col-3">
													<label>Color</label>
													<input  class="form-control jscolor trace_change" name="font_color" value="'.( 
														get_post_meta( $post->ID, 'font_color', true ) && 
																	get_post_meta( $post->ID, 'font_color', true ) != '' ?
																	get_post_meta( $post->ID, 'font_color', true ) :
																	'000000'
													).'" >
												</div>
												<div class="col-3">
													<label>Size</label>
													<select  class="form-control trace_change" name="font_size">';												
													for( $i=10; $i<=60; $i++ ){
														$out .= '<option value="'.$i.'"  ';
														if( get_post_meta( $post->ID, 'font_size', true ) == $i ){
															$out .= ' selected ';
														}
														
														if( !get_post_meta( $post->ID, 'font_size', true ) || get_post_meta( $post->ID, 'font_size', true ) == '' ){
															if( $i == 40 ){
																$out .= ' selected ';
															}
														}
														
														$out .= ' >'.$i;
													}
													$out .= '</select>
												</div>
												<div class="col-6">
													<label>Font</label>
													<select name="font_family" class="form-control trace_change">';
													$list_of_fonts = array( 
														'arial.ttf' => 'Arial' ,
														'trench100free.ttf' => 'trench100free' 
													);
													foreach( $list_of_fonts as $key => $value ){
														$out .= '<option value="'.$key.'"  '. (
															get_post_meta( $post->ID, 'font_color', true ) == $key ? 
															' selected ' : ''
															
														) .' >'.$value;
													}
													
													$out .= '</select>
												</div>
											</div>
											<div class="row  mb-3">
												<div class="col-6">
													<label>Vertical Align</label> 
													<select class="form-control trace_change" name="text_position"  style="display:none;">';
													foreach( array( 'top' => 'Top', 'center' => 'Center', 'bottom' => 'Bottom' ) as $key => $value ){
														$out .= '<option '.( get_post_meta( $post->ID, 'text_position', true ) == $key ? ' selected ' : '' ).' value="'.$key.'">'.$value;
													}
													$out .= '</select>
							 		
													<div class="btn-group" role="group" aria-label="Basic example">
														<button type="button" data-target="text_position" data-value="top" class="btn btn-light btngrp '.( get_post_meta( $post->ID, 'text_position', true ) == 'top' ? ' active ' : '' ).'    '.( !get_post_meta( $post->ID, 'text_position', true ) || get_post_meta( $post->ID, 'text_position', true ) == '' ? ' active ' : '' ).' "><img src="'.plugins_url( '/images/align-top.png', __FILE__ ).'" /></button>
														<button type="button" data-target="text_position" data-value="center" class="btn btn-light btngrp '.( get_post_meta( $post->ID, 'text_position', true ) == 'center' ? ' active ' : '' ).'"><img src="'.plugins_url( '/images/align-middle.png', __FILE__ ).'" /></button>
														<button type="button" data-target="text_position" data-value="bottom" class="btn btn-light btngrp '.( get_post_meta( $post->ID, 'text_position', true ) == 'bottom' ? ' active ' : '' ).'"><img src="'.plugins_url( '/images/align-bottom.png', __FILE__ ).'" /></button>														
													</div>
													
												</div>
												
												<div class="col-6">
													<label>Horizontal Align</label> 
													<select class="form-control trace_change" name="text_position_hor" style="display:none;">';
													foreach( array( 'left' => 'Left', 'center' => 'Center', 'right' => 'Right' ) as $key => $value ){
														$out .= '<option '.( get_post_meta( $post->ID, 'text_position_hor', true ) == $key ? ' selected ' : '' ).' value="'.$key.'">'.$value;
													}
													$out .= '</select>
													
													<div class="btn-group" role="group" aria-label="Basic example">
														<button type="button" data-target="text_position_hor" data-value="left" class="btn btn-light btngrp '.( get_post_meta( $post->ID, 'text_position_hor', true ) == 'left' ? ' active ' : '' ).'    '.( !get_post_meta( $post->ID, 'text_position_hor', true ) || get_post_meta( $post->ID, 'text_position_hor', true ) == '' ? ' active ' : '' ).'"><i class="fa fa-align-left" aria-hidden="true"></i></button>
														<button type="button" data-target="text_position_hor" data-value="center" class="btn btn-light btngrp '.( get_post_meta( $post->ID, 'text_position_hor', true ) == 'center' ? ' active ' : '' ).'"><i class="fa fa-align-center" aria-hidden="true"></i></button>
														<button type="button" data-target="text_position_hor" data-value="right" class="btn btn-light btngrp '.( get_post_meta( $post->ID, 'text_position_hor', true ) == 'right' ? ' active ' : '' ).'"><i class="fa fa-align-right" aria-hidden="true"></i></button>
														
													</div>
													 
												</div>
											</div>
											
										</div>
										
									</div>
								
								</div>
								<div class="col-6-custom">
									<label>Preview and Download Images</label>
									<div class="preview_block">';
									
									$preview_url = get_post_meta( $post->ID, 'image_facebook', true );
									if( $preview_url && $preview_url != '' ){
										$out .= '<img src="'.$preview_url.'?'.md5(microtime()).'" />';
									}else{
										$image = new imageManipulator( $post->ID );
										
										if( $this->post_has_image ){
											$image->generate_social_banner( $image->social_sizes['facebook'], 'facebook' );
											$preview_url = get_post_meta( $post->ID, 'image_facebook', true );
											$out .= '<img src="'.$preview_url.'?'.md5(microtime()).'" />';
										}else{
											$out .= '<div class="alert alert-info mb-0">Sorry, you need to add featured image to post and save it before preview will work.</div>';
										}
									}
									
									$out .= '</div>
									<div class="generate_block text-center">
										<button type="button" class="btn btn-success" id="generate_preview">Generate Preview</button>
									</div>
									<div class="row  mb-3">
										<div class="col-4">
											<a  class="btn btn-success btn-sm" href="'.get_option('home').'?image_action=social_pack&id='.$post->ID.'">Download Social Pack</a>
										</div>
										
										<div class="col-4">
											<a  class="btn btn-info btn-sm" href="'.get_option('home').'?image_action=ads_pack&id='.$post->ID.'">Download Ads Pack</a>
										</div>
										
										<div class="col-4">
											<a  class="btn btn-warning btn-sm" href="'.get_option('home').'?image_action=all_pack&id='.$post->ID.'">Download All</a>
										</div>
									</div>
									<div class="row  mb-3">
										<div class="col-12">
											<label>View or Download Individual Images:</label>
										</div>
										<div class="col-12">
											<div class="row">
												<div class="col-8">
													<select class="form-control" id="image_preview">';
													
													$image = new imageManipulator( $post->ID );
													
													$out .= '<optgroup label="Social Images">';
													foreach( $image->social_sizes as $key => $value ){
														$out .= '<option value="'.site_url().'?process_image=true&size='.$key.'&id='.$post->ID.'">'.$value['name'];
													}
													$out .= '</optgroup>';
													$out .= '<optgroup label="Ad Images">';
													foreach( $image->banner_sizes as $key => $value ){
														$out .= '<option value="'.site_url().'?process_image=true&size='.$key.'&id='.$post->ID.'">'.$key;
													}
													$out .= '</optgroup>';
													$out .= '</select>
												</div>
												<div class="col-4">
													<a href="" class="btn btn-info btn-sm"   target="_blank" id="image_view_link">View</a>
													<a href="" class="btn btn-warning btn-sm" target="_blank" download="download" id="image_download_link">Download</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div> 
						 
						';
					break;
					/*
					case "image_preview_block":
						$out .= '
						<div class="form-group '.$single_field['parent_style'].'">  
							<label class="control-label" for="'.$single_field['id'].'">'.$single_field['title'].'</label>';  
							 
							 $image = new imageManipulator( $post->ID );
							 
							 $out .= '
							 
							 <div class="ajax_img_preview"></div>
							 
							 <div class="'.( $image->post_has_image ? ' row ' : '' ).' image_blocks">';
							 
							 	
							 if( $image->post_has_image ){
								 $out .= '
									<div class="col-4 text-left">
										<div class="image_preview_title">
											Social Images
										</div>
									';
									$out .= $image->generate_social_images_array_output();
								
								$out .= '
									</div>';
								
								$out .= '
									<div class="col-4 text-left">
									<div class="image_preview_title">
										Ads Images
									</div>
									';
			
									$out .= $image->generate_banner_images_array_output();

									 
								$out .= '
									
									</div>';
									
								$out .= '
									<div class="col-4 text-left">
										
										<div class="image_preview_title text-center">
											Images
										</div>
										
											<div class=" text-center">
												<a  class="btn btn-success btn-sm" href="'.get_option('home').'?image_action=social_pack&id='.$post->ID.'">Download Social Pack</a>
											</div>
											<br/>
											<div class=" text-center">
												<a  class="btn btn-info btn-sm" href="'.get_option('home').'?image_action=ads_pack&id='.$post->ID.'">Download Ads Pack</a>
											</div>
											<br/>
											<div class=" text-center">
												<a  class="btn btn-warning btn-sm" href="'.get_option('home').'?image_action=all_pack&id='.$post->ID.'">Download All</a>
											</div>
										
									</div>';
							 }else{
								$out .= '
								<div class="alert alert-info">
									No images generated. Please, add featured image and save post.
								</div>
								';
							 }
							 
							$out .= '
							 </div> 
							  <p class="help-block">'.$single_field['subtext'].'</p> 
							 
						  </div>  
						';
						break;
					*/
				}
			}		
			
					
					
			$out .= '
					</div>	
				</div>
				';	
			$this->data_html = $out;
			 
			$this->echo_data();
		}
		
		function echo_data(){
			echo $this->data_html;
		}
		
		function save_postdata( $post_id ) {
			global $current_user; 
			 if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
				  return;

			  if ( 'page' == $_POST['post_type'] ) 
			  {
				if ( !current_user_can( 'edit_page', $post_id ) )
					return;
			  }
			  else
			  {
				if ( !current_user_can( 'edit_post', $post_id ) )
					return;
			  }
			  /// User editotions

				if(  in_array( get_post_type($post_id),  $this->metabox_parameters['post_type'] ) ){
					foreach( $this->fields_parameters as $single_parameter ){
					
						if( is_array( $_POST[$single_parameter['name']] ) ){
							update_post_meta( $post_id, $single_parameter['name'],  $_POST[$single_parameter['name']]  );
						}else{
							update_post_meta( $post_id, $single_parameter['name'], sanitize_text_field( $_POST[$single_parameter['name']] ) );
						}
					
					}
					// update images
					/*
					if( $_POST['use_global'] ){
						$image = new imageManipulator( $post_id );
						 
						if( $image->post_has_image ){
							$image->generate_social_images( );
							$image->generate_banner_images( );
						}
					}
					*/
				}
				
			}
	}
}

 
 
add_Action('admin_init',  function (){
	 global $locale;
	 
	 
	 $all_taxonomies = get_taxonomies();
	 
	 $out_categories = array();
	 
	 
	 if( count($all_taxonomies) > 0 ){
		foreach( $all_taxonomies as $key => $value ) {
			$all_cats =  get_terms( array( 'taxonomy' => $key, 'hide_empty' => 0 ) ) ;
			if( count($all_cats) > 0 ){
				$out_categories[0] = __('Select Term', $locale); 
				foreach( $all_cats as $single_cat ){
					$out_categories[$single_cat->term_id] = $single_cat->name.' ('.$value.')';
				}
			}
		}
		 
	 }
	 
	 // post types
	 $all_post_types = get_post_types( array( 'publicly_queryable' => 'true' ) );
	 
	 $out_post_types = array();
	 foreach( $all_post_types as  $key => $value ){
		 $out_post_types[] = $key;
	 }
	 
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
	 
	 // font size
	 $rotation_angle = array();
	 for( $i=0; $i<=360; $i = $i + 30 ){
		$rotation_angle[$i] = $i;
	 }
	 
	 $meta_box = array(
		'title' => 'Image Studio',
		'post_type' => $out_post_types,
		'position' => 'advanced',
		'place' => 'high'
	);
	$fields_parameters = array(
 
 
 
		array(
			'type' => 'big_input_block',
			'title' => 'Use Global Settings',
			'name' => 'use_global',
			'value' => array( 'yes' => 'Yes', 'no' => 'No' )
		),
 
 
		array(
			'type' => 'select',
			'title' => 'Use Global Settings',
			'name' => 'use_global',
			'value' => array( 'yes' => 'Yes', 'no' => 'No' )
		),
		array(
			'type' => 'select',
			'title' => 'use_gradient',
			'name' => 'use_gradient',
			'value' => array( 'yes' => 'Yes', 'no' => 'No' )
		),
 
		array(
			'type' => 'colorpicker',
			'title' => 'Overlay Color ( Gradient From )',
			'name' => 'overlay_color',
			'parent_style' => 'hideable trace_change',
			'default' => '000000'
		),
		
		
		array(
			'type' => 'colorpicker',
			'title' => 'Overlay Color ( Gradient To )',
			'name' => 'overlay_color_to',
			'parent_style' => 'hideable trace_change',
			'default' => '000000'
		),
		
		array(
			'type' => 'select',
			'title' => 'Opacity',
			'name' => 'opacity',
			'value' => $opacity,
			'parent_style' => 'hideable trace_change',
			'default' => '50'
		),
		array(
			'type' => 'select',
			'title' => 'Gradient Rotation',
			'name' => 'gradient_rotation',
			'value' => $rotation_angle,
			'parent_style' => 'hideable trace_change',
			'default' => '0'
		),
		
		array(
			'type' => 'colorpicker',
			'title' => 'Font Color',
			'name' => 'font_color',
			'parent_style' => 'hideable trace_change',
			'default' => 'FFFFFF'
		),
		
		array(
			'type' => 'select',
			'title' => 'Font Family',
			'name' => 'font_family',
			'value' => array( 
				'arial.ttf' => 'Arial' ,
				'trench100free.ttf' => 'trench100free' 
			),
			'parent_style' => 'hideable trace_change',
			'default' => 'arial.ttf'
		),
		array(
			'type' => 'select',
			'title' => 'Font Size',
			'name' => 'font_size',
			'value' => $font_sizes,
			'parent_style' => 'hideable trace_change',
			'default' => '35'
		),
		/*
		array(
			'type' => 'select',
			'title' => 'Font Style',
			'name' => 'font_style',
			'value' => array( 'normal' => 'Normal', 'italic' => 'Italic' ),
			'parent_style' => 'hideable'
		),
		*/
		array(
			'type' => 'select',
			'title' => 'Text Position',
			'name' => 'text_position',
			'value' => array( 'top' => 'Top', 'center' => 'Center', 'bottom' => 'Bottom' ),
			'parent_style' => 'hideable trace_change'
		),	
		array(
			'type' => 'select',
			'title' => 'Text Position',
			'name' => 'text_position_hor',
			 
		),
		array(
			'type' => 'multiselect',
			'title' => 'Fields to display',
			'name' => 'fields_to_display',
			'value' => array( 'category' => 'Category', 'title' => 'Title', 'date' => 'Date' ),
			'parent_style' => 'hideable trace_change',
			'default' => array( 'title', 'category', 'date' )
		),
		array(
			'type' => 'select',
			'title' => 'Rewrite OG:IMAGE tag',
			'name' => 'rewrite_og_image',
			'value' => array( 'no' => 'No', 'yes' => 'Yes' ),
			'parent_style' => 'hideable'
		),

		array(
			'type' => 'image_preview_block',
			'title' => 'Images Preview',
		 
	 
		),
		
 
	 
	);		
	$new_metabox = new vooMetaBoxImage( $meta_box, $fields_parameters, $locale); 
	 
 } );
 

?>