# maca Njuvs — Benutzerhandbuch

**Version:** 1.0.15  
**Gilt für:** **maca Njuvs** — WordPress-Plugin für Nachrichten, Veranstaltungen, Gutenberg-Blöcke, iCal-Kalender und optionale Freigabe auf Facebook und Instagram.

Mit maca Njuvs erstellen und veröffentlichen Sie Nachrichten und Veranstaltungen direkt in WordPress. Inhalte erscheinen auf Ihrer Website über Gutenberg-Blöcke (oder Shortcodes) und können bei verbundener Meta-App in sozialen Medien geteilt werden.

---

## Admin-Tabs

| Tab | Was Sie hier tun |
|-----|------------------|
| **News** | Nachrichten erstellen, bearbeiten und verwalten |
| **Events** | Veranstaltungen erstellen, bearbeiten und verwalten (inkl. wiederkehrender Serien) |
| **Social media** | Meta-App, Facebook-Seite und Instagram verbinden (erfordert besondere Berechtigung) |
| **Settings** | Modul aktivieren/deaktivieren, iCal-URLs, Link zur Social-Media-Anleitung |
| **Import** | Bestehende WordPress-Beiträge als Nachrichten importieren |
| **Guide** | Dieses Handbuch — Blöcke, Einstellungen und Funktionen |

---

## Nachrichten

Unter *maca Njuvs → News* erstellen und bearbeiten Sie Nachrichten für Ihre Website.

### Felder

| Feld | Beschreibung |
|------|--------------|
| **Title** | Hauptüberschrift — auf der Website, im Banner und zuerst in Social-Media-Texten |
| **Excerpt** | Kurze Zusammenfassung für Listen und Banner. Gehört nach der Überschrift zum Social-Text |
| **Content** | Vollständiger Text. Klick öffnet den gesamten Inhalt (Popup oder erweiterte Ansicht) |
| **Image** | Optionales Bild aus der Mediathek |
| **Status** | Entwurf, Geplant, Veröffentlicht oder Archiviert |
| **Publish at** | Optionales Datum/Uhrzeit. Zukünftiges Datum mit Status Veröffentlicht wird bis dahin Geplant |
| **Expires at** | Optional — Eintrag wird danach automatisch ausgeblendet |
| **Publishing** | Kontrollkästchen für Website, Facebook und Instagram |

### Status

- **Draft** — nicht auf der Website sichtbar
- **Scheduled** — wird automatisch zur festgelegten Zeit veröffentlicht
- **Published** — auf der Website sichtbar (wenn das Modul aktiviert ist)
- **Archived** — von der Website ausgeblendet, in der Verwaltung gespeichert

### Bildtipps

- Verwenden Sie **Select image** — fügen Sie keine Bilder in Auszug oder Inhalt ein.
- Komprimieren Sie große Bilder (möglichst unter 500 KB). Das Plugin warnt bei großen Dateien; sehr große Bilder können beim Speichern die Meldung *Please reduce the amount of data* auslösen.

---

## Veranstaltungen

Unter *maca Njuvs → Events* verwalten Sie anstehende und wiederkehrende Veranstaltungen.

### Felder

| Feld | Beschreibung |
|------|--------------|
| **Title** | Name der Veranstaltung |
| **Description** | Ausführlicher Text |
| **Location** | Veranstaltungsort |
| **Image** | Optionales Bild |
| **Price** | Optional — auf der Website angezeigt, wenn gesetzt |
| **All day** | Aktivieren, wenn die Veranstaltung ganztägig ist |
| **Start / End** | Datum und Uhrzeit |
| **Recurrence** | Keine, Täglich, Wöchentlich oder Monatlich mit Intervall, Wochentagen und Enddatum oder Anzahl |
| **Active** | Auf der Website anzeigen |
| **Publishing** | Website, Facebook und Instagram |

### Ausnahmen bei wiederkehrenden Serien

Bei wiederkehrenden Veranstaltungen können Sie beim Bearbeiten **Ausnahmen** hinzufügen — einzelne Termine absagen oder verschieben, ohne die ganze Serie zu ändern.

---

## Gutenberg-Blöcke

Fügen Sie Blöcke aus der Kategorie **maca Njuvs** im Block-Editor hinzu (suchen Sie nach *maca News* oder *maca Events*).

### maca News

Zeigt veröffentlichte Nachrichten aus maca Njuvs.

| Einstellung | Beschreibung |
|-------------|--------------|
| **Layout** | Liste, In page (Tabelle/Spalte), Fixed panel links/rechts oder Top banner |
| **Number of items** | 1–20 Nachrichten |
| **Scrolling ticker** | (Banner) Kontinuierliches horizontales Scrollen |
| **Show image** | (Liste) Vorschaubild anzeigen |
| **Show date** | Veröffentlichungsdatum anzeigen |
| **Show excerpt** | Kurze Zusammenfassung anzeigen |

**Layout-Tipps:**

- **List** — Standardansicht mit optionalem Bild
- **In page** — bleibt dort, wo Sie den Block platzieren, z. B. in Tabellen und Spalten
- **Fixed panel** — auf dem Desktop beim Scrollen fixiert; auf Mobilgeräten am Ende der Seite. Klick öffnet den vollständigen Beitrag im Popup
- **Top banner** — Band oben. Maximal ein Banner-Block pro Seite

### maca Events

Zeigt anstehende Veranstaltungen.

| Einstellung | Beschreibung |
|-------------|--------------|
| **View** | Liste oder Monatskalender |
| **Number of events** | 1–30 (Listenansicht) |
| **Show image** | (Listenansicht) |
| **Show location** | (Listenansicht) |
| **Week starts on Monday** | (Monatskalender) |
| **Show calendar subscription** | Links zum Abonnieren des iCal-Feeds |

---

## Shortcodes

Ohne Block-Editor können dieselben Inhalte per Shortcode angezeigt werden:

### Nachrichten

```
[maca_njuvs_news limit="5" layout="list" show_image="1" show_date="1" show_excerpt="1" banner_scroll="1"]
```

| Attribut | Werte | Standard |
|----------|-------|----------|
| `limit` | 1–50 | 5 |
| `layout` | `list`, `embedded`, `sidebar-left`, `sidebar-right`, `banner` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_date` | `1` / `0` | `1` |
| `show_excerpt` | `1` / `0` | `1` |
| `banner_scroll` | `1` / `0` | `1` |

### Veranstaltungen

```
[maca_njuvs_events limit="10" view="list" show_image="1" show_location="1" show_subscribe="1"]
```

| Attribut | Werte | Standard |
|----------|-------|----------|
| `limit` | 1–50 | 10 |
| `view` | `list`, `month` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_location` | `1` / `0` | `1` |
| `show_subscribe` | `1` / `0` | `1` |

### Kalender-Abonnement

```
[maca_njuvs_calendar_subscribe]
```

Zeigt Links zum Abonnieren des Veranstaltungskalenders in Kalender-Apps.

---

## Einstellungen

Unter *maca Njuvs → Settings*:

| Einstellung | Beschreibung |
|-------------|--------------|
| **Enable maca Njuvs** | Hauptschalter — ausgeschaltet wird nichts auf der Website oder in Blöcken angezeigt |
| **iCal feed URL** | Öffentlicher Feed für Kalender-Apps: `{{ICAL_URL}}` |
| **Subscribe URL** | webcal-Link für Apple Kalender u. a.: `{{WEBCAL_URL}}` |

> **Tipp:** Gibt der iCal-Feed 404 zurück, speichern Sie einmal unter *Einstellungen → Permalinks* in WordPress.

### Facebook und Instagram

Die Social-Media-Verbindung wird unter *Social media* verwaltet. Eine separate Schritt-für-Schritt-Anleitung finden Sie über die Schaltfläche *Setup guide: Facebook & Instagram* auf der Einstellungsseite.

---

## Import

Unter *maca Njuvs → Import* können bestehende WordPress-Beiträge als Nachrichten in maca Njuvs kopiert werden.

| Option | Beschreibung |
|--------|--------------|
| **Content type** | Beitrag oder Seite |
| **Category** | Optionales Filter (nur Beiträge) |
| **Skip already imported** | Duplikate vermeiden |

Originalbeiträge werden nicht gelöscht — der Import erstellt neue Nachrichten in maca Njuvs.

---

## Weitere Funktionen

- **iCal-Kalender** — Veranstaltungen werden in einen öffentlichen Feed exportiert, der sich bei Änderungen aktualisiert
- **Geplante Veröffentlichung** — Nachrichten können zur festgelegten Zeit ohne manuelles Eingreifen live gehen
- **Ablaufdatum** — Nachrichten können automatisch ausgeblendet werden
- **Wiederkehrende Veranstaltungen** — tägliche, wöchentliche und monatliche Serien mit Ausnahmen
- **Social Publishing** — optionale Freigabe auf Facebook Page und Instagram Business beim Speichern (Meta-App erforderlich)

---

## Schnellstart

1. Aktivieren Sie maca Njuvs unter *Settings*
2. Erstellen Sie mindestens eine Nachricht oder Veranstaltung
3. Fügen Sie die Blöcke **maca News** und **maca Events** auf einer Seite ein
4. (Optional) Verbinden Sie Facebook/Instagram unter *Social media*
5. (Optional) Teilen Sie die iCal-URL, damit Gäste den Kalender abonnieren können

---

## Nutzungsbedingungen

Durch die Nutzung von **maca Njuvs** stimmen Sie folgenden Bedingungen zu:

1. **Lizenz** — Das Plugin wird unter der GNU General Public License v2 oder später (GPL v2+) vertrieben. Sie dürfen das Plugin gemäß den Lizenzbedingungen nutzen, ändern und weitergeben.
2. **Inhaltsverantwortung** — Als Website-Betreiber sind Sie für alle Inhalte (Nachrichten, Veranstaltungen, Bilder und Texte) verantwortlich, die Sie über das Plugin, auf Ihrer Website und über verbundene soziale Medien veröffentlichen.
3. **Drittanbieterdienste** — Funktionen mit Facebook, Instagram und der Meta Graph API unterliegen den jeweiligen Nutzungsbedingungen. Sie müssen die Meta-Richtlinien einhalten und die erforderlichen Rechte an geteilten Inhalten besitzen.
4. **Keine Garantie** — maca Njuvs wird ohne ausdrückliche oder stillschweigende Garantie bereitgestellt. Maca Development haftet nicht für Ausfallzeiten, Datenverlust oder Schäden durch die Nutzung des Plugins.
5. **Haftungsbeschränkung** — Soweit gesetzlich zulässig haftet Maca Development nicht für indirekte Schäden, entgangenen Gewinn oder Datenverlust durch das Plugin oder integrierte Dienste.
6. **Updates** — Funktionen können in zukünftigen Versionen geändert oder entfernt werden. Wir empfehlen ein Backup vor Updates.

## Datenschutzerklärung

maca Njuvs verarbeitet Daten lokal auf Ihrer WordPress-Website. Als Website-Betreiber sind Sie Verantwortlicher für Besucher- und Inhaltsdaten nach geltendem Recht, z. B. DSGVO.

### Welche Daten gespeichert werden

| Daten | Wo | Zweck |
|-------|-----|-------|
| Nachrichten und Veranstaltungen | WordPress-Datenbank (eigene Tabellen) | Veröffentlichung auf der Website und in Blöcken |
| Bild-URLs und Texte | Dieselbe Datenbank | Anzeige und Social Sharing |
| Meta App ID, Tokens usw. | WordPress-Optionen (wo zutreffend verschlüsselt) | Facebook/Instagram-Veröffentlichung |
| Social-Publish-Log | WordPress-Datenbank | Fehlersuche und Status in der Verwaltung |
| Importierte Beitrags-IDs | Beitrags-Metadaten | Duplikate beim Import vermeiden |

### Welche Daten extern geteilt werden

- **Standardmäßig werden keine Daten an Maca Development gesendet**, wenn Sie das Plugin nutzen.
- **Der iCal-Feed** (`{{ICAL_URL}}`) ist öffentlich — Titel, Zeit, Ort und Beschreibung aktiver Veranstaltungen können von jedem mit dem Link gelesen werden.
- **Social Publishing** sendet Inhalte und Bilder gemäß Ihrer Einstellungen und der Meta-API an Meta (Facebook/Instagram).

### Speicherung und Löschung

- Daten bleiben nach Deinstallation erhalten, sofern die Konstante `MACA_NJUVS_UNINSTALL_DROP_DATA` nicht vor der Deinstallation auf `true` gesetzt wird.
- Sie können Nachrichten, Veranstaltungen und Social-Media-Verbindungen jederzeit in der Verwaltung löschen.

### Ihre Pflichten

- Informieren Sie Besucher in der **Datenschutzerklärung** Ihrer Website über den iCal-Feed, etwaige Tracking-Technologien (über andere Plugins) und Social Publishing.
- Geben Sie in Ihrer Meta-App eine öffentliche Datenschutz-URL an, wenn Sie Facebook/Instagram verbinden (Meta-Anforderung).

### Kontakt

Support und Fragen zum Plugin: [maca.se](https://maca.se/)
