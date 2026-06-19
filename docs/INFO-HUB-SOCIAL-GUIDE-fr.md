# Pas à pas : connecter Facebook et Instagram à maca Njuvs

**Version :** 1.0.15  
**S'applique à :** **maca Njuvs** — extension WordPress pour actualités, événements, calendrier iCal et partage sur les réseaux sociaux

Ce guide vous aide à publier des actualités et des événements depuis **maca Njuvs** sur votre **page Facebook** et votre **compte Instagram Business**. maca Njuvs n'héberge pas votre connexion Meta de manière centralisée — vous créez votre propre application dans [Meta for Developers](https://developers.facebook.com/) et la connectez à votre site WordPress.

---

## Avant de commencer

1. **maca Njuvs installé et actif** — l'extension doit apparaître sous *Extensions* dans WordPress.
2. **Publication activée** — *maca Njuvs → Settings* → cochez *Enable maca Njuvs* et enregistrez.
3. **Page Facebook** — vous devez être administrateur de la page sur laquelle vous souhaitez publier.
4. **Instagram Business ou Creator** — le compte doit être **lié à la page Facebook** (dans Meta Business Suite ou dans l'application Instagram sous *Profil → Modifier le profil → Pages*).
5. **HTTPS** — l'**adresse du site** WordPress doit commencer par `https://`.
6. **Compte Meta Developer** — compte gratuit sur [developers.facebook.com](https://developers.facebook.com/).

> **Astuce :** Ouvrez *maca Njuvs → Social media* en parallèle de ce guide. Vous y verrez l'URL de redirection OAuth et saisirez l'App ID et l'App Secret.

---

## Vue d'ensemble

| Étape | Où | Quoi |
|-------|-----|------|
| 1 | Meta for Developers | Créer l'application |
| 2 | Application Meta | Choisir les bons **cas d'utilisation** (Page + Instagram) |
| 3 | Application Meta | Domaines, politique de confidentialité, redirection OAuth |
| 4 | Application Meta | Autorisations |
| 5 | WordPress (maca Njuvs) | App ID + App Secret |
| 6 | WordPress (maca Njuvs) | Connecter Facebook et choisir la page |
| 7 | WordPress (maca Njuvs) | Tester la publication |
| 8 | WordPress (maca Njuvs) | Publier actualités et événements |

---

## Étape 1 — Créer l'application Meta

1. Allez sur [developers.facebook.com/apps](https://developers.facebook.com/apps) et cliquez **Créer une application**.
2. Choisissez **Business** comme type d'application si demandé.
3. Donnez un nom clair, p. ex. *Nom de votre entreprise – maca Njuvs*.
4. Sélectionnez votre portefeuille **Business Manager** si demandé.
5. Créez l'application et notez l'**App ID** (en haut du tableau de bord).

---

## Étape 2 — Ajouter les bons cas d'utilisation

Cette étape est importante — de mauvais cas d'utilisation provoquent des erreurs d'autorisation et OAuth.

1. Dans le tableau de bord : **Cas d'utilisation** → **Ajouter des cas d'utilisation**.
2. Ajoutez **Gérer tout sur votre Page**.
3. Ajoutez **Gérer la messagerie et le contenu sur Instagram**.

**N'utilisez pas** uniquement *Facebook Login* générique — ce n'est pas suffisant pour publier sur Page et Instagram.

---

## Étape 3 — Paramètres de l'application

### Domaines de l'application

Sous **Paramètres de l'application → De base** :

- **Domaines de l'application :** domaine de votre site sans `https://`, p. ex. `{{SITE_DOMAIN}}`.
- **URL de la politique de confidentialité :** page HTTPS publique (requise par Meta). Exemple : `https://maca.se/policy/`
- **Site web :** URL de votre site, p. ex. `https://{{SITE_DOMAIN}}`

Enregistrez les modifications.

### URI de redirection OAuth

1. Allez à **Cas d'utilisation → Facebook Login for Business** (ou le produit Login lié à votre application).
2. Sous **Paramètres** / **Valid OAuth Redirect URIs**, collez **exactement** l'URL affichée dans WordPress sous *maca Njuvs → Social media → OAuth redirect URI* :

```
{{OAUTH_REDIRECT_URI}}
```

3. Enregistrez. L'URL doit correspondre **caractère par caractère** — pas de barre oblique en trop, pas de `http` si le site utilise `https`.

---

## Étape 4 — Autorisations

maca Njuvs a besoin de ces autorisations lors de la connexion (Meta peut les afficher à la connexion) :

| Autorisation | Objectif |
|--------------|----------|
| `pages_show_list` | Lister les pages que vous administrez |
| `pages_manage_posts` | Publier sur la page Facebook |
| `pages_read_engagement` | Lire les informations de base de la page |
| `instagram_basic` | Lier le compte Instagram Business |
| `instagram_content_publish` | Publier sur Instagram |
| `business_management` | Lier Page et Instagram dans Business Manager |

En **mode développement**, cela fonctionne pour les administrateurs et testeurs de l'app. En production, Meta peut exiger **App Review** et **Business Verification** — suivez la liste Meta dans Developer Console.

> **Les webhooks ne sont pas requis** pour publier des actualités et événements depuis maca Njuvs.

---

## Étape 5 — Saisir App ID et App Secret dans WordPress

1. Allez à *maca Njuvs → Social media*.
2. Sous **Meta app credentials** :
   - **App ID** — depuis Meta Developer Console
   - **App Secret** — dans *Paramètres → De base* (cliquez *Afficher*)
3. **Test image URL** (optionnel mais recommandé) — image HTTPS publique pour les tests Instagram (Instagram exige toujours une image).
4. Cliquez **Save Meta settings**.

---

## Étape 6 — Connecter Facebook et choisir la page

1. Cliquez **Connect Facebook & Instagram**.
2. Connectez-vous avec un compte **administrateur** de la page Facebook.
3. Approuvez les autorisations affichées par Meta.
4. Sélectionnez **quelle page Facebook** connecter (si vous en avez plusieurs).
5. Confirmez — vous devriez voir le nom de la page et éventuellement `@nom-instagram` sous **Connection**.

Si Instagram n'apparaît pas : vérifiez que le compte est **Business/Creator** et **lié à cette page Facebook**.

---

## Étape 7 — Tester la publication

1. Renseignez **Test image URL** si manquant (image HTTPS publique).
2. Cliquez **Test publish** dans l'onglet *Social media*.
3. Vérifiez qu'une publication test apparaît sur la page Facebook (et Instagram si connecté).
4. En cas d'erreur : consultez **Publish log** plus bas sur le même onglet — les messages d'erreur de l'API Meta y sont enregistrés.

---

## Étape 8 — Publier actualités et événements

1. Créez ou modifiez une **actualité** ou un **événement** sous *maca Njuvs → News* ou *maca Njuvs → Events*.
2. Définissez le statut sur **Published** (ou planifié avec une date déjà passée).
3. Sous **Publishing** — cochez **Facebook** et/ou **Instagram**.
4. **Instagram exige une image** sur l'actualité ou l'événement.
5. Enregistrez — la publication s'exécute immédiatement si Facebook est connecté.

**Texte social :** titre plus contenu (ou extrait si renseigné) est envoyé comme légende. Sur Instagram le texte peut apparaître sous l'image — appuyez sur *plus* pour tout lire.

**Publier à nouveau :** si déjà publié, cochez *Publish again to Facebook/Instagram* lors de la modification.

---

## Dépannage

| Problème | Solution |
|----------|----------|
| *Invalid OAuth Redirect URI* | Comparez l'URL dans Meta avec la valeur exacte sous *maca Njuvs → Social media* (étape 3). |
| *Invalid Scopes* | Vérifiez les cas d'utilisation à l'étape 2 — ajoutez Page + Instagram. |
| Redirection vers wp-admin / page blanche | Mettez maca Njuvs à jour (OAuth utilise l'URL REST ci-dessus). |
| Instagram absent après connexion | Liez Instagram Business à la page Facebook dans Meta Business Suite. |
| Image seulement, pas de texte | Remplissez **Content** sur l'actualité ; utilisez *Publish again* si déjà publié. |
| *Instagram requires an image* | Téléversez une image sur l'actualité ou l'événement. |
| Jeton expiré | Reconnectez via *Connect Facebook & Instagram* ; maca Njuvs tente de renouveler le jeton automatiquement. |
| Connexion Meta fonctionnait sur un autre site | maca Njuvs stocke sa **propre** connexion Meta par site — configurez l'app et reconnectez sous *maca Njuvs → Social media*. |

---

## Sécurité et confidentialité

- **App Secret** est stocké chiffré dans WordPress — ne le partagez pas publiquement.
- **Page access token** est stocké chiffré sur votre serveur.
- maca.se **n'héberge pas** votre OAuth — tout le trafic passe entre votre site et Meta.
- maca Njuvs stocke les paramètres sociaux dans ses propres tables (`wp_maca_njuvs_*`).
- Mentionnez dans votre politique de confidentialité que des publications peuvent être partagées sur les réseaux sociaux lorsque vous utilisez cette fonction.

---

## Référence rapide dans WordPress

| Emplacement | Objectif |
|-------------|----------|
| *maca Njuvs → Settings* | Activer la publication, URL iCal, **lien vers ce guide** |
| *maca Njuvs → Social media* | App ID/Secret, connexion, test, journal |
| *maca Njuvs → News / Events* | Cocher Facebook/Instagram lors de la publication |
