<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
	<form method="post" action="options.php">
		<?php
			settings_fields( $fields );
			do_settings_sections( $sections );
			submit_button();
		?>
	</form>
</div>