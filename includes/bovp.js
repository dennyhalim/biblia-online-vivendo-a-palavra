jQuery(document).ready(function(){

	// Capther select

	jQuery('#bovp_chapter').hide();


	jQuery('#bovp_book').change(function(){

		jQuery('#bovp_chapter').empty();

		var num_pages =  jQuery('#bovp_book option:selected').attr('num_pages');

		if(num_pages != 0) {
		
			var itens = []; 

			for(i = 1; i <= num_pages; i++) {itens.push(i);}


			jQuery('#bovp_chapter').append('<option value="0" >All</option>');


			jQuery.each(itens, function(index, value) {

			  jQuery('#bovp_chapter').append('<option value="'+value+'" >'+value+'</option>');


			});

			jQuery('#bovp_chapter').show();

		} 

		jQuery('#bovp_chapter:empty').hide();
		
			
		

    });
					




});