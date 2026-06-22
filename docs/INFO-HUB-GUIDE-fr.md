# maca Njuvs — guide utilisateur

**Version :** 1.0.15  
**S'applique à :** **maca Njuvs** — extension WordPress pour actualités, événements, blocs Gutenberg, calendrier iCal et partage optionnel sur Facebook et Instagram.

maca Njuvs vous permet de créer et publier des actualités et des événements directement dans WordPress. Le contenu s'affiche sur votre site via des blocs Gutenberg (ou des shortcodes) et peut être partagé sur les réseaux sociaux si une application Meta est connectée.

---

## Onglets d'administration

| Onglet | Ce que vous faites ici |
|--------|------------------------|
| **News** | Créer, modifier et gérer les actualités |
| **Events** | Créer, modifier et gérer les événements (y compris les séries récurrentes) |
| **Social media** | Connecter l'application Meta, la page Facebook et Instagram (autorisation spéciale requise) |
| **Settings** | Activer/désactiver le module, URL iCal, lien vers le guide social |
| **Import** | Importer des articles WordPress existants comme actualités |
| **Guide** | Ce guide — blocs, paramètres et fonctionnalités |

---

## Actualités

Sous *maca Njuvs → News*, vous créez et modifiez les actualités affichées sur votre site.

### Champs

| Champ | Description |
|-------|-------------|
| **Title** | Titre principal — affiché sur le site, dans la bannière et en premier dans les textes pour les réseaux sociaux |
| **Excerpt** | Résumé court pour les listes et la bannière. Inclus dans le texte social après le titre |
| **Content** | Texte complet. Un clic ouvre le contenu intégral (fenêtre modale ou vue développée) |
| **Image** | Image optionnelle depuis la médiathèque |
| **Status** | Brouillon, Planifié, Publié ou Archivé |
| **Publish at** | Date/heure optionnelle. Une date future avec le statut Publié devient Planifié jusqu'à cette date |
| **Expires at** | Optionnel — l'élément est masqué automatiquement après cette date |
| **Publishing** | Cases à cocher pour le site web, Facebook et Instagram |

### Statuts

- **Draft** — non visible sur le site web
- **Scheduled** — publié automatiquement à l'heure définie
- **Published** — visible sur le site web (si le module est activé)
- **Archived** — masqué du site web mais conservé dans l'administration

### Conseils sur les images

- Utilisez **Select image** — ne collez pas d'images dans les champs extrait ou contenu.
- Compressez les grandes images (de préférence moins de 500 Ko). L'extension avertit si l'image est volumineuse ; des fichiers très lourds peuvent provoquer *Please reduce the amount of data* à l'enregistrement.

---

## Événements

Sous *maca Njuvs → Events*, vous gérez les événements à venir et récurrents.

### Champs

| Champ | Description |
|-------|-------------|
| **Title** | Nom de l'événement |
| **Description** | Texte détaillé |
| **Location** | Lieu de l'événement |
| **Image** | Image optionnelle |
| **Price** | Optionnel — affiché sur le site si renseigné |
| **All day** | Cocher si l'événement dure toute la journée |
| **Start / End** | Date et heure |
| **Recurrence** | Aucune, Quotidienne, Hebdomadaire ou Mensuelle avec intervalle, jours de la semaine et date de fin ou nombre d'occurrences |
| **Active** | Afficher sur le site web |
| **Publishing** | Site web, Facebook et Instagram |

### Exceptions dans les séries récurrentes

Pour les événements récurrents, vous pouvez ajouter des **exceptions** lors de la modification — annuler ou reprogrammer une seule date sans modifier toute la série.

---

## Blocs Gutenberg

Ajoutez des blocs de la catégorie **maca Njuvs** dans l'éditeur de blocs (recherchez *maca News* ou *maca Events*).

### maca News

Affiche les actualités publiées depuis maca Njuvs.

| Paramètre | Description |
|-----------|-------------|
| **Layout** | Liste, In page (tableau/colonne), Fixed panel gauche/droite ou Top banner |
| **Number of items** | 1–20 actualités |
| **Scrolling ticker** | (Bannière) Défilement horizontal continu |
| **Show image** | (Liste) Afficher la miniature |
| **Show date** | Afficher la date de publication |
| **Show excerpt** | Afficher le résumé court |

**Conseils de mise en page :**

- **List** — vue standard avec image optionnelle
- **In page** — reste où vous placez le bloc, p. ex. dans des tableaux et colonnes
- **Fixed panel** — fixe sur le côté au défilement sur ordinateur ; sur mobile en bas de page. Le clic ouvre l'article complet dans une fenêtre modale
- **Top banner** — bandeau en haut. Utilisez au plus un bloc bannière par page

### maca Events

Affiche les événements à venir.

| Paramètre | Description |
|-----------|-------------|
| **View** | Liste ou calendrier mensuel |
| **Number of events** | 1–30 (vue liste) |
| **Show image** | (Vue liste) |
| **Show location** | (Vue liste) |
| **Week starts on Monday** | (Calendrier mensuel) |
| **Show calendar subscription** | Liens pour s'abonner au flux iCal |

---

## Shortcodes

Sans l'éditeur de blocs, le même contenu peut s'afficher via des shortcodes :

### Actualités

```
[maca_njuvs_news limit="5" layout="list" show_image="1" show_date="1" show_excerpt="1" banner_scroll="1"]
```

| Attribut | Valeurs | Par défaut |
|----------|---------|------------|
| `limit` | 1–50 | 5 |
| `layout` | `list`, `embedded`, `sidebar-left`, `sidebar-right`, `banner` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_date` | `1` / `0` | `1` |
| `show_excerpt` | `1` / `0` | `1` |
| `banner_scroll` | `1` / `0` | `1` |

### Événements

```
[maca_njuvs_events limit="10" view="list" show_image="1" show_location="1" show_subscribe="1"]
```

| Attribut | Valeurs | Par défaut |
|----------|---------|------------|
| `limit` | 1–50 | 10 |
| `view` | `list`, `month` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_location` | `1` / `0` | `1` |
| `show_subscribe` | `1` / `0` | `1` |

### Abonnement au calendrier

```
[maca_njuvs_calendar_subscribe]
```

Affiche des liens pour s'abonner au calendrier des événements dans les applications de calendrier.

---

## Paramètres

Sous *maca Njuvs → Settings* :

| Paramètre | Description |
|-----------|-------------|
| **Enable maca Njuvs** | Interrupteur principal — désactivé, aucun contenu sur le site ni dans les blocs |
| **iCal feed URL** | Flux public pour les applications de calendrier : `{{ICAL_URL}}` |
| **Subscribe URL** | Lien webcal pour Apple Calendar et autres : `{{WEBCAL_URL}}` |

> **Astuce :** Si le flux iCal renvoie 404, enregistrez une fois sous *Réglages → Permaliens* dans WordPress.

### Facebook et Instagram

La connexion aux réseaux sociaux se gère sous *Social media*. Un guide pas à pas séparé est disponible via le bouton *Setup guide: Facebook & Instagram* sur la page des paramètres.

---

## Import

Sous *maca Njuvs → Import*, vous pouvez copier des articles WordPress existants dans maca Njuvs comme actualités.

| Option | Description |
|--------|-------------|
| **Content type** | Article ou page |
| **Category** | Filtre optionnel (articles uniquement) |
| **Skip already imported** | Éviter les doublons |

Les articles originaux ne sont pas supprimés — l'import crée de nouvelles actualités dans maca Njuvs.

---

## Autres fonctionnalités

- **Calendrier iCal** — les événements sont exportés vers un flux public mis à jour lors des modifications
- **Publication planifiée** — les actualités peuvent être publiées à l'heure définie sans action manuelle
- **Date d'expiration** — les actualités peuvent être masquées automatiquement
- **Événements récurrents** — séries quotidiennes, hebdomadaires et mensuelles avec exceptions
- **Publication sociale** — partage optionnel sur Facebook Page et Instagram Business à l'enregistrement (application Meta requise)

---

## Démarrage rapide

1. Activez maca Njuvs sous *Settings*
2. Créez au moins une actualité ou un événement
3. Ajoutez les blocs **maca News** et **maca Events** sur une page
4. (Optionnel) Connectez Facebook/Instagram sous *Social media*
5. (Optionnel) Partagez l'URL iCal pour que les visiteurs s'abonnent au calendrier

---

## Conditions d'utilisation

En utilisant **maca Njuvs**, vous acceptez les conditions suivantes :

1. **Licence** — L'extension est distribuée sous GNU General Public License v2 ou ultérieure (GPL v2+). Vous pouvez utiliser, modifier et distribuer l'extension selon les termes de la licence.
2. **Responsabilité du contenu** — En tant que propriétaire du site, vous êtes responsable de tout contenu (actualités, événements, images et textes) publié via l'extension, sur votre site et via les réseaux sociaux connectés.
3. **Services tiers** — Les fonctionnalités impliquant Facebook, Instagram et l'API Meta Graph API sont régies par les conditions de chaque service. Vous devez respecter les règles de la plateforme Meta et détenir les droits nécessaires sur le contenu partagé.
4. **Absence de garantie** — maca Njuvs est fourni tel quel sans garantie expresse ou implicite. Maca Development n'est pas responsable des interruptions, pertes de données ou dommages liés à l'utilisation de l'extension.
5. **Limitation de responsabilité** — Dans la mesure permise par la loi, Maca Development n'est pas responsable des dommages indirects, pertes de profit ou pertes de données résultant de l'extension ou des services intégrés.
6. **Mises à jour** — Les fonctionnalités peuvent changer ou être supprimées dans les versions futures. Nous recommandons une sauvegarde avant les mises à jour.

## Politique de confidentialité

maca Njuvs traite les données localement sur votre site WordPress. En tant que propriétaire du site, vous êtes responsable du traitement des données des visiteurs et du contenu selon la loi applicable, p. ex. RGPD.

### Données stockées

| Données | Où | Finalité |
|---------|-----|----------|
| Actualités et événements | Base de données WordPress (tables dédiées) | Publication sur le site et dans les blocs |
| URL d'images et textes | Même base de données | Affichage et partage social |
| Meta App ID, jetons, etc. | Options WordPress (chiffrées le cas échéant) | Publication Facebook/Instagram |
| Journal de publication sociale | Base de données WordPress | Dépannage et statut dans l'administration |
| ID d'articles importés | Métadonnées d'articles | Éviter les doublons à l'import |

### Données partagées à l'extérieur

- **Aucune donnée n'est envoyée à Maca Development** par défaut lors de l'utilisation de l'extension.
- **Le flux iCal** (`{{ICAL_URL}}`) est public — titre, heure, lieu et description des événements actifs peuvent être lus par toute personne disposant du lien.
- **La publication sociale** envoie contenu et images à Meta (Facebook/Instagram) selon vos paramètres et l'API Meta.

### Conservation et suppression

- Les données restent après désinstallation sauf si la constante `MACA_NJUVS_UNINSTALL_DROP_DATA` est définie à `true` avant la désinstallation.
- Vous pouvez supprimer actualités, événements et connexions sociales à tout moment dans l'administration.

### Vos obligations

- Informez les visiteurs dans la **politique de confidentialité** de votre site du flux iCal, des technologies de suivi éventuelles (via d'autres extensions) et de la publication sociale.
- Indiquez une URL publique de politique de confidentialité dans votre application Meta si vous utilisez la connexion Facebook/Instagram (exigence Meta).

### Contact

Support et questions sur l'extension : [maca.se](https://maca.se/)
