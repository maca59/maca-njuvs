# Paso a paso: conectar Facebook e Instagram a maca Njuvs

**Versión:** 1.0.15  
**Aplica a:** **maca Njuvs** — complemento de WordPress para noticias, eventos, calendario iCal y uso en redes sociales

Esta guía le ayuda a publicar noticias y eventos desde **maca Njuvs** en su **página de Facebook** y su **cuenta de Instagram Business**. maca Njuvs no aloja su inicio de sesión de Meta de forma centralizada — usted crea su propia aplicación en [Meta for Developers](https://developers.facebook.com/) y la conecta a su sitio WordPress.

---

## Antes de empezar

1. **maca Njuvs instalado y activo** — el complemento debe aparecer en *Plugins* de WordPress.
2. **Publicación activada** — *maca Njuvs → Settings* → marque *Enable maca Njuvs* y guarde.
3. **Página de Facebook** — debe ser administrador de la página donde desea publicar.
4. **Instagram Business o Creator** — la cuenta debe estar **vinculada a la página de Facebook** (en Meta Business Suite o en la app de Instagram en *Perfil → Editar perfil → Páginas*).
5. **HTTPS** — la **dirección del sitio** de WordPress debe comenzar con `https://`.
6. **Cuenta de Meta Developer** — cuenta gratuita en [developers.facebook.com](https://developers.facebook.com/).

> **Consejo:** Abra *maca Njuvs → Social media* junto con esta guía. Allí verá la URL de redirección OAuth e introducirá App ID y App Secret.

---

## Resumen

| Paso | Dónde | Qué |
|------|-------|-----|
| 1 | Meta for Developers | Crear aplicación |
| 2 | Aplicación Meta | Elegir los **casos de uso** correctos (Page + Instagram) |
| 3 | Aplicación Meta | Dominios, política de privacidad, redirección OAuth |
| 4 | Aplicación Meta | Permisos |
| 5 | WordPress (maca Njuvs) | App ID + App Secret |
| 6 | WordPress (maca Njuvs) | Conectar Facebook y elegir página |
| 7 | WordPress (maca Njuvs) | Probar publicación |
| 8 | WordPress (maca Njuvs) | Publicar noticias y eventos |

---

## Paso 1 — Crear aplicación Meta

1. Vaya a [developers.facebook.com/apps](https://developers.facebook.com/apps) y haga clic en **Crear aplicación**.
2. Elija **Business** como tipo de aplicación si se le solicita.
3. Asigne un nombre claro, p. ej. *Nombre de su empresa – maca Njuvs*.
4. Seleccione su cartera de **Business Manager** si se le pregunta.
5. Cree la aplicación y anote el **App ID** (visible en la parte superior del panel).

---

## Paso 2 — Añadir los casos de uso correctos

Este paso es importante — casos de uso incorrectos provocan errores de permisos y OAuth.

1. En el panel de la aplicación: **Casos de uso** → **Añadir casos de uso**.
2. Añada **Gestionar todo en su Page**.
3. Añada **Gestionar mensajes y contenido en Instagram**.

**No use** solo el *Facebook Login* genérico — no basta para publicar en Page e Instagram.

---

## Paso 3 — Configuración de la aplicación

### Dominios de la aplicación

En **Configuración de la aplicación → Básico**:

- **Dominios de la aplicación:** dominio de su sitio sin `https://`, p. ej. `{{SITE_DOMAIN}}`.
- **URL de política de privacidad:** página HTTPS pública con política de privacidad (requerido por Meta). Ejemplo: `https://maca.se/policy/`
- **Sitio web:** URL de su sitio, p. ej. `https://{{SITE_DOMAIN}}`

Guarde los cambios.

### URI de redirección OAuth

1. Vaya a **Casos de uso → Facebook Login for Business** (o el producto Login vinculado a su aplicación).
2. En **Configuración** / **Valid OAuth Redirect URIs**, pegue **exactamente** la URL que aparece en WordPress en *maca Njuvs → Social media → OAuth redirect URI*:

```
{{OAUTH_REDIRECT_URI}}
```

3. Guarde. La URL debe coincidir **carácter por carácter** — sin barra extra, sin `http` si el sitio usa `https`.

---

## Paso 4 — Permisos

maca Njuvs necesita estos permisos al conectar (Meta puede mostrarlos al iniciar sesión):

| Permiso | Finalidad |
|---------|-----------|
| `pages_show_list` | Listar páginas que administra |
| `pages_manage_posts` | Publicar en la página de Facebook |
| `pages_read_engagement` | Leer información básica de la página |
| `instagram_basic` | Vincular cuenta de Instagram Business |
| `instagram_content_publish` | Publicar en Instagram |
| `business_management` | Vincular Page e Instagram en Business Manager |

En **modo de desarrollo** funciona para administradores y probadores de la app. En producción, Meta puede exigir **App Review** y **Business Verification** — siga la lista de Meta en Developer Console.

> **No se requieren webhooks** para publicar noticias y eventos desde maca Njuvs.

---

## Paso 5 — Introducir App ID y App Secret en WordPress

1. Vaya a *maca Njuvs → Social media*.
2. En **Meta app credentials**:
   - **App ID** — desde Meta Developer Console
   - **App Secret** — en *Configuración → Básico* (clic en *Mostrar*)
3. **Test image URL** (opcional pero recomendado) — imagen HTTPS pública para pruebas en Instagram (Instagram siempre requiere imagen).
4. Haga clic en **Save Meta settings**.

---

## Paso 6 — Conectar Facebook y elegir página

1. Haga clic en **Connect Facebook & Instagram**.
2. Inicie sesión con una cuenta que sea **administrador** de la página de Facebook.
3. Apruebe los permisos que muestra Meta.
4. Seleccione **qué página de Facebook** conectar (si tiene varias).
5. Confirme — debe ver el nombre de la página y opcionalmente `@usuario-instagram` en **Connection**.

Si Instagram no aparece: verifique que la cuenta sea **Business/Creator** y esté **vinculada a esa página de Facebook**.

---

## Paso 7 — Probar publicación

1. Complete **Test image URL** si falta (imagen HTTPS pública).
2. Haga clic en **Test publish** en la pestaña *Social media*.
3. Compruebe que aparece una publicación de prueba en la página de Facebook (e Instagram si está conectado).
4. En caso de error: consulte **Publish log** más abajo en la misma pestaña — allí se guardan los mensajes de error de la API de Meta.

---

## Paso 8 — Publicar noticias y eventos

1. Cree o edite una **noticia** o **evento** en *maca Njuvs → News* o *maca Njuvs → Events*.
2. Establezca el estado en **Published** (o programado con fecha ya pasada).
3. En **Publishing** — marque **Facebook** y/o **Instagram**.
4. **Instagram requiere imagen** en la noticia o evento.
5. Guarde — la publicación se ejecuta de inmediato si Facebook está conectado.

**Texto social:** título más contenido (o extracto si está rellenado) se envía como pie de foto. En Instagram el texto puede quedar bajo la imagen — pulse *más* para leer todo.

**Publicar de nuevo:** si ya se publicó, puede marcar *Publish again to Facebook/Instagram* al editar la noticia.

---

## Solución de problemas

| Problema | Solución |
|----------|----------|
| *Invalid OAuth Redirect URI* | Compare la URL en Meta con el valor exacto en *maca Njuvs → Social media* (paso 3). |
| *Invalid Scopes* | Revise los casos de uso en el paso 2 — añada Page + Instagram. |
| Redirección a wp-admin / página en blanco | Actualice maca Njuvs a la última versión (OAuth usa la URL REST anterior). |
| Instagram ausente tras conectar | Vincule Instagram Business a la página de Facebook en Meta Business Suite. |
| Solo imagen, sin texto | Rellene **Content** en la noticia; use *Publish again* si ya se publicó. |
| *Instagram requires an image* | Suba una imagen en la noticia o evento. |
| Token caducado | Conecte de nuevo con *Connect Facebook & Instagram*; maca Njuvs intenta renovar el token automáticamente. |
| La conexión Meta funcionó en otro sitio | maca Njuvs guarda su **propia** conexión Meta por sitio — configure la app y conecte de nuevo en *maca Njuvs → Social media*. |

---

## Seguridad y privacidad

- **App Secret** se almacena cifrado en WordPress — no lo comparta públicamente.
- **Page access token** se almacena cifrado en su servidor.
- maca.se **no aloja** su OAuth — todo el tráfico va entre su sitio y Meta.
- maca Njuvs guarda la configuración social en tablas propias (`wp_maca_njuvs_*`).
- Mencione en su política de privacidad que las publicaciones pueden compartirse en redes sociales al usar esta función.

---

## Referencia rápida en WordPress

| Ubicación | Finalidad |
|-----------|-----------|
| *maca Njuvs → Settings* | Activar publicación, URL iCal, **enlace a esta guía** |
| *maca Njuvs → Social media* | App ID/Secret, conexión, prueba, registro |
| *maca Njuvs → News / Events* | Marcar Facebook/Instagram al publicar |
