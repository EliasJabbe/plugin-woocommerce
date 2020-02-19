<?php
/**
 * Admin View: Footer
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="lgw-container lgw-footer clear">
    <div class="lgw-content-section text-center">
        <div id="lgw-footer">
            <p>
                <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_legals' ); ?>"
                   class="sub-link" title="Legal"><?php echo $locale->t( 'footer.legals' ); ?></a>
                | <?php echo $locale->t( 'footer.plugin_lengow' ) ?> - v.<?php echo LENGOW_VERSION; ?>
				<?php if ( 'lengow.net' === Lengow_Connector::LENGOW_URL ) : ?>
                    <span class="lgw-label-preprod">preprod</span>
				<?php endif; ?>
                | copyright © <?php echo date( 'Y' ); ?> <a href="http://www.lengow.com" target="_blank"
                                                            class="sub-link" title="Lengow.com">Lengow</a>
            </p>
        </div>
    </div>
</div>