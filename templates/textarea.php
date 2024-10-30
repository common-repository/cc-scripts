<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<label>
	<?php if ( ! empty( $before ) ) : ?><?= $before; ?><?php endif; ?>
	<textarea <?php if ( ! empty( $attrs ) ) : ?><?= $attrs; ?><?php endif; ?>><?php if ( ! empty( $value ) ) : ?><?= $value; ?><?php endif; ?></textarea>
	<?php if ( ! empty( $after ) ) : ?><?= $after; ?><?php endif; ?>
</label>
<?php if ( ! empty( $desc ) ) : ?>
	<p class="description">
		<?= $desc; ?>
	</p>
<?php endif; ?>