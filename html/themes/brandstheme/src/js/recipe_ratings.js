
( function( window ) {

	jQuery(document).ready(function(){

		// jQuery(".recipe-detail__rate input").change(function(){
		jQuery(".recipe-stars span").click(function(){
			//@TODO: Validate that this is integer
			var nb_star = jQuery(this).data("star");
			clear_stars();
			set_rating_stars(nb_star);



			if( !/^\d+$/.test(nb_star) ){
				return false;
			}
			if (jQuery("body").hasClass("user-logged-in")){
				processValue(nb_star);
			}
		});

	});

	function clear_stars(){
		jQuery(".recipe-stars span").removeClass("checked");
	}

	function set_rating_stars(nb_star){
		for (var i = 0; i < nb_star; i++) {
			jQuery(".recipe-stars span:eq(" + i + ")").addClass("checked");
		}
	}


	// process the vote and save in DB and the cookie
	function processValue(vote_value){
	    
	    var node_id = jQuery(".recipe-stars").data("nid");

	    if (node_id != ""){
	               
	            // var exists = existCookies();    // verify if the vote cookie exists
	            
	            // Skip cookie processing for now
	            /*if (exists){
	                processVoteCookies(node_id, vote_value);    // modify the node's vote in the cookies and update it on the DB
	            }else{
	                createVoteCookies(node_id, vote_value);     // create the node's vote in the cookies and save it on the DB
	            }*/
                
                createVoteCookies(node_id, vote_value);     // create the node's vote in the cookies and save it on the DB
	            
	    }else{
	        console.log("node id empty");
	    }

	}

	// verify if the cookie exists
	function existCookies(){

	    if (getCookie('vote') === ''){
	        return false;   //no cookie
	    } else {
	        return true;    //have cookie
	    }
	   
	}

	// create the node's vote in the cookies and save it on the DB
	function createVoteCookies(node_id,value){
	    
	    var vote = [
	        { 'node' : node_id, 'value' : value }
	    ];
	    setCookie("vote", JSON.stringify(vote), 10); //save the value in the cookie
	    saveVoteInDB(node_id, value);              //save the value in the DB
	}

	// modify the node's vote in the cookies and update it on the DB
	function processVoteCookies(id, value){
	    
	    var node_value = getVoteValueInCookies(id);     //get the vote value saved in the cookie
	   
	    if (node_value == value){       //if the value is the same, do nothing
	        
	        return false; //do nothing
	    }else{
	        //update vote value
	        var updated = updateVoteValueInCookies(id, value);
	        if (updated){
	           updateVoteInDB(id, value, node_value);  
	        }else{
	           saveVoteInDB(id, value); 
	        }
	        
	    }
	    
	}

	//get the vote value saved in the cookie
	function getVoteValueInCookies(id){
	    
	    var vote_arr = $.parseJSON(getCookie("vote"));
	    var result = $.grep(vote_arr, function(e){return e.node == id; });   //search the elem on the array 
	    
	    var value = 0;
	    if (result.length > 0) {
	        value = result[0].value;    
	    }
	    return value;
	}

	//update the vote in the cookie
	function updateVoteValueInCookies(id, value){
		//    var vote = [
		//                { 'node' : node_id, 'value' : value },
		//             ];
	    var updated = false;
	    var vote_arr = $.parseJSON(getCookie("vote"));
	    var i = 0;
	    for ( i; i < vote_arr.length; ++i) {
	        // search the node and update the value
	        if (vote_arr[i].node == id){
	            vote_arr[i].value = value;
	            updated = true;
	        }
	    }
	    // if doesn't find the node, insert the value on the array
	    if (!updated ){
	       vote_arr.push({ 'node' : id, 'value' : value }); 
	    }
	    
	    setCookie("vote", JSON.stringify(vote_arr), 10)
	    return updated;
	     
	}

	// save the vote in the DB
	function saveVoteInDB(id, value){
	    // var lang = $('html').attr('lang'); 
	    var lang = window.current_lang + "-ca";
	    var node_data = { 'node' : id, 'value' : value , 'langcode': lang};
	    
	    jQuery.ajax({
	          url:"/" + lang + "/pepsi/saverecipevote/ajaxaction",
	          type: "POST",
	          data:  node_data,
	          success:function(data) {
	              
	              var averag = data.average;
	              var total = data.total;
	              updateStars(averag, total)
	          }
	        });
	}

	// save the vote in the DB
	function updateVoteInDB(id, value,old_value){
	    // var lang = $('html').attr('lang');
	    var lang = window.language
	    var node_data = { 'node' : id, 'value' : value , 'old_value': old_value , 'langcode': lang};
	    
	    $.ajax({
	          url:"/" + lang + "/pepsi/updaterecipevote/ajaxaction",
	          type: "POST",
	          data:  node_data,
	          success:function(data) {
	              var averag = data.average;
	              var total = data.total;
	              updateStars(averag, total)

	          }
	        });
	}

	function updateStars(averag, total){
	     var id = "star" + averag
	     jQuery(".recipe-detail__rate input#" + id).attr('checked', 'checked')
	    // $("#reviewsNumber").html(total);
	    // printStars(averag);
	}


	//print the stars according to the average
	function printStars(averag){
	    //remove the fullClass 
	    $('#star1 a').removeClass("glyphicon-star-empty");
	    $('#star1 a').removeClass("glyphicon-star");
	    $('#star1').children('a').each(function () {
	        //console.log(this.value); // "this" is the current element in the loop
	        if (averag !=0 ){
	            $(this).addClass("glyphicon-star");
	            averag --;
	        }else{
	            $(this).addClass("glyphicon-star-empty");
	        }
	    });
	    
	}

	function getCookie(cname) {
	    var name = cname + "=";
	    var ca = document.cookie.split(';');
	    for(var i = 0; i < ca.length; i++) {
	        var c = ca[i];
	        while (c.charAt(0) == ' ') {
	            c = c.substring(1);
	        }
	        if (c.indexOf(name) == 0) {
	            return c.substring(name.length, c.length);
	        }
	    }
	    return "";
	}

	function setCookie(cname, cvalue, exdays) {
		// @TODO: perform input validation on arguments
		// prevent setting cookies if one of them fails validation
		if (!/^[a-zA-Z0-9]*$/g.test(cname) || 
			!/^\d+$/.test(exdays) ||
			// allow string of the form [{"node":"657","value":"3"}]
			!/^[a-zA-Z0-9:,"\[\]{}]*$/g.test(cvalue))
		{
			return false;
		}
	    var d = new Date();
	    d.setTime(d.getTime() + (exdays*24*60*60*1000));
	    var expires = "expires="+d.toUTCString();
	    document.cookie = cname + "=" + cvalue + "; " + expires;
	}

})( window );