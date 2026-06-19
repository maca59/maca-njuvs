# Trin for trin: tilslut Facebook og Instagram til maca Njuvs

**Version:** 1.0.15  
**Gælder for:** **maca Njuvs** — WordPress-plugin til nyheder, begivenheder, iCal-kalender og deling på sociale medier

Denne guide hjælper dig med at publicere nyheder og begivenheder fra **maca Njuvs** til din **Facebook-side** og din **Instagram Business-konto**. maca Njuvs hoster ikke din Meta-login centralt — du opretter din egen app i [Meta for Developers](https://developers.facebook.com/) og forbinder den til dit WordPress-website.

---

## Før du starter

1. **maca Njuvs installeret og aktivt** — pluginet skal findes under *Plugins* i WordPress.
2. **Publicering aktiveret** — *maca Njuvs → Settings* → markér *Enable maca Njuvs* og gem.
3. **Facebook-side** — du skal være administrator på den side, du vil publicere til.
4. **Instagram Business eller Creator** — kontoen skal være **knyttet til Facebook-siden** (i Meta Business Suite eller Instagram-appen under *Profil → Rediger profil → Sider*).
5. **HTTPS** — WordPress **webadresse** skal starte med `https://`.
6. **Meta Developer-konto** — gratis konto på [developers.facebook.com](https://developers.facebook.com/).

> **Tip:** Åbn *maca Njuvs → Social media* sammen med denne guide. Der ser du OAuth-redirect-URL og indtaster App ID og App Secret.

---

## Oversigt

| Trin | Hvor | Hvad |
|------|------|------|
| 1 | Meta for Developers | Opret app |
| 2 | Meta-app | Vælg korrekte **anvendelsestilfælde** (Page + Instagram) |
| 3 | Meta-app | App-domæner, privatlivspolitik, OAuth-redirect |
| 4 | Meta-app | Tilladelser |
| 5 | WordPress (maca Njuvs) | App ID + App Secret |
| 6 | WordPress (maca Njuvs) | Tilslut Facebook og vælg side |
| 7 | WordPress (maca Njuvs) | Test publicering |
| 8 | WordPress (maca Njuvs) | Publicer nyheder og begivenheder |

---

## Trin 1 — Opret Meta-app

1. Gå til [developers.facebook.com/apps](https://developers.facebook.com/apps) og klik **Opret app**.
2. Vælg **Business** som apptype, hvis du bliver spurgt.
3. Giv appen et tydeligt navn, f.eks. *Jeres firmanavn – maca Njuvs*.
4. Vælg jeres **Business Manager**-portefølje, hvis I bliver spurgt.
5. Opret appen og notér **App ID** (vist øverst i app-dashboardet).

---

## Trin 2 — Tilføj de rigtige anvendelsestilfælde

Dette trin er vigtigt — forkerte anvendelsestilfælde giver tilladelses- og OAuth-fejl.

1. I app-dashboardet: **Anvendelsestilfælde** → **Tilføj anvendelsestilfælde**.
2. Tilføj **Administrer alt på jeres Page**.
3. Tilføj **Administrer beskeder og indhold på Instagram**.

**Brug ikke** kun det generiske *Facebook Login* — det er ikke nok til at publicere til Page og Instagram.

---

## Trin 3 — App-indstillinger

### App-domæner

Under **App-indstillinger → Grundlæggende**:

- **App-domæner:** jeres webdomæne uden `https://`, f.eks. `{{SITE_DOMAIN}}`.
- **URL til privatlivspolitik:** en offentlig HTTPS-side med privatlivspolitik (krævet af Meta). Eksempel: `https://maca.se/policy/`
- **Website:** jeres web-URL, f.eks. `https://{{SITE_DOMAIN}}`

Gem ændringerne.

### OAuth redirect URI

1. Gå til **Anvendelsestilfælde → Facebook Login for Business** (eller det Login-produkt, der er knyttet til appen).
2. Under **Indstillinger** / **Valid OAuth Redirect URIs** indsæt **præcis** den URL, der vises i WordPress under *maca Njuvs → Social media → OAuth redirect URI*:

```
{{OAUTH_REDIRECT_URI}}
```

3. Gem. URL'en skal matche **tegn for tegn** — ingen ekstra skråstreg, ingen `http` hvis siden bruger `https`.

---

## Trin 4 — Tilladelser

maca Njuvs har brug for disse tilladelser, når I forbinder (Meta kan vise dem ved login):

| Tilladelse | Formål |
|------------|--------|
| `pages_show_list` | Liste sider, I administrerer |
| `pages_manage_posts` | Publicere til Facebook-siden |
| `pages_read_engagement` | Læse grundlæggende sideinfo |
| `instagram_basic` | Knytte Instagram Business-konto |
| `instagram_content_publish` | Publicere til Instagram |
| `business_management` | Knytte Page og Instagram i Business Manager |

I **udviklingstilstand** virker dette for app-administratorer og testere. Til produktion kan Meta kræve **App Review** og **Business Verification** — følg Metas tjekliste i Developer Console.

> **Webhooks er ikke påkrævet** for at publicere nyheder og begivenheder fra maca Njuvs.

---

## Trin 5 — Indtast App ID og App Secret i WordPress

1. Gå til *maca Njuvs → Social media*.
2. Under **Meta app credentials**:
   - **App ID** — fra Meta Developer Console
   - **App Secret** — fra *Indstillinger → Grundlæggende* (klik *Vis*)
3. **Test image URL** (valgfrit men anbefalet) — et offentligt HTTPS-billede til Instagram-test (Instagram kræver altid billede).
4. Klik **Save Meta settings**.

---

## Trin 6 — Tilslut Facebook og vælg side

1. Klik **Connect Facebook & Instagram**.
2. Log ind med en konto, der er **administrator** på Facebook-siden.
3. Godkend de tilladelser, Meta viser.
4. Vælg **hvilken Facebook-side** der skal forbindes (hvis I har flere).
5. Bekræft — I bør se sidenavn og evt. `@instagram-brugernavn` under **Connection**.

Hvis Instagram ikke vises: kontrollér at Instagram-kontoen er **Business/Creator** og **knyttet til netop den Facebook-side**.

---

## Trin 7 — Test publicering

1. Udfyld **Test image URL**, hvis den mangler (offentligt HTTPS-billede).
2. Klik **Test publish** på fanen *Social media*.
3. Kontrollér at et testopslag vises på Facebook-siden (og Instagram hvis tilsluttet).
4. Ved fejl: se **Publish log** længere nede på samme fane — Meta API-fejlbeskeder gemmes der.

---

## Trin 8 — Publicer nyheder og begivenheder

1. Opret eller rediger en **nyhed** eller **begivenhed** under *maca Njuvs → News* eller *maca Njuvs → Events*.
2. Sæt status til **Published** (eller planlagt med dato, der allerede er passeret).
3. Under **Publishing** — markér **Facebook** og/eller **Instagram**.
4. **Instagram kræver billede** på nyheden eller begivenheden.
5. Gem — publicering sker med det samme, hvis Facebook er tilsluttet.

**Tekst på sociale medier:** titel plus indhold (eller uddrag hvis udfyldt) sendes som billedtekst. På Instagram kan teksten ligge under billedet — tryk *mere* for at læse alt.

**Publicer igen:** hvis et opslag allerede er publiceret, kan I markere *Publish again to Facebook/Instagram* ved redigering af nyheden.

---

## Fejlfinding

| Problem | Løsning |
|---------|---------|
| *Invalid OAuth Redirect URI* | Sammenlign URL i Meta med den præcise værdi under *maca Njuvs → Social media* (trin 3). |
| *Invalid Scopes* | Kontrollér anvendelsestilfælde i trin 2 — tilføj Page + Instagram. |
| Omdirigering til wp-admin / blank side | Opdater maca Njuvs til seneste version (OAuth bruger REST-URL ovenfor). |
| Instagram mangler efter tilslutning | Knyt Instagram Business til Facebook-siden i Meta Business Suite. |
| Kun billede, ingen tekst | Udfyld **Content** på nyheden; brug *Publish again* hvis allerede publiceret. |
| *Instagram requires an image* | Upload billede på nyheden eller begivenheden. |
| Token udløbet | Tilslut igen via *Connect Facebook & Instagram*; maca Njuvs forsøger at forny token automatisk. |
| Meta-forbindelse virkede på andet site | maca Njuvs har **egen** Meta-forbindelse pr. website — konfigurer appen og tilslut igen under *maca Njuvs → Social media*. |

---

## Sikkerhed og privatliv

- **App Secret** gemmes krypteret i WordPress — del det ikke offentligt.
- **Page access token** gemmes krypteret på jeres server.
- maca.se **hoster ikke** jeres OAuth — al trafik går mellem jeres website og Meta.
- maca Njuvs gemmer sociale medier-indstillinger i egne tabeller (`wp_maca_njuvs_*`).
- Oplys i jeres privatlivspolitik, at opslag kan publiceres på sociale medier, når I bruger funktionen.

---

## Hurtig reference i WordPress

| Sted | Formål |
|------|--------|
| *maca Njuvs → Settings* | Aktiver publicering, iCal-URL, **link til denne guide** |
| *maca Njuvs → Social media* | App ID/Secret, forbindelse, test, log |
| *maca Njuvs → News / Events* | Markér Facebook/Instagram ved publicering |
