<?php 

add_action('wp_ajax_edit_post_field', 'wig_edit_post_field');
add_action('wp_ajax_nopriv_edit_post_field', 'wig_edit_post_field');

function wig_edit_post_field(){
	global $current_user, $wpdb;
	//if( check_ajax_referer( 'save_plan_security_nonce', 'security') ){
	 
		$values = $_POST['values'];
		$post_id = sanitize_text_field( $_POST['post_id'] );
		
		foreach( $values as $single_value ){
		 
			if( is_array( $single_value['value'] ) ){
				update_post_meta( $post_id,  $single_value['name']  ,  $single_value['value']  );
			}else{
				update_post_meta( $post_id,  $single_value['name']  , sanitize_text_field( $single_value['value']  ) );
			}
			
		}
		if( $post_id ){
			$image = new imageManipulator( $post_id );
			if( $image->post_has_image ){
			
				
			
				$image->generate_social_banner( $image->social_sizes['facebook'], 'facebook' );
				$image_url = get_post_meta( $post_id, 'image_facebook', true );
			}
			if( $image_url && $image_url != '' ){
				echo json_encode( array( 'result' => 'success', 'url' => $image_url.'?rand='.rand(10000,99999) ) );
			}else{
				echo json_encode( array( 'result' => 'error' ) );
			}
		}else{
			echo json_encode( array( 'result' => 'error' ) );
		}
		
	//}
	die();
}
 
?>