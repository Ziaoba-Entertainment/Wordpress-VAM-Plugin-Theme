    <footer style="background: #000; padding: 60px 0; border-top: 1px solid #222; margin-top: 80px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px;">
                <div>
                    <div class="logo" style="margin-bottom: 20px;">ZIAOBA</div>
                    <p style="color: #808080; font-size: 14px;">The home of African Entertainment and Education. Stream your favorite dramas and learn new skills today.</p>
                </div>
                <div>
                    <h4 style="margin-bottom: 20px;">Explore</h4>
                    <ul style="list-style: none; padding: 0; color: #808080; font-size: 14px; line-height: 2;">
                        <li><a href="<?php echo home_url(); ?>">Home</a></li>
                        <li><a href="<?php echo get_post_type_archive_link('entertainment'); ?>">Entertainment</a></li>
                        <li><a href="<?php echo get_post_type_archive_link('education'); ?>">Education</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="margin-bottom: 20px;">Support</h4>
                    <ul style="list-style: none; padding: 0; color: #808080; font-size: 14px; line-height: 2;">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Terms of Use</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div style="margin-top: 60px; padding-top: 30px; border-top: 1px solid #111; text-align: center; color: #666; font-size: 12px;">
                <p>&copy; <?php echo date('Y'); ?> Ziaoba Entertainment. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            window.addEventListener('scroll', function() {
                const header = document.getElementById('mainHeader');
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        });
    </script>
</body>
</html>
