<?php
require 'auth/db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.html');
    exit;
}

if (!isset($_GET['area_id'])) {
    die('Missing area_id.');
}

$area_id = (int)$_GET['area_id'];
$username = $_SESSION['username'];

// Fetch area metadata
$stmt = $pdo->prepare("SELECT * FROM areas WHERE id = ? AND username = ?");
$stmt->execute([$area_id, $username]);
$area = $stmt->fetch();

if (!$area) {
    die('Area not found or not authorized.');
}

// Fetch room data
$stmt = $pdo->prepare("SELECT data FROM rooms WHERE area_id = ? AND username = ?");
$stmt->execute([$area_id, $username]);
$roomRow = $stmt->fetch();

// Fetch all monsters as associative arrays
$stmt = $pdo->prepare("SELECT * FROM monsters WHERE owner = ?");
$stmt->execute([$username]);
$monsters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all obj as associative arrays
$stmt = $pdo->prepare("SELECT * FROM obj WHERE owner = ?");
$stmt->execute([$username]);
$obj = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all MonObj as associative arrays
$stmt = $pdo->prepare("SELECT * FROM monsterObj");
$stmt->execute([]);
$monsterItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
$grid = json_decode($roomRow->data, true)['grid'];


$usedMonsterIds = [];

foreach ($grid as $room) {
    if (!empty($room['monsters'])) {
        foreach ($room['monsters'] as $mid) {
            $usedMonsterIds[$mid] = true;
        }
    }
}

if (!$roomRow) {
    die('No rooms found for this area.');
}

$areaName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $area->name); // sanitize folder name
$basePath = rtrim($area->basePath, '/') . '/';
$baseLevel = $area->levelRange;

$coordToFile = [];
$existingNames = [];

$usedObjectIds = [];

foreach ($grid as $room) {
    if (!empty($room['objects'])) {
        foreach ($room['objects'] as $objectId) {
            $usedObjectIds[] = (int)$objectId;
        }
    }
}

foreach ($monsterItems as $mi) {
    $usedObjectIds[] = (int)$mi['object_id'];
}

$usedObjectIds = array_unique($usedObjectIds);

$objectMap = [];
$objects = array_filter($obj, function ($o) use ($usedObjectIds) {
    return in_array((int)$o['id'], $usedObjectIds);
});

$monsterMap = [];
foreach ($monsters as $monster) {
    if (!isset($usedMonsterIds[$monster['id']])) continue; // Only include used monsters

    $short = $monster['set_short'] ?? 'monster';
    $filename = strtolower(preg_replace('/[^a-z0-9_]+/', '_', $short));
    $monsterMap[$monster['id']] = $filename;
}

foreach ($objects as $o) {
    $short = $o['short'] ?? 'item';
    $filename = strtolower(preg_replace('/[^a-z0-9_]+/', '_', $short));
    $objectMap[$o['id']] = $filename;
}

// Build mapping from monster_id to array of object filenames
$monsterItemMap = [];

foreach ($monsterItems as $mi) {
    $monster_id = $mi['monster_id'];
    $object_id = $mi['object_id'];
    if (!isset($monsterItemMap[$monster_id])) {
        $monsterItemMap[$monster_id] = [];
    }
    if (isset($objectMap[$object_id])) {
        $monsterItemMap[$monster_id][] = $objectMap[$object_id]; // store filename without extension
    }
}


function makeSafeFileName($short) {
    $safe = strtolower($short);
    $safe = preg_replace('/[^a-z0-9]+/', '_', $safe); // Replace non-alphanumerics with underscores
    $safe = trim($safe, '_'); // Trim leading/trailing underscores
    return $safe;
}

// First pass: assign filenames and store mapping
foreach ($grid as $coords => $room) {
    $short = $room['set_short'] ?? 'A Room';
    $baseName = makeSafeFileName($short);
    $fileName = $baseName; // . ".c";

    $counter = 1;
    while (in_array($fileName, $existingNames)) {
        $fileName = $baseName . "_" . str_pad($counter, 2, '0', STR_PAD_LEFT); // . ".c";
        $counter++;
    }

    $existingNames[] = $fileName;
    $coordToFile[$coords] = $fileName;
}


// Create temporary export folder
$baseDir = sys_get_temp_dir() . "/export_$areaName";
@mkdir("$baseDir/include", 0777, true);
@mkdir("$baseDir/rooms", 0777, true);
@mkdir("$baseDir/mon");
@mkdir("$baseDir/obj");

// Write include.h
file_put_contents("$baseDir/include/include.h", <<<H
// Auto-generated LPC area code - created via Web MUD Editor Export Tool

#include <std.h>
#include <defs.h>
#define MY_PATH "$basePath"

#define OBDIR   MY_PATH + "obj/"
#define MONDIR  MY_PATH + "mon/"
#define ROOMDIR MY_PATH + "rooms/"

#define BASE_LEVEL $baseLevel

H);

$monsterDir = "$baseDir/mon";
if (!is_dir($monsterDir)) {
    mkdir($monsterDir, 0755, true);
}

$objectDir = "$baseDir/obj";
if (!is_dir($objectDir)) {
    mkdir($objectDir, 0755, true);
}

foreach ($objects as $object) {
    $short = trim($object['short']);
    $set_name = addslashes($object['name'] ?? 'item');
    $long = trim($object['longdesc'] ?? 'A mysterious object lies here.');
    $name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $object['short'] ?? 'item'));

    $levelOffset = (int)$object['level'] - $baseLevel;
    $rawClass = trim($object['class'] ?? 'blade');
    $class = strtolower($rawClass);
    $filename = "$objectDir/{$name}.c";
    $objectMap[$object['id']] = $name;

    $weaponTypes = ['blade','blunt','knife','polearm','projectile','staff','thrown','two handed','whip'];
    $accessoryTypes = ['amulet','cloak','ring','shield'];
    $armourSlotMap = [
        'head' => 'helm',
        'torso' => 'armour',
        'hands' => 'gloves',
        'feet' => 'boots'
    ];

    $inherit = 'ARMOUR'; // default
    $typeLine = '';
    $subtypeLine = '';

    if (in_array($class, $weaponTypes)) {
        $inherit = 'WEAPON';
        $typeLine = '    set_type("' . $class . '");';

    } elseif (in_array($class, $accessoryTypes)) {
        $inherit = 'ARMOUR';
        $typeLine = '    set_type("' . $class . '");';

    } else {
        // Assume format like "Cloth Head", "Leather Torso"
        $parts = explode(' ', $rawClass);
        $material = strtolower($parts[0] ?? 'cloth');
        $slot = strtolower($parts[1] ?? 'torso');
        $subtype = $armourSlotMap[$slot] ?? $slot;

        $typeLine = '    set_type("' . $subtype . '");';
        $subtypeLine = '    set_subtype("' . $material . '");';
    }

    $code = <<<C
// Autogenerated object file
// A standard object - from the Elephant Mudlib

#include "../include/include.h"
#include <std.h>

inherit {$inherit};

void create()
{
    ::create();
    set_name("{$set_name}");
    set_id(({"{$name}"}));
    set_short("{$short}");
    set_long("{$long}");
{$typeLine}
{$subtypeLine}
    set_level(BASE_LEVEL + ({$levelOffset}));
}
C;

    file_put_contents($filename, $code);
}

// Generate room files
foreach ($grid as $coords => $room) {
//    $fileName = "room_" . str_replace(',', '_', $coords) . ".c";
    $fileName = $coordToFile[$coords];
    $short = addslashes($room['set_short'] ?? 'A Room');
    $long = addslashes(trim($room['set_long'] ?? ''));
    $items = $room['set_items'] ?? [];
    $exits = $room['exits'] ?? [];

    // Format items
    $itemStrs = [];
    foreach ($items as $item) {
        $itemStrs[] = '        "' . addslashes($item['name']) . '": "' . addslashes($item['description']) . '"';
    }
    $itemBlock = empty($itemStrs) ? '' : "    set_items(([\n" . implode(",\n", $itemStrs) . "\n    ]));\n";

    // Format exits
    $exitStrs = [];
    foreach ($exits as $dir => $target) {
        $targetCoords = "room_" . str_replace(',', '_', $target);
	$targetFile = $coordToFile[$target];
        $targetFileNoExt = pathinfo($targetFile, PATHINFO_FILENAME);
        $exitStrs[] = '        "' . $dir . '": ROOMDIR + "' . $targetFileNoExt . '"';
    }
    $exitBlock = empty($exitStrs) ? '' : "    set_exits(([\n" . implode(",\n", $exitStrs) . "\n    ]));\n";

    $monsterLines = '';
    $monsterIds = $room['monsters'] ?? [];

    foreach ($monsterIds as $id) {
        if (isset($monsterMap[$id])) {
            $monsterFilename = $monsterMap[$id];
            $monsterLines .= "    add_object(MONDIR + \"$monsterFilename\", \"\", RT_TRACK);\n";
        }
    }

$objectLines = '';
$objectIds = $room['objects'] ?? [];

foreach ($objectIds as $id) {
    if (isset($objectMap[$id])) {
        $objectFilename = $objectMap[$id];
        $objectLines .= "    add_object(OBDIR + \"$objectFilename\", \"\", RT_TRACK);\n";
    }
}


    $roomCode = <<<C
#include "../include/include.h"

#include <std.h>

inherit ROOM;

void create()
{
    ::create();
    set_short("$short");
    set_long("$long");
$itemBlock$exitBlock$monsterLines$objectLines}
C;

    file_put_contents("$baseDir/rooms/$fileName" . ".c", $roomCode);
}

foreach ($monsters as $monster) {
    if (!isset($usedMonsterIds[$monster['id']])) continue;

    $short = addslashes($monster['set_short'] ?? 'A Creature');
    $set_name = addslashes($monster['set_name'] ?? 'creature');
    $long = addslashes(trim($monster['set_long'] ?? 'It looks unremarkable.'));
    $name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $monster['set_short'] ?? 'creature'));

    $levelOffset = (int)$monster['set_level'] - $baseLevel;
    $race = strtolower(trim($monster['set_race'] ?? 'unknown'));
    $class = strtolower(trim($monster['set_class'] ?? 'fighter'));
    $gender = strtolower(trim($monster['set_gender'] ?? 'neuter'));

    $filename = "$monsterDir/{$name}.c";

    // Build the add_object lines
    $addObjectCode = '';
    if (!empty($monsterItemMap[$monster['id']])) {
        foreach ($monsterItemMap[$monster['id']] as $objFilename) {
            $addObjectCode .= "\n    add_object(OBDIR+\"$objFilename\");";
        }
    }

    $code = <<<C
// Autogenerated monster file

#include "../include/include.h"
#include <std.h>

inherit MONSTER;

void create()
{
    ::create();
    set_name("{$set_name}");
    set_id(({"{$name}"}));
    set_short("{$short}");
    set_long("{$long}");
    set_level(BASE_LEVEL + ({$levelOffset}));
    set_gender("{$gender}");
    set_race("{$race}");
    set_class("{$class}");{$addObjectCode}
}
C;

    file_put_contents($filename, $code);
}


// Create zip
$zipFile = tempnam(sys_get_temp_dir(), "mudzip_");
$zip = new ZipArchive();
$zip->open($zipFile, ZipArchive::OVERWRITE | ZipArchive::CREATE);

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $file) {
    if (!$file->isFile()) continue;
    $filePath = $file->getRealPath();
    $relativePath = substr($filePath, strlen($baseDir) + 1);
    $zip->addFile($filePath, "$areaName/$relativePath");
}
$zip->close();

// Serve file
header('Content-Type: application/zip');
header("Content-Disposition: attachment; filename=\"$areaName.zip\"");
header('Content-Length: ' . filesize($zipFile));
readfile($zipFile);

// Cleanup
unlink($zipFile);
function deleteFolder($folder) {
    foreach (glob($folder . '/*') as $file) {
        if (is_dir($file)) deleteFolder($file);
        else unlink($file);
    }
    rmdir($folder);
}
deleteFolder($baseDir);
exit;
