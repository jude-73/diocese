<footer style="background: linear-gradient(135deg, #1a365d, #15315b); color: white; padding: 3rem 0 1.5rem; margin-top: 3rem;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
        <!-- About Section -->
        <div style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1.25rem; color: #d4af37; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-church" style="font-size: 1.1rem;"></i>
                Keta-Akatsi Diocese
            </h3>
            <p style="line-height: 1.6; opacity: 0.9;">The Catholic Diocese of Keta-Akatsi is committed to the spiritual growth and development of its members across all parishes.</p>
            <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                <a href="#" style="color: white; font-size: 1.25rem;"><i class="fab fa-facebook"></i></a>
                <a href="#" style="color: white; font-size: 1.25rem;"><i class="fab fa-twitter"></i></a>
                <a href="#" style="color: white; font-size: 1.25rem;"><i class="fab fa-instagram"></i></a>
                <a href="#" style="color: white; font-size: 1.25rem;"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <!-- Quick Links -->
        <div style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1.25rem; color: #d4af37; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-link" style="font-size: 1.1rem;"></i>
                Quick Links
            </h3>
            <ul style="list-style: none;">
                <li style="margin-bottom: 0.75rem;">
                    <a href="<?php echo BASE_URL; ?>/public/index.php" style="color: white; text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                        Home
                    </a>
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <a href="<?php echo BASE_URL; ?>/public/gallery.php" style="color: white; text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                        Gallery
                    </a>
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <a href="<?php echo BASE_URL; ?>/public/search.php" style="color: white; text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                        Find Member
                    </a>
                </li>
                <li style="margin-bottom: 0.75rem;">
                    <a href="<?php echo BASE_URL; ?>/admin/index.php" style="color: white; text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
                        Admin Login
                    </a>
                </li>
            </ul>
        </div>

        <!-- Contact Info -->
        <div style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1.25rem; color: #d4af37; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-envelope" style="font-size: 1.1rem;"></i>
                Contact Us
            </h3>
            <div style="line-height: 1.8; opacity: 0.9;">
                <p style="display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <i class="fas fa-building" style="margin-top: 0.2rem;"></i>
                    Diocesan Secretariat
                </p>
                <p style="display: flex; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <i class="fas fa-map-marker-alt" style="margin-top: 0.2rem;"></i>
                    P.O. Box 12, Keta, Volta Region, Ghana
                </p>
                <p style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <i class="fas fa-phone-alt"></i>
                    +233 123 456 789
                </p>
                <p style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <i class="fas fa-envelope"></i>
                    info@keta-akatsidiocese.org
                </p>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div style="text-align: center; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
        <p style="opacity: 0.8; font-size: 0.9rem;">
            &copy; <?php echo date('Y'); ?> Keta-Akatsi Catholic Diocese. All rights reserved.
        </p>
    </div>
</footer>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>