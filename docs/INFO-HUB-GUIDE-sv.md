# maca Njuvs — användarguide

**Version:** 1.0.15  
**Gäller:** **maca Njuvs** — WordPress-plugin för nyheter, evenemang, Gutenberg-block, iCal-kalender och valfri delning till Facebook och Instagram.

maca Njuvs låter er skapa och publicera nyheter och evenemang direkt i WordPress. Innehållet visas på webbplatsen via Gutenberg-block (eller kortkoder) och kan delas till sociala medier om ni har kopplat en Meta-app.

---

## Adminflikar

| Flik | Vad du gör här |
|------|----------------|
| **Nyheter** | Skapa, redigera och hantera nyheter |
| **Evenemang** | Skapa, redigera och hantera evenemang (inkl. återkommande serier) |
| **Sociala medier** | Koppla Meta-app, Facebook-sida och Instagram (kräver särskild behörighet) |
| **Inställningar** | Aktivera/inaktivera modulen, iCal-URL:er, länk till social guide |
| **Import** | Importera befintliga WordPress-inlägg som nyheter |
| **Guide** | Denna guide — block, inställningar och funktioner |

---

## Nyheter

Under *maca Njuvs → Nyheter* skapar och redigerar ni nyheter som visas på webbplatsen.

### Fält

| Fält | Beskrivning |
|------|-------------|
| **Rubrik** | Huvudrubrik — visas på webbplatsen, i banner och först i texten vid delning till sociala medier |
| **Ingress** | Kort sammanfattning för listor och banner. Ingår i social text efter rubriken |
| **Innehåll** | Fullständig text. Klick på en nyhet öppnar hela innehållet (popup eller expanderad vy) |
| **Bild** | Valfri bild via mediabiblioteket |
| **Status** | Utkast, Schemalagd, Publicerad eller Arkiverad |
| **Publicera vid** | Valfritt datum/tid. Framtida datum med status Publicerad blir Schemalagd tills tiden passerat |
| **Utgår vid** | Valfritt — nyheten döljs automatiskt efter detta datum |
| **Publicering** | Kryssrutor för webbplats, Facebook och Instagram |

### Statusar

- **Utkast** — syns inte på webbplatsen
- **Schemalagd** — publiceras automatiskt vid angiven tid
- **Publicerad** — visas på webbplatsen (om modulen är aktiverad)
- **Arkiverad** — dold från webbplatsen men sparad i admin

---

## Evenemang

Under *maca Njuvs → Evenemang* hanterar ni kommande och återkommande evenemang.

### Fält

| Fält | Beskrivning |
|------|-------------|
| **Rubrik** | Evenemangets namn |
| **Beskrivning** | Detaljerad text |
| **Plats** | Var evenemanget hålls |
| **Bild** | Valfri bild |
| **Pris** | Valfritt — visas på webbplatsen om angivet |
| **Heldag** | Kryssa i om evenemanget pågår hela dagen |
| **Start / Slut** | Datum och tid |
| **Återkommande** | Ingen, Dagligen, Veckovis eller Månadsvis med intervall, veckodagar och slutdatum eller antal tillfällen |
| **Aktiv** | Visa på webbplatsen |
| **Bordsbokning** | Visa boka-bord-knapp (om maca Menulist-bokning finns på webbplatsen) |
| **Publicering** | Webbplats, Facebook och Instagram |

### Undantag i återkommande serier

När ett evenemang återkommer kan ni under redigering lägga till **undantag** — avboka eller flytta ett enskilt tillfälle utan att ändra hela serien.

---

## Gutenberg-block

Lägg till blocken från kategorin **maca Njuvs** i blockredigeraren (sök efter *maca News* eller *maca Events*).

### maca News

Visar publicerade nyheter från maca Njuvs.

| Inställning | Beskrivning |
|-------------|-------------|
| **Layout** | Lista, I sidan (tabell/kolumn), Fast panel vänster/höger, eller Toppbanner |
| **Antal** | 1–20 nyheter |
| **Rullande ticker** | (Banner) Kontinuerlig horisontell rullning |
| **Visa bild** | (Lista) Visa miniatyrbild |
| **Visa datum** | Visa publiceringsdatum |
| **Visa ingress** | Visa kort sammanfattning |

**Layouttips:**

- **Lista** — standardvy med valfri bild
- **I sidan** — stannar där du placerar blocket, t.ex. i tabeller och kolumner
- **Fast panel** — fast på sidan vid scroll på desktop; på mobil visas den sist på sidan. Klick öppnar hela nyheten i popup
- **Toppbanner** — band högst upp. Använd högst ett banner-block per sida

### maca Events

Visar kommande evenemang.

| Inställning | Beskrivning |
|-------------|-------------|
| **Vy** | Lista eller Månadskalender |
| **Antal evenemang** | 1–30 (listvy) |
| **Visa bild** | (Listvy) |
| **Visa plats** | (Listvy) |
| **Veckan börjar på måndag** | (Månadskalender) |
| **Visa kalenderprenumeration** | Länkar för att prenumerera på iCal-flödet |

---

## Kortkoder

Om ni inte använder blockredigeraren kan samma innehåll visas med kortkoder:

### Nyheter

```
[maca_njuvs_news limit="5" layout="list" show_image="1" show_date="1" show_excerpt="1" banner_scroll="1"]
```

| Attribut | Värden | Standard |
|----------|--------|----------|
| `limit` | 1–50 | 5 |
| `layout` | `list`, `embedded`, `sidebar-left`, `sidebar-right`, `banner` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_date` | `1` / `0` | `1` |
| `show_excerpt` | `1` / `0` | `1` |
| `banner_scroll` | `1` / `0` | `1` |

### Evenemang

```
[maca_njuvs_events limit="10" view="list" show_image="1" show_location="1" show_subscribe="1"]
```

| Attribut | Värden | Standard |
|----------|--------|----------|
| `limit` | 1–50 | 10 |
| `view` | `list`, `month` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_location` | `1` / `0` | `1` |
| `show_subscribe` | `1` / `0` | `1` |

### Kalenderprenumeration

```
[maca_njuvs_calendar_subscribe]
```

Visar länkar för att prenumerera på evenemangskalendern i kalenderappar.

---

## Inställningar

Under *maca Njuvs → Inställningar*:

| Inställning | Beskrivning |
|-------------|-------------|
| **Aktivera maca Njuvs** | Masterbrytare — när avstängd visas inget innehåll på webbplatsen eller i block |
| **iCal-flödes-URL** | Publik feed för kalenderappar: `{{ICAL_URL}}` |
| **Prenumerations-URL** | webcal-länk för Apple Kalender m.fl.: `{{WEBCAL_URL}}` |

> **Tips:** Om iCal-flödet ger 404, gå till *Inställningar → Permalänkar* i WordPress och spara en gång.

### Facebook och Instagram

Koppling till sociala medier sker under *Sociala medier*. En separat steg-för-steg-guide finns via knappen *Installationsguide: Facebook och Instagram* på inställningssidan.

---

## Import

Under *maca Njuvs → Import* kan befintliga WordPress-inlägg kopieras till maca Njuvs som nyheter.

| Alternativ | Beskrivning |
|------------|-------------|
| **Innehållstyp** | Inlägg eller sida |
| **Kategori** | Valfritt filter (endast inlägg) |
| **Hoppa över redan importerade** | Undvik dubbletter |

Originalinläggen raderas inte — importen skapar nya nyheter i maca Njuvs.

---

## Övriga funktioner

- **iCal-kalender** — evenemang exporteras automatiskt till ett publikt flöde som uppdateras vid ändringar
- **Schemalagd publicering** — nyheter kan publiceras vid angiven tid utan manuellt ingrepp
- **Utgångsdatum** — nyheter kan döljas automatiskt
- **Återkommande evenemang** — dagliga, veckovisa och månatliga serier med undantag
- **Social publicering** — valfri delning till Facebook Page och Instagram Business vid sparning (kräver Meta-app)
- **Bordsbokning** — evenemang kan visa boka-bord-knapp om maca Menulist finns installerat

---

## Snabbstart

1. Aktivera maca Njuvs under *Inställningar*
2. Skapa minst en nyhet eller ett evenemang
3. Lägg till blocken **maca News** och **maca Events** på en sida
4. (Valfritt) Koppla Facebook/Instagram via *Sociala medier*
5. (Valfritt) Dela iCal-URL:en så gäster kan prenumerera på kalendern

---

## Användarvillkor

Genom att använda **maca Njuvs** godkänner du följande villkor:

1. **Licens** — Pluginet distribueras under GNU General Public License v2 eller senare (GPL v2+). Du får använda, ändra och distribuera pluginet enligt licensvillkoren.
2. **Ansvar för innehåll** — Du som webbplatsägare är ansvarig för allt innehåll (nyheter, evenemang, bilder och texter) som du publicerar via pluginet, på webbplatsen och via kopplade sociala medier.
3. **Tredjepartstjänster** — Funktioner som Facebook, Instagram och Meta Graph API styrs av respektive tjänsts villkor. Du måste följa Metas plattformsregler och ha nödvändiga rättigheter till innehåll du delar.
4. **Ingen garanti** — maca Njuvs tillhandahålls i befintligt skick utan uttrycklig eller underförstådd garanti. Maca Development ansvarar inte för driftstopp, dataförlust eller skador som uppstår genom användning av pluginet.
5. **Ansvarsbegränsning** — I den utsträckning lagen tillåter ansvarar Maca Development inte för indirekta skador, utebliven vinst eller förlust av data till följd av pluginet eller integrerade tjänster.
6. **Uppdateringar** — Funktioner kan ändras eller tas bort i framtida versioner. Vi rekommenderar säkerhetskopiering innan uppdateringar.

## Integritetspolicy

maca Njuvs behandlar data lokalt på din WordPress-webbplats. Som webbplatsägare är du personuppgiftsansvarig för besökar- och innehållsdata enligt tillämplig lag, t.ex. GDPR.

### Vilken data lagras

| Data | Var | Syfte |
|------|-----|-------|
| Nyheter och evenemang | WordPress-databas (egna tabeller) | Publicering på webbplatsen och i block |
| Bild-URL:er och texter | Samma databas | Visning och social delning |
| Meta App ID, tokens m.m. | WordPress-alternativ (krypterat där tillämpligt) | Facebook/Instagram-publicering |
| Social publiceringslogg | WordPress-databas | Felsökning och status i admin |
| Importerade inläggs-ID:n | Inläggsmetadata | Undvika dubbletter vid import |

### Vilken data delas externt

- **Ingen data skickas till Maca Development** som standard när du använder pluginet.
- **iCal-flödet** (`{{ICAL_URL}}`) är publikt — titel, tid, plats och beskrivning för aktiva evenemang kan läsas av alla som har länken.
- **Social publicering** skickar innehåll och bilder till Meta (Facebook/Instagram) enligt dina inställningar och Metas API.

### Lagring och radering

- Data finns kvar vid avinstallation om inte konstanten `MACA_NJUVS_UNINSTALL_DROP_DATA` är satt till `true` före avinstallation.
- Du kan radera nyheter, evenemang och kopplingar till sociala medier när som helst i admin.

### Dina skyldigheter

- Informera besökare i er webbplats **integritetspolicy** om iCal-flöde, eventuella spårningstekniker (via andra tillägg) och social publicering.
- Ange en publik integritetspolicy-URL i er Meta-app om ni använder Facebook/Instagram-koppling (krav från Meta).

### Kontakt

För support och frågor om pluginet: [maca.se](https://maca.se/)
