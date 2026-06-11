# ASTALA CodeIgniter 4 Migration

This folder is the CodeIgniter 4 recreation of the existing Node/Express ASTALA app.

## Current Status

- CodeIgniter 4 app scaffolded with `codeigniter4/appstarter`.
- Existing public assets copied into `public/`.
- Database settings mapped from the Node Sequelize config into `.env`.
- Auth flow ported:
  - login
  - signup
  - logout
  - SSO JWT verification
  - activity logging
- CI4 filters added for the old Express middleware rules:
  - `auth`
  - `guest`
  - `role`
  - `canEdit`
  - `canViewInventory`
- CI4 models added for the existing tables.
- Existing Express route structure mirrored in `app/Config/Routes.php`.
- Database migration added for the ASTALA schema.
- Super-admin seeder added.
- Dashboard module ported:
  - role-aware dashboard page
  - admin/manager inventory and pengambilan stats
  - mitra pengambilan summary
  - karyawan peminjaman summary
  - loan and pengambilan chart APIs
- Barang inventory module ported:
  - list/search/filter/pagination
  - add/edit/delete
  - detail page with photos and loan timeline
  - photo upload handling
  - barcode and name autocomplete APIs
  - QR-required toggle
- Peminjaman module ported:
  - available/unavailable item list
  - borrow form and borrow submission
  - current loans
  - return form and return submission
  - user loan history
  - loan detail with documentation photos
  - admin/manager all-loans page
- Gudang and aset material modules ported:
  - gudang list/add/edit/delete
  - aset material list with gudang/type/search filters
  - add/edit/delete aset material
  - asset photos
  - add stock and stock history
  - asset detail timeline
  - low-stock notifications
  - by-gudang and stock-check APIs
- Pengambilan aset workflow ported:
  - mitra list/search/filter and request form
  - request submission with item rows and admin notifications
  - admin list/search/filter/pagination
  - approve/reject flow
  - pickup photo upload and petugas signature
  - final admin confirmation with stock decrement and stock history
  - detail/timeline views for mitra and admin
- Profile and activity log modules ported:
  - profile detail update with profile photo upload
  - password change
  - theme settings persistence
  - admin activity log search/filter/pagination
- Report routes ported as native CI4 downloads:
  - inventory
  - loans and all-loans
  - aset material
  - pengambilan
  - activity logs
- `/admin/report/pdf/{type}` now renders the existing DOCX templates through PHPWord/Dompdf and returns PDF files for the supported report types.
- `/admin/report/excel/{type}` and template export links currently return CSV downloads.
- PHP dependencies added for template PDF rendering:
  - `phpoffice/phpword`
  - `dompdf/dompdf`
- Laragon PHP's `zip` extension must be enabled because DOCX files are ZIP archives.
- If LibreOffice is installed and `LIBREOFFICE_PATH` is set, DOCX-to-PDF conversion will use LibreOffice; otherwise it falls back to Dompdf.
- Scheduler/email utilities ported:
  - `astala:check-loans` Spark command for upcoming-deadline reminders and overdue loan updates
  - SMTP email helper for reminder, overdue, and approval messages

## Run Locally

Use the Laragon PHP binary:

```powershell
& 'C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe' spark serve --host 127.0.0.1 --port 8080
```

Then open:

```text
http://127.0.0.1:8080/auth/login
```

## Verification

```powershell
& 'C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe' spark routes
Get-ChildItem app -Recurse -Filter *.php | ForEach-Object { & 'C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe' -l $_.FullName }
& 'C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe' spark astala:check-loans
```

## Database Migration

Review `.env` before running migrations. It currently mirrors the original Node database config, so running this command will target that configured database.

```powershell
& 'C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe' spark db:create astala
& 'C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe' spark migrate
& 'C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe' spark db:seed SuperAdminSeeder
```

For a safer first run, point `.env` to a local empty database before running `spark migrate`.

## Next Porting Order

1. Add LibreOffice and set `LIBREOFFICE_PATH` if Dompdf's DOCX rendering is not close enough to the old output.
2. Add deployment scheduling for `spark astala:check-loans` every 15 minutes.
