<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<label>
	<?php if ( ! empty( $before ) ) : ?><?= $before; ?><?php endif; ?>
	<input <?php if ( ! empty( $attrs ) ) : ?><?= $attrs; ?><?php endif; ?><?php if ( ! empty( $checked ) ) : ?> <?= $checked; ?><?php endif; ?>/>
	<?php if ( ! empty( $after ) ) : ?><?= $after; ?><?php endif; ?>
</label>
<?php if ( ! empty( $desc ) ) : ?>
	<p class="description">
		<?= $desc; ?>
	</p>
<?php endif; ?>