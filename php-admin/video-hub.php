<?php
require_once 'config.php';
requireAuth();

$message = '';

// Handle video file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
    $uploadsDir = __DIR__ . '/uploads/videos';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    $file = $_FILES['video_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedExts = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
    if (in_array(strtolower($ext), $allowedExts)) {
        $name = uniqid('video_') . '.' . $ext;
        $dest = $uploadsDir . '/' . $name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $videoUrl = $scheme . '://' . $host . '/uploads/videos/' . $name;
            $_POST['video_url'] = $videoUrl;
        }
    }
}

// Handle thumbnail image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
    $uploadsDir = __DIR__ . '/uploads/videos';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    $file = $_FILES['thumbnail_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array(strtolower($ext), $allowedExts)) {
        $name = uniqid('thumb_') . '.' . $ext;
        $dest = $uploadsDir . '/' . $name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $thumbnailUrl = $scheme . '://' . $host . '/uploads/videos/' . $name;
            $_POST['thumbnail'] = $thumbnailUrl;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO videos (title, description, video_url, thumbnail, category, duration, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['video_url'] ?? '',
                $_POST['thumbnail'] ?? '',
                $_POST['category'] ?? 'Beauty',
                $_POST['duration'] ?? '',
                $_POST['status'] ?? 'Active'
            ]);
            $message = 'Video added successfully!';
        } elseif ($_POST['action'] === 'edit') {
            // Get existing video data
            $existingStmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
            $existingStmt->execute([$_POST['id']]);
            $existing = $existingStmt->fetch();
            
            $stmt = $pdo->prepare("UPDATE videos SET title = ?, description = ?, video_url = ?, thumbnail = ?, category = ?, duration = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $_POST['title'] ?? $existing['title'] ?? '',
                $_POST['description'] ?? $existing['description'] ?? '',
                $_POST['video_url'] ?? $existing['video_url'] ?? '',
                $_POST['thumbnail'] ?? $existing['thumbnail'] ?? '',
                $_POST['category'] ?? $existing['category'] ?? 'Beauty',
                $_POST['duration'] ?? $existing['duration'] ?? '',
                $_POST['status'] ?? $existing['status'] ?? 'Active',
                $_POST['id']
            ]);
            $message = 'Video updated successfully!';
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = 'Video deleted successfully!';
        }
    }
}

$videos = $pdo->query("SELECT * FROM videos ORDER BY created_at DESC")->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Video Hub</h1>
    <button class="btn btn-primary" onclick="openModal('addModal')">
        <span class="material-icons">video_call</span>
        Add Video
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<div class="content-card">
    <?php if (empty($videos)): ?>
        <div class="empty-state">
            <span class="material-icons">smart_display</span>
            <p>No videos yet</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Thumbnail</th>
                    <th>Category</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($videos as $video): ?>
                    <tr>
                        <td><?= $video['id'] ?></td>
                        <td><?= htmlspecialchars($video['title']) ?></td>
                        <td>
                            <?php if (!empty($video['thumbnail'])): ?>
                                <img src="<?= htmlspecialchars($video['thumbnail']) ?>" alt="Video thumbnail" style="max-width: 100px; max-height: 80px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($video['category'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($video['duration'] ?? '-') ?></td>
                        <td><span class="status-badge <?= strtolower($video['status']) ?>"><?= $video['status'] ?></span></td>
                        <td class="actions">
                            <button class="action-btn edit" onclick="editVideo(<?= $video['id'] ?>)" 
                                data-video='<?= htmlspecialchars(json_encode($video, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>
                                <span class="material-icons">edit</span>
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Delete this video?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $video['id'] ?>">
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
            <h2>Add Video</h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>
                <div class="form-group">
                    <label>Video URL</label>
                    <input type="text" name="video_url" id="add_video_url" placeholder="YouTube or Video URL">
                    <label style="margin-top: 10px; display: block; font-weight: normal;">Or Upload Video File</label>
                    <input type="file" name="video_file" accept="video/*" id="add_video_file">
                    <div id="add_video_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-group">
                    <label>Thumbnail URL</label>
                    <input type="text" name="thumbnail" id="add_thumbnail_url" placeholder="Thumbnail image URL">
                    <label style="margin-top: 10px; display: block; font-weight: normal;">Or Upload Thumbnail Image</label>
                    <input type="file" name="thumbnail_file" accept="image/*" id="add_thumbnail_file">
                    <div id="add_thumbnail_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="Beauty">Beauty</option>
                            <option value="Lifestyle">Lifestyle</option>
                            <option value="Wellness">Wellness</option>
                            <option value="Tutorial">Tutorial</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" name="duration" placeholder="e.g. 10:30">
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
                <button type="submit" class="btn btn-primary">Add Video</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h2>Edit Video</h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description"></textarea>
                </div>
                <div class="form-group">
                    <label>Video URL</label>
                    <input type="text" name="video_url" id="edit_video_url" placeholder="YouTube or Video URL">
                    <label style="margin-top: 10px; display: block; font-weight: normal;">Or Upload Video File</label>
                    <input type="file" name="video_file" accept="video/*" id="edit_video_file">
                    <div id="edit_video_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-group">
                    <label>Thumbnail URL</label>
                    <input type="text" name="thumbnail" id="edit_thumbnail_url" placeholder="Thumbnail image URL">
                    <label style="margin-top: 10px; display: block; font-weight: normal;">Or Upload Thumbnail Image</label>
                    <input type="file" name="thumbnail_file" accept="image/*" id="edit_thumbnail_file">
                    <div id="edit_thumbnail_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="edit_category">
                            <option value="Beauty">Beauty</option>
                            <option value="Lifestyle">Lifestyle</option>
                            <option value="Wellness">Wellness</option>
                            <option value="Tutorial">Tutorial</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" name="duration" id="edit_duration">
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
                <button type="submit" class="btn btn-primary">Update Video</button>
            </div>
        </form>
    </div>
</div>

<script>
function editVideo(id) {
    const btn = event.target.closest('button');
    const videoData = JSON.parse(btn.getAttribute('data-video'));
    document.getElementById('edit_id').value = videoData.id;
    document.getElementById('edit_title').value = videoData.title || '';
    document.getElementById('edit_description').value = videoData.description || '';
    document.getElementById('edit_video_url').value = videoData.video_url || '';
    document.getElementById('edit_thumbnail_url').value = videoData.thumbnail || '';
    document.getElementById('edit_category').value = videoData.category || 'Beauty';
    document.getElementById('edit_duration').value = videoData.duration || '';
    document.getElementById('edit_status').value = videoData.status || 'Active';
    
    // Show video preview if exists
    const videoPreview = document.getElementById('edit_video_preview');
    if (videoData.video_url) {
        if (videoData.video_url.includes('youtube.com') || videoData.video_url.includes('youtu.be')) {
            let embedUrl = videoData.video_url.replace('watch?v=', 'embed/').replace('youtu.be/', 'youtube.com/embed/');
            videoPreview.innerHTML = '<iframe src="' + embedUrl + '" width="300" height="200" frameborder="0" allowfullscreen style="border-radius: 4px;"></iframe>';
        } else {
            videoPreview.innerHTML = '<video src="' + videoData.video_url + '" controls width="300" height="200" style="border-radius: 4px;"></video>';
        }
    } else {
        videoPreview.innerHTML = '';
    }
    
    // Show thumbnail preview if exists
    const thumbPreview = document.getElementById('edit_thumbnail_preview');
    if (videoData.thumbnail) {
        thumbPreview.innerHTML = '<img src="' + videoData.thumbnail + '" style="max-width: 200px; max-height: 150px; border-radius: 4px;">';
    } else {
        thumbPreview.innerHTML = '';
    }
    
    openModal('editModal');
}

// Video file preview for add form
document.getElementById('add_video_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const preview = document.getElementById('add_video_preview');
        const url = URL.createObjectURL(file);
        preview.innerHTML = '<video src="' + url + '" controls width="300" height="200" style="border-radius: 4px;"></video><p style="margin-top: 5px; color: #666; font-size: 12px;">Selected: ' + file.name + '</p>';
    }
});

// Thumbnail preview for add form
document.getElementById('add_thumbnail_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('add_thumbnail_preview');
            preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; border-radius: 4px;">';
        };
        reader.readAsDataURL(file);
    }
});

// Video file preview for edit form
document.getElementById('edit_video_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const preview = document.getElementById('edit_video_preview');
        const url = URL.createObjectURL(file);
        preview.innerHTML = '<video src="' + url + '" controls width="300" height="200" style="border-radius: 4px;"></video><p style="margin-top: 5px; color: #666; font-size: 12px;">Selected: ' + file.name + '</p>';
    }
});

// Thumbnail preview for edit form
document.getElementById('edit_thumbnail_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('edit_thumbnail_preview');
            preview.innerHTML = '<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; border-radius: 4px;">';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
