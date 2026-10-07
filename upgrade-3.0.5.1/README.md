# dekor.gr — OpenCart 3.0.2.0 → 3.0.5.1 on PHP 8.1

## What changed

- Core updated to OpenCart 3.0.5.1. The 94 core files with site edits were merged three-way
  (site / 3.0.2.0 / 3.0.5.1); the site edits are kept. Unchanged core files are stock 3.0.5.1.
- Kept on purpose as in 3.0.2.0 (marked `// dekor:` in the code):
  - DB session time zone is never changed (`system/framework.php`, both `startup/startup.php`).
  - No no-cache headers on every page (`system/framework.php`).
  - `SET SQL_MODE = ''` (`system/library/db/mysqli.php`).
  - Request input is not `trim()`med (`system/library/request.php`).
  - Language/currency cookies keep the host domain (`catalog/controller/startup/startup.php`).
  - DB sessions keep the old expiry logic (`system/library/session/db.php`): the 3.0.5.1 one
    expires carts after ~24 min and runs an unbounded DELETE on the session table in page requests.
  - Admin stays on jQuery 2.1.1 (old admin extensions).
  - Slider modules load the same swiper file as the theme.
- Twig 1 → Twig 3: `system/library/template/twig.php` adds a filesystem loader (theme
  `{% include %}`s) and the d_twig_manager extension, ported in
  `system/library/template/Twig/Extension/DTwigManager3.php`.
- PHP 8.1 fixes: `{}` string offsets (PHPExcel, Social Login, OnePage Checkout, pp_express,
  xlsxwriter), d_seo_module `reset()` on non-arrays (product/information forms), X-Shipping Pro
  admin template, Tag Manager signature, PHP 8 deprecations not printed into pages/JSON
  (`system/startup.php`).
- `system/storage/vendor/` (Twig 3, Guzzle, scssphp…) is now in git — the site does not start without it.

## Deploy

1. Backup files + database.
2. Upload the files. **`system/storage/vendor/` must end up in the live `DIR_STORAGE`**
   (check `DIR_STORAGE` in the live `config.php`; if storage lives outside `public_html`, copy it there).
3. Do not upload `install/` (not included).
4. `php upgrade-3.0.5.1/apply_db.php` (dry run), then `php upgrade-3.0.5.1/apply_db.php --apply`.
   Optional, independent of the upgrade: `php upgrade-3.0.5.1/fix_seo_slugs.php` (dry run) then `--apply` — fixes the 47 product SEO URLs that contain spaces/commas (meta keywords pasted into the SEO URL field) and adds 301s from the old URLs via the iSenseLabs 404-redirect table.
   The stock upgrade wizard is not used: it drops every non-stock index, adds Google Ads events
   and converts collations.
5. Admin → Extensions → Modifications → **Refresh** (twice: the 2nd run uses the refreshed
   Modification Manager). For the few seconds of the refresh the storefront throws the Tag
   Manager `isActive()` error — do it at a quiet time.
6. Clear `system/storage/cache/` (incl. `template/`).
7. Switch the domain to PHP 8.1.
