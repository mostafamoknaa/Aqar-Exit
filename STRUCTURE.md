# Aqar Exit — Structure (Admin Dashboard + Mobile API)

Same product as [aqarexit.com](https://aqarexit.com): a seller who can't continue installments
recovers **100% of what he paid (cash, no "over")**, a buyer takes the **old contract price**
and continues installments. Platform verifies every number from documents, then walks both
parties through the official assignment (تنازل) with the developer.
Seller commission **0%** · Buyer commission **1.25% only on completion** (`config/aqar.php`).

```
                                                          ┌────────────────────┐
  Mobile App (Flutter / React Native) ── REST /api/v1 ───▶│  Laravel (this repo)│── Blade + Tailwind ──▶ Admin Dashboard /admin
                                                          └────────────────────┘
```

## 1. Domain (database + models)

| Table / Model | Purpose |
|---|---|
| `users` (`role: admin\|manager\|user`, `phone` login) | Admin, Case Manager (مسؤول حالة), mobile users (seller and/or buyer) |
| `developers` / `projects` | مطورين / مشاريع — projects carry city + district, developers carry assignment notes |
| `listings` | The unit/opportunity. `paid_amount` = cash buyer pays seller, `total_price` = old contract price, `remaining_amount` auto-computed. `unit_number`, `exact_address`, documents are **private** (never in public API). `is_silent` = silent exit. Workflow: `draft → pending_review → published → reserved → completed` (+ `changes_requested/rejected/hidden`) |
| `listing_images` / `listing_documents` | Public images / private verified files (`contract`, `payment_receipt`, … each `pending\|verified\|rejected`) |
| `favorites` | Buyer bookmarks |
| `interests` | Buyer says "I'm interested" + cash available → admin qualifies → converts to a deal |
| `buyer_preferences` | "Send me deals that fit my cash/installment" — matching input for admins |
| `deals` | Assignment case: buyer, seller, case manager, `stage` pipeline `new → contacted → reserved → documents → developer_approval → signing → completed`, commission snapshot (1.25%) |
| `evaluations` | Free exit-calculator leads (قيّم عقدك) — result computed by `App\Services\ExitCalculatorService` |

Enums: `app/Enums/{UserRole,ListingStatus,DealStage,UnitType}.php` · Business rules: `config/aqar.php`

## 2. Mobile API — `/api/v1` (Sanctum bearer token)

Auth: `Authorization: Bearer <token>` from register/login. All responses: `{ "data": … }` (+ pagination meta).

| Area | Endpoints |
|---|---|
| Auth | `POST auth/register` (phone+password) · `POST auth/login` (`login` = phone or email) · `GET auth/me` · `POST auth/logout` |
| Home/Catalog | `GET home` (stats, featured, developers, commission) · `GET listings` (filters: `city, district, unit_type, project_id, min_paid, max_paid, bedrooms, search`, `sort=cash_low\|saving_high`) · `GET listings/{id}` · `GET developers` · `GET projects` · `GET filters` |
| Exit calculator | `POST evaluations/calculate` (guest, instant) · `POST evaluations` (save lead) · `GET evaluations` |
| Seller | `GET/POST/PUT/DELETE my-listings` · `POST my-listings/{id}/documents` (private upload) · `POST my-listings/{id}/submit` (→ pending_review) |
| Buyer | `POST listings/{id}/favorite` · `GET favorites` · `POST listings/{id}/interest` · `GET/DELETE interests` · `GET/PUT profile/buyer-preference` |
| Deal tracker | `GET deals` · `GET deals/{id}` (includes `progress_percent` + pipeline for the tracker UI) |
| Profile | `PUT profile` · `PUT profile/password` · `DELETE profile` |

Code: `app/Http/Controllers/Api/V1/*`, `app/Http/Resources/V1/*` (public resources hide private fields),
`app/Http/Requests/Api/V1/*`, routes in `routes/api.php`.

## 3. Admin Dashboard — `/admin` (Tailwind CSS v4, Blade)

Session login, staff only (`admin` middleware = admin/manager roles).

| Page | What staff do there |
|---|---|
| Dashboard | KPIs, verification queue, deals-by-stage, commission earned |
| Listings | Filter/search, **review page**: verify documents (download/review private files), publish / request changes / reject, feature, convert a qualified interest → Deal |
| Deals | **Kanban pipeline board** + table; move stage, assign case manager, signing date. Completing a deal completes the listing; cancelling re-publishes it |
| Evaluations | Calculator leads with "cancel vs Aqar Exit" loss comparison, follow-up status |
| Interests | Qualify buyer interests (cash vs required) |
| Users | Roles, enable/disable (revokes app tokens), buyer preferences, buying/selling history |
| Developers / Projects | CRUD catalog used by listings + app filters |

Code: `app/Http/Controllers/Admin/*`, `resources/views/admin/*`, layout `resources/views/layouts/admin.blade.php`,
theme/components in `resources/css/app.css` (Tailwind v4 CSS-first `@theme`, brand colors).

## 4. Run it

```bash
composer install && npm install
php artisan migrate:fresh --seed
npm run build            # or: npm run dev
php artisan serve
```

- Admin: http://localhost:8000/admin/login → `admin@aqarexit.test` / `password` (manager: `manager@aqarexit.test`)
- Mobile demo users: `seller@aqarexit.test` / `buyer@aqarexit.test` / `password` (login via API with phone or email)
- Tests: `php artisan test` (API flow + admin verification flow)

## 5. Next steps (not in this skeleton)

OTP phone verification, push notifications (FCM), payment of commission, chat between buyer/seller/case-manager,
Arabic localization (`name_ar` fields already exist), matching engine (auto-match `buyer_preferences` → new listings),
public website (aqarexit.com pages) can reuse the same API/catalog.
