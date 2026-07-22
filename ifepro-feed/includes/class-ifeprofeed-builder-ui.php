<?php
/**
 * Import Facebook Events Live Feed Builder UI.
 *
 * @package Import_Facebook_Events\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IFEPRO_Feed_Builder_UI {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_action( 'edit_form_after_title', array( $this, 'render_builder_shell' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_builder_assets' ) );
		add_action( 'admin_body_class', array( $this, 'add_body_class' ) );
	}

	public function add_body_class( $classes ) {
		$screen = get_current_screen();
		if ( $screen && IFEPRO_FEED_CPT === $screen->post_type && in_array( $screen->base, array( 'post', 'post-new' ), true ) ) {
			$classes .= ' ifepro-builder-active';
		}
		return $classes;
	}

	public function enqueue_builder_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || IFEPRO_FEED_CPT !== $screen->post_type ) return;
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) return;

		wp_enqueue_script( 'masonry' );
		wp_enqueue_script( 'imagesloaded' );

		wp_enqueue_style(
			'ifeprofeed-builder-ui',
			IFEPRO_FEED_URL . 'assets/builder-ui.css',
			array( 'dashicons' ),
			IFEPRO_FEED_VERSION
		);
		wp_enqueue_script(
			'ifeprofeed-builder-ui',
			IFEPRO_FEED_URL . 'assets/builder-ui.js',
			array( 'jquery' ),
			IFEPRO_FEED_VERSION,
			true
		);
		wp_localize_script( 'ifeprofeed-builder-ui', 'ifeproBuilderUI', array(
			'backUrl'   => admin_url( 'edit.php?post_type=' . IFEPRO_FEED_CPT ),
			'isNewPost' => ( 'post-new.php' === $hook ),
			'i18n'      => array(
				'back'            => __( 'Back to Facebook Widgets', 'import-facebook-events' ),
				'save'            => __( 'Save Widget', 'import-facebook-events' ),
				'saving'          => __( 'Saving…', 'import-facebook-events' ),
				'next'            => __( 'Continue', 'import-facebook-events' ),
				'prev'            => __( 'Previous', 'import-facebook-events' ),
				/* translators: 1: step number, 2: total steps */
				'step_of'         => __( 'Step %1$s of %2$s', 'import-facebook-events' ),
				'titlePlh'        => __( 'Enter widget name…', 'import-facebook-events' ),
				'shortcode_label' => __( 'Your Shortcode', 'import-facebook-events' ),
				'copied'          => __( 'Copied!', 'import-facebook-events' ),
				'reqTitle'        => __( 'Widget name is required.', 'import-facebook-events' ),
				'reqPageId'       => __( 'Facebook Page ID or Slug is required.', 'import-facebook-events' ),
				'reqGroupId'      => __( 'Facebook Group URL or ID is required.', 'import-facebook-events' ),
				'reqEventIds'     => __( 'At least one Event ID is required.', 'import-facebook-events' ),
				'reqIcalUrl'      => __( 'iCal URL is required.', 'import-facebook-events' ),
			),
		) );
	}

	public function render_builder_shell( $post ) {
		if ( IFEPRO_FEED_CPT !== $post->post_type ) return;

		$steps = array(
			array( 'id' => 'source',   'label' => __( 'Source', 'import-facebook-events' ),   'icon' => 'dashicons-admin-site',    'desc' => __( 'Choose where to pull Facebook events from', 'import-facebook-events' ) ),
			array( 'id' => 'display',  'label' => __( 'Display', 'import-facebook-events' ),  'icon' => 'dashicons-layout',        'desc' => __( 'Customize layout and visible fields', 'import-facebook-events' ) ),
			array( 'id' => 'tickets',  'label' => __( 'Buttons', 'import-facebook-events' ),  'icon' => 'dashicons-tickets-alt',   'desc' => __( 'Configure event links and button labels', 'import-facebook-events' ) ),
			array( 'id' => 'filters',  'label' => __( 'Filters', 'import-facebook-events' ),  'icon' => 'dashicons-filter',        'desc' => __( 'Narrow down which events to show', 'import-facebook-events' ) ),
			array( 'id' => 'settings', 'label' => __( 'Settings', 'import-facebook-events' ), 'icon' => 'dashicons-admin-generic', 'desc' => __( 'Cache settings and custom CSS', 'import-facebook-events' ) ),
		);

		$total_steps = count( $steps );
		$shortcode   = ( $post->ID && 'auto-draft' !== $post->post_status )
			? '[ifepro_live_feed id="' . $post->ID . '"]'
			: '';
		?>

		<div id="ifepro-builder" class="ifepro-builder">

			<!-- Global Warning Notice for Restricted Pages -->
			<div id="ifepro-builder-global-warning" style="display: none; background: #fff8e5; border-left: 4px solid #f0b849; padding: 12px 15px; margin-bottom: 15px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<p style="margin: 0; font-size: 13px; color: #3c434a;">
					<strong><?php esc_html_e( 'Warning:', 'import-facebook-events' ); ?></strong>
					<span class="ifepro-warning-text"></span>
				</p>
			</div>

			<!-- Top Bar -->
			<div class="ifepro-builder__topbar">
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . IFEPRO_FEED_CPT ) ); ?>" class="ifepro-builder__back">
					<span class="dashicons dashicons-arrow-left-alt2"></span>
					<?php esc_html_e( 'Back to Facebook Widgets', 'import-facebook-events' ); ?>
				</a>

				<?php if ( $shortcode ) : ?>
				<div class="ifepro-builder__shortcode-bar">
					<span class="ifepro-builder__shortcode-label">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
						<?php esc_html_e( 'Shortcode:', 'import-facebook-events' ); ?>
					</span>
					<code class="ifepro-builder__shortcode-code" id="ifepro-builder-shortcode"><?php echo esc_html( $shortcode ); ?></code>
					<button type="button" class="ifepro-builder__shortcode-copy" id="ifepro-builder-copy-sc" title="<?php esc_attr_e( 'Copy shortcode', 'import-facebook-events' ); ?>">
						<span class="ifepro-copy-icon-wrap" style="display:flex;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></span>
					</button>
				</div>
				<?php endif; ?>
			</div>

			<!-- Progress Stepper -->
			<div class="ifepro-builder__stepper">
				<?php foreach ( $steps as $i => $step ) : ?>
				<div class="ifepro-builder__step-indicator <?php echo 0 === $i ? 'is-active' : ''; ?>" data-step="<?php echo esc_attr( $i ); ?>">
					<div class="ifepro-builder__step-circle">
						<span class="ifepro-builder__step-number"><?php echo esc_html( $i + 1 ); ?></span>
						<span class="ifepro-builder__step-check dashicons dashicons-yes"></span>
					</div>
					<span class="ifepro-builder__step-label"><?php echo esc_html( $step['label'] ); ?></span>
					<?php if ( $i < $total_steps - 1 ) : ?>
					<div class="ifepro-builder__step-line"></div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>

			<!-- Workspace -->
			<div class="ifepro-builder__workspace">
				<div class="ifepro-builder__panels">
					<?php foreach ( $steps as $i => $step ) : ?>
					<div class="ifepro-builder__panel <?php echo 0 === $i ? 'is-active' : ''; ?>"
					     id="ifepro-panel-<?php echo esc_attr( $step['id'] ); ?>"
					     data-step="<?php echo esc_attr( $i ); ?>">
						<div class="ifepro-builder__panel-header">
							<span class="dashicons <?php echo esc_attr( $step['icon'] ); ?> ifepro-builder__panel-icon"></span>
							<h2 class="ifepro-builder__panel-title"><?php echo esc_html( $step['label'] ); ?></h2>
							<span class="ifepro-builder__panel-hint" title="<?php echo esc_attr( $step['desc'] ); ?>">
								<span class="dashicons dashicons-editor-help"></span>
							</span>
						</div>
						<?php if ( 0 === $i ) : ?>
						<div class="ifepro-builder__title-slot" id="ifepro-builder-title-slot"></div>
						<?php endif; ?>
						<div class="ifepro-builder__panel-body" id="ifepro-panel-body-<?php echo esc_attr( $step['id'] ); ?>">
							<!-- JS moves metabox fields here -->
						</div>
					</div>
					<?php endforeach; ?>
				</div>

				<!-- Live Preview Sidebar -->
				<div class="ifepro-builder__preview-sidebar">
					<div class="ifepro-builder__preview-card">
						<div class="ifepro-builder__preview-card-header">
							<div class="ifepro-builder__preview-title-wrap" style="display:flex;align-items:center;gap:10px;">
								<h3><?php esc_html_e( 'Live Preview', 'import-facebook-events' ); ?></h3>
								<span class="ifeprofeed-preview-loading" style="display:none;"><?php esc_html_e( 'Updating...', 'import-facebook-events' ); ?></span>
							</div>
							<button type="button" class="ifepro-builder__full-preview-btn" id="ifepro-builder-toggle-full-preview" style="display:inline-flex;align-items:center;gap:6px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;color:#334155;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,0.02);transition:all 0.2s ease;">
								<span class="ifepro-preview-icon-wrap" style="display:flex;align-items:center;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg></span>
								<span class="btn-text"><?php esc_html_e( 'Full Preview', 'import-facebook-events' ); ?></span>
							</button>
						</div>
						<div class="ifepro-builder__preview-card-body">
							<div id="ifepro-builder-preview-container">
								<div class="ifepro-builder__preview-placeholder">
									<p><?php esc_html_e( 'Interactive preview shows up here', 'import-facebook-events' ); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Footer Nav -->
			<div class="ifepro-builder__footer">
				<div class="ifepro-builder__footer-inner">
					<button type="button" class="ifepro-builder__btn ifepro-builder__btn--prev" id="ifepro-builder-prev" style="visibility:hidden;">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
						<?php esc_html_e( 'Previous', 'import-facebook-events' ); ?>
					</button>
					<div class="ifepro-builder__step-counter" id="ifepro-builder-counter">
						<?php
						$step_text = sprintf(
							/* translators: 1: step number, 2: total steps */
							__( 'Step %1$s of %2$s', 'import-facebook-events' ),
							'<strong>1</strong>',
							'<strong>' . esc_html( $total_steps ) . '</strong>'
						);
						echo wp_kses_post( $step_text );
						?>
					</div>
					<div class="ifepro-builder__footer-actions">
						<button type="button" class="ifepro-builder__btn ifepro-builder__btn--next" id="ifepro-builder-next">
							<?php esc_html_e( 'Continue', 'import-facebook-events' ); ?>
							<span class="dashicons dashicons-arrow-right-alt2"></span>
						</button>
						<button type="button" class="ifepro-builder__btn ifepro-builder__btn--save" id="ifepro-builder-save">
							<span class="dashicons dashicons-saved"></span>
							<?php esc_html_e( 'Save Widget', 'import-facebook-events' ); ?>
						</button>
					</div>
				</div>
			</div>

		</div><!-- #ifepro-builder -->
		<?php
	}
}
