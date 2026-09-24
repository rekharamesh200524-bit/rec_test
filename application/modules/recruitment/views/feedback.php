<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= !empty($page_title) ? htmlspecialchars($page_title) : 'Candidate Portal | Application Feedback'; ?></title>

  <?php $theme_path = $this->config->item('theme_locations') . $this->config->item('active_template'); ?>
  
  <link href="<?= $theme_path ?>/images/favicon.png" rel="shortcut icon">
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,600,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="<?= $theme_path ?>/assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="<?= $theme_path ?>/assets/plugins/toastr/toastr.min.css">
  <!-- AdminLTE theme style -->
  <link rel="stylesheet" href="<?= $theme_path ?>/assets/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?= $theme_path ?>/css/custom-style.css">
</head>
<body class="layout-top-nav">
<div class="wrapper">

  <!-- Header / Navigation Bar -->
  <nav class="navbar navbar-expand-md navbar-dark public-navbar py-2 px-3">
    <div class="container">
      <a href="<?= base_url('recruitment/user/apply'); ?>" class="navbar-brand d-flex align-items-center text-white">
        <i class="fas fa-briefcase text-info mr-2 fa-lg"></i>
        <span>INET <span class="text-info">HRMS</span> | Careers</span>
      </a>
      <div class="ml-auto d-flex align-items-center">
        <span class="badge badge-pill badge-light text-primary font-weight-bold px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
          <i class="fas fa-user-check mr-1"></i> Candidate Portal
        </span>
      </div>
    </div>
  </nav>

  <!-- Main Content Wrapper -->
  <div class="content-wrapper py-4">
    <div class="container">

      <!-- Hero Header -->
      <div class="portal-hero text-center position-relative overflow-hidden">
        <h1 class="mb-2"><i class="fas fa-paper-plane mr-2"></i> Application Status &amp; Feedback</h1>
        <p class="lead mb-0 text-white-50">Track your application and share your application experience.</p>
      </div>

      <!-- Main Container Row -->
      <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
          
          <?php if (!empty($is_new_submit)): ?>
          <!-- Success Notification Banner for New Application Submissions -->
          <div class="alert alert-success border-0 shadow-sm p-3 mb-4" style="border-radius: 0.5rem; background-color: #d4edda; color: #155724;">
            <div class="d-flex align-items-center">
              <i class="fas fa-check-circle fa-2x text-success mr-3"></i>
              <div>
                <h5 class="font-weight-bold mb-1">Application Submitted Successfully!</h5>
                <p class="mb-0" style="font-size: 0.92rem;">
                  Thank you for applying. Your application details have been recorded into our HR system.
                </p>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <!-- Application Summary Card -->
          <div class="card card-apply shadow-sm mb-4">
            <div class="card-header py-3 bg-white border-bottom">
              <h5 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-file-alt text-primary mr-2"></i> Application Status
              </h5>
            </div>
            <div class="card-body p-4">
              <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                  <span class="text-muted small d-block font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">APPLICATION ID</span>
                  <h4 class="font-weight-bold text-primary mb-0">APP-<?= htmlspecialchars($app['ApplicationId']); ?></h4>
                </div>
                <div class="col-md-6">
                  <span class="text-muted small d-block font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">POSITION</span>
                  <h5 class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($app['JobTitle']); ?></h5>
                  <?php if (!empty($app['Departmentname'])): ?>
                    <small class="text-muted"><i class="fas fa-building mr-1"></i><?= htmlspecialchars($app['Departmentname']); ?></small>
                  <?php endif; ?>
                </div>
              </div>

              <hr class="my-3">

              <div class="row">
                <div class="col-md-6 mb-2 mb-md-0">
                  <span class="text-muted small d-block">APPLICANT NAME:</span>
                  <strong class="text-dark"><?= htmlspecialchars($app['Fullname']); ?></strong>
                </div>
                <div class="col-md-6">
                  <span class="text-muted small d-block">STATUS:</span>
                  <span class="badge badge-info px-3 py-1 font-weight-bold" style="font-size: 0.88rem;">
                    <i class="fas fa-clock mr-1"></i> <?= htmlspecialchars($app['CurrentStatus'] ?? 'CV Uploaded'); ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Candidate Feedback Form Card -->
          <div class="card card-feedback shadow-sm mb-5" id="feedbackFormCard">
            <div class="card-header py-3 bg-white border-bottom">
              <h5 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-star text-warning mr-2"></i> Candidate Application Experience Feedback
              </h5>
            </div>
            <div class="card-body p-4 p-md-5">
              <p class="text-muted mb-4">
                How was your experience applying for this position? Please rate the aspects below to help us improve our recruitment process.
              </p>

              <?php 
                $attributes = array('id' => 'candidateFeedbackForm', 'class' => 'needs-validation', 'data-submit-url' => base_url('recruitment/user/submit_feedback'));
                echo form_open('recruitment/user/submit_feedback', $attributes); 
              ?>

                <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
                <?php if ($this->config->item('csrf_protection')): ?>
                  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <?php endif; ?>

                <!-- Rating 1: Overall Experience -->
                <div class="rating-row">
                  <label class="rating-label">
                    <i class="fas fa-award text-primary mr-2"></i> Overall Experience
                  </label>
                  <div class="star-rating">
                    <input type="radio" id="star_overall_5" name="overall_experience" value="5" checked>
                    <label for="star_overall_5" title="5 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_overall_4" name="overall_experience" value="4">
                    <label for="star_overall_4" title="4 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_overall_3" name="overall_experience" value="3">
                    <label for="star_overall_3" title="3 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_overall_2" name="overall_experience" value="2">
                    <label for="star_overall_2" title="2 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_overall_1" name="overall_experience" value="1">
                    <label for="star_overall_1" title="1 star"><i class="fas fa-star"></i></label>
                  </div>
                </div>

                <!-- Rating 2: Application Process -->
                <div class="rating-row">
                  <label class="rating-label">
                    <i class="fas fa-tasks text-info mr-2"></i> Application Process
                  </label>
                  <div class="star-rating">
                    <input type="radio" id="star_process_5" name="application_process" value="5" checked>
                    <label for="star_process_5" title="5 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_process_4" name="application_process" value="4">
                    <label for="star_process_4" title="4 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_process_3" name="application_process" value="3">
                    <label for="star_process_3" title="3 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_process_2" name="application_process" value="2">
                    <label for="star_process_2" title="2 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_process_1" name="application_process" value="1">
                    <label for="star_process_1" title="1 star"><i class="fas fa-star"></i></label>
                  </div>
                </div>

                <!-- Rating 3: Ease of Applying -->
                <div class="rating-row">
                  <label class="rating-label">
                    <i class="fas fa-hand-pointer text-success mr-2"></i> Ease of Applying
                  </label>
                  <div class="star-rating">
                    <input type="radio" id="star_ease_5" name="ease_of_applying" value="5" checked>
                    <label for="star_ease_5" title="5 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_ease_4" name="ease_of_applying" value="4">
                    <label for="star_ease_4" title="4 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_ease_3" name="ease_of_applying" value="3">
                    <label for="star_ease_3" title="3 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_ease_2" name="ease_of_applying" value="2">
                    <label for="star_ease_2" title="2 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_ease_1" name="ease_of_applying" value="1">
                    <label for="star_ease_1" title="1 star"><i class="fas fa-star"></i></label>
                  </div>
                </div>

                <!-- Rating 4: Communication -->
                <div class="rating-row">
                  <label class="rating-label">
                    <i class="fas fa-comments text-warning mr-2"></i> Communication &amp; Clarity
                  </label>
                  <div class="star-rating">
                    <input type="radio" id="star_comm_5" name="communication" value="5" checked>
                    <label for="star_comm_5" title="5 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_comm_4" name="communication" value="4">
                    <label for="star_comm_4" title="4 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_comm_3" name="communication" value="3">
                    <label for="star_comm_3" title="3 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_comm_2" name="communication" value="2">
                    <label for="star_comm_2" title="2 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star_comm_1" name="communication" value="1">
                    <label for="star_comm_1" title="1 star"><i class="fas fa-star"></i></label>
                  </div>
                </div>

                <!-- Additional Comments -->
                <div class="form-group mt-4">
                  <label for="comments" class="font-weight-bold text-dark">Additional Comments / Suggestions</label>
                  <textarea name="comments" id="comments" class="form-control" rows="4" placeholder="Share any thoughts, feedback, or suggestions to help us improve..."></textarea>
                </div>

                <!-- Submit Button -->
                <div class="pt-3 border-top d-flex justify-content-end align-items-center">
                  <button type="submit" id="btnSubmitFeedback" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Feedback
                  </button>
                </div>

              <?php echo form_close(); ?>
            </div>
          </div>

          <!-- Submitted Success State Box (Initially hidden) -->
          <div id="feedbackSubmittedSuccessCard" class="card card-success shadow-sm mb-5 d-none">
            <div class="card-body p-4 p-md-5 text-center">
              <div class="d-inline-block rounded-circle p-3 mb-3" style="background-color: #e8f5e9;">
                <i class="fas fa-check-circle text-success fa-5x"></i>
              </div>
              <h4 class="font-weight-bold text-success mb-2">Thank You for Your Feedback!</h4>
              <p class="text-muted mb-4">Your feedback has been recorded successfully. We appreciate your valuable input.</p>
              
              <div class="alert alert-light border p-3 font-weight-bold text-secondary" style="font-size: 0.92rem;">
                <i class="fas fa-calendar-check text-primary mr-2"></i> Submitted On: <span id="feedbackSubmittedAtText"></span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  <!-- Public Footer -->
  <footer class="footer-public text-center">
    <div class="container">
      <p class="mb-0">
        Copyright &copy; <?= date('Y'); ?> Designed &amp; Developed by 
        <a href="https://www.i-net.in/" target="_blank" class="text-primary font-weight-bold">I-NET Secure Labs Pvt. Ltd</a>. All Rights Reserved.
      </p>
    </div>
  </footer>

</div>

<!-- Scripts -->
<script src="<?= $theme_path ?>/assets/plugins/jquery/jquery.min.js"></script>
<script src="<?= $theme_path ?>/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $theme_path ?>/assets/plugins/toastr/toastr.min.js"></script>
<script src="<?= $theme_path ?>/assets/dist/js/adminlte.min.js"></script>
<script src="<?= $theme_path ?>/js/custom-script.js"></script>
</body>
</html>
