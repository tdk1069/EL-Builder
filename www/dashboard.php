<?php
require 'auth/db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.html');
    exit;
}

$username = $_SESSION['username'];
$filter = $_SESSION['username'];

$stmt = $pdo->prepare('SELECT * FROM areas WHERE username = ? ORDER BY id DESC');
$stmt->execute([$username]);
$areas = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>MUD Area Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Fancy font -->
  <link href="https://fonts.googleapis.com/css2?family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">

  <style>
    body {
      background: url('assets/background.svg') no-repeat center center;
      background-size: cover;
      font-family: 'Georgia', serif;
      color: #3e2f1c;
    }

    h1 {
      font-family: 'UnifrakturCook', cursive;
      text-align: center;
      margin-top: 2rem;
      margin-bottom: 1rem;
    }

    .area-card {
      background-color: rgba(255, 248, 220, 0.95);
      border: 2px solid #7a5c3e;
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 1rem;
      box-shadow: 3px 3px 8px rgba(0,0,0,0.3);
    }

    .btn-parchment {
      background-color: #7a5c3e;
      border: none;
      color: #fff8e7;
    }

    .btn-parchment:hover {
      background-color: #5a3e2b;
    }

    .parchment-bg {
      background: #fdf6e3;
      border: 1px solid #d8c792;
      color: #3a2f1b;
    }

  </style>
</head>
<body>
<?php $currentPage = 'object'; include 'header.php'; ?>
<div class="container mt-4">
  <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>

<?php if (empty($areas)): ?>
  <p class="text-center">You haven't created any areas yet.</p>
<?php else: ?>
  <?php foreach ($areas as $area): ?>
    <div class="area-card">
      <h4><?= htmlspecialchars($area->name ?? 'Unnamed Area') ?></h4>
      <p><strong>Base Path:</strong> <?= htmlspecialchars($area->basePath ?? '-') ?></p>
      <p><strong>Base Level:</strong> <?= htmlspecialchars($area->levelRange ?? '?') ?></p>
      <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($area->description ?? 'No description')) ?></p>
      <a href="editor.php?id=<?= $area->id ?>" class="btn btn-parchment">Designer</a>
      <button class="btn btn-secondary btn-parchment edit-properties-btn"
              data-area='<?= json_encode($area, JSON_HEX_APOS | JSON_HEX_AMP) ?>'>Edit Properties</button>
<a class="btn btn-warning" href="export_area.php?area_id=<?= $area->id ?>">Export LPC</a>
      <button class="btn btn-danger delete-area" data-id="<?= $area->id ?>">Delete</button>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

  <div class="d-flex justify-content-center mt-4 gap-3 flex-wrap">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createAreaModal">
      + Create New Area
    </button>
    <a href="auth/logout.php" class="btn btn-secondary btn-parchment">
      Log Out
    </a>
  </div>

</div>

<!-- Modal -->
<div class="modal fade" id="createAreaModal" tabindex="-1" aria-labelledby="createAreaModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content parchment-bg">
      <div class="modal-header">
        <h5 class="modal-title" id="createAreaModalLabel">Create New Area</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="createAreaForm">
          <input type="hidden" name="areaId" id="areaId" />
          <div class="mb-3">
            <label class="form-label">Area Name</label>
            <input type="text" class="form-control" name="name" required id="name"/>
          </div>
          <div class="mb-3">
            <label class="form-label">Base Path</label>
            <input type="text" class="form-control" name="basePath" required id="basePath"/>
          </div>
          <div class="mb-3">
            <label class="form-label">Base Level</label>
            <input type="number" class="form-control" name="levelRange" placeholder="e.g. 10-20" id="levelRange"/>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" id="description"></textarea>
          </div>
          <button type="submit" class="btn btn-primary" id="areaCreateConfirm">Create</button>
          <button type="button" class="btn btn-secondary btn-parchment" data-bs-dismiss="modal">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content parchment-bg">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this area?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-parchment" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
      </div>
    </div>
  </div>
</div>


</body>

<!-- Bootstrap Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('createAreaForm').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = {
        id: formData.get('areaId') || null,
        name: formData.get('name'),
        basePath: formData.get('basePath'),
        levelRange: formData.get('levelRange'),
        description: formData.get('description')
    };

console.log(data);

    const res = await fetch('auth/create_area.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    const result = await res.json();
    if (result.success) {
        location.reload(); // Refresh to show the new area
    } else {
        alert(result.error || "Failed to create area.");
    }
};

document.querySelectorAll('.edit-properties-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    const area = JSON.parse(btn.getAttribute('data-area'));

    // Fill form fields
    document.getElementById('createAreaModalLabel').textContent = 'Edit Area Properties';
    document.getElementById('areaCreateConfirm').textContent = "Update" || '';
    // document.getElementById('areaId').value = area._id['$oid'] || area._id; // cast ID properly
    document.getElementById('areaId').value = area.id;
    document.getElementById('name').value = area.name || '';
    document.getElementById('basePath').value = area.basePath || '';
    document.getElementById('levelRange').value = area.levelRange || '';
    document.getElementById('description').value = area.description || '';

    // Show modal
    const areaModal = new bootstrap.Modal(document.getElementById('createAreaModal'));
    areaModal.show();
  });
});

let deleteAreaId = null;

document.querySelectorAll('.delete-area').forEach(button => {
  button.addEventListener('click', () => {
    deleteAreaId = button.getAttribute('data-id');
    const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    deleteModal.show();
  });
});

document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
  if (!deleteAreaId) return;

  const res = await fetch('auth/delete_area.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: deleteAreaId })
  });

  const result = await res.json();
  if (result.success) {
    location.reload();
  } else {
    alert(result.error || 'Failed to delete area.');
  }
});
</script>


</html>
