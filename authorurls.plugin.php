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
	public function action_form_user( $form, $user )
	{
		$desc = $form->user_info->append('textarea', 'description', $user, 'About Yourself');
		$desc->raw = true;
	}

	public function action_update_check()
	{
		Update::add( 'AuthorURLs', 'd16fb23f-acf4-413c-a0f8-5ba55c4b3775',$this->info->version );
	}

	/**
	 * Generate the permalink for this user. create a slug if none exists.
	 */
	public function filter_user_permalink( $out, $user )
	{
		if ( !$user->info->slug ) {
			$slug = Utils::slugify($user->displayname);
			$user->info->slug = $slug;
			$user->info->commit();
		}
		return URL::get( 'display_entries_by_author', array('author' => $user->info->slug) );
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
	public function filter_template_where_filters( $filters )
	{
		$vars = Controller::get_handler_vars();
		if( isset( $vars['author'] ) ) {
			$user = Users::get_by_info('slug', $vars['author']);
			if ( count($user) > 0 ) {
				$filters['user_id'] = $user[0]->id;
			}
			elseif ( $user = User::get($vars['author']) ) {
				$filters['user_id'] = $user->id;
			}
			else {
				$filters['user_id'] = 'none';
			}
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
		$user = Users::get_by_info('slug', $vars['author']);
		if ( count($user) > 0 ) {
			$author = $user[0];
		}
		elseif ( $user = User::get($vars['author']) ) {
			$author = $user;
		}
		else {
			$theme->request->{URL::get_matched_rule()->name} = false;
			$theme->request->{URL::set_404()->name} = true;
			$theme->matched_rule = URL::get_matched_rule();
			$theme->act_display_404();
			return;
		}


		if ( isset( $author ) ) {
			$paramarray['fallback'][] = 'author.{$author->id}';
		}

		$paramarray['fallback'][] = 'author';
		$paramarray['fallback'][] = 'multiple';
		$paramarray['fallback'][] = 'home';

		$default_filters = array(
 			'content_type' => Post::type( 'entry' ),
		);

		$paramarray[ 'user_filters' ] = $default_filters;
		$theme->assign( 'author', $author );
		$theme->act_display( $paramarray );
		return true;
	}

}

?>
