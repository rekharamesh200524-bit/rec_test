<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= !empty($page_title) ? htmlspecialchars($page_title) : 'Feedback Already Submitted | Candidate Portal'; ?></title>

  <?php $theme_path = $this->config->item('theme_locations') . $this->config->item('active_template'); ?>
  
  <link href="<?= $theme_path ?>/images/favicon.png" rel="shortcut icon">
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,600,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="<?= $theme_path ?>/assets/plugins/fontawesome-free/css/all.min.css">
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
        <span class="badge badge-pill badge-light text-success font-weight-bold px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
          <i class="fas fa-check-circle mr-1"></i> Feedback Recorded
        </span>
      </div>
    </div>
  </nav>

  <!-- Main Content Wrapper -->
  <div class="content-wrapper py-4">
    <div class="container">

      <!-- Hero Header -->
      <div class="portal-hero hero-feedback-success text-center position-relative overflow-hidden">
        <h1 class="mb-2"><i class="fas fa-check-double mr-2"></i> Feedback Already Submitted</h1>
        <p class="lead mb-0 text-white-50">Your application experience feedback has already been recorded.</p>
      </div>

      <!-- Main Content Card Row -->
      <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">

          <!-- Application Details Card -->
          <div class="card shadow-sm mb-4">
            <div class="card-header py-3 bg-white border-bottom">
              <h5 class="font-weight-bold text-dark mb-0">
                <i class="fas fa-file-alt text-primary mr-2"></i> Application Record
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
                  <span class="text-muted small d-block">APPLICATION STATUS:</span>
                  <span class="badge badge-info px-3 py-1 font-weight-bold" style="font-size: 0.88rem;">
                    <i class="fas fa-clock mr-1"></i> <?= htmlspecialchars($app['CurrentStatus'] ?? 'CV Uploaded'); ?>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Feedback Record Card -->
          <div class="card card-feedback-submitted shadow-sm mb-5 text-center p-4 p-md-5">
            <div class="mb-4">
              <div class="d-inline-block rounded-circle p-3 mb-3" style="background-color: #e8f5e9;">
                <i class="fas fa-check-circle text-success fa-5x"></i>
              </div>
              <h4 class="font-weight-bold text-success mb-2">Feedback Already Submitted</h4>
              <p class="text-muted mb-3">
                Your feedback for this application has already been received and recorded in our database.
              </p>
              
              <div class="badge badge-light border text-secondary px-3 py-2 font-weight-bold" style="font-size: 0.92rem;">
                <i class="fas fa-calendar-check text-primary mr-2"></i> Submitted On: <?= htmlspecialchars($submitted_at_formatted ?? date('d M Y')); ?>
              </div>
            </div>

            <?php if (!empty($feedback)): ?>
            <div class="text-left bg-light p-4 rounded border mb-4">
              <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                <i class="fas fa-star text-warning mr-2"></i> Rating Summary Recorded
              </h6>
              <div class="row">
                <div class="col-md-6 mb-2">
                  <span class="text-muted d-block small">Overall Experience:</span>
                  <span class="star-display">
                    <?= str_repeat('<i class="fas fa-star"></i>', (int)$feedback['OverallExperience']); ?>
                    <?= str_repeat('<i class="far fa-star text-muted"></i>', 5 - (int)$feedback['OverallExperience']); ?>
                  </span>
                </div>
                <div class="col-md-6 mb-2">
                  <span class="text-muted d-block small">Application Process:</span>
                  <span class="star-display">
                    <?= str_repeat('<i class="fas fa-star"></i>', (int)$feedback['ApplicationProcess']); ?>
                    <?= str_repeat('<i class="far fa-star text-muted"></i>', 5 - (int)$feedback['ApplicationProcess']); ?>
                  </span>
                </div>
                <div class="col-md-6 mb-2">
                  <span class="text-muted d-block small">Ease of Applying:</span>
                  <span class="star-display">
                    <?= str_repeat('<i class="fas fa-star"></i>', (int)$feedback['EaseOfApplying']); ?>
                    <?= str_repeat('<i class="far fa-star text-muted"></i>', 5 - (int)$feedback['EaseOfApplying']); ?>
                  </span>
                </div>
                <div class="col-md-6 mb-2">
                  <span class="text-muted d-block small">Communication &amp; Clarity:</span>
                  <span class="star-display">
                    <?= str_repeat('<i class="fas fa-star"></i>', (int)$feedback['Communication']); ?>
                    <?= str_repeat('<i class="far fa-star text-muted"></i>', 5 - (int)$feedback['Communication']); ?>
                  </span>
                </div>
              </div>

              <?php if (!empty($feedback['Comments'])): ?>
                <div class="mt-3 pt-2 border-top">
                  <span class="text-muted d-block small">Comments:</span>
                  <p class="mb-0 text-dark font-italic">"<?= htmlspecialchars($feedback['Comments']); ?>"</p>
                </div>
              <?php endif; ?>
            </div>
            <?php endif; ?>
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
<script src="<?= $theme_path ?>/assets/dist/js/adminlte.min.js"></script>

</body>
</html>
