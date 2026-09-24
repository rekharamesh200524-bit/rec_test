<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= !empty($page_title) ? htmlspecialchars($page_title) : 'Apply for a Position | Recruitment Portal'; ?></title>

  <?php $theme_path = $this->config->item('theme_locations') . $this->config->item('active_template'); ?>
  
  <link href="<?= $theme_path ?>/images/favicon.png" rel="shortcut icon">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,600,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="<?= $theme_path ?>/assets/plugins/fontawesome-free/css/all.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="<?= $theme_path ?>/assets/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="<?= $theme_path ?>/assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
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
          <i class="fas fa-user-plus mr-1"></i> Public Candidate Portal
        </span>
      </div>
    </div>
  </nav>

  <!-- Main Content Wrapper -->
  <div class="content-wrapper py-4">
    <div class="container">

      <!-- Hero Header -->
      <div class="portal-hero text-center position-relative overflow-hidden">
        <h1 class="mb-2"><i class="fas fa-paper-plane mr-2"></i> Apply for a Position</h1>
        <p class="lead mb-0 text-white-50">Upload your resume to get started with your application.</p>
      </div>

      <!-- Main Container Card -->
      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
          <div class="card card-apply shadow-sm mb-5">
            
            <!-- STEP 1: RESUME UPLOAD SECTION -->
            <div id="step1UploadSection" class="card-body p-4 p-md-5">
              <div class="text-center mb-4">
                <h4 class="font-weight-bold text-dark mb-1">
                  <span class="step-indicator">1</span> Upload Your Resume
                </h4>
                <p class="text-muted">Upload your resume file to get started with the recruitment application process.</p>
              </div>

              <!-- Drag & Drop Zone -->
              <div class="upload-dragzone shadow-sm mb-4" id="dragDropZone">
                <i class="fas fa-cloud-upload-alt text-primary fa-4x mb-3"></i>
                <h5 class="font-weight-bold text-dark mb-2">Drag and drop your resume file here</h5>
                <p class="text-muted mb-3">or click below to choose a file from your device</p>
                
                <button type="button" id="btnChooseResume" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm">
                  <i class="fas fa-folder-open mr-2"></i> Choose Resume
                </button>

                <input type="file" id="resumeFileInput" accept=".pdf,.doc,.docx" class="d-none">

                <div class="mt-3">
                  <span class="badge badge-light text-secondary px-3 py-2 border" style="font-size: 0.82rem;">
                    <i class="fas fa-info-circle text-info mr-1"></i> Supported formats: <strong>PDF, DOC, DOCX</strong> (Max: 5MB)
                  </span>
                </div>
              </div>

              <!-- Uploading Spinner -->
              <div id="uploadSpinner" class="text-center py-4 d-none">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                  <span class="sr-only">Uploading...</span>
                </div>
                <h6 class="font-weight-bold text-dark mt-3 mb-1">Uploading resume...</h6>
                <p class="text-muted" style="font-size: 0.9rem;">Please wait while we process your file securely.</p>
              </div>
            </div>

            <!-- STEP 2: CONFIRMATION & AUTO-FILL CHOICE SECTION -->
            <div id="step2ChoiceSection" class="card-body p-4 p-md-5 text-center d-none">
              <div class="mb-4">
                <div class="d-inline-block rounded-circle bg-success-light p-3 mb-3" style="background-color: #e8f5e9;">
                  <i class="fas fa-check-circle text-success fa-4x"></i>
                </div>
                <h4 class="font-weight-bold text-dark mb-1">Resume Uploaded Successfully</h4>
                <div id="uploadedFileSummary" class="text-muted font-weight-bold my-2" style="font-size: 0.95rem;">
                  <!-- Dynamically populated with file icon, original filename, file size -->
                </div>
                <button type="button" id="btnRemoveResume" class="btn btn-sm btn-link text-danger font-weight-bold">
                  <i class="fas fa-times-circle mr-1"></i> Remove / Change Resume
                </button>
              </div>

              <hr class="my-4">

              <div class="py-2">
                <h5 class="font-weight-bold text-dark mb-3">
                  Would you like us to automatically fill your application details?
                </h5>
                <p class="text-muted mb-4" style="max-width: 540px; margin: 0 auto;">
                  Our system can parse information such as your name, contact information, skills, and experience directly from your uploaded resume to save you time.
                </p>

                <div class="d-flex justify-content-center align-items-center flex-wrap gap-3">
                  <!-- Button 1: Yes, Auto-fill (Primary) -->
                  <button type="button" id="btnAutoFill" class="btn btn-primary btn-lg font-weight-bold px-4 py-2 m-2 shadow-sm">
                    <i class="fas fa-magic mr-2"></i> Yes, Auto-fill
                  </button>

                  <!-- Button 2: Enter Details Manually (Secondary / Outline) -->
                  <button type="button" id="btnManualEntry" class="btn btn-outline-secondary btn-lg font-weight-bold px-4 py-2 m-2">
                    <i class="fas fa-pen mr-2"></i> Enter Details Manually
                  </button>
                </div>
              </div>
            </div>

            <!-- STEP 3: CANDIDATE APPLICATION FORM SECTION -->
            <div id="step3FormSection" class="card-body p-4 d-none">
              
              <!-- Auto-Fill Indication Alert (Visible only when auto-filled) -->
              <div id="autoFillNoticeAlert" class="alert alert-info border-info shadow-sm d-flex align-items-center mb-4 d-none">
                <i class="fas fa-info-circle fa-lg text-info mr-3"></i>
                <div>
                  <strong class="d-block text-info">Details extracted from your resume.</strong>
                  <span style="font-size: 0.9rem;">Please review and edit any values before submitting.</span>
                </div>
              </div>

              <!-- Attached Resume Summary Box -->
              <div class="p-3 bg-light rounded border mb-4 d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center my-1">
                  <i class="fas fa-file-alt text-primary fa-2x mr-3"></i>
                  <div>
                    <span class="text-muted small d-block font-weight-bold">ATTACHED RESUME</span>
                    <strong id="attachedResumeDisplayName" class="text-dark">Resume File</strong>
                    <span id="attachedResumeDisplaySize" class="badge badge-secondary ml-2"></span>
                  </div>
                </div>
                <button type="button" id="btnChangeAttachedResume" class="btn btn-sm btn-outline-secondary font-weight-bold my-1">
                  <i class="fas fa-sync-alt mr-1"></i> Change Resume
                </button>
              </div>

              <div class="border-bottom pb-2 mb-4 d-flex justify-content-between align-items-center">
                <h5 class="card-title font-weight-bold text-dark mb-0">
                  <i class="fas fa-user-edit text-primary mr-2"></i> Application Details
                </h5>
                <span class="text-muted" style="font-size: 0.85rem;"><span class="required-star">*</span> Required fields</span>
              </div>

              <?php 
                $attributes = array(
                  'id' => 'candidateApplyForm',
                  'class' => 'needs-validation',
                  'novalidate' => 'novalidate',
                  'data-upload-url' => base_url('recruitment/user/upload_resume'),
                  'data-parse-url'  => base_url('recruitment/user/parse_resume'),
                  'data-submit-url' => base_url('recruitment/user/submitApplication')
                );
                echo form_open_multipart('recruitment/user/apply', $attributes); 
              ?>

                <?php if ($this->config->item('csrf_protection')): ?>
                  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                <?php endif; ?>

                <!-- Hidden Input for Attached Uploaded Resume Token -->
                <input type="hidden" name="resume_file_token" id="resume_file_token" value="">

                <!-- Personal Information Section -->
                <h6 class="text-primary font-weight-bold text-uppercase border-bottom pb-2 mb-3" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                  <i class="fas fa-user-circle mr-1"></i> Personal Information
                </h6>

                <div class="row">
                  <!-- Full Name -->
                  <div class="col-md-6 form-group">
                    <label for="full_name">Full Name <span class="required-star">*</span></label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                      </div>
                      <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Enter your full name" required>
                    </div>
                  </div>

                  <!-- Email Address -->
                  <div class="col-md-6 form-group">
                    <label for="email">Email Address <span class="required-star">*</span></label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                      </div>
                      <input type="email" name="email" id="email" class="form-control" placeholder="e.g. john.doe@example.com" required>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <!-- Phone Number -->
                  <div class="col-md-6 form-group">
                    <label for="phone">Phone Number <span class="required-star">*</span></label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                      </div>
                      <input type="tel" name="phone" id="phone" class="form-control" placeholder="e.g. +91 9876543210" required>
                    </div>
                  </div>

                  <!-- Years of Experience -->
                  <div class="col-md-6 form-group">
                    <label for="experience_years">Years of Experience <span class="required-star">*</span></label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-history text-muted"></i></span>
                      </div>
                      <input type="number" name="experience_years" id="experience_years" class="form-control" min="0" max="50" step="0.1" placeholder="e.g. 3.5" required>
                      <div class="input-group-append">
                        <span class="input-group-text">Years</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Professional Information Section -->
                <h6 class="text-primary font-weight-bold text-uppercase border-bottom pb-2 mb-3 mt-4" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                  <i class="fas fa-briefcase mr-1"></i> Job Preference & Skills
                </h6>

                <div class="row">
                  <!-- Position Applying For (Dynamic DB Approved Vacancies Only) -->
                  <div class="col-md-6 form-group">
                    <label for="position">Position Applying For <span class="required-star">*</span></label>
                    <select name="position" id="position" class="form-control" required style="width:100%;">
                      <option value=""></option>
                      <?php if (!empty($vacancies)): ?>
                        <?php foreach ($vacancies as $vac): ?>
                          <option value="<?= htmlspecialchars($vac['Jid']); ?>">
                            <?= htmlspecialchars($vac['JobTitle']); ?>
                            <?= !empty($vac['JobCode']) ? ' ('.htmlspecialchars($vac['JobCode']).')' : ''; ?>
                            <?= !empty($vac['Departmentname']) ? ' - '.htmlspecialchars($vac['Departmentname']) : ''; ?>
                          </option>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <option value="" disabled>No open vacancies currently available</option>
                      <?php endif; ?>
                    </select>
                  </div>

                  <!-- Key Skills (Interactive Tag/Chip Input) -->
                  <div class="col-md-6 form-group">
                    <label for="skill_input">Skills</label>
                    <div class="skills-chip-container p-2 border" id="skillsChipWrapper" style="min-height: 44px; cursor: text;">
                      <!-- Dynamic Skill Chips Rendered Here -->
                      <input type="text" id="skill_input" class="skill-input-field border-0 flex-grow-1 px-2 py-1" placeholder="Type a skill and press Enter..." style="outline: none; min-width: 170px; font-size: 0.95rem;">
                    </div>

                    <!-- Hidden Input for Form Submission -->
                    <input type="hidden" name="skills" id="skills_hidden" value="">

                    <small class="form-text text-muted">
                      Type a skill and press <kbd>Enter</kbd> or <kbd>,</kbd> to add. Click <i class="fas fa-times text-danger"></i> to remove.
                    </small>
                  </div>
                </div>

                <!-- Cover Letter Section -->
                <h6 class="text-primary font-weight-bold text-uppercase border-bottom pb-2 mb-3 mt-4" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                  <i class="fas fa-pen-fancy mr-1"></i> Additional Information
                </h6>

                <div class="form-group">
                  <label for="cover_letter">Optional Cover Letter</label>
                  <textarea name="cover_letter" id="cover_letter" class="form-control" rows="4" placeholder="Briefly describe why you are a great fit for this role..."></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 border-top d-flex justify-content-end align-items-center">
                  <button type="button" id="btnBackToChoice" class="btn btn-outline-secondary mr-auto font-weight-bold">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                  </button>
                  <button type="submit" class="btn btn-primary font-weight-bold px-5 py-2 shadow-sm">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Application
                  </button>
                </div>

              <?php echo form_close(); ?>
            </div>

            <!-- STEP 4A: SUCCESS RESULT CARD (hidden by default) -->
            <div id="stepSuccessSection" class="card-body p-4 p-md-5 text-center d-none">
              <div class="mb-4">
                <div class="d-inline-block rounded-circle p-3 mb-3" style="background-color:#e8f5e9;">
                  <i class="fas fa-check-circle text-success fa-5x"></i>
                </div>
                <h4 class="font-weight-bold text-success mb-1">Application Submitted Successfully!</h4>
                <p class="text-muted">Thank you for applying. Your application has been received.</p>
              </div>

              <div class="row justify-content-center">
                <div class="col-md-8">
                  <div class="card border-0 shadow-sm" style="border-radius:0.75rem; overflow:hidden;">
                    <div class="card-header py-2" style="background:linear-gradient(135deg,#007bff,#0056b3);">
                      <h6 class="mb-0 text-white font-weight-bold">
                        <i class="fas fa-file-alt mr-2"></i> Application Details
                      </h6>
                    </div>
                    <div class="card-body py-3 px-4 text-left">
                      <table class="table table-sm table-borderless mb-0">
                        <tbody>
                          <tr>
                            <td class="text-muted font-weight-bold" style="width:45%;"><i class="fas fa-hashtag mr-1 text-primary"></i> Application ID</td>
                            <td><strong id="successAppId" class="text-dark">—</strong></td>
                          </tr>
                          <tr>
                            <td class="text-muted font-weight-bold"><i class="fas fa-briefcase mr-1 text-primary"></i> Position</td>
                            <td><strong id="successJobTitle" class="text-dark">—</strong></td>
                          </tr>
                          <tr>
                            <td class="text-muted font-weight-bold"><i class="fas fa-info-circle mr-1 text-primary"></i> Application Status</td>
                            <td><span id="successStatus" class="badge badge-info px-3 py-1" style="font-size:0.88rem;">CV Uploaded</span></td>
                          </tr>
                          <tr>
                            <td class="text-muted font-weight-bold"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Applied On</td>
                            <td><span id="successAppliedOn" class="text-dark">—</span></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div class="alert alert-light border mt-4 text-left" style="border-radius:0.5rem;">
                    <i class="fas fa-lightbulb text-warning mr-2"></i>
                    <strong>What happens next?</strong> Our HR team will review your application and contact you if you are shortlisted for the next round.
                  </div>

                  <button type="button" id="btnApplyAnother" class="btn btn-outline-primary font-weight-bold mt-2">
                    <i class="fas fa-plus-circle mr-1"></i> Apply for Another Position
                  </button>
                </div>
              </div>
            </div>

            <!-- STEP 4B: ALREADY APPLIED CARD (hidden by default) -->
            <div id="stepAlreadyAppliedSection" class="card-body p-4 p-md-5 text-center d-none">
              <div class="mb-4">
                <div class="d-inline-block rounded-circle p-3 mb-3" style="background-color:#fff3e0;">
                  <i class="fas fa-clipboard-check text-warning fa-5x"></i>
                </div>
                <h4 class="font-weight-bold text-warning mb-1">Already Applied</h4>
                <p class="text-muted">You have already applied for this position. We've kept your original application.</p>
              </div>

              <div class="row justify-content-center">
                <div class="col-md-8">
                  <div class="card border-0 shadow-sm" style="border-radius:0.75rem; overflow:hidden;">
                    <div class="card-header py-2" style="background:linear-gradient(135deg,#f5a623,#e59400);">
                      <h6 class="mb-0 text-white font-weight-bold">
                        <i class="fas fa-file-alt mr-2"></i> Existing Application Details
                      </h6>
                    </div>
                    <div class="card-body py-3 px-4 text-left">
                      <table class="table table-sm table-borderless mb-0">
                        <tbody>
                          <tr>
                            <td class="text-muted font-weight-bold" style="width:45%;"><i class="fas fa-hashtag mr-1 text-warning"></i> Application ID</td>
                            <td><strong id="dupAppId" class="text-dark">—</strong></td>
                          </tr>
                          <tr>
                            <td class="text-muted font-weight-bold"><i class="fas fa-briefcase mr-1 text-warning"></i> Position Applied For</td>
                            <td><strong id="dupJobTitle" class="text-dark">—</strong></td>
                          </tr>
                          <tr>
                            <td class="text-muted font-weight-bold"><i class="fas fa-info-circle mr-1 text-warning"></i> Current Status</td>
                            <td><span id="dupStatus" class="badge badge-warning px-3 py-1" style="font-size:0.88rem;">Under Review</span></td>
                          </tr>
                          <tr>
                            <td class="text-muted font-weight-bold"><i class="fas fa-calendar-alt mr-1 text-warning"></i> Applied On</td>
                            <td><span id="dupAppliedOn" class="text-dark">—</span></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div class="alert alert-info border-info mt-4 text-left" style="border-radius:0.5rem;">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Did you mean to apply for a different position?</strong> You can apply for any other open vacancy using a separate application.
                  </div>

                  <button type="button" id="btnApplyDifferent" class="btn btn-outline-primary font-weight-bold mt-2">
                    <i class="fas fa-arrow-left mr-1"></i> Apply for a Different Position
                  </button>
                </div>
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
<script src="<?= $theme_path ?>/assets/plugins/select2/js/select2.full.min.js"></script>
<script src="<?= $theme_path ?>/assets/plugins/toastr/toastr.min.js"></script>
<script src="<?= $theme_path ?>/assets/dist/js/adminlte.min.js"></script>
<script src="<?= $theme_path ?>/js/custom-script.js"></script>
</body>
</html>
