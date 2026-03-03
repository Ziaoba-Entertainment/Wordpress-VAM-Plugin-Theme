<?php
/**
 * footer.php - Site footer.
 * ziaoba-stream/footer.php
 */
?>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-links">
                <a href="#"><?php _e( 'Audio Description', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Help Center', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Gift Cards', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Media Center', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Investor Relations', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Jobs', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Terms of Use', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Privacy', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Legal Notices', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Cookie Preferences', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Corporate Information', 'ziaoba-stream' ); ?></a>
                <a href="#"><?php _e( 'Contact Us', 'ziaoba-stream' ); ?></a>
            </div>
            
            <div style="margin-top: 30px;">
                <button style="background: transparent; border: 1px solid #666; color: #666; padding: 6px 12px; font-size: 13px; cursor: pointer;">Service Code</button>
            </div>

            <div style="margin-top: 20px; font-size: 12px; color: #666;">
                <?php echo get_theme_mod( 'ziaoba_footer_text', '&copy; ' . date('Y') . ' Ziaoba Entertainment. Entertainment That Builds You.' ); ?>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>
