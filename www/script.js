const canvas = document.getElementById("mapCanvas");
const ctx = canvas.getContext("2d");

const TILE_SIZE = 40;
const ROOM_SPACING = 60;
const GRID_SIZE = 25;

let grid = {};
let currentX = 0;
let currentY = 0;
let currentZ = 0;

let squareMap = {}; // key: "x,y,z", value: {x: px, y: py, w: TILE_SIZE, h: TILE_SIZE}

const roomDescriptions = [
  {
    short: "A dusty old chamber",
    long: "Cobwebs hang from the ceiling, and the floor is strewn with broken tiles.",
  },
  {
    short: "A brightly lit hall",
    long: "Torches flicker along the walls, casting dancing shadows in every corner.",
  },
  {
    short: "A shadowy corridor",
    long: "The air is thick with dust and the faint scent of mildew.",
  },
  {
    short: "A moss-covered stone room",
    long: "Cracks run along the walls, hinting at the age of this forgotten place.",
  },
  {
    short: "A nondescript room",
    long: "This is a plain, empty room with nothing particularly remarkable about it.",
  },
  {
    short: "A circular chamber",
    long: "Smooth stone walls form a perfect circle, echoing even the softest footstep.",
  },
  {
    short: "A flooded passage",
    long: "Cold water laps at your boots as you wade through the shallow passage.",
  },
  {
    short: "A scorched tunnel",
    long: "The walls are blackened with soot, and a faint scent of sulfur hangs in the air.",
  },
  {
    short: "A frozen grotto",
    long: "Glittering icicles hang from above, and frost crunches underfoot.",
  },
  {
    short: "A jungle overhang",
    long: "Thick vines dangle from the ceiling, and the call of unseen creatures echoes around.",
  },
  {
    short: "An ancient library",
    long: "Dusty tomes line the shelves, and the air is thick with the scent of old parchment.",
  },
  {
    short: "A crumbling staircase",
    long: "The stone steps crack underfoot, threatening to give way with each step.",
  },
  {
    short: "A bloodstained ritual chamber",
    long: "Dark stains mar the floor, and strange symbols are scrawled in flaking red pigment.",
  },
  {
    short: "A forgotten armory",
    long: "Rusty weapons and shattered shields lie scattered across the room.",
  },
  {
    short: "A vaulted cathedral",
    long: "Stained glass windows throw colored light across the cracked marble floor.",
  },
  {
    short: "A narrow mine shaft",
    long: "Wooden supports groan under pressure, and a pickaxe lies abandoned nearby.",
  },
  {
    short: "A sandy cavern",
    long: "Loose sand shifts beneath your feet, whispering with every movement.",
  },
  {
    short: "A misty glade",
    long: "Thin mist curls around tree trunks, and the scent of damp moss fills the air.",
  },
  {
    short: "A spider-infested nest",
    long: "Thick webs cover the walls, and skittering legs echo in the darkness.",
  },
  {
    short: "A broken bridge crossing",
    long: "A shattered stone bridge ends abruptly over a dark chasm.",
  },
  {
    short: "A glowing mushroom cave",
    long: "Bioluminescent fungi cast an eerie blue glow throughout the chamber.",
  },
  {
    short: "A hidden alcove",
    long: "Barely noticeable, this small recess in the wall holds secrets lost to time.",
  },
  {
    short: "A buried tomb",
    long: "Sarcophagi rest against the far wall, undisturbed for centuries—until now.",
  },
  {
    short: "A windblown terrace",
    long: "Dust and small stones whip across the open platform with each gust.",
  },
  {
    short: "A cave of echoes",
    long: "Every sound you make is returned to you a second later, amplified and distorted.",
  },
  {
    short: "A root-choked tunnel",
    long: "Thick roots burst through the walls and ceiling, their gnarled limbs reaching out.",
  },
  {
    short: "A torchlit passage",
    long: "The flickering flames offer some comfort, but the darkness ahead is foreboding.",
  },
  {
    short: "A deserted campsite",
    long: "Ashes and torn fabric mark the site of a hurried departure.",
  },
  {
    short: "A steep mountain ledge",
    long: "One misstep could send you tumbling into the foggy abyss below.",
  },
  {
    short: "A crystalline hall",
    long: "Gleaming crystals jut from every surface, casting rainbows across the floor.",
  },
  {
    short: "A sulfurous pit",
    long: "Yellow fumes rise from cracks in the ground, and the stench burns your nostrils.",
  },
  {
    short: "A twisted hallway",
    long: "The walls are uneven and warped, as if reality here is slightly bent.",
  },
  {
    short: "A scorched battlefield",
    long: "Charred bones and shattered weapons speak of a battle long past.",
  },
  {
    short: "A dripping stone room",
    long: "Water drips steadily from the ceiling, collecting in shallow puddles on the floor.",
  },
  {
    short: "A grand dining hall",
    long: "A long table sits abandoned, silverware still in place beneath a thick layer of dust.",
  },
  {
    short: "A rotting cellar",
    long: "The scent of mold and rot clings to the damp, timber-lined walls.",
  },
  {
    short: "A glowing runestone chamber",
    long: "Pulsing runes light up the walls, responding faintly to your presence.",
  },
  {
    short: "A silent crypt",
    long: "The air is perfectly still, and the silence is oppressive.",
  },
  {
    short: "A moonlit garden",
    long: "Wildflowers bloom beneath silver moonlight, untouched by time.",
  },
  {
    short: "A stone bridge over a dark void",
    long: "The bridge creaks underfoot, suspended above an endless abyss.",
  },
];

function getRandomRoomDescription() {
  return roomDescriptions[Math.floor(Math.random() * roomDescriptions.length)];
}

// Initialize starting room with default content
const startKey = key(currentX, currentY, currentZ);
grid[startKey] = {
  exits: {},
  set_short: "The beginning",
  set_long: "You stand at the starting point of a new adventure.",
};

function key(x, y, z = 0) {
  return `${x},${y},${z}`;
}

function saveRoom() {
  const room = {
    set_short: document.getElementById("set_short").value,
    set_long: document.getElementById("set_long").value,
    set_smell: document.getElementById("set_smell").value,
    set_items: getItems(),
    exits: grid[key(currentX, currentY, currentZ)]?.exits || {},
    monsters: getMonsters(),
    objects: getObjects(),
  };
  grid[key(currentX, currentY, currentZ)] = room;
  drawMap();
}

function move(dir) {
  // Save current room before moving
  saveRoom();

  const dx = {
    west: -1,
    east: 1,
    north: 0,
    south: 0,
    northeast: 1,
    northwest: -1,
    southeast: 1,
    southwest: -1,
    up: 0,
    down: 0,
  };

  const dy = {
    west: 0,
    east: 0,
    north: -1,
    south: 1,
    northeast: -1,
    northwest: -1,
    southeast: 1,
    southwest: 1,
    up: 0,
    down: 0,
  };

  const dz = {
    west: 0,
    east: 0,
    north: 0,
    south: 0,
    northeast: 0,
    northwest: 0,
    southeast: 0,
    southwest: 0,
    up: 1,
    down: -1,
  };

  const newX = currentX + (dx[dir] || 0);
  const newY = currentY + (dy[dir] || 0);
  const newZ = currentZ + (dz[dir] || 0);
  const newKey = key(newX, newY, newZ);
  const curKey = key(currentX, currentY, currentZ);

  if (!grid[curKey]) grid[curKey] = { exits: {} };
  grid[curKey].exits[dir] = newKey;

  if (!grid[newKey]) {
    const desc = getRandomRoomDescription();
    grid[newKey] = {
      exits: {},
      set_short: desc.short,
      set_long: desc.long,
    };
  }

  const reverse = {
    north: "south",
    south: "north",
    east: "west",
    west: "east",
    northeast: "southwest",
    northwest: "southeast",
    southeast: "northwest",
    southwest: "northeast",
    up: "down",
    down: "up",
  };
  grid[newKey].exits[reverse[dir]] = curKey;

  currentX = newX;
  currentY = newY;
  currentZ = newZ;

  loadRoom();
  drawMap();
}

function loadRoom() {
  const curKey = key(currentX, currentY, currentZ);
  const room = grid[curKey] || {};

  document.getElementById("set_short").value = room.set_short || "";
  document.getElementById("set_long").value = room.set_long || "";
  document.getElementById("set_smell").value = room.set_smell || "";

  // Clear existing item inputs
  const itemContainer = document.getElementById("itemsContainer");
  itemContainer.innerHTML = "";

  // Load each item
  if (Array.isArray(room.set_items)) {
    room.set_items.forEach((item) => {
      addItemRow(item.name, item.description);
    });
  }

  // Clear existing monster inputs
  const monsterContainer = document.getElementById("monsterContainer");
  monsterContainer.innerHTML = "";

  // Load each monster ID (if any)
  if (Array.isArray(room.monsters)) {
    room.monsters.forEach((monsterID) => {
      addMonsterRow(monsterID);
    });
  }

  // Clear existing object inputs
  const objectContainer = document.getElementById("objectContainer");
  objectContainer.innerHTML = "";

  // Load each object ID (if any)
  if (Array.isArray(room.objects)) {
    room.objects.forEach((objectID) => {
      addObjectRow(objectID);
    });
  }
}

function addItemRow(name = "", description = "") {
  const itemDiv = document.createElement("div");
  itemDiv.classList.add("item-row");
  itemDiv.style.marginBottom = "6px";

  const itemInput = document.createElement("input");
  itemInput.type = "text";
  itemInput.placeholder = "Item name";
  itemInput.name = "item_name[]";
  itemInput.value = name;
  itemInput.style.marginRight = "8px";

  const descInput = document.createElement("input");
  descInput.type = "text";
  descInput.placeholder = "Description";
  descInput.name = "item_desc[]";
  descInput.value = description;
  descInput.style.marginRight = "8px";

  const removeBtn = document.createElement("button");
  removeBtn.type = "button";
  removeBtn.textContent = "-";
  removeBtn.addEventListener("click", () => {
    itemDiv.remove();
  });

  itemDiv.appendChild(itemInput);
  itemDiv.appendChild(descInput);
  itemDiv.appendChild(removeBtn);

  const container = document.getElementById("itemsContainer");
  container.appendChild(itemDiv);
}

function drawMap() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  squareMap = {}; // Reset on redraw

  const centerX = canvas.width / 2;
  const centerY = canvas.height / 2;

  for (const k in grid) {
    const [x, y, z] = k.split(",").map(Number);
    if (z !== currentZ) continue;

    const px = centerX + (x - currentX) * ROOM_SPACING;
    const py = centerY + (y - currentY) * ROOM_SPACING;

    const room = grid[k];

    // Store square bounds
    squareMap[`${x},${y},${z}`] = {
      x: px,
      y: py,
      w: TILE_SIZE,
      h: TILE_SIZE,
      roomKey: k,
    };

    for (const dir in room.exits) {
      const targetKey = room.exits[dir];
      const [tx, ty, tz] = targetKey.split(",").map(Number);
      if (tz !== currentZ) continue;

      const txPx = centerX + (tx - currentX) * ROOM_SPACING;
      const tyPx = centerY + (ty - currentY) * ROOM_SPACING;

      ctx.strokeStyle = "#999";
      ctx.lineWidth = 4;
      ctx.beginPath();
      ctx.moveTo(px + TILE_SIZE / 2, py + TILE_SIZE / 2);
      ctx.lineTo(txPx + TILE_SIZE / 2, tyPx + TILE_SIZE / 2);
      ctx.stroke();
    }
  }

  for (const k in grid) {
    const [x, y, z] = k.split(",").map(Number);
    if (z !== currentZ) continue;

    const px = centerX + (x - currentX) * ROOM_SPACING;
    const py = centerY + (y - currentY) * ROOM_SPACING;

    const room = grid[k];

    // Draw room square
    ctx.fillStyle = x === currentX && y === currentY ? "#3a6" : "#555";
    ctx.fillRect(px, py, TILE_SIZE, TILE_SIZE);
    ctx.strokeStyle = "#aaa";
    ctx.strokeRect(px, py, TILE_SIZE, TILE_SIZE);

    // Draw up/down indicators
    ctx.font = "12px sans-serif";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";

    if (room.exits["up"]) {
      ctx.fillStyle = "#6cf"; // light blue
      ctx.fillText("↑", px + TILE_SIZE + 8, py + TILE_SIZE / 2 - 8);
    }

    if (room.exits["down"]) {
      ctx.fillStyle = "#ffffcc";
      ctx.fillText("↓", px + TILE_SIZE + 8, py + TILE_SIZE / 2 + 8);
    }
  }
}

function downloadArea() {
  const data = {
    grid: grid,
    currentX: currentX,
    currentY: currentY,
    currentZ: currentZ,
  };
  const json = JSON.stringify(data, null, 2);
  const blob = new Blob([json], { type: "application/json" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "area.json";
  a.click();
  URL.revokeObjectURL(url);
}

function uploadArea(event) {
  const file = event.target.files[0];
  const reader = new FileReader();
  reader.onload = function (e) {
    try {
      const area = JSON.parse(e.target.result); // parse full object, not just grid
      console.log("Loaded area:", area);

      // Replace grid entirely
      grid = area.grid || {};

      if (
        typeof area.currentX === "number" &&
        typeof area.currentY === "number" &&
        typeof area.currentZ === "number"
      ) {
        currentX = area.currentX;
        currentY = area.currentY;
        currentZ = area.currentZ;
        console.log("Setting current room to:", currentX, currentY, currentZ);
      } else {
        const firstRoomKey = Object.keys(grid)[0];
        if (firstRoomKey) {
          const coords = firstRoomKey.split(",").map(Number);
          currentX = coords[0];
          currentY = coords[1];
          currentZ = coords[2];
          console.log(
            "Setting current room to (from first room):",
            currentX,
            currentY,
            currentZ
          );
        } else {
          currentX = 0;
          currentY = 0;
          currentZ = 0;
          console.warn("Grid empty after upload");
        }
      }

      loadRoom();
      drawMap();
    } catch (ex) {
      console.error("Error parsing uploaded file:", ex);
    }
  };
  reader.readAsText(file);
}

function saveAreaToDb(callback) {
  saveRoom();
  const areaData = {
    grid: grid,
    currentX: currentX,
    currentY: currentY,
    currentZ: currentZ,
  };

  fetch("save_area.php?id=" + areaId, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(areaData),
  })
    .then((response) => response.json())
    .then((data) => {
      const statusDiv = document.getElementById("saveStatus");
      if (data.success) {
        statusDiv.textContent = "Area saved successfully!";
        statusDiv.style.color = "green";
        if (callback) callback(); // Call the callback after successful save
      } else {
        statusDiv.textContent = "Error saving area: " + data.error;
        statusDiv.style.color = "red";
      }
      setTimeout(() => (statusDiv.textContent = ""), 3000);
    })
    .catch((err) => {
      const statusDiv = document.getElementById("saveStatus");
      statusDiv.textContent = "Network error: " + err.message;
      statusDiv.style.color = "red";
      setTimeout(() => (statusDiv.textContent = ""), 3000);
    });
}

// Handle back button with autosave
document
  .getElementById("backToDashboard")
  .addEventListener("click", function (event) {
    event.preventDefault(); // Stop immediate navigation
    saveAreaToDb(() => {
      window.location.href = "dashboard.php"; // Redirect after save
    });
  });

const itemsContainer = document.getElementById("itemsContainer");
const addItemBtn = document.getElementById("addItemBtn");

addItemBtn.addEventListener("click", () => {
  const textarea = document.getElementById("set_long");
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const selectedText = textarea.value.substring(start, end);
  const itemDiv = document.createElement("div");
  itemDiv.classList.add("item-row");
  itemDiv.style.marginBottom = "6px";

  const itemInput = document.createElement("input");
  itemInput.type = "text";
  itemInput.placeholder = "Item name";
  itemInput.name = "item_name[]";
  itemInput.style.marginRight = "8px";
  itemInput.value = selectedText.trim();

  const descInput = document.createElement("input");
  descInput.type = "text";
  descInput.placeholder = "Description";
  descInput.name = "item_desc[]";
  descInput.style.marginRight = "8px";

  const removeBtn = document.createElement("button");
  removeBtn.type = "button";
  removeBtn.textContent = "-";
  removeBtn.addEventListener("click", () => {
    itemsContainer.removeChild(itemDiv);
  });

  itemDiv.appendChild(itemInput);
  itemDiv.appendChild(descInput);
  itemDiv.appendChild(removeBtn);

  itemsContainer.appendChild(itemDiv);
});

function getItems() {
  const names = Array.from(document.getElementsByName("item_name[]")).map((i) =>
    i.value.trim()
  );
  const descs = Array.from(document.getElementsByName("item_desc[]")).map((i) =>
    i.value.trim()
  );

  // Combine into objects, filtering out empty names
  const items = [];
  for (let i = 0; i < names.length; i++) {
    if (names[i]) {
      items.push({ name: names[i], description: descs[i] || "" });
    }
  }
  return items;
}

function showRoomMenu(roomKey, tileX, tileY) {
  const menu = document.getElementById("roomMenu");
  const room = grid[roomKey];

  // Get canvas position relative to the page
  const canvasRect = canvas.getBoundingClientRect();

  // Convert tile coords to page coords
  const pageX = canvasRect.left + tileX;
  const pageY = canvasRect.top + tileY;

  menu.innerHTML = `
    <strong>Room:</strong> ${roomKey}<br>
    <button onclick="saveRoom();editRoom('${roomKey}')">Edit</button>
    <button onclick="deleteRoom('${roomKey}')">Delete</button>
  `;

  menu.style.left = `${pageX}px`;
  menu.style.top = `${pageY}px`;
  menu.style.display = "block";
}

function editRoom(roomKey) {
  const [x, y, z] = roomKey.split(",").map(Number);
  currentX = x;
  currentY = y;
  currentZ = z;

  // Close the menu
  document.getElementById("roomMenu").style.display = "none";

  // Redraw map centered on new current room
  loadRoom();
  drawMap();
}

function deleteRoom(roomKey) {
  const room = grid[roomKey];
  if (!room) return;

  // 1. Remove links TO this room from other rooms
  for (const key in grid) {
    const exits = grid[key].exits;
    for (const dir in exits) {
      if (exits[dir] === roomKey) {
        delete exits[dir];
      }
    }
  }

  // 2. Remove the room itself
  delete grid[roomKey];

  // 3. If we just deleted the current room, move to a neighbor
  const [x, y, z] = roomKey.split(",").map(Number);
  if (x === currentX && y === currentY && z === currentZ) {
    const neighborKey = Object.values(room.exits).find((k) => grid[k]);
    if (neighborKey) {
      const [nx, ny, nz] = neighborKey.split(",").map(Number);
      currentX = nx;
      currentY = ny;
      currentZ = nz;
    } else {
      // No valid neighbors, reset view to 0,0,0 or first available room
      const keys = Object.keys(grid);
      if (keys.length > 0) {
        const [nx, ny, nz] = keys[0].split(",").map(Number);
        currentX = nx;
        currentY = ny;
        currentZ = nz;
      } else {
        // Grid is now empty
        currentX = currentY = currentZ = 0;
      }
    }
  }

  // 4. Close popup and refresh
  document.getElementById("roomMenu").style.display = "none";
  loadRoom();
  drawMap();
}

window.addEventListener("DOMContentLoaded", () => {
  if (loadedRoomData) {
    try {
      const area = loadedRoomData;

      if (typeof area.grid === "object") {
        grid = area.grid;
        currentX = area.currentX ?? 0;
        currentY = area.currentY ?? 0;
        currentZ = area.currentZ ?? 0;
        loadRoom();
        drawMap();
      }
    } catch (err) {
      console.error("Error parsing loaded room data:", err);
    }
  } else {
    // First-time area — create the starting room
    const desc = getRandomRoomDescription();
    const key0 = key(0, 0, 0);
    grid[key0] = {
      set_short: desc.short,
      set_long: desc.long,
      exits: {},
      items: [],
    };
    currentX = currentY = currentZ = 0;
    loadRoom();
    drawMap();
  }

  // You can also use areaMeta to populate a sidebar or title
  // document.getElementById("areaName").textContent = areaMeta.name || "Unnamed Area";
});

canvas.addEventListener("click", (e) => {
  const rect = canvas.getBoundingClientRect();
  const mouseX = e.clientX - rect.left;
  const mouseY = e.clientY - rect.top;

  for (const key in squareMap) {
    const { x, y, w, h, roomKey } = squareMap[key];
    if (mouseX >= x && mouseX <= x + w && mouseY >= y && mouseY <= y + h) {
      // You clicked this room!
      showRoomMenu(roomKey, x + w, y); // open menu near the room
      e.stopPropagation();
      // console.log(roomKey);
      break;
    }
  }
});

document.addEventListener("click", (e) => {
  const menu = document.getElementById("roomMenu");
  if (!menu.contains(e.target)) {
    menu.style.display = "none";
  }
});

document.getElementById("extractItemsBtn").addEventListener("click", () => {
  const desc = document.getElementById("set_long").value;
  const doc = nlp(desc);

  // Define stopwords to exclude abstract concepts and pronouns
  const STOPWORDS = [
    "you",
    "your",
    "sense",
    "direction",
    "journey",
    "route",
    "steps",
    "maze",
  ];

  // Step 1: Get all noun phrases from the document
  const phrases = doc.nouns().out("array");

  // Step 2: Clean and filter phrases
  const filtered = phrases
    .map((p) => p.toLowerCase().trim())
    .filter(
      (p) =>
        !STOPWORDS.some((sw) => p.includes(sw)) &&
        !p.match(/\byou\b/) &&
        p.length > 2
    );

  // Step 3: Get existing item names already on the form
  const existingInputs = document.querySelectorAll('input[name="item_name[]"]');
  const existingItems = Array.from(existingInputs).map((input) =>
    input.value.trim().toLowerCase()
  );

  // Step 4: Add only non-duplicate, filtered items
  for (const item of filtered) {
    if (!existingItems.includes(item)) {
      addItemRow(item, "");
    }
  }
});

function showTab(tabId) {
  const tabs = document.querySelectorAll(".tab-content");
  tabs.forEach((tab) => (tab.style.display = "none"));

  const buttons = document.querySelectorAll(".tab-buttons button");
  buttons.forEach((btn) => btn.classList.remove("active"));

  document.getElementById("tab-" + tabId).style.display = "block";
  event.target.classList.add("active");
}

function addMonsterRow(selectedId = "") {
  const container = document.getElementById("monsterContainer");

  const row = document.createElement("div");
  row.classList.add("monster-row");
  row.style.marginBottom = "6px";

  const select = document.createElement("select");
  select.name = "monster_id[]";

  // Default placeholder
  const placeholder = document.createElement("option");
  placeholder.value = "";
  placeholder.textContent = "-- Select a monster --";
  select.appendChild(placeholder);

  // Fetch monsters via AJAX
  fetch("get_monsters.php")
    .then((response) => response.json())
    .then((data) => {
      data.forEach((monster) => {
        const option = document.createElement("option");
        option.value = monster.id;
        option.textContent =
          monster.id +
            " " +
            monster.set_short +
            " (" +
            monster.set_class +
            ")" || "(no name)";
        select.appendChild(option);
      });
      // Set default selected after options are loaded
      if (selectedId) {
        select.value = selectedId.toString();
      }
    })
    .catch((err) => {
      console.error("Error fetching monsters:", err);
      const option = document.createElement("option");
      option.value = "";
      option.textContent = "Error loading monsters";
      select.appendChild(option);
    });

  const removeBtn = document.createElement("button");
  removeBtn.type = "button";
  removeBtn.textContent = "-";
  removeBtn.addEventListener("click", () => {
    row.remove();
  });

  row.appendChild(select);
  row.appendChild(removeBtn);
  container.appendChild(row);
}

function getMonsters() {
  const selects = document.querySelectorAll("#monsterContainer select");
  const ids = [];

  selects.forEach((select) => {
    const id = parseInt(select.value);
    if (!isNaN(id)) ids.push(id);
  });

  return ids;
}

function getObjects() {
  const selects = document.querySelectorAll("#objectContainer select");
  const ids = [];

  selects.forEach((select) => {
    const id = parseInt(select.value);
    if (!isNaN(id)) ids.push(id);
  });

  return ids;
}

function addObjectRow(selectedId = "") {
  const container = document.getElementById("objectContainer");

  const row = document.createElement("div");
  row.classList.add("object-row");
  row.style.marginBottom = "6px";

  const select = document.createElement("select");
  select.name = "object_id[]";

  // Default placeholder
  const placeholder = document.createElement("option");
  placeholder.value = "";
  placeholder.textContent = "-- Select a object --";
  select.appendChild(placeholder);

  // Fetch objects via AJAX
  fetch("get_objects.php")
    .then((response) => response.json())
    .then((data) => {
      data.forEach((object) => {
        const option = document.createElement("option");
        option.value = object.id;
        option.textContent =
          object.id + " " + object.short + " (" + object.class + ")" ||
          "(no name)";
        select.appendChild(option);
      });
      // Set default selected after options are loaded
      if (selectedId) {
        select.value = selectedId.toString();
      }
    })
    .catch((err) => {
      console.error("Error fetching objects:", err);
      const option = document.createElement("option");
      option.value = "";
      option.textContent = "Error loading objects";
      select.appendChild(option);
    });

  const removeBtn = document.createElement("button");
  removeBtn.type = "button";
  removeBtn.textContent = "-";
  removeBtn.addEventListener("click", () => {
    row.remove();
  });

  row.appendChild(select);
  row.appendChild(removeBtn);
  container.appendChild(row);
}

loadRoom();
drawMap();
