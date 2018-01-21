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

function gitsmarthttpwrapper_admin_init() {
  register_setting( 'gitsmarthttpwrapper', 'gitsmarthttpwrapper_permalink' );
  register_setting( 'gitsmarthttpwrapper', 'gitsmarthttpwrapper_git_path' );
  register_setting( 'gitsmarthttpwrapper', 'gitsmarthttpwrapper_backend_path' );
  register_setting( 'gitsmarthttpwrapper', 'gitsmarthttpwrapper_repositories_path' );
  register_setting( 'gitsmarthttpwrapper', 'gitsmarthttpwrapper_allowed_roles_to_write' );
  register_setting( 'gitsmarthttpwrapper', 'gitsmarthttpwrapper_allowed_roles_to_read' );
  register_setting( 'gitsmarthttpwrapper-add-repository', 'gitsmarthttpwrapper_name' );
  register_setting( 'gitsmarthttpwrapper-add-repository', 'gitsmarthttpwrapper_description' );
}

function gitsmarthttpwrapper_admin_menu() {
  add_options_page(
    __( 'Git Repositories', 'gitsmarthttpwrapper' ),
    __( 'Git Repositories', 'gitsmarthttpwrapper' ),
    'manage_options',
    'gitsmarthttpwrapper',
    'gitsmarthttpwrapper_options'
  );
}

function gitsmarthttpwrapper_options() {
	require_once( GITSMARTHTTPWRAPPER__DIR_PATH . 'admin/options.php' );
}

add_action( 'admin_init', 'gitsmarthttpwrapper_admin_init' );
add_action( 'admin_menu', 'gitsmarthttpwrapper_admin_menu' );
