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
      $stmt = $pdo->prepare("INSERT INTO monsters (owner, set_class, set_race, set_gender, set_level, set_short, set_long) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([
        $username,
        $_POST['set_class'],
        $_POST['set_race'],
        $_POST['set_gender'],
        $_POST['set_level'],
        $_POST['set_short'],
        $_POST['set_long']
      ]);
    } elseif ($action === 'update' && isset($_POST['id'])) {
      $stmt = $pdo->prepare("UPDATE monsters SET set_class=?, set_race=?, set_gender=?, set_level=?, set_short=?, set_long=? WHERE id=? AND owner=?");
      $stmt->execute([
        $_POST['set_class'],
        $_POST['set_race'],
        $_POST['set_gender'],
        $_POST['set_level'],
        $_POST['set_short'],
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
$races = ['Arachnid', 'Artrell', 'Basilisk', 'Bat', 'Bee', 'Beetle', 'Beholder', 'Bird', 'Carrion-crawler', 'Centaur', 'Centipede', 'Chimera', 'Cockatrice', 'Crocodile', 'Crustacean', 'Dragon', 'Drider', 'Dwarf', 'Equine', 'Equine-winged', 'Faerie', 'Fish', 'Frog', 'Human', 'Halfling', 'Humanoid-tail', 'Insectoid', 'Insectoid-winged', 'Merperson', 'Orc', 'Octopus', 'Otyugh', 'Quadruped', 'Quadruped-winged', 'Ray', 'Reptile', 'Reptile-winged', 'Satyr', 'Slime', 'Snake-winged', 'Snakeman', 'Turtle', 'Worm'];
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

<script>
const examples = {
  Ranger: [
    { set_short: "lean ranger", set_long: "A sharp-eyed wanderer dressed in green and brown, moving silently through the forest.", set_level: 10, set_race: "Human", set_gender: "Male" },
    { set_short: "elven ranger", set_long: "An agile elf with keen senses and a quiver full of arrows, guardian of the wilds.", set_level: 12, set_race: "Elf", set_gender: "Female" }
  ],
  Druid: [
    { set_short: "rugged druid", set_long: "A wild-hearted spellcaster who communes with animals and the forces of nature.", set_level: 11, set_race: "Elf", set_gender: "Male" },
    { set_short: "serene druidess", set_long: "She commands the power of plants and animals with a calm and steady voice.", set_level: 13, set_race: "Human", set_gender: "Female" }
  ],
  Cleric: [
    { set_short: "stern cleric", set_long: "A devoted servant of the divine, healing allies and smiting foes with holy light.", set_level: 14, set_race: "Dwarf", set_gender: "Male" },
    { set_short: "compassionate cleric", set_long: "Her prayers bring warmth and protection to those in need, guided by faith.", set_level: 15, set_race: "Human", set_gender: "Female" }
  ],
  Warmage: [
    { set_short: "fiery mage", set_long: "A master of elemental fire, shaping flames to her will.", set_level: 16, set_race: "Human", set_gender: "Female" },
    { set_short: "mysterious mage", set_long: "He wields arcane power with subtlety, weaving illusions and curses.", set_level: 17, set_race: "Elf", set_gender: "Male" }
  ],
  Monk: [
    { set_short: "disciplined monk", set_long: "A serene fighter who channels inner energy into powerful strikes.", set_level: 13, set_race: "Human", set_gender: "Male" },
    { set_short: "swift monk", set_long: "She moves with grace and strikes with precision, embodying peace and strength.", set_level: 14, set_race: "Elf", set_gender: "Female" }
  ],
  Fighter: [
    { set_short: "fighter", set_long: "A veteran warrior known for his strength and unyielding will.", set_level: 15, set_race: "Human", set_gender: "Male" },
    { set_short: "fierce fighter", set_long: "She charges into battle with a roar, unstoppable and fearless.", set_level: 16, set_race: "Orc", set_gender: "Female" }
  ],
  Rogue: [
    { set_short: "shadowy rogue", set_long: "A stealthy thief who moves unseen and strikes without warning.", set_level: 12, set_race: "Halfling", set_gender: "Male" },
    { set_short: "cunning rogue", set_long: "She slips through the darkest alleys, her smile as sharp as her blades.", set_level: 13, set_race: "Human", set_gender: "Female" }
  ]
};

function fillExample() {
  const classType = document.getElementById('classSelect').value;
  const shortInput = document.getElementById('shortInput');
  const longdescInput = document.getElementById('longdescInput');
  const levelInput = document.getElementById('levelInput');
  const raceSelect = document.getElementById('raceSelect');
  const genderSelect = document.getElementById('genderSelect');

  if (examples[classType]) {
    const random = examples[classType][Math.floor(Math.random() * examples[classType].length)];

    shortInput.value = random.set_short || '';
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
