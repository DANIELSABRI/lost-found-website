<footer class="site-footer">
    <div class="container" style="padding-top: 0; padding-bottom: 0;">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-col">
                <div class="nav-brand" style="margin-bottom: 20px;">Lost & Found</div>
                <p style="font-size: 14px; color: rgba(255,255,255,0.6); line-height: 1.8;">
                    The ultimate campus intelligence network for item recovery. We connect honesty with efficiency to keep our community whole.
                </p>
            </div>

            <!-- Links Column -->
            <div class="footer-col">
                <h4>Platform</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a></li>
                    <li><a href="<?= BASE_URL ?>/user/report-lost.php">Report Lost</a></li>
                    <li><a href="<?= BASE_URL ?>/user/report-found.php">Report Found</a></li>
                    <li><a href="<?= BASE_URL ?>/user/profile.php">My Profile</a></li>
                </ul>
            </div>

            <!-- Contact Column -->
            <div class="footer-col">
                <h4>Connect</h4>
                <ul class="footer-links">
                    <li><a href="mailto:support@lostfound.edu">Email Intelligence</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">About the Project</a></li>
                    <li><a href="#">Privacy Protocol</a></li>
                </ul>
            </div>

            <!-- Newsletter Column -->
            <div class="footer-col">
                <h4>Recovery Updates</h4>
                <p style="font-size: 13px; color: rgba(255,255,255,0.5); margin-bottom: 15px;">Get notified about successfully matched high-value items.</p>
                <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Subscribed to campus alerts!')">
                    <input type="email" class="newsletter-input" placeholder="Your faculty email" required>
                    <button type="submit" class="newsletter-btn">Join</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p>
                &copy; <?= date('Y') ?> University Lost & Found Network. All rights reserved. 
                <span style="margin: 0 10px; opacity: 0.3;">|</span>
                Built with ❤️ by <span class="built-by">Daniel Sabri</span>, <span class="built-by">Segui Josue</span> and <span class="built-by">Prince Saye</span>
            </p>
        </div>
    </div>
</footer>
