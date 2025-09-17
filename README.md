# M-C-B_Config_Editor

Ein schlanker, **zustandsloser** Web-Editor für `config.mscc`-Dateien (MissionChief / Leitstellenspiel Umfeld).  
Läuft als Single-File-PHP-App (**`site.php`** oder **`index.php`**), **ohne Login**, speichert **nichts** auf dem Server, und erlaubt die Bearbeitung **aller Felder** über ein Webformular **oder** direkt im Roh-JSON.

> **Privacy by design:** Uploads werden nur zur Laufzeit im Request verarbeitet. Es findet **keine** serverseitige Persistenz statt.

---

## Features

- ✅ **Kein Login**, keine DB, kein Session-State  
- ✅ **Formular-Editor**: rekursiv für **alle Felder** (Bool/Int/Float/String mit Typ-Konvertierung)  
- ✅ **Roh-Editor**: direktes Bearbeiten des JSON  
- ✅ **Fehlertolerant**: akzeptiert `//`- und `/* … */`-Kommentare sowie **trailing commas**  
- ✅ **Download** der aktuellen In-Memory-Version als `config.mscc`  
- ✅ **Info-Tooltips** zu bekannten Feldern (konfigurierbar im Code)  
- ✅ **Labels im Code** anpassbar (Anzeigenamen ändern, JSON-Keys bleiben unverändert)  
- ✅ **Stateless**: keine Speicherung von Nutzerinhalten auf dem Server

---

## Quickstart

1. **PHP** ≥ 7.2 (empfohlen: 8.1+) bereitstellen.
2. Repo deployen (z. B. auf einen vHost / Webspace).
3. **Eine** der folgenden Dateien als Entry-Point nutzen:
   - `site.php` (aktuell im Repo) **oder**
   - gleiches Skript als `index.php` ablegen.
4. Domain aufrufen → der Editor ist sofort nutzbar.

> Bei Nginx/Apache sicherstellen, dass PHP ausgeführt wird und `index.php` (falls genutzt) in `index`/`DirectoryIndex` enthalten ist.

---

## Nutzung

1. **Upload (optional):** Eigene `config.mscc` auswählen und „Upload & laden“.  
2. **Formular-Editor:** Alle Felder typgerecht bearbeiten.  
3. **Roh-Editor:** JSON frei ändern (Kommentare/Trailing Commas sind ok).  
4. **Download:** „Herunterladen“ gibt die aktuelle `config.mscc` zurück.

> Auf dem Server bleibt **nichts** zurück. Zwischenstände werden nur per Hidden-Feldern in Formularen transportiert.

---

## Konfiguration im Code

### Labels (sichtbare Feldnamen)

Anzeigenamen werden **im PHP-Code** gepflegt (Array wie `$LABELS_DEFAULT`).  
Beispiel:

```php
$LABELS_DEFAULT['environment.country']  = 'Shard / Land';
$LABELS_DEFAULT['environment.username'] = 'Login-Name';
$LABELS_DEFAULT['filter.share.message.scheme'] = 'Nachrichten-Vorlage';
