<?php

 /*
    Plugin Name: sj_portal
    Plugin URI: http://www.skovjacobsen.dk
    Description: undervisningsportal
    Author: Morten Jacobsen / SkovJacobsen
    Version: 1.0
    Author URI: http://www.skovjacobsen.dk
    */

 //=================================================
// Security: Abort if this file is called directly
//=================================================

/**BEMÆRK POST_ID er hardcoded ($post_objects = get_field($acf_field, 869);) linie 714**/

if ( !defined('ABSPATH') ) { 
    die;
}   


add_shortcode( 'portal', 'portal_func' );
function portal_func( $atts ) 
{
	
	$htmlstr = "";
	$toplevel_count = 1;
	$materialetype_obj = get_field_object('field_62badff841a86');
	
$htmlstr .='<div id="filter_bar" ref-data-nonce="'.wp_create_nonce('ajax-nonce').'" ref-currentpageID="'.get_the_ID().'">
<div id="filter_row">';

	
  $hiterms = get_terms("filtrering", array("meta_key"=>"sotering","orderby" => "meta_value","order"=>"ASC", "parent" => 0,'hide_empty' => false)); 
	
foreach($hiterms as $key => $hiterm) : 

/*INDSÆTTER MATRIALE VALG START*/	
if($toplevel_count == 2)
{

$htmlstr .= '<div class="level0 filter_top_level_wrapper">';
$htmlstr .= '<a href="'.$materialetype_obj["name"].'" class="filter_top_level" ><span>'.$materialetype_obj["label"].'</span><i></i></a>';

	if( $materialetype_obj['choices'] ){ 

			if(count($materialetype_obj['choices']) > 9)
			{
			$htmlstr .= '<ul class="two_column">';	
			}
			else
			{
			$htmlstr .= '<ul>';
			}
		
        foreach( $materialetype_obj['choices'] as $value => $label ){
		$htmlstr .= '<li><a href="'.$value.'" class="filter_child_level view-list">'.$label.'</a></li>';
       };
		$htmlstr .= '<li><a href="#alle" class="filter_child_level view-list toggle_all">Alle</a></li>';	
    $htmlstr .='</ul>';	
 }; 
	
	$htmlstr .='</div>';
		
	}
/*INDSÆTTER MATRIALE VALG SLUT*/	
	
        $htmlstr .= '<div class="level'.$toplevel_count.' filter_top_level_wrapper">';
	    $toplevel_count++;
         
	$htmlstr .= '<a href="'.$hiterm->slug.'" class="filter_top_level" ><span>'.$hiterm->name.'</span><i></i></a>';
          $loterms = get_terms("filtrering", array("meta_key"=>"sotering","orderby" => "meta_value","order"=>"ASC", "parent" => $hiterm->term_id,'hide_empty' => false));
           
	if($loterms) : 
	
	if(count($loterms) > 8)
			{
			$htmlstr .= '<ul class="two_column">';	
			}
			else
			{
			$htmlstr .= '<ul>';
			}
	
             foreach($loterms as $key => $loterm) :
					 $htmlstr .='<li><a href="'. $loterm->slug.'" class="filter_child_level cat-list">'.$loterm->name.'</a></li>';
            endforeach;
					$htmlstr .= '<li><a href="#alle" class="filter_child_level view-list toggle_all">Alle</a></li>';
                  $htmlstr .='</ul>';
            endif;
        $htmlstr .= '</div>';
   endforeach;
	
	
$htmlstr .= '<div class="levelsearch filter_top_level_wrapper"><input name="search_input" id="search_input" tabindex="1" class="" type="search" autocomplete="off" title="Indtast søgeord" placeholder="Indtast søgeord" /></div>
<div class="levelupdatebtn filter_top_level_wrapper"><button id="update_list_result">SØG</button></div>	
</div>	
	
</div>

<div id="list_result">'.portal_default_search_result_for_pageload('start_resultater').'</div>';
	
	return $htmlstr;
};


add_action( 'wp_ajax_portal_search', 'portal_search' );
add_action( 'wp_ajax_nopriv_portal_search', 'portal_search' );

function portal_search()
{
	
$nonce = $_POST['nonce'];
$catqueryone_query = $_POST["catqueryone"] ? trim ($_POST["catqueryone"]) : '';
$catquerytwo_query = $_POST["catquerytwo"] ? trim ($_POST["catquerytwo"]) : '';
$catquerythree_query = $_POST["catquerythree"] ? trim ($_POST["catquerythree"]) : '';
$view_query = $_POST["view_query"] ? trim ($_POST["view_query"]) : '';
$search_query = $_POST["search_query"] ? trim ($_POST["search_query"]) : '';
$paged = $_POST["paged"] ? trim ($_POST["paged"]) : '1';

$currentpageID = $_POST["currentpage_id"] ? trim ($_POST["currentpage_id"]) : '';



$vis_start_resultater = $_POST["vis_start_resultater"] ? trim ($_POST["vis_start_resultater"]) : 'no';


  if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
  {

      $fields = array('status' => "error");
      $json[] = $fields;
      $data_string = json_encode($json);
      header('Content-Type: application/json');
      echo $data_string;
      die ();

  }
	
header('Content-Type: text/html; charset=UTF-8');
//header('Content-Type: application/json');


if($vis_start_resultater != "yes")
{
 echo portal_search_result( $catqueryone_query,$catquerytwo_query,$catquerythree_query, $view_query,$search_query, $paged, $currentpageID );
}
elseif($vis_start_resultater == "yes")
{
 echo portal_default_search_result_for_pageload('start_resultater');
}

die ();	
};



function portal_search_result( $catqueryone_query,$catquerytwo_query,$catquerythree_query, $view_query, $search_query,$paged,$currentpageID )
{
$htmlstr =''; 
	
	$category_filter_one = explode(',', $catqueryone_query);	
	
	
	$taxonomyArr_one = array('relation' => 'OR');
	
		foreach ($category_filter_one as $value){ 
		$filter_one[] = array(
					'taxonomy' => 'filtrering',
					'field'    => 'slug',
					'terms'    => $value,
				    'compare'   => '='
				);
		 };

	
		 foreach ($filter_one as $value){ 
		  $taxonomyArr_one[] = $value;
		}
	
	 $collectin = array('relation' => 'AND');
	 $collectin[] =  $taxonomyArr_one;
	
	//
	
	$category_filter_two = explode(',', $catquerytwo_query);	
	
	
	$taxonomyArr_two = array('relation' => 'OR');
	
		foreach ($category_filter_two as $value){ 
		$filter_two[] = array(
					'taxonomy' => 'filtrering',
					'field'    => 'slug',
					'terms'    => $value,
				    'compare'   => '='
				);
		 };

	
		 foreach ($filter_two as $value){ 
		  $taxonomyArr_two[] = $value;
		}
	$collectin[] =  $taxonomyArr_two;
	
	$category_filter_three = explode(',', $catquerythree_query);	
	
	
	$taxonomyArr_three = array('relation' => 'OR');
	
		foreach ($category_filter_three as $value){ 
		$filter_three[] = array(
					'taxonomy' => 'filtrering',
					'field'    => 'slug',
					'terms'    => $value,
				    'compare'   => '='
				);
		 };

	
		 foreach ($filter_three as $value){ 
		  $taxonomyArr_three[] = $value;
		}
	$collectin[] =  $taxonomyArr_three;
	//
	
	
	
$view_filter = explode(',', $view_query);
	$viewArr = array('relation' => 'OR');
	
	foreach ($view_filter as $value){ 
		$viewfilter[] = array(
            'key'       => 'materialetype',
            'value'     => $value,
            'compare'   => '='
        );
	};
		

		 foreach ($viewfilter as $value){ 
		  $viewArr[] = $value;
		}
	
	
$args = array(  
        'post_type' => 'undervisning',
        'post_status' => 'publish',
        'meta_key'  => 'prioriteringsliste',
    	'orderby' => 'meta_value_num',
    	'order'   => 'ASC',
        'posts_per_page' => 8,
        'paged' => $paged	
);	


 



	if(strlen($search_query) > 2)
	{
	$args['s'] = trim($search_query);
	}


$args['tax_query'] = $collectin;
$args['meta_query'] = $viewArr;	
	
//return json_encode($args,true);
	
	
$the_query = new WP_Query( $args );
$posts = $the_query->posts;
$num_post = count($posts);
$total_pages = $the_query->max_num_pages;
$total_found_posts = $the_query->found_posts;
	
	foreach($posts as $post) 
	{
		$displaypostID = esc_html( $post->ID );
		$url = esc_url( get_permalink($displaypostID));
		$materialetype = get_field('materialetype', $displaypostID );
		$materialetype_label =  get_field_object('materialetype', $displaypostID );
		$teaser_billede = get_the_post_thumbnail_url($displaypostID);
		
		if (get_field('video_link', $displaypostID)) 
		{
		  $video_link = get_field('video_link', $displaypostID );
		}
		if (get_field('podcast_link', $displaypostID)) 
		{
		  $podcast_link = get_field('podcast_link', $displaypostID );
		}
		if (get_field('digital_modul_link', $displaypostID)) 
		{
		  $digital_modul_link = get_field('digital_modul_link', $displaypostID );
		}
		
		if (get_field('forlob_link', $displaypostID)) 
		{
		  $forlob_link = get_field('forlob_link', $displaypostID );
		}

		if(get_field('ekstern_link', $displaypostID))
		{
			 $forlob_link = get_field('ekstern_link', $displaypostID );
		}
		
		
		$htmlstr .= '<div class="result-item">
		<div class="content">';
		
		if (get_field('video_link', $displaypostID)) 
		{
		$htmlstr .= '<a href="'.$video_link.'" class="teaser_img video_link" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['video']).'</div></a>';
			
		$htmlstr .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$htmlstr .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$htmlstr .= '</div>';	
			
	$htmlstr .=	'<div class="title"><a href="'.$video_link.'" title="'.$post->post_title.'" class="video_link">'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$video_link.'" title="'.$post->post_title.'" class="video_link">'.get_the_excerpt($displaypostID ).'</a></div>';
	
		$htmlstr .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';	
			
		}
		elseif (get_field('podcast_link', $displaypostID)) 
		{
	$htmlstr .= '<a href="'.$podcast_link.'" class="teaser_img podcast_link" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['podcast']).'</div></a>';
			
	$htmlstr .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$htmlstr .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$htmlstr .= '</div>';
			
	$htmlstr .= '<div class="title"><a href="'.$podcast_link.'" title="'.$post->post_title.'" class="podcast_link">'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$podcast_link.'" title="'.$post->post_title.'" class="podcast_link">'.get_the_excerpt($displaypostID ).'</a></div>';
	
	$htmlstr .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';		
		}	
		elseif (get_field('digital_modul_link', $displaypostID)) 
		{
	
			$htmlstr .= '<a href="'.$digital_modul_link.'" class="teaser_img digital_modul_link" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['digitalmodul']).'</div></a>';
			
	$htmlstr .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$htmlstr .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$htmlstr .= '</div>';		
			
			
	$htmlstr .=	'<div class="title"><a href="'.$digital_modul_link.'" title="'.$post->post_title.'" class="digital_modul_link">'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$digital_modul_link.'" title="'.$post->post_title.'" class="digital_modul_link">'.get_the_excerpt($displaypostID ).'</a></div>';
	
	$htmlstr .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';	
		
		}
		elseif (get_field('forlob_link', $displaypostID) || get_field('ekstern_link', $displaypostID) ) {


		 $setblankAttr = "_self";
		if(get_field('ekstern_link', $displaypostID))
		  {
			$forlob_link = get_field('ekstern_link', $displaypostID );
			$setblankAttr = "_blank";

		  }



		
			$htmlstr .= '<a href="'.$forlob_link.'" target="'.$setblankAttr.'" class="teaser_img" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['forlob']).'</div></a>';
			
			$htmlstr .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$htmlstr .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$htmlstr .= '</div>';
			
		$htmlstr .= '<div class="title"><a href="'.$forlob_link.'" target="'.$setblankAttr.'" title="'.$post->post_title.'" >'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$forlob_link.'" target="'.$setblankAttr.'" title="'.$post->post_title.'">'.get_the_excerpt($displaypostID ).'</a></div>';

	$htmlstr .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';	
			
		}
		else 
		{


			if(ucfirst($materialetype) == "Digitalmodul")
			{
				$materialetype = "Digitalt modul";
			}
	

			
	$htmlstr .= '<a href="'.get_permalink( $displaypostID ).'" class="teaser_img" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype).'</div></a>';
			
			$htmlstr .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$htmlstr .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$htmlstr .= '</div>';
			
		$htmlstr .= '<div class="title"><a href="'.get_permalink( $displaypostID ).'" title="'.$post->post_title.'" >'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.get_permalink( $displaypostID ).'" title="'.$post->post_title.'">'.get_the_excerpt($displaypostID ).'</a></div>';

	$htmlstr .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';		
			
		}
		
		
		$htmlstr .= '</div>
        </div>';
		


	}
	
wp_reset_postdata();
	
	if($num_post)
	{
		if($paged < $total_pages)
		{
	    $htmlstr .='<button class="pagination" next_update="'.($paged+1).'" total="'.$total_pages.'">Hent flere resultater.</buttom>';
		}
     return $htmlstr;	
	}
	else
	{
		
	$no_results_str = "";
		//PREVALGT resultater start		
	$post_objects = get_field('ingen_soge_resutater', $currentpageID);
	$post_objects_text = get_field('ingen_soge_resutater_tekst', $currentpageID);	
		
		
	foreach($post_objects as $post) 
	{
		$displaypostID = esc_html( $post->ID );
		$url = esc_url( get_permalink($displaypostID));
		
		$materialetype = get_field('materialetype', $displaypostID );
		$materialetype_label =  get_field_object('materialetype', $displaypostID );
		$teaser_billede = get_the_post_thumbnail_url($displaypostID);
		
		if (get_field('video_link', $displaypostID)) 
		{
		  $video_link = get_field('video_link', $displaypostID );
		}
		if (get_field('podcast_link', $displaypostID)) 
		{
		  $podcast_link = get_field('podcast_link', $displaypostID );
		}
		if (get_field('digital_modul_link', $displaypostID)) 
		{
		  $digital_modul_link = get_field('digital_modul_link', $displaypostID );
		}
		
		if (get_field('forlob_link', $displaypostID)) 
		{
		  $forlob_link = get_field('forlob_link', $displaypostID );
		}

		if(get_field('ekstern_link', $displaypostID))
		{
			 $forlob_link = get_field('ekstern_link', $displaypostID );
		}



		
		$no_results_str .= '<div class="result-item">
		<div class="content">';
		
		
		if (get_field('video_link', $displaypostID)) 
		{
		$no_results_str .= '<a href="'.$video_link.'" class="teaser_img video_link" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['video']).'</div></a>';
		
			$no_results_str .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$no_results_str .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$no_results_str .= '</div>';
			
		$no_results_str .= '<div class="title"><a href="'.$video_link.'" title="'.$post->post_title.'" class="video_link">'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$video_link.'" title="'.$post->post_title.'" class="video_link">'.get_the_excerpt($displaypostID ).'</a></div>';

$no_results_str .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';			
			
		}
		elseif (get_field('podcast_link', $displaypostID)) 
		{
	$no_results_str .= '<a href="'.$podcast_link.'" class="teaser_img podcast_link" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['podcast']).'</div></a>';
			
	$no_results_str .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$no_results_str .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$no_results_str .= '</div>';
			
		$no_results_str .= '<div class="title"><a href="'.$podcast_link.'" title="'.$post->post_title.'" class="podcast_link">'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$podcast_link.'" title="'.$post->post_title.'" class="podcast_link">'.get_the_excerpt($displaypostID ).'</a></div>';

$no_results_str .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';
		}	
		elseif (get_field('digital_modul_link', $displaypostID)) 
		{
	
			$no_results_str .= '<a href="'.$digital_modul_link.'" class="teaser_img digital_modul_link" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['digitalmodul']).'</div></a>';
			
			$no_results_str .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$no_results_str .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$no_results_str .= '</div>';
			
		$no_results_str .='<div class="title"><a href="'.$digital_modul_link.'" title="'.$post->post_title.'" class="digital_modul_link">'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$digital_modul_link.'" title="'.$post->post_title.'" class="digital_modul_link">'.get_the_excerpt($displaypostID ).'</a></div>';
	
	$no_results_str .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';	
			
		}
		elseif (get_field('forlob_link', $displaypostID) || get_field('ekstern_link', $displaypostID)) {


			$setblankAttr = "_self";
		if(get_field('ekstern_link', $displaypostID))
		  {
			$forlob_link = get_field('ekstern_link', $displaypostID );
			$setblankAttr = "_blank";

		  }	


		$no_results_str .= '<a href="'.$forlob_link.'" class="teaser_img" target="'.$setblankAttr.'" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['forlob']).'</div></a>';
			
			$no_results_str .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$no_results_str .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$no_results_str .= '</div>';
			
				$no_results_str .='<div class="title"><a href="'.$forlob_link.'" target="'.$setblankAttr.'" title="'.$post->post_title.'" >'.$post->post_title.'</a></div>
				<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$forlob_link.'" target="'.$setblankAttr.'" title="'.$post->post_title.'">'.get_the_excerpt($displaypostID ).'</a></div>';

			$no_results_str .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';	
			
		}
		else 
		{


			if(ucfirst($materialetype) == "Digitalmodul")
			{
				$materialetype = "Digitalt modul";
			}

			
	$no_results_str .= '<a href="'.get_permalink( $displaypostID ).'" class="teaser_img" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype).'</div></a>';
			
			
	$no_results_str .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$no_results_str .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$no_results_str .= '</div>';
			
			
			
			
		$no_results_str .='<div class="title"><a href="'.get_permalink( $displaypostID ).'" title="'.$post->post_title.'" >'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.get_permalink( $displaypostID ).'" title="'.$post->post_title.'">'.get_the_excerpt($displaypostID ).'</a></div>';

$no_results_str .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';			
			
		}
		
		
		$no_results_str .= '</div>
        </div>';
		


	}
	
//PREVALGT resultater slut		
	return "<div id='no_results'>".$post_objects_text."</div>". $no_results_str;
	
	};
	
};







function sj_portal_func_enqueue() {


global $post;
     
if(has_shortcode( $post->post_content, 'portal') && ( is_single() || is_page() ) ){
     
wp_enqueue_style( 'sj_portal_css', plugins_url('sj_portal/css/portal.css?v0.1'.rand()) );
wp_enqueue_script( 'sj_portal_js', plugins_url('sj_portal/js/portal.js?11'.rand()), array('jquery'), '', true );    
     
    }


} 
add_action('wp_enqueue_scripts', 'sj_portal_func_enqueue');



function portal_default_search_result_for_pageload($acf_field)
{

/*Tilføjet/ændret 10.11.2022*/	
/*$paged = $_POST["paged"] ? trim ($_POST["paged"]) : '1';*/
$paged = 1;

	
$htmlstr =''; 


$url = $_SERVER["REQUEST_URI"];
$get_id = "";	
$is_urlparm = false; 	
if( strpos( $url, "?" ) === false )
{
	$is_urlparm = false;
}
else{	
	$get_id = explode( "?", $url )[1];
	if ( FALSE === get_post_status( $get_id )  ) {
	  $is_urlparm = false;
	} else {
		
		if(get_post_type( $get_id ) == "undervisning")
		{
		$is_urlparm = true;
		}else{
		$is_urlparm = false;
		}
	 
	}	
}
 
if($is_urlparm == true)
{

$args = array(  
        'post_type' => 'undervisning',
        'post_status' => 'publish',
        'post__in'   => array($get_id ),

);	


	
}else{

/**BEMÆRK POST_ID er hardcoded ($post_objects = get_field($acf_field, 869);)**/
$post_objects = get_field($acf_field, 869);


$args = array(  
        'post_type' => 'undervisning',
        'post_status' => 'publish',
        'post__in'   => $post_objects,
        'orderby'=> 'post__in'
);	


}	


	
$the_query = new WP_Query( $args );
$posts = $the_query->posts;
$num_post = count($posts);
$total_pages = $the_query->max_num_pages;
$total_found_posts = $the_query->found_posts;
	
	foreach($posts as $post) 
	{
			
		$displaypostID = esc_html( $post->ID );
		$url = esc_url( get_permalink($displaypostID));
		$materialetype = get_field('materialetype', $displaypostID );
		$materialetype_label =  get_field_object('materialetype', $displaypostID );
		$teaser_billede = get_the_post_thumbnail_url($displaypostID);
		
		if (get_field('video_link', $displaypostID)) 
		{
		  $video_link = get_field('video_link', $displaypostID );
		}
		if (get_field('podcast_link', $displaypostID)) 
		{
		  $podcast_link = get_field('podcast_link', $displaypostID );
		}
		
		if (get_field('digital_modul_link', $displaypostID)) 
		{
		  $digital_modul_link = get_field('digital_modul_link', $displaypostID );
		}
		
		if (get_field('forlob_link', $displaypostID)) 
		{
		  $forlob_link = get_field('forlob_link', $displaypostID );
		}


		if(get_field('ekstern_link', $displaypostID))
		  {
			$forlob_link = get_field('ekstern_link', $displaypostID );
		  }	


		
		if($is_urlparm == true)
		{
		$htmlstr .= '<div class="result-item" ref-autostart="true">
		<div class="content">';	
		}
		else{
		$htmlstr .= '<div class="result-item">
		<div class="content">';
		}
		
		
	if (get_field('video_link', $displaypostID)) 
		{
		$htmlstr .= '<a href="'.$video_link.'" class="teaser_img video_link" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['video']).'</div></a>';
		
	
	$htmlstr .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$htmlstr .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$htmlstr .= '</div>';
		
		$htmlstr .= '<div class="title"><a href="'.$video_link.'" title="'.$post->post_title.'" class="video_link">'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$video_link.'" title="'.$post->post_title.'" class="video_link">'.get_the_excerpt($displaypostID ).'</a></div>';
	
	$htmlstr .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';		
			
		}
		elseif (get_field('podcast_link', $displaypostID)) 
		{
	$htmlstr .= '<a href="'.$podcast_link.'" class="teaser_img podcast_link" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['podcast']).'</div></a>';
			
	$htmlstr .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$htmlstr .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$htmlstr .= '</div>';		
		
		
	$htmlstr .=	'<div class="title"><a href="'.$podcast_link.'" title="'.$post->post_title.'" class="podcast_link">'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$podcast_link.'" title="'.$post->post_title.'" class="podcast_link">'.get_the_excerpt($displaypostID ).'</a></div>';

$htmlstr .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';			
		}
		elseif (get_field('digital_modul_link', $displaypostID)) 
		{
	
			$htmlstr .= '<a href="'.$digital_modul_link.'" class="teaser_img digital_modul_link" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['digitalmodul']).'</div></a>';
		
			$htmlstr .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$htmlstr .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$htmlstr .= '</div>';
			
			$htmlstr .='<div class="title"><a href="'.$digital_modul_link.'" title="'.$post->post_title.'" class="digital_modul_link">'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$digital_modul_link.'" title="'.$post->post_title.'" class="digital_modul_link">'.get_the_excerpt($displaypostID ).'</a></div>';
	
	$htmlstr .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';	
			
		}
	elseif (get_field('forlob_link', $displaypostID) || get_field('ekstern_link', $displaypostID)) {	


		$setblankAttr = "_self";
		if(get_field('ekstern_link', $displaypostID))
		  {
			$forlob_link = get_field('ekstern_link', $displaypostID );
			$setblankAttr = "_blank";

		  }	


		$htmlstr .= '<a href="'.$forlob_link.'" target="'.$setblankAttr.'" class="teaser_img" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype_label['choices']['forlob']).'</div></a>';
		
		$htmlstr .= '<div class="post_cats_wrapper">';	
	$getCategory = get_the_terms($post->ID,'filtrering');	
	foreach( $getCategory as $category ) 
	{	
		//categorien skal være fra denne hvem-skal-laere-noget
		if($category->parent == 5)
		{
		$htmlstr .= '<div class="post_cat"><i aria-hidden="true" class="fas fa-user-alt"></i>'.$category->name.'</div>';
		}
	}
		$htmlstr .= '</div>';
		
		$htmlstr .='<div class="title"><a href="'.$forlob_link.'" title="'.$post->post_title.'"  target="'.$setblankAttr.'" >'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.$forlob_link.'"  target="'.$setblankAttr.'" title="'.$post->post_title.'">'.get_the_excerpt($displaypostID ).'</a></div>';

	$htmlstr .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';	
	}
		else
		{


		if(ucfirst($materialetype) == "Digitalmodul")
			{
				$materialetype = "Digitalt modul";
			}	
			
	$htmlstr .= '<a href="'.get_permalink( $displaypostID ).'" class="teaser_img" style="background-image: url('.$teaser_billede.');"  title="'.$post->post_title.'"><div class="label">'.ucfirst($materialetype).'</div></a>';
			
			
$htmlstr .='<div class="title"><a href="'.get_permalink( $displaypostID ).'" title="'.$post->post_title.'" >'.$post->post_title.'</a></div>
		<div class="teaser_text" ><i aria-hidden="true" class="fas fa-clock"></i><a href="'.get_permalink( $displaypostID ).'" title="'.$post->post_title.'">'.get_the_excerpt($displaypostID ).'</a></div>';	
			
$htmlstr .= '<div class="read_more_wrapper" ><a href="#læs mere" title="Læs mere" class="open_close">Læs mere</a><div class="read_more_text">'.$post->post_content.'</div></div><a class="sharelink" href="?'.$displaypostID.'" target="_blank"><i aria-hidden="true" class="fas fa-link"></i> Del</a>';			
			
		}

		$htmlstr .= '</div>
        </div>';
		
	}
	
wp_reset_postdata();
	
	if($num_post)
	{
		if($paged < $total_pages)
		{
	    $htmlstr .='<button class="pagination" next_update="'.($paged+1).'" total="'.$total_pages.'">Hent flere resultater.</buttom>';
		}
     return $htmlstr;	
	}
	else
	{
		return "<div><!--0 resultater fundet!--></div>";
	};
	
};





function opdate_privoteringsliste_protal_funktion($post_id) {
    
    if ($post_id == 869) 
	{
		 $args = array(
        'post_type'      => 'undervisning',
        'posts_per_page' => -1,
    );

    $undervisning_posts = get_posts($args);

    foreach ($undervisning_posts as $post) {
        // Opdater "prioriteringsliste" med værdien 1000
        update_field('prioriteringsliste', 1001, $post->ID);
    }
		
$start_resultater = get_field('start_resultater', 869);

if( $start_resultater )
{
    $prioritering = 1;
    foreach( $start_resultater as $featured_post )
    {
        update_field('prioriteringsliste', $prioritering, $featured_post);   
        $prioritering++;
    }
}
		
}
	
		
		
}

add_action('save_post', 'opdate_privoteringsliste_protal_funktion');

