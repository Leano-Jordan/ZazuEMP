# Zazu EMP — Working File Map

This file is the quick orientation map for the owner. It explains which files are normally edited during feature work and which folders are framework/vendor infrastructure.

## The files you will inspect most

| Path | Plain-English job |
|---|---|
| `routes/web.php` | The traffic map. It decides which URL calls which controller action. |
| `app/Http/Controllers/WorkController.php` | The Work workflow brain. It receives form requests, validates them, reads/writes models, then chooses a page. |
| `app/Http/Controllers/CustomerController.php` | The Customer workflow brain. |
| `app/Models/Event.php` | The PHP representation of an Event/Work database record and its relationships. |
| `app/Models/Customer.php` | The PHP representation of a Customer and its contacts/work. |
| `app/Models/CustomerContact.php` | The PHP representation of a customer contact. |
| `resources/views/components/app-layout.blade.php` | The shared application shell: navigation, header, validation messages and page frame. |
| `resources/views/work/index.blade.php` | Work list/overview shown in the browser. |
| `resources/views/work/create.blade.php` | New Work form. |
| `resources/views/work/show.blade.php` | The Work operational workspace. |
| `resources/views/work/edit.blade.php` | Edit Work form. |
| `resources/views/customers/index.blade.php` | Customer directory. |
| `resources/views/customers/create.blade.php` | New Customer form. |
| `resources/css/app.css` | Tailwind CSS entry point and project styling configuration. |
| `resources/js/app.js` | Global browser JavaScript entry point. Feature-local scripts currently live beside the relevant Blade form when that keeps the change small. |

## Database: where it actually lives

There are three different things to keep separate:

1. **Migration files** in `database/migrations/`
   - These are versioned instructions for creating or changing database tables.
   - They are source-controlled.
   - They do not hold the live records themselves.

2. **Database connection configuration** in `config/database.php`
   - Laravel reads the `DB_*` values from `.env`.
   - The repository's example configuration defaults to SQLite.
   - The real local connection is determined by the owner's local `.env`, which must never be committed.

3. **The live local database**
   - The actual records are stored in the database configured by the local `.env`.
   - Laravel's `migrations` table records which migrations have already run.
   - A migration changes the schema when it is executed. A model/controller changes records at runtime.

## The browser-to-database path

For a normal "Save Customer" action:

`Browser form → POST route → CustomerController@store → validation → Customer model → database → redirect → Blade view`

For "Create Work":

`Browser form → POST /work → WorkController@store → validation → Customer/Event models → events table → Work workspace`

The Blade files do **not** directly write SQL. The controller/model/database layer does that.

## Files you normally do NOT edit

- `vendor/` — third-party PHP packages installed by Composer.
- `node_modules/` — third-party JavaScript packages installed by npm.
- `storage/framework/` — generated Laravel runtime/cache/view files.
- `bootstrap/cache/` — generated framework cache.

If Git starts showing thousands of changes inside `vendor/`, that is not a normal feature change and should be diagnosed separately rather than committed as application work.

## Source-of-truth rule

For Zazu development:

**Current repository/runtime evidence > documentation > old chat plans.**

A working-file map is an orientation aid, not permission to assume a file is safe to change. Inspect the current implementation before modifying it.
