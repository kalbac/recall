<?php

add_filter('regform_fields_rcl','get_captcha_field_rcl',999);
function get_captcha_field_rcl($fields){

    $rcl_captcha = new ReallySimpleCaptcha();

    $rcl_captcha->chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $rcl_captcha->char_length = '4';
    $rcl_captcha->img_size = array( '72', '24' );
    $rcl_captcha->fg = array( '0', '0', '0' );
    $rcl_captcha->bg = array( '255', '255', '255' );
    $rcl_captcha->font_size = '16';
    $rcl_captcha->font_char_width = '15';
    $rcl_captcha->img_type = 'png';
    $rcl_captcha->base = array( '6', '18' );

    $rcl_captcha_word = $rcl_captcha->generate_random_word();
    $rcl_captcha_prefix = mt_rand();
    $rcl_captcha_image_name = $rcl_captcha->generate_image($rcl_captcha_prefix, $rcl_captcha_word);
    $rcl_captcha_image_url =  get_bloginfo('wpurl') . '/wp-content/plugins/really-simple-captcha/tmp/';
    $rcl_captcha_image_src = $rcl_captcha_image_url . $rcl_captcha_image_name;
    $rcl_captcha_image_width = $rcl_captcha->img_size[0];
    $rcl_captcha_image_height = $rcl_captcha->img_size[1];
    $rcl_captcha_field_size = $rcl_captcha->char_length;

    $fields .= '
      <div class="form-block-rcl">
        <label>'.__('Введите символы','rcl').' <span class="required">*</span></label>
        <img src="'.$rcl_captcha_image_src.'" alt="captcha" width="'.$rcl_captcha_image_width.'" height="'.$rcl_captcha_image_height.'" />
        <input id="rcl_captcha_code" required name="rcl_captcha_code" style="width: 160px;" size="'.$rcl_captcha_field_size.'" type="text" />
        <input id="rcl_captcha_prefix" name="rcl_captcha_prefix" type="hidden" value="'.$rcl_captcha_prefix.'" />
     </div>';

    return $fields;

}

add_action('pre_register_user_rcl','check_register_captcha_rcl');
function check_register_captcha_rcl($ref) {
    $rcl_captcha = new ReallySimpleCaptcha();
    $rcl_captcha_prefix = $_POST['rcl_captcha_prefix'];
    $rcl_captcha_code = $_POST['rcl_captcha_code'];
    $rcl_captcha_correct = false;
    $rcl_captcha_check = $rcl_captcha->check( $rcl_captcha_prefix, $rcl_captcha_code );
    $rcl_captcha_correct = $rcl_captcha_check;
    $rcl_captcha->remove($rcl_captcha_prefix);
    $rcl_captcha->cleanup();
    if ( ! $rcl_captcha_correct ) {
        wp_redirect(get_redirect_url_rcl($ref).'action-rcl=register&error=captcha');exit;
        exit;
    }

}