<?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template'); ?>

<div class="login-shell" id="otpContainer" data-remaining="<?php echo sprintf('%d', isset($remaining_seconds) ? (int)$remaining_seconds : 60); ?>" data-resend-url="<?php echo site_url('admin/ResendOtp'); ?>">

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
      <h2>Streamline your hiring process.</h2>
      <p>
        Manage job applications, track candidates, schedule interviews,
        monitor hiring stages, and simplify recruitment workflows —
        all in one ATS platform.
      </p>
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

      <h1>OTP Verification</h1>
      <p class="sub">An OTP has been sent to your registered email address.</p>

      <?php if (!empty($user_email)): ?>
      <div class="email-badge">
        <i class="fas fa-envelope"></i>
        <span><?php echo htmlspecialchars($user_email); ?></span>
      </div>
      <?php endif; ?>

      <!-- Dynamic alert box -->
      <div id="dynamicAlert" style="display: none;"></div>

      <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger" id="serverErrorAlert">
          <i class="fas fa-circle-exclamation"></i>
          <span><?= $this->session->flashdata('error'); ?></span>
        </div>
      <?php endif; ?>

      <?php if ($this->session->flashdata('success') || $this->session->flashdata('true')): ?>
        <div class="alert alert-success" id="serverSuccessAlert">
          <i class="fas fa-circle-check"></i>
          <span><?= $this->session->flashdata('success') ?: $this->session->flashdata('true'); ?></span>
        </div>
      <?php endif; ?>

      <form action="<?php echo site_url('admin/VerifyOtpSubmit'); ?>" method="post" id="otpForm">

        <div class="field">
          <label class="field-label" for="otpInput">Enter 6-Digit OTP</label>
          <div class="otp-input-wrap">
            <input
              type="text"
              id="otpInput"
              name="otp"
              placeholder="••••••"
              maxlength="6"
              inputmode="numeric"
              pattern="[0-9]{6}"
              autocomplete="one-time-code"
              required
              autofocus
            >
          </div>
        </div>

        <div class="timer-container" id="timerContainer">
          <span class="timer-text" id="timerLabel">
            <i class="fas fa-clock" id="timerIcon"></i>
            <span id="timerDescription">OTP expires in <?php echo sprintf('%02d', isset($remaining_seconds) ? (int)$remaining_seconds : 60); ?> seconds</span>
          </span>
          <span class="timer-badge" id="timerBadge">
            <span id="countdownDisplay"><?php echo sprintf('%02d', isset($remaining_seconds) ? (int)$remaining_seconds : 60); ?></span>s
          </span>
        </div>

        <button type="submit" class="btn-submit" id="btnVerify">
          <i class="fas fa-shield-halved"></i> Verify OTP
        </button>

      </form>

      <div class="actions-row">
        <button type="button" class="btn-resend" id="btnResend" data-resend-url="<?php echo site_url('admin/ResendOtp'); ?>">
          <i class="fas fa-rotate-right"></i> Resend OTP
        </button>

        <a href="<?php echo site_url('admin/index'); ?>" class="link-back">
          <i class="fas fa-arrow-left"></i> Back to Login
        </a>
      </div>

      <div class="panel-footer">
        &copy; <?php echo date('Y'); ?> <a href="#">I-Net Secure Labs Pvt Ltd</a>. All rights reserved.
      </div>

    </div>
  </div>

</div>
