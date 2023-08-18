jQuery( document ).ready(function($) {
	
	

	

    $('#list_result').on("click", '.result-item .sharelink', function(event) {    
        
        navigator.clipboard
          .writeText(location.protocol + '//' + location.host + location.pathname + $(this).attr('href'))
          .then(() => {
           $(this).html('<i aria-hidden="true" class="fas fa-link"></i> Linket er kopieret');
            var base = $(this);
             $(this).addClass("shareblink");
            setTimeout(function() {
                $(base).html('<i aria-hidden="true" class="fas fa-link"></i> Del');
                $(base).removeClass("shareblink");
            },2000);	
            
          })
          .catch(() => {
            alert("Direkte link: \r\n"+location.protocol + '//' + location.host + location.pathname + $(this).attr('href'));
          });
        
    
        
        event.preventDefault();
    });	
        
        
        
        
        
        
    $("#search_input").on("search", function(evt){
        if($(this).val().length == 0){
           sj_get_results(1);
        }
    });
        
        
        
        if($(".result-item").attr('ref-autostart') == "true")
        {
            
    
        }
        
        
    
    function remove_toogle_all_value(myArray)
        {
            var myIndex = myArray.indexOf('#alle');
            if (myIndex !== -1) {
                myArray.splice(myIndex, 1);
            }
            return myArray;
        }	
        
        
        
    function sj_get_results(paged_value)
    {
        
        window.removeEventListener('scroll',scrollhandler);	
        
        var catqueryone = [];
        var catquerytwo = [];
        var catquerythree = [];
        var view = [];
        var search_string = '';
    
        var use_portal_default_search_result_for_pageload = [];
    
    
        
        
        if($('#filter_bar #search_input').val().trim().length > 2)
        {
            search_string = $('#filter_bar #search_input').val();
        }
    
        
        
        if($('.level0 a.filter_child_level.view-list.selected').length > 0){
            
        $( ".level0 a.filter_child_level.view-list.selected" ).each(function( index ) {
            view.push(jQuery( this ).attr("href"));
       });
            
        }
        else
        {
            $( ".level0 a.filter_child_level.view-list" ).each(function( index ) {
            view.push(jQuery( this ).attr("href"));
       });
    
        use_portal_default_search_result_for_pageload.push(true);
        }	
        
        
    
        if($('.level1 a.filter_child_level.cat-list.selected').length > 0){
        $( ".level1 a.filter_child_level.cat-list.selected" ).each(function( index ) {
            catqueryone.push(jQuery( this ).attr("href"));
       });
        
        }
        else
        {
            $( ".level1 a.filter_child_level.cat-list" ).each(function( index ) {
            catqueryone.push(jQuery( this ).attr("href"));
       });
            use_portal_default_search_result_for_pageload.push(true);
        }	
            
        
        if($('.level2 a.filter_child_level.cat-list.selected').length > 0){
        $( ".level2 a.filter_child_level.cat-list.selected" ).each(function( index ) {
            catquerytwo.push(jQuery( this ).attr("href"));
       });
        }
        else
        {
            $( ".level2 a.filter_child_level.cat-list" ).each(function( index ) {
            catquerytwo.push(jQuery( this ).attr("href"));
       });
            use_portal_default_search_result_for_pageload.push(true);
        }	
        
        
        
        if($('.level3 a.filter_child_level.cat-list.selected').length > 0){
            
        $( ".level3 a.filter_child_level.cat-list.selected" ).each(function( index ) {
            catquerythree.push(jQuery( this ).attr("href"));
       });
            
        }
        else
        {
            $( ".level3 a.filter_child_level.cat-list" ).each(function( index ) {
            catquerythree.push(jQuery( this ).attr("href"));
       });
            use_portal_default_search_result_for_pageload.push(true);
        }
    
    
    
        var use_portal_default = "no";
    
            if(use_portal_default_search_result_for_pageload.length == 4 && $('#filter_bar #search_input').val().trim().length < 2)
            {
                use_portal_default = "yes";
            }
    
    
    
    
    
    
        
        catqueryone = remove_toogle_all_value(catqueryone);
        catquerytwo = remove_toogle_all_value(catquerytwo);
        catquerythree = remove_toogle_all_value(catquerythree);
        view = remove_toogle_all_value(view);
        
        
        var list_formData = new FormData();
        list_formData.append( "action", 'portal_search');
        list_formData.append("nonce", $('#filter_bar').attr('ref-data-nonce'));
        list_formData.append("catqueryone", catqueryone.toString());
        list_formData.append("catquerytwo", catquerytwo.toString());
        list_formData.append("catquerythree", catquerythree.toString());
        list_formData.append("view_query", view.toString());
        list_formData.append("search_query", search_string);
        list_formData.append("paged", paged_value);
        list_formData.append("currentpage_id",$('#filter_bar').attr('ref-currentpageid'));
        list_formData.append("vis_start_resultater",use_portal_default);
    
    
        
    $.ajax({
      type: "POST",
      url: '/wp-admin/admin-ajax.php',
      data: list_formData,
      processData: false,
      contentType: false,
      beforeSend: function () {  
          if(paged_value <= 1){
         // $('#list_result').html('<div>Søger....</div>');
              }
          
         //  $('.filter_top_level_wrapper.selected ').removeClass('selected');
          
      },
      success : function(response) 
        {
            if(paged_value <= 1){
            jQuery('#list_result').html(response);	
            }
            else
            {
             jQuery('#list_result').append(response);
            }
            
            if($("#list_result").find("button.pagination").length)
            {
                window.addEventListener("scroll",scrollhandler, false);
            }
            
        }
    });
        
    }	
        
    
        /*$("#list_result").on( "click", "button", function() {
        sj_get_results( $( this ).attr("next_update"));	
            $(this).remove();
        });
        */
    
    
    
    $(document).keypress( function(event){
    
    var logindhasFocus = $('#search_input').is(':focus');
    
    
    if(logindhasFocus){
    
    if (event.which == '13')
    {   
      event.preventDefault(); 
     
      $( "#update_list_result" ).click();
     }
    
    }
    
    });
    
    
    $(document).keyup( function(event){
            
    var logindhasFocus = $('#search_input').is(':focus');
    
    if(logindhasFocus)
    {
        if ($('#search_input').val().length == 0)
        {
          sj_get_results(1);
          event.preventDefault(); 
    
        }	
        
    }
                
        });
    
    
    
    
        
    $('#update_list_result').click(function(){
    sj_get_results(1);
     event.preventDefault();	
    });
        
    
        
        
        $("a.filter_child_level:not('.toggle_all')").click(function(event){
            $(this).toggleClass('selected');
             event.preventDefault();
            
            $(this).closest(".filter_top_level_wrapper.selected").find(".filter_top_level i").text("");
            
            var category_selected_arr = [];
               
             $(this).parent().parent().find(".selected").each(function( index ) {
                    if($( this ).attr('href') != '#alle')
                    {
                    category_selected_arr.push(index);
                    }
               });
            
             
            if(category_selected_arr.length > 0)
              {
                  
               $(this).closest(".filter_top_level_wrapper.selected").find(".filter_top_level i").text("+"+category_selected_arr.length);
               }
            sj_get_results(1);
        });
        
        
        
    $("a.filter_child_level.toggle_all").click(function(event){
            $(this).toggleClass('selected');
        
        if($(this).hasClass('selected'))
        {
            $(this).parent().parent().find('li a.filter_child_level').addClass('selected');
        }
        else
        {
            $(this).parent().parent().find('li a.filter_child_level').removeClass('selected');
        }
        
        $(this).closest(".filter_top_level_wrapper.selected").find(".filter_top_level i").text("");
            
            var category_selected_arr = [];
               
             $(this).parent().parent().find(".selected").each(function( index ) {
                    if($( this ).attr('href') != '#alle')
                    {
                    category_selected_arr.push(index);
                    }
               });
            
             
            if(category_selected_arr.length > 0)
              {
                  
               $(this).closest(".filter_top_level_wrapper.selected").find(".filter_top_level i").text("+"+category_selected_arr.length);
               }
        
             event.preventDefault();
        sj_get_results(1);
    })	
        
        
        
    $(".filter_top_level").click(function(event){
        /*$(this).parent().toggleClass('selected')*/;
        
        if($(this).parent().hasClass("selected")){
            $(".filter_top_level_wrapper").removeClass('selected');
        }
        else
        {
            $(".filter_top_level_wrapper").removeClass('selected');
            $(this).parent().addClass('selected');
        }
        $(this).parent().find('ul').css({'top': $(this).parent().outerHeight() + "px"});	
        event.preventDefault();
    });
    
    //LASYLOAD
    //
    //	
    //	
    
    function removescrollhandler() {
    window.removeEventListener('scroll',scrollhandler);	
    sj_get_results($("#list_result").find("button.pagination").attr("next_update"));	
    $("#list_result").find("button.pagination").remove();	
    }
        
        
    function scrollhandler(event) 
        {
        var top = this.scrollY,
            left = this.scrollX;
    console.log(top);
    var result_item = document.getElementsByClassName('result-item');
    var  result_item_height = result_item[0].getBoundingClientRect().height;		
            
    let list_result_height = document.querySelector('#list_result').getBoundingClientRect().height - (result_item_height * 1.5);
        if(list_result_height <= top)
        {
        removescrollhandler();
        }
    
    };
        
        
        
    
    $('#list_result').on("click", '.result-item .content .read_more_wrapper a.open_close', function(event) { 	
           $(this).parent().toggleClass('selected');
         $(this).text(function(i, text){
              return text === "Læs mere" ? "Skjul" : "Læs mere";
          })
         event.preventDefault();
    });
        
        
        
        
        
        
        
    $('#list_result').on("click", '.result-item .content a.video_link', function(event) { 	
        event.preventDefault();
    let searchurl = $(this).attr('href');
    let position = searchurl.search("vimeo");
    
        if(position !== -1 ){
            //Dette er en vimeo
        $( this).closest( ".content" ).prepend('<div class="overlay" style="display:block;z-index:9999;"><div class="overlay_wrapper"><div class="close_overlaver"><i class="eicon-close"></i></div><div class="overlay_video_wrapper"><div style="padding:56.25% 0 0 0;position:relative;"><iframe src="'+$(this).attr('href')+'?title=0&byline=0&portrait=0" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div><script src="https://player.vimeo.com/api/player.js"></script></div><div class="video_info"><div class="video_title">'+$( this).closest( ".content" ).find('.title a').text()+'</div><div class="video_description">'+$( this).closest( ".content" ).find('.teaser_text a').html()+'</div></div></div></div>')
            console.log("hej vimeo video");
        }
        else
        {
        //Dette er IKKE en vimeo	
        $( this).closest( ".content" ).prepend('<div class="overlay" style="display:block;z-index:9999;"><div class="overlay_wrapper"><div class="close_overlaver"><i class="eicon-close"></i></div><div class="overlay_video_wrapper"><video controls><source src="'+$(this).attr('href')+'" type="video/mp4">Your browser does not support the video tag.</video></div><div class="video_info"><div class="video_title">'+$( this).closest( ".content" ).find('.title a').text()+'</div><div class="video_description">'+$( this).closest( ".content" ).find('.teaser_text a').html()+'</div></div></div></div>')
        console.log("hej video");
        }
    
    
    
    });
        
    
     $('#list_result').on("click", '.result-item .content a.podcast_link', function(event) { 	
        event.preventDefault();
    $( this).closest( ".content" ).prepend('<div class="overlay" style="display:block;z-index:9999;"><div class="overlay_wrapper"><div class="close_overlaver"><i class="eicon-close"></i></div><div class="overlay_podcast_wrapper"><audio controls="controls"><source src="'+$(this).attr('href')+'" type="audio/mpeg">Your browser does not support the audio element.</audio></div><div class="podcast_info"><div class="podcast_title">'+$( this).closest( ".content" ).find('.title a').text()+'</div><div class="podcast_description">'+$( this).closest( ".content" ).find('.teaser_text a').html()+'</div></div></div></div>')
        console.log("hej podcast");
    });
        
    
     $('#list_result').on("click", '.result-item .content a.digital_modul_link', function(event) { 	
        event.preventDefault();
    $( this).closest( ".content" ).prepend('<div class="overlay" style="display:block;z-index:9999"><div class="overlay_wrapper" style="width: 80vw;height: 80vh;top: calc(50%);"><div class="close_overlaver"><i class="eicon-close"></i></div><div class="overlay_digital_modul_wrapper"><iframe src="'+$(this).attr('href')+'" border="0" style="width: 80vw;height: 80vh;"></iframe></div></div></div>')
        console.log("hej digital_modul");
    });	
            
        
    
    $('#list_result').on("click", '.result-item .content .close_overlaver', function(event) { 	
        event.preventDefault();
    $(".overlay").remove();
    });
    
        
        
    
    });
    