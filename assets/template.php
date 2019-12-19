<?php
/**
 * Template for dss feed module
 *
 * $this is an instance of the Feed object.
 *
 * Available properties:
 * $this->feed (array) Array containing all download items.
 * $this->preview_image (string) If a preview image should be displayed or a generic icon.
 *
 * @package Hogan
 */

declare( strict_types = 1 );
namespace Dekode\Hogan;

if ( ! defined( 'ABSPATH' ) || ! ( $this instanceof Feed ) ) {
	return; // Exit if accessed directly.
}

// phpcs:disable
$feeds = [];
foreach ( $this->feed_items as $key => $feed_item ) {
	$feeds[] = $feed_item['feed'];
}
// phpcs:enable

$feed_url        = ( isset( $feeds ) ) ? $feeds : '';
$feed_url        = ( 1 === count( $feed_url ) ) ? implode( $feed_url ) : $feed_url;
$number_of_items = ( isset( $this->items ) && is_numeric( $this->items ) ) ? $this->items : 5;
$number_of_words = ( isset( $this->words ) && is_numeric( $this->words ) ) ? $this->words : 20;


require_once ABSPATH . WPINC . '/feed.php';

if ( function_exists( 'fetch_feed' ) ) {
	$feed = fetch_feed( $feed_url );
	if ( ! is_wp_error( $feed ) ) :
		$feed->init();
		$feed->set_output_encoding( 'UTF-8' );
		$feed->handle_content_type();
		$feed->set_cache_duration( HOUR_IN_SECONDS * 6 );
		$limit = $feed->get_item_quantity( $number_of_items );
		$items = $feed->get_items( 0, $limit );
	endif;
} else {
	echo '<!-- dss-hogan-feed: fetch_feed() not found -->';
	return;
}

if ( empty( $items ) || ! is_array( $items ) ) {
	return;
}

$large = ( isset( $this->card ) && true === $this->card );
printf( '<ul class="list-items card-type-%s">', ( false !== $large ) ? 'large' : 'small' );
foreach ( $items as $item ) {
	$feed_description = ( null !== $item->get_feed()->get_description() ) ? str_ireplace( [ 'Departement:', 'Ministry:' ], '', wp_kses( $item->get_feed()->get_description(), [] ) ) : '';
	$item_description = wp_trim_words(
		wp_kses(
			$item->get_description(),
			[
				'br' => [],
			]
		),
		$number_of_words,
		''
	);
	printf(
		'<li class="list-item">
				<div class="column">
					%s
					<h3 class="entry-title"><a href="%2$s">%3$s</a></h3>
					<div class="entry-summary"><p>%4$s %5$s</p></div>
				</div>
			</a>
		</li>',
		( $this->ministry_name && $feed_description ) ? sprintf( '<p>%s</p>', esc_html( $feed_description ) ) : '',
		esc_url( $item->get_permalink() ),
		esc_html( $item->get_title() ),
		esc_html( $item_description ),
		sprintf( ' <a href="%s">%s</a>', esc_url( $item->get_permalink() ), esc_html__( 'Les mer...', 'dss-hogan-feed' ) )
	);
}
echo '</ul>';
