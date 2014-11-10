var bovpJQuery = jQuery.noConflict(); 

bovpJQuery().ready(function() {
	
// Font-size
    var default_size = bovpJQuery(".bovp_text").css("font-size");

    var current_size = parseInt(getCookie("current_size"));

    if(current_size > 0) {bovpJQuery(".bovp_text").css("font-size", current_size);}

        bovpJQuery(".increase").click(function(){

                var size = bovpJQuery(".bovp_text").css("font-size");
                var newSize = parseInt(size.substr(0,2));
                if(newSize != 22){
                    var newSize = newSize + 2;
                    bovpJQuery(".bovp_text").css("font-size", newSize);

                    setCookie("current_size",newSize,30);
                }                
        });
        
        bovpJQuery(".decrease").click(function(){
                var size = bovpJQuery(".bovp_text").css("font-size");
                var newSize = parseInt(size.substr(0,2));
                if(newSize != 10){
                    var newSize = newSize - 2;
                    bovpJQuery(".bovp_text").css("font-size", newSize);
                    
                    setCookie("current_size",newSize,30);
                }
        });


        bovpJQuery(".default").click(function(){
            
                bovpJQuery(".bovp_text").css("font-size", default_size);
                
                setCookie("current_size",default_size,30);

        });



	// Capther select Bible


	bovpJQuery("#bovp_widget_chapter").hide();


	bovpJQuery("#bovp_widget_book").change(function(){

		bovpJQuery("#bovp_widget_chapter").empty();

		var num_pages =  bovpJQuery("#bovp_widget_book option:selected").attr("num_pages");

		if(num_pages != 0) {

			var itens = []; 
			for(i = 1; i <= num_pages; i++) {itens.push(i);}
			bovpJQuery("#bovp_widget_chapter").append("<option value=\"0\" >All</option>");
			bovpJQuery.each(itens, function(index, value) {

			  bovpJQuery("#bovp_widget_chapter").append("<option value=\""+value+"\" >"+value+"</option>");

			});

			bovpJQuery("#bovp_widget_chapter").show();

		} 

		bovpJQuery("#bovp_widget_chapter:empty").hide();


    });


    bovpJQuery('.show_in_book').live('click', function(e){

        e.preventDefault();

        var return_url =  bovpJQuery('.return_url input').val();

        var action =  bovpJQuery(this).attr("href");

        bovpJQuery('.return_url').attr("action",action);
        
        bovpJQuery(".return_url").submit();

    });

    bovpJQuery('button.ind_friendly').live('click', function(e){

        e.preventDefault();

        var bk =  bovpJQuery('#bovp_widget_book').val();
        var cp =  bovpJQuery('#bovp_widget_chapter').val();
        var url = bovpJQuery("#bovp_form_index").attr("action") + bk + '/' + cp;

        if(bk != 0 ) {

            bovpJQuery("#bovp_form_index").attr("action",url);
            bovpJQuery("#bovp_form_index").submit();

        }

    });

    bovpJQuery('button.old_sh_friendly').live('click', function(e){

        e.preventDefault();

        var sh =  bovpJQuery('input.bovp_search_input').val();

        alert(sh);
        var slug = bovpJQuery('#bovp_slug_search').val();  
        alert(slug);   
        var action = bovpJQuery("form.bovp_form_search").attr("action");
        alert(action); 
        var new_action =  bovpJQuery.trim(action) + '/' + bovpJQuery.trim(sh) + '/';
        alert(new_action);
        bovpJQuery("form.bovp_form_search").attr("action",new_action);
        bovpJQuery("form.bovp_form_search").submit();

    });


     bovpJQuery('.bovp_popup').click(function(e) { // WINDOW SHARE POPUP
        e.preventDefault();
        window.open(bovpJQuery(this).attr("href"), "popupWindow", "width=600,height=600,scrollbars=no");
    });


});


function setCookie(cname,cvalue,exdays) {

    var d = new Date();
    d.setTime(d.getTime()+(exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname+"="+cvalue+"; "+expires;

}

function getCookie(cname) {

    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) 
      {
      var c = ca[i].trim();
      if (c.indexOf(name)==0) return c.substring(name.length,c.length);
      }
    return "";

}

function checkCookie() {

    var user=getCookie("username");
    if (user!="") { alert("Welcome again " + user); }
    else { 

        user = prompt("Please enter your name:","");

        if (user!="" && user!=null) {

            setCookie("username",user,30);
        }
    }

}