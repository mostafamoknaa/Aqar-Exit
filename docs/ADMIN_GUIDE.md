# Administrator Guide — Aqar Exit

## Roles
- **Admin**: everything, including Settings, Reports export, Audit Logs, user roles.
- **Case Manager (Manager)**: dashboard, reviews, and deals — but stage changes only on cases assigned to them; cannot change Settings or view Audit Logs.
- **User**: mobile app only (seller/buyer).

## Daily workflow
1. **Dashboard** → Verification Queue: open each pending listing.
2. **Listing review**: check numbers against uploaded documents (Documents panel → Download/Verify/Reject — every decision is written to document history + audit log). Record *verified financial figures* in Financial Verification — a mismatch flags "discrepancy" and does not change the seller's reported numbers. Then Publish / Request changes / Reject with notes the seller sees in the app.
3. **Interests**: qualify buyers (cash available vs cash required), then on the listing page **Create Deal** — the listing becomes Reserved.
4. **Deals**: move the case only forward through the pipeline (invalid jumps are rejected by the server), assign the case manager, set signing date, add a reason — every move appears in Case History and notifies buyer + seller. *Completed in the platform means our workflow is done; the legal transfer itself happens at the developer.*
5. **Evaluations**: follow up calculator leads (cancel-loss vs Aqar Exit comparison is shown for the call).

## Settings (Admin → Settings)
Project name (EN/AR), logo, support contacts, buyer commission %, calculator default %, default language, registration/silent-listing toggles. Commission changes affect new deals only.

## Reports & Audit
Reports shows counts by status/stage and financial-verification status counts (never presents estimates as verified totals). CSV export is admin-only. Audit Logs record who changed listings, documents, deals, users, and settings.

## Safety rules
Never ask users for documents outside the platform, never share private document links (downloads are authenticated and owner/staff-only), and never mark financials "verified" without seeing evidence.
