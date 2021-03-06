<?php # -*- coding: utf-8 -*-

namespace Inpsyde\MultilingualPress\SiteDuplication;

use Inpsyde\MultilingualPress\Common\Setting\Site\SiteSettingViewModel;

/**
 * Site duplication "Search Engine Visibility" setting.
 *
 * @package Inpsyde\MultilingualPress\SiteDuplication
 * @since   3.0.0
 */
final class SearchEngineVisibilitySetting implements SiteSettingViewModel {

	/**
	 * @var string
	 */
	private $id = 'mlp-search-engine-visibility';

	/**
	 * Returns the markup for the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @param int $site_id Site ID.
	 *
	 * @return string The markup for the site setting.
	 */
	public function markup( $site_id ) {

		/**
		 * Filters the default search engine visibility value when adding a new site.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $visible Whether or not the new site should be visible by default.
		 */
		$visible = (bool) apply_filters( 'multilingualpress.new_site_search_engine_visibility', false );

		return sprintf(
			'<label for="%2$s"><input type="checkbox" value="0" id="%2$s" name="blog[%3$s]"%4$s>%1$s</label><p class="description">%5$s</p>',
			esc_html__( 'Discourage search engines from indexing this site', 'multilingual-press' ),
			esc_attr( $this->id ),
			esc_attr( SiteDuplicator::NAME_SEARCH_ENGINE_VISIBILITY ),
			checked( $visible, false, false ),
			esc_html__( 'It is up to search engines to honor this request.', 'multilingual-press' )
		);
	}

	/**
	 * Returns the title of the site setting.
	 *
	 * @since 3.0.0
	 *
	 * @return string The markup for the site setting.
	 */
	public function title() {

		return sprintf(
			'<label for="%2$s">%1$s</label>',
			esc_html__( 'Search Engine Visibility', 'multilingual-press' ),
			esc_attr( $this->id )
		);
	}
}
