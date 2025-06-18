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
      $stmt = $pdo->prepare("INSERT INTO monsters (owner, set_class, set_race, set_gender, set_level, set_short, set_name, set_long) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([
        $username,
        $_POST['set_class'],
        $_POST['set_race'],
        $_POST['set_gender'],
        $_POST['set_level'],
        $_POST['set_short'],
        $_POST['set_name'],
        $_POST['set_long']
      ]);
    } elseif ($action === 'update' && isset($_POST['id'])) {
      $stmt = $pdo->prepare("UPDATE monsters SET set_class=?, set_race=?, set_gender=?, set_level=?, set_short=?, set_name=?, set_long=? WHERE id=? AND owner=?");
      $stmt->execute([
        $_POST['set_class'],
        $_POST['set_race'],
        $_POST['set_gender'],
        $_POST['set_level'],
        $_POST['set_short'],
        $_POST['set_name'],
        $_POST['set_long'],
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
  <!-- Fancy font -->
  <link href="https://fonts.googleapis.com/css2?family=UnifrakturCook:wght@700&display=swap" rel="stylesheet">
<style>
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
    margin-top: 20px;
    background-color: rgba(255, 248, 220, 0.95);
    border: 2px solid #7a5c3e;
    border-radius: 10px;
    box-shadow: 3px 3px 8px rgba(0,0,0,0.3);
  }

  th, td {
    padding: 10px;
    border: 1px solid #bfa77f;
    text-align: left;
  }

  th {
    background-color: #d8c792;
    color: #3e2f1c;
    font-weight: bold;
  }

  input, textarea, select {
    background-color: #fdf6e3;
    color: #3a2f1b;
    border: 1px solid #d8c792;
    padding: 6px;
    width: 100%;
    font-family: 'Georgia', serif;
  }

  .form-row {
    margin-top: 20px;
    background-color: rgba(255, 248, 220, 0.95);
    border: 2px solid #7a5c3e;
    padding: 1rem;
    border-radius: 12px;
    box-shadow: 3px 3px 8px rgba(0,0,0,0.3);
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
    box-shadow: 2px 2px 5px rgba(0,0,0,0.2);
  }

  .btn:hover {
    background-color: #5a3e2b;
  }
</style>
</head>
<body>

<h1>Welcome, <?=htmlspecialchars($username)?> – Your Monsters</h1>

<!-- Add New Monster -->
<div class="form-row">
  <h2>Add New Monster</h2>
  <form method="POST" id="monsterForm">
    <input type="hidden" name="action" value="add">

    <label>Class:
      <select name="set_class" id="classSelect" required onchange="fillExample()">
        <option value="">-- Select --</option>
        <?php
        foreach ($classes as $class) {
          echo "<option value=\"$class\">$class</option>";
        }
        ?>
      </select>
    </label>

    <label>Race:
      <select name="set_race" id="raceSelect" required>
        <option value="">-- Select --</option>
        <?php
        foreach ($races as $race) {
          echo "<option value=\"$race\">$race</option>";
        }
        ?>
      </select>
    </label>

    <label>Gender:
      <select name="set_gender" id="genderSelect" required>
        <option value="">-- Select --</option>
        <?php
        foreach ($genders as $gender) {
          echo "<option value=\"$gender\">$gender</option>";
        }
        ?>
      </select>
    </label>

    <label>Level:
      <input type="number" name="set_level" id="levelInput" min="1" max="100" required>
    </label><br><br>

    <label>Short Description:
      <input type="text" name="set_short" id="shortInput" required>
    </label><br><br>

    <label>Name:
      <input type="text" name="set_name" id="nameInput" required>
    </label><br><br>

    <label>Long Description:
      <textarea name="set_long" id="longdescInput" rows="4" required></textarea>
    </label><br><br>

    <button class="btn" type="submit">Add Monster</button>
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
      <th>Long</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($monsters as $monster): ?>
    <tr>
      <form method="POST">
        <input type="hidden" name="id" value="<?=$monster->id?>">
        <input type="hidden" name="action" value="update">

        <td><?= $monster->id ?></td>

        <td>
          <select name="set_class">
            <?php foreach ($classes as $class): ?>
              <option value="<?= $class ?>" <?= $monster->set_class === $class ? 'selected' : '' ?>><?= $class ?></option>
            <?php endforeach; ?>
          </select>
        </td>

        <td>
          <select name="set_race">
            <?php foreach ($races as $race): ?>
              <option value="<?= $race ?>" <?= $monster->set_race === $race ? 'selected' : '' ?>><?= $race ?></option>
            <?php endforeach; ?>
          </select>
        </td>

        <td>
          <select name="set_gender">
            <?php foreach ($genders as $gender): ?>
              <option value="<?= $gender ?>" <?= $monster->set_gender === $gender ? 'selected' : '' ?>><?= $gender ?></option>
            <?php endforeach; ?>
          </select>
        </td>

        <td><input type="number" name="set_level" value="<?= htmlspecialchars($monster->set_level) ?>"></td>

        <td><input type="text" name="set_short" value="<?= htmlspecialchars($monster->set_short) ?>"></td>

        <td><input type="text" name="set_name" value="<?= htmlspecialchars($monster->set_name) ?>"></td>

        <td><textarea name="set_long" rows="2"><?= htmlspecialchars($monster->set_long) ?></textarea></td>

        <td>
          <button class="btn" type="submit">Update</button>
      </form>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="id" value="<?=$monster->id?>">
        <input type="hidden" name="action" value="delete">
        <button class="btn" type="submit" style="background:#c33;">Delete</button>
      </form>
        </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <script src="examples.js"></script>
<script>

function fillExample() {
  const classType = document.getElementById('classSelect').value;
  const shortInput = document.getElementById('shortInput');
  const longdescInput = document.getElementById('longdescInput');
  const levelInput = document.getElementById('levelInput');
  const raceSelect = document.getElementById('raceSelect');
  const genderSelect = document.getElementById('genderSelect');

  if (exampleMonsters[classType]) {
    const random = exampleMonsters[classType][Math.floor(Math.random() * exampleMonsters[classType].length)];

    shortInput.value = random.set_short || '';
    nameInput.value = random.set_name || '';
    longdescInput.value = random.set_long || '';
    levelInput.value = random.set_level || '';
    raceSelect.value = random.set_race || '';
    genderSelect.value = random.set_gender || '';
  } else {
    shortInput.value = '';
    longdescInput.value = '';
    levelInput.value = '';
    raceSelect.value = '';
    genderSelect.value = '';
  }
}
</script>

</body>
</html>
