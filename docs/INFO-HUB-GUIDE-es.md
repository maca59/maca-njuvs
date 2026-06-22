# maca Njuvs — guía de usuario

**Versión:** 1.0.15  
**Aplica a:** **maca Njuvs** — complemento de WordPress para noticias, eventos, bloques Gutenberg, calendario iCal y uso opcional de Facebook e Instagram.

maca Njuvs le permite crear y publicar noticias y eventos directamente en WordPress. El contenido aparece en su sitio web mediante bloques Gutenberg (o shortcodes) y puede compartirse en redes sociales si tiene una aplicación Meta conectada.

---

## Pestañas de administración

| Pestaña | Qué hace aquí |
|---------|---------------|
| **News** | Crear, editar y gestionar noticias |
| **Events** | Crear, editar y gestionar eventos (incluidas series recurrentes) |
| **Social media** | Conectar aplicación Meta, página de Facebook e Instagram (requiere permiso especial) |
| **Settings** | Activar/desactivar el módulo, URL de iCal, enlace a la guía social |
| **Import** | Importar entradas de WordPress existentes como noticias |
| **Guide** | Esta guía — bloques, ajustes y funciones |

---

## Noticias

En *maca Njuvs → News* crea y edita noticias que se muestran en su sitio web.

### Campos

| Campo | Descripción |
|-------|-------------|
| **Title** | Título principal — visible en el sitio, en el banner y primero en los textos para redes sociales |
| **Excerpt** | Resumen breve para listas y banner. Se incluye en el texto social tras el título |
| **Content** | Texto completo. Al hacer clic se abre el contenido completo (ventana emergente o vista ampliada) |
| **Image** | Imagen opcional de la biblioteca de medios |
| **Status** | Borrador, Programado, Publicado o Archivado |
| **Publish at** | Fecha/hora opcional. Una fecha futura con estado Publicado queda Programado hasta entonces |
| **Expires at** | Opcional — el elemento se oculta automáticamente después de esta fecha |
| **Publishing** | Casillas para sitio web, Facebook e Instagram |

### Estados

- **Draft** — no visible en el sitio web
- **Scheduled** — se publica automáticamente a la hora indicada
- **Published** — visible en el sitio web (si el módulo está activado)
- **Archived** — oculto del sitio web pero guardado en administración

### Consejos sobre imágenes

- Use **Select image** — no pegue imágenes en los campos de extracto o contenido.
- Comprima imágenes grandes (preferiblemente menos de 500 KB). El complemento avisa si la imagen es grande; archivos muy grandes pueden provocar *Please reduce the amount of data* al guardar.

---

## Eventos

En *maca Njuvs → Events* gestiona eventos próximos y recurrentes.

### Campos

| Campo | Descripción |
|-------|-------------|
| **Title** | Nombre del evento |
| **Description** | Texto detallado |
| **Location** | Lugar del evento |
| **Image** | Imagen opcional |
| **Price** | Opcional — se muestra en el sitio si se indica |
| **All day** | Marcar si el evento dura todo el día |
| **Start / End** | Fecha y hora |
| **Recurrence** | Ninguna, Diaria, Semanal o Mensual con intervalo, días de la semana y fecha final o número de repeticiones |
| **Active** | Mostrar en el sitio web |
| **Publishing** | Sitio web, Facebook e Instagram |

### Excepciones en series recurrentes

En eventos recurrentes puede añadir **excepciones** al editar — cancelar o reprogramar una sola fecha sin cambiar toda la serie.

---

## Bloques Gutenberg

Añada bloques de la categoría **maca Njuvs** en el editor de bloques (busque *maca News* o *maca Events*).

### maca News

Muestra noticias publicadas de maca Njuvs.

| Ajuste | Descripción |
|--------|-------------|
| **Layout** | Lista, In page (tabla/columna), Fixed panel izquierda/derecha o Top banner |
| **Number of items** | 1–20 noticias |
| **Scrolling ticker** | (Banner) Desplazamiento horizontal continuo |
| **Show image** | (Lista) Mostrar miniatura |
| **Show date** | Mostrar fecha de publicación |
| **Show excerpt** | Mostrar resumen breve |

**Consejos de diseño:**

- **List** — vista estándar con imagen opcional
- **In page** — permanece donde coloca el bloque, p. ej. en tablas y columnas
- **Fixed panel** — fijo al desplazarse en escritorio; en móvil aparece al final de la página. El clic abre el artículo completo en ventana emergente
- **Top banner** — franja superior. Use como máximo un bloque banner por página

### maca Events

Muestra eventos próximos.

| Ajuste | Descripción |
|--------|-------------|
| **View** | Lista o calendario mensual |
| **Number of events** | 1–30 (vista de lista) |
| **Show image** | (Vista de lista) |
| **Show location** | (Vista de lista) |
| **Week starts on Monday** | (Calendario mensual) |
| **Show calendar subscription** | Enlaces para suscribirse al feed iCal |

---

## Shortcodes

Si no usa el editor de bloques, el mismo contenido puede mostrarse con shortcodes:

### Noticias

```
[maca_njuvs_news limit="5" layout="list" show_image="1" show_date="1" show_excerpt="1" banner_scroll="1"]
```

| Atributo | Valores | Predeterminado |
|----------|---------|----------------|
| `limit` | 1–50 | 5 |
| `layout` | `list`, `embedded`, `sidebar-left`, `sidebar-right`, `banner` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_date` | `1` / `0` | `1` |
| `show_excerpt` | `1` / `0` | `1` |
| `banner_scroll` | `1` / `0` | `1` |

### Eventos

```
[maca_njuvs_events limit="10" view="list" show_image="1" show_location="1" show_subscribe="1"]
```

| Atributo | Valores | Predeterminado |
|----------|---------|----------------|
| `limit` | 1–50 | 10 |
| `view` | `list`, `month` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_location` | `1` / `0` | `1` |
| `show_subscribe` | `1` / `0` | `1` |

### Suscripción al calendario

```
[maca_njuvs_calendar_subscribe]
```

Muestra enlaces para suscribirse al calendario de eventos en aplicaciones de calendario.

---

## Ajustes

En *maca Njuvs → Settings*:

| Ajuste | Descripción |
|--------|-------------|
| **Enable maca Njuvs** | Interruptor principal — desactivado no se muestra contenido en el sitio ni en bloques |
| **iCal feed URL** | Feed público para aplicaciones de calendario: `{{ICAL_URL}}` |
| **Subscribe URL** | Enlace webcal para Apple Calendar y otros: `{{WEBCAL_URL}}` |

> **Consejo:** Si el feed iCal devuelve 404, guarde una vez en *Ajustes → Enlaces permanentes* de WordPress.

### Facebook e Instagram

La conexión con redes sociales se gestiona en *Social media*. Hay una guía paso a paso separada mediante el botón *Setup guide: Facebook & Instagram* en la página de ajustes.

---

## Importación

En *maca Njuvs → Import* puede copiar entradas de WordPress existentes a maca Njuvs como noticias.

| Opción | Descripción |
|--------|-------------|
| **Content type** | Entrada o página |
| **Category** | Filtro opcional (solo entradas) |
| **Skip already imported** | Evitar duplicados |

Las entradas originales no se eliminan — la importación crea nuevas noticias en maca Njuvs.

---

## Otras funciones

- **Calendario iCal** — los eventos se exportan a un feed público que se actualiza al cambiar
- **Publicación programada** — las noticias pueden publicarse a la hora indicada sin intervención manual
- **Fecha de caducidad** — las noticias pueden ocultarse automáticamente
- **Eventos recurrentes** — series diarias, semanales y mensuales con excepciones
- **Publicación social** — uso opcional en Facebook Page e Instagram Business al guardar (requiere aplicación Meta)

---

## Inicio rápido

1. Active maca Njuvs en *Settings*
2. Cree al menos una noticia o un evento
3. Añada los bloques **maca News** y **maca Events** en una página
4. (Opcional) Conecte Facebook/Instagram en *Social media*
5. (Opcional) Comparta la URL iCal para que los visitantes se suscriban al calendario

---

## Términos de uso

Al usar **maca Njuvs** acepta los siguientes términos:

1. **Licencia** — El complemento se distribuye bajo GNU General Public License v2 o posterior (GPL v2+). Puede usar, modificar y distribuir el complemento según los términos de la licencia.
2. **Responsabilidad del contenido** — Como propietario del sitio es responsable de todo el contenido (noticias, eventos, imágenes y textos) que publique mediante el complemento, en su sitio web y en redes sociales conectadas.
3. **Servicios de terceros** — Las funciones con Facebook, Instagram y la API Meta Graph API se rigen por los términos de cada servicio. Debe cumplir las políticas de Meta y tener los derechos necesarios sobre el contenido compartido.
4. **Sin garantía** — maca Njuvs se proporciona tal cual sin garantía expresa o implícita. Maca Development no es responsable de interrupciones, pérdida de datos o daños por el uso del complemento.
5. **Limitación de responsabilidad** — En la medida permitida por la ley, Maca Development no responde por daños indirectos, lucro cesante o pérdida de datos derivados del complemento o servicios integrados.
6. **Actualizaciones** — Las funciones pueden cambiar o eliminarse en versiones futuras. Recomendamos hacer copia de seguridad antes de actualizar.

## Política de privacidad

maca Njuvs procesa los datos localmente en su sitio WordPress. Como propietario del sitio es el responsable del tratamiento de datos de visitantes y contenido según la legislación aplicable, p. ej. RGPD.

### Qué datos se almacenan

| Datos | Dónde | Finalidad |
|-------|-------|-----------|
| Noticias y eventos | Base de datos WordPress (tablas propias) | Publicación en el sitio y en bloques |
| URL de imágenes y textos | Misma base de datos | Visualización y uso en redes sociales |
| Meta App ID, tokens, etc. | Opciones de WordPress (cifrado cuando corresponda) | Publicación en Facebook/Instagram |
| Registro de publicación social | Base de datos WordPress | Diagnóstico y estado en administración |
| ID de entradas importadas | Metadatos de entradas | Evitar duplicados en importación |

### Qué datos se comparten externamente

- **Por defecto no se envían datos a Maca Development** al usar el complemento.
- **El feed iCal** (`{{ICAL_URL}}`) es público — título, hora, lugar y descripción de eventos activos pueden leerlos quienes tengan el enlace.
- **La publicación social** envía contenido e imágenes a Meta (Facebook/Instagram) según su configuración y la API de Meta.

### Conservación y eliminación

- Los datos permanecen tras desinstalar salvo que la constante `MACA_NJUVS_UNINSTALL_DROP_DATA` esté en `true` antes de desinstalar.
- Puede eliminar noticias, eventos y conexiones sociales en cualquier momento en administración.

### Sus obligaciones

- Informe a los visitantes en la **política de privacidad** de su sitio sobre el feed iCal, tecnologías de seguimiento (vía otros complementos) y publicación social.
- Indique una URL pública de política de privacidad en su aplicación Meta si usa la conexión Facebook/Instagram (requisito de Meta).

### Contacto

Soporte y preguntas sobre el complemento: [maca.se](https://maca.se/)
