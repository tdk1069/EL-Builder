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
      $stmt = $pdo->prepare("INSERT INTO obj (owner, class, short, longdesc, level) VALUES (?, ?, ?, ?, ?)");
      $stmt->execute([$username, $_POST['class'], $_POST['short'], $_POST['longdesc'], $_POST['level']]);
    } elseif ($action === 'update' && isset($_POST['id'])) {
      $stmt = $pdo->prepare("UPDATE obj SET class=?, short=?, longdesc=? ,level=? WHERE id=? AND owner=?");
      $stmt->execute([$_POST['class'], $_POST['short'], $_POST['longdesc'], $_POST['level'], $_POST['id'], $username]);
    } elseif ($action === 'delete' && isset($_POST['id'])) {
      $stmt = $pdo->prepare("DELETE FROM obj WHERE id=? AND owner=?");
      $stmt->execute([$_POST['id'], $username]);
    }
  }
}

// Fetch all objects
$stmt = $pdo->prepare("SELECT * FROM obj WHERE owner=? ORDER BY class, id");
$stmt->execute([$username]);
$objects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Your Objects</title>
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
<?php include 'header.php'; ?>
<h1>Welcome, <?=htmlspecialchars($username)?> – Your Objects</h1>

<!-- Add New Object -->
<div class="form-row">
  <h2>Add New Object</h2>
  <form method="POST" id="objectForm">
    <input type="hidden" name="action" value="add">
    
    <label>Class:
      <select name="class" id="classSelect" required onchange="fillExample()">
        <option value="">-- Select --</option>
        <?php
        $classes = ['Blade','Blunt','Knife','Polearm','Projectile','Staff','Thrown','Two-Handed','Whip','Amulet','Cloak','Ring','Shield','Cloth Head','Cloth Torso','Cloth Hands','Cloth Feet','Leather Head','Leather Torso','Leather Hands','Leather Feet','Mail Head','Mail Torso','Mail Hands','Mail Feet','Plate Head','Plate Torso','Plate Hands','Plate Feet'];
        foreach ($classes as $class) {
          echo "<option value=\"$class\">$class</option>";
        }
        ?>
      </select>
    </label>

    <label>Level:
      <input type="number" name="level" id="levelInput" min="1" max="100" required>
    </label><br><br>

    <label>Short Description:
      <input type="text" name="short" id="shortInput" required>
    </label><br><br>

    <label>Long Description:
      <textarea name="longdesc" id="longdescInput" rows="4" required></textarea>
    </label><br><br>

    <button class="btn" type="submit">Add Object</button>
  </form>
</div>

<!-- List of Objects -->
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Class</th>
      <th>Short</th>
      <th>Long</th>
      <th>Level</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($objects as $obj): ?>
    <tr>
      <form method="POST">
        <input type="hidden" name="id" value="<?=$obj->id?>">
        <input type="hidden" name="action" value="update">
        <td><?= $obj->id ?></td>
        <td>
          <select name="class">
            <?php foreach ($classes as $class): ?>
              <option value="<?= $class ?>" <?= $obj->class === $class ? 'selected' : '' ?>><?= $class ?></option>
            <?php endforeach; ?>
          </select>
        </td>
        <td><input type="text" name="short" value="<?= htmlspecialchars($obj->short) ?>"></td>
        <td><textarea name="longdesc" rows="2"><?= htmlspecialchars($obj->longdesc) ?></textarea></td>
        <td><input type="number" name="level" value="<?= htmlspecialchars($obj->level) ?>"></td>
        <td>
          <button class="btn" type="submit">Update</button>
      </form>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="id" value="<?=$obj->id?>">
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
  Blade: [
    { short: "a gleaming longsword", long: "This longsword has a finely honed edge and a jewel-encrusted hilt.", level: 10 },
    { short: "a curved scimitar", long: "The blade of this scimitar glints wickedly in the light.", level: 7 }
  ],
  Blunt: [
    { short: "a heavy iron mace", long: "The mace has a spiked head and a thick shaft perfect for crushing.", level: 9 },
    { short: "a polished warhammer", long: "The warhammer's head gleams with recent use, its balance nearly perfect.", level: 12 }
  ],
  Knife: [
    { short: "a bone-handled dagger", long: "This dagger is small but deadly, with a bone grip and silver blade.", level: 5 },
    { short: "a serrated dirk", long: "A short stabbing weapon with jagged edges ideal for close encounters.", level: 6 }
  ],
  Polearm: [
    { short: "a steel-tipped halberd", long: "This halberd combines an axe blade with a sharp spear tip.", level: 11 },
    { short: "a long wooden spear", long: "A sturdy shaft topped with a leaf-shaped metal tip.", level: 6 }
  ],
  Projectile: [
    { short: "a yew shortbow", long: "Crafted from seasoned yew, this shortbow is light and fast.", level: 5 },
    { short: "a heavy crossbow", long: "This crossbow packs a punch and is reinforced with iron.", level: 10 }
  ],
  Staff: [
    { short: "an oak wizard's staff", long: "A runed staff topped with a crystal that glows faintly.", level: 8 },
    { short: "a charred wooden staff", long: "Scorch marks run along its shaft, hinting at fire magic.", level: 6 }
  ],
  Thrown: [
    { short: "a throwing axe", long: "Small and well-balanced for long-distance combat.", level: 4 },
    { short: "a set of steel throwing knives", long: "Slim blades designed for silent takedowns.", level: 5 }
  ],
  "Two-Handed": [
    { short: "a massive claymore", long: "This two-handed sword requires immense strength to wield.", level: 13 },
    { short: "a brutal great axe", long: "Double-bladed and fearsome, it cleaves through armor with ease.", level: 12 }
  ],
  Whip: [
    { short: "a barbed leather whip", long: "Studded with small hooks for extra sting.", level: 7 },
    { short: "a snakebone lash", long: "This whip is crafted from vertebrae and crackles with energy.", level: 9 }
  ],
  Amulet: [
    { short: "an amulet of protection", long: "This silver charm glows faintly and wards off harm.", level: 5 },
    { short: "a sapphire pendant", long: "A deep-blue gem hangs from a golden chain, calming the mind.", level: 7 }
  ],
  Cloak: [
    { short: "a midnight cloak", long: "Dark as night, this cloak helps the wearer fade from view.", level: 6 },
    { short: "a weathered traveler's cape", long: "Stitched with many repairs, it has clearly seen long journeys.", level: 3 }
  ],
  Ring: [
    { short: "a ring of agility", long: "This slim band improves the wearer’s reflexes.", level: 6 },
    { short: "a fire-etched ring", long: "Warm to the touch, this ring channels inner flame.", level: 8 }
  ],
  Shield: [
    { short: "an iron buckler", long: "Small and light, ideal for quick parries.", level: 5 },
    { short: "a tower shield", long: "This massive shield covers the user from shoulder to shin.", level: 12 }
  ],
  "Cloth Head": [
    { short: "a tattered wizard hat", long: "A pointed hat sagging at the tip, patched with stars.", level: 2 },
    { short: "a silk skullcap", long: "Smooth and embroidered with tiny golden runes.", level: 4 }
  ],
  "Cloth Torso": [
    { short: "a novice's robe", long: "A simple robe with a rope belt and no flair.", level: 1 },
    { short: "a mystic's vestments", long: "Robes dyed in deep purple, decorated with arcane symbols.", level: 7 }
  ],
  "Cloth Hands": [
    { short: "a pair of linen gloves", long: "Thin gloves for dexterous fingerwork, not battle.", level: 2 },
    { short: "embroidered mage gloves", long: "These gloves shimmer faintly and crackle with latent magic.", level: 5 }
  ],
  "Cloth Feet": [
    { short: "a pair of soft slippers", long: "Comfortable and nearly silent on stone floors.", level: 1 },
    { short: "glowing thread boots", long: "These fine boots hum with residual energy.", level: 6 }
  ],
  "Leather Head": [
    { short: "a leather hood", long: "Dark and snug, perfect for a rogue.", level: 3 },
    { short: "a reinforced leather helm", long: "Provides light protection without sacrificing mobility.", level: 5 }
  ],
  "Leather Torso": [
    { short: "a studded leather jerkin", long: "Reinforced with rivets, it's lightweight but tough.", level: 6 },
    { short: "a hunter’s vest", long: "Smells faintly of the forest, adorned with fur trims.", level: 4 }
  ],
  "Leather Hands": [
    { short: "archer’s gloves", long: "These gloves are flexible with padded palms.", level: 4 },
    { short: "spiked leather gloves", long: "The knuckles are reinforced with short spikes.", level: 7 }
  ],
  "Leather Feet": [
    { short: "a pair of ranger boots", long: "Perfect for hiking quietly through brush and grass.", level: 4 },
    { short: "shadowstep boots", long: "Each step feels lighter, quieter, quicker.", level: 7 }
  ],
  "Mail Head": [
    { short: "a chainmail coif", long: "Woven metal rings protect the head and neck.", level: 6 },
    { short: "a rusted mail helm", long: "Old but sturdy, passed down through generations.", level: 5 }
  ],
  "Mail Torso": [
    { short: "a chainmail vest", long: "Flexible torso protection of fine dwarven links.", level: 7 },
    { short: "an engraved mail shirt", long: "Each ring is etched with a protective rune.", level: 9 }
  ],
  "Mail Hands": [
    { short: "mail-backed gloves", long: "Padded leather gloves reinforced with chain links.", level: 6 },
    { short: "gauntlets of interwoven mail", long: "Crafted for grip and defense alike.", level: 8 }
  ],
  "Mail Feet": [
    { short: "heavy mail boots", long: "Clank with every step but protect like stone.", level: 8 },
    { short: "scaled greaves", long: "Layered with fish-scale metal for extra defense.", level: 9 }
  ],
  "Plate Head": [
    { short: "a steel greathelm", long: "Completely encases the head in iron and intimidation.", level: 10 },
    { short: "a crested helm", long: "Decorative yet battle-ready, with a crimson plume.", level: 11 }
  ],
  "Plate Torso": [
    { short: "a paladin's breastplate", long: "Holy symbols are etched into this radiant armor.", level: 12 },
    { short: "a blackened cuirass", long: "Worn by dark knights, it absorbs the light around it.", level: 13 }
  ],
  "Plate Hands": [
    { short: "spiked gauntlets", long: "Formidable metal gloves that double as weapons.", level: 11 },
    { short: "engraved plate gloves", long: "Covered in swirling patterns and battle-tested dents.", level: 10 }
  ],
  "Plate Feet": [
    { short: "iron sabatons", long: "Boots that thunder with every step.", level: 10 },
    { short: "gilded footguards", long: "Elegant and deadly, these plate boots gleam with pride.", level: 12 }
  ]
};

function fillExample() {
  const classType = document.getElementById('classSelect').value;
  const shortInput = document.getElementById('shortInput');
  const longdescInput = document.getElementById('longdescInput');
  const levelInput = document.getElementById('levelInput');

  if (examples[classType]) {
    const random = examples[classType][Math.floor(Math.random() * examples[classType].length)];
    shortInput.value = random.short;
    longdescInput.value = random.long;
    levelInput.value = random.level || '';
  } else {
    shortInput.value = '';
    longdescInput.value = '';
    levelInput.value = '';
  }
}
</script>

</body>
</html>
