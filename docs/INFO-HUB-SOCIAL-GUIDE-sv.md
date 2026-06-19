# Steg-för-steg: Koppla Facebook och Instagram till maca Njuvs

**Version:** 1.0.15  
**Gäller:** **maca Njuvs** — WordPress-plugin för nyheter, evenemang, iCal-kalender och delning till sociala medier

Med den här guiden kan ni publicera nyheter och evenemang från **maca Njuvs** till er **Facebook-sida** och ert **Instagram Business-konto**. maca Njuvs lagrar inte era Meta-inloggningar centralt — ni skapar en egen app i [Meta for Developers](https://developers.facebook.com/) och kopplar den till er WordPress-webbplats.

---

## Innan du börjar

1. **maca Njuvs installerat och aktiverat** — pluginet ska finnas under *Tillägg* i WordPress.
2. **Publicering aktiverad** — *maca Njuvs → Inställningar* → kryssa i *Aktivera maca Njuvs* och spara.
3. **Facebook-sida** — ni måste vara administratör på sidan ni vill publicera till.
4. **Instagram Business eller Creator** — kontot ska vara **kopplat till Facebook-sidan** (i Meta Business Suite eller Instagram-appen under *Profil → Redigera profil → Sidor*).
5. **HTTPS** — WordPress **Webbplatsadress** ska börja med `https://`.
6. **Meta Developer-konto** — gratis konto på [developers.facebook.com](https://developers.facebook.com/).

> **Tips:** Öppna *maca Njuvs → Sociala medier* parallellt med guiden. Där ser ni OAuth-redirect-URL och fyller i App ID och App Secret.

---

## Översikt

| Steg | Var | Vad |
|------|-----|-----|
| 1 | Meta for Developers | Skapa app |
| 2 | Meta app | Välj rätt **användningsfall** (Page + Instagram) |
| 3 | Meta app | Appdomäner, integritetspolicy, OAuth-redirect |
| 4 | Meta app | Behörigheter |
| 5 | WordPress (maca Njuvs) | App ID + App Secret |
| 6 | WordPress (maca Njuvs) | Anslut Facebook och välj sida |
| 7 | WordPress (maca Njuvs) | Testa publicering |
| 8 | WordPress (maca Njuvs) | Publicera nyheter och evenemang |

---

## Steg 1 — Skapa Meta-app

1. Gå till [developers.facebook.com/apps](https://developers.facebook.com/apps) och klicka **Skapa app**.
2. Välj **Företag** (Business) som apptyp om du får välja.
3. Ge appen ett tydligt namn, t.ex. *Ert företagsnamn – maca Njuvs*.
4. Välj er **Business Manager**-portfölj om du tillfrågas.
5. Skapa appen och notera **App-ID** (visas överst i appens instrumentpanel).

---

## Steg 2 — Lägg till rätt användningsfall

Det här steget är viktigt — fel användningsfall ger fel behörigheter och OAuth-fel.

1. I appens instrumentpanel: **Användningsfall** → **Lägg till användningsfall**.
2. Lägg till **Hantera allt på er Page**.
3. Lägg till **Hantera meddelanden och innehåll på Instagram**.

**Använd inte** enbart det generiska *Facebook Login* som enda användningsfall — det räcker inte för att publicera inlägg till Page och Instagram.

---

## Steg 3 — Appinställningar

### Appdomäner

Under **Appinställningar → Grundläggande**:

- **Appdomäner:** er webbplatsdomän utan `https://`, t.ex. `{{SITE_DOMAIN}}`.
- **Integritetspolicy-URL:** en publik HTTPS-sida med integritetspolicy (krävs av Meta). Exempel: `https://maca.se/policy/`
- **Webbplats:** er webbplats-URL, t.ex. `https://{{SITE_DOMAIN}}`

Spara ändringarna.

### OAuth redirect URI

1. Gå till **Användningsfall → Facebook Login for Business** (eller motsvarande Login-produkt kopplad till appen).
2. Under **Inställningar** / **Valid OAuth Redirect URIs** klistrar du in **exakt** den URL som visas i WordPress under *maca Njuvs → Sociala medier → OAuth redirect URI*:

```
{{OAUTH_REDIRECT_URI}}
```

3. Spara. URL:en måste matcha **tecken för tecken** — inget extra snedstreck, ingen `http` om sidan kör `https`.

---

## Steg 4 — Behörigheter

maca Njuvs behöver följande behörigheter när ni ansluter (Meta kan visa dem vid inloggning):

| Behörighet | Syfte |
|------------|--------|
| `pages_show_list` | Lista sidor ni administrerar |
| `pages_manage_posts` | Publicera till Facebook-sidan |
| `pages_read_engagement` | Läsa grundläggande sidinfo |
| `instagram_basic` | Koppla Instagram Business-konto |
| `instagram_content_publish` | Publicera till Instagram |
| `business_management` | Koppla Page och Instagram i Business Manager |

I **utvecklingsläge** (Development mode) fungerar detta för app-administratörer och testare. För produktion kan Meta kräva **App Review** och **Business Verification** — följ Metas checklista i Developer Console.

> **Webhooks behövs inte** för att publicera nyheter och evenemang från maca Njuvs.

---

## Steg 5 — Fyll i App ID och App Secret i WordPress

1. Gå till *maca Njuvs → Sociala medier*.
2. Under **Meta-appuppgifter**:
   - **App ID** — från Meta Developer Console
   - **App Secret** — från *Appinställningar → Grundläggande* (klicka *Visa*)
3. **Testbild-URL** (valfritt men rekommenderat) — en publik HTTPS-bild som används vid test mot Instagram (Instagram kräver alltid bild).
4. Klicka **Spara Meta-inställningar**.

---

## Steg 6 — Anslut Facebook och välj sida

1. Klicka **Anslut Facebook och Instagram**.
2. Logga in med ett konto som är **admin** på Facebook-sidan.
3. Godkänn behörigheterna Meta visar.
4. Välj **vilken Facebook-sida** som ska kopplas (om ni har flera).
5. Bekräfta — ni ska se sidnamn och ev. `@instagram-användarnamn` under **Anslutning**.

Om Instagram inte visas: kontrollera att Instagram-kontot är **Business/Creator** och **länkat till just den Facebook-sidan**.

---

## Steg 7 — Testa publicering

1. Fyll i **Testbild-URL** om den saknas (publik HTTPS-bild).
2. Klicka **Testa publicering** på fliken *Sociala medier*.
3. Kontrollera att ett testinlägg dyker upp på Facebook-sidan (och Instagram om kopplat).
4. Vid fel: se **Publiceringslogg** längre ner på samma flik — där sparas felmeddelanden från Meta API.

---

## Steg 8 — Publicera nyheter och evenemang

1. Skapa eller redigera en **nyhet** eller **evenemang** under *maca Njuvs → Nyheter* respektive *maca Njuvs → Evenemang*.
2. Sätt status till **Publicerad** (eller schemalagd med datum som redan passerat).
3. Under **Publicering** — kryssa i **Facebook** och/eller **Instagram**.
4. **Instagram kräver bild** på nyheten/händelsen.
5. Spara — publicering sker direkt om Facebook är anslutet.

**Text på sociala medier:** titel plus innehåll (eller utdrag om ni fyllt i det fältet) skickas som caption. På Instagram kan texten ligga under bilden — tryck *mer* för att läsa allt.

**Publicera igen:** om ett inlägg redan publicerats kan ni kryssa i *Publicera igen till Facebook/Instagram* när ni redigerar nyheten.

---

## Felsökning

| Problem | Lösning |
|---------|---------|
| *Invalid OAuth Redirect URI* | Jämför URL i Meta med exakt värdet i *maca Njuvs → Sociala medier* (steg 3). |
| *Invalid Scopes* | Kontrollera use cases i steg 2 — lägg till Page + Instagram. |
| Omdirigering till wp-admin / vit sida | Uppdatera maca Njuvs till senaste version (OAuth går via REST-URL ovan). |
| Instagram saknas efter anslutning | Koppla Instagram Business till Facebook-sidan i Meta Business Suite. |
| Endast bild, ingen text | Fyll i **Innehåll** på nyheten; använd *Publicera igen* om inlägget redan publicerats. |
| *Instagram requires an image* | Ladda upp bild på nyheten/händelsen. |
| Token går ut | Anslut igen via *Anslut Facebook & Instagram*; maca Njuvs försöker förnya token automatiskt. |
| Meta-koppling fungerade tidigare på annan sajt | maca Njuvs har **egen** Meta-koppling per webbplats — konfigurera appen och anslut igen under *maca Njuvs → Sociala medier*. |

---

## Säkerhet och integritet

- **App Secret** lagras krypterat i WordPress — dela det inte publikt.
- **Page access token** lagras krypterat på er server.
- maca.se **hostar inte** er OAuth — all trafik går mellan er webbplats och Meta.
- maca Njuvs lagrar sociala medier-inställningar i egna tabeller (`wp_maca_njuvs_*`).
- Informera besökare i er integritetspolicy om att inlägg kan publiceras på sociala medier när ni använder funktionen.

---

## Snabbreferens i WordPress

| Plats | Syfte |
|-------|--------|
| *maca Njuvs → Inställningar* | Aktivera publicering, iCal-URL, **länk till denna guide** |
| *maca Njuvs → Sociala medier* | App ID/Secret, anslutning, test, logg |
| *maca Njuvs → Nyheter / Evenemang* | Kryssa i Facebook/Instagram vid publicering |
