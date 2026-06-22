# WordPress.org SVN release guide — maca Njuvs

This guide covers publishing **maca Njuvs** to the official WordPress.org plugin directory via SVN.

**SVN URL:** `https://plugins.svn.wordpress.org/maca-njuvs/`  
**Plugin page:** https://wordpress.org/plugins/maca-njuvs/  
**SVN browser:** https://plugins.svn.wordpress.org/maca-njuvs/

Use the same WordPress.org username and password you received when the plugin was approved.

---

## One-time setup

### 1. Install SVN

- **Windows:** [TortoiseSVN](https://tortoisesvn.net/) or [Slik SVN](https://sliksvn.com/download/)
- **macOS:** `brew install subversion`
- **Linux:** `sudo apt install subversion`

### 2. Check out the repository

```bash
svn checkout https://plugins.svn.wordpress.org/maca-njuvs/ maca-njuvs-svn
cd maca-njuvs-svn
```

You will see:

```
assets/     # screenshots, banners (optional)
trunk/      # current development / latest release code
tags/       # frozen copies per version (1.0.31, 1.0.32, …)
```

---

## Release workflow (every version)

### Step 1 — Bump version and changelog (Git repo)

In your development repo:

1. Update `Version` and `MACA_NJUVS_VERSION` in `maca-njuvs.php`
2. Set `Stable tag:` in `readme.txt` to the same version
3. Add a `= x.y.z =` section under `== Changelog ==`
4. Optionally add `== Upgrade Notice ==` for the new version

Or run the build script (auto-bumps patch version):

```powershell
.\create-zip.ps1
```

For a specific version without auto-bump:

```powershell
.\create-zip.ps1 -Version 1.0.31
```

### Step 2 — Build a clean release ZIP (sanity check)

```powershell
.\create-zip.ps1 -NoBump
```

Verify `maca-njuvs-{version}.zip` contains `maca-njuvs/maca-njuvs.php` with the correct version header.

### Step 3 — Copy files into SVN `trunk/`

From your plugin source directory, copy **everything that ships in the release** into `maca-njuvs-svn/trunk/`.

**Include:** `maca-njuvs.php`, `readme.txt`, `uninstall.php`, `index.php`, `assets/`, `includes/`, `languages/`, `docs/`, etc.

**Exclude** (see `.distignore`):

- `.git/`, `.gitignore`, `.distignore`
- `*.ps1`, `maca-njuvs-*.zip`
- `.cursor/`, `.vscode/`
- `tools/`

On Windows PowerShell (adjust paths):

```powershell
$src = "C:\Users\maca5\appar\WP-Plugin\maca-njuvs"
$dst = "C:\path\to\maca-njuvs-svn\trunk"

# Remove old trunk contents except .svn
Get-ChildItem $dst -Exclude .svn | Remove-Item -Recurse -Force

# Copy release files (respect .distignore manually or use the ZIP)
Expand-Archive "$src\maca-njuvs-1.0.31.zip" -DestinationPath $env:TEMP\maca-njuvs-extract -Force
Copy-Item "$env:TEMP\maca-njuvs-extract\maca-njuvs\*" $dst -Recurse -Force
```

Using the ZIP is the safest way — it matches exactly what you ship.

### Step 4 — Commit `trunk/`

```bash
cd maca-njuvs-svn
svn status
svn add --force trunk/*
svn delete --force $(svn status trunk | grep '^!' | awk '{print $2}')   # Linux/macOS — remove deleted files
svn commit -m "Updating to version 1.0.31."
```

**Windows (TortoiseSVN):** right-click `trunk` → SVN Commit.

WordPress.org reads `Stable tag:` from `trunk/readme.txt` to know which `tags/` folder is the live release.

### Step 5 — Create the version tag

```bash
svn copy trunk/ tags/1.0.31/ -m "Tagging version 1.0.31."
```

**Important:** Tag folder name must match `Stable tag:` in `readme.txt` exactly (e.g. `1.0.31`, not `v1.0.31`).

### Step 6 — Wait for WordPress.org

- Updates usually appear within **5–15 minutes**
- The plugin page, ZIP download, and auto-updates are generated from `tags/{stable-tag}/`
- Check https://wordpress.org/plugins/maca-njuvs/#developers for commit log

---

## Optional: assets (screenshots & banner)

Place under `assets/` in the SVN root (not inside `trunk/`).

**In this repo:** run `python tools/build-wporg-assets.py` — output is in `wordpress-org/assets/`. Copy those files into your SVN checkout’s `assets/` folder.

| File | Size | Purpose |
|------|------|---------|
| `icon-128x128.png` | 128×128 | Plugin icon |
| `icon-256x256.png` | 256×256 | Retina icon |
| `banner-772x250.png` | 772×250 | Plugin page banner |
| `banner-1544x500.png` | 1544×500 | Retina banner |
| `screenshot-1.png` … `screenshot-5.png` | any | Screenshots (reference in readme `== Screenshots ==`) |

Commit assets separately:

```bash
svn add assets/*
svn commit -m "Add plugin banner and screenshots."
```

---

## Checklist before each release

- [ ] `Version` in `maca-njuvs.php` matches `Stable tag:` in `readme.txt`
- [ ] Changelog entry for the new version
- [ ] `Plugin URI` points to `https://maca.se/maca-njuvs/`
- [ ] Release ZIP builds and validates (`create-zip.ps1`)
- [ ] No dev files in `trunk/` (`.git`, `tools/`, `*.ps1`)
- [ ] `svn copy trunk/ tags/x.y.z/` after trunk commit
- [ ] Tag name equals `Stable tag:`

---

## Troubleshooting

**"Plugin not found" after commit**  
Wait 15 minutes. Confirm `Stable tag:` matches an existing `tags/` folder.

**Wrong version offered to users**  
`Stable tag:` in `trunk/readme.txt` must point to the tag you want live. Re-commit readme or re-tag.

**Authentication failed**  
Use your WordPress.org username and an [Application Password](https://wordpress.org/support/article/application-passwords/) if 2FA is enabled.

**Forgot to tag**  
Users on the old version until you `svn copy trunk/ tags/newversion/`.

---

## Quick reference

```bash
# Full release in one session
svn checkout https://plugins.svn.wordpress.org/maca-njuvs/ maca-njuvs-svn
# … copy release files to trunk/ …
svn add --force trunk/*
svn commit -m "Updating to version 1.0.31."
svn copy trunk/ tags/1.0.31/ -m "Tagging version 1.0.31."
```

Future updates: edit `trunk/`, commit, then `svn copy trunk/ tags/x.y.z/`.
