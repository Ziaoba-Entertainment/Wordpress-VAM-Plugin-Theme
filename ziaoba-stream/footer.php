    <footer class="site-footer" style="background: #000; padding: 60px 0; border-top: 1px solid #222; margin-top: 60px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px;">
                <div>
                    <h3 style="color: var(--primary-color); margin-bottom: 20px;">ZIAOBA TV</h3>
                    <p style="color: #888; font-size: 14px;">Entertainment That Builds You. African stories, lessons that last.</p>
                </div>
                <div>
                    <h4 style="margin-bottom: 20px;">Explore</h4>
                    <ul style="list-style: none; color: #888; font-size: 14px; line-height: 2;">
                        <li><a href="<?php echo home_url('/'); ?>">Home</a></li>
                        <li><a href="<?php echo get_post_type_archive_link('entertainment'); ?>">Entertainment</a></li>
                        <li><a href="<?php echo get_post_type_archive_link('education'); ?>">Education</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="margin-bottom: 20px;">Support</h4>
                    <ul style="list-style: none; color: #888; font-size: 14px; line-height: 2;">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Terms of Use</a></li>
                        <li><a href="#">Privacy</a></li>
                    </ul>
                </div>
            </div>
            <div style="margin-top: 60px; border-top: 1px solid #222; padding-top: 20px; text-align: center; color: #555; font-size: 12px;">
                &copy; <?php echo date('Y'); ?> Ziaoba Entertainment. All rights reserved.
            </div>
        </div>
    </footer>
    <?php wp_footer(); ?>
</body>
</html>
