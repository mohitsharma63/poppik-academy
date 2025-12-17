<?php
require_once 'config.php';
requireAuth();

$message = '';

// Handle logo image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
    $uploadsDir = __DIR__ . '/uploads/partners';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    $file = $_FILES['logo_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    if (in_array(strtolower($ext), $allowedExts)) {
        $name = uniqid('partner_') . '.' . $ext;
        $dest = $uploadsDir . '/' . $name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $logoUrl = $scheme . '://' . $host . '/uploads/partners/' . $name;
            $_POST['logo'] = $logoUrl;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO partners (name, logo, website, description, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'] ?? '',
                $_POST['logo'] ?? '',
                $_POST['website'] ?? '',
                $_POST['description'] ?? '',
                $_POST['sort_order'] ?: 0,
                $_POST['status'] ?? 'Active'
            ]);
            $message = 'Partner added successfully!';
        } elseif ($_POST['action'] === 'edit') {
            $stmt = $pdo->prepare("UPDATE partners SET name = ?, logo = ?, website = ?, description = ?, sort_order = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'] ?? '',
                $_POST['logo'] ?? '',
                $_POST['website'] ?? '',
                $_POST['description'] ?? '',
                $_POST['sort_order'] ?: 0,
                $_POST['status'] ?? 'Active',
                $_POST['id']
            ]);
            $message = 'Partner updated successfully!';
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM partners WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = 'Partner deleted successfully!';
        }
    }
}

$partners = $pdo->query("SELECT * FROM partners ORDER BY sort_order ASC")->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Partners</h1>
    <button class="btn btn-primary" onclick="openModal('addModal')">
        <span class="material-icons">add</span>
        Add Partner
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<div class="content-card">
    <?php if (empty($partners)): ?>
        <div class="empty-state">
            <span class="material-icons">handshake</span>
            <p>No partners yet</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Logo</th>
                    <th>Name</th>
                    <th>Website</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($partners as $partner): ?>
                    <tr>
                        <td><?= $partner['id'] ?></td>
                        <td>
                            <?php if (!empty($partner['logo'])): ?>
                                <img src="<?= htmlspecialchars($partner['logo']) ?>" alt="Partner logo" style="max-width: 80px; max-height: 60px; object-fit: contain; border-radius: 4px;">
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($partner['name']) ?></td>
                        <td><?= htmlspecialchars($partner['website'] ?? '-') ?></td>
                        <td><span class="status-badge <?= strtolower($partner['status']) ?>"><?= $partner['status'] ?></span></td>
                        <td class="actions">
                            <button class="action-btn edit" onclick="editPartner(<?= $partner['id'] ?>)" 
                                data-partner='<?= htmlspecialchars(json_encode($partner, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>
                                <span class="material-icons">edit</span>
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Delete this partner?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $partner['id'] ?>">
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
            <h2>Add Partner</h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Partner Name <span style="color: red;">*</span></label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Logo Upload</label>
                    <input type="file" name="logo_file" accept="image/*" id="add_logo_file">
                    <input type="text" name="logo" id="add_logo" placeholder="Or enter logo URL" style="margin-top: 8px;">
                    <div id="add_logo_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-group">
                    <label>Website</label>
                    <input type="text" name="website" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
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
                <button type="submit" class="btn btn-primary">Add Partner</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Partner</h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Partner Name <span style="color: red;">*</span></label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Logo Upload</label>
                    <input type="file" name="logo_file" accept="image/*" id="edit_logo_file">
                    <input type="text" name="logo" id="edit_logo" placeholder="Or enter logo URL" style="margin-top: 8px;">
                    <div id="edit_logo_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-group">
                    <label>Website</label>
                    <input type="text" name="website" id="edit_website">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort_order">
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
                <button type="submit" class="btn btn-primary">Update Partner</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPartner(id) {
    const btn = event.target.closest('button');
    const partnerData = JSON.parse(btn.getAttribute('data-partner'));
    document.getElementById('edit_id').value = partnerData.id;
    document.getElementById('edit_name').value = partnerData.name || '';
    document.getElementById('edit_logo').value = partnerData.logo || '';
    document.getElementById('edit_website').value = partnerData.website || '';
    document.getElementById('edit_description').value = partnerData.description || '';
    document.getElementById('edit_sort_order').value = partnerData.sort_order || 0;
    document.getElementById('edit_status').value = partnerData.status || 'Active';
    
    // Show logo preview if exists
    const preview = document.getElementById('edit_logo_preview');
    if (partnerData.logo) {
        preview.innerHTML = '<img src="' + partnerData.logo + '" style="max-width: 200px; max-height: 150px; object-fit: contain; border-radius: 4px;">';
    } else {
        preview.innerHTML = '';
    }
    
    openModal('editModal');
}

// Logo preview for add form
document.getElementById('add_logo_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('add_logo_preview');
            preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; object-fit: contain; border-radius: 4px;">';
        };
        reader.readAsDataURL(file);
    }
});

// Logo preview for edit form
document.getElementById('edit_logo_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('edit_logo_preview');
            preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; object-fit: contain; border-radius: 4px;">';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
