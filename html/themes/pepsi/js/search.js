/* ------------------------------------------------------------ *\
|* ------------------------------------------------------------ *|
|* Some JS to help with our search
|* ------------------------------------------------------------ *|
\* ------------------------------------------------------------ */
(function(window){

       
    
       nav = document.querySelector(".navbarcontainer");
       
       if (nav) {
	// get vars
	var searchEl = document.querySelector("#input");
	var labelEl = document.querySelector("#label");
	var navEl = document.querySelector("#desktopNav");

	// register clicks and toggle classes
	labelEl.addEventListener("click",function(){
		if (classie.has(searchEl,"focus")) {
			classie.remove(searchEl,"focus");
			classie.remove(labelEl,"active");
			classie.add(searchEl,"notfocus");
			classie.add(labelEl,"inactive");
			classie.remove(navEl,"hidenav");
		} else {
			classie.remove(searchEl,"notfocus");
			classie.remove(labelEl,"inactive");
			classie.add(searchEl,"focus");
			classie.add(labelEl,"active");
			classie.add(navEl,"hidenav");
		}
	});

	// register clicks outisde search box, and toggle correct classes
	document.addEventListener("click",function(e){
		var clickedID = e.target.id;
		if (clickedID != "search-terms" && clickedID != "search-label") {
			if (classie.has(searchEl,"focus")) {
				classie.remove(searchEl,"focus");
				classie.remove(labelEl,"active");
				classie.add(searchEl,"notfocus");
				classie.add(labelEl,"inactive");
				classie.add(navEl,"hidenav");
			}
		}
                classie.remove(navEl,"hidenav");
	});
    }
}(window));


(function($) {
    
    $(document).ready(function(){ 

        checkIfSearchPage();    //check if the url has a parameter with the search text

    });
    
    // check if the url has a parameter with the search text in that case show the search box on the header
function checkIfSearchPage(){
   
    var search_text = getUrlParameter('search_api_fulltext');
    if (search_text != undefined){
        $("#search-terms").val(search_text);
        $("#search-label").click();
    }

    //add active class to the filter
    var url = window.location.href;     // Returns full URL
    if (url.indexOf("content_type") >= 0){
        
        $("#ct-facet li").removeClass("is-active");
        if (url.indexOf("article") >= 0){  
            $("#ct-article").addClass("is-active");
        }else if (url.indexOf("recipe") >= 0){
            $("#ct-recipe").addClass("is-active");
        }else if (url.indexOf("brand") >= 0){
            $("#ct-brand").addClass("is-active");           
        }else if (url.indexOf("contest_callout") >= 0){
            $("#ct-contest").addClass("is-active");           
        }
    }else{
        $("#ct-facet li").removeClass("is-active");
        $("#ct-all").addClass("is-active"); 
    }

}

//get the value of the specific parameter in the url
function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

})(jQuery);
 
