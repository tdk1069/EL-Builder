<?php
require 'auth/db.php';
session_start();

$areaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MUD Area Editor</title>
  <link rel="icon" href="/assets/background.svg" type="image/svg+xml">
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <script>
    const areaMeta = <?php echo json_encode($area); ?>;
    const loadedRoomData = <?php echo $roomJson ? $roomJson : 'null'; ?>;
    const areaId = <?php echo (int) $areaId; ?>;
  </script>

  <div id="container">
    <div id="map">
      <canvas id="mapCanvas" width="800" height="800"></canvas>
    </div>
    <div class="sidebar">
      <!-- Tab Buttons -->
      <div class="tab-buttons">
        <button onclick="showTab('basic')" class="active">Basic</button>
        <button onclick="showTab('properties')">Room Properties</button>
        <button onclick="showTab('objects')">Objects</button>
        <button onclick="showTab('monsters')">Monsters</button>
        <button onclick="showTab('notes')">Notes</button>
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
            <a href="dashboard.php" id="backToDashboard" class="back-button">Back to Dashboard</a>
            <button onclick="openImportModal()">Import Room Code</button>

          </div>

          <div id="saveStatus" style="margin-top: 8px; font-weight: bold;"></div>
        </div>
      </div>
      <div id="tab-monsters" class="tab-content" style="display: none;">
        <!-- Monster assignment UI (dropdown + list of assigned monsters) -->
        <div id="monsterContainer"></div>
        <button type="button" onclick="addMonsterRow()">+ Add Monster</button>
	<button onclick="openMonsterModal()">Add Monster (test)</button>
      </div>
      <div id="tab-properties" class="tab-content" style="display: none;">
      <div id="editor">
      <label>Terrain:
        <select name="set_terrain" id="terrainSelect">
          <option value="">-- Select --</option>
          <?php
	  $terrains = ['forest','plains','hills','mountains','town','coast','sea','swamp','jungle','underwater','desert','river','tundra','snowplains','treetops','river','lake'];
          foreach ($terrains as $terrain) {
            echo "<option value=\"$terrain\">$terrain</option>";
          }
          ?>
        </select>
      </label>
      </div>
      </div>
      <div id="tab-objects" class="tab-content" style="display: none;">
        <!-- Object assignment UI (dropdown + list of assigned Objects-->
        <div id="objectContainer"></div>
        <button type="button" onclick="addObjectRow()">+ Add Object</button>
      </div>

      <!-- Tab Content Notes -->
      <div id="tab-notes" class="tab-content"  style="display: none;">
      <div id="editor">
        <label for="notes" style="display:block; margin-bottom:0.5rem; font-weight:bold;">Room Notes:</label>
        <textarea id="notes" name="notes" rows="10" placeholder="Add design notes, ideas, or comments here..." style="width:100%; padding:0.75rem; font-family:monospace; border:1px solid #ccc; border-radius:8px; resize:vertical;box-sizing: border-box;"></textarea>
      </div>
      </div>
    </div>

    <!-- Modal -->
    <div id="importModal">
      <div class="modal-content">
        <h2>Import LPC Room Code</h2>
        <textarea id="lpcInput" style="width: 100%; height: 300px;"></textarea>
        <div style="margin-top: 1em; text-align: right;">
          <button onclick="closeImportModal()">Cancel</button>
          <button onclick="parseAndImportLPC()">Import</button>
        </div>
        <button onclick="closeImportModal()" style="position:absolute; top: 0.5em; right: 0.75em; font-size: 1.5em; background: none; border: none;">&times;</button>
      </div>
    </div>

    <div id="roomMenu"
      style="position: absolute; display: none; background: #222; color: #fff; padding: 8px; border: 1px solid #555;">
    </div>

</body>
<!-- Monster Modal -->

<!-- Modal -->
<div id="monsterModal">
  <div class="modal-content">
    <h2>Add a New Monster</h2>
    <form id="monsterForm" onsubmit="submitMonsterForm(event)">
      <label>Name</label>
      <input type="text" name="set_name" required id="nameInput">

      <label>Short Description</label>
      <textarea name="set_short" id="shortInput"></textarea>

      <label>Long Description</label>
      <textarea name="set_long" id="longdescInput"></textarea>

      <label>Spells</label>
      <textarea name="set_spells" id="spellsInput"></textarea>

<div style="display: flex; align-items: flex-end; gap: 0.5rem; margin-bottom: 1rem;">
  <div style="flex: 1;">
    <label for="classSelect">Class</label>
    <select id="classSelect" name="set_class" required style="width: 100%;">
      <option value="">-- Select Class --</option>
      <option>Fighter</option>
      <option>Warmage</option>
      <option>Rogue</option>
      <option>Cleric</option>
      <option>Monk</option>
      <option>Druid</option>
      <option>Ranger</option>
    </select>
  </div>

  <button type="button" onclick="fillExample()" style="height: 2.2rem; white-space: nowrap;">Fill Example</button>
</div>

      <label>Race</label>
 <select id="raceSelect" name="set_race" required>
    <option value="">-- Select Race --</option>
    <option>Arachnid</option>
    <option>Artrell</option>
    <option>Basilisk</option>
    <option>Bat</option>
    <option>Bee</option>
    <option>Beetle</option>
    <option>Beholder</option>
    <option>Bird</option>
    <option>Carrion-crawler</option>
    <option>Centaur</option>
    <option>Centipede</option>
    <option>Chimera</option>
    <option>Cockatrice</option>
    <option>Crocodile</option>
    <option>Crustacean</option>
    <option>Dragon</option>
    <option>Drider</option>
    <option>Dwarf</option>
    <option>Elf</option>
    <option>Equine</option>
    <option>Equine-winged</option>
    <option>Faerie</option>
    <option>Fish</option>
    <option>Frog</option>
    <option>Human</option>
    <option>Halfling</option>
    <option>Humanoid-tail</option>
    <option>Insectoid</option>
    <option>Insectoid-winged</option>
    <option>Merperson</option>
    <option>Orc</option>
    <option>Octopus</option>
    <option>Otyugh</option>
    <option>Quadruped</option>
    <option>Quadruped-winged</option>
    <option>Ray</option>
    <option>Reptile</option>
    <option>Reptile-winged</option>
    <option>Satyr</option>
    <option>Slime</option>
    <option>Snake-winged</option>
    <option>Snakeman</option>
    <option>Turtle</option>
    <option>Worm</option>
  </select>

      <label>Level</label>
      <input type="number" name="set_level" id="levelInput" required>

  <label for="genderSelect">Gender</label>
  <select id="genderSelect" name="set_gender" id="genderSelect" required>
    <option value="">-- Select Gender --</option>
    <option>Male</option>
    <option>Female</option>
    <option>Unknown</option>
  </select>

      <label for="alignmentSelect">Alignment:</label>
      <select name="set_alignment" id="alignmentSelect" required>
        <option value="1250">SAINTLY</option>
        <option value="1000">RIGHTEOUS</option>
        <option value="750">GOOD</option>
        <option value="500">BENEVOLENT</option>
        <option value="250">NICE</option>
        <option value="0" selected>NEUTRAL</option>
        <option value="-250">MEAN</option>
        <option value="-500">MALEVOLENT</option>
        <option value="-750">EVIL</option>
        <option value="-1000">NEFARIOUS</option>
        <option value="-1250">DEMONIC</option>
     </select>


<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
  <!-- Monster dropdown loader -->
  <div style="display: flex; align-items: center;">
    <label for="monsterSelect" style="margin-right: 0.5rem;">Load Monster:</label>
    <select id="monsterSelect" style="min-width: 200px;">
      <option value="">-- Select --</option>
    </select>
  </div>

  <!-- Buttons -->
  <div style="text-align: right;">
    <button type="submit">Add Monster</button>
    <button type="button" onclick="closeMonsterModal()">Cancel</button>
  </div>
</div>

<input type="hidden" id="monsterId" name="id" value="">
<button type="button" onclick="toggleInventory()">Manage Inventory</button>

<div id="inventorySection" style="display: none; margin-top: 1rem;">
  <h4>Monster Inventory</h4>
  <ul id="monsterInventoryList"></ul>

  <button type="button" onclick="openItemModal()">Add/Edit Items</button>
</div>



<div id="itemModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Edit Item</h3>
    <form id="itemForm" onsubmit="submitItemForm(event)">
      <input type="hidden" name="id" id="itemId">
      
      <label for="itemClass">Class</label>
      <input type="text" id="itemClass" name="class" required>

      <label for="itemShort">Short Description</label>
      <input type="text" id="itemShort" name="short">

      <label for="itemName">Internal Name</label>
      <input type="text" id="itemName" name="name">

      <label for="itemLongdesc">Long Description</label>
      <textarea id="itemLongdesc" name="longdesc"></textarea>

      <label for="itemLevel">Level</label>
      <input type="number" id="itemLevel" name="level" min="1">

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
  <!-- Item dropdown loader -->
  <div style="display: flex; align-items: center;">
    <label for="itemSelect" style="margin-right: 0.5rem;">Load Item:</label>
    <select id="itemSelect" style="min-width: 200px;">
      <option value="">-- Select --</option>
    </select>
  </div>

  <!-- Buttons -->
  <div style="text-align: right;">
    <button type="submit">Save Item</button>
    <button type="button" onclick="closeItemModal()">Cancel</button>
  </div>
</div>
    </form>
  </div>
</div>

    <script src="compromise.js"></script>
    <script src="examples.js"></script>
    <script src="script.js"></script>

</html>
