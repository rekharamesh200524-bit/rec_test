# Project Memory

## startup error
Reference for the application startup / 404 error issues and resolutions for the REC project:

1. **Hardcoded Domain**:
   - **Problem**: `application/config/config.php` hardcoded `$domain = 'rec.inetcsc.com';`.
   - **Fix**: Updated to `$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';`.

2. **Hardcoded HTTPS Protocol**:
   - **Problem**: `application/config/config.php` forced `"https"` in `base_url`, breaking local XAMPP `http://localhost` calls.
   - **Fix**: Added dynamic protocol detection (`http://` vs `https://`).

3. **Subdirectory Rewriting (`.htaccess`)**:
   - **Problem**: Project runs under `/rec/` subfolder in XAMPP. Without `RewriteBase /rec/`, Apache rewrote requests to root `htdocs/index.php` causing 404s.
   - **Fix**: Added `RewriteBase /rec/` to `.htaccess`.

4. **Controller Constructor Session Check**:
   - **Problem**: `application/modules/admin/controllers/Admin.php` accessed `$check_session['EmpRoleId']` when `$check_session` was `null` on login.
   - **Fix**: Added `!empty($check_session)` validation before fetching role menus.
