<?php
require_once '../includes/config.php';

$breadcrumb = 'Gallery';
?>
<?php include '../includes/header.php'; ?>

<div class="gallery-container">
    <div class="gallery-header animate__animated animate__fadeIn">
        <h2 class="gallery-title">Diocesan Gallery</h2>
        
        <form action="" method="GET" class="gallery-filter">
            <select name="parish" onchange="this.form.submit()" class="parish-select">
                <option value="">All Parishes</option>
                <?php
                $parishes = $conn->query("SELECT * FROM parishes ORDER BY name");
                $selected_parish = isset($_GET['parish']) ? $_GET['parish'] : '';
                
                while ($parish = $parishes->fetch_assoc()) {
                    $selected = ($selected_parish == $parish['id']) ? 'selected' : '';
                    echo '<option value="' . $parish['id'] . '" ' . $selected . '>' . htmlspecialchars($parish['name']) . '</option>';
                }
                ?>
            </select>
            <div class="select-arrow">
                <i class="fas fa-chevron-down"></i>
            </div>
        </form>
    </div>
    
    <div class="gallery-grid">
        <?php
        $query = "SELECT g.*, p.name as parish_name FROM gallery g LEFT JOIN parishes p ON g.parish_id = p.id";
        $params = [];
        $types = "";
        
        if (!empty($selected_parish)) {
            $query .= " WHERE g.parish_id = ?";
            $params[] = $selected_parish;
            $types .= "i";
        }
        
        $query .= " ORDER BY g.uploaded_at DESC";
        
        $stmt = $conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="gallery-card animate__animated animate__fadeInUp">';
                echo '<div class="gallery-image-container">';
                echo '<img src="' . BASE_URL . '/uploads/' . htmlspecialchars($row['image_path']) . '" alt="Gallery Image" class="gallery-image">';
                echo '<div class="gallery-overlay">';
                echo '<button class="view-button" onclick="openLightbox(\'' . BASE_URL . '/uploads/' . htmlspecialchars($row['image_path']) . '\', \'' . htmlspecialchars($row['caption']) . '\')">';
                echo '<i class="fas fa-expand"></i> View Full';
                echo '</button>';
                echo '</div>';
                echo '</div>';
                echo '<div class="gallery-info">';
                echo '<h3 class="gallery-caption">' . htmlspecialchars($row['caption']) . '</h3>';
                if (!empty($row['parish_name'])) {
                    echo '<p class="gallery-parish">' . htmlspecialchars($row['parish_name']) . '</p>';
                }
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="no-results animate__animated animate__fadeIn">No gallery items found.</p>';
        }
        ?>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox">
    <span class="close-btn" onclick="closeLightbox()">&times;</span>
    <img class="lightbox-content" id="lightbox-image">
    <div class="lightbox-caption" id="lightbox-caption"></div>
</div>

<?php include '../includes/footer.php'; ?>

<style>
    .gallery-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    .gallery-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .gallery-title {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        position: relative;
        display: inline-block;
    }

    .gallery-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
        border-radius: 2px;
    }

    .gallery-filter {
        position: relative;
        display: inline-block;
    }

    .parish-select {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding: 0.8rem 2.5rem 0.8rem 1.2rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background-color: white;
        font-size: 1rem;
        color: #333;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .parish-select:hover {
        border-color: var(--secondary-color);
    }

    .parish-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
    }

    .select-arrow {
        position: absolute;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
        pointer-events: none;
        color: var(--primary-color);
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }

    .gallery-card {
        background-color: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .gallery-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .gallery-image-container {
        position: relative;
        overflow: hidden;
        height: 250px;
    }

    .gallery-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .gallery-card:hover .gallery-image {
        transform: scale(1.05);
    }

    .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-card:hover .gallery-overlay {
        opacity: 1;
    }

    .view-button {
        background: rgba(255,255,255,0.9);
        color: var(--primary-color);
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .view-button:hover {
        background: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    .gallery-info {
        padding: 1.5rem;
    }

    .gallery-caption {
        font-size: 1.2rem;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .gallery-parish {
        color: #666;
        font-size: 0.9rem;
    }

    .no-results {
        grid-column: 1 / -1;
        text-align: center;
        padding: 2rem;
        color: #666;
    }

    /* Lightbox Styles */
    .lightbox {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        overflow: auto;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .lightbox-content {
        display: block;
        margin: 60px auto;
        max-width: 90%;
        max-height: 80vh;
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0,0,0,0.6);
    }

    .lightbox-caption {
        color: white;
        text-align: center;
        padding: 1rem;
        max-width: 800px;
        margin: 0 auto;
    }

    .close-btn {
        position: absolute;
        top: 20px;
        right: 30px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close-btn:hover {
        color: var(--accent-color);
        transform: scale(1.1);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .gallery-grid {
            grid-template-columns: 1fr;
        }
        
        .gallery-title {
            font-size: 2rem;
        }
    }
</style>

<script>
    // Lightbox functionality
    function openLightbox(imageSrc, caption) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-image');
        const lightboxCaption = document.getElementById('lightbox-caption');
        
        lightbox.style.display = "block";
        lightboxImg.src = imageSrc;
        lightboxCaption.textContent = caption;
        document.body.style.overflow = "hidden";
    }

    function closeLightbox() {
        document.getElementById('lightbox').style.display = "none";
        document.body.style.overflow = "auto";
    }

    // Close lightbox when clicking outside the image
    window.onclick = function(event) {
        const lightbox = document.getElementById('lightbox');
        if (event.target == lightbox) {
            closeLightbox();
        }
    }

    // Add animation delays to gallery cards
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.gallery-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    });
</script>