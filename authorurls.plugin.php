<?php

class AuthorUrls extends Plugin
{

	/**
	 * Add the category vocabulary and create the admin token
	 *
	 **/
	public function action_plugin_activation($file)
	{
/*		if ( Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__) ) {
		}
*/
	}

	/**
	 *
	 **/
	public function action_plugin_deactivation($file)
	{
	}

	/**
	 *
	 **/
	public function action_init()
	{
	}

	public function action_update_check()
	{
		Update::add( 'AuthorURLs', 'd16fb23f-acf4-413c-a0f8-5ba55c4b3775',$this->info->version );
	}

	/**
	 * Add an author rewrite rule
	 * @param Array $rules Current rewrite rules
	 **/
	public function filter_default_rewrite_rules( $rules ) {
		$rule = array( 	'name' => 'display_entries_by_author', 
				'parse_regex' => '%^author/(?P<author>[^/]*)(?:/page/(?P<page>\d+))?/?$%i',
				'build_str' => 'author/{$author}(/page/{$page})', 
				'handler' => 'UserThemeHandler', 
				'action' => 'display_entries_by_author', 
				'priority' => 5, 
				'description' => 'Return posts matching specified author.', 
		);

		$rules[] = $rule;	
		return $rules;
	}

	/**
	 * function filter_template_where_filters
	 * Limit the Posts::get call to authors 
	 **/
	public function filter_template_where_filters( $filters ) {
		$vars = Controller::get_handler_vars();
		if( isset( $vars['author'] ) ) {
			$filters['user_id']= User::get( $vars['author'] )->id;
		}
		return $filters;
	}


	/**
	 * function filter_theme_act_display_entries_by_author
	 * Helper function: Display the posts for an author. Probably should be more generic eventually.
	 */
	public function filter_theme_act_display_entries_by_author( $handled, $theme ) {
		$paramarray = array();
		$vars = Controller::get_handler_vars();
		$author = User::get( $vars['author'] )->id;

		if ( isset( $author ) ) {
			$paramarray['fallback'][] = 'author.{$author}';
		}

		$paramarray['fallback'][] = 'author';
		$paramarray['fallback'][] = 'multiple';
		$paramarray['fallback'][] = 'home';

		$default_filters = array(
 			'content_type' => Post::type( 'entry' ),
		);

		$paramarray[ 'user_filters' ] = $default_filters;

		$theme->act_display( $paramarray );
		return true;
	}

}

?>
