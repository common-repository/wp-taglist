<?php
/*
Plugin Name: WP Taglistpage
Plugin URI: http://wordpress.org/extend/plugins/wp-taglist/
Description: This Plugin create a taglist overview page with most important tags to each letter category. This plugin will also create a page for each letter, who display all tags of this letter category.&nbsp;| &nbsp;Inspired by Inhouse SEO Hossa!&nbsp;&nbsp;| &nbsp;<a href="options-general.php?page=the_teaglist_config/">Plugin Settings</a> 
Version: 1.1.2
Author: 'derpixler' René Reimann
Author URI: http://www.die-pixler.de
*/

global $wpdb;
global $wp_rewrite;

get_option('wp_taglist_overview_title') ? $overview_title = get_option('wp_taglist_overview_title') : $overview_title = get_the_translation('Themen von A - Z[@_L]Tags from A - Z');
get_option('wp_taglist_detail_title') ? $detail_title = get_option('wp_taglist_detail_title') : $detail_title = get_the_translation('Alle Themen zum Buchstabe  %abc%[@_L]All tags to the letter %abc%');
get_option('wp_taglist_sub_title') ? $sub_title = get_option('wp_taglist_sub_title') : $sub_title = get_the_translation('Themen mit %abc%[@_L]Tags with %abc%');
get_option('wp_taglist_tagcount') ? $tagcount = get_option('wp_taglist_tagcount') : $tagcount = 4;
get_option('wp_taglist_overview_pagetitle') ? $overview_pagetitle = get_option('wp_taglist_overview_pagetitle') : $overview_pagetitle = get_the_translation('Themen von A - Z[@_L]Tags from A - Z');
get_option('wp_taglist_detail_pagetitle') ? $detail_pagetitle = get_option('wp_taglist_detail_pagetitle') : $detail_pagetitle = get_the_translation('Alle Themen zum Buchstabe  %abc%[@_L]All tags to the letter %abc%');


if (get_option('permalink_structure') != ''){
	$permalink = true;
}else{
	$permalink = false;
}

if(get_settings('wp_taglist_urlbase')){$url_str = get_settings('wp_taglist_urlbase');}else{$url_str = 'taglist';}
$plugin_dir = $wpdbb_plugin_dir.'/wp-taglist/';

if(isset($_GET[$url_str])){
	$carakter = array($_GET[$url_str],$url_str);
}else{
	$carakter = explode('/',$_SERVER['REQUEST_URI']);
	krsort($carakter);
}

$carakter = array_clean($carakter);

if($carakter[2] == $url_str){
	add_filter('generate_rewrite_rules', 'taglist_rewrite');
}elseif($carakter[0] == $url_str || $carakter[1] == $url_str){
	add_action('template_redirect', 'load_taglist_template');
	add_filter('generate_rewrite_rules', 'taglist_rewrite');
	add_action('wp_print_styles', 'taglist_stylesheets');
	add_filter('wp_title', 'tag_page_title');
	add_filter('the_content', 'get_the_taglist');
	add_action('the_taglist_title', 'get_the_taglist_title');

}

add_filter('the_tags', 'rewrite_tag_url');

function is_taglist_page() {return true;}

function load_taglist_template () {
	if(@file_exists(TEMPLATEPATH.'/taglist.php')) {
		require_once(TEMPLATEPATH.'/taglist.php');
	}else{
		require_once(WP_PLUGIN_DIR.'/wp-taglist/taglist.php');
	}
	exit;
}

function get_the_taglist_title(){
	global $carakter, $url_str;
  	
	get_option('wp_taglist_overview_title') ? $overview_title = get_option('wp_taglist_overview_title') : $overview_title = get_the_translation('Themen von A - Z[@_L]Letters from A - Z');
	get_option('wp_taglist_detail_title') ? $detail_title = get_option('wp_taglist_detail_title') : $detail_title = get_the_translation('Alle Themen zum Buchstabe %abc%[@_L]All tags to %abc% ');
	$detail_title = str_replace('%abc%',strtoupper($carakter[0]),$detail_title);

	if($carakter[1] == $url_str){
		$t = $detail_title;	
	}else{
		$t = $overview_title;
	}
	echo $t;
}

function the_taglist_title(){do_action('the_taglist_title');}

function get_the_taglist($the_content){
	global $carakter;
	global $url_str;
	global $permalink;

	$tags = get_tags('orderby=count');

	krsort($tags);
	$char_sort_tags = array();
	foreach($tags as $tag){
		$firstchar = strtolower(substr($tag->name,0,1));
		$char[] = $firstchar;
		$array = array(
					'term_id' => $tag->term_id,
        			'name' => $tag->name,
            		'slug' => $tag->slug,
            		'term_group' => $tag->term_group,
            		'term_taxonomy_id' => $tag->term_taxonomy_id,
            		'taxonomy' => $tag->taxonomy,
            		'description' => $tag->description,
            		'parent' => $tag->parent,
            		'count' => $tag->count,
				 );
				
				
		$char_sort_tags[$firstchar][] = $array;
	}
	
		ksort($char_sort_tags);
	
	if($carakter[0] == $url_str){
		get_option('wp_taglist_tagcount') ? $max_tag = get_option('wp_taglist_tagcount') : $max_tag = 4;
		$i=0;
		
		$string = '<ul>';
		foreach($char_sort_tags as $k => $v){
			if($permalink==true){
				$link = '/'.$carakter[0].'/'.$k;
			}else{
				$link = '?'.$url_str.'='.$k;
			}
			get_option('wp_taglist_sub_title') ? $sub_title = get_option('wp_taglist_sub_title') : $sub_title = 'Themen mit %abc%';
			get_option('wp_taglist_count_of_posts') ? $countposts = get_option('wp_taglist_count_of_posts') : $countposts = 'on';
			$sub_title = str_replace('%abc%',strtoupper($k),$sub_title);
			$string .= '<li class="category"><a href="'.get_option('home').$link.'">'.$sub_title.'</a><ul>';
			foreach($char_sort_tags[$k] as $key => $tag){
				if($i < $max_tag){
 					if($permalink==true){
						$link = '/'.$carakter[0].'/'.$k.'/'.strtolower($char_sort_tags[$k][$key]['name']);
					}else{
						$link = '?tag='.strtolower($char_sort_tags[$k][$key]['name']);
					}
					$string .= '<li><a href="'.get_option('home').$link.'">'.$char_sort_tags[$k][$key]['name'].'</a>';
					if($countposts == 'on'){
						$string .= ' ('.$char_sort_tags[$k][$key]['count'].')';
					}
					$string .= '</li>';
				}
				$i++;
			}
			$string .= '</ul></li>';
			$i=0;
		}
		$string .= '</ul>';
 	}else{
		foreach($char_sort_tags[$carakter[0]] as $key => $tag){
				if($permalink==true){
						$link = '/'.$carakter[1].'/'.$carakter[0].'/'.strtolower($char_sort_tags[$carakter[0]][$key]['name']);
				}else{
						$link = '?tag='.strtolower($char_sort_tags[$carakter[0]][$key]['name']);
				}
 					$string .= '<li><a href="'.get_option('home').$link.'">'.$char_sort_tags[$carakter[0]][$key]['name'].'</a>';
					if($countposts == 'on'){
						$string .= ' ('.$char_sort_tags[$carakter[0]][$key]['count'].')';
					}
					$string .= '</li>';
		}
		
 	}
	return $string;
		
}

function get_the_translation($string,$type = null){
	$contents = explode('[@_L]',$string);

	if(WPLANG == 'de_DE'){
		$content = $contents[0];
	}elseif(WPLANG == ''){
		$content = $contents[1];
	}

	if($content){
		return $content;
	}

}

function taglist_rewrite($wp_rewrite) {
	global $wp_rewrite, $url_str;
	
	$themen_rules = array(
		$url_str.'/([a-z])/(.+)' => 'index.php?tag=$matches[2]',
		$url_str.'/([a-z])' => 'index.php?'.$url_str.'=$matches[1]',
		$url_str => 'index.php?'.$url_str,
	);
  $wp_rewrite->rules = $themen_rules + $wp_rewrite->rules;
}


function rewrite_tag_url($the_tags) {
	global $url_str, $permalink;
	$the_tags = get_the_tags();
	if($the_tags){
	foreach($the_tags as $tag){
		$firstchar = strtolower(substr($tag->name,0,1));
		if($permalink==true){
			$tags .= '<a href="'.get_option('home').'/'.$url_str.'/'.$firstchar.'/'.strtolower($tag->name).'">'.$tag->name.'</a>, ';
		}else{
			$tags .= '<a href="'.get_option('home').'/?tag='.strtolower($tag->name).'">'.$tag->name.'</a>, ';
		}
	}
	$the_tags = '<p>Schlagworte: '.$tags.'</p>';
	}
 	return $the_tags;
}


function tag_page_title($title) {
	global $carakter, $url_str;
	
	get_option('wp_taglist_overview_pagetitle') ? $overview_pagetitle = get_option('wp_taglist_overview_pagetitle') : $overview_pagetitle = 'Themen von A - Z';
	get_option('wp_taglist_detail_pagetitle') ? $detail_pagetitle = get_option('wp_taglist_detail_pagetitle') : $detail_pagetitle = 'Alle Tags zum Buchstabe %abc%';
	$detail_pagetitle = str_replace('%abc%',strtoupper($carakter[0]),$detail_pagetitle);

	if($carakter[1] == $url_str){
		$t = $detail_pagetitle;	
	}else{
		$t = $overview_pagetitle;
	}

	return $t;
}

function taglist_stylesheets() {
	if(@file_exists(TEMPLATEPATH.'/taglist.css')) {
		wp_enqueue_style('wp-taglist', get_stylesheet_directory_uri().'/taglist.css', false, '2.50', 'all');
	} else {
		wp_enqueue_style('wp-taglist', plugins_url('wp-taglist/taglist.css'), false, '2.50', 'all');
	}	
}

function array_clean($array,$option = ''){
	$clean_array = array();
	foreach($array as $k => $value){
		if(!$option){
			$value = str_replace(array(',','/','.',':',';','-','_','?','!','"'),'',$value);
		}
	    if ($value != "" && !is_numeric($value)) {
		     $clean_array[] = trim($value);
	    }
	}
	return $clean_array;
}

# Admin Backend #

$prefix = "wp_taglist";
$url = $plugin_dir;


if($permalink == false){
	$pluginpadth = get_bloginfo('url')."/?".$url_str;
}else{
	$pluginpadth = get_bloginfo('url')."/".$url_str;
}

$options = array (
   "0" => array( 
			"type" => 'table',
			"headline" => "WP Taglist Options",
			"desc" => get_the_translation('Hier kannst du deine Taglist-Seite einstellen![@_L]Here you can make your settings!'),
			"content" => array(
							array( 
								"type" => "info",
								"style" => "width:90%;border-top:1px solid #BFBFB3;border-bottom:1px solid # CFCFCF;background:#EFEFD1;",
								"ID" => "plugindescr",
								"class" => "off",
								"value" => '<script>
												jQuery(document).ready( function($) {
													$("#tog").click(function () {
      														$("p#info").toggle(
																function(){
																	if(!$("#plugindescr").attr("class")){
																		$("#plugindescr").attr("class","");
																	
																	}else{
																		$("#plugindescr").attr("class","on");
																	
																	}
																}
															);
    												});
												});
											</script>
'.get_the_translation('Was das wp-taglist Pligin alles kann.[@_L]What the wp-taglist Pligin can do').' <a id="tog" style="cursor:pointer;">'.get_the_translation('Lies es hier![@_L]read it here!').'</a>
<p id="info" style="display:none">
WP-Taglist bringt ein eigenes Template und eine eigene CSS-Datei, taglist.php und taglist.css. Diese befinden sich im Pluginordner. 
Um die Taglist individuellen Anpassungen zu unterziehen können die Templte-Datei und die CSS-Datei in den aktuelle verwendeten Themeordner verschoben werden.<br /><br />

Mit <i>&lt;? is_taglist_page(); ?&gt;</i> steht eine Prüfmöglichkeit zur verfügung, ob es sich aktuell um eine Taglistseite handelt.
</p> ',	
								),
							array( 
								"type" => "input",
								"name" => get_the_translation('Urlpfad[@_L]Urlpath'),
								"desc" => get_the_translation('Hier kannst du bestimmen unter welchem Urlpfad deine Taglistseite zu erreichen ist.<br /> Beispiel[@_L]Here you can determine under what Urlpfad Taglist page your reach is.<br />Sample')." <a href='".$pluginpadth."' target='blank'>".$pluginpadth."</a>",
								"id" => "urlbase",
								"style" => "width:90%;border:1px solid #B3DAEF;",
								"value" => $url_str,	
								),
							array( 
								"type" => "radio",
								"name" => get_the_translation('Zeige anzahl Posts[@_L]Show post count'),
								"desc" => get_the_translation('[@_L]'),
								"id" => "count_of_posts",
								"style" => "width:30px;",
								"value" => array('on' => get_the_translation('anzeigen[@_L]show'),'off' => get_the_translation('ausblenden[@_L]hide')),	
								),
							array( 
								"type" => "subheadline",
								"value" => get_the_translation('&Uuml;bersichtsseite[@_L]Overview page'),
								),	 
							array( 
								"type" => "input",
								"name" => get_the_translation('Seitentitel[@_L]Pagetitle'),
								#"desc" => "Als Standartwert ist hier 'Themen von A - Z' eingetragen",
								"style" => "width:90%;border:1px solid #B3DAEF;",
								"id" => "overview_pagetitle",
								"value" => $overview_pagetitle,	
								),
							array( 
								"type" => "input",
								"name" => get_the_translation('&Uuml;berschrift[@_L]Headline'),
								#"desc" => "Als Standartwert ist hier 'Themen von A - Z' eingetragen",
								"style" => "width:90%;border:1px solid #B3DAEF;",
								"id" => "overview_title",
								"value" => $overview_title,	
								),
							array( 
								"type" => "input",
								"name" => get_the_translation('Anzahl Tags[@_L]Count of tags'),
								"desc" => get_the_translation('Anzahl der gezeigten Tags zu jeder Buchstabenkategorie.[@_L]Number of displayed tags to each letter category'),
								"style" => "width:90%;border:1px solid #B3DAEF;",
								"id" => "tagcount",
								"value" => $tagcount,	
								),
							array( 
								"type" => "subheadline",
								"value" => get_the_translation('Detailseiten[@_L]Detail Pages'),
								),
							array( 
								"type" => "input",
								"name" => get_the_translation('Seitentitel[@_L]Pagetitle'),
								"style" => "width:90%;border:1px solid #B3DAEF;",
								"desc" => get_the_translation("%abc% wird durch den jeweiligen Buchstaben ersetzt![@_L]%abc% is replaced by the respective letters!"),
								"id" => "detail_pagetitle",
								"value" => $detail_pagetitle,	
								),	 
							array( 
								"type" => "input",
								"name" => get_the_translation('&Uuml;berschrift[@_L]Headline'),
								"style" => "width:90%;border:1px solid #B3DAEF;",
								"desc" => get_the_translation("%abc% wird durch den jeweiligen Buchstaben ersetzt![@_L]%abc% is replaced by the respective letters!"),
								"id" => "detail_title",
								"value" => $detail_title,	
								),
							array( 
								"type" => "input",
								"name" => get_the_translation('Zwischen&uuml;berschriften[@_L]subtitles'),
								"style" => "width:90%;border:1px solid #B3DAEF;",
								"desc" => get_the_translation("Zwischenüberschrift auf den Übersichtsseite, %abc% wird durch den jeweiligen Buchstaben ersetzt.[@_L]subtitles on the overview pages, %abc% is replaced by the respective letters!"),
								"id" => "sub_title",
								"value" => $sub_title,	
								), 
						), 
       		),
);

// Hook for adding admin menus
add_action('admin_menu', 'taglist_add_pages');

// action function for above hook
function taglist_add_pages() {
    global $url;
    // Add a new top-level menu (ill-advised):
    add_options_page('WP Taglist', 'WP Taglist', 'administrator', 'the_teaglist_config', 'the_teaglist_config');
}



// mt_manage_page() displays the page content for the Test Manage submenu
function the_teaglist_config() {

    global $prefix, $url, $options, $wpdb;

	#echo '<pre>';print_r($_REQUEST);echo '</pre>';
    #echo basename(__FILE__);
		
	if ( 'save' == $_REQUEST['action'] ) {
		foreach ($options as $k => $value) { 
			foreach ($options[$k]['content'] as $key => $value) { 
				
				if($options[$k]['content'][$key]['type'] == 'select'){
					foreach ($options[$k]['content'][$key] as $selectboxs) {
						 if(is_array($selectboxs)){
							foreach($selectboxs as $selectboxnum => $sel){
									$var = $prefix.'_'.$selectboxs[$selectboxnum]['id'];
									echo $var.', '.$_REQUEST[$var].'<br />';
									if(isset($_REQUEST[$var])) {
	 									update_option( $var, $_REQUEST[ $var ]  ); 
									}else{ 
										#delete_option( $value['id'] ); 
									}
							}
						 }
					}
					
				}else{
					if( isset($_REQUEST[$value['id']])) {
						update_option( $prefix.'_'.$value['id'], $_REQUEST[ $value['id'] ] );
					}else{ 
						#delete_option( $value['id'] ); 
					} 			
				}
			  }
			}
			#wp_redirect('admin.php?page=the_teaglist_config&saved=true', 301);
			echo '<meta http-equiv="refresh" content="0; url=options-general.php?page=the_teaglist_config&saved=true">';

        }else if( 'reset' == $_REQUEST['action'] ) {
			echo 'sss';
            foreach ($options as $value) {
                delete_option( $value['id'] ); 
                update_option( $value['id'], '' );
			}
			echo '<meta http-equiv="refresh" content="0; url=options-general.php?page=the_teaglist_config&reset=true">';
        #header("Location: themes.php?page=shoppingcartconfig.php&reset=true");
        #die;
	}

    if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.get_the_translation('einstellungen gespeichert[@_L]settings saved').'.</strong></p></div>';
 
    
?>
<div class="wrap">
	
<div class="icon32" id="icon-plugins"><br></div>
<h2>WP Taglist <?=get_the_translation('Einstellungen[@_L]Settings')?></h2>

<form method="post">
<?php
/* 
echo '<Pre>';
Print_r($options);
echo '</Pre>';
*/

$i = 0;
foreach ($options as $k => $value) { 
	if($options[$k]['type'] == 'table'){
		echo '<table width="100%"><tr><td style="width: 70%;"><table class="widefat" style="width: 100%;margin-top:20px;float:left;"><thead><tr><th style="text-align: left;" scope="col" colspan="10">'.$options[$k]['headline'].'<span> - '.$options[$k]['desc'].'</span></th></tr></thead><tbody>';
			/*if(WPENVIRONMENT == 'dev'){
				echo '<tr><td style="padding:15px 0 0 14px">Sprache </td>';
				echo '<td class="'.$odd.'" style="padding:10px 0 15px">';
				echo get_settings($prefix.'_lang');
				echo '<select name="lang">';
				echo '<option value="de_DE">deutsch</option><option value="en_EN">Englisch</option>';
				echo '</select></td></tr>';			
			}*/
			foreach ($options[$k]['content'] as $key => $value) { 
				if($key % 2 < 1){$odd = '';}else{$odd = 'alternate';}
				if($options[$k]['content'][$key]['id']){
				echo '<tr><td class="'.$odd.'" style="padding:15px 0 0 14px">';
				echo $options[$k]['content'][$key]['name'];
				echo '</td>';
				echo '<td class="'.$odd.'" style="padding:10px 0 15px">';
				if($options[$k]['content'][$key]['type'] == 'textarea'){
				
				}elseif($options[$k]['content'][$key]['type'] == 'input'){
					if($options[$k]['content'][$key]['style']){$style = ' style="'.$options[$k]['content'][$key]['style'].'" ';}
					echo '<input type="text"'.$style.'class="regular-text" value="'.$options[$k]['content'][$key]['value'].'" id="'.$options[$k]['content'][$key]['id'].'" name="'.$options[$k]['content'][$key]['id'].'">';
				}elseif($options[$k]['content'][$key]['type'] == 'select'){
					foreach ($options[$k]['content'][$key] as $selectboxs) {
						 if(is_array($selectboxs)){
							foreach($selectboxs as $selectboxnum => $sel){
								$selected = get_settings($prefix.'_'.$selectboxs[$selectboxnum]['id']);
								if(is_array($sel['option'])){
									foreach($sel['option'] as $sel_k => $sel_val){
										if($sel_val == $selected){
											 #$select = ' selected="selected"';
											$select = $selected;
										}
									}
								}
							#echo $select.' == '.$val;
								echo '<select id="'.$prefix.'_'.$selectboxs[$selectboxnum]['id'].'" name="'.$prefix.'_'.$selectboxs[$selectboxnum]['id'].'">';
									if(is_array($selectboxs[$selectboxnum]['option'])){
										foreach($selectboxs[$selectboxnum]['option'] as $ke => $val){
											$selected = get_settings($prefix.'_'.$selectboxs[$selectboxnum]['id']);
											#echo 'schnubbi';
											if($select == $val){
											 	$select = ' selected="selected"';
											}
											
											echo '<option value="'.$val.'"'.$select.'>'.$ke.'</option>';
										}
						 			}elseif(is_int($selectboxs[$selectboxnum]['option'])){
										echo '<option value="">'.$selectboxs[$selectboxnum]['id'].'</option>';
										for($c=0;$c<$selectboxs[$selectboxnum]['option'];$c++){
											echo '<option '.$selected.' value="'.$c.'">'.$c.'</option>';
										}
									}
								echo '</select>';
							}
						 }
					}
				}elseif($options[$k]['content'][$key]['type'] == 'checkbox'){
					
				}elseif($options[$k]['content'][$key]['type'] == 'radio'){
					if($options[$k]['content'][$key]['style']){$style = ' style="'.$options[$k]['content'][$key]['style'].'" ';}
					get_option('wp_taglist_count_of_posts') ? $count_of_posts = get_option('wp_taglist_count_of_posts') : $count_of_posts = 'on';
					foreach($options[$k]['content'][$key]['value'] as $value => $decs){
						if($count_of_posts == $value){
							echo '<input type="radio"'.$style.'class="regular-text" value="'.$value.'" checked="checked" id="'.$options[$k]['content'][$key]['id'].'" name="'.$options[$k]['content'][$key]['id'].'">'.$decs.'<br />';
						}else{
							echo '<input type="radio"'.$style.'class="regular-text" value="'.$value.'" id="'.$options[$k]['content'][$key]['id'].'" name="'.$options[$k]['content'][$key]['id'].'">'.$decs.'<br />';
						}
						
					}
				}
						
				if($options[$k]['content'][$key]['desc']){
							echo '<br /><span>'.$options[$k]['content'][$key]['desc'].'</span>';
				}
				echo '</td></tr>';
				}else{
					if($options[$k]['content'][$key]['type'] == 'textarea'){
						echo '<tr><td class="error"><b>Im Option Array wurde keine ID für das Textarea = '.$options[$k]['content'][$key]['name'].' vergeben!</b></td></tr>';
					}elseif($options[$k]['content'][$key]['type'] == 'input'){
						echo '<tr><td class="error"><b>Im Option Array wurde keine ID für das Inputfeld = '.$options[$k]['content'][$key]['name'].' vergeben!</b></td></tr>';
					}elseif($options[$k]['content'][$key]['type'] == 'select'){
						echo '<tr><td class="error"><b>Im Option Array wurde keine ID für die Selectbox = '.$options[$k]['content'][$key]['name'].' vergeben!</b></td></tr>';
					}elseif($options[$k]['content'][$key]['type'] == 'checkbox'){
						echo '<tr><td class="error"><b>Im Option Array wurde keine ID für die Checkboxen = '.$options[$k]['content'][$key]['name'].' vergeben!</b></td></tr>';
					}elseif($options[$k]['content'][$key]['type'] == 'radio'){
						echo '<tr><td class="error"><b>Im Option Array wurde keine ID für die Radiobutons  = '.$options[$k]['content'][$key]['name'].' vergeben!</b></td></tr>';
					}elseif($options[$k]['content'][$key]['type'] == 'subheadline'){
						echo '<tr><td style="background-color:#DFDFDF;border-bottom:#CFCFCF 1px solid;" colspan="5"><b>'.$options[$k]['content'][$key]['value'].'</b></td></tr>';
					}elseif($options[$k]['content'][$key]['type'] == 'info'){
						echo '<tr><td style="'.$options[$k]['content'][$key]['style'].'" class="'.$options[$k]['content'][$key]['class'].'" id="'.$options[$k]['content'][$key]['ID'].'" colspan="5">'.$options[$k]['content'][$key]['value'].'</td></tr>';
					}
				}	

			}
			
		?>
			<tr><td colspan="4" align="right">
				
<p class="submit"><!--
<div id="delete-action" style="padding-right:20px;">
	<a href="admin.php?page=the_teaglist_config&action=reset&reset=true" class="submitdelete deletion">reset settings</a>
</div>	-->
<input name="save" type="submit" value="<?=get_the_translation('Einstellungen speichern[@_L]Save settings')?>"  class="button-primary"/>    
<input type="hidden" name="action" value="save"/>
</p>
</form>
			</td></tr>
			
		<?
		echo '</tbody></table>';	
	}
	if($options[$k]['type'] == 'div'){
		if($options[$k]['class']){$class = ' class="'.$options[$k]['class'].'"';}
		if($options[$k]['id']){$id = ' id="'.$options[$k]['id'].'"';}
		echo '<div'.$id.$class.'>'.$options[$k]['headline'];
		echo '</div>';
	}
} 

?>
</td>
<td valign="top" style="width:40%;padding-left:20px;">
<table class="widefat" style="width: 100%;margin-top:20px;">
	<thead>
		<tr>
			<th style="text-align: left;" scope="col" colspan="10"><?=get_the_translation('Der Author![@_L]The Author!')?></span></th>
		</tr>
	</thead>
	<tbody>
	<tr>
		<td>
			<img style="float:right;padding:7px 0 0 10px;" src="http://about.die-pixler.de/files/img.jpg" width="30%"/>
			<p>
			<?=get_the_translation('Hi ich bin Ren&eacute; Reimann - ich m&ouml;cht mich kurz vortellen und f&uuml;r dein Interesse an diesem Plugin bedanken. Ich bin leidenschaftlicher Mediengestalter für Digital- & Printmedien. Wenn ich nicht gerade tolle Ideen von Inhouse SEO Hossa umsetze dann entwickle ich 1A Fontend- und Backendl&ouml;sungen oder setze mein B&ouml;rsenwissen bei Spekulationen an der B&ouml;rse ein.[@_L]Hi I´m René Reimann - I´d like me supposed this short and thank you for your interest in this plugin. Im passionate media designer for digital and print media. If I take not great ideas of in-house SEO Hossa develop   then I develop 1A Fontend and backend solutions, in that case used i my market knowledge for speculations on the exchange.')?>
			</p>
			<a href="http://twitter.com/DerPixler"><?=get_the_translation('Folge mir auf [@_L]Follow me on ')?>Twitter</a> <?=get_the_translation('oder besuch doch mein [@_L] or take a look at my ')?><a href="http://www.die-pixler.de?plugin">Portfolio </a>
		</td>
	</tr>
	<tr><td style="background-color:#EEEEEE;" colspan="5"><b><?=get_the_translation('Sag danke![@_L]Say thank you')?></b> </td></tr>
	<tr>
		<td>
			<b><?=get_the_translation('Gefällt dir dieses Plugin?[@_L]You like this plugin?')?></b><br /> <?=get_the_translation('Hast du den ein oder anderen Euro / Dollar damit verdient? Bedanke dich mit:[@_L]Do you make the one or other euro / dollar width this plugin? Than say thank you with:')?><br />
		
			<ul style="list-style:disc outside none;padding-left:30px;">
				<li><a href="http://www.amazon.de/registry/wishlist/13O28PLXICVA7/ref=cm_wl_act_vv?_encoding=UTF8&visitor-view=1&reveal=">Amazon <?=get_the_translation('Wunschzettel[@_L]Wishlist')?></a></li>
				<li><a href="http://twitter.com/home?status=<?=get_the_translation('SEO+Boost+für+dein+%23Wordpress+blog+mit+dem+%23wptaglist+%23Plugin+(danke+@derpixler)+http://tinyurl.com/wp-taglist[@_L]SEO+Boost+for+your+%23Wordpress+blog+with+the+%23wptaglist+%23Plugin+(thanks+@derpixler)+http://tinyurl.com/wp-taglist')?>"><?=get_the_translation('einem [@_L]a ')?>Tweet</a> <?=get_the_translation('oder mit [@_L]or ')?><a href="http://twitter.com/home?status=<?=get_the_translation('Hi+@derpixler+richte+dem+Inhouse+%23SEO+Hossa+aus,+das+%23plugin+%23wptaglist+ist+eine+geniale+Idee+http://tinyurl.com/wp-taglist[@_L]Hi+@derpixler+judge+from+the+inhouse+%23SEO+Hossa,+the+%23plugin+%23wptaglist+is+a+great+idea+http://tinyurl.com/wp-taglist')?>"><?=get_the_translation('diesem[@_L]this one')?></a></li>
				<li><a href="mailto:rene@die-pixler.de"><?=get_the_translation('Netten Worte[@_L]Kind words')?></a></li>
				<li><a href="mailto:rene@die-pixler.de"><?=get_the_translation('einem Auftrag[@_L]a job')?></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td align="center">
			
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="SWBKTFYQZAHL4">
				<input  width="50%" type="image" src="https://www.paypal.com/<?=get_the_translation('de_DE[@_L]en_US');?>/DE/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal.">
				<img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
			</form>
		</td>
	</tr>
	</tbody>
</table>

</td>
</tr>
</table>

<?php

}

?>
