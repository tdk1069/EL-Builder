<?php
require 'auth/db.php';
session_start();

$areaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($areaId <= 0) {
    die("Invalid area ID.");
}

// Fetch metadata for this area
$stmt = $pdo->prepare("SELECT * FROM areas WHERE id = ?");
$stmt->execute([$areaId]);
$area = $stmt->fetch();

if (!$area) {
    die("Area not found.");
}

// Optionally fetch existing room data too
$stmt = $pdo->prepare("SELECT data FROM rooms WHERE area_id = ?");
$stmt->execute([$areaId]);
$roomRow = $stmt->fetch();

$roomJson = $roomRow ? $roomRow->data : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MUD Area Editor</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
<script>
  const areaMeta = <?php echo json_encode($area); ?>;
  const loadedRoomData = <?php echo $roomJson ? $roomJson : 'null'; ?>;
  const areaId = <?php echo (int)$areaId; ?>;
</script>

  <div id="container">
    <div id="map">
      <canvas id="mapCanvas" width="800" height="800"></canvas>
    </div>
<div class="sidebar">
  <!-- Tab Buttons -->
  <div class="tab-buttons">
    <button onclick="showTab('basic')" class="active">Basic</button>
    <button onclick="showTab('objects')">Objects</button>
    <button onclick="showTab('monsters')">Monsters</button>
  </div>
  <!-- Tab Content Areas -->
  <div id="tab-basic" class="tab-content">
    <!-- Your current fields like set_short, set_long, etc. -->
    <div id="editor">
      <h2>Room Editor</h2>
      <form id="roomForm">
        <label>Short Description:<br>
          <input type="text" id="set_short" name="set_short">
        </label><br><br>
        <label>Long Description:<br>
          <textarea id="set_long" name="set_long" rows="4"></textarea>
        </label><br><br>
        <label style="display:none;">Smell:<br>
          <input type="text" id="set_smell" name="set_smell">
        </label><br><br>

      </form>
<div id="compass">
    <div class="dir-row">
        <button onclick="move('northwest')">↖</button>
        <button onclick="move('north')">↑</button>
        <button onclick="move('northeast')">↗</button>
    </div>
    <div class="dir-row">
        <button onclick="move('west')">←</button>
        <button onclick="move('')">•</button>
        <button onclick="move('east')">→</button>
    </div>
    <div class="dir-row">
        <button onclick="move('southwest')">↙</button>
        <button onclick="move('south')">↓</button>
        <button onclick="move('southeast')">↘</button>
    </div>
    <div class="dir-row">
        <button onclick="move('up')">Up</button>
        <button onclick="move('down')">Down</button>
    </div>
<label>
  Items
  <button type="button" id="addItemBtn">+</button>
  <button type="button" id="extractItemsBtn" title="Extract items from description">🔭</button>
</label>

<div id="itemsContainer"></div>
<!-- Container for download/upload -->
<div class="area-controls">
  <button onclick="downloadArea()">Download Area</button>

  <!-- Hidden file input -->
  <input type="file" id="uploadFile" accept=".json" onchange="uploadArea(event)" style="display:none;">

  <!-- Label styled as button -->
  <label for="uploadFile" class="upload-label">Upload Area</label>
</div>

  </div> <!-- end of Basic Tab -->
<!-- Save and Back buttons -->
<div class="save-controls" style="margin-top: 10px;">
  <button onclick="saveAreaToDb()">Save Area to Database</button>
  <a href="dashboard.php" class="back-button">Back to Dashboard</a>
</div>

<div id="saveStatus" style="margin-top: 8px; font-weight: bold;"></div>
      </div>
    </div>
    <div id="tab-monsters" class="tab-content" style="display: none;">
      <!-- Monster assignment UI (dropdown + list of assigned monsters) -->
      <div id="monsterContainer"></div>
      <button type="button" onclick="addMonsterRow()">+ Add Monster</button>
    </div>
  <div id="tab-objects" class="tab-content" style="display: none;">
      <!-- Object assignment UI (dropdown + list of assigned Objects-->
      <div id="objectContainer"></div>
      <button type="button" onclick="addObjectRow()">+ Add Object</button>
  </div>


  </div>

  <script src="compromise.js"></script>
  <script src="script.js"></script>
  <div id="roomMenu" style="position: absolute; display: none; background: #222; color: #fff; padding: 8px; border: 1px solid #555;"></div>

</body>
</html>
