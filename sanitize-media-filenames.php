<?php
/*
Plugin Name: Sanitize Media Filenames
Plugin URI: 
Description: Sanitize the filenames when you upload new media files. Helps you to avoid problems with special characters and white spaces in filenames.
Author: Rafael Chaves Freitas
Version: 1.0 
Text Domain: 
*/
add_action('init', '_sanitize_media_filenames_init');
register_activation_hook(__FILE__, '_sanitize_media_filenames_activate');
register_deactivation_hook(__FILE__, '_sanitize_media_filenames_deactivate');

function _sanitize_media_filenames_activate(){
    update_option('_sanitize_media_filenames', array(
        'remove_accents' => true,
        'remove_spaces' => true,
        'allowed_special_chars' => '-_.'
    ));
}

function _sanitize_media_filenames_deactivate(){
    delete_option('_sanitize_media_filenames');
}

function _sanitize_media_filenames_init(){
    if($_FILES && is_array($_FILES)){
        foreach($_FILES as $key => $f){
            if(is_array($f['name']))
                foreach($f['name'] as $_key => $_name)
                    $f['name'][$_key] = sanitize_media_filename ($_name);
            else
                $f['name'] = sanitize_media_filename ($f['name']);

            $_FILES[$key] = $f;
        }
    }
}
/**
 * Removes accents, spaces and all special characters of the given file name
 * @param string $name 
 * @return string
 */
function sanitize_media_filename($filename){
    $options = get_option("_sanitize_media_filenames");
    if($options['remove_spaces'])
        $filename = str_replace(' ', '_', $filename);
    
    if($options['remove_accents'])
        $filename = remove_accents($filename);
    $special_chars = preg_quote($options["allowed_special_chars"]);
    
    $filename = preg_replace("/([^[:alnum:]$special_chars\.]+)/", "", $filename);
    return $filename;
}

add_action('admin_menu', '_sanitize_media_filenames_menu');
function _sanitize_media_filenames_menu(){
    add_options_page(__('Sanitize Media Filenames','sanitize-media-filenames'), __('Sanitize Media Filenames','sanitize-media-filenames'), 'manage_options', 'sanitize-media-filenames', '_sanitize_media_filenames_page');
}

function _sanitize_media_filenames_page(){
    $saved = false;
    if(isset($_POST['action']) && $_POST['action'] === "save-sanitize-media-filenames-options"){
        update_option("_sanitize_media_filenames", $_POST["sanitize_media_filenames"]);
        $saved = true;
    }
    $options = get_option("_sanitize_media_filenames");
    ?>
<div class="wrap">
    <h2><?php _e('Sanitize Media Filenames'); ?></h2>
    <?php if($saved): ?>
        <div id="setting-error-settings_updated" class="updated settings-error"> 
        <p><strong><?php echo _e("Saved"); ?></strong></p></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="action" value="save-sanitize-media-filenames-options" />
        <label>
            <input type="checkbox" name="sanitize_media_filenames[remove_accents]" value='1' <?php if($options['remove_accents']) echo 'checked="checked"'; ?> /> 
            <?php _e('Remove accents', 'sanitize-media-filenames'); ?>
        </label>
        <br/>
        <label>
            <input type="checkbox" name="sanitize_media_filenames[remove_spaces]" value='1' <?php if($options['remove_spaces']) echo 'checked="checked"'; ?> /> 
            <?php _e('Remove blank spaces', 'sanitize-media-filenames'); ?>
        </label>
        <br/>
        <label> 
            <?php _e("Allowed special characters"); ?>
            <input type="text" name="sanitize_media_filenames[allowed_special_chars]" value="<?php echo htmlentities($options["allowed_special_chars"]) ;?>"/>
        </label>
        
        <input type='submit' name='submit' value="<?php _e("Save"); ?>" class="button-primary" />
    </form>
</div>
    <?php
}
?>
