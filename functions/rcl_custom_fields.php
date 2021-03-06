<?php

function get_custom_fields_rcl($post_id,$posttype=false,$id_form=false){
    
    if($post_id){
            $post = get_post($post_id);
            $posttype = $post->post_type;
        }
        
    switch($posttype){
        case 'post': 
                if(isset($post)) $id_form = get_post_meta($post->ID,'publicform-id',1);
                if(!$id_form) $id_form = 1;
                $id_field = 'custom_public_fields_'.$id_form; 
        break;
        case 'products': $id_field = 'custom_saleform_fields'; break;
        default: $id_field = 'custom_fields_'.$posttype;
    }

    return get_option($id_field);
}

function get_custom_post_meta_recall($post_id){
        
    $get_fields = get_custom_fields_rcl($post_id);

    if($get_fields){

        foreach((array)$get_fields as $custom_field){				
            $slug = $custom_field['slug'];
            if(($custom_field['type']=='text'||$custom_field['type']=='number'||$custom_field['type']=='date'||$custom_field['type']=='time')&&get_post_meta($post_id,$slug,1))
                    $show_custom_field .= '<p><b>'.$custom_field['title'].':</b> <span>'.get_post_meta($post_id,$slug,true).'</span></p>';
            if($custom_field['type']=='select'&&get_post_meta($post_id,$slug,true)||$custom_field['type']=='radio'&&get_post_meta($post_id,$slug,1))
                    $show_custom_field .= '<p><b>'.$custom_field['title'].':</b> <span>'.get_post_meta($post_id,$slug,true).'</span></p>';
            if($custom_field['type']=='checkbox'){
                    $cheks = get_post_meta($post_id,$slug,true);
                    $chek_field = implode(', ',$cheks);					
                    $show_custom_field .= '<p><b>'.$custom_field['title'].': </b>'.$chek_field.'</p>';
            }					
            if($custom_field['type']=='textarea'&&get_post_meta($post_id,$slug,true))
                    $show_custom_field .= '<p><b>'.$custom_field['title'].':</b></p><p>'.get_post_meta($post_id,$slug,true).'</p>';
        }

        return $show_custom_field;
    }	
}

function get_post_meta_recall($content){
    global $post,$rcl_options;
    if(!isset($rcl_options['pm_rcl'])||!$rcl_options['pm_rcl'])return $content;
    $pm = get_custom_post_meta_recall($post->ID);
    if(!$rcl_options['pm_place']) $content .= $pm;
    else $content = $pm.$content;
    return $content;
}
if(!is_admin()) add_filter('the_content','get_post_meta_recall');

class Rcl_Custom_Fields{
    
    public $value;
    public $slug;
    public $required;
    
    function __construct(){
        
    }
    
    function get_input($field,$value=false){
        
        
        $this->value = $value;
        $this->slug = $field['slug'];        
        $this->required = ($field['requared']==1)? 'required': '';
        
        if(!$field['type']) return false;
        
        if($field['type']=='date') add_datepicker_scripts();
        
        $callback = 'get_type_'.$field['type'];
        
        return $this->$callback($field);
        
    }
    
    function get_type_text($field){
        return '<input type="text" '.$this->required.' name="'.$this->slug.'" id="'.$this->slug.'" maxlength="50" value="'.$this->value.'"/>';
    }
    
    function get_type_date($field){       
        return '<input type="text" '.$this->required.' class="datepicker" name="'.$this->slug.'" id="'.$this->slug.'" value="'.$this->value.'"/>';
    }
    
    function get_type_time($field){
        return '<input type="time" '.$this->required.' name="'.$this->slug.'" id="'.$this->slug.'" maxlength="50" value="'.$this->value.'"/>';
    }
    
    function get_type_number($field){
        return '<input type="number" '.$this->required.' name="'.$this->slug.'" id="'.$this->slug.'" maxlength="50" value="'.$this->value.'"/>';
    }
    
    function get_type_textarea($field){
        return '<textarea name="'.$this->slug.'" '.$this->required.' id="'.$this->slug.'" rows="5" cols="50">'.$this->value.'</textarea>';
    }
    
    function get_type_select($field){
        $fields = explode('#',$field['field_select']);
        $count_field = count($fields);
        $field_select = '';
        for($a=0;$a<$count_field;$a++){
                $field_select .='<option '.selected($this->value,$fields[$a],false).' value="'.$fields[$a].'">'.$fields[$a].'</option>';
        }
        return '<select '.$this->required.' name="'.$this->slug.'" id="'.$this->slug.'">
        '.$field_select.'
        </select>';
    }
    
    function get_type_checkbox($field){
        $chek = explode('#',$field['field_select']);
        $count_field = count($chek);
        $input = '';
        $class = ($this->required) ? 'class="requared-checkbox"':'';
        for($a=0;$a<$count_field;$a++){             
            $sl = '';
            if($this->value){
                foreach($this->value as $meta){
                    if($chek[$a]!=$meta) continue;
                    $sl = 'checked=checked';
                    break;
                }
            }
            $input .='<input '.$this->required.' '.$sl.' type="checkbox" '.$class.' name="'.$this->slug.'[]" value="'.$chek[$a].'"> ';
            $input .= (!isset($field['before']))? '': $field['before'];
            $input .= $chek[$a];
            $input .= (!isset($field['after']))? '<br />': $field['after'];
        }
        return $input;
    }
    
    function get_type_radio($field){
        $radio = explode('#',$field['field_select']);
        $count_field = count($radio);
        $input = '';
        for($a=0;$a<$count_field;$a++){            
            $input .='<input '.$this->required.' '.checked($this->value,$radio[$a],false).' type="radio" '.checked($a,0,false).' name="'.$this->slug.'" value="'.$radio[$a].'"> '.$radio[$a].'<br />';
        }
        return $input;
    }
    
    function get_field_value($field,$value=false){
        $show = '';
        if($field['type']=='text'&&$value)
                $show = ' <span>'.esc_textarea($value).'</span>';
        if($field['type']=='time'&&$value)
                $show = ' <span>'.esc_textarea($value).'</span>';
        if($field['type']=='date'&&$value)
                $show = ' <span>'.esc_textarea($value).'</span>';
        if($field['type']=='number'&&$value)
                $show = ' <span>'.esc_textarea($value).'</span>';
        if($field['type']=='select'&&$value||$field['type']=='radio'&&$value)
                $show = ' <span>'.esc_textarea($value).'</span>';
        if($field['type']=='checkbox'){
                $chek_field = '';
                if(is_array($value)) $chek_field = implode(', ',$value);
                if($chek_field)
                    $show = $chek_field;
        }					
        if($field['type']=='textarea'&&$value)
                $show = '<p>'.nl2br(esc_textarea($value));
        
        if($show) $show = '<p><b>'.$field['title'].':</b> '.$show.'</p>';
        
        return $show;
    }
    
    function register_user_metas($user_id){
        
        $get_fields = get_option( 'custom_profile_field' );
        
        if(!$get_fields) return false;
			
        foreach((array)$get_fields as $custom_field){				
            $slug = $custom_field['slug'];
            if($custom_field['type']=='checkbox'){                   
                $select = explode('#',$custom_field['field_select']);
                $count_field = count($select);
                if(isset($_POST[$slug])){
                    foreach($_POST[$slug] as $val){
                        for($a=0;$a<$count_field;$a++){
                            if($select[$a]==$val){
                                $vals[] = $val;
                            }
                        }
                    }

                    if($vals) update_usermeta($user_id, $slug, $vals);
                }

            }else{
                if($_POST[$slug]) update_usermeta($user_id, $slug, $_POST[$slug]);	
            }
        }
	
    }
    
}

