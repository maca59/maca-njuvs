# Vaihe vaiheelta: yhdistä Facebook ja Instagram maca Njuvsiin

**Versio:** 1.0.15  
**Koskee:** **maca Njuvs** — WordPress-lisäosa uutisiin, tapahtumiin, iCal-kalenteriin ja jakamiseen sosiaalisessa mediassa

Tämä opas auttaa julkaisemaan uutisia ja tapahtumia **maca Njuvsista** **Facebook-sivullesi** ja **Instagram Business -tilillesi**. maca Njuvs ei tallenna Meta-kirjautumistasi keskitetysti — luot oman sovelluksen [Meta for Developers](https://developers.facebook.com/) -palvelussa ja yhdistät sen WordPress-sivustoon.

---

## Ennen aloitusta

1. **maca Njuvs asennettu ja aktiivinen** — lisäosan pitäisi näkyä kohdassa *Lisäosat* WordPressissä.
2. **Julkaisu käytössä** — *maca Njuvs → Settings* → valitse *Enable maca Njuvs* ja tallenna.
3. **Facebook-sivu** — sinun on oltava sivun ylläpitäjä, jolle haluat julkaista.
4. **Instagram Business tai Creator** — tilin on oltava **yhdistetty Facebook-sivuun** (Meta Business Suitessa tai Instagram-sovelluksessa kohdassa *Profiili → Muokkaa profiilia → Sivut*).
5. **HTTPS** — WordPressin **sivuston osoitteen** on alettava `https://`.
6. **Meta Developer -tili** — ilmainen tili osoitteessa [developers.facebook.com](https://developers.facebook.com/).

> **Vinkki:** Avaa *maca Njuvs → Social media* rinnakkain tämän oppaan kanssa. Siellä näet OAuth-uudelleenohjaus-URL:n ja syötät App ID:n ja App Secretin.

---

## Yleiskatsaus

| Vaihe | Missä | Mitä |
|-------|-------|------|
| 1 | Meta for Developers | Luo sovellus |
| 2 | Meta-sovellus | Valitse oikeat **käyttötapaukset** (Page + Instagram) |
| 3 | Meta-sovellus | Sovellusverkkotunnukset, tietosuojakäytäntö, OAuth-uudelleenohjaus |
| 4 | Meta-sovellus | Käyttöoikeudet |
| 5 | WordPress (maca Njuvs) | App ID + App Secret |
| 6 | WordPress (maca Njuvs) | Yhdistä Facebook ja valitse sivu |
| 7 | WordPress (maca Njuvs) | Testaa julkaisu |
| 8 | WordPress (maca Njuvs) | Julkaise uutisia ja tapahtumia |

---

## Vaihe 1 — Luo Meta-sovellus

1. Siirry osoitteeseen [developers.facebook.com/apps](https://developers.facebook.com/apps) ja napsauta **Luo sovellus**.
2. Valitse sovellustyypiksi **Business**, jos sinulta kysytään.
3. Anna sovellukselle selkeä nimi, esim. *Yrityksesi nimi – maca Njuvs*.
4. Valitse **Business Manager** -portfolio, jos sinulta kysytään.
5. Luo sovellus ja merkitse **App ID** (näkyy sovelluksen hallintapaneelin yläosassa).

---

## Vaihe 2 — Lisää oikeat käyttötapaukset

Tämä vaihe on tärkeä — väärät käyttötapaukset aiheuttavat käyttöoikeus- ja OAuth-virheitä.

1. Sovelluksen hallintapaneelissa: **Käyttötapaukset** → **Lisää käyttötapauksia**.
2. Lisää **Hallitse kaikkea Page-sivullasi**.
3. Lisää **Hallitse viestejä ja sisältöä Instagramissa**.

**Älä käytä** pelkästään yleistä *Facebook Login* -ratkaisua — se ei riitä julkaisuun Pageen ja Instagramiin.

---

## Vaihe 3 — Sovellusasetukset

### Sovellusverkkotunnukset

Kohdassa **Sovellusasetukset → Perus**:

- **Sovellusverkkotunnukset:** sivustosi verkkotunnus ilman `https://`, esim. `{{SITE_DOMAIN}}`.
- **Tietosuojakäytännön URL:** julkinen HTTPS-sivu tietosuojakäytännöllä (Meta vaatii). Esimerkki: `https://maca.se/policy/`
- **Verkkosivusto:** sivustosi URL, esim. `https://{{SITE_DOMAIN}}`

Tallenna muutokset.

### OAuth-uudelleenohjauksen URI

1. Siirry kohtaan **Käyttötapaukset → Facebook Login for Business** (tai sovellukseen liitetty Login-tuote).
2. Kohdassa **Asetukset** / **Valid OAuth Redirect URIs** liitä **täsmälleen** URL, joka näkyy WordPressissä kohdassa *maca Njuvs → Social media → OAuth redirect URI*:

```
{{OAUTH_REDIRECT_URI}}
```

3. Tallenna. URL:n on vastattava **merkki merkiltä** — ei ylimääräistä kauttaviivaa, ei `http`-osoitetta, jos sivusto käyttää `https`.

---

## Vaihe 4 — Käyttöoikeudet

maca Njuvs tarvitsee nämä käyttöoikeudet yhdistettäessä (Meta voi näyttää ne kirjautumisessa):

| Käyttöoikeus | Tarkoitus |
|--------------|-----------|
| `pages_show_list` | Listaa hallitsemasi sivut |
| `pages_manage_posts` | Julkaise Facebook-sivulle |
| `pages_read_engagement` | Lue sivun perustiedot |
| `instagram_basic` | Yhdistä Instagram Business -tili |
| `instagram_content_publish` | Julkaise Instagramiin |
| `business_management` | Yhdistä Page ja Instagram Business Managerissa |

**Kehitystilassa** tämä toimii sovelluksen ylläpitäjille ja testaajille. Tuotannossa Meta voi vaatia **App Review** -tarkistuksen ja **Business Verification** -vahvistuksen — seuraa Metan tarkistuslistaa Developer Consolessa.

> **Webhookit eivät ole pakollisia** uutisten ja tapahtumien julkaisuun maca Njuvsista.

---

## Vaihe 5 — Syötä App ID ja App Secret WordPressiin

1. Siirry kohtaan *maca Njuvs → Social media*.
2. Kohdassa **Meta app credentials**:
   - **App ID** — Meta Developer Consolesta
   - **App Secret** — kohdasta *Asetukset → Perus* (napsauta *Näytä*)
3. **Test image URL** (valinnainen mutta suositeltava) — julkinen HTTPS-kuva Instagram-testeihin (Instagram vaatii aina kuvan).
4. Napsauta **Save Meta settings**.

---

## Vaihe 6 — Yhdistä Facebook ja valitse sivu

1. Napsauta **Connect Facebook & Instagram**.
2. Kirjaudu tilillä, jolla on **ylläpitäjäoikeudet** Facebook-sivulla.
3. Hyväksy Metan näyttämät käyttöoikeudet.
4. Valitse **mikä Facebook-sivu** yhdistetään (jos sinulla on useita).
5. Vahvista — sinun pitäisi nähdä sivun nimi ja mahdollisesti `@instagram-käyttäjänimi` kohdassa **Connection**.

Jos Instagram ei näy: varmista, että Instagram-tili on **Business/Creator** ja **yhdistetty juuri tuohon Facebook-sivuun**.

---

## Vaihe 7 — Testaa julkaisu

1. Täytä **Test image URL**, jos se puuttuu (julkinen HTTPS-kuva).
2. Napsauta **Test publish** välilehdellä *Social media*.
3. Tarkista, että testijulkaisu näkyy Facebook-sivulla (ja Instagramissa, jos yhdistetty).
4. Virhetilanteessa: katso **Publish log** alempana samalla välilehdellä — Meta API -virheviestit tallennetaan sinne.

---

## Vaihe 8 — Julkaise uutisia ja tapahtumia

1. Luo tai muokkaa **uutista** tai **tapahtumaa** kohdassa *maca Njuvs → News* tai *maca Njuvs → Events*.
2. Aseta tilaksi **Published** (tai ajastettu päivämäärällä, joka on jo mennyt).
3. Kohdassa **Publishing** — valitse **Facebook** ja/tai **Instagram**.
4. **Instagram vaatii kuvan** uutisessa tai tapahtumassa.
5. Tallenna — julkaisu tapahtuu heti, jos Facebook on yhdistetty.

**Sosiaalisen median teksti:** otsikko plus sisältö (tai tiivistelmä, jos täytetty) lähetetään kuvatekstinä. Instagramissa teksti voi olla kuvan alla — napauta *lisää* lukeaksesi kaiken.

**Julkaise uudelleen:** jos julkaisu on jo tehty, voit valita *Publish again to Facebook/Instagram* muokatessasi uutista.

---

## Vianetsintä

| Ongelma | Ratkaisu |
|---------|----------|
| *Invalid OAuth Redirect URI* | Vertaa Meta-URL:ää tarkkaan arvoon kohdassa *maca Njuvs → Social media* (vaihe 3). |
| *Invalid Scopes* | Tarkista käyttötapaukset vaiheessa 2 — lisää Page + Instagram. |
| Uudelleenohjaus wp-adminiin / tyhjä sivu | Päivitä maca Njuvs uusimpaan versioon (OAuth käyttää yllä olevaa REST-URL:ää). |
| Instagram puuttuu yhdistämisen jälkeen | Yhdistä Instagram Business Facebook-sivuun Meta Business Suitessa. |
| Vain kuva, ei tekstiä | Täytä **Content** uutisessa; käytä *Publish again*, jos jo julkaistu. |
| *Instagram requires an image* | Lataa kuva uutiseen tai tapahtumaan. |
| Token vanhentunut | Yhdistä uudelleen *Connect Facebook & Instagram* -painikkeella; maca Njuvs yrittää uusia tokenin automaattisesti. |
| Meta-yhteys toimi toisella sivustolla | maca Njuvs tallentaa **oman** Meta-yhteyden sivustoa kohden — määritä sovellus ja yhdistä uudelleen kohdassa *maca Njuvs → Social media*. |

---

## Turvallisuus ja tietosuoja

- **App Secret** tallennetaan salattuna WordPressiin — älä jaa sitä julkisesti.
- **Page access token** tallennetaan salattuna palvelimellesi.
- maca.se **ei isännöi** OAuth-yhteyttäsi — kaikki liikenne kulkee sivustosi ja Metan välillä.
- maca Njuvs tallentaa sosiaalisen median asetukset omiin tauluihinsa (`wp_maca_njuvs_*`).
- Mainitse sivustosi **tietosuojakäytännössä**, että julkaisuja voidaan jakaa sosiaalisessa mediassa, kun käytät tätä toimintoa.

---

## Pikaopas WordPressissä

| Sijainti | Tarkoitus |
|----------|-----------|
| *maca Njuvs → Settings* | Ota julkaisu käyttöön, iCal-URL, **linkki tähän oppaaseen** |
| *maca Njuvs → Social media* | App ID/Secret, yhteys, testi, loki |
| *maca Njuvs → News / Events* | Valitse Facebook/Instagram julkaisussa |
