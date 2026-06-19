# Steg for steg: koble Facebook og Instagram til maca Njuvs

**Versjon:** 1.0.15  
**Gjelder for:** **maca Njuvs** — WordPress-utvidelse for nyheter, arrangementer, iCal-kalender og deling på sosiale medier

Denne veiledningen hjelper deg med å publisere nyheter og arrangementer fra **maca Njuvs** til din **Facebook-side** og din **Instagram Business-konto**. maca Njuvs lagrer ikke Meta-innloggingen din sentralt — du oppretter din egen app i [Meta for Developers](https://developers.facebook.com/) og kobler den til WordPress-nettstedet ditt.

---

## Før du starter

1. **maca Njuvs installert og aktivt** — utvidelsen skal finnes under *Utvidelser* i WordPress.
2. **Publisering aktivert** — *maca Njuvs → Settings* → kryss av for *Enable maca Njuvs* og lagre.
3. **Facebook-side** — du må være administrator på siden du vil publisere til.
4. **Instagram Business eller Creator** — kontoen må være **koblet til Facebook-siden** (i Meta Business Suite eller Instagram-appen under *Profil → Rediger profil → Sider*).
5. **HTTPS** — WordPress **nettadresse** må starte med `https://`.
6. **Meta Developer-konto** — gratis konto på [developers.facebook.com](https://developers.facebook.com/).

> **Tips:** Åpne *maca Njuvs → Social media* sammen med denne veiledningen. Der ser du OAuth-redirect-URL og fyller inn App ID og App Secret.

---

## Oversikt

| Steg | Hvor | Hva |
|------|------|-----|
| 1 | Meta for Developers | Opprett app |
| 2 | Meta-app | Velg riktige **bruksområder** (Page + Instagram) |
| 3 | Meta-app | App-domener, personvernerklæring, OAuth-redirect |
| 4 | Meta-app | Tillatelser |
| 5 | WordPress (maca Njuvs) | App ID + App Secret |
| 6 | WordPress (maca Njuvs) | Koble Facebook og velg side |
| 7 | WordPress (maca Njuvs) | Test publisering |
| 8 | WordPress (maca Njuvs) | Publiser nyheter og arrangementer |

---

## Steg 1 — Opprett Meta-app

1. Gå til [developers.facebook.com/apps](https://developers.facebook.com/apps) og klikk **Opprett app**.
2. Velg **Business** som apptype hvis du blir spurt.
3. Gi appen et tydelig navn, f.eks. *Firmanavnet ditt – maca Njuvs*.
4. Velg **Business Manager**-porteføljen din hvis du blir spurt.
5. Opprett appen og noter **App ID** (vises øverst i app-dashboardet).

---

## Steg 2 — Legg til riktige bruksområder

Dette steget er viktig — feil bruksområder gir tillatelses- og OAuth-feil.

1. I app-dashboardet: **Bruksområder** → **Legg til bruksområder**.
2. Legg til **Administrer alt på Page-en din**.
3. Legg til **Administrer meldinger og innhold på Instagram**.

**Ikke bruk** bare generisk *Facebook Login* — det er ikke nok til å publisere til Page og Instagram.

---

## Steg 3 — App-innstillinger

### App-domener

Under **App-innstillinger → Grunnleggende**:

- **App-domener:** nettstedets domene uten `https://`, f.eks. `{{SITE_DOMAIN}}`.
- **URL til personvernerklæring:** en offentlig HTTPS-side med personvernerklæring (påkrevd av Meta). Eksempel: `https://maca.se/policy/`
- **Nettsted:** nettstedets URL, f.eks. `https://{{SITE_DOMAIN}}`

Lagre endringene.

### OAuth redirect URI

1. Gå til **Bruksområder → Facebook Login for Business** (eller Login-produktet knyttet til appen).
2. Under **Innstillinger** / **Valid OAuth Redirect URIs** limer du inn **nøyaktig** URL-en som vises i WordPress under *maca Njuvs → Social media → OAuth redirect URI*:

```
{{OAUTH_REDIRECT_URI}}
```

3. Lagre. URL-en må matche **tegn for tegn** — ingen ekstra skråstrek, ingen `http` hvis nettstedet bruker `https`.

---

## Steg 4 — Tillatelser

maca Njuvs trenger disse tillatelsene når du kobler til (Meta kan vise dem ved innlogging):

| Tillatelse | Formål |
|------------|--------|
| `pages_show_list` | Liste sider du administrerer |
| `pages_manage_posts` | Publisere til Facebook-siden |
| `pages_read_engagement` | Lese grunnleggende sideinfo |
| `instagram_basic` | Koble Instagram Business-konto |
| `instagram_content_publish` | Publisere til Instagram |
| `business_management` | Koble Page og Instagram i Business Manager |

I **utviklingsmodus** fungerer dette for app-administratorer og testere. For produksjon kan Meta kreve **App Review** og **Business Verification** — følg Metas sjekkliste i Developer Console.

> **Webhooks er ikke påkrevd** for å publisere nyheter og arrangementer fra maca Njuvs.

---

## Steg 5 — Fyll inn App ID og App Secret i WordPress

1. Gå til *maca Njuvs → Social media*.
2. Under **Meta app credentials**:
   - **App ID** — fra Meta Developer Console
   - **App Secret** — fra *Innstillinger → Grunnleggende* (klikk *Vis*)
3. **Test image URL** (valgfritt men anbefalt) — et offentlig HTTPS-bilde for Instagram-test (Instagram krever alltid bilde).
4. Klikk **Save Meta settings**.

---

## Steg 6 — Koble Facebook og velg side

1. Klikk **Connect Facebook & Instagram**.
2. Logg inn med en konto som er **administrator** på Facebook-siden.
3. Godta tillatelsene Meta viser.
4. Velg **hvilken Facebook-side** som skal kobles (hvis du har flere).
5. Bekreft — du skal se sidenavn og evt. `@instagram-brukernavn` under **Connection**.

Hvis Instagram ikke vises: kontroller at Instagram-kontoen er **Business/Creator** og **koblet til akkurat den Facebook-siden**.

---

## Steg 7 — Test publisering

1. Fyll inn **Test image URL** hvis den mangler (offentlig HTTPS-bilde).
2. Klikk **Test publish** på fanen *Social media*.
3. Kontroller at et testinnlegg vises på Facebook-siden (og Instagram hvis koblet).
4. Ved feil: se **Publish log** lenger ned på samme fane — Meta API-feilmeldinger lagres der.

---

## Steg 8 — Publiser nyheter og arrangementer

1. Opprett eller rediger en **nyhet** eller et **arrangement** under *maca Njuvs → News* eller *maca Njuvs → Events*.
2. Sett status til **Published** (eller planlagt med dato som allerede er passert).
3. Under **Publishing** — kryss av for **Facebook** og/eller **Instagram**.
4. **Instagram krever bilde** på nyheten eller arrangementet.
5. Lagre — publisering skjer umiddelbart hvis Facebook er koblet til.

**Tekst på sosiale medier:** tittel pluss innhold (eller utdrag hvis fylt inn) sendes som bildetekst. På Instagram kan teksten ligge under bildet — trykk *mer* for å lese alt.

**Publiser på nytt:** hvis et innlegg allerede er publisert, kan du kryss av for *Publish again to Facebook/Instagram* når du redigerer nyheten.

---

## Feilsøking

| Problem | Løsning |
|---------|---------|
| *Invalid OAuth Redirect URI* | Sammenlign URL i Meta med nøyaktig verdi under *maca Njuvs → Social media* (steg 3). |
| *Invalid Scopes* | Kontroller bruksområder i steg 2 — legg til Page + Instagram. |
| Omdirigering til wp-admin / blank side | Oppdater maca Njuvs til nyeste versjon (OAuth bruker REST-URL ovenfor). |
| Instagram mangler etter tilkobling | Koble Instagram Business til Facebook-siden i Meta Business Suite. |
| Kun bilde, ingen tekst | Fyll inn **Content** på nyheten; bruk *Publish again* hvis allerede publisert. |
| *Instagram requires an image* | Last opp bilde på nyheten eller arrangementet. |
| Token utløpt | Koble til igjen via *Connect Facebook & Instagram*; maca Njuvs prøver å fornye token automatisk. |
| Meta-kobling fungerte på annet nettsted | maca Njuvs har **egen** Meta-kobling per nettsted — konfigurer appen og koble til igjen under *maca Njuvs → Social media*. |

---

## Sikkerhet og personvern

- **App Secret** lagres kryptert i WordPress — ikke del det offentlig.
- **Page access token** lagres kryptert på serveren din.
- maca.se **hoster ikke** OAuth-en din — all trafikk går mellom nettstedet ditt og Meta.
- maca Njuvs lagrer innstillinger for sosiale medier i egne tabeller (`wp_maca_njuvs_*`).
- Informer besøkende i nettstedets **personvernerklæring** om at innlegg kan publiseres på sosiale medier når du bruker funksjonen.

---

## Hurtigreferanse i WordPress

| Sted | Formål |
|------|--------|
| *maca Njuvs → Settings* | Aktiver publisering, iCal-URL, **lenke til denne veiledningen** |
| *maca Njuvs → Social media* | App ID/Secret, tilkobling, test, logg |
| *maca Njuvs → News / Events* | Kryss av Facebook/Instagram ved publisering |
