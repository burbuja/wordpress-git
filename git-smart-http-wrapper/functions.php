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

function gitsmarthttpwrapper() {
  global $user, $wp;

  $root = trailingslashit( get_option( 'gitsmarthttpwrapper_repositories_path' ) ); // Fix it!

  if ( 2 > strlen( $wp->query_vars['gitsmarthttpwrapper'] ) )
    return;

  list( $project, $path ) = explode( '/', $wp->query_vars['gitsmarthttpwrapper'], 2 );

  $user = gitsmarthttpwrapper_login( $path );

  $descriptorspec = array(
    0 => array( 'pipe', 'r' ),
    1 => array( 'pipe', 'w' ),
  );

  $file = file_get_contents( 'php://input' );
  $request_headers = getallheaders();
  $env = array(
    'CONTENT_TYPE' => isset( $request_headers['Content-Type'] ) ? $request_headers['Content-Type'] : '',
    'GIT_PROJECT_ROOT' => trailingslashit( $root ) . $project,
    'GIT_HTTP_EXPORT_ALL' => true,
    'PATH_INFO' => '/' . $path,
    'QUERY_STRING' => isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '',
    'REMOTE_ADDR' => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
    'REMOTE_USER' => ! empty( $user ) ? $user->user_login : 'nobody',
    'REQUEST_METHOD' => isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '',
  );

  $process = proc_open( get_option( 'gitsmarthttpwrapper_backend_path' ), $descriptorspec, $pipes, null, $env ); // Fix it!

  if ( is_resource( $process ) ) {
    fwrite( $pipes[0], $file );
    fclose( $pipes[0] );

    $output = stream_get_contents( $pipes[1] );
    fclose( $pipes[1] );

    $return_value = proc_close( $process );
  }

  if ( ! empty( $output ) ) {
    list( $headers, $body ) = preg_split( '/\R\R/', $output, 2, PREG_SPLIT_NO_EMPTY );
    foreach( preg_split( '/\R/', $headers ) as $header ) {
      header( $header );
    }
    echo $body;
  }
}

function gitsmarthttpwrapper_401() {
  header( 'WWW-Authenticate: Basic realm="Git Server"' );
  header( 'HTTP/1.0 401 Unauthorized' );
  exit;
}

function gitsmarthttpwrapper_login( $path ) {
  global $user;

  if ( ! empty( $user ) )
    wp_die();

  if ( ! empty( get_option( 'gitsmarthttpwrapper_allowed_roles_to_read' ) ) ) {
    $readers = array( 'administrator' ) + get_option( 'gitsmarthttpwrapper_allowed_roles_to_read' );
  } else {
    $readers = array( 'administrator' );
  }

  if ( ! empty( get_option( 'gitsmarthttpwrapper_allowed_roles_to_write' ) ) ) {
    $writers = array( 'administrator' ) + get_option( 'gitsmarthttpwrapper_allowed_roles_to_write' );
  } else {
    $writers = array( 'administrator' );
  }

  if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) ) {
    if ( ! in_array( 'guest', $readers ) || ( 'git-receive-pack' == $path || ( isset( $_GET['service'] ) && $_GET['service'] == 'git-receive-pack' ) ) ) {
      gitsmarthttpwrapper_401();
    }

    return NULL;
  }

  $user = wp_authenticate( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );

  if ( empty( $user->ID ) ) {
    gitsmarthttpwrapper_401();
  }

  if ( 'git-receive-pack' == $path || ( isset( $_GET['service'] ) && $_GET['service'] == 'git-receive-pack' ) ) {
    if ( 0 == count( array_intersect( $writers, $user->roles ) ) ) {
      gitsmarthttpwrapper_401();
    }

    return $user;
  }

  if ( 0 == count( array_intersect( $readers, $user->roles ) ) ) {
    gitsmarthttpwrapper_401();
  }

  return $user;
}

function gitsmarthttpwrapper_posts_request( $sql, WP_Query $wp_query ) {
	if ( $wp_query->is_main_query() ) {
		$wp_query->query_vars['no_found_rows'] = true;
		$wp_query->query_vars['cache_results'] = false;
		return false;
	}
	return $sql;
}

function is_gitsmarthttpwrapper() {
  global $wp;

	if ( ! is_admin() && null != $wp->query_vars && array_key_exists( 'gitsmarthttpwrapper', $wp->query_vars ) ) {
    return true;
  }
	return false;
}

/* Functions for hooks */

function gitsmarthttpwrapper_template_redirect() {
  global $wp;

  if ( is_gitsmarthttpwrapper() ) {
    remove_filter( 'authenticate', array( 'SimpleSAMLAuthenticator', 'authenticate' ), 10, 2);
    gitsmarthttpwrapper();
    exit;
  }
}

function gitsmarthttpwrapper_parse_request() {
	if ( is_gitsmarthttpwrapper() ) {
		add_filter( 'posts_request', 'gitsmarthttpwrapper_posts_request', 10, 2 );
	}
}

function gitsmarthttpwrapper_redirect_canonical() {
  if ( is_gitsmarthttpwrapper() ) {
    return false;
  }
}

add_action( 'template_redirect', 'gitsmarthttpwrapper_template_redirect' );
add_action( 'parse_request', 'gitsmarthttpwrapper_parse_request' );
add_filter( 'redirect_canonical', 'gitsmarthttpwrapper_redirect_canonical' );
