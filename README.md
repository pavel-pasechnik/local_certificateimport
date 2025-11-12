# Certificate PDF Importer — `local_certificateimport`

[![Moodle](https://img.shields.io/badge/Moodle--4.5+-orange?logo=moodle&style=flat-square)](https://moodle.org/plugins/local_certificateimport)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg?style=flat-square)](https://www.gnu.org/licenses/gpl-3.0)
[![Latest Release](https://img.shields.io/github/v/release/pavel-pasechnik/local_certificateimport?label=Release&style=flat-square)](https://github.com/pavel-pasechnik/local_certificateimport/releases/latest)

> Import legacy PDF certificates (CSV + ZIP) straight into the official `tool_certificate` plugin.

---

## Features

- 📤 Upload a CSV file (`userid,templateid,code,filename,timecreated`) and a ZIP archive with PDF certificates.
- 📝 One-click CSV template download so column order and sample data are ready.
- 📁 Automatically extracts PDFs, creates missing records in `tool_certificate_issues`, and stores files via the Moodle file API.
- 🔁 Idempotent: updates existing issues (code/time) and replaces stored PDFs when needed.
- 📊 Generates an import report showing *User → Code → Status* with “Imported / File not found / Error”.
- 🔐 Respects the dedicated capability `local/certificateimport:import` so you can delegate the task without giving full site admin access.

---

## Installation

1. Copy (or symlink) this directory to `local/certificateimport` in your Moodle codebase.
2. Run the Moodle upgrade script: `php admin/cli/upgrade.php`.
3. (Optional) Purge caches: `php admin/cli/purge_caches.php`.

The plugin requires Moodle 4.5 (2024041900) or newer and the official `tool_certificate` component.

---

## Usage

1. Navigate to **Site administration → Certificates → Certificate PDF import** (or open `/local/certificateimport/index.php`).
2. Upload:
   - CSV file: UTF‑8, comma separator, header optional, columns → `userid,templateid,code,filename,timecreated`.
   - ZIP archive: contains every PDF referenced in the CSV `filename` column.
   - Need a sample? Use the **Download CSV template** button on the page.
3. Click **Import certificates**.
4. Review the report. You can cross-check results via `tool/certificate/index.php`.

### CSV tips

| Column      | Description                                                   |
|-------------|---------------------------------------------------------------|
| `userid`    | Moodle user ID receiving the certificate.                     |
| `templateid`| ID of the certificate template (`tool_certificate_templates`).|
| `code`      | Primary code shown on the certificate (max 40 chars).         |
| `filename`  | PDF filename inside the ZIP archive.                          |
| `timecreated` | Optional UNIX timestamp or date string (e.g. `2025-05-31`, `31.05.2025`). |

### ZIP tips

- Only PDF files are imported; other files are ignored.
- Filenames are matched case-insensitively. If the CSV contains a path (`subdir/file.pdf`) it will be matched as well.
- Each PDF is attached to the `tool_certificate` file area (`component=tool_certificate`, `filearea=issues`).

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
- Custom capability: `db/access.php`.
- Upgrade steps: `db/upgrade.php`.
- Privacy provider: `classes/privacy/provider.php` (no extra data stored).

Contributions and issues are welcome!

---

## License

GNU GPL v3 © 2025 Pavel Pasechnik
