# Sylphia Shop - Đồ Án Lập Trình Web PHP/MySQL

**Full e-commerce: User (search/cart/buy), Admin (manage), DB giá bình quân.**

## Chạy XAMPP:

```
1. XAMPP Control → Apache + MySQL ON
2. phpMyAdmin localhost/phpmyadmin:
   - DB `Sylphia Shop`
   - Import sql/complete_schema.sql
   - Import sql/full_data_fixed.sql (203 SP, 10 users, 20 orders)
   - Import admin/giaban_trig.sql (giá vốn)
3. Website: http://localhost/web b/user/index.php
```

## Features:

- **User:** Search/filter/paginate, login (user1/password), cart AJAX, profile orders.
- **Admin:** Login, dashboard, import/price management.
- **DB:** sanpham/loaisp/donhang, trigger giá = vốn\*(1+lợi nhuận).

## Test:

- Products: Filter iPhone → paginate.
- Cart: Add → update qty.
- Giá: `CALL sp_nhap_hang(1, 20e6, 10, 30);`

**Ready! No error.** 🚀
