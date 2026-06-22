# maca Njuvs — käyttöopas

**Versio:** 1.0.15  
**Koskee:** **maca Njuvs** — WordPress-lisäosa uutisiin, tapahtumiin, Gutenberg-lohkoihin, iCal-kalenteriin ja valinnaiseen jakamiseen Facebookissa ja Instagramissa.

maca Njuvs mahdollistaa uutisten ja tapahtumien luomisen ja julkaisemisen suoraan WordPressissä. Sisältö näkyy verkkosivustolla Gutenberg-lohkojen (tai shortcodejen) kautta ja voidaan jakaa sosiaalisessa mediassa, jos Meta-sovellus on yhdistetty.

---

## Hallintavälilehdet

| Välilehti | Mitä teet täällä |
|-----------|------------------|
| **News** | Luo, muokkaa ja hallitse uutisia |
| **Events** | Luo, muokkaa ja hallitse tapahtumia (ml. toistuvat sarjat) |
| **Social media** | Yhdistä Meta-sovellus, Facebook-sivu ja Instagram (vaatii erityisoikeuden) |
| **Settings** | Ota moduuli käyttöön/pois, iCal-URL-osoitteet, linkki sosiaaliseen oppaaseen |
| **Import** | Tuo olemassa olevat WordPress-artikkelit uutisina |
| **Guide** | Tämä opas — lohkot, asetukset ja toiminnot |

---

## Uutiset

Kohdassa *maca Njuvs → News* luot ja muokkaat verkkosivustolla näkyviä uutisia.

### Kentät

| Kenttä | Kuvaus |
|--------|--------|
| **Title** | Pääotsikko — näkyy sivustolla, bannerissa ja ensimmäisenä sosiaalisen median teksteissä |
| **Excerpt** | Lyhyt yhteenveto listoille ja bannerille. Sisältyy sosiaaliseen tekstiin otsikon jälkeen |
| **Content** | Koko teksti. Klikkaus avaa koko sisällön (ponnahdusikkuna tai laajennettu näkymä) |
| **Image** | Valinnainen kuva mediakirjastosta |
| **Status** | Luonnos, Ajastettu, Julkaistu tai Arkistoitu |
| **Publish at** | Valinnainen päivämäärä/aika. Tuleva päivämäärä tilalla Julkaistu on Ajastettu siihen asti |
| **Expires at** | Valinnainen — kohde piilotetaan automaattisesti tämän päivämäärän jälkeen |
| **Publishing** | Valintaruudut verkkosivustolle, Facebookille ja Instagramille |

### Tilat

- **Draft** — ei näy verkkosivustolla
- **Scheduled** — julkaistaan automaattisesti määritettynä ajankohtana
- **Published** — näkyy verkkosivustolla (kun moduuli on käytössä)
- **Archived** — piilotettu sivustolta, mutta tallennettu hallintaan

### Kuvavinkit

- Käytä **Select image** -painiketta — älä liitä kuvia ingressi- tai sisältökenttiin.
- Pakkaa suuret kuvat (mieluiten alle 500 KB). Lisäosa varoittaa suurista kuvista; erittäin suuret tiedostot voivat aiheuttaa virheen *Please reduce the amount of data* tallennuksessa.

---

## Tapahtumat

Kohdassa *maca Njuvs → Events* hallitset tulevia ja toistuvia tapahtumia.

### Kentät

| Kenttä | Kuvaus |
|--------|--------|
| **Title** | Tapahtuman nimi |
| **Description** | Yksityiskohtainen teksti |
| **Location** | Tapahtumapaikka |
| **Image** | Valinnainen kuva |
| **Price** | Valinnainen — näytetään sivustolla, jos asetettu |
| **All day** | Valitse, jos tapahtuma kestää koko päivän |
| **Start / End** | Päivämäärä ja aika |
| **Recurrence** | Ei toistoa, Päivittäin, Viikoittain tai Kuukausittain intervallilla, viikonpäivillä ja päättymispäivällä tai esiintymien määrällä |
| **Active** | Näytä verkkosivustolla |
| **Publishing** | Verkkosivusto, Facebook ja Instagram |

### Poikkeukset toistuvissa sarjoissa

Toistuvissa tapahtumissa voit lisätä **poikkeuksia** muokkauksessa — peruuta tai siirrä yksittäinen päivämäärä muuttamatta koko sarjaa.

---

## Gutenberg-lohkot

Lisää lohkoja kategoriasta **maca Njuvs** lohkoeditorissa (hae *maca News* tai *maca Events*).

### maca News

Näyttää julkaistut uutiset maca Njuvsista.

| Asetus | Kuvaus |
|--------|--------|
| **Layout** | Lista, In page (taulukko/sarake), Fixed panel vasen/oikea tai Top banner |
| **Number of items** | 1–20 uutista |
| **Scrolling ticker** | (Banner) Jatkuva vaakasuuntainen vieritys |
| **Show image** | (Lista) Näytä pikkukuva |
| **Show date** | Näytä julkaisupäivä |
| **Show excerpt** | Näytä lyhyt yhteenveto |

**Asetteluvinkit:**

- **List** — vakiomuoto valinnaisella kuvalla
- **In page** — pysyy siinä, mihin sijoitat lohkon, esim. taulukoissa ja sarakkeissa
- **Fixed panel** — kiinnitetty sivulla vieritettäessä työpöydällä; mobiilissa näkyy sivun lopussa. Klikkaus avaa koko uutisen ponnahdusikkunassa
- **Top banner** — nauha ylhäällä. Käytä enintään yhtä banner-lohkoa sivua kohden

### maca Events

Näyttää tulevat tapahtumat.

| Asetus | Kuvaus |
|--------|--------|
| **View** | Lista tai Kuukausikalenteri |
| **Number of events** | 1–30 (listanäkymä) |
| **Show image** | (Listanäkymä) |
| **Show location** | (Listanäkymä) |
| **Week starts on Monday** | (Kuukausikalenteri) |
| **Show calendar subscription** | Linkit iCal-syötteen tilaamiseen |

---

## Shortcodet

Ilman lohkoeditoria sama sisältö voidaan näyttää shortcodeilla:

### Uutiset

```
[maca_njuvs_news limit="5" layout="list" show_image="1" show_date="1" show_excerpt="1" banner_scroll="1"]
```

| Attribuutti | Arvot | Oletus |
|-------------|-------|--------|
| `limit` | 1–50 | 5 |
| `layout` | `list`, `embedded`, `sidebar-left`, `sidebar-right`, `banner` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_date` | `1` / `0` | `1` |
| `show_excerpt` | `1` / `0` | `1` |
| `banner_scroll` | `1` / `0` | `1` |

### Tapahtumat

```
[maca_njuvs_events limit="10" view="list" show_image="1" show_location="1" show_subscribe="1"]
```

| Attribuutti | Arvot | Oletus |
|-------------|-------|--------|
| `limit` | 1–50 | 10 |
| `view` | `list`, `month` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_location` | `1` / `0` | `1` |
| `show_subscribe` | `1` / `0` | `1` |

### Kalenteritilaus

```
[maca_njuvs_calendar_subscribe]
```

Näyttää linkit tapahtumakalenterin tilaamiseen kalenterisovelluksissa.

---

## Asetukset

Kohdassa *maca Njuvs → Settings*:

| Asetus | Kuvaus |
|--------|--------|
| **Enable maca Njuvs** | Pääkytkin — pois päältä sisältöä ei näytetä sivustolla tai lohkoissa |
| **iCal feed URL** | Julkinen syöte kalenterisovelluksille: `{{ICAL_URL}}` |
| **Subscribe URL** | webcal-linkki Apple Kalenterille ym.: `{{WEBCAL_URL}}` |

> **Vinkki:** Jos iCal-syöte palauttaa 404, tallenna kerran kohdassa *Asetukset → Pysyvät linkit* WordPressissä.

### Facebook ja Instagram

Sosiaalisen median yhteys hallitaan kohdassa *Social media*. Erillinen vaiheittainen opas on saatavilla painikkeesta *Setup guide: Facebook & Instagram* asetussivulla.

---

## Tuonti

Kohdassa *maca Njuvs → Import* voit kopioida olemassa olevat WordPress-artikkelit maca Njuvsiin uutisina.

| Vaihtoehto | Kuvaus |
|------------|--------|
| **Content type** | Artikkeli tai sivu |
| **Category** | Valinnainen suodatin (vain artikkelit) |
| **Skip already imported** | Vältä kaksoiskappaleet |

Alkuperäisiä artikkeleita ei poisteta — tuonti luo uusia uutisia maca Njuvsiin.

---

## Muut toiminnot

- **iCal-kalenteri** — tapahtumat viedään julkiseen syötteeseen, joka päivittyy muutoksista
- **Ajastettu julkaisu** — uutiset voidaan julkaista määritettynä ajankohtana ilman manuaalista toimenpidettä
- **Vanhenemispäivä** — uutiset voidaan piilottaa automaattisesti
- **Toistuvat tapahtumat** — päivittäiset, viikoittaiset ja kuukausittaiset sarjat poikkeuksineen
- **Sosiaalinen julkaisu** — valinnainen jako Facebook Pageen ja Instagram Businessiin tallennuksen yhteydessä (Meta-sovellus vaaditaan)

---

## Pika-aloitus

1. Ota maca Njuvs käyttöön kohdassa *Settings*
2. Luo vähintään yksi uutinen tai tapahtuma
3. Lisää lohkot **maca News** ja **maca Events** sivulle
4. (Valinnainen) Yhdistä Facebook/Instagram kohdassa *Social media*
5. (Valinnainen) Jaa iCal-URL, jotta vieraat voivat tilata kalenterin

---

## Käyttöehdot

Käyttämällä **maca Njuvs** -lisäosaa hyväksyt seuraavat ehdot:

1. **Lisenssi** — Lisäosa jaetaan GNU General Public License v2 tai myöhemmän (GPL v2+) mukaisesti. Voit käyttää, muokata ja jakaa lisäosaa lisenssiehtojen mukaisesti.
2. **Sisällön vastuu** — Verkkosivuston omistajana olet vastuussa kaikesta sisällöstä (uutiset, tapahtumat, kuvat ja tekstit), jonka julkaiset lisäosan, verkkosivuston ja yhdistettyjen sosiaalisten medioiden kautta.
3. **Kolmannen osapuolen palvelut** — Facebookiin, Instagramiin ja Meta Graph API -rajapintaan liittyviä toimintoja koskevat kunkin palvelun ehdot. Sinun on noudatettava Metan alustasääntöjä ja omistettava tarvittavat oikeudet jaettuun sisältöön.
4. **Ei takuuta** — maca Njuvs toimitetaan sellaisenaan ilman nimenomaista tai epäsuoraa takuuta. Maca Development ei vastaa käyttökatkoksista, tietojen menetyksestä tai vahingoista, jotka johtuvat lisäosan käytöstä.
5. **Vastuun rajoitus** — Lain sallimissa rajoissa Maca Development ei vastaa epäsuorista vahingoista, menetetystä voitosta tai tietojen menetyksestä, jotka johtuvat lisäosasta tai integroiduista palveluista.
6. **Päivitykset** — Toiminnot voivat muuttua tai poistua tulevissa versioissa. Suosittelemme varmuuskopiota ennen päivityksiä.

## Tietosuojakäytäntö

maca Njuvs käsittelee tietoja paikallisesti WordPress-sivustollasi. Verkkosivuston omistajana olet rekisterinpitäjä vierailija- ja sisältötiedoille sovellettavan lain, esim. GDPR:n, mukaisesti.

### Mitä tietoja tallennetaan

| Tiedot | Missä | Tarkoitus |
|--------|-------|-----------|
| Uutiset ja tapahtumat | WordPress-tietokanta (omat taulut) | Julkaisu sivustolla ja lohkoissa |
| Kuva-URL-osoitteet ja tekstit | Sama tietokanta | Näyttö ja sosiaalinen jako |
| Meta App ID, tunnukset ym. | WordPress-asetukset (salattu tarvittaessa) | Facebook/Instagram-julkaisu |
| Sosiaalisen julkaisun loki | WordPress-tietokanta | Vianetsintä ja tila hallinnassa |
| Tuotujen artikkelien tunnukset | Artikkelin metatiedot | Kaksoiskappaleiden välttäminen tuonnissa |

### Mitä tietoja jaetaan ulkoisesti

- **Oletuksena tietoja ei lähetetä Maca Developmentille** lisäosaa käytettäessä.
- **iCal-syöte** (`{{ICAL_URL}}`) on julkinen — aktiivisten tapahtumien otsikko, aika, paikka ja kuvaus ovat luettavissa kaikille, joilla on linkki.
- **Sosiaalinen julkaisu** lähettää sisältöä ja kuvia Metaan (Facebook/Instagram) asetustesi ja Metan API:n mukaisesti.

### Säilytys ja poisto

- Tiedot säilyvät asennuksen poiston jälkeen, ellei vakiota `MACA_NJUVS_UNINSTALL_DROP_DATA` ole asetettu arvoon `true` ennen poistoa.
- Voit poistaa uutisia, tapahtumia ja sosiaalisen median yhteyksiä milloin tahansa hallinnassa.

### Velvollisuutesi

- Ilmoita vierailijoille verkkosivustosi **tietosuojakäytännössä** iCal-syötteestä, mahdollisista seurantatekniikoista (muiden lisäosien kautta) ja sosiaalisesta julkaisusta.
- Anna julkinen tietosuojakäytännön URL Meta-sovelluksessasi, jos käytät Facebook/Instagram-yhteyttä (Metan vaatimus).

### Yhteystiedot

Tuki ja kysymykset lisäosasta: [maca.se](https://maca.se/)
