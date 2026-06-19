# Schritt für Schritt: Facebook und Instagram mit maca Njuvs verbinden

**Version:** 1.0.15  
**Gilt für:** **maca Njuvs** — WordPress-Plugin für Nachrichten, Veranstaltungen, iCal-Kalender und Social Sharing

Mit dieser Anleitung veröffentlichen Sie Nachrichten und Veranstaltungen aus **maca Njuvs** auf Ihrer **Facebook-Seite** und Ihrem **Instagram Business-Konto**. maca Njuvs speichert Ihre Meta-Anmeldung nicht zentral — Sie erstellen eine eigene App in [Meta for Developers](https://developers.facebook.com/) und verbinden sie mit Ihrer WordPress-Website.

---

## Bevor Sie beginnen

1. **maca Njuvs installiert und aktiviert** — das Plugin sollte unter *Plugins* in WordPress erscheinen.
2. **Veröffentlichung aktiviert** — *maca Njuvs → Settings* → *Enable maca Njuvs* aktivieren und speichern.
3. **Facebook-Seite** — Sie müssen Administrator der Seite sein, auf der Sie veröffentlichen möchten.
4. **Instagram Business oder Creator** — das Konto muss **mit der Facebook-Seite verknüpft** sein (in Meta Business Suite oder in der Instagram-App unter *Profil → Profil bearbeiten → Seiten*).
5. **HTTPS** — die WordPress-**Website-Adresse** muss mit `https://` beginnen.
6. **Meta Developer-Konto** — kostenloses Konto auf [developers.facebook.com](https://developers.facebook.com/).

> **Tipp:** Öffnen Sie *maca Njuvs → Social media* parallel zu dieser Anleitung. Dort sehen Sie die OAuth-Redirect-URL und tragen App ID und App Secret ein.

---

## Übersicht

| Schritt | Wo | Was |
|---------|-----|-----|
| 1 | Meta for Developers | App erstellen |
| 2 | Meta-App | Richtige **Anwendungsfälle** wählen (Page + Instagram) |
| 3 | Meta-App | App-Domains, Datenschutzerklärung, OAuth-Redirect |
| 4 | Meta-App | Berechtigungen |
| 5 | WordPress (maca Njuvs) | App ID + App Secret |
| 6 | WordPress (maca Njuvs) | Facebook verbinden und Seite wählen |
| 7 | WordPress (maca Njuvs) | Veröffentlichung testen |
| 8 | WordPress (maca Njuvs) | Nachrichten und Veranstaltungen veröffentlichen |

---

## Schritt 1 — Meta-App erstellen

1. Gehen Sie zu [developers.facebook.com/apps](https://developers.facebook.com/apps) und klicken Sie **App erstellen**.
2. Wählen Sie bei Aufforderung **Business** als App-Typ.
3. Geben Sie der App einen klaren Namen, z. B. *Ihr Firmenname – maca Njuvs*.
4. Wählen Sie Ihr **Business Manager**-Portfolio, falls gefragt.
5. Erstellen Sie die App und notieren Sie die **App-ID** (oben im App-Dashboard).

---

## Schritt 2 — Richtige Anwendungsfälle hinzufügen

Dieser Schritt ist wichtig — falsche Anwendungsfälle verursachen Berechtigungs- und OAuth-Fehler.

1. Im App-Dashboard: **Anwendungsfälle** → **Anwendungsfälle hinzufügen**.
2. Fügen Sie **Alles auf Ihrer Page verwalten** hinzu.
3. Fügen Sie **Nachrichten und Inhalte auf Instagram verwalten** hinzu.

**Verwenden Sie nicht** allein das generische *Facebook Login* — das reicht nicht zum Veröffentlichen auf Page und Instagram.

---

## Schritt 3 — App-Einstellungen

### App-Domains

Unter **App-Einstellungen → Basis**:

- **App-Domains:** Ihre Website-Domain ohne `https://`, z. B. `{{SITE_DOMAIN}}`.
- **URL der Datenschutzerklärung:** eine öffentliche HTTPS-Seite mit Datenschutzerklärung (von Meta erforderlich). Beispiel: `https://maca.se/policy/`
- **Website:** Ihre Website-URL, z. B. `https://{{SITE_DOMAIN}}`

Änderungen speichern.

### OAuth-Redirect-URI

1. Gehen Sie zu **Anwendungsfälle → Facebook Login for Business** (oder das mit der App verknüpfte Login-Produkt).
2. Unter **Einstellungen** / **Gültige OAuth-Redirect-URIs** fügen Sie **exakt** die URL ein, die in WordPress unter *maca Njuvs → Social media → OAuth redirect URI* angezeigt wird:

```
{{OAUTH_REDIRECT_URI}}
```

3. Speichern. Die URL muss **Zeichen für Zeichen** übereinstimmen — kein zusätzlicher Schrägstrich, kein `http`, wenn die Website `https` nutzt.

---

## Schritt 4 — Berechtigungen

maca Njuvs benötigt diese Berechtigungen bei der Verbindung (Meta kann sie bei der Anmeldung anzeigen):

| Berechtigung | Zweck |
|--------------|-------|
| `pages_show_list` | Seiten auflisten, die Sie verwalten |
| `pages_manage_posts` | Auf der Facebook-Seite veröffentlichen |
| `pages_read_engagement` | Grundlegende Seiteninfos lesen |
| `instagram_basic` | Instagram Business-Konto verknüpfen |
| `instagram_content_publish` | Auf Instagram veröffentlichen |
| `business_management` | Page und Instagram im Business Manager verknüpfen |

Im **Entwicklungsmodus** funktioniert dies für App-Administratoren und Tester. Für den Produktivbetrieb kann Meta **App Review** und **Business Verification** verlangen — folgen Sie der Meta-Checkliste in der Developer Console.

> **Webhooks sind nicht erforderlich**, um Nachrichten und Veranstaltungen aus maca Njuvs zu veröffentlichen.

---

## Schritt 5 — App ID und App Secret in WordPress eintragen

1. Gehen Sie zu *maca Njuvs → Social media*.
2. Unter **Meta app credentials**:
   - **App ID** — aus der Meta Developer Console
   - **App Secret** — unter *Einstellungen → Basis* (*Show* klicken)
3. **Test image URL** (optional, empfohlen) — ein öffentliches HTTPS-Bild für Instagram-Tests (Instagram erfordert immer ein Bild).
4. Klicken Sie **Save Meta settings**.

---

## Schritt 6 — Facebook verbinden und Seite wählen

1. Klicken Sie **Connect Facebook & Instagram**.
2. Melden Sie sich mit einem Konto an, das **Administrator** der Facebook-Seite ist.
3. Genehmigen Sie die von Meta angezeigten Berechtigungen.
4. Wählen Sie **welche Facebook-Seite** verbunden werden soll (bei mehreren Seiten).
5. Bestätigen — unter **Connection** sollten Seitenname und ggf. `@instagram-benutzername` erscheinen.

Wenn Instagram nicht erscheint: prüfen Sie, ob das Instagram-Konto **Business/Creator** ist und **mit dieser Facebook-Seite verknüpft**.

---

## Schritt 7 — Veröffentlichung testen

1. Tragen Sie **Test image URL** ein, falls leer (öffentliches HTTPS-Bild).
2. Klicken Sie **Test publish** auf dem Tab *Social media*.
3. Prüfen Sie, ob ein Testbeitrag auf der Facebook-Seite erscheint (und auf Instagram, wenn verbunden).
4. Bei Fehlern: siehe **Publish log** weiter unten auf demselben Tab — Meta-API-Fehlermeldungen werden dort gespeichert.

---

## Schritt 8 — Nachrichten und Veranstaltungen veröffentlichen

1. Erstellen oder bearbeiten Sie eine **Nachricht** oder **Veranstaltung** unter *maca Njuvs → News* bzw. *maca Njuvs → Events*.
2. Setzen Sie den Status auf **Published** (oder geplant mit bereits vergangenem Datum).
3. Unter **Publishing** — aktivieren Sie **Facebook** und/oder **Instagram**.
4. **Instagram erfordert ein Bild** bei der Nachricht oder Veranstaltung.
5. Speichern — die Veröffentlichung erfolgt sofort, wenn Facebook verbunden ist.

**Social-Text:** Titel plus Inhalt (oder Auszug, falls ausgefüllt) wird als Beschriftung gesendet. Auf Instagram kann der Text unter dem Bild stehen — tippen Sie auf *mehr*, um alles zu lesen.

**Erneut veröffentlichen:** wenn bereits veröffentlicht, können Sie beim Bearbeiten *Publish again to Facebook/Instagram* aktivieren.

---

## Fehlerbehebung

| Problem | Lösung |
|---------|--------|
| *Invalid OAuth Redirect URI* | URL in Meta mit dem exakten Wert unter *maca Njuvs → Social media* vergleichen (Schritt 3). |
| *Invalid Scopes* | Anwendungsfälle in Schritt 2 prüfen — Page + Instagram hinzufügen. |
| Weiterleitung zu wp-admin / leere Seite | maca Njuvs auf die neueste Version aktualisieren (OAuth nutzt die REST-URL oben). |
| Instagram fehlt nach Verbindung | Instagram Business mit der Facebook-Seite in Meta Business Suite verknüpfen. |
| Nur Bild, kein Text | **Content** bei der Nachricht ausfüllen; *Publish again* nutzen, wenn bereits veröffentlicht. |
| *Instagram requires an image* | Bild bei Nachricht oder Veranstaltung hochladen. |
| Token abgelaufen | Erneut über *Connect Facebook & Instagram* verbinden; maca Njuvs versucht die Token-Aktualisierung automatisch. |
| Meta-Verbindung funktionierte auf anderer Website | maca Njuvs speichert eine **eigene** Meta-Verbindung pro Website — App konfigurieren und erneut unter *maca Njuvs → Social media* verbinden. |

---

## Sicherheit und Datenschutz

- **App Secret** wird verschlüsselt in WordPress gespeichert — nicht öffentlich teilen.
- **Page access token** wird verschlüsselt auf Ihrem Server gespeichert.
- maca.se **hostet Ihr OAuth nicht** — der gesamte Datenverkehr läuft zwischen Ihrer Website und Meta.
- maca Njuvs speichert Social-Media-Einstellungen in eigenen Tabellen (`wp_maca_njuvs_*`).
- Erwähnen Sie in Ihrer Datenschutzerklärung, dass Beiträge bei Nutzung dieser Funktion in sozialen Medien veröffentlicht werden können.

---

## Kurzreferenz in WordPress

| Ort | Zweck |
|-----|-------|
| *maca Njuvs → Settings* | Veröffentlichung aktivieren, iCal-URL, **Link zu dieser Anleitung** |
| *maca Njuvs → Social media* | App ID/Secret, Verbindung, Test, Log |
| *maca Njuvs → News / Events* | Facebook/Instagram beim Veröffentlichen aktivieren |
