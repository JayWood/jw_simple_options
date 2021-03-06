/* Javascript Document */

jQuery(document).ready(function($) {
	var addRow = $('.addrow'),
		delRow = $('.removerow'),
		curNum;
	
	addRow.click(function(e){
		var theID = $(this).attr('data_id'),
			curBlock = $('table#'+theID+' tbody'),
			newRow = $('.data_row.'+theID).last().clone(true);
			
		if(curNum == null || curNum < 1){
			curNum = parseInt(curBlock.children('tr').length, 10);
		}
		
		curNum++;
		newRow.attr('id', 'data_row_'+curNum);
		newRow.children('td').each(function(index){
			if( $(this).has('input') ){
				var name = $(this).children('input').first().attr('name');
				if(name != undefined){
					var nName = name.replace(/\[[0-9]\]/, '['+curNum+']');
					//console.log(nName);
				}
				$(this).children('input').first().attr('name', nName);
				$(this).children('input').first().attr('value', '');
				$(this).children('.removerow').attr('id', (curNum));
				$(this).children('.removerow').addClass(theID);
			}
		});
		
		curBlock.append(newRow);
	});
	
	
	delRow.click(function(e){
		var id = $(this).attr('id');
		var blockID = $(this).attr('curBlock');
		var curBlock = $('table#'+blockID+' tbody');
		var len = curBlock.children('tr').length;
		console.log(len);
		if( len != 1 ){
			$('#data_row_'+id+'.'+blockID).fadeOut(250,function(e){
				$('#data_row_'+id+'.'+blockID).remove();
			})
		}
	});
	
	$('.color_select').spectrum({
		showButtons:	 false,
		showInput:		 true,
		preferredFormat: "hex6"
	});
	
	// Uploading files
	var file_frame, uploadID;
	
	$('.upload_image_button').live('click', function( event ){
		event.preventDefault();
		uploadID = $(this).attr('data-id');
		console.log(event);
		
		if ( file_frame ) {
		  file_frame.open();
		  return;
		}
		
		file_frame = wp.media.frames.file_frame = wp.media({
		  title: jQuery( this ).data( 'uploader_title' ),
		  button: {
			text: jQuery( this ).data( 'uploader_button_text' ),
		  },
		  multiple: false
		});
		
		file_frame.on( 'select', function() {
		  attachment = file_frame.state().get('selection').first().toJSON();
		  
		  $('#'+uploadID).val(attachment.url);	  
		});
		
		file_frame.open();
	});
	//END UPLOAD FUNCTIONS
	
	
		
});
