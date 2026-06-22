# maca Njuvs — brugervejledning

**Version:** 1.0.15  
**Gælder for:** **maca Njuvs** — WordPress-plugin til nyheder, begivenheder, Gutenberg-blokke, iCal-kalender og valgfri deling til Facebook og Instagram.

maca Njuvs lader dig oprette og publicere nyheder og begivenheder direkte i WordPress. Indhold vises på dit website via Gutenberg-blokke (eller shortcodes) og kan deles på sociale medier, hvis en Meta-app er tilkoblet.

---

## Admin-faner

| Fane | Hvad du gør her |
|------|-----------------|
| **News** | Opret, rediger og administrer nyheder |
| **Events** | Opret, rediger og administrer begivenheder (inkl. gentagne serier) |
| **Social media** | Tilslut Meta-app, Facebook-side og Instagram (kræver særlig tilladelse) |
| **Settings** | Aktiver/deaktiver modulet, iCal-URL'er, link til social guide |
| **Import** | Importer eksisterende WordPress-indlæg som nyheder |
| **Guide** | Denne guide — blokke, indstillinger og funktioner |

---

## Nyheder

Under *maca Njuvs → News* opretter og redigerer du nyheder, der vises på dit website.

### Felter

| Felt | Beskrivelse |
|------|-------------|
| **Title** | Hovedoverskrift — vises på websitet, i banneret og først i tekst til sociale medier |
| **Excerpt** | Kort resumé til lister og banner. Indgår i social tekst efter overskriften |
| **Content** | Fuld tekst. Klik åbner hele indholdet (popup eller udvidet visning) |
| **Image** | Valgfrit billede fra mediebiblioteket |
| **Status** | Kladde, Planlagt, Publiceret eller Arkiveret |
| **Publish at** | Valgfri dato/tid. Fremtidig dato med status Publiceret bliver Planlagt indtil da |
| **Expires at** | Valgfrit — elementet skjules automatisk efter denne dato |
| **Publishing** | Afkrydsningsfelter for website, Facebook og Instagram |

### Statusser

- **Draft** — vises ikke på websitet
- **Scheduled** — publiceres automatisk på det angivne tidspunkt
- **Published** — vises på websitet (når modulet er aktiveret)
- **Archived** — skjult fra websitet men gemt i admin

### Billedtips

- Brug **Select image** — indsæt ikke billeder i uddrag eller indholdsfelter.
- Komprimer store billeder (helst under 500 KB). Pluginet advarer, hvis billedet er stort; meget store filer kan give fejlen *Please reduce the amount of data* ved gemning.

---

## Begivenheder

Under *maca Njuvs → Events* administrerer du kommende og gentagne begivenheder.

### Felter

| Felt | Beskrivelse |
|------|-------------|
| **Title** | Begivenhedens navn |
| **Description** | Detaljeret tekst |
| **Location** | Hvor begivenheden afholdes |
| **Image** | Valgfrit billede |
| **Price** | Valgfrit — vises på websitet hvis angivet |
| **All day** | Marker hvis begivenheden varer hele dagen |
| **Start / End** | Dato og tid |
| **Recurrence** | Ingen, Dagligt, Ugentligt eller Månedligt med interval, ugedage og slutdato eller antal forekomster |
| **Active** | Vis på websitet |
| **Publishing** | Website, Facebook og Instagram |

### Undtagelser i gentagne serier

Ved gentagne begivenheder kan du tilføje **undtagelser** under redigering — aflys eller flyt en enkelt dato uden at ændre hele serien.

---

## Gutenberg-blokke

Tilføj blokke fra kategorien **maca Njuvs** i blokeditoren (søg efter *maca News* eller *maca Events*).

### maca News

Viser publicerede nyheder fra maca Njuvs.

| Indstilling | Beskrivelse |
|-------------|-------------|
| **Layout** | Liste, In page (tabel/kolonne), Fixed panel venstre/højre eller Top banner |
| **Number of items** | 1–20 nyheder |
| **Scrolling ticker** | (Banner) Kontinuerlig vandret rulning |
| **Show image** | (Liste) Vis miniature |
| **Show date** | Vis publiceringsdato |
| **Show excerpt** | Vis kort resumé |

**Layout-tips:**

- **List** — standardvisning med valgfrit billede
- **In page** — forbliver hvor du placerer blokken, f.eks. i tabeller og kolonner
- **Fixed panel** — fast på siden ved scroll på desktop; på mobil vises den sidst på siden. Klik åbner hele nyheden i popup
- **Top banner** — bånd øverst. Brug højst ét banner-blok per side

### maca Events

Viser kommende begivenheder.

| Indstilling | Beskrivelse |
|-------------|-------------|
| **View** | Liste eller Månedskalender |
| **Number of events** | 1–30 (listevisning) |
| **Show image** | (Listevisning) |
| **Show location** | (Listevisning) |
| **Week starts on Monday** | (Månedskalender) |
| **Show calendar subscription** | Links til at abonnere på iCal-feedet |

---

## Shortcodes

Uden blokeditoren kan samme indhold vises med shortcodes:

### Nyheder

```
[maca_njuvs_news limit="5" layout="list" show_image="1" show_date="1" show_excerpt="1" banner_scroll="1"]
```

| Attribut | Værdier | Standard |
|----------|---------|----------|
| `limit` | 1–50 | 5 |
| `layout` | `list`, `embedded`, `sidebar-left`, `sidebar-right`, `banner` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_date` | `1` / `0` | `1` |
| `show_excerpt` | `1` / `0` | `1` |
| `banner_scroll` | `1` / `0` | `1` |

### Begivenheder

```
[maca_njuvs_events limit="10" view="list" show_image="1" show_location="1" show_subscribe="1"]
```

| Attribut | Værdier | Standard |
|----------|---------|----------|
| `limit` | 1–50 | 10 |
| `view` | `list`, `month` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_location` | `1` / `0` | `1` |
| `show_subscribe` | `1` / `0` | `1` |

### Kalenderabonnement

```
[maca_njuvs_calendar_subscribe]
```

Viser links til at abonnere på begivenhedskalenderen i kalenderapps.

---

## Indstillinger

Under *maca Njuvs → Settings*:

| Indstilling | Beskrivelse |
|-------------|-------------|
| **Enable maca Njuvs** | Hovedkontakt — når slået fra vises intet indhold på websitet eller i blokke |
| **iCal feed URL** | Offentligt feed til kalenderapps: `{{ICAL_URL}}` |
| **Subscribe URL** | webcal-link til Apple Kalender m.fl.: `{{WEBCAL_URL}}` |

> **Tip:** Hvis iCal-feedet giver 404, gem én gang under *Indstillinger → Permalinks* i WordPress.

### Facebook og Instagram

Forbindelse til sociale medier administreres under *Social media*. En separat trin-for-trin-guide findes via knappen *Setup guide: Facebook & Instagram* på indstillingssiden.

---

## Import

Under *maca Njuvs → Import* kan eksisterende WordPress-indlæg kopieres til maca Njuvs som nyheder.

| Mulighed | Beskrivelse |
|----------|-------------|
| **Content type** | Indlæg eller side |
| **Category** | Valgfilt filter (kun indlæg) |
| **Skip already imported** | Undgå dubletter |

Originale indlæg slettes ikke — importen opretter nye nyheder i maca Njuvs.

---

## Andre funktioner

- **iCal-kalender** — begivenheder eksporteres til et offentligt feed, der opdateres ved ændringer
- **Planlagt publicering** — nyheder kan gå live på angivet tid uden manuel handling
- **Udløbsdato** — nyheder kan skjules automatisk
- **Gentagne begivenheder** — daglige, ugentlige og månedlige serier med undtagelser
- **Social publicering** — valgfri deling til Facebook Page og Instagram Business ved gem (Meta-app påkrævet)

---

## Hurtig start

1. Aktiver maca Njuvs under *Settings*
2. Opret mindst én nyhed eller begivenhed
3. Tilføj blokkene **maca News** og **maca Events** på en side
4. (Valgfrit) Tilslut Facebook/Instagram under *Social media*
5. (Valgfrit) Del iCal-URL'en, så gæster kan abonnere på kalenderen

---

## Vilkår for brug

Ved at bruge **maca Njuvs** accepterer du følgende vilkår:

1. **Licens** — Pluginet distribueres under GNU General Public License v2 eller senere (GPL v2+). Du må bruge, ændre og distribuere pluginet i henhold til licensvilkårene.
2. **Indholdsansvar** — Som website-ejer er du ansvarlig for alt indhold (nyheder, begivenheder, billeder og tekster), du publicerer via pluginet, på dit website og via tilkoblede sociale medier.
3. **Tredjepartstjenester** — Funktioner med Facebook, Instagram og Meta Graph API er underlagt hver tjenestes vilkår. Du skal overholde Metas platformregler og have nødvendige rettigheder til delt indhold.
4. **Ingen garanti** — maca Njuvs leveres som den er uden udtrykkelig eller underforstået garanti. Maca Development er ikke ansvarlig for nedetid, datatab eller skader ved brug af pluginet.
5. **Ansvarsbegrænsning** — I det omfang loven tillader det, er Maca Development ikke ansvarlig for indirekte skader, tabt fortjeneste eller datatab som følge af pluginet eller integrerede tjenester.
6. **Opdateringer** — Funktioner kan ændres eller fjernes i fremtidige versioner. Vi anbefaler backup før opdateringer.

## Privatlivspolitik

maca Njuvs behandler data lokalt på dit WordPress-website. Som website-ejer er du dataansvarlig for besøgs- og indholdsdata i henhold til gældende lov, f.eks. GDPR.

### Hvilke data gemmes

| Data | Hvor | Formål |
|------|------|--------|
| Nyheder og begivenheder | WordPress-database (egne tabeller) | Publicering på websitet og i blokke |
| Billede-URL'er og tekster | Samme database | Visning og social deling |
| Meta App ID, tokens m.m. | WordPress-indstillinger (krypteret hvor relevant) | Facebook/Instagram-publicering |
| Social publiceringslog | WordPress-database | Fejlfinding og status i admin |
| Importerede indlægs-ID'er | Indlægsmetadata | Undgå dubletter ved import |

### Hvilke data deles eksternt

- **Ingen data sendes til Maca Development** som standard, når du bruger pluginet.
- **iCal-feedet** (`{{ICAL_URL}}`) er offentligt — titel, tid, sted og beskrivelse for aktive begivenheder kan læses af alle med linket.
- **Social publicering** sender indhold og billeder til Meta (Facebook/Instagram) i henhold til dine indstillinger og Metas API.

### Opbevaring og sletning

- Data forbliver efter afinstallation, medmindre konstanten `MACA_NJUVS_UNINSTALL_DROP_DATA` er sat til `true` før afinstallation.
- Du kan slette nyheder, begivenheder og forbindelser til sociale medier når som helst i admin.

### Dine forpligtelser

- Oplys besøgende i jeres websites **privatlivspolitik** om iCal-feed, eventuelle sporingsteknologier (via andre plugins) og social publicering.
- Angiv en offentlig privatlivspolitik-URL i jeres Meta-app, hvis I bruger Facebook/Instagram-forbindelse (Meta-krav).

### Kontakt

Support og spørgsmål om pluginet: [maca.se](https://maca.se/)
