<?php
/*
 * MSC Config Service
 * Copyright: © 2025 Timan Angerer
 */

// --- Debug bei Bedarf aktivieren ---
// ini_set('display_errors','1'); error_reporting(E_ALL);

/* ================= Hilfsfunktionen ================= */

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function strip_block_comments($s){
  $out=''; $len=strlen($s); $i=0; $in=false;
  while($i<$len){
    if(!$in && $i+1<$len && $s[$i]==='/' && $s[$i+1]==='*'){ $in=true; $i+=2; continue; }
    if($in  && $i+1<$len && $s[$i]==='*' && $s[$i+1]==='/'){ $in=false; $i+=2; continue; }
    if(!$in){ $out.=$s[$i]; }
    $i++;
  }
  return $out;
}
function strip_line_comments($s){
  $lines = preg_split("/(\r\n|\r|\n)/", $s);
  if($lines===false) $lines = array($s);
  foreach($lines as &$line){
    $in=false; $esc=false; $cut=null; $L=strlen($line);
    for($i=0;$i<$L;$i++){
      $ch=$line[$i];
      if($in){
        if($esc){ $esc=false; continue; }
        if($ch==='\\'){ $esc=true; continue; }
        if($ch==='"'){ $in=false; continue; }
      } else {
        if($ch==='"'){ $in=true; continue; }
        if($ch==='/' && $i+1<$L && $line[$i+1]==='/'){ $cut=$i; break; }
      }
    }
    if($cut!==null) $line = substr($line,0,$cut);
  }
  return implode("\n",$lines);
}
function strip_trailing_commas($s){
  return preg_replace('/,\s*([}\]])/', '$1', $s);
}
function normalize_json_like($input){
  return trim(strip_trailing_commas(strip_line_comments(strip_block_comments($input))));
}
function pretty_json($data){
  $j = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
  return $j!==false ? $j : '';
}
function is_assoc($arr){
  if(!is_array($arr)) return false;
  return array_keys($arr)!==range(0,count($arr)-1);
}
function path_join($base,$key){
  if($base==='' || $base===null) return (string)$key;
  return $base.'.'.$key;
}
function set_deep_value(&$root, $path, $value){
  $parts = explode('.', $path);
  $ref =& $root;
  foreach($parts as $i=>$k){
    if($k==='') continue;
    if($i===count($parts)-1){ $ref[$k] = $value; break; }
    if(!isset($ref[$k]) || !is_array($ref[$k])) $ref[$k] = array();
    $ref =& $ref[$k];
  }
}
function coerce_scalar($raw, $original){
  if(is_bool($original))  return $raw==='1';
  if(is_int($original))   return is_numeric($raw)?(int)$raw:0;
  if(is_float($original)) return is_numeric($raw)?(float)$raw:0.0;
  return (string)$raw;
}
function prettify_name($key){
  $key = str_replace(array('_','-'), ' ', (string)$key);
  $key = preg_replace('/\[(\d+)\]/', ' [$1]', $key);
  $key = trim($key);
  if ($key==='') return '';
  $parts = explode(' ', $key);
  foreach($parts as &$p){ $p = mb_strtoupper(mb_substr($p,0,1)).mb_substr($p,1); }
  return implode(' ', $parts);
}

/* ==================== Default-Konfiguration ==================== */

$default = array(
  "environment" => array(
    "country"  => "de",
    "username" => "CHANGEME",
    "password" => "CHANGEME"
  ),
  "building" => array(
    "hire" => array(
      "is_enabled"     => false,
      "default_amount" => 1
    )
  ),
  "interval" => 3,
  "filter" => array(
    "alliance" => array(
      "distance"                   => 400,
      "is_created_enabled"         => false,
      "is_created_events_enabled"  => false,
      "is_enabled"                 => false,
      "is_events_enabled"          => false,
      "is_stopover_enabled"        => false,
      "is_vehicle_consider_enabled"=> false
    ),
    "distance" => 400,
    "forward"  => array(
      "distance"           => 400,
      "estimated_duration" => 300,
      "is_enabled"         => false
    ),
    "is_complete_vehicles_enabled"       => false,
    "is_complete_vehicles_list_enabled"  => false,
    "is_complete_vehicles_list_offset"   => 3,
    "is_enabled"                         => true,
    "is_events_enabled"                  => true,
    "is_vehicle_consider_enabled"        => false,
    "is_vehicle_disable_enabled"         => false,
    "is_vehicle_scaling_enabled"         => true,
    "is_stopover_enabled"                => false,
    "limit" => array(
      "credits" => array(
        "amount" => 0,
        "offset" => 1
      ),
      "default_amount" => 5,
      "is_enabled"     => false,
      "is_user_enabled"=> false
    ),
    "share" => array(
      "is_enabled"                => false,
      "is_events_enabled"         => false,
      "is_requests_enabled"       => false,
      "is_vehicle_missing_enabled"=> false,
      "message" => array(
        "is_alliance_chat_enabled" => false,
        "is_enabled"               => false,
        "scheme"                   => '{Address} - {Place}, ${Credits}'
      )
    ),
    "speech" => array(
      "alliance" => array(
        "capacity_patient"  => 0,
        "capacity_prisoner" => 0,
        "distance"          => 400,
        "is_enabled"        => true,
        "tax"               => 0
      ),
      "capacity_patient"  => 0,
      "capacity_prisoner" => 0,
      "distance"          => 400,
      "interval"          => 3,
      "is_enabled"        => true
    )
  ),
  "speed_step" => 3
);

/* ==================== Meta-Beschreibungen (Tooltips) ==================== */
$META = array(
  "environment" => "Login-Umgebung für den Spielserver.",
  "environment.country"  => "Server-Land (z.B. de=leitstellenspiel.de, us=missionchief.com).",
  "environment.username" => "Benutzername oder E-Mail für den Login.",
  "environment.password" => "Passwort für den Login.",

  "building" => "Einstellungen für Gebäudeaktionen.",
  "building.hire" => "Personal-Einstellungen (automatisches Hire).",
  "building.hire.is_enabled" => "Automatisches Einstellen aktivieren.",
  "building.hire.default_amount" => "Einstellungs Tage: 1–3, -1=automatisch (Premium).",

  "interval" => "Intervall (Sekunden) zwischen Missionstasks, Standard 3s.",
  "speed_step" => "Ingame-Geschwindigkeit: 0=pause, 1=turbo … 8=extrem langsam.",

  "filter" => "Filter & Regeln für (Weiter-)Alarmierung.",
  "filter.distance" => "Maximale Entfernung (km) für Missionen.",
  "filter.is_enabled" => "Missionen (Weiter-)alarmieren aktiv.",
  "filter.is_events_enabled" => "Mission-Events berücksichtigen.",
  "filter.is_complete_vehicles_enabled" => "Nur alarmieren, wenn alle Fahrzeuge möglich sind.",
  "filter.is_complete_vehicles_list_enabled" => "Mehr Seiten laden für vollständige Liste (langsamer).",
  "filter.is_complete_vehicles_list_offset" => "Anzahl zusätzlicher Seiten.",
  "filter.is_vehicle_consider_enabled" => "Fahrzeuge von Verbandsmitgliedern berücksichtigen.",
  "filter.is_vehicle_disable_enabled" => "Fahrzeuge deaktivieren, wenn zu wenig Personal.",
  "filter.is_vehicle_scaling_enabled" => "Dynamische Skalierung (Wasser/Personal/Patienten).",
  "filter.is_stopover_enabled" => "Einsatz erneut öffnen, unabhängig vom Status.",

  "filter.limit" => "Begrenzungen für Alarme.",
  "filter.limit.default_amount" => "Standard-Anzahl der Missionen.",
  "filter.limit.is_enabled" => "Limits aktivieren.",
  "filter.limit.is_user_enabled" => "Benutzerbezogene Limits aktivieren.",
  "filter.limit.credits.amount" => "Mindestgutschrift (Credits).",
  "filter.limit.credits.offset" => "Offset/Multiplikator für Credits.",

  "filter.forward" => "Fahrzeuge weiter schicken.",
  "filter.forward.is_enabled" => "Weiteralarmieren aktivieren.",
  "filter.forward.distance" => "Maximale Entfernung (km).",
  "filter.forward.estimated_duration" => "Max. Restzeit (Sekunden) bei fast erledigten Einsätzen.",

  "filter.share" => "Einsätze im Verband teilen.",
  "filter.share.is_enabled" => "Teilen aktiv.",
  "filter.share.is_events_enabled" => "Event-Missionen teilen.",
  "filter.share.is_requests_enabled" => "Teilen bei Patienten/Gefangenen-Anforderung.",
  "filter.share.is_vehicle_missing_enabled" => "Teilen bei fehlenden Fahrzeugen.",
  "filter.share.message" => "Chat-Nachrichten beim Teilen.",
  "filter.share.message.is_enabled" => "Nachricht in Verbandschat senden.",
  "filter.share.message.is_alliance_chat_enabled" => "Nachricht in Einsatz-Verbandschat senden.",
  "filter.share.message.scheme" => "Vorlage: Platzhalter {Address}, {Place}, ${Credits}.",

  "filter.alliance" => "Regeln für Verbandsmissionen.",
  "filter.alliance.distance" => "Max. Entfernung (km) für Verbandsmissionen.",
  "filter.alliance.is_enabled" => "Geteilte Verbandsmissionen berücksichtigen.",
  "filter.alliance.is_events_enabled" => "Geteilte Verbands-Events berücksichtigen.",
  "filter.alliance.is_created_enabled" => "Selbst erstellte Verbandsmissionen berücksichtigen.",
  "filter.alliance.is_created_events_enabled" => "Selbst erstellte Verbands-Events berücksichtigen.",
  "filter.alliance.is_stopover_enabled" => "Einsatz erneut öffnen.",
  "filter.alliance.is_vehicle_consider_enabled" => "Fahrzeuge der Mitglieder berücksichtigen.",

  "filter.speech" => "Dispositionierung an Gebäude (Krankenhäuser/Gefängnisse).",
  "filter.speech.is_enabled" => "Automatisches Dispositionieren aktiv.",
  "filter.speech.distance" => "Maximale Entfernung zu Zielgebäuden.",
  "filter.speech.interval" => "Intervall (Sekunden) zwischen Speech-Tasks.",
  "filter.speech.capacity_patient" => "Max. Patienten, bevor nächstes Gebäude gewählt wird.",
  "filter.speech.capacity_prisoner" => "Max. Gefangene, bevor nächstes Gebäude gewählt wird.",
  "filter.speech.alliance" => "Verbandseinrichtungen für Speech berücksichtigen.",
  "filter.speech.alliance.distance" => "Max. Entfernung zu Verbands-Gebäuden.",
  "filter.speech.alliance.is_enabled" => "Speech zu Verbandsgebäuden aktivieren.",
  "filter.speech.alliance.capacity_patient" => "Max. Patienten in Verbands-Krankenhäusern.",
  "filter.speech.alliance.capacity_prisoner" => "Max. Gefangene in Verbands-Gefängnissen.",
  "filter.speech.alliance.tax" => "Max. Gebühren (0=aus)."
);

/* ==================== Labels ==================== */
$LABELS_DEFAULT = array(
  "config" => "Konfiguration",
  "environment" => "Umgebung",
  "environment.country"  => "Server-Land",
  "environment.username" => "Login-Name",
  "environment.password" => "Login-Passwort",

  "building" => "Gebäude",
  "building.hire" => "Anwerben",
  "building.hire.is_enabled" => "Automatisch einstellen",
  "building.hire.default_amount" => "Einstellungs-Tage",

  "interval" => "Aufgaben-Intervall (Sek.)",
  "speed_step" => "Spielgeschwindigkeit",

  "filter" => "Einsatz-Filter",
  "filter.distance" => "Einsatzreichweite (km)",
  "filter.is_enabled" => "Aktiv",
  "filter.is_events_enabled" => "Events aktiv",
  "filter.is_complete_vehicles_enabled" => "Nur vollständige Fahrzeuge",
  "filter.is_complete_vehicles_list_enabled" => "Mehr Seiten laden (Fahrzeuge)",
  "filter.is_complete_vehicles_list_offset" => "Seitenanzahl (Fahrzeuge)",
  "filter.is_vehicle_consider_enabled" => "Verband-Fahrzeuge berücksichtigen",
  "filter.is_vehicle_disable_enabled" => "Fahrzeuge deaktivieren bei Personalmangel",
  "filter.is_vehicle_scaling_enabled" => "Dynamische Skalierung",
  "filter.is_stopover_enabled" => "Einsatz erneut öffnen",

  "filter.limit" => "Limits",
  "filter.limit.default_amount" => "Standard-Limit",
  "filter.limit.is_enabled" => "Limits aktiv",
  "filter.limit.is_user_enabled" => "User-Limits aktiv",
  "filter.limit.credits.amount" => "Mindest-Credits",
  "filter.limit.credits.offset" => "Credits-Offset",

  "filter.forward" => "Weiteralarm",
  "filter.forward.is_enabled" => "Weiteralarm aktiv",
  "filter.forward.distance" => "Weiteralarm-Entfernung (km)",
  "filter.forward.estimated_duration" => "Max. Restzeit (Sek.)",

  "filter.share" => "Teilen",
  "filter.share.is_enabled" => "Teilen aktiv",
  "filter.share.is_events_enabled" => "Events teilen",
  "filter.share.is_requests_enabled" => "Teilen bei Anforderung",
  "filter.share.is_vehicle_missing_enabled" => "Teilen bei fehlenden Fahrzeugen",
  "filter.share.message" => "Teilen – Nachricht",
  "filter.share.message.is_enabled" => "In Verbandschat posten",
  "filter.share.message.is_alliance_chat_enabled" => "In Einsatz-Chat posten",
  "filter.share.message.scheme" => "Nachrichten-Vorlage",

  "filter.alliance" => "Verbandseinsätze",
  "filter.alliance.distance" => "Verband-Reichweite (km)",
  "filter.alliance.is_enabled" => "Geteilte Einsätze aktiv",
  "filter.alliance.is_events_enabled" => "Geteilte Events aktiv",
  "filter.alliance.is_created_enabled" => "Selbst erstellte Einsätze",
  "filter.alliance.is_created_events_enabled" => "Selbst erstellte Events",
  "filter.alliance.is_stopover_enabled" => "Erneut öffnen",
  "filter.alliance.is_vehicle_consider_enabled" => "Mitglieder-Fahrzeuge berücksichtigen",

  "filter.speech" => "Abgaben (Speech)",
  "filter.speech.is_enabled" => "Speech aktiv",
  "filter.speech.distance" => "Speech-Entfernung (km)",
  "filter.speech.interval" => "Speech-Intervall (Sek.)",
  "filter.speech.capacity_patient" => "Max. Patienten je Haus",
  "filter.speech.capacity_prisoner" => "Max. Gefangene je Zelle",
  "filter.speech.alliance" => "Speech – Verband",
  "filter.speech.alliance.distance" => "Speech – Verband Reichweite (km)",
  "filter.speech.alliance.is_enabled" => "Speech zu Verband aktiv",
  "filter.speech.alliance.capacity_patient" => "Max. Patienten (Verband)",
  "filter.speech.alliance.capacity_prisoner" => "Max. Gefangene (Verband)",
  "filter.speech.alliance.tax" => "Max. Gebühren (Verband)"
);

/* ==================== Label-Helfer ==================== */

function label_for_path($path, $key, $labels){
  if(isset($labels[$path]) && $labels[$path] !== '') return (string)$labels[$path];
  if($path==='' || $path==='config'){
    if(isset($labels['config']) && $labels['config']!=='') return (string)$labels['config'];
    return 'Konfiguration';
  }
  $last = $key!=='' ? $key : $path;
  $pos = strrpos($path, '.');
  if($pos!==false) $last = substr($path, $pos+1);
  return prettify_name($last);
}

/* ==================== Rendering: Formularfelder ==================== */

function info_html($path){
  global $META;
  if(!isset($META[$path])) return '';
  $text = $META[$path];
  return '<span class="info" title="'.h($text).'">i</span><div class="desc">'.h($text).'</div>';
}

function render_fields_recursive($node, $path, $orig, $labels){
  $html = '';
  if(is_array($node)){
    $label = label_for_path($path===''?'config':$path, '', $labels);
    $html .= '<details class="group" open><summary><strong>'.h($label).'</strong> '
           . info_html($path===''?'config':$path)
           . '<span class="path">('.h($path===''?'config':$path).')</span>'
           . '</summary><div class="box">';
    foreach($node as $k=>$v){
      $childPath = path_join($path, (string)$k);
      $origVal = is_array($orig)&&array_key_exists($k,$orig) ? $orig[$k] : $v;
      $html .= render_fields_recursive($v, $childPath, $origVal, $labels);
    }
    $html .= '</div></details>';
    return $html;
  }

  // Scalar
  $id = 'f_'.md5($path);
  $valStr = (string)$node;
  $display = label_for_path($path, '', $labels);
  $field = '';
  if(is_bool($node)){
    $checked = $node ? 'checked' : '';
    $field = '<input type="hidden" name="form['.h($path).']" value="0">'
           . '<input type="checkbox" id="'.$id.'" name="form['.h($path).']" value="1" '.$checked.'>';
  } elseif (is_int($node)){
    $field = '<input type="number" step="1" id="'.$id.'" name="form['.h($path).']" value="'.h($valStr).'">';
  } elseif (is_float($node)){
    $field = '<input type="number" step="any" id="'.$id.'" name="form['.h($path).']" value="'.h($valStr).'">';
  } else {
    $field = '<input type="text" id="'.$id.'" name="form['.h($path).']" value="'.h($valStr).'">';
  }

  return '<label class="row"><span><strong>'.h($display).'</strong><div class="path-small">'.h($path).'</div></span>'.$field.'</label>'.info_html($path);
}

/* ==================== Request-Handling ==================== */

$action       = isset($_POST['action']) ? (string)$_POST['action'] : '';
$state_raw    = isset($_POST['state_raw']) ? (string)$_POST['state_raw'] : null;

$errors = array();
$notes  = array();

$current_data   = null;
$current_raw    = null;
$current_labels = $LABELS_DEFAULT;

// Upload
if($action==='upload'){
  if(!empty($_FILES['upload']['tmp_name'])){
    $content = @file_get_contents($_FILES['upload']['tmp_name']);
    if($content===false){
      $errors[]='Upload konnte nicht gelesen werden.';
    } else {
      $test = json_decode(normalize_json_like($content), true);
      if($test===null && json_last_error()!==JSON_ERROR_NONE){
        $errors[]='Upload ungültig: '.json_last_error_msg();
      } else {
        $current_raw = rtrim($content)."\n";
        $current_data = $test;
        $notes[]='Datei geladen (ohne Server-Speicherung).';
      }
    }
  } else {
    $errors[]='Keine Datei ausgewählt.';
  }
}

// Roh übernehmen
if($action==='save_raw'){
  $raw = isset($_POST['raw']) ? (string)$_POST['raw'] : '';
  $test = json_decode(normalize_json_like($raw), true);
  if($test===null && json_last_error()!==JSON_ERROR_NONE){
    $errors[]='Ungültiges JSON: '.json_last_error_msg();
    $current_raw = $raw;
    $current_data = $default;
  } else {
    $current_raw = rtrim($raw)."\n";
    $current_data = $test;
    $notes[]='Roh-Änderungen übernommen.';
  }
}

// Formular anwenden (rekursiv ALLE Felder)
if($action==='save_form'){
  $base = $default;
  if(!empty($_POST['state_raw'])){
    $tmp = json_decode(normalize_json_like($_POST['state_raw']), true);
    if(is_array($tmp)) $base = $tmp;
  }

  $flat = isset($_POST['form']) && is_array($_POST['form']) ? $_POST['form'] : array();

  foreach($flat as $p=>$rawVal){
    $parts = explode('.', $p);
    $ref = $base;
    foreach($parts as $k){
      if($k==='') continue;
      if(isset($ref[$k])) $ref = $ref[$k];
      else { $ref = null; break; }
    }
    $coerced = coerce_scalar((string)$rawVal, $ref);
    set_deep_value($base, $p, $coerced);
  }

  $current_data = $base;
  $current_raw  = pretty_json($base)."\n";
  $notes[]='Formularwerte angewendet.';
}

// Download
if($action==='download'){
  $raw = isset($_POST['state_raw']) ? (string)$_POST['state_raw'] : '';
  if($raw==='') $raw = pretty_json($default)."\n";
  header('Content-Type: application/json; charset=utf-8');
  header('Content-Disposition: attachment; filename="config.mscc"');
  echo $raw; exit;
}

// Initialzustand
if($current_data===null){
  if($state_raw!==null){
    $tmp = json_decode(normalize_json_like($state_raw), true);
    if(is_array($tmp)){ $current_data=$tmp; $current_raw=$state_raw; }
    else { $current_data=$default; $current_raw="/**\n * Neue config.mscc\n */\n".pretty_json($default)."\n"; }
  } else {
    $current_data=$default; $current_raw="/**\n * Neue config.mscc\n */\n".pretty_json($default)."\n";
  }
}

/* ==================== Ausgabe ==================== */
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LSSBot Config Service</title>
<link rel="icon" type="image/png" href="favicon.png">
<link rel="shortcut icon" href="favicon.ico">
<style>
*{box-sizing:border-box;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif}
body{margin:0;background:#0b0c0f;color:#e6e6e6}
.header{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#13151a;border-bottom:1px solid #2a2f3a}
.container{max-width:1100px;margin:20px auto;padding:0 16px}
.card{background:#13151a;border:1px solid #2a2f3a;border-radius:12px;padding:16px;margin-bottom:16px}
.row{display:grid;grid-template-columns:360px 1fr;gap:12px;align-items:center;margin:10px 0}
input[type="text"],input[type="number"],textarea{width:100%;padding:10px;border:1px solid #2a2f3a;border-radius:10px;background:#1a1d24;color:#e6e6e6}
.btn{background:#7aa2f7;color:#fff;border:none;border-radius:10px;padding:10px 14px;cursor:pointer}
.btn.secondary{background:transparent;color:#e6e6e6;border:1px solid #2a2f3a}
.hint{color:#9aa0a6;font-size:12px}
.ok{color:#22c55e}.err{color:#ef4444}
.box{padding:12px;border:1px dashed #2a2f3a;border-radius:10px;background:#1a1d24;margin:10px 0}
details.group summary{cursor:pointer;display:flex;align-items:center;gap:8px}
.info{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:#1a1d24;border:1px solid #2a2f3a;color:#9aa0a6;font-size:12px;margin-left:6px}
.desc{color:#9aa0a6;font-size:12px;margin:4px 0 12px 0}
.path{color:#9aa0a6;font-size:12px;margin-left:auto}
.path-small{color:#9aa0a6;font-size:11px;margin-top:4px}
textarea{min-height:240px}
@media (max-width:900px){.row{grid-template-columns:1fr}}
</style>
</head>
<body>
  <div class="header">
    <h1 style="margin:0;font-size:18px">LSS Bot Config Service</h1>
    <span class="hint">Nichts wird serverseitig gespeichert</span>
  </div>

  <div class="container">
    <?php foreach($errors as $e): ?>
      <div class="card"><div class="err"><strong>Fehler:</strong> <?=h($e)?></div></div>
    <?php endforeach; ?>
    <?php foreach($notes as $n): ?>
      <div class="card"><div class="ok"><strong>OK:</strong> <?=h($n)?></div></div>
    <?php endforeach; ?>

    <div class="card">
      <h2 style="margin:0 0 10px 0">Upload (optional)</h2>
      <form method="post" enctype="multipart/form-data">
        <input type="file" name="upload" accept=".json,.mscc,application/json">
        <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap">
          <button class="btn" name="action" value="upload">Upload &amp; laden</button>
          <button class="btn secondary" name="action" value="download" onclick="return syncBeforeDownload()">Aktuellen Stand herunterladen</button>
        </div>
        <textarea name="state_raw" id="state_a" hidden><?=h($current_raw)?></textarea>
      </form>
      <p class="hint">Upload wird nur im aktuellen Vorgang genutzt – keine Dateien/Backups auf dem Server.</p>
    </div>

    <div class="card">
      <h2 style="margin:0 0 10px 0">Formular-Editor (alle Felder)</h2>
      <form method="post">
        <?php
          echo render_fields_recursive($current_data, '', $current_data, $current_labels);
        ?>
        <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap">
          <button class="btn" name="action" value="save_form">Änderungen anwenden</button>
          <button class="btn secondary" name="action" value="download" onclick="return syncBeforeDownload()">Herunterladen</button>
          <a class="btn secondary" href="#raw">Zum Roh-Editor ⤵</a>
        </div>
        <textarea name="state_raw" id="state_b" hidden><?=h($current_raw)?></textarea>
      </form>
      <p class="hint">Alle Keys änderbar. Werte werden typgerecht (bool/int/float/string) übernommen.</p>
    </div>

    <div class="card" id="raw">
      <h2 style="margin:0 0 10px 0">Roh-Editor</h2>
      <form method="post">
        <textarea name="raw" id="raw_area" spellcheck="false"><?=h($current_raw)?></textarea>
        <div style="margin-top:10px;display:flex;gap:10px;flex-wrap:wrap">
          <button class="btn" name="action" value="save_raw" onclick="document.getElementById('state_c').value=document.getElementById('raw_area').value;">Roh übernehmen</button>
          <button class="btn secondary" name="action" value="download" onclick="return syncBeforeDownload()">Herunterladen</button>
        </div>
        <textarea name="state_raw" id="state_c" hidden><?=h($current_raw)?></textarea>
      </form>
      <p class="hint">Kommentare (//, /*…*/) & nachgestellte Kommas werden für die Validierung entfernt.</p>
    </div>
  </div>

<script>
function syncBeforeDownload(){
  var raw = document.getElementById('raw_area');
  ['state_a','state_b','state_c'].forEach(function(id){
    var el = document.getElementById(id);
    if(el && raw) el.value = raw.value;
  });
  return true;
}
</script>
</body>
</html>
