<?php
/**
 * DSS Feed module class
 *
 * @package Hogan
 */

declare( strict_types = 1 );

namespace Dekode\Hogan;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( '\\Dekode\\Hogan\\Feed' ) && class_exists( '\\Dekode\\Hogan\\Module' ) ) {

	/**
	 * Simple Posts module class (WYSIWYG).
	 *
	 * @extends Modules base class.
	 */
	class Feed extends Module {

		/**
		 * Preview image
		 *
		 * @var string
		 */
		public $preview_image = 'off';

		/**
		 * List of feed urls.
		 *
		 * @var array $feed_items
		 */
		public $feed_items;
		/**
		 * URL of feed.
		 *
		 * @var $feed
		 */
		public $feed;

		/**
		 * Number of items.
		 *
		 * @var $items
		 */
		public $items;

		/**
		 * Number of words.
		 *
		 * @var $words
		 */
		public $words;
		/**
		 * Display card (true) or list? .
		 *
		 * @var $card
		 */
		public $card;
		/**
		 * Display Ministry name?.
		 *
		 * @var $words
		 */
		public $ministry_name;

		/**
		 * Module constructor.
		 */
		public function __construct() {

			$this->label    = __( 'Feed', 'dss-hogan-feed' );
			$this->template = __DIR__ . '/assets/template.php';

			parent::__construct();
		}

		/**
		 * Enqueue module assets
		 */
		public function enqueue_assets() {
			wp_enqueue_script( 'dss-hogan-feed', plugins_url( '/assets/js/dss-hogan-feed.js', __FILE__ ), [ 'jquery' ], '1.2.0', true );
			wp_enqueue_style( 'dss-hogan-feed', plugins_url( '/assets/css/dss-hogan-feed.css', __FILE__ ), [], '1.2.0' );

		}

		/**
		 * Field definitions for module.
		 *
		 * @return array $fields Fields for this module
		 */
		public function get_fields() : array {

			$fields = [
				[
					'type'         => 'tab',
					'key'          => $this->field_key . '_data_tab',
					'label'        => __( 'Data', 'dss-hogan-highcharts' ),
					'name'         => 'data_tab',
					'instructions' => '',
					'placement'    => 'top',
					'endpoint'     => 0,
				],
				[
					'type'         => 'repeater',
					'key'          => $this->field_key . '_feed_items',
					'label'        => __( 'Feeds', 'dss-hogan-feed' ),
					'name'         => 'feed_items',
					'instructions' => __( 'Add feeds', 'dss-hogan-feed' ),
					'min'          => 1,
					'max'          => 0,
					'layout'       => 'block',
					'button_label' => __( 'Add feed', 'dss-hogan-feed' ),
					'sub_fields'   => [
						[
							'type'          => 'url',
							'key'           => $this->field_key . '_feed',
							'label'         => __( 'Feed URL', 'dss-hogan-feed' ),
							'name'          => 'feed',
							'instructions'  => __( 'Add feed URL', 'dss-hogan-feed' ),
							'allow_null'    => 0,
							'default_value' => '',
							'return_format' => 'value',
						],
					],
				],
				[
					'type'         => 'tab',
					'key'          => $this->field_key . '_settings_tab',
					'label'        => __( 'Settings', 'dss-hogan-highcharts' ),
					'name'         => 'settings_tab',
					'instructions' => '',
					'placement'    => 'top',
					'endpoint'     => 0,
				],
				[
					'type'          => 'number',
					'key'           => $this->field_key . '_items',
					'label'         => __( 'Number of items', 'dss-hogan-feed' ),
					'name'          => 'items',
					'instructions'  => __( 'Max number of items display', 'dss-hogan-feed' ),
					'required'      => 0,
					'default_value' => apply_filters( 'dss/hogan/module/feed/items', 5 ), // phpcs:ignore
				],
				[
					'type'          => 'number',
					'key'           => $this->field_key . '_words',
					'label'         => __( 'Max number of words', 'dss-hogan-feed' ),
					'name'          => 'words',
					'instructions'  => __( 'Max number of words per item', 'dss-hogan-feed' ),
					'required'      => 0,
					'default_value' => apply_filters( 'dss/hogan/module/feed/words', 20 ), // phpcs:ignore

				],
				[
					'type'          => 'true_false',
					'key'           => $this->field_key . '_card',
					'name'          => 'card',
					'ui'            => true,
					'ui_on_text'    => 'Card',
					'ui_off_text'   => 'List',
					'layout'        => 'horizontal',
					'wrapper'       => [
						'width' => '50',
					],
					'label'         => __( 'Layout', 'dss-hogan-feed' ),
					'default_value' => apply_filters( 'dss/hogan/module/feed/layout', true ), // phpcs:ignore
				],
				[
					'type'          => 'true_false',
					'key'           => $this->field_key . '_ministry_name',
					'name'          => 'ministry_name',
					'ui'            => true,
					'layout'        => 'horizontal',
					'wrapper'       => [
						'width' => '50',
					],
					'label'         => __( 'Name of Ministry', 'dss-hogan-feed' ),
					'instructions'  => __( 'From parent feed description', 'dss-hogan-feed' ),
					'default_value' => apply_filters( 'dss/hogan/module/feed/ministry_name', true ), // phpcs:ignore
				],
			];

			return $fields;
		}

		/**
		 * Map raw fields from acf to object variable.
		 *
		 * @param array $raw_content Content values.
		 * @param int   $counter Module location in page layout.
		 *
		 * @return void
		 */
		public function load_args_from_layout_content( array $raw_content, int $counter = 0 ) {

			$this->feed_items    = $raw_content['feed_items'];
			$this->counter       = $counter;
			$this->items         = $raw_content['items'];
			$this->words         = $raw_content['words'];
			$this->card          = $raw_content['card'];
			$this->ministry_name = $raw_content['ministry_name'];

			parent::load_args_from_layout_content( $raw_content, $counter );

		}

		/**
		 * Validate module content before template is loaded.
		 *
		 * @return bool Whether validation of the module is successful / filled with content.
		 */
		public function validate_args() : bool {
			return ! empty( $this->feed_items );
		}


	}
}
