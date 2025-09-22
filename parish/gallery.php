<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isParishLoggedIn()) {
    header("Location: index.php");
    exit();
}

$parish_id = $_SESSION['parish_id'];
$parish_name = $_SESSION['parish_name'];

$breadcrumb = 'Parish Dashboard &raquo; Gallery';
?>
<?php include '../includes/header.php'; ?>

<style>
    /* Modern Gallery Styles */
    .container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    /* Header Styles */
    .gallery-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .gallery-title {
        color: var(--primary-color);
        margin: 0;
        font-size: 1.75rem;
        font-weight: 600;
    }

    /* Modern Add New Button */
    .add-new-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #4a6fa5 0%, #3a5a8a 100%);
        color: white;
        border: none;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        box-shadow: 0 4px 15px rgba(74, 111, 165, 0.3);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
    }

    .add-new-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(74, 111, 165, 0.4);
        background: linear-gradient(135deg, #3a5a8a 0%, #4a6fa5 100%);
    }

    .add-new-btn:active {
        transform: translateY(0);
    }

    .add-new-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
    }

    .add-new-btn:hover::before {
        left: 100%;
    }

    .add-new-btn i {
        margin-right: 0.5rem;
        font-size: 1.1rem;
    }

    /* Gallery Grid */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    /* Gallery Card */
    .gallery-card {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .gallery-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .gallery-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-bottom: 1px solid #eee;
    }

    .gallery-card-body {
        padding: 1.25rem;
    }

    .gallery-card-title {
        margin: 0 0 0.5rem 0;
        color: var(--primary-color);
        font-size: 1.1rem;
        font-weight: 500;
    }

    .gallery-card-date {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.75rem;
    }

    .btn-edit {
        color: var(--secondary-color);
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.2s;
    }

    .btn-edit:hover {
        color: #5a6268;
        text-decoration: underline;
    }

    .btn-delete {
        color: #dc3545;
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.2s;
    }

    .btn-delete:hover {
        color: #c82333;
        text-decoration: underline;
    }

    /* Empty State */
    .empty-gallery {
        grid-column: 1 / -1;
        text-align: center;
        padding: 3rem;
        color: #6c757d;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
    }
</style>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div class="container">
    <div class="gallery-header">
        <h2 class="gallery-title"><?php echo htmlspecialchars($parish_name); ?> Gallery</h2>
        <a href="actions/gallery-crud.php?action=add" class="add-new-btn">
            <i class="fas fa-plus-circle"></i> Add New Image
        </a>
    </div>
    
    <div class="gallery-grid">
        <?php
        $stmt = $conn->prepare("SELECT * FROM gallery WHERE parish_id = ? ORDER BY uploaded_at DESC");
        $stmt->bind_param("i", $parish_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="gallery-card">';
                echo '<img src="' . BASE_URL . '/uploads/' . htmlspecialchars($row['image_path']) . '" alt="Gallery Image" class="gallery-image">';
                echo '<div class="gallery-card-body">';
                echo '<h3 class="gallery-card-title">' . htmlspecialchars($row['caption']) . '</h3>';
                echo '<p class="gallery-card-date">' . date('M d, Y', strtotime($row['uploaded_at'])) . '</p>';
                echo '<div class="action-buttons">';
                echo '<a href="actions/gallery-crud.php?action=edit&id=' . $row['id'] . '" class="btn-edit">Edit</a>';
                echo '<a href="actions/gallery-crud.php?action=delete&id=' . $row['id'] . '" class="btn-delete" onclick="return confirm(\'Are you sure you want to delete this image?\')">Delete</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="empty-gallery">';
            echo '<p>No gallery items found. Add your first image.</p>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>