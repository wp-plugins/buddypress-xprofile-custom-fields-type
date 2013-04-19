<?php
/*
    Plugin Name: Buddypress Xprofile Custom Fields Type
    Description: Buddypress installation required!! Add more custom fields type to extended profiles in buddypress: Birthdate, Email, Web, Datepicker. If you need more fields type, you are free to add them yourself or request us at info@atallos.com.
    Version: 1.5.3
    Author: Atallos Cloud
    Author URI: http://www.atallos.com/
    Plugin URI: http://www.atallos.com/portfolio/buddypress-xprofile-custom-fields-type/
*/
//load text domain
function bxcft_load_textdomain() {
    $locale = apply_filters( 'bxcft_load_load_textdomain_get_locale', get_locale() );
	// if load .mo file
	if ( !empty( $locale ) ) {
		$mofile_default = sprintf( '%slang/%s.mo', plugin_dir_path(__FILE__), $locale );
		$mofile = apply_filters( 'bxcft_load_textdomain_mofile', $mofile_default );

        if ( file_exists( $mofile ) ) 
			load_textdomain( "bxcft", $mofile );
	}
}
add_action ( 'bp_init', 'bxcft_load_textdomain', 2 );

function bxcft_add_new_xprofile_field_type($field_types){
   $new_field_types = array('birthdate', 'email', 'web', 'datepicker',
       'select_custom_post_type', 'multiselect_custom_post_type', 
       'checkbox_acceptance', 'image', 'file');
   $field_types = array_merge($field_types, $new_field_types);
   return $field_types;
}
add_filter( 'xprofile_field_types', 'bxcft_add_new_xprofile_field_type' );

function bxcft_admin_render_new_xprofile_field_type($field, $echo = true) {
   $html = '';
   switch ( $field->type ) {
       case 'image':
       case 'file':
           $html .= '<input type="file" name="field_'.$field->id.'" id="'.$field->id.'" class="input-file" /> ';
           break;
       
       case 'checkbox_acceptance':
           $html .= '<input type="checkbox" name="field_'.$field->id.'" id="'.$field->id.'" class="input-checkbox" value="" /> ';
           $html .= $field->description;
           $field->description = '';
           break;
       
       case 'select_custom_post_type':
           $childs = $field->get_children();
           if (isset($childs) && count($childs) > 0 && is_object($childs[0])) {
               // Get the name of custom post type.
               $custom_post_type = $childs[0]->name;
               // Get the posts of custom post type.
               $loop = new WP_Query(array('posts_per_page' => -1, 'post_type' => $custom_post_type, 'order_by' => 'title', 'order' => ASC ));
           }
           $select_custom_post_type = BP_XProfile_ProfileData::get_value_byid( $field->id );
           $html .= '<select name="field_'.$field->id.'" id="field_'.$field->id.'">';
           if (isset($loop)) {
           foreach ($loop->posts as $post) {
                $html .= '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
           }
           }
           $html .= '</select>';
           break;
           
       case "multiselect_custom_post_type":
           $childs = $field->get_children();
           if (isset($childs) && count($childs) > 0 && is_object($childs[0])) {
               // Get the name of custom post type.
               $custom_post_type = $childs[0]->name;
               // Get the posts of custom post type.
               $loop = new WP_Query(array('posts_per_page' => -1, 'post_type' => $custom_post_type, 'order_by' => 'title', 'order' => ASC ));
           }
           $select_custom_post_type = BP_XProfile_ProfileData::get_value_byid( $field->id );
           $html .= '<select name="field_'.$field->id.'" id="field_'.$field->id.'" multiple="multiple">';
           if (isset($loop)) {
           foreach ($loop->posts as $post) {
                $html .= '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
           }
           }
           $html .= '</select>';
           break;
       
       case "datepicker":
           $datepicker = BP_XProfile_ProfileData::get_value_byid( $field->id );
           $html .= '<input type="date" name="field_'.$field->id.'" id="'.$field->id.'" class="input" value="" />';
           break;
       
       case "web":
           $web = BP_XProfile_ProfileData::get_value_byid( $field->id );
           $html .= '<input type="url" name="field_'.$field->id.'" id="'.$field->id.'" class="input" placeholder="'.__('yourwebsite.com', 'bxcft').'" value="" />';
           break;
       
       case "email":
           $email = BP_XProfile_ProfileData::get_value_byid( $field->id );
           $html .= '<input type="email" name="field_'.$field->id.'" id="'.$field->id.'" class="input" placeholder="'.__('example@mail.com', 'bxcft').'" value="" />';
           break;
       
       case "birthdate":
           $date = BP_XProfile_ProfileData::get_value_byid( $field->id );

           // Set day, month, year defaults
           $day   = '';
           $month = '';
           $year  = '';

           if ( !empty( $date ) ) {

               // If Unix timestamp
               if ( is_numeric( $date ) ) {
                   $day   = date( 'j', $date );
                   $month = date( 'F', $date );
                   $year  = date( 'Y', $date );

               // If MySQL timestamp
               } else {
                   $day   = mysql2date( 'j', $date );
                   $month = mysql2date( 'F', $date, false ); // Not localized, so that selected() works below
                   $year  = mysql2date( 'Y', $date );
               }
           }

           // Check for updated posted values, and errors preventing
           // them from being saved first time.
           if ( !empty( $_POST['field_' . $field->id . '_day'] ) ) {
               if ( $day != $_POST['field_' . $field->id . '_day'] ) {
                   $day = $_POST['field_' . $field->id . '_day'];
               }
           }

           if ( !empty( $_POST['field_' . $field->id . '_month'] ) ) {
               if ( $month != $_POST['field_' . $field->id . '_month'] ) {
                   $month = $_POST['field_' . $field->id . '_month'];
               }
           }

           if ( !empty( $_POST['field_' . $field->id . '_year'] ) ) {
               if ( $year != date( "j", $_POST['field_' . $field->id . '_year'] ) ) {
                   $year = $_POST['field_' . $field->id . '_year'];
               }
           }

           // Day.
           $html .= '<select name="field_'.$field->id.'_day" id="field_'.$field->id.'_day">';
           $html .= '<option value=""' . selected( $day, '', false ) . '>--</option>';
           for ( $i = 1; $i < 32; ++$i ) {
               $html .= '<option value="' . $i .'"' . selected( $day, $i, false ) . '>' . $i . '</option>';
           }
           $html .= '</select>';

           // Month.
           $html .= '<select name="field_'.$field->id.'_month" id="field_'.$field->id.'_month">';
           $eng_months = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
           $months = array(
               __( 'January', 'buddypress' ),
               __( 'February', 'buddypress' ),
               __( 'March', 'buddypress' ),
               __( 'April', 'buddypress' ),
               __( 'May', 'buddypress' ),
               __( 'June', 'buddypress' ),
               __( 'July', 'buddypress' ),
               __( 'August', 'buddypress' ),
               __( 'September', 'buddypress' ),
               __( 'October', 'buddypress' ),
               __( 'November', 'buddypress' ),
               __( 'December', 'buddypress' )
           );
           $html .= '<option value=""' . selected( $month, '', false ) . '>------</option>';
           for ( $i = 0; $i < 12; ++$i ) {
               $html .= '<option value="' . $eng_months[$i] . '"' . selected( $month, $eng_months[$i], false ) . '>' . $months[$i] . '</option>';
           }
           $html .= '</select>';

           // Year.
           $html .= '<select name="field_'.$field->id.'_year" id="field_'.$field->id.'_year">';
           $html .= '<option value=""' . selected( $year, '', false ) . '>----</option>';
           for ( $i = date('Y')-1; $i > 1901; $i-- ) {
               $html .= '<option value="' . $i .'"' . selected( $year, $i, false ) . '>' . $i . '</option>';
           }
           $html .= '</select>';
           break;

       default:
           // Field type unrecognized.
           // $html = "<p>".__('Field type unrecognized', 'bxcft')."</p>";
           break;
   }

   if ($echo){
       echo $html;
       return;
   } else {
       return $html;
   }

}
add_filter( 'xprofile_admin_field', 'bxcft_admin_render_new_xprofile_field_type' );

function bxcft_edit_render_new_xprofile_field($echo = true) {
   global $field;
   if(empty ($echo)){
       $echo = true;
   }

   $uploads = wp_upload_dir();

   ob_start();
       if ( bp_get_the_profile_field_type() == 'birthdate' ) {
           $date = BP_XProfile_ProfileData::get_value_byid( $field->id );
           // Set day, month, year defaults
           $day   = '';
           $month = '';
           $year  = '';

           if ( !empty( $date ) ) {

               // If Unix timestamp
               if ( is_numeric( $date ) ) {
                   $day   = date( 'j', $date );
                   $month = date( 'F', $date );
                   $year  = date( 'Y', $date );

               // If MySQL timestamp
               } else {
                   $day   = mysql2date( 'j', $date );
                   $month = mysql2date( 'F', $date, false ); // Not localized, so that selected() works below
                   $year  = mysql2date( 'Y', $date );
               }
           }

           // Check for updated posted values, and errors preventing
           // them from being saved first time.
           if ( !empty( $_POST['field_' . $field->id . '_day'] ) ) {
               if ( $day != $_POST['field_' . $field->id . '_day'] ) {
                   $day = $_POST['field_' . $field->id . '_day'];
               }
           }

           if ( !empty( $_POST['field_' . $field->id . '_month'] ) ) {
               if ( $month != $_POST['field_' . $field->id . '_month'] ) {
                   $month = $_POST['field_' . $field->id . '_month'];
               }
           }

           if ( !empty( $_POST['field_' . $field->id . '_year'] ) ) {
               if ( $year != date( "j", $_POST['field_' . $field->id . '_year'] ) ) {
                   $year = $_POST['field_' . $field->id . '_year'];
               }
           }
       ?>
           <div class="datebox">
               <label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) : ?>required<?php endif; ?>" for="<?php bp_the_profile_field_input_name(); ?>_day"><?php bp_the_profile_field_name(); ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bxcft'); } ?></label>

               <select name="<?php bp_the_profile_field_input_name(); ?>_day" id="<?php bp_the_profile_field_input_name(); ?>_day" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>
                   <option value=""<?=selected( $day, '', false )?>>--</option>
               <?php
                   for ( $i = 1; $i < 32; ++$i ) {
                       echo '<option value="' . $i .'"' . selected( $day, $i, false ) . '>' . $i . '</option>';
                   } 
               ?>

               </select>

               <select name="<?php bp_the_profile_field_input_name() ?>_month" id="<?php bp_the_profile_field_input_name(); ?>_month" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>
               <?php
                   $eng_months = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
                   $months = array(
                       __( 'January', 'buddypress' ),
                       __( 'February', 'buddypress' ),
                       __( 'March', 'buddypress' ),
                       __( 'April', 'buddypress' ),
                       __( 'May', 'buddypress' ),
                       __( 'June', 'buddypress' ),
                       __( 'July', 'buddypress' ),
                       __( 'August', 'buddypress' ),
                       __( 'September', 'buddypress' ),
                       __( 'October', 'buddypress' ),
                       __( 'November', 'buddypress' ),
                       __( 'December', 'buddypress' )
                   );
               ?>
               <option value=""<?=selected( $month, '', false )?>>------</option>
               <?php
                   for ( $i = 0; $i < 12; ++$i ) {
                       echo '<option value="' . $eng_months[$i] . '"' . selected( $month, $eng_months[$i], false ) . '>' . $months[$i] . '</option>';
                   }
               ?>
               </select>
               <?php
               $birthdate_start_year = date('Y')-1;
               ?>               

               <select name="<?php bp_the_profile_field_input_name() ?>_year" id="<?php bp_the_profile_field_input_name(); ?>_year" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true" required="required"<?php endif; ?>>
               <option value=""<?=selected( $year, '', false )?>>----</option>
               <?php
                   for ( $i = $birthdate_start_year; $i > 1901; $i-- ) {
                       echo '<option value="' . $i .'"' . selected( $year, $i, false ) . '>' . $i . '</option>';
                   }
               ?>
               </select>
           </div>
       <?php
       } 
       elseif ( bp_get_the_profile_field_type() == 'email' ) {
       ?>
        <div class="input-email">
            <label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) : ?>required<?php endif; ?>" for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bxcft'); } ?></label>
            <input type="email" name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true" required="required"<?php endif; ?> class="input" value="<?php bp_the_profile_field_edit_value() ?>" placeholder="<?php _e('example@mail.com', 'bxcft'); ?>" />
       </div>
       <?php
       } 
       elseif ( bp_get_the_profile_field_type() == 'web' ) {
       ?>
        <div class="input-web">
            <label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) : ?>required<?php endif; ?>" for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bxcft'); } ?></label>
            <input type="url" name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true" required="required"<?php endif; ?> class="input" value="<?php bp_the_profile_field_edit_value() ?>" placeholder="<?php _e('http://yourwebsite.com', 'bxcft'); ?>" />
       </div>
       <?php
       }
       elseif ( bp_get_the_profile_field_type() == 'datepicker' ) {
       ?>
        <div class="input-web">
            <label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) : ?>required<?php endif; ?>" for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bxcft'); } ?></label>
            <input type="date" name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true" required="required"<?php endif; ?> class="input" value="<?php bp_the_profile_field_edit_value() ?>" />
       </div>
       <?php
       }
       elseif ( bp_get_the_profile_field_type() == 'select_custom_post_type' ) {
           $custom_post_type_selected = BP_XProfile_ProfileData::get_value_byid( $field->id );
           if ( isset( $_POST['field_' . $field->id] ) && $_POST['field_' . $field->id] != $option_value ) {
                if ( !empty( $_POST['field_' . $field->id] ) ) {
                    $custom_post_type_selected = $_POST['field_' . $field->id];
                }
           }
           // Get field's data.
           $field_data = new BP_XProfile_Field($field->id);
           // Get the childs of field
           $childs = $field_data->get_children();
           if (isset($childs) && count($childs) > 0 && is_object($childs[0])) {
               // Get the name of custom post type.
               $custom_post_type = $childs[0]->name;
               // Get the posts of custom post type.
               $loop = new WP_Query(array('posts_per_page' => -1, 'post_type' => $custom_post_type, 'order_by' => 'title', 'order' => ASC ));
       ?>
       <label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) : ?>required<?php endif; ?>" for="<?php bp_the_profile_field_input_name(); ?>"><?php bp_the_profile_field_name(); ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bxcft'); } ?></label>
       <select name="<?php bp_the_profile_field_input_name(); ?>" id="<?php bp_the_profile_field_input_name(); ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true" required="required"<?php endif; ?> class="select">
           <option value=""><?php _e('Select...', 'bxcft'); ?></option>
       <?php foreach ($loop->posts as $post): ?>
           <option value="<?php echo $post->ID; ?>" <?php if ($custom_post_type_selected == $post->ID): ?>selected="selected"<?php endif; ?>><?php echo $post->post_title; ?></option>
       <?php endforeach; ?>
       </select>
       <?php
           }
       }
       elseif ( bp_get_the_profile_field_type() == 'multiselect_custom_post_type' ) {
           $custom_post_type_selected = maybe_unserialize(BP_XProfile_ProfileData::get_value_byid( $field->id ));
           if ( isset( $_POST['field_' . $field->id] ) && $_POST['field_' . $field->id] != $option_value ) {
                if ( !empty( $_POST['field_' . $field->id] ) ) {
                    $custom_post_type_selected = $_POST['field_' . $field->id];
                }
           }
           // Get field's data.
           $field_data = new BP_XProfile_Field($field->id);
           // Get the childs of field
           $childs = $field_data->get_children();
           if (isset($childs) && $childs && count($childs) > 0 && is_object($childs[0])) {
               // Get the name of custom post type.
               $custom_post_type = $childs[0]->name;
               // Get the posts of custom post type.
               $loop = new WP_Query(array('posts_per_page' => -1, 'post_type' => $custom_post_type, 'order_by' => 'title', 'order' => ASC ));
       ?>
       <label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) : ?>required<?php endif; ?>" for="<?php bp_the_profile_field_input_name(); ?>"><?php bp_the_profile_field_name(); ?> <?php if ( bp_get_the_profile_field_is_required() ) { echo __('*', 'bxcft'); } ?></label>
       <select name="<?php bp_the_profile_field_input_name(); ?>[]" id="<?php bp_the_profile_field_input_name(); ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true" required="required"<?php endif; ?> class="select" multiple="multiple">
       <?php foreach ($loop->posts as $post): ?>
           <option value="<?php echo $post->ID; ?>" <?php if (in_array($post->ID, $custom_post_type_selected)): ?>selected="selected"<?php endif; ?>><?php echo $post->post_title; ?></option>
       <?php endforeach; ?>
       </select>
       <?php
           } else {
                _e('There is no custom post type selected.', 'bxcft');
           }
       }
       elseif (bp_get_the_profile_field_type() == 'checkbox_acceptance') {
       ?>
           <label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) : ?>required<?php endif; ?>"
                  for="<?php bp_the_profile_field_input_name(); ?>"><?php bp_the_profile_field_name(); ?></label>
               <input type="checkbox" name="<?php bp_the_profile_field_input_name(); ?>" id="<?php bp_the_profile_field_input_name(); ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true" required="required"<?php endif; ?> class="checkbox" value="1" <?php if (bp_get_the_profile_field_edit_value()): ?>checked="checked"<?php endif; ?> />
               <?php bp_the_profile_field_description(); $field->description = ''; ?>
       <?php
       }
       elseif (bp_get_the_profile_field_type() == 'image') {
       ?>
            <label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) : ?>required<?php endif; ?>"
                   for="<?php bp_the_profile_field_input_name(); ?>"><?php bp_the_profile_field_name(); ?></label>
            <input type="file" name="<?php bp_the_profile_field_input_name(); ?>" id="<?php bp_the_profile_field_input_name(); ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true" required="required"<?php endif; ?> />
            <?php if (bp_get_the_profile_field_edit_value() != ''): ?>
                <img src="<?php echo $uploads['baseurl'] . bp_get_the_profile_field_edit_value(); ?>" alt="<?php bp_the_profile_field_input_name(); ?>" />
                <label for="<?php bp_the_profile_field_input_name(); ?>_deleteimg">
                    <input type="checkbox" name="<?php bp_the_profile_field_input_name(); ?>_deleteimg" id="<?php bp_the_profile_field_input_name(); ?>_deleteimg" value="1" />
                    <?php _e('Check this to delete this image', 'bxcft'); ?>
                </label>
                <input type="hidden" name="<?php bp_the_profile_field_input_name(); ?>_hiddenimg" id="<?php bp_the_profile_field_input_name(); ?>_hiddenimg" value="<?php bp_the_profile_field_edit_value(); ?>" />
            <?php endif; ?>
            <script type="text/javascript">
                jQuery('#profile-edit-form').attr('enctype', 'multipart/form-data');
            </script>
       <?php
       }
       elseif (bp_get_the_profile_field_type() == 'file') {
       ?>
            <label class="label-form <?php if ( bp_get_the_profile_field_is_required() ) : ?>required<?php endif; ?>"
                   for="<?php bp_the_profile_field_input_name(); ?>"><?php bp_the_profile_field_name(); ?></label>
            <input type="file" name="<?php bp_the_profile_field_input_name(); ?>" id="<?php bp_the_profile_field_input_name(); ?>" <?php if ( bp_get_the_profile_field_is_required() ) : ?>aria-required="true" required="required"<?php endif; ?> />
            <?php if (bp_get_the_profile_field_edit_value() != ''): ?>
                <a href="<?php echo $uploads['baseurl'] . bp_get_the_profile_field_edit_value(); ?>" title="<?php bp_the_profile_field_input_name(); ?>"><?php _e('Download file', 'bxcft'); ?></a>
                <label for="<?php bp_the_profile_field_input_name(); ?>_deletefile">
                    <input type="checkbox" name="<?php bp_the_profile_field_input_name(); ?>_deletefile" id="<?php bp_the_profile_field_input_name(); ?>_deletefile" value="1" />
                    <?php _e('Check this to delete this file', 'bxcft'); ?>
                </label>
                <input type="hidden" name="<?php bp_the_profile_field_input_name(); ?>_hiddenfile" id="<?php bp_the_profile_field_input_name(); ?>_hiddenfile" value="<?php bp_the_profile_field_edit_value(); ?>" />
            <?php endif; ?>
            <script type="text/javascript">
                jQuery('#profile-edit-form').attr('enctype', 'multipart/form-data');
            </script>
       <?php
       }

       $output = ob_get_contents();
   ob_end_clean();

   if($echo){
       echo $output;
       return;
   }
   else{
       return $output;
   }

}
/*
 * Buddypress v1.7 has better hook bp_custom_profile_edit_fields_pre_visibility which shows fields before visibility settings.
 * In case this action does not exist we use the other tag bp_custom_profile_edit_fields.
 */
if (has_action('bp_custom_profile_edit_fields_pre_visibility'))
    add_action( 'bp_custom_profile_edit_fields_pre_visibility', 'bxcft_edit_render_new_xprofile_field' );
else 
add_action( 'bp_custom_profile_edit_fields', 'bxcft_edit_render_new_xprofile_field' );

function bxcft_get_field_value( $value='', $type='', $id='') {

    $uploads = wp_upload_dir();
    
    if ($type == 'birthdate') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        $field = new BP_XProfile_Field($id);
        // Get children.
        $childs = $field->get_children();
        $show_age = false;
        if (isset($childs) && $childs && count($childs) > 0 && is_object($childs[0])) {
            // Get the name of custom post type.
            if ($childs[0]->name == 'show_age') 
                $show_age = true;
        }
        if ($show_age) {
            return '<p>'.floor((time() - strtotime($value))/31556926).'</p>';
        }
        return '<p>'.date_i18n(get_option('date_format') ,strtotime($value) ).'</p>';
    }
    elseif ($type == 'datepicker') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        return '<p>'.date_i18n(get_option('date_format') ,strtotime($value) ).'</p>';
    }
    elseif ($type == 'email') {
        if (strpos($value, 'mailto') === false) {
            $value = str_replace("<p>", "", $value);
            $value = str_replace("</p>", "", $value);
            return '<p><a href="mailto:'.$value.'">'.$value.'</a></p>';
        }
    }
    elseif ($type == 'web') {
        if (strpos($value, 'href=') === false) {
            $value = str_replace("<p>", "", $value);
            $value = str_replace("</p>", "", $value);
            return '<p><a href="'.$value.'">'.$value.'</a></p>';      
        }
    }
    elseif ($type == 'select_custom_post_type') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        // Get field's data.
        $field = new BP_XProfile_Field($id);
        // Get children.
        $childs = $field->get_children();
        if (isset($childs) && count($childs) > 0) {
            // Get the name of custom post type.
            $custom_post_type = $childs[0]->name;
        }
        $post = get_post($value);
        if ($post->post_type == $custom_post_type) {
            return '<p>'.$post->post_title.'</p>';
        } else {
            // Custom post type is not the same.
            return '<p>--</p>';
        }
    }
    elseif ($type == 'multiselect_custom_post_type') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        // Get field's data.
        $field = new BP_XProfile_Field($id);
        // Get children.
        $childs = $field->get_children();
        if (isset($childs) && count($childs) > 0 && is_object($childs[0])) {
            // Get the name of custom post type.
            $custom_post_type = $childs[0]->name;
        } else {
            $custom_post_type = '';
        }
        $values = explode(",", $value);
        $cad = '';
        foreach ($values as $v) {
            $post = get_post($v);
            if ($post->post_type == $custom_post_type) {
                if ($cad == '')
                    $cad .= '<ul class="list_custom_post_type">';
                $cad .= '<li>'.$post->post_title.'</li>';
            }
        }
        if ($cad != '') {
            $cad .= '</ul>';
            return '<p>'.$cad.'</p>';
        } else {
            return '<p>--</p>';
        }
    } elseif ($type == 'checkbox_acceptance') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        return '<p>'.($value==1)?__('yes', 'bxcft'):__('no', 'bxcft').'</p>';
    } elseif ($type == 'image') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        return '<p><img src="'.$uploads['baseurl'].$value.'" alt="" /></p>';
    } elseif ($type == 'file') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        return '<p><a href="'.$uploads['baseurl'].$value.'">'.__('Download file', 'bxcft').'</a></p>';
    }
    
    return $value;
}
add_filter( 'bp_get_the_profile_field_value', 'bxcft_get_field_value', 15, 3);

/**
 * Filter for those who use xprofile_get_field_data instead of get_field_value.
 * @param type $value
 * @param type $field_id
 * @param type $user_id
 * @return string
 */
function bxcft_get_field_data($value, $field_id) {
    $uploads = wp_upload_dir();
    $field = new BP_XProfile_Field($field_id);
    if ($field->type == 'birthdate') {
        // Get children.
        $childs = $field->get_children();
        $show_age = false;
        if (isset($childs) && $childs && count($childs) > 0 && is_object($childs[0])) {
            if ($childs[0]->name == 'show_age') 
                $show_age = true;
        }
        if ($show_age) {
            return '<p>'.floor((time() - strtotime($value))/31556926).'</p>';
        }
        return '<p>'.date_i18n(get_option('date_format') ,strtotime($value) ).'</p>';
    }
    elseif ($field->type == 'datepicker') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        return '<p>'.date_i18n(get_option('date_format') ,strtotime($value) ).'</p>';
    }
    elseif ($field->type == 'email') {
        if (strpos($value, 'mailto') === false) {
            $value = str_replace("<p>", "", $value);
            $value = str_replace("</p>", "", $value);
            return '<p><a href="mailto:'.$value.'">'.$value.'</a></p>';
        }
    }
    elseif ($field->type == 'web') {
        if (strpos($value, 'href=') === false) {
            $value = str_replace("<p>", "", $value);
            $value = str_replace("</p>", "", $value);
            return '<p><a href="'.$value.'">'.$value.'</a></p>';      
        }
    }
    elseif ($field->type == 'select_custom_post_type') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        // Get children.
        $childs = $field->get_children();
        if (isset($childs) && count($childs) > 0) {
            // Get the name of custom post type.
            $custom_post_type = $childs[0]->name;
        }
        $post = get_post($value);
        if ($post->post_type == $custom_post_type) {
            return '<p>'.$post->post_title.'</p>';
        } else {
            // Custom post type is not the same.
            return '<p>--</p>';
        }
    }
    elseif ($field->type == 'multiselect_custom_post_type') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        // Get children.
        $childs = $field->get_children();
        $values = explode(",", $value);
        $cad = '';
        if (isset($childs) && is_array($childs) && count($childs) > 0) {
            // Get the name of custom post type.
            $custom_post_type = $childs[0]->name;
            
            foreach ($values as $v) {
                $post = get_post($v);
                if ($post->post_type == $custom_post_type) {
                    if ($cad == '')
                        $cad .= '<ul class="list_custom_post_type">';
                    $cad .= '<li>'.$post->post_title.'</li>';
                }
            }
        }
        if ($cad != '') {
            $cad .= '</ul>';
            return '<p>'.$cad.'</p>';
        } else {
            return '<p>--</p>';
        }
    } elseif ($field->type == 'checkbox_acceptance') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        return '<p>'.($value==1)?__('yes', 'bxcft'):__('no', 'bxcft').'</p>';
    } elseif ($field->type == 'image') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        return '<p><img src="'.$uploads['baseurl'].$value.'" alt="" /></p>';
    } elseif ($field->type == 'file') {
        $value = str_replace("<p>", "", $value);
        $value = str_replace("</p>", "", $value);
        return '<p><a href="'.$uploads['baseurl'].$value.'">'.__('Download file', 'bxcft').'</a></p>';
    }
    
    return $value;
}
add_filter( 'xprofile_get_field_data', 'bxcft_get_field_data', 15, 2);

/**
 * Function replacing the original buddypress filter.
 * @param type $field_value
 * @param type $field_type
 * @return string
 */
function bxcft_xprofile_filter_link_profile_data( $field_value, $field_type = 'textbox' ) {
	if ( 'datebox' == $field_type || 'email' == $field_type || 'birthdate' == $field_type ||
            'datepicker' == $field_type || 'web' == $field_type || 'select_custom_post_type' == $field_type ||
            'multiselect_custom_post_type' == $field_type || 'image' == $field_type || 'file' == $field_type)
		return $field_value;

	if ( !strpos( $field_value, ',' ) && ( count( explode( ' ', $field_value ) ) > 5 ) )
		return $field_value;

	$values = explode( ',', $field_value );

	if ( !empty( $values ) ) {
		foreach ( (array) $values as $value ) {
			$value = trim( $value );

			// If the value is a URL, skip it and just make it clickable.
			if ( preg_match( '@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $value ) ) {
				$new_values[] = make_clickable( $value );

			// Is not clickable
			} else {

				// More than 5 spaces
				if ( count( explode( ' ', $value ) ) > 5 ) {
					$new_values[] = $value;

				// Less than 5 spaces
				} else {
					$search_url   = add_query_arg( array( 's' => urlencode( $value ) ), bp_get_members_directory_permalink() );
					$new_values[] = '<a href="' . $search_url . '" rel="nofollow">' . $value . '</a>';
				}
			}
		}

		$values = implode( ', ', $new_values );
	}

	return $values;
}

function bxcft_add_js($hook) {    
    if ('users_page_bp-profile-setup' != $hook && 'buddypress_page_bp-profile-setup' != $hook)
       return;

    wp_enqueue_script( 'bxcft-js', plugins_url('assets/js/addfields.js', __FILE__), array( 'jquery' ), '1.0' );
    $params = array(
        'birthdate' => __('Birthdate', 'bxcft'),
        'email' => __('Email', 'bxcft'),
        'web' => __('Website', 'bxcft'),
        'datepicker' => __('Datepicker', 'bxcft'),
        'select_custom_post_type' => __('Custom Post Type Selector', 'bxcft'),
        'multiselect_custom_post_type' => __('Custom Post Type Multiselector', 'bxcft'),
        'checkbox_acceptance' => __('Checkbox acceptance', 'bxcft'),
        'image' => __('Image', 'bxcft'),
        'file' => __('File', 'bxcft')
    );
    wp_localize_script('bxcft-js', 'params', $params);
}
add_action( 'admin_enqueue_scripts', 'bxcft_add_js');

function bxcft_save_custom_option($field) {
    global $bp, $wpdb;
    
    if ( 'select_custom_post_type' == $field->type ) {
        $post_option  = !empty( $_POST['select_custom_post_type_option']) ? $_POST['select_custom_post_type_option'] : '';
        if ( '' != $post_option ) {
            if ( !empty( $field->id ) ) {
				$field_id = $field->id;
			} else {
				$field_id = $wpdb->insert_id;
			}
            if ( !$wpdb->query( $wpdb->prepare( "INSERT INTO {$bp->profile->table_name_fields} (group_id, parent_id, type, name, description, is_required, option_order, is_default_option) VALUES (%d, %d, 'custom_post_type_option', %s, '', 0, %d, %d)", $field->group_id, $field_id, $post_option, 1, 1 ) ) ) {
                return false;
            }
        }
    }
    elseif ( 'multiselect_custom_post_type' == $field->type) {
        $post_option  = !empty( $_POST['multiselect_custom_post_type_option']) ? $_POST['multiselect_custom_post_type_option'] : '';
        if ( '' != $post_option ) {
            if ( !empty( $field->id ) ) {
				$field_id = $field->id;
			} else {
				$field_id = $wpdb->insert_id;
			}
            if ( !$wpdb->query( $wpdb->prepare( "INSERT INTO {$bp->profile->table_name_fields} (group_id, parent_id, type, name, description, is_required, option_order, is_default_option) VALUES (%d, %d, 'multi_custom_post_type_option', %s, '', 0, %d, %d)", $field->group_id, $field_id, $post_option, 1, 1 ) ) ) {
                return false;
            }
        }
    }
    elseif ( 'birthdate' == $field->type) {
        $post_option  = !empty( $_POST['birthdate_option']) ? $_POST['birthdate_option'] : '';
        if ( '' != $post_option ) {
            if ( !empty( $field->id ) ) {
				$field_id = $field->id;
			} else {
				$field_id = $wpdb->insert_id;
			}
            if ( !$wpdb->query( $wpdb->prepare( "INSERT INTO {$bp->profile->table_name_fields} (group_id, parent_id, type, name, description, is_required, option_order, is_default_option) VALUES (%d, %d, 'birthdate_option', %s, '', 0, %d, %d)", $field->group_id, $field_id, $post_option, 1, 1 ) ) ) {
                return false;
            }
        }
    }
        
}
add_action( 'xprofile_field_after_save', 'bxcft_save_custom_option');

function bxcft_delete_field_custom($field) {
    if ($field->type == 'select_custom_post_type' || $field->type == 'multiselect_custom_post_type'
             || $field->type == 'birthdate') {
        $field->delete_children();
    }
}
add_action( 'xprofile_fields_deleted_field', 'bxcft_delete_field_custom');

function bxcft_selected_field($field) {
    $childs = $field->get_children();
    if (isset($childs) && count($childs) > 0 && is_object($childs[0]) && $field->type == 'select_custom_post_type') {
        $selected_option = $childs[0]->name;
    } else {
        $selected_option = null;
    }
    
    if (!empty($_POST['select_custom_post_type_option'])) {
        $selected_option = $_POST['select_custom_post_type_option'];
    }
?>
<div style="display: none; margin-left: 15px;" class="options-box" id="select_custom_post_type">
<h4><?php _e('Please, select custom post type', 'bxcft'); ?></h4>
<p>
    <?php _e('Custom Post Type:', 'bxcft'); ?>
    <select name="select_custom_post_type_option" id="select_custom_post_type_option">
<?php
    $args = array(
        'public'   => true,
        '_builtin' => false
    ); 
    $custom_post_types = get_post_types($args);
    foreach ($custom_post_types as $post_type) :
?>
        <option value="<?php echo $post_type; ?>" <?php if ($selected_option == $post_type): ?>selected="selected"<?php endif; ?>>
            <?php echo $post_type; ?>
        </option>
<?php endforeach; ?>
    </select>
</p>
</div>
<?php
    if (isset($childs) && count($childs) > 0 && is_object($childs[0]) && $field->type == 'multiselect_custom_post_type') {
        $selected_option = $childs[0]->name;
    } else {
        $selected_option = null;
    }
    
    if (!empty($_POST['multiselect_custom_post_type_option'])) {
        $selected_option = $_POST['multiselect_custom_post_type_option'];
    }
?>
<div style="display: none; margin-left: 15px;" class="options-box" id="multiselect_custom_post_type">
<h4><?php _e('Please, select custom post type', 'bxcft'); ?></h4>
<p>
    <?php _e('Custom Post Type:', 'bxcft'); ?>
    <select name="multiselect_custom_post_type_option" id="multiselect_custom_post_type_option">
<?php
    foreach ($custom_post_types as $post_type) :
?>
        <option value="<?php echo $post_type; ?>" <?php if ($selected_option == $post_type): ?>selected="selected"<?php endif; ?>>
            <?php echo $post_type; ?>
        </option>
<?php endforeach; ?>
    </select>
</p>
</div>     
<?php
    if (isset($childs) && $childs && count($childs) > 0 && is_object($childs[0]) && $field->type == 'birthdate') {
        $selected_option = $childs[0]->name;
    } else {
        $selected_option = null;
    }
    
    if (!empty($_POST['birthdate_option'])) {
        $selected_option = $_POST['birthdate_option'];
    }
?>
<div style="display: none; margin-left: 15px;" class="options-box" id="birthdate">
<h4><?php _e('Show age (hide birthdate)', 'bxcft'); ?></h4>
<p>
    <?php _e('Check this if you want to show age instead of birthdate:', 'bxcft'); ?>
    <input type="checkbox" name="birthdate_option" value="show_age" id="birthdate_option"
           <?php if ($selected_option == 'show_age') : ?>checked="checked"<?php endif; ?>/>
</p>
</div>
<?php if (!is_null($field->type)) : ?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        bxcft.select('<?php echo $field->type; ?>');
    });    
</script>
<?php
    endif;
}
add_action('xprofile_field_additional_options', 'bxcft_selected_field');

/**
 * This filter add a new level of visibility. Hide this field for everyone. Nobody can see.
 * @param type $visibility_levels
 * @return type
 */
function bxcft_xprofile_get_visibility_levels($visibility_levels) {
    $visibility_levels['nobody'] = array('id' => 'nobody',  'label' => __('Nobody', 'bxcft'));
    return $visibility_levels;
}
add_filter('bp_xprofile_get_visibility_levels', 'bxcft_xprofile_get_visibility_levels', 1);

/**
 * This filter is necessary to hide fields when 'nobody' new visibility is selected.
 * @param type $hidden_fields
 * @param type $displayed_user_id
 * @param type $current_user_id
 * @return type
 */
function bxcft_xprofile_get_hidden_fields_for_user($hidden_fields, $displayed_user_id=0, $current_user_id=0) {
    if ( !$displayed_user_id ) {
		$displayed_user_id = bp_displayed_user_id();
	}

	if ( !$displayed_user_id ) {
		return array();
	}

	if ( !$current_user_id ) {
		$current_user_id = bp_loggedin_user_id();
	}

	// @todo - This is where you'd swap out for current_user_can() checks

	if ( $current_user_id ) {
		// Current user is logged in
		if ( $displayed_user_id == $current_user_id ) {
			// If you're viewing your own profile, nothing's private
			$hidden_fields = array();

		} else if ( bp_is_active( 'friends' ) && friends_check_friendship( $displayed_user_id, $current_user_id ) ) {
			// If the current user and displayed user are friends, so exclude nobody
            $hidden_levels = array( 'nobody' );
			$hidden_fields = bp_xprofile_get_fields_by_visibility_levels( $displayed_user_id, $hidden_levels );

		} else {
			// current user is logged-in but not friends, so exclude friends-only
			$hidden_levels = array( 'friends', 'nobody' );
			$hidden_fields = bp_xprofile_get_fields_by_visibility_levels( $displayed_user_id, $hidden_levels );
		}

	} else {
		// Current user is not logged in, so exclude friends-only and loggedin
		$hidden_levels = array( 'friends', 'loggedin', 'nobody' );
		$hidden_fields = bp_xprofile_get_fields_by_visibility_levels( $displayed_user_id, $hidden_levels );
	}
    
    return $hidden_fields;
}
add_filter('bp_xprofile_get_hidden_fields_for_user', 'bxcft_xprofile_get_hidden_fields_for_user', 3);

/**
 * Create profile edit handler
 */
function bxcft_screen_edit_profile(){
 
    global $bp;

	if ( !bp_is_my_profile() && !bp_current_user_can( 'bp_moderate' ) )
		return false;

	// Make sure a group is set.
	if ( !bp_action_variable( 1 ) )
		bp_core_redirect( trailingslashit( bp_displayed_user_domain() . $bp->profile->slug . '/edit/group/1' ) );

	// Check the field group exists
	if ( !bp_is_action_variable( 'group' ) || !xprofile_get_field_group( bp_action_variable( 1 ) ) ) {
		bp_do_404();
		return;
	}

	// No errors
	$errors = false;

	// Check to see if any new information has been submitted
	if ( isset( $_POST['field_ids'] ) ) {

		// Check the nonce
		check_admin_referer( 'bp_xprofile_edit' );

		// Check we have field ID's
		if ( empty( $_POST['field_ids'] ) )
			bp_core_redirect( trailingslashit( bp_displayed_user_domain() . $bp->profile->slug . '/edit/group/' . bp_action_variable( 1 ) ) );

		// Explode the posted field IDs into an array so we know which
		// fields have been submitted
		$posted_field_ids = explode( ',', $_POST['field_ids'] );
		$is_required      = array();

		// Loop through the posted fields formatting any datebox values
		// then validate the field
		foreach ( (array) $posted_field_ids as $field_id ) {
			if ( !isset( $_POST['field_' . $field_id] ) ) {

				if ( !empty( $_POST['field_' . $field_id . '_day'] ) && !empty( $_POST['field_' . $field_id . '_month'] ) && !empty( $_POST['field_' . $field_id . '_year'] ) ) {
					// Concatenate the values
					$date_value =   $_POST['field_' . $field_id . '_day'] . ' ' . $_POST['field_' . $field_id . '_month'] . ' ' . $_POST['field_' . $field_id . '_year'];

					// Turn the concatenated value into a timestamp
					$_POST['field_' . $field_id] = date( 'Y-m-d H:i:s', strtotime( $date_value ) );
				}

			}

			$is_required[$field_id] = xprofile_check_is_required_field( $field_id );
			if ( $is_required[$field_id] && empty( $_POST['field_' . $field_id] ) ) {
				$errors = true;
			}
		}

		// There are errors
		if ( !empty( $errors ) ) {
			bp_core_add_message( __( 'Please make sure you fill in all required fields in this profile field group before saving.', 'buddypress' ), 'error' );

		// No errors
		} else {

			// Reset the errors var
			$errors = false;
            
			// Now we've checked for required fields, lets save the values.
			foreach ( (array) $posted_field_ids as $field_id ) {

				// Certain types of fields (checkboxes, multiselects) may come through empty. Save them as an empty array so that they don't get overwritten by the default on the next edit.
				if ( empty( $_POST['field_' . $field_id] ) )
					$value = array();
				else
					$value = $_POST['field_' . $field_id];
                
                $post_action_found = false;
                $post_action = '';
                if (isset($_POST['action'])) {
                    $post_action_found = true;
                    $post_action = $_POST['action'];
                }
                
                // Handles delete checkbox.
                $uploads = wp_upload_dir();
                $field = new BP_XProfile_Field($field_id);
                if ($field->type == 'image' && isset($_POST['field_'.$field_id.'_deleteimg']) && $_POST['field_'.$field_id.'_deleteimg']) {
                    if (file_exists($uploads['basedir'] . $_POST['field_'.$field_id.'_hiddenimg'])) {
                        unlink($uploads['basedir'] . $_POST['field_'.$field_id.'_hiddenimg']);
                    } 
                    $value = array();
                }
                if ($field->type == 'file' && isset($_POST['field_'.$field_id.'_deletefile']) && $_POST['field_'.$field_id.'_deletefile']) {
                    if (file_exists($uploads['basedir'] . $_POST['field_'.$field_id.'_hiddenfile'])) {
                        unlink($uploads['basedir'] . $_POST['field_'.$field_id.'_hiddenfile']);
                    } 
                    $value = array();
                }
                
                // Handles image field type saving.
                if (isset($_FILES['field_'.$field_id]) && $_FILES['field_'.$field_id]['size'] > 0) {
                    $field = new BP_XProfile_Field($field_id);
                    if ($field->type == 'image') {
                        $ext_allowed = array('jpg','jpeg','gif','png');
                        apply_filters('images_ext_allowed', $ext_allowed);
                    } elseif ($field->type == 'file') {
                        $ext_allowed = array('doc','docx','pdf');
                        apply_filters('files_ext_allowed', $ext_allowed);
                    }
                        
                    $ext = strtolower(substr($_FILES['field_'.$field_id]['name'], strrpos($_FILES['field_'.$field_id]['name'],'.')+1));
                    if (!in_array($ext, $ext_allowed)) {
                        $errors = true;
                    } else {
                        require_once( ABSPATH . '/wp-admin/includes/file.php' );
                        add_filter( 'upload_dir', 'bxcft_profile_upload_dir', 10, 1 );
                        $_POST['action'] = 'wp_handle_upload';
                        $uploaded_file = wp_handle_upload( $_FILES['field_'.$field_id] );
                        $uploads = wp_upload_dir();
                        $value = str_replace($uploads['baseurl'], '', $uploaded_file['url']);
                    }
                } else {
                    if ($field->type == 'image' && (!isset($_POST['field_'.$field_id.'_deleteimg']) || !$_POST['field_'.$field_id.'_deleteimg']) && isset($_POST['field_'.$field_id.'_hiddenimg']) && $_POST['field_'.$field_id.'_hiddenimg'] != '') {
                        $value = $_POST['field_'.$field_id.'_hiddenimg'];
                    }
                    elseif ($field->type == 'file' && (!isset($_POST['field_'.$field_id.'_deletefile']) || !$_POST['field_'.$field_id.'_deletefile']) && isset($_POST['field_'.$field_id.'_hiddenfile']) && $_POST['field_'.$field_id.'_hiddenfile'] != '') {
                        $value = $_POST['field_'.$field_id.'_hiddenfile'];
                    }
                }
                if ($post_action_found) {
                    $_POST['action'] = $post_action;
                } else{
                    unset($_POST['action']);
                }

				if ( !xprofile_set_field_data( $field_id, bp_displayed_user_id(), $value, $is_required[$field_id] ) )
					$errors = true;
				else
					do_action( 'xprofile_profile_field_data_updated', $field_id, $value );

				// Save the visibility level
				$visibility_level = !empty( $_POST['field_' . $field_id . '_visibility'] ) ? $_POST['field_' . $field_id . '_visibility'] : 'public';
				xprofile_set_field_visibility_level( $field_id, bp_displayed_user_id(), $visibility_level );
			}

			do_action( 'xprofile_updated_profile', bp_displayed_user_id(), $posted_field_ids, $errors );

			// Set the feedback messages
			if ( !empty( $errors ) )
				bp_core_add_message( __( 'There was a problem updating some of your profile information, please try again.', 'buddypress' ), 'error' );
			else
				bp_core_add_message( __( 'Changes saved.', 'buddypress' ) );

			// Redirect back to the edit screen to display the updates and message
			bp_core_redirect( trailingslashit( bp_displayed_user_domain() . $bp->profile->slug . '/edit/group/' . bp_action_variable( 1 ) ) );
		}
	}

	do_action( 'xprofile_screen_edit_profile' );
	bp_core_load_template( apply_filters( 'xprofile_template_edit_profile', 'members/single/home' ) );
 
}

function bxcft_profile_upload_dir( $upload_dir ) {  
    $user_id = bp_displayed_user_id();
    $profile_subdir = '/profiles/' . $user_id;
 
    $upload_dir['path'] = $upload_dir['basedir'] . $profile_subdir;
    $upload_dir['url'] = $upload_dir['baseurl'] . $profile_subdir;
    $upload_dir['subdir'] = $profile_subdir;
 
    return $upload_dir;
}

/**
 * Replacing the buddypress filter link profile is it has the filter.
 * If user deactivated the filter, we don't add another filter.
 */
function bxcft_remove_xprofile_links() {
    if (has_filter('bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data')) {
        remove_filter( 'bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data', 9, 2 );
        add_filter( 'bp_get_the_profile_field_value', 'bxcft_xprofile_filter_link_profile_data', 9, 2);
    }
}
add_action( 'bp_setup_globals', 'bxcft_remove_xprofile_links', 9999 );

/**
 * Override default action hook in order to support images
 */
function bxcft_override_xprofile_screen_edit_profile(){
    $screen_edit_profile_priority = has_filter('bp_screens', 'xprofile_screen_edit_profile');
 
    if($screen_edit_profile_priority !== false){
        //Remove the default profile_edit handler
        remove_action( 'bp_screens', 'xprofile_screen_edit_profile', $screen_edit_profile_priority );
 
        //Install replacement hook
        add_action( 'bp_screens', 'bxcft_screen_edit_profile', $screen_edit_profile_priority );
    }
}
add_action( 'bp_actions', 'bxcft_override_xprofile_screen_edit_profile', 10 );

/**
 * This function only works if plugin bp-profile search is active.
 * @global type $bps_options
 * @param type $type
 * @return string
 */
function bxcft_profile_field_type($type) {
    if (function_exists('is_plugin_active') && is_plugin_active('bp-profile-search/bps-main.php')
            || in_array( 'bp-profile-search/bps-main.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {
        global $bps_options;
        $id = bp_get_the_profile_field_id ();

        if ( ((is_admin() && strpos($_SERVER['REQUEST_URI'], 'page=bp-profile-search'))
                || (isset($bps_options) && $bps_options['agerange'] == $id 
                        && isset($_POST['bp_profile_search']) && $_POST['bp_profile_search']))
                && $type == 'birthdate') {
            return 'datebox';
        }
    }
    
    return $type;
}
add_filter('bp_the_profile_field_type', 'bxcft_profile_field_type', 1);
