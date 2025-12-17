<?php
require_once 'config.php';
requireAuth();

$message = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $uploadsDir = __DIR__ . '/uploads/gallery';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    $file = $_FILES['image_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array(strtolower($ext), $allowedExts)) {
        $name = uniqid('gallery_') . '.' . $ext;
        $dest = $uploadsDir . '/' . $name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $imageUrl = $scheme . '://' . $host . '/uploads/gallery/' . $name;
            $_POST['image'] = $imageUrl;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, image, category, sort_order, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['title'] ?? '',
                $_POST['image'] ?? '',
                $_POST['category'] ?? 'Beauty',
                $_POST['sort_order'] ?: 0,
                $_POST['status'] ?? 'Active'
            ]);
            $message = 'Image added successfully!';
        } elseif ($_POST['action'] === 'edit') {
            $stmt = $pdo->prepare("UPDATE gallery SET title = ?, image = ?, category = ?, sort_order = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $_POST['title'] ?? '',
                $_POST['image'] ?? '',
                $_POST['category'] ?? 'Beauty',
                $_POST['sort_order'] ?: 0,
                $_POST['status'] ?? 'Active',
                $_POST['id']
            ]);
            $message = 'Image updated successfully!';
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = 'Image deleted successfully!';
        }
    }
}

$images = $pdo->query("SELECT * FROM gallery ORDER BY sort_order ASC")->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Gallery</h1>
    <button class="btn btn-primary" onclick="openModal('addModal')">
        <span class="material-icons">add_photo_alternate</span>
        Add Image
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<div class="content-card">
    <?php if (empty($images)): ?>
        <div class="empty-state">
            <span class="material-icons">collections</span>
            <p>No images in gallery yet</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Image</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($images as $image): ?>
                    <tr>
                        <td><?= $image['id'] ?></td>
                        <td><?= htmlspecialchars($image['title'] ?? '-') ?></td>
                        <td>
                            <?php if (!empty($image['image'])): ?>
                                <img src="<?= htmlspecialchars($image['image']) ?>" alt="Gallery image" style="max-width: 100px; max-height: 80px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <span style="color: #999;">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($image['category'] ?? '-') ?></td>
                        <td><span class="status-badge <?= strtolower($image['status']) ?>"><?= $image['status'] ?></span></td>
                        <td class="actions">
                            <button class="action-btn edit" onclick="editImage(<?= $image['id'] ?>)" 
                                data-image='<?= htmlspecialchars(json_encode($image, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>
                                <span class="material-icons">edit</span>
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Delete this image?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $image['id'] ?>">
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
            <h2>Add Image</h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" placeholder="Image title (optional)">
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image_file" accept="image/*" id="add_image_file">
                    <input type="text" name="image" id="add_image" placeholder="Or enter image URL" style="margin-top: 8px;">
                    <div id="add_image_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="Beauty">Beauty</option>
                            <option value="Lifestyle">Lifestyle</option>
                            <option value="Wellness">Wellness</option>
                            <option value="Events">Events</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Image</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Image</h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" id="edit_title" placeholder="Image title (optional)">
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image_file" accept="image/*" id="edit_image_file">
                    <input type="text" name="image" id="edit_image" placeholder="Or enter image URL" style="margin-top: 8px;">
                    <div id="edit_image_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="edit_category">
                            <option value="Beauty">Beauty</option>
                            <option value="Lifestyle">Lifestyle</option>
                            <option value="Wellness">Wellness</option>
                            <option value="Events">Events</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort_order">
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Image</button>
            </div>
        </form>
    </div>
</div>

<script>
function editImage(id) {
    const btn = event.target.closest('button');
    const imageData = JSON.parse(btn.getAttribute('data-image'));
    document.getElementById('edit_id').value = imageData.id;
    document.getElementById('edit_title').value = imageData.title || '';
    document.getElementById('edit_image').value = imageData.image || '';
    document.getElementById('edit_category').value = imageData.category || 'Beauty';
    document.getElementById('edit_sort_order').value = imageData.sort_order || 0;
    document.getElementById('edit_status').value = imageData.status || 'Active';
    
    // Show image preview if exists
    const preview = document.getElementById('edit_image_preview');
    if (imageData.image) {
        preview.innerHTML = '<img src="' + imageData.image + '" style="max-width: 200px; max-height: 150px; border-radius: 4px;">';
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
