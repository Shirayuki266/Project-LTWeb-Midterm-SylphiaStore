# Sylphia Shop - Full Ecommerce Upgrade TODO

Project: PHP/MySQL Website (XAMPP). Each PHP file ≤300 lines. Demo DB via phpMyAdmin.

## Current Status

- [x] Analyzed files/DB (partial dynamic, duplicates, missing logic)
- [x] Plan approved (with demo DB + short files)

## Step 1: Database (Demo/Fake data)

- [ ] Enhance sql/schema.sql: Add tables (admins, diachi, phieu_nhap\*, tonkho, gia_ban). Triggers. ~50 fake products/users/orders.
- [ ] Update/create setup_db.php: Create DB + import all.
- [ ] Test: phpMyAdmin → Sylphia_Shop → verify data.

## Step 2: Core APIs/Includes (short modules)

- [ ] api/auth.php: Login/register/session (user/admin).
- [ ] api/cart.php: Session/DB cart.
- [ ] api/products.php: CRUD/search/paginate.
- [ ] api/orders.php: Checkout/history.
- [ ] api/admin/_.php: Mgmt (imports, prices compute giaban=giavonal_(1+llnn/100), reports).
- [ ] includes/functions.php: Utils (validate, paginate, format).
- [ ] js/app.js: Client validate/AJAX.

## Step 3: User Frontend (consolidate, dynamic, ≤300L)

- [ ] user/index.php (trangchu): Hero + dynamic featured/cats.
- [ ] user/products.php: Full search/paginate/sort/JS validate.
- [ ] user/product-detail.php: Detail + addCart.
- [ ] user/login.php + register.php.
- [ ] user/profile.php + orders.php.
- [ ] user/cart.php + checkout.php (payments: cash/transfer/online-display).

## Step 4: Admin (modular ≤300L)

- [ ] admin/login.php.
- [ ] admin/dashboard.php: Stats/alerts low stock.
- [ ] admin/crud-\*.php: Users/cats/products/imports/prices/orders/reports (single pages or tabs).

## Step 5: Assets/Polish

- [ ] css/: Bootstrap5 + custom (admin/user).
- [ ] Update paths/sessions everywhere.
- [ ] JS: Form validate/search.

## Step 6: Cleanup

- [ ] Remove duplicates ( \*-dangnhap/chuadangnhap ).
- [ ] Test all flows.
- [ ] README.md: XAMPP guide (`http://localhost/web b`).

**Progress: 0/N | Run: php setup_db.php then browser test.**
