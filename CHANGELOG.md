# Changelog

## 1.2.0 (2026-10-19) - Moodle 5.2 compatibility

Branch: `MOODLE_502_STABLE`. Requires Moodle 5.2 (`2026042000`) and PHP 8.2+.
Older Moodle versions (4.1 - 4.3) remain supported by the `main` branch.

### Added
- **Test connection** button on the settings page: checks the entered API key and URL (saved or not) via the new
  `local_lionai_reports_test_connection` web service.
- `local/lionai_reports:manage` capability (`db/access.php`), used by the new web service.
- **Export row limit** setting (default 100000); CSV exports are now streamed row by row.
- `local_lionai_reports_save_sql` web service to store manually edited SQL in the report history.
- Notice on the settings page explaining that an AI token (API key) is required.
- Bundled DataTables Bootstrap 5 integration (`amd/src/dataTables.bootstrap5.js`, `css/datatables.bootstrap5.css`).
- PHPUnit tests (API client, `locallib`, test-connection service) and Behat tests (test connection).

### Changed
- Default API URL is now `https://lionai.learnapp.io/v1/execute`.
- No API key is provided by default any more; one must be requested from info@devlion.co.
- Reworked API client (`classes/lionai_reports_api.php`) with improved error handling and clearer messages for invalid
  keys, invalid URLs, bad responses and connection failures.
- Hardened SQL handling: only a single read-only `SELECT` (or `WITH ... SELECT`) statement is executed.
- Updated `locallib.php`, `externallib.php`, `export.php`, `index.php` and `lib.php` for Moodle 5.2 APIs.
- Several web services changed to `write` type where they modify data.
- Updated Mustache templates (list, report, history modal), AMD modules (rebuilt) and styles.
- Updated English and Hebrew language strings; improved privacy provider metadata.
- Refreshed `README.md` (features, requirements, installation, configuration, security and privacy).
- CI: Ubuntu 24.04, PHP 8.3/8.4, Moodle `main`, PostgreSQL 17 / MariaDB 11, `actions/checkout@v6`, Behat faildump
  upload, read-only workflow permissions; removed phpcpd and the Mustache lint step.

### Removed
- Hard-coded default API key and the old `apireprot.learnapp.io` default endpoint.
- Unused `core_reportbuilder\permission` import in `settings.php`.

## 1.0.6.2 (2025-12)
- Added AWS API version support and fixed new API detection.

## 1.0.6.1 (2024-11)
- Make sure the eligibility message is presented.

## 1.0.6 (2024-09)
- Privacy API fixes, `classes` directory renamed to Latin, namespace collision fix.
- `validate_context()` used in every external function.
