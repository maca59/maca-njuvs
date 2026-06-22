# WordPress.org directory assets

Generated files for the SVN `assets/` folder (not shipped inside the plugin ZIP).

Regenerate after updating source images in `assets/`:

```bash
python tools/build-wporg-assets.py
```

Source files (repo `assets/`):

| Source | Output |
|--------|--------|
| `banner.png` | `banner-772x250.png`, `banner-1544x500.png` |
| `maca-njuvs_ikon.png` | `icon-128x128.png`, `icon-256x256.png` |
| `screenshot-1.png` … `screenshot-6.png` | same names (optimized, max width 1280 px) |

Upload everything in this folder to `https://plugins.svn.wordpress.org/maca-njuvs/assets/`.
