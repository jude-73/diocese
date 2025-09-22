<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

$breadcrumb = 'Admin Dashboard &raquo; Gallery Management';
?>
<?php include '../includes/header.php'; ?>

<div class="container" style="margin-top: 30px; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 style="color: #4a00e0; font-weight: 600; font-size: 28px;">Gallery Management</h2>
        <a href="actions/gallery-crud.php?action=add" style="background: linear-gradient(135deg, #8e2de2, #4a00e0); color: white; padding: 12px 20px; text-decoration: none; border-radius: 8px; font-weight: 500; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(142, 45, 226, 0.3); transition: transform 0.2s, box-shadow 0.2s;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 5px;">
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            Add New Image
        </a>
    </div>
    
    <div style="background-color: white; padding: 25px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.08);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr>
                        <th style="padding: 15px; text-align: left; background: linear-gradient(135deg, #f5f7fa, #e4e8f0); color: #4a5568; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Image</th>
                        <th style="padding: 15px; text-align: left; background: linear-gradient(135deg, #f5f7fa, #e4e8f0); color: #4a5568; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Caption</th>
                        <th style="padding: 15px; text-align: left; background: linear-gradient(135deg, #f5f7fa, #e4e8f0); color: #4a5568; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Parish</th>
                        <th style="padding: 15px; text-align: left; background: linear-gradient(135deg, #f5f7fa, #e4e8f0); color: #4a5568; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Uploaded By</th>
                        <th style="padding: 15px; text-align: left; background: linear-gradient(135deg, #f5f7fa, #e4e8f0); color: #4a5568; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Date</th>
                        <th style="padding: 15px; text-align: left; background: linear-gradient(135deg, #f5f7fa, #e4e8f0); color: #4a5568; font-weight: 600; border-bottom: 2px solid #e2e8f0;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT g.*, p.name as parish_name FROM gallery g LEFT JOIN parishes p ON g.parish_id = p.id ORDER BY g.uploaded_at DESC");
                    
                    if ($result->num_rows > 0) {
                        $rowColor = true;
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr style="background-color: ' . ($rowColor ? '#ffffff' : '#f8fafc') . '; transition: background-color 0.2s;">';
                            echo '<td style="padding: 15px; border-bottom: 1px solid #edf2f7;"><img src="' . BASE_URL . '/uploads/' . htmlspecialchars($row['image_path']) . '" alt="Gallery Image" style="width: 80px; height: 60px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></td>';
                            echo '<td style="padding: 15px; border-bottom: 1px solid #edf2f7; color: #2d3748; font-weight: 500;">' . htmlspecialchars($row['caption']) . '</td>';
                            echo '<td style="padding: 15px; border-bottom: 1px solid #edf2f7; color: #4a5568;">' . (!empty($row['parish_name']) ? htmlspecialchars($row['parish_name']) : 'N/A') . '</td>';
                            echo '<td style="padding: 15px; border-bottom: 1px solid #edf2f7; color: #4a5568;">' . ucfirst($row['uploaded_by']) . '</td>';
                            echo '<td style="padding: 15px; border-bottom: 1px solid #edf2f7; color: #718096;">' . date('M d, Y', strtotime($row['uploaded_at'])) . '</td>';
                            echo '<td style="padding: 15px; border-bottom: 1px solid #edf2f7;">';
                            echo '<div style="display: flex; gap: 12px;">';
                            echo '<a href="actions/gallery-crud.php?action=edit&id=' . $row['id'] . '" style="color: #4a00e0; text-decoration: none; font-weight: 500; padding: 6px 12px; border-radius: 6px; background-color: #f0e6ff; display: inline-flex; align-items: center; transition: all 0.2s;" onmouseover="this.style.backgroundColor=\'#e2d4ff\'; this.style.transform=\'translateY(-2px)\'" onmouseout="this.style.backgroundColor=\'#f0e6ff\'; this.style.transform=\'translateY(0)\'">';
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 6px;">';
                            echo '<path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>';
                            echo '</svg>Edit</a>';
                            echo '<a href="actions/gallery-crud.php?action=delete&id=' . $row['id'] . '" style="color: #e53e3e; text-decoration: none; font-weight: 500; padding: 6px 12px; border-radius: 6px; background-color: #ffebeb; display: inline-flex; align-items: center; transition: all 0.2s;" onmouseover="this.style.backgroundColor=\'#ffdbdb\'; this.style.transform=\'translateY(-2px)\'" onmouseout="this.style.backgroundColor=\'#ffebeb\'; this.style.transform=\'translateY(0)\'" onclick="return confirm(\'Are you sure you want to delete this image?\')">';
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 6px;">';
                            echo '<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>';
                            echo '<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>';
                            echo '</svg>Delete</a>';
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                            $rowColor = !$rowColor;
                        }
                    } else {
                        echo '<tr><td colspan="6" style="padding: 20px; text-align: center; color: #718096; font-size: 16px;">No gallery items found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>