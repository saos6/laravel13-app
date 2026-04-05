# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Development
```bash
composer dev          # Start all services concurrently (Laravel server + queue + Vite HMR)
```

### Build & Frontend
```bash
npm run build         # Production build + triggers Wayfinder TypeScript generation
npm run dev           # Vite dev server only
npm run lint          # ESLint with auto-fix
npm run lint:check    # ESLint check only
npm run format        # Prettier write
npm run types:check   # vue-tsc type check
```

### Backend
```bash
php artisan migrate
php artisan db:seed --class=XxxSeeder
composer lint                          # Laravel Pint (PSR-12)
php artisan test                       # Pest full suite
php artisan test --filter=Dashboard    # Run single test by name
php artisan test tests/Feature/DashboardTest.php  # Run single file
```

### PDF fonts (run once per environment)
```bash
php artisan pdf:setup-fonts   # Downloads IPAex Gothic + registers with DomPDF
```
Font file lands in `storage/fonts/` (gitignored). Required for Japanese text in PDFs.

### After adding a new controller
Always run `npm run build` after creating or modifying controllers — **Laravel Wayfinder** auto-generates TypeScript action files under `resources/js/actions/` (e.g., `QuoteController.ts`). These are imported in Vue pages for type-safe URL generation.

## Architecture

### Stack
- **Laravel 13** + **Inertia.js v3** + **Vue 3** (TypeScript) SPA
- **SQLite** (local dev, file at `database/database.sqlite`)
- **Tailwind CSS v4** + **Reka UI** (headless component primitives)
- **Maatwebsite/Excel** for `.xlsx` export
- **barryvdh/laravel-dompdf** for PDF generation (Japanese font subsetting enabled)
- **Laravel Fortify** for authentication (with optional 2FA)
- **Pest** for testing

### Request lifecycle
Browser → Laravel route → Controller → `Inertia::render('Pages/Foo', [...])` → Vue page component.  
All navigation is SPA via `<Link>` / `router.get()` from `@inertiajs/vue3`.

### Shared Inertia props (`HandleInertiaRequests.php`)
Every page receives: `auth.user`, `sidebarOpen`, `flash.success`, `flash.error`.  
Flash messages are displayed automatically in `AppSidebarLayout.vue`. Controllers set them with `->with('success', '...')` or `->with('error', '...')`.

### Soft delete pattern
All models use an `is_deleted` boolean column instead of Laravel's `SoftDeletes` trait.  
Every model has `scopeActive(Builder $query)` — always call `.active()` when querying.  
`is_deleted` is **not** in `$fillable`; set it directly: `$model->is_deleted = true; $model->save()`.

### Delete guards
Before soft-deleting a parent record, check for active child records:
- **Customer delete**: blocked if active quotes exist (`$customer->quotes()->active()->exists()`) → redirect back with `error`
- **Employee delete**: allowed but warns how many active quotes will lose their `employee_id` (set to `null` via `nullOnDelete()` FK)
- **Quote items**: `cascadeOnDelete()` on the FK — no `is_deleted` needed on `quote_items`

### Model conventions
- `scopeActive()` — filters `is_deleted = false`
- `scopeFiltered(Builder $query, string $search, ...)` — search/filter logic
- Enum constants defined on the model (e.g., `Quote::STATUSES`, `Product::TAX_RATES`) and passed to Vue via controller props
- Validation in dedicated `app/Http/Requests/XxxRequest.php`; unique rules use `Rule::unique()->ignore($model?->id)->where('is_deleted', false)`
- `generateQuoteNumber()` uses `withoutGlobalScopes()` (not `withTrashed()`) to include deleted records when calculating the next sequence number

### Vue page conventions
- Pages live in `resources/js/pages/{Resource}/` (Index, Create, Edit, Show)
- Shared form logic extracted to `resources/js/components/{Resource}Form.vue`
- Column visibility persisted to `localStorage` with key `{resource}.columns` (see Products/Index.vue for the pattern)
- Sortable column headers use a unified `columns` ref loop; non-sortable columns listed in a `nonSortable` array
- `__none__` sentinel value used for nullable Select inputs to distinguish "no selection" from empty string

### Replicate pattern
All resources support a "複製" (duplicate) action:
- Masters (所属/社員/得意先/商品): replicate button on the **Edit** page
- 見積: replicate button on the **Show** page (next to 編集)
- Controller `replicate()` renders the Create page with a `prefill` prop (unique fields like `code`, `quote_number` are excluded/reset)
- Create page accepts optional `prefill` prop and shows an amber "複製元データ適用済み" badge when active

### Route pattern
```php
// Named sub-routes must come BEFORE the resource route to avoid conflict
Route::get('quotes/export',              [QuoteController::class, 'exportMethod'])->name('quotes.export');
Route::get('quotes/{quote}/pdf',         [QuoteController::class, 'pdf'])->name('quotes.pdf');
Route::get('quotes/{quote}/replicate',   [QuoteController::class, 'replicate'])->name('quotes.replicate');
Route::resource('quotes', QuoteController::class);
```

### Wayfinder usage in Vue
```typescript
import QuoteController from '@/actions/App/Http/Controllers/QuoteController';
QuoteController.index.url()       // /quotes
QuoteController.show.url(id)      // /quotes/1
QuoteController.store.url()       // POST /quotes
QuoteController.pdf.url(id)       // /quotes/1/pdf
```

### PDF generation
- Blade template: `resources/views/pdf/quote.blade.php` (table-based layout — DomPDF does not support flexbox/grid)
- Font: `font-family: 'IPAexGothic'` — resolved from `storage/fonts/installed-fonts.json`
- Config: `config/dompdf.php` with `enable_font_subsetting: true` (required for correct CJK character mapping)
- PDF download uses `<a>` tag (not `<Link>`) with `target="_blank"` in Vue

### UI components
Components in `resources/js/components/ui/` are Reka UI wrappers (Button, Input, Select, Badge, etc.).  
The Textarea component at `resources/js/components/ui/textarea/` was manually created (not shipped by default).

### Navigation structure
`AppSidebar.vue` defines `mainNavItems`. Items with `children` render as collapsible submenus (auto-expand when a child URL is active). Currently:
- Dashboard (top-level)
- 見積 (top-level)
- 設定 (collapsible) → 所属/社員/得意先/商品マスタ

### Excel export pattern
Each resource has an `app/Exports/XxxExport.php` implementing `FromQuery + WithHeadings + WithMapping + WithStyles`.  
Export routes accept the same filter params as the index route.
