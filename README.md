# Certificate PDF Importer — `local_certificateimport`

[![Moodle](https://img.shields.io/badge/Moodle--4.5+-orange?logo=moodle&style=flat-square)](https://moodle.org/plugins/local_certificateimport)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg?style=flat-square)](https://www.gnu.org/licenses/gpl-3.0)
[![Latest Release](https://img.shields.io/github/v/release/pavel-pasechnik/local_certificateimport?label=Release&style=flat-square)](https://github.com/pavel-pasechnik/local_certificateimport/releases/latest)

> Import legacy PDF certificates (CSV + ZIP) straight into the official `tool_certificate` plugin.

---

## Features

- 📤 Upload a CSV file (`userid,filename,timecreated`) and a ZIP archive with PDF certificates.
- 📝 One-click CSV template download so column order and sample data are ready.
- 🎯 Pick the destination certificate template from a dropdown instead of memorising template IDs.
- 🔢 Certificate numbers are generated automatically by the official `tool_certificate` component, so the CSV `code` column can stay empty.
- 📁 Automatically extracts PDFs, converts them to JPEG backgrounds, and stores the images via the Moodle file API (no original PDFs kept).
- 🕒 Background conversion runs asynchronously via Moodle cron, so big uploads no longer block the browser while Ghostscript/pdftoppm does the heavy lifting.
- 🔁 Issues are created via the standard `tool_certificate` API during the “Register certificates” step, so numbering, notifications, and reports stay native.
- ⏱️ Batches decouple the heavy ZIP import from the registration phase, so you can prepare multiple uploads and trigger issuance when ready.
- 📊 Built-in report lists every imported certificate with filtering (template/user/status/date), pagination, CSV export, the ability to reissue revoked entries in bulk, delete revoked/not-issued records, and inspect background previews inline.
- ⚖️ Site admins can cap how many certificates (CSV rows) and how large the ZIP archive may be per import run, and even override Ghostscript/pdftoppm paths, to keep the server safe from oversized or misconfigured uploads.
- 🔐 Respects the dedicated capability `local/certificateimport:import` so you can delegate the task without giving full site admin access.

---

## Installation

1. Copy (or symlink) this directory to `local/certificateimport` in your Moodle codebase.
2. Run the Moodle upgrade script: `php admin/cli/upgrade.php`.
3. (Optional) Purge caches: `php admin/cli/purge_caches.php`.

The plugin requires Moodle 4.5 (2024041900) or newer and the official `tool_certificate` component.  
PDF conversion depends on the Imagick PHP extension (recommended) or one of the CLI tools `convert`, `pdftoppm`, or `gs` (Ghostscript).

---

## Usage

1. Navigate to **Site administration → Certificates → Certificate PDF import** (or open `/local/certificateimport/index.php`).
2. Choose the certificate template from the dropdown, then upload:
   - CSV file: UTF‑8, comma separator, header optional, columns → `userid,filename,timecreated` (the `timecreated` column is optional and may stay blank).
   - ZIP archive: contains every PDF referenced in the CSV `filename` column.
   - Need a sample? Use the **Download CSV template** button on the page.
3. Click **Import certificates** — the plugin extracts the ZIP, stores PDFs, and queues background conversion jobs that cron will process in the background.
4. Scroll down to **Import batches** and press **Register certificates** for the desired batch when you want to issue the certificates via `tool_certificate`. The official plugin handles numbering, PDF generation, and notifications at that stage.
5. Review both the on-page report (you can still **Export CSV**) and the batch table to see which certificates are queued, registered, or failed.

### CSV tips

| Column      | Description                                                   |
|-------------|---------------------------------------------------------------|
| `userid`    | Moodle user ID receiving the certificate.                     |
| `filename`  | PDF filename inside the ZIP archive.                          |
| `timecreated` | Optional UNIX timestamp or date string (e.g. `2025-05-31`, `31.05.2025`). |

### ZIP tips

- Only PDF files are imported; other files are ignored.
- Filenames are matched case-insensitively. If the CSV contains a path (`subdir/file.pdf`) it will be matched as well.
- PDFs are deleted after conversion; only the generated JPEG background is stored until the registration step.
- The final certificates (PDF) are produced by `tool_certificate` and stored in its native file area.

---

## Permissions

| Capability                          | Default | Purpose                            |
|-------------------------------------|---------|------------------------------------|
| `local/certificateimport:import`    | Manager | Allows access to the import screen |

Grant this capability to trusted roles if you need to delegate certificate uploads without full admin rights.

---

## Development Notes

- Business logic lives in `lib.php` (`local_certificateimport_run_import()` and helpers).
- The upload form is defined in `classes/form/import_form.php`.
- PDF conversion helper: `classes/local/converter.php`.
- Registration workflow: `classes/local/registrar.php`.
- Import batches/items are stored in `local_certimp_batches` and `local_certimp_items`.
- Custom capability: `db/access.php`.
- Upgrade steps: `db/upgrade.php`.
- Privacy provider: `classes/privacy/provider.php` (no extra data stored).

Contributions and issues are welcome!

---

## License

GNU GPL v3 © 2025 Pavel Pasechnik
- To inspect previous uploads, open **Site administration → Certificates → Imported certificates report** (or `/local/certificateimport/report.php`). You can filter by template, status, user or date, export the visible rows to CSV, and reissue any revoked certificates directly from this screen.
