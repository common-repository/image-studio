jQuery(document).ready(function($){
	
	// btn groups
	$('.btngrp').click(function(){
		var parent = $(this).parents('.btn-group');
		var target = $(this).attr('data-target');
		var value = $(this).attr('data-value');
		$('select[name='+target+']').val( value );
		$('.btngrp', parent).removeClass('active');
		$(this).addClass('active');
	})
	
	// links
	$('#image_preview').change( function() {
		$('#image_view_link').attr('href', $(this).val()+'&todo=view' );
		$('#image_download_link').attr('href', $(this).val()+'&todo=download' );
	
	})
	$('#image_preview').change();
	
	
	// gradiewnt
	$('input[name=use_gradient]').change(function(){
		if( $('input[name=use_gradient]:checked' ).val() == 'yes' ){
			$('#gradient_to_color').fadeIn();
		}
		if( $('input[name=use_gradient]:checked' ).val() == 'no' ){
			$('#gradient_to_color').fadeOut();
		}
	
	})
	$('input[name=use_gradient]').change();
	
	
	// use global 
	$('input[name=use_global]').change(function(){
		if( $('input[name=use_global]:checked' ).val() == 'no' ){
			$('.gray_big_box').fadeIn();
		}
		if( $('input[name=use_global]:checked' ).val() == 'yes' ){
			$('.gray_big_box').fadeOut();
		}
	
	})
	 $('input[name=use_global]').change();
	
		
	$('#slider_opacity_value').keyup(function(){
		if( $(this).val() > 100 ){
			$(this).val(100)
		}
		$( "#slider_opacity" ).slider('value', $(this).val() );
	})
	$( "#slider_opacity" ).slider({
		
		min: 0,
		max: 100,
		value: $('#slider_opacity_value').val( ),
      create: function( ui ) {
	  console.log( ui.value );
        //handle.text( $( this ).slider( "value" ) );
		//$('#slider_opacity_value').val( $( this ).slider( "value" ) );
		
		//$( "#slider_opacity .ui-slider-handle" ).html( $( this ).slider( "value" ) );
		//$('#slider_opacity_value').val( $( this ).slider( "value" ) );
      },
      slide: function( event, ui ) {
       // handle.text( ui.value );
		$('#slider_opacity_value').val( ui.value );
		//$( "#slider_opacity .ui-slider-handle" ).html( $( this ).slider( "value" ) );
      }
    });
	
	$('#slider_rotation_value').keyup(function(){
		if( $(this).val() > 360 ){
			$(this).val(360)
		}
		$( "#slider_rotation" ).slider('value', $(this).val() );
	})
	$( "#slider_rotation" ).slider({
		
		min: 0,
		max: 360,
		value: $('#slider_rotation_value').val( ),
      create: function() {
       // handle.text( $( this ).slider( "value" ) );
		//$('#slider_rotation_value').val( $( this ).slider( "value" ) );
		//$( "#slider_rotation .ui-slider-handle" ).html( $( this ).slider( "value" ) );
		//$('#slider_rotation_value').val( $( this ).slider( "value" ) );
      },
      slide: function( event, ui ) {
       // handle.text( ui.value );
		$('#slider_rotation_value').val( ui.value );
		//$( "#slider_rotation .ui-slider-handle" ).html( $( this ).slider( "value" ) );
      }
    });
	
	
// option listner
	if( $('select[name="use_global"]').length > 0 ){
		setInterval(function(){
			var value = $('select[name="use_global"]').val();
			if( value == 'yes' ){
				$('.hideable').fadeOut();
			}
			if( value == 'no' ){
				$('.hideable').fadeIn();
			}
		}, 500);
	}
	
	//$('body').on('change', '.trace_change', function(){
	$('body').on('click', '#generate_preview', function(){
	
		
	
		var obj = [];
		//$('.trace_change').each(function(){
		//	obj.push({ name: $('.form-control', this).attr('name'), value: $('.form-control', this).val() })
		//})
		
		
		obj.push({ name: "use_global", value: $('input[name=use_global]:checked' ).val() });
		obj.push({ name: "use_gradient", value: $('input[name=use_gradient]:checked' ).val() });
		obj.push({ name: "overlay_color", value: $('input[name=overlay_color]' ).val() });
		obj.push({ name: "overlay_color_to", value: $('input[name=overlay_color_to]' ).val() });
		obj.push({ name: "opacity", value: $('input[name=opacity]' ).val() });
		
		obj.push({ name: "gradient_rotation", value: $('input[name=gradient_rotation]' ).val() });
		
		var fields_to_show = [];
		$('input[name="fields_to_display[]"]:checked' ).each(function(){
			fields_to_show.push( $(this).val() )  ;
		})
		
		obj.push({ name: "fields_to_display" , value: fields_to_show });
		
		
		obj.push({ name: "font_color", value: $('input[name=font_color]' ).val() });
		obj.push({ name: "font_size", value: $('select[name=font_size]' ).val() });
		obj.push({ name: "font_family", value: $('select[name=font_family]' ).val() });
		obj.push({ name: "text_position", value: $('select[name=text_position]' ).val() });
		obj.push({ name: "text_position_hor", value: $('select[name=text_position_hor]' ).val() });
		
		 
		 console.log( obj );
		 
		var data = {
			values  : obj,
			post_id  : $('#post_ID').val(),
			action : 'edit_post_field'
		}
	 
		jQuery.ajax({url: wig_local_data.ajaxurl,
				type: 'POST',
				data: data,            
				beforeSend: function(msg){
						jQuery('body').prepend('<div class="custom_loader"></div>');;
					},
					success: function(msg){
						
						
						console.log( msg );
						
						jQuery('.custom_loader').replaceWith('');
						var obj = jQuery.parseJSON( msg );
						
						console.log( obj );
					 
						if( obj.result == 'success'){
			 
							$('.ajax_img_preview, .preview_block').html('');
							$('.ajax_img_preview, .preview_block').html('<img src="'+obj.url+'" class="facebook_dyn_preview">');
						}else{
							 $('.ajax_img_preview, .preview_block').html('<div class="alert alert-info mb-0">Sorry, you need to add featured image to post and save it before preview will work.</div>');
						}
						 
					} , 
					error:  function(msg) {
									
					}          
			});
	})
	
	
});