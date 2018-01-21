<?php

/*
 * Git Smart HTTP Wrapper is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Git Smart HTTP Wrapper is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Git Smart HTTP Wrapper. If not, see http://www.gnu.org/licenses/gpl-2.0 .
 */

if ( ! defined( 'WPINC' ) )
 	die;

if ( ! current_user_can( 'manage_options' ) )
	return;

$prefix = $blog_prefix = '';
if ( ! got_url_rewrite() )
	$prefix = '/index.php';

if ( is_multisite() && ! is_subdomain_install() && is_main_site() && 0 === strpos( $permalink_structure, '/blog/' ) ) {
	$blog_prefix = '/blog';
}

if ( isset( $_GET['settings-updated'] ) && ! empty( get_option( 'gitsmarthttpwrapper_name' ) ) && ! is_dir( trailingslashit( get_option( 'gitsmarthttpwrapper_repositories_path' ) ) . get_option( 'gitsmarthttpwrapper_name' ) ) ) {
	mkdir( trailingslashit( get_option( 'gitsmarthttpwrapper_repositories_path' ) ) . get_option( 'gitsmarthttpwrapper_name' ) );
	exec( get_option( 'gitsmarthttpwrapper_git_path' ) . ' init --bare \'' . trailingslashit( get_option( 'gitsmarthttpwrapper_repositories_path' ) ) . get_option( 'gitsmarthttpwrapper_name' ) . '\'');

	if ( is_file( trailingslashit( trailingslashit( get_option( 'gitsmarthttpwrapper_repositories_path' ) ) . get_option( 'gitsmarthttpwrapper_name' ) ) . 'description' ) && ! empty( get_option( 'gitsmarthttpwrapper_description' ) ) ) {
		$gitsmarthttpwrapper_file = trailingslashit( trailingslashit( get_option( 'gitsmarthttpwrapper_repositories_path' ) ) . get_option( 'gitsmarthttpwrapper_name' ) ) . 'description';
		file_put_contents( $gitsmarthttpwrapper_file, get_option( 'gitsmarthttpwrapper_description' ) . "\n", LOCK_EX);
	}

	update_option( 'gitsmarthttpwrapper_name', '' );
	update_option( 'gitsmarthttpwrapper_description', '' );
}

if ( isset( $_GET['settings-updated'] ) && empty( get_option( 'gitsmarthttpwrapper_name' ) ) && empty( get_option( 'gitsmarthttpwrapper_description' ) ) ) {
  flush_rewrite_rules();
}

?>
<div class="wrap">
<h1><?php echo __( 'Git Repositories Options', 'gitsmarthttpwrapper' ); ?></h1>

<?php if ( ! empty( get_option( 'gitsmarthttpwrapper_permalink' ) ) ) : ?>

<h2 class="title"><?php echo __( 'Repositories', 'gitsmarthttpwrapper' ); ?></h2>
<!-- https://wordpress.stackexchange.com/a/76561 -->
<ul>
<?php
$repositories = scandir( get_option( 'gitsmarthttpwrapper_repositories_path' ) );

foreach ( $repositories as $repository ) {
	if ( $repository != '.' && $repository != '..' ) {
		echo '<li><code>' . $repository . '</code> - ';
		if ( file_exists( trailingslashit( get_option( 'gitsmarthttpwrapper_repositories_path' ) ) . $repository . '/description' ) ) {
			include_once( trailingslashit( get_option( 'gitsmarthttpwrapper_repositories_path' ) ) . $repository . '/description' );
		}
		echo '</li>';
	}
}

?>
</ul>

<form method="post" action="options.php">
<?php settings_fields( 'gitsmarthttpwrapper-add-repository' ); ?>
<h2 class="title"><?php echo __( 'Add a New Repository', 'gitsmarthttpwrapper' ); ?></h2>
<table class="form-table">

<tr>
<th scope="row"><label for="name"><?php echo __( 'Name' ); ?></label></th>
<td><input name="gitsmarthttpwrapper_name" type="text" id="name" value="" class="regular-text code" /></td>
</tr>

<th scope="row"><label for="description"><?php echo __( 'Description' ); ?></label></th>
<td><input name="gitsmarthttpwrapper_description" type="text" id="description" value="" class="regular-text" /></td>
</tr>

</table>

<?php submit_button( __( 'Add' ) ); ?>
</form>

<?php endif; ?>

<form method="post" action="options.php">
<?php settings_fields( 'gitsmarthttpwrapper' ); ?>
<h2 class="title"><?php echo __( 'Common Settings' ); ?></h2>
<table class="form-table">

<tr>
<th scope="row"><label for="gitsmarthttpwrapper_permalink"><?php echo str_replace( ':', '', __( 'Permalink:' ) ); ?></label></th>
<td><code><?php echo trailingslashit( get_option( 'home' ) . $blog_prefix . $prefix ); ?></code><input name="gitsmarthttpwrapper_permalink" type="text" id="gitsmarthttpwrapper_permalink" value="<?php echo ( get_option( 'gitsmarthttpwrapper_permalink' ) ? get_option( 'gitsmarthttpwrapper_permalink' ) : 'git' ); ?>" class="regular-text code" /><code>/<?php echo __( 'example-repository', 'gitsmarthttpwrapper' ); ?></code></td>
</tr>

<tr>
<th scope="row"><label for="gitsmarthttpwrapper_git_path"><?php echo __( 'Path to Git', 'gitsmarthttpwrapper' ); ?></label></th>
<td><input name="gitsmarthttpwrapper_git_path" type="text" id="gitsmarthttpwrapper_git_path" value="<?php echo ( get_option( 'gitsmarthttpwrapper_git_path' ) ? get_option( 'gitsmarthttpwrapper_git_path' ) : '/usr/bin/git' ); ?>" class="regular-text" /></td>
</tr>

<tr>
<th scope="row"><label for="gitsmarthttpwrapper_backend_path"><?php echo __( 'Path to Git HTTP Backend', 'gitsmarthttpwrapper' ); ?></label></th>
<td><input name="gitsmarthttpwrapper_backend_path" type="text" id="gitsmarthttpwrapper_backend_path" value="<?php echo ( get_option( 'gitsmarthttpwrapper_backend_path' ) ? get_option( 'gitsmarthttpwrapper_backend_path' ) : '/usr/lib/git-core/git-http-backend' ); ?>" class="regular-text" /></td>
</tr>

<tr>
<th scope="row"><label for="gitsmarthttpwrapper_repositories_path"><?php echo __( 'Path to Repositories', 'gitsmarthttpwrapper' ); ?></label></th>
<td><input name="gitsmarthttpwrapper_repositories_path" type="text" id="gitsmarthttpwrapper_repositories_path" value="<?php echo ( get_option( 'gitsmarthttpwrapper_repositories_path' ) ? get_option( 'gitsmarthttpwrapper_repositories_path' ) : trailingslashit( dirname( dirname( ABSPATH ) ) ) . 'git' ); ?>" class="regular-text" /></td>
</tr>

<tr>
<th scope="row"><?php echo __( 'Roles Allowed to Write', 'gitsmarthttpwrapper' ); ?></th>
<td>
<?php
$editable_roles = array_reverse( get_editable_roles() );

foreach ( $editable_roles as $role => $details ) {
	$name = translate_user_role( $details['name'] );
	echo '<p><label for="w_' . esc_attr( $role ) . '"><input name="gitsmarthttpwrapper_allowed_roles_to_write[]" type="checkbox"' . ( esc_attr( $role ) == 'administrator' ? ' checked="checked" disabled="disabled" ' : ' ' ) . ( in_array( $role, get_option( 'gitsmarthttpwrapper_allowed_roles_to_write' ) ) ? ' checked="checked" ' : '' ) . 'value="' . esc_attr( $role ) . '" id="w_' . esc_attr( $role ) . '">' . $name . '</label></p>';
}
?>
</td>
</tr>

<tr>
<th scope="row"><?php echo __( 'Roles Allowed to Read', 'gitsmarthttpwrapper' ); ?></th>
<td>
<?php
$editable_roles = array( 'guest' => array( 'name' => 'Guest' ) ) + $editable_roles;

foreach ( $editable_roles as $role => $details ) {
	$name = translate_user_role( $details['name'] );

	if ( $name == 'Guest' )
		$name = __( $name, 'gitsmarthttpwrapper' );

	echo '<p><label for="r_' . esc_attr( $role ) . '"><input name="gitsmarthttpwrapper_allowed_roles_to_read[]" type="checkbox"' . ( esc_attr( $role ) == 'administrator' ? ' checked="checked" disabled="disabled" ' : ' ' ) . ( in_array( $role, get_option( 'gitsmarthttpwrapper_allowed_roles_to_read' ) ) ? ' checked="checked" ' : '' ) . 'value="' . esc_attr( $role ) . '" id="r_' . esc_attr( $role ) . '">' . $name . '</label></p>';
}
?>
</td>
</tr>

</table>

<?php submit_button(); ?>
</form>
</div>
