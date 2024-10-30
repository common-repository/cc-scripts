<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( ! empty( $url ) && ! empty( $link ) ) : ?>
	<a href="<?= $url; ?>">
		<?= $link; ?>
	</a>
<?php endif; ?>