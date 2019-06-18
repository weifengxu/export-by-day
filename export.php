<?php
/*
 Plugin Name:   Exportar Medias por Dia
 Description: Exportar Medias seleccionando el dia.
 Version: 1.0.0
 Author: Weifeng Xu
 */

  //--------------------------------------------------------------------------
  add_action( 'export_filters', function() { 
  ?>
  	<p>
    	<ul id="wpse-post-filters" class="wpse-export-filters">
  	  	<li>
          <label><?php _e( 'Exportar por dia:' ); ?></label>
  				<select name="wpse_single_day">
            <option value="0"><?php _e( 'Seleccionar el dia' ); ?></option>
            <?php wpse_export_single_day_options(); ?>
          </select>
        </li>
      </ul>
    </p>
  <?php 
  });

  //--------------------------------------------------------------------------
  function wpse_export_single_day_options( $post_type = 'attachment' ) {
    global $wpdb, $wp_locale;
    $months = $wpdb->get_results( 
      $wpdb->prepare(
        "SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month, Day( post_date ) as day
        FROM {$wpdb->posts}
        WHERE post_type = %s AND post_status != 'auto-draft'
        ORDER BY post_date DESC",
        $post_type 
      ) 
    );
    $month_count = count( $months );
    if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
      return;
    
    foreach ( $months as $date ) 
    {
      if ( 0 == $date->year )
        continue;
      $month = zeroise( $date->month, 2 );
      printf( 
        '<option value="%d-%d-%d">%d. %s %d</option>', 
        $date->year,
        $month,
        $date->day,     
        $date->day,
        $wp_locale->get_month( $month ),
        $date->year
      );
    }
  }

  //--------------------------------------------------------------------------
  add_action( 'admin_head', function(){ 
  ?>
    <script>
      jQuery(document).ready(function($){
    	  $('#wpse-post-filters').appendTo( $('#attachment-filters') );
      });
    </script>
  <?php 
  });

  //--------------------------------------------------------------------------
  add_filter( 'export_args', function( $args )
  {	
    $date = filter_input( INPUT_GET, 'wpse_single_day' );
	  $dt = DateTime::createFromFormat( 'Y-m-d', $date );

	  if( method_exists( $dt, 'format' ) && $Ymd = $dt->format( 'Y-m-d' ) )
	  {

		  $args['start_date'] = $Ymd;

		  $args['end_date'] =  date( 
        'Y-m-d', 
        strtotime( 
          '-1 month', 
          strtotime( 
            '+1 day', 
            strtotime( $Ymd ) 
          ) 
        ) 
      );
	  }
    return $args;
  });
