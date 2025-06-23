<?php
require 'auth/db.php'; // Adjust if needed
session_start();

if (!isset($_SESSION['username'])) {
  die("Not logged in.");
}

$username = $_SESSION['username'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
      $stmt = $pdo->prepare("INSERT INTO monsters (owner, set_class, set_race, set_gender, set_level, set_short, set_spells, set_name, set_alignment, set_long) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([
        $username,
        $_POST['set_class'],
        $_POST['set_race'],
        $_POST['set_gender'],
        $_POST['set_level'],
        $_POST['set_short'],
        $_POST['set_spells'],
        $_POST['set_name'],
        $_POST['set_alignment'],
        $_POST['set_long']
      ]);
    } elseif ($action === 'update' && isset($_POST['id'])) {
      $stmt = $pdo->prepare("UPDATE monsters SET set_class=?, set_race=?, set_gender=?, set_level=?, set_short=?, set_spells=?, set_name=?, set_long=? , set_alignment=? WHERE id=? AND owner=?");
      $stmt->execute([
        $_POST['set_class'],
        $_POST['set_race'],
        $_POST['set_gender'],
        $_POST['set_level'],
        $_POST['set_short'],
        $_POST['set_spells'],
        $_POST['set_name'],
        $_POST['set_long'],
        $_POST['set_alignment'],
        $_POST['id'],
        $username
      ]);
    } elseif ($action === 'delete' && isset($_POST['id'])) {
      $stmt = $pdo->prepare("DELETE FROM monsters WHERE id=? AND owner=?");
      $stmt->execute([$_POST['id'], $username]);
    }
  }
}

// Fetch all monsters
$stmt = $pdo->prepare("SELECT * FROM monsters WHERE owner=? ORDER BY set_class, id");
$stmt->execute([$username]);
$monsters = $stmt->fetchAll();

// Options for select inputs
$classes = ['Fighter', 'Warmage', 'Rogue', 'Cleric', 'Monk', 'Druid', 'Ranger'];
$races = ['Arachnid', 'Artrell', 'Basilisk', 'Bat', 'Bee', 'Beetle', 'Beholder', 'Bird', 'Carrion-crawler', 'Centaur', 'Centipede', 'Chimera', 'Cockatrice', 'Crocodile', 'Crustacean', 'Dragon', 'Drider', 'Dwarf', 'Elf', 'Equine', 'Equine-winged', 'Faerie', 'Fish', 'Frog', 'Human', 'Halfling', 'Humanoid-tail', 'Insectoid', 'Insectoid-winged', 'Merperson', 'Orc', 'Octopus', 'Otyugh', 'Quadruped', 'Quadruped-winged', 'Ray', 'Reptile', 'Reptile-winged', 'Satyr', 'Slime', 'Snake-winged', 'Snakeman', 'Turtle', 'Worm'];
$genders = ['Male', 'Female', 'Unknown'];

?>

<!DOCTYPE html>
<html>

<head>
  <?php include 'header.php'; ?>
  <title>Your Monsters</title>
  <link rel="icon" href="/assets/background.svg" type="image/svg+xml">
  <!-- Fancy font -->
  <link href="https://fonts.googleapis.com/css2?family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">
  <style>
    .modal-overlay {
      position: fixed;
      inset: 0;
      background-color: rgba(0, 0, 0, 0.6);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }

    .modal-content {
      background: #fff;
      padding: 2rem;
      border-radius: 0.5rem;
      max-width: 400px;
      width: 100%;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
      text-align: center;
    }

    .hidden {
      display: none;
    }

    body {
      background: url('assets/background.svg') no-repeat center center;
      background-size: cover;
      font-family: 'Georgia', serif;
      color: #3e2f1c;
      padding: 0px;
    }

    h1 {
      font-family: 'UnifrakturCook', cursive;
      text-align: center;
      color: #3e2f1c;
      margin-top: 2rem;
      margin-bottom: 1.5rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin-top: 20px;
      background-color: rgba(255, 248, 220, 0.95);
      border: 2px solid #7a5c3e;
      border-radius: 10px;
      box-shadow: 3px 3px 8px rgba(0, 0, 0, 0.3);
    }

    th,
    td {
      padding: 10px;
      border: 1px solid #bfa77f;
      text-align: left;
    }

    th {
      background-color: #d8c792;
      color: #3e2f1c;
      font-weight: bold;
    }

    input,
    textarea,
    select {
      background-color: #fdf6e3;
      color: #3a2f1b;
      border: 1px solid #d8c792;
      padding: 6px;
      width: 100%;
      font-family: 'Georgia', serif;
    }
td input,
td select,
td textarea {
  width: 100%;
  box-sizing: border-box;
  max-width: 100%;
}

    .form-row {
      margin-top: 20px;
      background-color: rgba(255, 248, 220, 0.95);
      border: 2px solid #7a5c3e;
      padding: 1rem;
      border-radius: 12px;
      box-shadow: 3px 3px 8px rgba(0, 0, 0, 0.3);
    }

    .form-row h2 {
      margin-top: 0;
      font-family: 'UnifrakturCook', cursive;
    }

    .btn {
      background-color: #7a5c3e;
      border: none;
      color: #fff8e7;
      padding: 6px 12px;
      cursor: pointer;
      font-family: 'Georgia', serif;
      margin-right: 6px;
      border-radius: 6px;
      box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
    }

    .btn:hover {
      background-color: #5a3e2b;
    }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      align-items: start;
    }

    .form-grid label {
      display: block;
    }

    .full-width {
      grid-column: 1 / -1;
    }
#alignmentSlider {
  width: 300px;
  margin: 0 1em;
}
#alignmentLabel {
  font-weight: bold;
  padding-left: 0.5em;
}

  </style>
</head>

<body>

  <h1>Welcome, <?= htmlspecialchars($username) ?> – Your Monsters</h1>

  <!-- Add New Monster -->
<div class="form-row">
  <h2>Add New Monster</h2>
  <form method="POST" id="monsterForm" data-mode="add">
    <input type="hidden" name="action" id="formAction" value="add">
    <input type="hidden" name="id" id="monsterId" value="">

    <div class="form-grid">
      <label>Class:
        <select name="set_class" id="classSelect" required onchange="fillExample()">
          <option value="">-- Select --</option>
          <?php foreach ($classes as $class): ?>
            <option value="<?= $class ?>"><?= $class ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Race:
        <select name="set_race" id="raceSelect" required>
          <option value="">-- Select --</option>
          <?php foreach ($races as $race): ?>
            <option value="<?= $race ?>"><?= $race ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Gender:
        <select name="set_gender" id="genderSelect" required>
          <option value="">-- Select --</option>
          <?php foreach ($genders as $gender): ?>
            <option value="<?= $gender ?>"><?= $gender ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Level:
        <input type="number" name="set_level" id="levelInput" min="1" max="100" required>
      </label>

      <label>Short Description:
        <input type="text" name="set_short" id="shortInput" required>
      </label>

      <label>Name:
        <input type="text" name="set_name" id="nameInput" required>
      </label>

      <label>Skills/Spells (comma separated):
        <input type="text" name="set_spells" id="spellsInput">
      </label>

      <label for="alignmentSlider">Alignment:
        <input type="range" id="alignmentSlider" name="set_alignment" min="-1250" max="1250" step="50" value="0" oninput="updateAlignmentLabel(this.value)">
        <span id="alignmentLabel">NEUTRAL</span>
      </label>

      <label class="full-width">Long Description:
        <textarea name="set_long" id="longdescInput" rows="3" required></textarea>
      </label>

      <div class="full-width">
        <button class="btn" type="submit" id="submitButton">Add Monster</button>
      </div>
    </div>
  </form>
</div>


  <!-- List of Monsters -->
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Class</th>
        <th>Race</th>
        <th>Gender</th>
        <th>Level</th>
        <th>Short</th>
        <th>Name</th>
        <th>Long</th>
        <th>Spells/Skills</th>
        <th>Alignment</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($monsters as $monster): ?>
        <tr>
          <form method="POST">
            <input type="hidden" name="id" value="<?= $monster->id ?>">
            <input type="hidden" name="action" value="update">

            <td><?= $monster->id ?></td>

            <td>
              <select name="set_class" disabled>
                <?php foreach ($classes as $class): ?>
                  <option value="<?= $class ?>" <?= $monster->set_class === $class ? 'selected' : '' ?>><?= $class ?></option>
                <?php endforeach; ?>
              </select>
            </td>

            <td>
              <select name="set_race" disabled>
                <?php foreach ($races as $race): ?>
                  <option value="<?= $race ?>" <?= $monster->set_race === $race ? 'selected' : '' ?>><?= $race ?></option>
                <?php endforeach; ?>
              </select>
            </td>

            <td>
              <select name="set_gender" disabled>
                <?php foreach ($genders as $gender): ?>
                  <option value="<?= $gender ?>" <?= $monster->set_gender === $gender ? 'selected' : '' ?>><?= $gender ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>

            <td><input type="number" name="set_level" value="<?= htmlspecialchars($monster->set_level) ?>" disabled></td>

            <td><input type="text" name="set_short" value="<?= htmlspecialchars($monster->set_short) ?>" disabled></td>

            <td><input type="text" name="set_name" value="<?= htmlspecialchars($monster->set_name) ?>" disabled></td>

            <td><textarea name="set_long" rows="2" disabled><?= htmlspecialchars($monster->set_long) ?></textarea></td>

            <td><input type="text" name="set_spells" value="<?= htmlspecialchars($monster->set_spells) ?>" disabled></td>

            <td><input type="text" name="set_alignment" value="<?= htmlspecialchars($monster->set_alignment) ?>" disabled></td>

            <td>
            <button class="btn" type="button"
              onclick='editMonster(<?= json_encode($monster, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
              Edit
            </button>

          </form>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="id" value="<?= $monster->id ?>">
            <input type="hidden" name="action" value="delete">
            <button class="btn" type="submit" style="background:#c33;">Delete</button>
          </form>
          <!-- New Items button -->
          <button type="button" class="btn" onclick="openItemModal(<?= $monster->id ?>)">Items</button>
          </td>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Monster Item Modal -->
  <div id="itemModal" class="modal-overlay hidden">
    <div class="modal-content">
      <h2>Monster Items</h2>

      <select id="itemSelector">
        <option value="">-- Select item to add --</option>
      </select>
      <button onclick="addItemToMonster()">Add</button>

      <ul id="selectedItemsList"></ul>

      <button onclick="saveMonsterItems()">Save</button>
      <button onclick="closeItemModal()">Close</button>
    </div>
  </div>

  <script src="examples.js"></script>
  <script>
    let availableItems = [];
    let selectedItems = [];
    let currentMonsterId = null;

    async function openItemModal(monsterId) {
      currentMonsterId = monsterId;
      document.getElementById('itemModal').classList.remove('hidden');

      // Load available items (objects)
      const res = await fetch('/get_objects.php');
      availableItems = await res.json();

      // Populate dropdown
      const selector = document.getElementById('itemSelector');
      selector.innerHTML = '<option value="">-- Select item to add --</option>';
      availableItems.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = `${item.short} (Lvl ${item.level})`;
        selector.appendChild(opt);
      });

      // Load assigned items for this monster
      const assignedRes = await fetch(`/monster_items.php?monster_id=${monsterId}`);
      const assignedIds = await assignedRes.json();

      // Pre-populate selectedItems by matching IDs with availableItems
      selectedItems = availableItems.filter(item => assignedIds.includes(item.id));

      updateSelectedItemsUI();
    }

    function addItemToMonster() {
      const selector = document.getElementById('itemSelector');
      const selectedId = parseInt(selector.value);
      if (!selectedId) return;

      if (selectedItems.find(i => i.id === selectedId)) return; // already added

      const item = availableItems.find(i => i.id === selectedId);
      if (!item) return;

      selectedItems.push(item);
      updateSelectedItemsUI();
    }

    function removeItem(itemId) {
      selectedItems = selectedItems.filter(i => i.id !== itemId);
      updateSelectedItemsUI();
    }

    function updateSelectedItemsUI() {
      const list = document.getElementById('selectedItemsList');
      list.innerHTML = '';

      selectedItems.forEach(item => {
        const li = document.createElement('li');
        li.textContent = `${item.short} (Lvl ${item.level})`;

        const btn = document.createElement('button');
        btn.textContent = '-';
        btn.style.marginLeft = '1em';
        btn.onclick = () => removeItem(item.id);

        li.appendChild(btn);
        list.appendChild(li);
      });
    }

    async function saveMonsterItems() {
      if (!currentMonsterId) return;

      const idsToSave = selectedItems.map(i => i.id);

      try {
        const res = await fetch('/monster_items.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            monster_id: currentMonsterId,
            items: idsToSave
          })
        });

        const json = await res.json();

        if (json.success) {
          console.log('Items saved successfully!');
          closeItemModal();
        } else {
          console.log('Error saving items: ' + (json.error || 'unknown error'));
        }
      } catch (e) {
        console.log('Request failed: ' + e.message);
      }
    }

    function closeItemModal() {
      document.getElementById('itemModal').classList.add('hidden');
    }

    function fillExample() {
      const classType = document.getElementById('classSelect').value;
      const shortInput = document.getElementById('shortInput');
      const nameInput = document.getElementById('nameInput');
      const longdescInput = document.getElementById('longdescInput');
      const levelInput = document.getElementById('levelInput');
      const raceSelect = document.getElementById('raceSelect');
      const genderSelect = document.getElementById('genderSelect');

      if (exampleMonsters[classType]) {
        const random = exampleMonsters[classType][Math.floor(Math.random() * exampleMonsters[classType].length)];

        shortInput.value = random.set_short || '';
        spellsInput.value = random.set_spells || '';
        nameInput.value = random.set_short || '';
        longdescInput.value = random.set_long || '';
        levelInput.value = random.set_level || '';
        raceSelect.value = random.set_race || '';
        genderSelect.value = random.set_gender || '';
        alignmentSlider.value = random.set_alignment || '';
      } else {
        shortInput.value = '';
        spellsInput.value = '';
        nameInput.value = '';
        longdescInput.value = '';
        levelInput.value = '';
        raceSelect.value = '';
        genderSelect.value = '';
        alignmentSlider.value = '';
      }
    }

    function editMonster(monster) {
      const form = document.getElementById("monsterForm");
      form.dataset.mode = "update";
      document.getElementById("formAction").value = "update";
      document.getElementById("submitButton").textContent = "Update Monster";
      document.getElementById("monsterId").value = monster.id;

      document.getElementById("classSelect").value = monster.set_class;
      document.getElementById("raceSelect").value = monster.set_race;
      document.getElementById("genderSelect").value = monster.set_gender;
      document.getElementById("levelInput").value = monster.set_level;
      document.getElementById("shortInput").value = monster.set_short;
      document.getElementById("nameInput").value = monster.set_name;
      document.getElementById("longdescInput").value = monster.set_long;
      document.getElementById("spellsInput").value = monster.set_spells;
      document.getElementById("alignmentSlider").value = monster.set_alignment;
    }

    function updateAlignmentLabel(value) {
      const val = parseInt(value);
      const label = document.getElementById("alignmentLabel");

      if (val >= 1125) label.textContent = "SAINTLY";
      else if (val >= 875) label.textContent = "RIGHTEOUS";
      else if (val >= 625) label.textContent = "GOOD";
      else if (val >= 375) label.textContent = "BENEVOLENT";
      else if (val >= 125) label.textContent = "NICE";
      else if (val > -125) label.textContent = "NEUTRAL";
      else if (val >= -375) label.textContent = "MEAN";
      else if (val >= -625) label.textContent = "MALEVOLENT";
      else if (val >= -875) label.textContent = "EVIL";
      else if (val >= -1125) label.textContent = "NEFARIOUS";
      else label.textContent = "DEMONIC";
    }

</script>

</body>

</html>
