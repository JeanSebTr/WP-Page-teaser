<?php
/*
Plugin Name: Teaser page
Plugin URI: https://github.com/JeanSebTr/WP-Page-teaser
Description: Affiche une page teaser selon la date
Version: 1.0
Author: JeanSebTr
Author URI: http://blog.jeansebtr.com/
License: CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
*/


add_action('init', 'wp_teaser_init');

function wp_teaser_init()
{
	/*/
	add_option('wp_teaser_active', 0);
	add_option('wp_teaser_date', '');
	add_option('wp_teaser_page', '');
	//*/
	
	if(is_admin())
	{
		add_action('admin_menu', 'wp_teaser_menu');
		add_action('admin_init', 'wp_teaser_options_register');
	}
	else
	{
		add_filter('request', 'wp_teaser_request');
	}
}


function wp_teaser_menu()
{
	add_options_page('Options de la page teaser', 'Page teaser', 'manage_options',
		'teaser-menu', 'wp_teaser_options');
}

function wp_teaser_options_register()
{
	register_setting( 'wp-teaser-group', 'wp_teaser_active' );
	register_setting( 'wp-teaser-group', 'wp_teaser_date' );
	register_setting( 'wp-teaser-group', 'wp_teaser_page' );
}

function wp_teaser_options()
{
?>
<div class="wrap">
<h2>Options de la page teaser</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'wp-teaser-group' ); ?>
    <?php do_settings_sections( 'wp-teaser-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Activer le teaser</th>
        <td><input type="checkbox" name="wp_teaser_active" value="1" <?= (intval(get_option('wp_teaser_active')) > 0)?'checked':'' ?> /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Date cible</th>
        <td><input type="datetime-local" name="wp_teaser_date" value="<?= get_option('wp_teaser_date'); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">Page à utiliser</th>
        <td><select name="wp_teaser_page">
			<? $id = get_option('wp_teaser_page');
			   foreach(get_pages() as $page): ?>
				<option <?= ($id == $page->ID)?'selected':'' ?>
					value="<?= $page->ID ?>"><?= $page->post_title ?></option>
			<? endforeach; ?>
			</select>
        </td>
        </tr>
    </table>

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php
}

function wp_teaser_request($q)
{
	global $oDateCible;
	if(is_admin()) return $q;
	
	if(is_user_logged_in()) return $q;

	
	if(intval(get_option('wp_teaser_active')) == 0) return $q;
	
	$zLoc = new DateTimeZone('America/Montreal');
	$zUTC = new DateTimeZone('UTC');

	$oDateCible = new DateTime(get_option('wp_teaser_date'), $zLoc);
	$oDateCible->setTimezone($zUTC);
	
	$oDateNow = new DateTime(null, $zUTC);

	if($oDateNow <= $oDateCible)
	{
		$q = array(
			'page_id' => get_option('wp_teaser_page'),
			'post_type' => 'page');
	}
	
	return $q;
}

function newDateCode()
{
	global $oDateCible;
	return 'new Date(Date.UTC('.
				$oDateCible->format('Y').', '.
				intval($oDateCible->format('n'))-1.', '.
				$oDateCible->format('j').', '.
				$oDateCible->format('G').', '.
				intval($oDateCible->format('i')).', '.
				intval($oDateCible->format('s'))+3.'))';
}
