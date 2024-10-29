<?php
/*
Plugin Name: Archive-AJAX
Plugin URI: http://wordpress.org/extend/plugins/archive-ajax/
Description: A way to show large archives by month.
Author: Rafael Poveda - RaveN
Author URI: http://mecus.es/herramientas/plugins/
Version: 0.1
Stable tag: 0.1
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

function mes($mes)
{
  $res = "";
  if(date( 'F', mktime(0, 0, 0, $mes) ) == "January")
    $res = "Enero";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "February")
    $res = "Febrero";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "March")
    $res = "Marzo";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "April")
    $res = "Abril";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "May")
    $res = "Mayo";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "June")
    $res = "Junio";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "July")
    $res = "Julio";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "August")
    $res = "Agosto";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "September")
    $res = "Septiembre";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "October")
    $res = "Octubre";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "November")
    $res = "Noviembre";
  else if(date( 'F', mktime(0, 0, 0, $mes) ) == "December")
    $res = "Diciembre";
  return $res;
}

function archive_ajax_widgets_init() {
	if ( function_exists('register_sidebar') )
		register_sidebar(array(
			'name' => __( 'Barra lateral derecha', 'twentyten' ),
			'id' => 'sidebar-derecha-widget-area',
			'description' => __( 'Zona para widgets en la barra lateral derecha del blog.', 'twentyten' ),
			'before_widget' => '<div class="cont_sidebar">', // Removes <li>
			'after_widget' => '</div>', // Removes </li>
			'before_title' => '<h2>', // Replaces <h2>
			'after_title' => '</h2>', // Replaces </h2>
		));
}
/** Register sidebars by running twentyten_widgets_init() on the widgets_init hook. */
add_action( 'widgets_init', 'archive_ajax_widgets_init' );

/* Function that registers our widget. */
function archive_ajax_load_widgets() {
	register_widget( 'WP_Widget_Archivo_Chic' );
	
	do_action('widgets_init');
}



/* Add our function to the widgets_init hook. */
add_action( 'init', 'archive_ajax_load_widgets', 1 );


class WP_Widget_Archivo_Chic extends WP_Widget {

	function WP_Widget_Archivo_Chic() {
		$widget_ops = array('classname' => 'widget_archive_chic', 'description' => __( "AJAX Archive by year.") );
		$this->WP_Widget('archive_chic', __('AJAX Archive'), $widget_ops);
	}

	function widget( $args, $instance ) {
		global $wpdb;
		
		extract($args);
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		// Use current theme search form if it exists
		?>
<?php
/**/
$years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' AND YEAR(post_date) <= YEAR (CURRENT_DATE)  ORDER BY post_date DESC");
?> <script>

jQuery(function($){
  // by default hide all child nodes
  $('#nav>li>ul').hide();
   
  // find li with a class of current
  $currentnode = $('#nav li.current');
  
  if ($currentnode.length){
    // there is a node with the class of current so
    // check to see if it has children
    if ($currentnode.find('ul').length){
      // the current node has children so show them
      $currentnode.find('ul').show();
    }
    else if (!$currentnode.parent().is('#nav')){
      // the current node is a child so show siblings
      $currentnode.parent().show();
    }
  }
   
  // code to handle expanding on mouseover
  $('#nav>li').bind('mouseover', function(){
    // check that the menu is not currently animated
    if ($('#nav ul:animated').length==0) {
      // create a reference to the active element (this)
      // so we don't have to keep creating a jQuery object
      $heading = $(this);
      // create a reference to visible sibling elements
      // so we don't have to keep creating a jQuery object
      $expandedSiblings = $heading.siblings().find('ul:visible');
      if ($expandedSiblings.length!=0) {
        $expandedSiblings.slideUp(500, function(){
          $heading.find('ul').slideDown(500);
        });
      }
      else {
        $heading.find('ul').slideDown(1000);
      }
    }
  });
});
</script>
<ul id="nav"><?php
$counter = 1;

foreach($years as $year) :
{
echo "<li>$year";
?>

	<ul>
		<?	$months = $wpdb->get_col("SELECT DISTINCT MONTH(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' AND YEAR(post_date) = '".$year."' ORDER BY post_date DESC");
			foreach($months as $month) :
			{
			$num_mes = $wpdb->get_col("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' AND MONTH(post_date) = '".$month."' AND YEAR(post_date) = '".$year."'" );
			?>
			<li <?php if ( '1' == $counter ) echo 'class="current"'; ?>><a href="<?php echo get_month_link($year, $month); $counter++; ?>">
      <?php 
      echo mes($month);
      ?>
      </a>
      <?php echo " (".$num_mes[0].")"; ?>
      </li>
			<?php }endforeach;?>
		</ul></li>
<?php }
endforeach; ?>
</ul>
  		<?php

		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = $instance['title'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

}


add_action('wp_head', 'archive_ajax_css');
 
function archive_ajax_css() {
?>
<link rel="stylesheet" href="<?php echo plugins_url( 'archive-ajax.css' , __FILE__ ); ?>" type="text/css" media="screen, projection" />
<?php
}
