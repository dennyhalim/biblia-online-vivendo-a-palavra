var bovpJQuery = jQuery.noConflict(); 

$().ready(function() {
	
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