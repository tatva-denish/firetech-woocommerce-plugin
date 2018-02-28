<?php
/**
 * Admin View: Page - Reports
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap woocommerce">
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php
			foreach ( $reports as $key => $report_group ) {
				echo '<a href="' . admin_url( 'admin.php?page=wc-custom-reports&tab=' . urlencode( $key ) ) . '" class="nav-tab ';
				if ( $current_tab == $key ) {
					echo 'nav-tab-active';
				}
				echo '">' . esc_html( $report_group['title'] ) . '</a>';
			}

			do_action( 'wc_reports_tabs' );
		?>
	</nav>
	<?php 

	if ( isset( $reports[ $current_tab ]['reports'][ $current_report ] ) ) {

		$report = $reports[ $current_tab ]['reports'][ $current_report ];

		if ( ! isset( $report['hide_title'] ) || true != $report['hide_title'] ) {
			echo '<h1>' . esc_html( $report['title'] ) . '</h1>';
		} else {
			echo '<h1 class="screen-reader-text">' . esc_html( $report['title'] ) . '</h1>';
		}

		if ( $report['description'] ) {
			echo '<p>' . $report['description'] . '</p>';
		}

		if ( $report['callback'] && ( is_callable( $report['callback'] ) ) ) {
			call_user_func( $report['callback'], $current_report );
		}
	}
	?>
</div>
