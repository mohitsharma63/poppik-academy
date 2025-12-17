<?php
require_once 'config.php';
requireAuth();

$message = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $uploadsDir = __DIR__ . '/uploads/hero-sliders';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    $file = $_FILES['image_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array(strtolower($ext), $allowedExts)) {
        $name = uniqid('slider_') . '.' . $ext;
        $dest = $uploadsDir . '/' . $name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $imageUrl = $scheme . '://' . $host . '/uploads/hero-sliders/' . $name;
            $_POST['image'] = $imageUrl;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            // Update table structure if needed (make fields nullable)
            try {
                $pdo->exec("ALTER TABLE hero_sliders MODIFY COLUMN title VARCHAR(255) DEFAULT NULL");
                $pdo->exec("ALTER TABLE hero_sliders MODIFY COLUMN subtitle TEXT DEFAULT NULL");
                $pdo->exec("ALTER TABLE hero_sliders MODIFY COLUMN button_text VARCHAR(255) DEFAULT NULL");
                $pdo->exec("ALTER TABLE hero_sliders MODIFY COLUMN button_link VARCHAR(255) DEFAULT NULL");
            } catch (PDOException $e) {
                // Table might already be updated, ignore
            }
            
            $stmt = $pdo->prepare("INSERT INTO hero_sliders (image, sort_order, status, title, subtitle, button_text, button_link) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['image'] ?? '',
                $_POST['sort_order'] ?: 0,
                $_POST['status'] ?? 'Active',
                '', // title
                '', // subtitle
                '', // button_text
                ''  // button_link
            ]);
            $message = 'Slider added successfully!';
        } elseif ($_POST['action'] === 'edit') {
            $stmt = $pdo->prepare("UPDATE hero_sliders SET image = ?, sort_order = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $_POST['image'] ?? '',
                $_POST['sort_order'] ?: 0,
                $_POST['status'] ?? 'Active',
                $_POST['id']
            ]);
            $message = 'Slider updated successfully!';
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM hero_sliders WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = 'Slider deleted successfully!';
        }
    }
}

$sliders = $pdo->query("SELECT * FROM hero_sliders ORDER BY sort_order ASC")->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Hero Sliders</h1>
    <button class="btn btn-primary" onclick="openModal('addModal')">
        <span class="material-icons">add</span>
        Add Slider
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<div class="content-card">
    <?php if (empty($sliders)): ?>
        <div class="empty-state">
            <span class="material-icons">view_carousel</span>
            <p>No hero sliders yet</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sliders as $slider): ?>
                    <tr>
                        <td><?= $slider['sort_order'] ?></td>
                        <td>
                            <?php if (!empty($slider['image'])): ?>
                                <img src="<?= htmlspecialchars($slider['image']) ?>" alt="Slider image" style="max-width: 150px; max-height: 100px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <span style="color: #999;">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="status-badge <?= strtolower($slider['status']) ?>"><?= $slider['status'] ?></span></td>
                        <td class="actions">
                            <button class="action-btn edit" onclick="editSlider(<?= $slider['id'] ?>)" 
                                data-slider='<?= htmlspecialchars(json_encode($slider, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>
                                <span class="material-icons">edit</span>
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Delete this slider?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $slider['id'] ?>">
                                <button type="submit" class="action-btn delete">
                                    <span class="material-icons">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Add Slider</h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image_file" accept="image/*" id="add_image_file" required>
                    <input type="text" name="image" id="add_image" placeholder="Or enter image URL" style="margin-top: 8px;">
                    <div id="add_image_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="0">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Slider</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Slider</h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image_file" accept="image/*" id="edit_image_file">
                    <input type="text" name="image" id="edit_image" placeholder="Or enter image URL" style="margin-top: 8px;">
                    <div id="edit_image_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort_order" value="0">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="edit_status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Slider</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSlider(id) {
    const btn = event.target.closest('button');
    const sliderData = JSON.parse(btn.getAttribute('data-slider'));
    document.getElementById('edit_id').value = sliderData.id;
    document.getElementById('edit_image').value = sliderData.image || '';
    document.getElementById('edit_sort_order').value = sliderData.sort_order || 0;
    document.getElementById('edit_status').value = sliderData.status || 'Active';
    
    // Show image preview if exists
    const preview = document.getElementById('edit_image_preview');
    if (sliderData.image) {
        preview.innerHTML = '<img src="' + sliderData.image + '" style="max-width: 200px; max-height: 150px; border-radius: 4px;">';
    } else {
        preview.innerHTML = '';
    }
    
    openModal('editModal');
}

// Image preview for add form
document.getElementById('add_image_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('add_image_preview');
            preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; border-radius: 4px;">';
        };
        reader.readAsDataURL(file);
    }
});

// Image preview for edit form
document.getElementById('edit_image_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('edit_image_preview');
            preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; border-radius: 4px;">';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
