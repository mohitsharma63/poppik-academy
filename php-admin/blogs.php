<?php
require_once 'config.php';
requireAuth();

$message = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $uploadsDir = __DIR__ . '/uploads/blogs';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    $file = $_FILES['image_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array(strtolower($ext), $allowedExts)) {
        $name = uniqid('blog_') . '.' . $ext;
        $dest = $uploadsDir . '/' . $name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST']; // Already includes port if non-standard (e.g., 127.0.0.1:8000)
            // Build full URL for storage in database
            $imageUrl = $scheme . '://' . $host . '/uploads/blogs/' . $name;
            $_POST['image'] = $imageUrl;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("INSERT INTO blogs (title, excerpt, content, image, author, category, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['title'],
                $_POST['excerpt'],
                $_POST['content'],
                $_POST['image'] ?? '',
                $_POST['author'],
                $_POST['category'],
                $_POST['status']
            ]);
            $message = 'Blog post added successfully!';
        } elseif ($_POST['action'] === 'edit') {
            $stmt = $pdo->prepare("UPDATE blogs SET title = ?, excerpt = ?, content = ?, image = ?, author = ?, category = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $_POST['title'],
                $_POST['excerpt'],
                $_POST['content'],
                $_POST['image'] ?? '',
                $_POST['author'],
                $_POST['category'],
                $_POST['status'],
                $_POST['id']
            ]);
            $message = 'Blog post updated successfully!';
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = 'Blog post deleted successfully!';
        }
    }
}

$blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();

include 'includes/header.php';
?>

<div class="page-header">
    <h1>Blogs</h1>
    <button class="btn btn-primary" onclick="openModal('addModal')">
        <span class="material-icons">add</span>
        Add Blog Post
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<div class="content-card">
    <?php if (empty($blogs)): ?>
        <div class="empty-state">
            <span class="material-icons">description</span>
            <p>No blog posts yet</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Image</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blogs as $blog): ?>
                    <tr>
                        <td><?= $blog['id'] ?></td>
                        <td><?= htmlspecialchars($blog['title']) ?></td>
                        <td>
                            <?php if (!empty($blog['image'])): ?>
                                <img src="<?= htmlspecialchars($blog['image']) ?>" alt="Blog image" style="max-width: 80px; max-height: 60px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($blog['author'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($blog['category'] ?? '-') ?></td>
                        <td><span class="status-badge <?= strtolower($blog['status']) ?>"><?= $blog['status'] ?></span></td>
                        <td><?= date('M d, Y', strtotime($blog['created_at'])) ?></td>
                        <td class="actions">
                            <button class="action-btn edit" onclick="editBlog(<?= $blog['id'] ?>)" 
                                data-blog='<?= htmlspecialchars(json_encode($blog, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>'>
                                <span class="material-icons">edit</span>
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Delete this blog post?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $blog['id'] ?>">
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
    <div class="modal" style="max-width:600px;">
        <div class="modal-header">
            <h2>Add Blog Post</h2>
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
                    <label>Excerpt</label>
                    <textarea name="excerpt" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <div class="editor-toolbar" id="add_toolbar">
                        <button type="button" onclick="execCommand('bold')" title="Bold"><strong>B</strong></button>
                        <button type="button" onclick="execCommand('italic')" title="Italic"><em>I</em></button>
                        <button type="button" onclick="execCommand('underline')" title="Underline"><u>U</u></button>
                        <button type="button" onclick="execCommand('insertUnorderedList')" title="Bullet List">• List</button>
                        <button type="button" onclick="execCommand('insertOrderedList')" title="Numbered List">1. List</button>
                        <button type="button" onclick="addLink('add_content')" title="Add Link">Link</button>
                        <button type="button" onclick="execCommand('undo')" title="Undo">↶ Undo</button>
                        <button type="button" onclick="execCommand('redo')" title="Redo">↷ Redo</button>
                    </div>
                    <div contenteditable="true" id="add_content" class="editor-content" style="min-height: 150px; border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: white;"></div>
                    <textarea name="content" id="add_content_hidden" style="display:none;"></textarea>
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image_file" accept="image/*" id="add_image_file">
                    <input type="text" name="image" id="add_image" placeholder="Or enter image URL" style="margin-top: 8px;">
                    <div id="add_image_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Author</label>
                        <input type="text" name="author">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="Beauty">Beauty</option>
                            <option value="Lifestyle">Lifestyle</option>
                            <option value="Wellness">Wellness</option>
                            <option value="Tips">Tips</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="Draft">Draft</option>
                        <option value="Published">Published</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Blog Post</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal" style="max-width:600px;">
        <div class="modal-header">
            <h2>Edit Blog Post</h2>
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
                    <label>Excerpt</label>
                    <textarea name="excerpt" id="edit_excerpt" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <div class="editor-toolbar" id="edit_toolbar">
                        <button type="button" onclick="execCommand('bold')" title="Bold"><strong>B</strong></button>
                        <button type="button" onclick="execCommand('italic')" title="Italic"><em>I</em></button>
                        <button type="button" onclick="execCommand('underline')" title="Underline"><u>U</u></button>
                        <button type="button" onclick="execCommand('insertUnorderedList')" title="Bullet List">• List</button>
                        <button type="button" onclick="execCommand('insertOrderedList')" title="Numbered List">1. List</button>
                        <button type="button" onclick="addLink('edit_content')" title="Add Link">Link</button>
                        <button type="button" onclick="execCommand('undo')" title="Undo">↶ Undo</button>
                        <button type="button" onclick="execCommand('redo')" title="Redo">↷ Redo</button>
                    </div>
                    <div contenteditable="true" id="edit_content" class="editor-content" style="min-height: 150px; border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: white;"></div>
                    <textarea name="content" id="edit_content_hidden" style="display:none;"></textarea>
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image_file" accept="image/*" id="edit_image_file">
                    <input type="text" name="image" id="edit_image" placeholder="Or enter image URL" style="margin-top: 8px;">
                    <div id="edit_image_preview" style="margin-top: 10px;"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Author</label>
                        <input type="text" name="author" id="edit_author">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="edit_category">
                            <option value="Beauty">Beauty</option>
                            <option value="Lifestyle">Lifestyle</option>
                            <option value="Wellness">Wellness</option>
                            <option value="Tips">Tips</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="Draft">Draft</option>
                        <option value="Published">Published</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Blog Post</button>
            </div>
        </form>
    </div>
</div>

<style>
.editor-toolbar {
    display: flex;
    gap: 5px;
    padding: 8px;
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-bottom: none;
    border-radius: 4px 4px 0 0;
    flex-wrap: wrap;
}
.editor-toolbar button {
    padding: 6px 12px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 3px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
}
.editor-toolbar button:hover {
    background: #e9e9e9;
}
.editor-content {
    outline: none;
    font-family: Arial, sans-serif;
    font-size: 14px;
    line-height: 1.6;
}
.editor-content:focus {
    border-color: #667eea;
}
.editor-content ul, .editor-content ol {
    margin: 10px 0;
    padding-left: 30px;
}
</style>

<script>
function execCommand(cmd, value) {
    document.execCommand(cmd, false, value || null);
    // Update hidden textarea
    updateContent();
}

function addLink(editorId) {
    const url = prompt('Enter the URL:');
    if (url) {
        const editor = document.getElementById(editorId);
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const link = document.createElement('a');
            link.href = url;
            link.textContent = selection.toString() || url;
            link.target = '_blank';
            range.deleteContents();
            range.insertNode(link);
            updateContent();
        } else {
            execCommand('createLink', url);
        }
    }
}

function updateContent() {
    // Update hidden textarea for add form
    const addEditor = document.getElementById('add_content');
    const addHidden = document.getElementById('add_content_hidden');
    if (addEditor && addHidden) {
        addHidden.value = addEditor.innerHTML;
    }
    
    // Update hidden textarea for edit form
    const editEditor = document.getElementById('edit_content');
    const editHidden = document.getElementById('edit_content_hidden');
    if (editEditor && editHidden) {
        editHidden.value = editEditor.innerHTML;
    }
}

// Update content on input
document.addEventListener('DOMContentLoaded', function() {
    const addEditor = document.getElementById('add_content');
    const editEditor = document.getElementById('edit_content');
    
    if (addEditor) {
        addEditor.addEventListener('input', updateContent);
        addEditor.addEventListener('blur', updateContent);
    }
    
    if (editEditor) {
        editEditor.addEventListener('input', updateContent);
        editEditor.addEventListener('blur', updateContent);
    }
    
    // Update content before form submit
    const forms = document.querySelectorAll('form[enctype="multipart/form-data"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            updateContent();
        });
    });
});

function editBlog(id) {
    const btn = event.target.closest('button');
    const blogData = JSON.parse(btn.getAttribute('data-blog'));
    document.getElementById('edit_id').value = blogData.id;
    document.getElementById('edit_title').value = blogData.title || '';
    document.getElementById('edit_excerpt').value = blogData.excerpt || '';
    document.getElementById('edit_content').innerHTML = blogData.content || '';
    document.getElementById('edit_content_hidden').value = blogData.content || '';
    document.getElementById('edit_image').value = blogData.image || '';
    document.getElementById('edit_author').value = blogData.author || '';
    document.getElementById('edit_category').value = blogData.category || 'Beauty';
    document.getElementById('edit_status').value = blogData.status || 'Draft';
    
    // Show image preview if exists
    const preview = document.getElementById('edit_image_preview');
    if (blogData.image) {
        preview.innerHTML = '<img src="' + blogData.image + '" style="max-width: 200px; max-height: 150px; border-radius: 4px;">';
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
