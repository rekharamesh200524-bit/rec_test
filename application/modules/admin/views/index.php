<!DOCTYPE html>
<?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template'); ?>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>INET - HRMS | Login - 2026</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/fontawesome-free/css/all.min.css">

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --brand:      #1a56e8;
      --brand-dk:   #1240c0;
      --brand-glow: rgba(26,86,232,0.22);
      --surface:    #ffffff;
      --bg:         #f2f5fd;
      --text-h:     #0d1b3e;
      --text-b:     #374060;
      --text-m:     #7a85a0;
      --border:     #dce3f5;
      --success:    #1a6e45;
      --success-bg: #e4f5ed;
      --danger:     #c0392b;
      --danger-bg:  #fef0ef;
    }

    html, body {
      height: 100%;
      font-family: 'DM Sans', sans-serif;
    }

    .login-shell {
      display: flex;
      flex-direction: row;
      width: 100%;
      min-height: 100vh;
    }

    
    .panel-left {
      width: 55%;
      min-height: 100vh;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 3rem;
      overflow: hidden;
    }

    .panel-left .bg-img {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center center;
      z-index: 0;
      display: block;
    }

    .panel-left::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(
        170deg,
        rgba(8, 14, 50, 0.30) 0%,
        rgba(8, 14, 50, 0.78) 100%
      );
      z-index: 0;
    }

    .pl-brand {
      position: absolute;
      top: 2.5rem;
      left: 3rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      z-index: 2;
    }

    .pl-brand .mark {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      background: rgba(255,255,255,0.16);
      border: 1.5px solid rgba(255,255,255,0.30);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Sora', sans-serif;
      font-weight: 700;
      font-size: 1.2rem;
      color: #fff;
      flex-shrink: 0;
    }

    .pl-brand .brand-name  { font-family: 'Sora', sans-serif; font-weight: 600; font-size: 1.05rem; color: #fff; line-height: 1.2; }
    .pl-brand .brand-tag   { font-size: 0.68rem; color: rgba(255,255,255,0.55); text-transform: uppercase; letter-spacing: 0.5px; }

    .pl-copy {
      position: relative;
      z-index: 2;
    }

    .pl-copy h2 {
      font-family: 'Sora', sans-serif;
      font-weight: 600;
      font-size: clamp(1.5rem, 2.2vw, 2rem);
      color: #fff;
      line-height: 1.28;
      margin-bottom: 0.65rem;
      letter-spacing: -0.4px;
    }

    .pl-copy p {
      font-size: 0.88rem;
      color: rgba(255,255,255,0.68);
      line-height: 1.65;
      max-width: 380px;
    }

    .pl-dots {
      display: flex;
      gap: 6px;
      margin-top: 1.5rem;
    }

    .pl-dots span {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: rgba(255,255,255,0.35);
    }

    .pl-dots span.active {
      width: 22px;
      border-radius: 4px;
      background: #fff;
    }

       .panel-right {
      width: 45%;
      min-height: 100vh;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3rem 4rem;
      box-shadow: -6px 0 40px rgba(13,27,62,0.08);
    }

    .form-shell {
      width: 100%;
      max-width: 360px;
      animation: fadeUp 0.45s cubic-bezier(0.22,1,0.36,1) both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .mobile-brand {
      display: none;
      align-items: center;
      gap: 0.65rem;
      margin-bottom: 2rem;
    }

    .mobile-brand .mark {
      width: 40px;
      height: 40px;
      border-radius: 9px;
      background: var(--brand);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Sora', sans-serif;
      font-weight: 700;
      font-size: 1.1rem;
      color: #fff;
      flex-shrink: 0;
    }

    .mobile-brand .brand-name { font-family: 'Sora', sans-serif; font-weight: 600; font-size: 1rem; color: var(--text-h); line-height: 1.2; }
    .mobile-brand .brand-tag  { font-size: 0.65rem; color: var(--text-m); text-transform: uppercase; letter-spacing: 0.5px; }

    .form-shell h1 {
      font-family: 'Sora', sans-serif;
      font-weight: 600;
      font-size: 1.6rem;
      color: var(--text-h);
      letter-spacing: -0.4px;
      margin-bottom: 0.3rem;
    }

    .form-shell .sub {
      font-size: 0.85rem;
      color: var(--text-m);
      margin-bottom: 2rem;
    }

    .alert {
      border-radius: 9px;
      padding: 0.75rem 1rem;
      font-size: 0.83rem;
      margin-bottom: 1.25rem;
      display: flex;
      align-items: flex-start;
      gap: 0.55rem;
      line-height: 1.5;
    }

    .alert-success { background: var(--success-bg); color: var(--success); border: 1px solid #a8dfca; }
    .alert-danger  { background: var(--danger-bg);  color: var(--danger);  border: 1px solid #f5c0bc; }
    .alert i { margin-top: 1px; flex-shrink: 0; }

    .field { margin-bottom: 1.15rem; }

    .field-label {
      display: block;
      font-size: 0.8rem;
      font-weight: 500;
      color: var(--text-b);
      margin-bottom: 0.45rem;
    }

    .iw { position: relative; }

    .iw .ico {
      position: absolute;
      left: 0.9rem;
      top: 50%;
      transform: translateY(-50%);
      font-size: 0.85rem;
      color: var(--text-m);
      pointer-events: none;
      transition: color 0.18s;
    }

    .iw input {
      width: 100%;
      height: 46px;
      padding: 0 2.8rem 0 2.6rem;
      border: 1.5px solid var(--border);
      border-radius: 9px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem;
      color: var(--text-h);
      background: var(--bg);
      outline: none;
      transition: border-color 0.18s, background 0.18s, box-shadow 0.18s;
      -webkit-appearance: none;
    }

    .iw input::placeholder { color: #b0bad4; }

    .iw input:focus {
      border-color: var(--brand);
      background: #fff;
      box-shadow: 0 0 0 3.5px var(--brand-glow);
    }

    .iw:focus-within .ico { color: var(--brand); }

    .iw .tpw {
      position: absolute;
      right: 0.8rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
      font-size: 0.88rem;
      color: var(--text-m);
      transition: color 0.18s;
      line-height: 1;
    }

    .iw .tpw:hover { color: var(--brand); }

    .extras {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 0.5rem 0 1.6rem;
    }

    .rem {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.83rem;
      color: var(--text-b);
      cursor: pointer;
      user-select: none;
    }

    .rem input[type=checkbox] {
      width: 15px;
      height: 15px;
      accent-color: var(--brand);
      cursor: pointer;
      flex-shrink: 0;
    }

    .fgt {
      font-size: 0.83rem;
      color: var(--brand);
      font-weight: 500;
      text-decoration: none;
    }

    .fgt:hover { text-decoration: underline; }

    .btn-submit {
      width: 100%;
      height: 48px;
      background: var(--brand);
      color: #fff;
      border: none;
      border-radius: 9px;
      font-family: 'Sora', sans-serif;
      font-size: 0.95rem;
      font-weight: 500;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      box-shadow: 0 3px 16px var(--brand-glow);
      transition: background 0.18s, box-shadow 0.18s, transform 0.14s;
      letter-spacing: 0.1px;
    }

    .btn-submit:hover  { background: var(--brand-dk); box-shadow: 0 5px 22px rgba(26,86,232,0.35); }
    .btn-submit:active { transform: scale(0.985); }

    .panel-footer {
      margin-top: 2.5rem;
      font-size: 0.73rem;
      color: var(--text-m);
      text-align: center;
    }

    .panel-footer a { color: var(--brand); font-weight: 500; text-decoration: none; }
    .panel-footer a:hover { text-decoration: underline; }

      @media (max-width: 900px) {
      .panel-right { width: 50%; padding: 2.5rem 2.5rem; }
      .panel-left  { width: 50%; }
    }

    @media (max-width: 768px) {
      .login-shell { flex-direction: column; }

      .panel-left  { display: none; }

      .panel-right {
        width: 100%;
        min-height: 100vh;
        padding: 3rem 1.75rem;
        box-shadow: none;
        justify-content: center;
      }

      .form-shell { max-width: 440px; }

      .mobile-brand { display: flex; }
    }

    @media (max-width: 400px) {
      .panel-right { padding: 2.5rem 1.25rem; }
    }
  </style>
</head>
<body>

<div class="login-shell">

  <div class="panel-left">
    <img class="bg-img" src="<?=$theme_path?>/assets/dist/img/copy.png" alt="">
    <div class="pl-brand">
      <div class="mark">I</div>
      <div>
        <div class="brand-name">I-ATS</div>
        <div class="brand-tag">Applicant Tracking System</div>
      </div>
    </div>

    <div class="pl-copy">
     
      <div class="pl-copy">
  <h2>Streamline your hiring process.</h2>

  <p>
    Manage job applications, track candidates, schedule interviews,
    monitor hiring stages, and simplify recruitment workflows —
    all in one ATS platform.
  </p>

</div>
    </div>
  </div>

 
  <div class="panel-right">
    <div class="form-shell">

   
      <div class="mobile-brand">
        <div class="mark">I</div>
        <div>
          <div class="brand-name">I-HRMS</div>
          <div class="brand-tag">Human Resource Management</div>
        </div>
      </div>

      <h1>Welcome back</h1>
      <p class="sub">Sign in to continue to your workspace</p>

     
      <form action="<?php echo site_url('admin/CheckLoginData'); ?>" method="post" multipart="">

        <div class="field">
          <label class="field-label" for="EmailInput">Email address</label>
          <div class="iw">
            <i class="ico fas fa-envelope"></i>
            <input type="email" id="EmailInput" name="EmailInput" placeholder="you@company.com" required>
          </div>
        </div>

        <div class="field">
          <label class="field-label" for="PassInput">Password</label>
          <div class="iw">
            <i class="ico fas fa-lock"></i>
            <input type="password" id="PassInput" name="PassInput" placeholder="Enter your password" required>
            <button type="button" class="tpw" aria-label="Show password" onclick="togglePw(this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="extras">
          <label class="rem">
            <input type="checkbox" id="remember">
            Remember me
          </label>
          <a href="<?php echo $this->config->item('base_url'); ?>admin/ForgotPassword">Forgot Password</a>
         </div>

        <button type="submit" class="btn-submit">
          <i class="fas fa-sign-in-alt"></i>
          Sign In
        </button>

      </form>

      <div class="panel-footer">
        &copy; 2026 <a href="#">I-Net Secure Labs Pvt Ltd</a>. All rights reserved.
      </div>

    </div>
  </div>

</div>

<!-- jQuery -->
<script src="<?=$theme_path?>/assets/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?=$theme_path?>/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?=$theme_path?>/assets/dist/js/adminlte.min.js"></script>

<script>
  function togglePw(btn) {
    var input = btn.closest('.iw').querySelector('input');
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
      btn.setAttribute('aria-label', 'Hide password');
    } else {
      input.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
      btn.setAttribute('aria-label', 'Show password');
    }
  }
</script>
</body>
</html>