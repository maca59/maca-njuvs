# maca Njuvs — brukerveiledning

**Versjon:** 1.0.15  
**Gjelder for:** **maca Njuvs** — WordPress-utvidelse for nyheter, arrangementer, Gutenberg-blokker, iCal-kalender og valgfri deling til Facebook og Instagram.

maca Njuvs lar deg opprette og publisere nyheter og arrangementer direkte i WordPress. Innhold vises på nettstedet via Gutenberg-blokker (eller shortcodes) og kan deles på sosiale medier hvis en Meta-app er koblet til.

---

## Admin-faner

| Fane | Hva du gjør her |
|------|-----------------|
| **News** | Opprett, rediger og administrer nyheter |
| **Events** | Opprett, rediger og administrer arrangementer (inkl. gjentakende serier) |
| **Social media** | Koble Meta-app, Facebook-side og Instagram (krever spesiell tilgang) |
| **Settings** | Aktiver/deaktiver modulen, iCal-URL-er, lenke til sosial guide |
| **Import** | Importer eksisterende WordPress-innlegg som nyheter |
| **Guide** | Denne veiledningen — blokker, innstillinger og funksjoner |

---

## Nyheter

Under *maca Njuvs → News* oppretter og redigerer du nyheter som vises på nettstedet.

### Felter

| Felt | Beskrivelse |
|------|-------------|
| **Title** | Hovedoverskrift — vises på nettstedet, i banneret og først i tekst for sosiale medier |
| **Excerpt** | Kort sammendrag for lister og banner. Inngår i sosial tekst etter overskriften |
| **Content** | Full tekst. Klikk åpner hele innholdet (popup eller utvidet visning) |
| **Image** | Valgfritt bilde fra mediebiblioteket |
| **Status** | Utkast, Planlagt, Publisert eller Arkivert |
| **Publish at** | Valgfri dato/tid. Fremtidig dato med status Publisert blir Planlagt til da |
| **Expires at** | Valgfritt — elementet skjules automatisk etter denne datoen |
| **Publishing** | Avkrysningsbokser for nettsted, Facebook og Instagram |

### Statuser

- **Draft** — vises ikke på nettstedet
- **Scheduled** — publiseres automatisk på angitt tidspunkt
- **Published** — vises på nettstedet (når modulen er aktivert)
- **Archived** — skjult fra nettstedet, men lagret i admin

### Bildetips

- Bruk **Select image** — ikke lim inn bilder i utdrag eller innholdsfelt.
- Komprimer store bilder (helst under 500 KB). Utvidelsen varsler hvis bildet er stort; svært store filer kan gi feilen *Please reduce the amount of data* ved lagring.

---

## Arrangementer

Under *maca Njuvs → Events* administrerer du kommende og gjentakende arrangementer.

### Felter

| Felt | Beskrivelse |
|------|-------------|
| **Title** | Arrangementets navn |
| **Description** | Detaljert tekst |
| **Location** | Hvor arrangementet holdes |
| **Image** | Valgfritt bilde |
| **Price** | Valgfritt — vises på nettstedet hvis angitt |
| **All day** | Kryss av hvis arrangementet varer hele dagen |
| **Start / End** | Dato og tid |
| **Recurrence** | Ingen, Daglig, Ukentlig eller Månedlig med intervall, ukedager og sluttdato eller antall forekomster |
| **Active** | Vis på nettstedet |
| **Publishing** | Nettsted, Facebook og Instagram |

### Unntak i gjentakende serier

For gjentakende arrangementer kan du legge til **unntak** ved redigering — avlys eller flytt en enkelt dato uten å endre hele serien.

---

## Gutenberg-blokker

Legg til blokker fra kategorien **maca Njuvs** i blokkseditoren (søk etter *maca News* eller *maca Events*).

### maca News

Viser publiserte nyheter fra maca Njuvs.

| Innstilling | Beskrivelse |
|-------------|-------------|
| **Layout** | Liste, In page (tabell/kolonne), Fixed panel venstre/høyre eller Top banner |
| **Number of items** | 1–20 nyheter |
| **Scrolling ticker** | (Banner) Kontinuerlig horisontal rulling |
| **Show image** | (Liste) Vis miniatyrbilde |
| **Show date** | Vis publiseringsdato |
| **Show excerpt** | Vis kort sammendrag |

**Layout-tips:**

- **List** — standardvisning med valgfritt bilde
- **In page** — blir der du plasserer blokken, f.eks. i tabeller og kolonner
- **Fixed panel** — fast på siden ved scrolling på desktop; på mobil vises den sist på siden. Klikk åpner hele nyheten i popup
- **Top banner** — bånd øverst. Bruk maks ett banner-blokk per side

### maca Events

Viser kommende arrangementer.

| Innstilling | Beskrivelse |
|-------------|-------------|
| **View** | Liste eller Månedskalender |
| **Number of events** | 1–30 (listevisning) |
| **Show image** | (Listevisning) |
| **Show location** | (Listevisning) |
| **Week starts on Monday** | (Månedskalender) |
| **Show calendar subscription** | Lenker for å abonnere på iCal-feedet |

---

## Shortcodes

Uten blokkseditoren kan samme innhold vises med shortcodes:

### Nyheter

```
[maca_njuvs_news limit="5" layout="list" show_image="1" show_date="1" show_excerpt="1" banner_scroll="1"]
```

| Attributt | Verdier | Standard |
|-----------|---------|----------|
| `limit` | 1–50 | 5 |
| `layout` | `list`, `embedded`, `sidebar-left`, `sidebar-right`, `banner` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_date` | `1` / `0` | `1` |
| `show_excerpt` | `1` / `0` | `1` |
| `banner_scroll` | `1` / `0` | `1` |

### Arrangementer

```
[maca_njuvs_events limit="10" view="list" show_image="1" show_location="1" show_subscribe="1"]
```

| Attributt | Verdier | Standard |
|-----------|---------|----------|
| `limit` | 1–50 | 10 |
| `view` | `list`, `month` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_location` | `1` / `0` | `1` |
| `show_subscribe` | `1` / `0` | `1` |

### Kalenderabonnement

```
[maca_njuvs_calendar_subscribe]
```

Viser lenker for å abonnere på arrangementskalenderen i kalenderapper.

---

## Innstillinger

Under *maca Njuvs → Settings*:

| Innstilling | Beskrivelse |
|-------------|-------------|
| **Enable maca Njuvs** | Hovedbryter — når avslått vises ikke innhold på nettstedet eller i blokker |
| **iCal feed URL** | Offentlig feed for kalenderapper: `{{ICAL_URL}}` |
| **Subscribe URL** | webcal-lenke for Apple Kalender m.fl.: `{{WEBCAL_URL}}` |

> **Tips:** Hvis iCal-feedet gir 404, lagre én gang under *Innstillinger → Permalenker* i WordPress.

### Facebook og Instagram

Kobling til sosiale medier administreres under *Social media*. En separat steg-for-steg-guide finnes via knappen *Setup guide: Facebook & Instagram* på innstillingssiden.

---

## Import

Under *maca Njuvs → Import* kan eksisterende WordPress-innlegg kopieres til maca Njuvs som nyheter.

| Alternativ | Beskrivelse |
|------------|-------------|
| **Content type** | Innlegg eller side |
| **Category** | Valgfritt filter (kun innlegg) |
| **Skip already imported** | Unngå duplikater |

Originale innlegg slettes ikke — importen oppretter nye nyheter i maca Njuvs.

---

## Andre funksjoner

- **iCal-kalender** — arrangementer eksporteres til en offentlig feed som oppdateres ved endringer
- **Planlagt publisering** — nyheter kan gå live på angitt tid uten manuell handling
- **Utløpsdato** — nyheter kan skjules automatisk
- **Gjentakende arrangementer** — daglige, ukentlige og månedlige serier med unntak
- **Sosial publisering** — valgfri deling til Facebook Page og Instagram Business ved lagring (Meta-app kreves)

---

## Hurtigstart

1. Aktiver maca Njuvs under *Settings*
2. Opprett minst én nyhet eller ett arrangement
3. Legg til blokkene **maca News** og **maca Events** på en side
4. (Valgfritt) Koble Facebook/Instagram under *Social media*
5. (Valgfritt) Del iCal-URL-en slik at gjester kan abonnere på kalenderen

---

## Bruksvilkår

Ved å bruke **maca Njuvs** godtar du følgende vilkår:

1. **Lisens** — Utvidelsen distribueres under GNU General Public License v2 eller senere (GPL v2+). Du kan bruke, endre og distribuere utvidelsen i henhold til lisensvilkårene.
2. **Innholdsansvar** — Som nettstedeier er du ansvarlig for alt innhold (nyheter, arrangementer, bilder og tekster) du publiserer via utvidelsen, på nettstedet og via tilkoblede sosiale medier.
3. **Tredjepartstjenester** — Funksjoner med Facebook, Instagram og Meta Graph API er underlagt hver tjenestes vilkår. Du må følge Metas plattformregler og ha nødvendige rettigheter til delt innhold.
4. **Ingen garanti** — maca Njuvs leveres som den er uten uttrykkelig eller underforstått garanti. Maca Development er ikke ansvarlig for nedetid, datatap eller skader ved bruk av utvidelsen.
5. **Ansvarsbegrensning** — I den grad loven tillater det, er Maca Development ikke ansvarlig for indirekte skader, tapt fortjeneste eller datatap som følge av utvidelsen eller integrerte tjenester.
6. **Oppdateringer** — Funksjoner kan endres eller fjernes i fremtidige versjoner. Vi anbefaler sikkerhetskopiering før oppdateringer.

## Personvernerklæring

maca Njuvs behandler data lokalt på ditt WordPress-nettsted. Som nettstedeier er du behandlingsansvarlig for besøks- og innholdsdata i henhold til gjeldende lov, f.eks. GDPR.

### Hvilke data lagres

| Data | Hvor | Formål |
|------|------|--------|
| Nyheter og arrangementer | WordPress-database (egne tabeller) | Publisering på nettstedet og i blokker |
| Bilde-URL-er og tekster | Samme database | Visning og sosial deling |
| Meta App ID, tokens m.m. | WordPress-innstillinger (kryptert der det gjelder) | Facebook/Instagram-publisering |
| Sosial publiseringslogg | WordPress-database | Feilsøking og status i admin |
| Importerte innleggs-ID-er | Innleggsmetadata | Unngå duplikater ved import |

### Hvilke data deles eksternt

- **Ingen data sendes til Maca Development** som standard når du bruker utvidelsen.
- **iCal-feedet** (`{{ICAL_URL}}`) er offentlig — tittel, tid, sted og beskrivelse for aktive arrangementer kan leses av alle med lenken.
- **Sosial publisering** sender innhold og bilder til Meta (Facebook/Instagram) i henhold til dine innstillinger og Metas API.

### Lagring og sletting

- Data blir værende etter avinstallering med mindre konstanten `MACA_NJUVS_UNINSTALL_DROP_DATA` er satt til `true` før avinstallering.
- Du kan slette nyheter, arrangementer og koblinger til sosiale medier når som helst i admin.

### Dine forpliktelser

- Informer besøkende i nettstedets **personvernerklæring** om iCal-feed, eventuelle sporingsteknologier (via andre utvidelser) og sosial publisering.
- Oppgi en offentlig personvernerklæring-URL i Meta-appen hvis du bruker Facebook/Instagram-kobling (Meta-krav).

### Kontakt

Support og spørsmål om utvidelsen: [maca.se](https://maca.se/)
