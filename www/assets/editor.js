const mapEl = document.getElementById("map");
const floorLabel = document.getElementById("floorLabel");

const shortInput = document.getElementById("short");
const longInput = document.getElementById("long");
const smellInput = document.getElementById("smell");
const itemsInput = document.getElementById("items");
const saveBtn = document.getElementById("saveBtn");

const directions = ["n","ne","e","se","s","sw","w","nw","u","d"];

const dirOffsets = {
  n:  [0, -1, 0],
  ne: [1, -1, 0],
  e:  [1, 0, 0],
  se: [1, 1, 0],
  s:  [0, 1, 0],
  sw: [-1, 1, 0],
  w:  [-1, 0, 0],
  nw: [-1, -1, 0],
  u:  [0, 0, 1],
  d:  [0, 0, -1],
};

const opposite = {
  n:"s", ne:"sw", e:"w", se:"nw",
  s:"n", sw:"ne", w:"e", nw:"se",
  u:"d", d:"u"
};

// 16x16 grid size and offset to center around (0,0)
const GRID_SIZE = 16;
const OFFSET = Math.floor(GRID_SIZE/2);

let current = {x:0,y:0,z:0};

let map = {};

// Initialize starting room at 0,0,0
map["0,0,0"] = {exits:{}, short:"Start Room", long:"This is the starting room.", smell:"A faint smell of dust.", items:[]};

function posToXY(x, y) {
  // convert grid coords to px, centered in map
  // room size is 40x40 + 10 margin
  return {
    left: (x+OFFSET)*50,
    top: (y+OFFSET)*50
  };
}

function dirToOffset(dir) {
  return dirOffsets[dir] || [0,0,0];
}

function oppositeDir(dir) {
  return opposite[dir];
}

function saveCurrentRoom() {
  const key = `${current.x},${current.y},${current.z}`;
  if (!map[key]) return;

  map[key].short = shortInput.value.trim();
  map[key].long = longInput.value.trim();
  map[key].smell = smellInput.value.trim();
  map[key].items = itemsInput.value.split(",").map(i => i.trim()).filter(i => i.length > 0);
}

function updateRoomForm() {
  const key = `${current.x},${current.y},${current.z}`;
  const room = map[key];
  if (!room) {
    shortInput.value = "";
    longInput.value = "";
    smellInput.value = "";
    itemsInput.value = "";
    return;
  }
  shortInput.value = room.short || "";
  longInput.value = room.long || "";
  smellInput.value = room.smell || "";
  itemsInput.value = (room.items || []).join(", ");
}

function drawMap() {
  mapEl.innerHTML = "";
  const floorRooms = Object.entries(map).filter(([k,v]) => {
    const [x,y,z] = k.split(",").map(Number);
    return z === current.z;
  });

  // Draw lines between rooms on same floor
  floorRooms.forEach(([key, room]) => {
    const [x,y,z] = key.split(",").map(Number);
    for (const dir of Object.keys(room.exits)) {
      if (!directions.includes(dir)) continue;
      const [dx, dy, dz] = dirToOffset(dir);
      if (dz !== 0) continue; // skip vertical lines on map
      const nx = x + dx;
      const ny = y + dy;
      if (!map[`${nx},${ny},${z}`]) continue;
      drawLine(x, y, nx, ny);
    }
  });

  // Draw rooms on floor
  floorRooms.forEach(([key, room]) => {
    const [x,y,z] = key.split(",").map(Number);
    drawRoom(x, y);
  });
}

function drawLine(x1, y1, x2, y2) {
  const start = posToXY(x1, y1);
  const end = posToXY(x2, y2);
  const line = document.createElement("div");
  line.className = "link-line";
  line.style.position = "absolute";

  // Calculate center points of rooms (20px offset for center of 40x40 room)
  const startX = start.left + 20;
  const startY = start.top + 20;
  const endX = end.left + 20;
  const endY = end.top + 20;

  // Length and angle of line
  const length = Math.hypot(endX - startX, endY - startY);
  const angle = Math.atan2(endY - startY, endX - startX) * 180 / Math.PI;

  line.style.width = length + "px";
  line.style.height = "2px";
  line.style.left = startX + "px";
  line.style.top = startY + "px";
  line.style.backgroundColor = "#aaa";
  line.style.transformOrigin = "0 0";
  line.style.transform = `rotate(${angle}deg)`;
  line.style.zIndex = "0";

  mapEl.appendChild(line);
}

function drawRoom(x, y) {
  const div = document.createElement("div");
  div.className = "room";
  if (x === current.x && y === current.y) {
    div.classList.add("current");
  }
  const pos = posToXY(x, y);
  div.style.position = "absolute";
  div.style.left = pos.left + "px";
  div.style.top = pos.top + "px";
  div.dataset.key = `${x},${y},${current.z}`;
  div.addEventListener("click", () => onRoomClick(x, y));
  mapEl.appendChild(div);
}

function onRoomClick(x, y) {
  saveCurrentRoom();  // Save current room before moving
  current.x = x;
  current.y = y;
  updateRoomForm();
  drawMap();
  updateFloorLabel();
}

function digRoom(dir) {
  // auto-save current room details here

  const [dx, dy, dz] = dirToOffset(dir);
  const newX = current.x + dx;
  const newY = current.y + dy;
  const newZ = current.z + dz;

  const fromKey = `${current.x},${current.y},${current.z}`;
  const toKey = `${newX},${newY},${newZ}`;

  if (!map[toKey]) {
    // create new room and link exits both ways
    map[toKey] = { exits:{}, short:"", long:"", smell:"", items:[] };
    map[fromKey].exits[dir] = true;
    map[toKey].exits[oppositeDir(dir)] = true;
  }

  // move current to new room
  current.x = newX;
  current.y = newY;
  current.z = newZ;

  drawMap();
  updateRoomForm();
  updateFloorLabel();
}

function updateFloorLabel() {
  floorLabel.textContent = `Floor: ${current.z}`;
}

function init() {
  updateRoomForm();
  drawMap();
  updateFloorLabel();

  directions.forEach(dir => {
    const btn = document.getElementById(`dig-${dir}`);
    if (btn) {
      btn.addEventListener("click", () => digRoom(dir));
    }
  });

  // Optional: Remove manual save button listener, since we auto-save
  saveBtn.style.display = "none";
}

init();
