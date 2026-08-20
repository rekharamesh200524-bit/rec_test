 <?php
    $employee_det = $this->session->userdata('logged_in');

    if (empty($employee_det)) {
        redirect($this->config->item('base_url') . 'admin/index');
    }
    $theme_path = $this->config->item('theme_locations') . $this->config->item('active_template');

    ?>



  <!-- Main content -->
  <section class="content">
      <div class="container-fluid">

          <div class="card card-success card-outline">
              <div class="card-header">
                  <div class="d-flex justify-content-between align-items-center">
                      <h3 class="card-title mb-0"><i class="fas fa-briefcase text-primary mr-2"></i> Vacancy List</h3>
                  </div>
              </div>
              <div class="">

                  <form method="POST" action="<?= base_url('admin/vacancies') ?>" class="mb-4">

                      <div class="card card-light">
                          <div class="card-body">

                              <div class="row align-items-end">

                                  <!-- Date Range -->
                                  <div class="col-md-3">
                                      <div class="form-group mb-0">
                                          <label>Posted Date</label>
                                          <input type="text"
                                              name="dateRange"
                                              id="dateRange"
                                              class="form-control"
                                              placeholder="Select date"
                                              value="<?= htmlspecialchars($this->input->post('dateRange', TRUE) ?: $this->input->get('dateRange', TRUE)) ?>">
                                      </div>
                                  </div>

                                  <!-- Department -->
                                  <div class="col-md-3">
                                      <div class="form-group mb-0">
                                          <label>Department</label>
                                          <select name="department" class="form-control" onchange="this.form.submit()">
                                              <option value="">All Departments</option>
                                              <?php foreach ($department as $d): ?>
                                                  <option value="<?= $d['Departmentname'] ?>"
                                                      <?= (($this->input->post('department', TRUE) ?: $this->input->get('department', TRUE)) == $d['Departmentname']) ? 'selected' : '' ?>>
                                                      <?= $d['Departmentname'] ?>
                                                  </option>
                                              <?php endforeach; ?>
                                          </select>
                                      </div>
                                  </div>

                                  <!-- Status -->
                                  <div class="col-md-3">
                                      <div class="form-group mb-0">
                                          <label>Status</label>
                                          <select name="status" class="form-control" onchange="this.form.submit()">
                                              <option value="">All Status</option>
                                              <option value="Open" <?= (($this->input->post('status', TRUE) ?: $this->input->get('status', TRUE)) == 'Open') ? 'selected' : '' ?>>Open</option>
                                              <option value="Dropped" <?= (($this->input->post('status', TRUE) ?: $this->input->get('status', TRUE)) == 'Dropped' || ($this->input->post('status', TRUE) ?: $this->input->get('status', TRUE)) == 'Closed') ? 'selected' : '' ?>>Dropped</option>
                                              <option value="On-Hold" <?= (($this->input->post('status', TRUE) ?: $this->input->get('status', TRUE)) == 'On-Hold') ? 'selected' : '' ?>>On-Hold</option>
                                              <option value="Draft" <?= (($this->input->post('status', TRUE) ?: $this->input->get('status', TRUE)) == 'Draft') ? 'selected' : '' ?>>Draft</option>
                                          </select>
                                      </div>
                                  </div>

                                  <!-- Reset Button -->
                                   <div class="col-md-2 d-flex align-items-end mb-1">
                                       <a href="<?= base_url('admin/vacancies') ?>"
                                           class="btn btn-outline-secondary btn-sm font-weight-bold">
                                           <i class="fas fa-undo mr-1"></i> Reset
                                       </a>
                                   </div>

                              </div>

                          </div>
                      </div>

                  </form>

                  <!-- /.card-header -->
                  <div class="card-body table-responsive">
                      <table id="example1" class="table table-bordered table-striped">
                          <thead class="bg-success">
                              <tr class="text-nowrap">
                                  <th>S.No</th>
                                  <th>Job Code</th>
                                  <th>Job Title</th>
                                  <th>Job Role</th>
                                  <th>Department</th>
                                  <th>Employment</th>
                                  <th>Work Mode</th>
                                  <th class="text-center">Position</th>
                                  <th class="text-center">Candidates</th>
                                  <th class="text-center">Job Status</th>
                                  <th>Posted On</th>
                                  <th class="text-center">Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php

                                // echo "<pre>vaclist"; print_r($vaclist); exit;

                                if (isset($vaclist) && !empty($vaclist)) {
                                    $i = 1;
                                    foreach ($vaclist as $vl) {
                                ?>
                                      <tr class="text-nowrap">
                                          <td><?= $i++; ?></td>
                                          <td><a href="<?php echo $this->config->item('base_url') ?>admin/Candidatelist/<?php echo $vl['Jid']; ?>"><?= $vl['JobCode'] ?></a></td>
                                          <td><?= htmlspecialchars($vl['JobTitle'] ?? ''); ?></td>
                                          <td><?= htmlspecialchars($vl['RoleSummary'] ?? ''); ?></td>
                                          <td><?= htmlspecialchars($vl['Departmentname'] ?? ''); ?></td>
                                          <td><?= $vl['EmploymentType'] ?></td>
                                          <td><?= $vl['WorkMode'] ?></td>
                                          <td class="text-center"><?= $vl['NoofOpenings'] ?></td>
                                          <td class="text-center">
                                              <?php $cnt = isset($vl['CandidateCount']) ? (int)$vl['CandidateCount'] : 0; ?>
                                              <span class="badge badge-pill <?= $cnt > 0 ? 'badge-info' : 'badge-secondary'; ?>">
                                                  <i class="fas fa-users mr-1"></i><?= $cnt; ?>
                                              </span>
                                          </td>
                                          <td class="text-center">
                                               <span class="badge badge-pill <?= ($vl['JobStatus'] == 'Closed' || $vl['JobStatus'] == 'Dropped') ? 'badge-danger' : ($vl['JobStatus'] == 'Open' ? 'badge-success' : ($vl['JobStatus'] == 'On-Hold' ? 'badge-warning' : 'badge-secondary')) ?>">
                                                   <?= ($vl['JobStatus'] == 'Closed' || $vl['JobStatus'] == 'Dropped') ? 'Dropped' : htmlspecialchars($vl['JobStatus']); ?>
                                               </span>
                                               <button type="button"
                                                   class="btn btn-xs btn-outline-info viewJobHistoryBtn ml-1"
                                                   title="View Job Life-Cycle History"
                                                   data-id="<?= $vl['Jid']; ?>">
                                                   <i class="fas fa-history"></i>
                                               </button>
                                           </td>
                                          <td><?= !empty($vl['PostedOn']) ? date('d-m-Y', strtotime($vl['PostedOn'])) : '-'; ?></td>
                                          <td class="text-center">

                                              <div class="btn-group" role="group">

                                                  <!-- Edit Job -->
                                                  <button type="button"
                                                      class="btn btn-sm btn-primary editJobBtn"
                                                      title="Edit Job"
                                                      data-id="<?= $vl['Jid']; ?>">
                                                      <i class="fas fa-edit"></i>
                                                  </button>

                                                  <button type="button"
                                                      class="btn btn-sm btn-secondary viewVacancyBtn"
                                                      title="View Vacancy"
                                                      data-id="<?= $vl['Jid']; ?>">
                                                      <i class="fas fa-eye"></i>
                                                  </button>

                                                  <?php if ($vl['JobStatus'] == 'Open') { ?>

                                                      <!-- Put On Hold -->
                                                      <button type="button"
                                                          class="btn btn-sm btn-warning jobStatusBtn"
                                                          data-id="<?= $vl['Jid']; ?>"
                                                          data-status="On-Hold"
                                                          title="Put On Hold">
                                                          <i class="fas fa-pause-circle"></i>
                                                      </button>

                                                      <!-- Drop Job -->
                                                      <button type="button"
                                                          class="btn btn-sm btn-danger jobStatusBtn"
                                                          data-id="<?= $vl['Jid']; ?>"
                                                          data-status="Dropped"
                                                          title="Drop Job">
                                                          <i class="fas fa-times-circle"></i>
                                                      </button>

                                                      <!-- Upload Resumes -->
                                                      <button type="button"
                                                          class="btn btn-sm btn-success uploadResumeBtn"
                                                          data-id="<?= $vl['Jid']; ?>"
                                                          title="Upload Resumes">
                                                          <i class="fas fa-upload"></i>
                                                      </button>

                                                  <?php } elseif ($vl['JobStatus'] == 'On-Hold') { ?>

                                                      <button type="button"
                                                          class="btn btn-sm btn-success jobStatusBtn"
                                                          data-id="<?= $vl['Jid']; ?>"
                                                          data-status="Open"
                                                          title="Re-Open Job">
                                                          <i class="fas fa-play-circle"></i>
                                                      </button>

                                                  <?php } elseif ($vl['JobStatus'] == 'Closed' || $vl['JobStatus'] == 'Dropped') { ?>

                                                      <button type="button"
                                                          class="btn btn-sm btn-info jobStatusBtn"
                                                          data-id="<?= $vl['Jid']; ?>"
                                                          data-status="Open"
                                                          title="Re-Open Job">
                                                          <i class="fas fa-redo"></i>
                                                      </button>

                                                  <?php } elseif ($vl['JobStatus'] == 'Re-Open') { ?>

                                                      <button type="button"
                                                          class="btn btn-sm btn-success jobStatusBtn"
                                                          data-id="<?= $vl['Jid']; ?>"
                                                          data-status="Open"
                                                          title="Mark as Open">
                                                          <i class="fas fa-check-circle"></i>
                                                      </button>

                                                      <!-- Drop Job -->
                                                      <button type="button"
                                                          class="btn btn-sm btn-danger jobStatusBtn"
                                                          data-id="<?= $vl['Jid']; ?>"
                                                          data-status="Dropped"
                                                          title="Drop Job">
                                                          <i class="fas fa-times-circle"></i>
                                                      </button>

                                                  <?php } elseif ($vl['JobStatus'] == 'Draft') { ?>

                                                      <button type="button"
                                                          class="btn btn-sm btn-success jobStatusBtn"
                                                          data-id="<?= $vl['Jid']; ?>"
                                                          data-status="Open"
                                                          title="Publish Job">
                                                          <i class="fas fa-upload"></i>
                                                      </button>

                                                  <?php } elseif ($vl['JobStatus'] == 'Not Required') { ?>

                                                      <button type="button"
                                                          class="btn btn-sm btn-secondary"
                                                          disabled
                                                          title="Job Not Required">
                                                          <i class="fas fa-ban"></i>
                                                      </button>

                                                  <?php } ?>

                                              </div>

                                          </td>

                                      </tr>
                              <?php
                                    }
                                }
                                ?>

                          </tbody>
                      </table>
                  </div>
                  <!-- /.card-body -->
              </div><!-- /.card -->
          </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->

  <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content" style="border-radius:12px; overflow:hidden;">

              <div class="modal-header bg-success text-white py-3">
                  <h5 class="modal-title font-weight-bold">
                      <i class="fas fa-file-upload mr-2"></i>Upload Multiple Candidate Resumes
                  </h5>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
              </div>

              <div class="modal-body p-4">
                  <form id="bulkResumeUploadForm" enctype="multipart/form-data">
                      <input type="hidden" id="upload_job_id" name="job_id">
                      
                      <!-- Drag & Drop Zone -->
                      <div id="dropZoneArea" class="p-5 text-center rounded border-2 border-dashed bg-light mb-3" style="border: 2px dashed #28a745; cursor: pointer; transition: all 0.3s ease;">
                          <i class="fas fa-cloud-upload-alt fa-3x text-success mb-3"></i>
                          <h5 class="font-weight-bold text-dark mb-1">Drag & Drop Resumes Here</h5>
                          <p class="text-muted small mb-3">Select multiple <strong>PDF</strong> or <strong>DOCX</strong> files to batch analyze with ATS Engine</p>
                          <button type="button" class="btn btn-outline-success font-weight-bold px-4 rounded-pill" onclick="$('#bulkResumeInput').click();">
                              <i class="fas fa-folder-open mr-2"></i>Browse Files (Multiple Allowed)
                          </button>
                          <input type="file" id="bulkResumeInput" name="resumes[]" multiple accept=".pdf,.docx,.doc" class="d-none">
                      </div>

                      <!-- Selected Files Preview List -->
                      <div id="selectedFilesContainer" class="d-none mb-3">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                              <h6 class="font-weight-bold text-dark mb-0">
                                  <i class="fas fa-paperclip text-info mr-2"></i>Selected Files (<span id="selectedFileCount">0</span>)
                              </h6>
                              <button type="button" class="btn btn-xs btn-outline-danger" id="btnClearSelectedFiles">
                                  <i class="fas fa-trash-alt mr-1"></i>Clear All
                              </button>
                          </div>
                          <div id="selectedFilesList" class="p-2 bg-light border rounded" style="max-height:180px; overflow-y:auto;"></div>
                      </div>

                      <!-- Upload Progress Bar -->
                      <div id="uploadProgressBarContainer" class="progress mb-3 d-none" style="height: 12px; border-radius: 6px;">
                          <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;"></div>
                      </div>

                      <!-- Status Message Alert -->
                      <div id="uploadStatusAlert" class="alert d-none mb-3" role="alert"></div>

                      <div class="d-flex justify-content-end align-items-center pt-3 border-top">
                          <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
                          <button type="submit" id="btnSubmitBulkResumes" class="btn btn-success font-weight-bold px-4" disabled>
                              <i class="fas fa-cogs mr-2"></i>Upload & Analyze Resumes
                          </button>
                      </div>
                  </form>
              </div>

          </div>
      </div>
  </div>

  <!-- Right Side Panel -->
    <!-- Right Side Panel -->
  <div id="vacancyPanel" class="right-form">
      <div class="right-form-header">
          <h5>Request Resource</h5>
          <button type="button" class="close-btn" id="closeVacancyPanel">&times;</button>
      </div>

      <div class="right-form-body">
          <div class="row">
              <div class="col-md-12">
                  <div class="card card-default shadow-none border-0">
                      <div class="card-header">
                          <h3 class="card-title font-weight-bold">Job Details</h3>
                      </div>
                      <form action="<?= base_url('admin/saveVacancy') ?>" method="post">
                          <div class="card-body p-0">
                              <div class="bs-stepper">
                                  <div class="bs-stepper-header" role="tablist">
                                      <div class="step" data-target="#logins-part">
                                          <button type="button" class="step-trigger" role="tab" aria-controls="logins-part" id="logins-part-trigger">
                                              <span class="bs-stepper-circle">1</span>
                                              <span class="bs-stepper-label">JOB INFO</span>
                                          </button>
                                      </div>
                                      <div class="line"></div>
                                      <div class="step" data-target="#information-part">
                                          <button type="button" class="step-trigger" role="tab" aria-controls="information-part" id="information-part-trigger">
                                              <span class="bs-stepper-circle">2</span>
                                              <span class="bs-stepper-label">SALARY & DATES</span>
                                          </button>
                                      </div>
                                      <div class="line"></div>
                                      <div class="step" data-target="#skill-part">
                                          <button type="button" class="step-trigger" role="tab" aria-controls="skill-part" id="skill-part-trigger">
                                              <span class="bs-stepper-circle">3</span>
                                              <span class="bs-stepper-label">SKILL & DETAILS</span>
                                          </button>
                                      </div>
                                      <div class="line"></div>
                                      <div class="step" data-target="#ctc-part">
                                          <button type="button" class="step-trigger" role="tab" aria-controls="ctc-part" id="ctc-part-trigger">
                                              <span class="bs-stepper-circle">4</span>
                                              <span class="bs-stepper-label">CTC</span>
                                          </button>
                                      </div>
                                  </div>
                                  
                                  <div class="bs-stepper-content mt-3">
                                      <!-- STEP 1: JOB INFO -->
                                      <div id="logins-part" class="content" role="tabpanel" aria-labelledby="logins-part-trigger">
                                          <div class="form-group">
                                              <label class="text-label">Job Title*</label>
                                              <div class="position-relative">
                                                  <input type="text" name="jobTitle" id="jobTitle"
                                                      class="form-control search-input"
                                                      placeholder="Type job title..." autocomplete="off" required>
                                                  <div class="dropdown-menu w-100" id="jobTitleDropdown"></div>
                                              </div>
                                          </div>

                                          <div class="form-group">
                                              <label class="text-label">Department*</label>
                                              <select name="department" id="department" class="form-control" required>
                                                  <option value="">Select Department</option>
                                                  <?php foreach ($department as $d): ?>
                                                      <option value="<?= $d['Did'] ?>">
                                                          <?= htmlspecialchars($d['Departmentname']) ?>
                                                      </option>
                                                  <?php endforeach; ?>
                                              </select>
                                          </div>

                                          <div class="form-group">
                                              <label class="text-label">Role*</label>
                                              <div class="position-relative">
                                                  <input type="text" name="role" id="role"
                                                      class="form-control search-input"
                                                      placeholder="Type role..." autocomplete="off" required>
                                                  <div class="dropdown-menu w-100" id="roleDropdown"></div>
                                              </div>
                                          </div>

                                          <div class="form-group">
                                              <label class="text-label">Position Type <span class="text-danger">*</span></label>
                                              <select name="positionType" id="positionType" class="form-control" required>
                                                  <option value="New Position">New Position</option>
                                                  <option value="Replacement">Replacement</option>
                                              </select>
                                          </div>

                                          <div class="form-group">
                                              <label class="text-label">Approver Name <span class="text-danger">*</span></label>
                                              <select name="approverId" id="approverId" class="form-control" required>
                                                  <option value="">Select Approver</option>
                                                  <?php 
                                                  $approverList = $this->admin_model->getApproverUsers();
                                                  if (!empty($approverList)):
                                                      foreach ($approverList as $app): ?>
                                                          <option value="<?= $app['IUid'] ?>"><?= htmlspecialchars($app['EmpName']) ?> (<?= htmlspecialchars($app['RoleName'] ? $app['RoleName'] : 'Approver') ?>)</option>
                                                      <?php endforeach; 
                                                  endif; ?>
                                              </select>
                                          </div>

                                          <div class="form-group">
                                              <label class="text-label">Work Mode*</label>
                                              <input type="hidden" name="workMode" id="work_mode" required>
                                              <div class="d-flex gap-2">
                                                  <span class="work-mode badge badge-pill badge-outline-primary" data-value="Onsite">Onsite</span>
                                                  <span class="work-mode badge badge-pill badge-outline-success" data-value="Remote">Remote</span>
                                                  <span class="work-mode badge badge-pill badge-outline-info" data-value="Hybrid">Hybrid</span>
                                              </div>
                                          </div>

                                          <div class="form-group">
                                              <label class="text-label">Employment Type*</label>
                                              <input type="hidden" name="employmentType" id="employment_type" required>
                                              <div class="d-flex flex-wrap gap-2">
                                                  <span class="emp-type badge badge-pill badge-outline-primary" data-value="Full-Time">Full-Time</span>
                                                  <span class="emp-type badge badge-pill badge-outline-warning" data-value="Part-Time">Part-Time</span>
                                                  <span class="emp-type badge badge-pill badge-outline-secondary" data-value="Contract">Contract</span>
                                                  <span class="emp-type badge badge-pill badge-outline-dark" data-value="Internship">Internship</span>
                                              </div>
                                          </div>

                                          <button type="button" class="btn btn-primary" onclick="stepper.next()">Next <i class="fas fa-arrow-right ml-1"></i></button>
                                      </div>

                                      <!-- STEP 2: DATES & LOCATION -->
                                      <div id="information-part" class="content" role="tabpanel" aria-labelledby="information-part-trigger">
                                          <div class="form-group">
                                              <label>Location*</label>
                                              <div class="position-relative">
                                                  <input type="text" id="jobLocationInput" class="form-control search-input"
                                                      placeholder="Type location..." autocomplete="off">
                                                  <input type="hidden" name="jobLocation" id="jobLocation">
                                                  <div class="dropdown-menu w-100" id="jobLocationDropdown"></div>
                                              </div>
                                              <div class="chip-container mt-2" id="jobLocationChips"></div>
                                          </div>

                                          <div class="form-group">
                                              <div class="row">
                                                  <div class="col-md-6">
                                                      <label class="text-label">Min Experience*</label>
                                                      <select id="expMin" name="expMin" class="form-control" required>
                                                          <option value="">Min</option>
                                                      </select>
                                                  </div>
                                                  <div class="col-md-6">
                                                      <label class="text-label">Max Experience*</label>
                                                      <select id="expMax" name="expMax" class="form-control" required>
                                                          <option value="">Max</option>
                                                      </select>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="form-group">
                                              <div class="row">
                                                  <div class="col-md-6">
                                                      <label class="text-label">Recruitment Start Date</label>
                                                      <input type="date" name="recruitmentStartDate" id="recruitmentStartDate" class="form-control">
                                                  </div>
                                                  <div class="col-md-6">
                                                      <label class="text-label">Target Onboarding Date</label>
                                                      <input type="date" name="targetOnboardingDate" id="targetOnboardingDate" class="form-control">
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="form-group">
                                              <label>Education*</label>
                                              <div class="position-relative">
                                                  <input type="text" id="educationInput" class="form-control search-input"
                                                      placeholder="Type education..." autocomplete="off">
                                                  <input type="hidden" name="education" id="education">
                                                  <div class="dropdown-menu w-100" id="educationDropdown"></div>
                                              </div>
                                              <div class="chip-container mt-2" id="educationChips"></div>
                                          </div>

                                          <button type="button" class="btn btn-secondary mr-1" onclick="stepper.previous()"><i class="fas fa-arrow-left mr-1"></i> Previous</button>
                                          <button type="button" class="btn btn-primary" onclick="stepper.next()">Next <i class="fas fa-arrow-right ml-1"></i></button>
                                      </div>

                                      <!-- STEP 3: SKILL & DESCRIPTION -->
                                      <div id="skill-part" class="content" role="tabpanel" aria-labelledby="skill-part-trigger">
                                          <div class="form-group">
                                              <label class="text-label">Positions*</label>
                                              <div class="quantity-cart">
                                                  <span class="qty-btn minus">-</span>
                                                  <input type="text" class="qty-input" id="positions" name="positions" value="1" inputmode="numeric" pattern="[0-9]*" required>
                                                  <span class="qty-btn plus">+</span>
                                              </div>
                                          </div>

                                          <div class="form-group">
                                              <label class="font-weight-bold"><i class="fas fa-check-circle text-success mr-1"></i> Must-Have Skills <span class="text-danger">*</span></label>
                                              <div class="position-relative">
                                                  <input type="text" id="mustHaveSkillsInput" class="form-control search-input"
                                                      placeholder="Type mandatory skill..." autocomplete="off">
                                                  <input type="hidden" name="mustHaveSkills" id="mustHaveSkills">
                                                  <div class="dropdown-menu w-100" id="mustHaveSkillsDropdown"></div>
                                              </div>
                                              <div class="chip-container mt-2" id="mustHaveSkillsChips"></div>
                                          </div>

                                          <div class="form-group">
                                              <label class="font-weight-bold"><i class="fas fa-star text-info mr-1"></i> Nice-to-Have Skills</label>
                                              <div class="position-relative">
                                                  <input type="text" id="niceToHaveSkillsInput" class="form-control search-input"
                                                      placeholder="Type optional skill..." autocomplete="off">
                                                  <input type="hidden" name="niceToHaveSkills" id="niceToHaveSkills">
                                                  <div class="dropdown-menu w-100" id="niceToHaveSkillsDropdown"></div>
                                              </div>
                                              <div class="chip-container mt-2" id="niceToHaveSkillsChips"></div>
                                          </div>

                                          <div class="form-group">
                                              <label>Communication Language*</label>
                                              <div class="position-relative">
                                                  <input type="text" id="languageInput" class="form-control search-input"
                                                      placeholder="Type language..." autocomplete="off">
                                                  <input type="hidden" name="comLanguage" id="comLanguage">
                                                  <div class="dropdown-menu w-100" id="languageDropdown"></div>
                                              </div>
                                              <div class="chip-container mt-2" id="languageChips"></div>
                                          </div>

                                          <div class="form-group">
                                              <label>Job Description*</label>
                                              <textarea name="JD" id="JD" class="form-control" rows="4" placeholder="Enter job description" required></textarea>
                                          </div>

                                          <div class="form-group">
                                              <label>Roles & Responsibilities*</label>
                                              <textarea name="RR" id="RR" class="form-control" rows="4" placeholder="Enter roles and responsibilities" required></textarea>
                                          </div>

                                          <button type="button" class="btn btn-secondary mr-1" onclick="stepper.previous()"><i class="fas fa-arrow-left mr-1"></i> Previous</button>
                                          <button type="button" class="btn btn-primary" onclick="stepper.next()">Next <i class="fas fa-arrow-right ml-1"></i></button>
                                      </div>

                                      <!-- STEP 4: CTC -->
                                      <div id="ctc-part" class="content" role="tabpanel" aria-labelledby="ctc-part-trigger">
                                          <div class="form-group">
                                              <label class="font-weight-bold">CTC Approver</label>
                                              <select name="CtcApproverId" id="CtcApproverId" class="form-control">
                                                  <option value="">Select CTC Approver</option>
                                                  <?php if (!empty($ctc_approvers)): ?>
                                                      <?php foreach ($ctc_approvers as $ca): ?>
                                                          <option value="<?= $ca['IUid']; ?>"><?= htmlspecialchars($ca['EmpName']); ?> (<?= htmlspecialchars($ca['RoleName'] ? $ca['RoleName'] : 'Employee'); ?>)</option>
                                                      <?php endforeach; ?>
                                                  <?php endif; ?>
                                              </select>
                                          </div>

                                          <button type="button" class="btn btn-secondary mr-1" onclick="stepper.previous()"><i class="fas fa-arrow-left mr-1"></i> Previous</button>
                                          <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane mr-1"></i> Submit</button>
                                      </div>

                                  </div>
                              </div>
                          </div>
                      </form>
                  </div>
              </div>
          </div>
      </div>
  </div>

<div id="editVacancyPanel" class="right-form">
          <form id="editVacancyForm" action="<?= base_url('admin/updateVacancy') ?>" method="post">

              <input type="hidden" name="jid" id="edit_jid">

              <div class="right-form-header">
                  <h5>
                      Edit Vacancy
                      <small id="editJobCodeText" class="badge badge-pill badge-info ml-2"></small>
                  </h5>
                  <button type="button" class="close-btn" id="closeEditVacancyPanel">&times;</button>
              </div>

              <div class="right-form-body">
                  <div class="bs-stepper">

                  <div class="bs-stepper-header">

                      <div class="step" data-target="#edit-logins-part">
                          <button type="button" class="step-trigger">
                              <span class="bs-stepper-circle">1</span>
                              <span class="bs-stepper-label">JOB INFO</span>
                          </button>
                      </div>

                      <div class="line"></div>

                      <div class="step" data-target="#edit-information-part">
                          <button type="button" class="step-trigger">
                              <span class="bs-stepper-circle">2</span>
                              <span class="bs-stepper-label">SALARY INFO</span>
                          </button>
                      </div>

                      <div class="line"></div>

                      <div class="step" data-target="#edit-skill-part">
                          <button type="button" class="step-trigger">
                              <span class="bs-stepper-circle">3</span>
                              <span class="bs-stepper-label">SKILL INFO</span>
                          </button>
                      </div>

                      <div class="line"></div>

                      <div class="step" data-target="#edit-ctc-part">
                          <button type="button" class="step-trigger">
                              <span class="bs-stepper-circle">4</span>
                              <span class="bs-stepper-label">CTC</span>
                          </button>
                      </div>

                  </div>

                  <div class="bs-stepper-content">

                      <!-- STEP 1 -->
                      <div id="edit-logins-part" class="content">
                          <div class="form-group">
                              <label>Job Title</label>
                              <input type="text" id="edit_jobTitle" class="form-control" readonly>
                          </div>

                          <div class="form-group">
                              <label>Department</label>
                              <input type="text" id="edit_department" class="form-control" readonly>
                          </div>

                          <div class="form-group">
                              <label>Role</label>
                              <input type="text" id="edit_role" class="form-control" readonly>
                          </div>

                          <div class="form-group">
                              <label>Work Mode*</label>
                              <input type="hidden" name="workMode" id="edit_work_mode">

                              <div class="d-flex gap-2">
                                  <span class="edit-work-mode badge badge-pill badge-outline-primary" data-value="Onsite">Onsite</span>
                                  <span class="edit-work-mode badge badge-pill badge-outline-success" data-value="Remote">Remote</span>
                                  <span class="edit-work-mode badge badge-pill badge-outline-info" data-value="Hybrid">Hybrid</span>
                              </div>
                          </div>

                          <div class="form-group">
                              <label>Employment Type*</label>
                              <input type="hidden" name="employmentType" id="edit_employment_type">

                              <div class="d-flex flex-wrap gap-2">
                                  <span class="edit-emp-type badge badge-pill badge-outline-primary" data-value="Full-Time">Full-Time</span>
                                  <span class="edit-emp-type badge badge-pill badge-outline-warning" data-value="Part-Time">Part-Time</span>
                                  <span class="edit-emp-type badge badge-pill badge-outline-secondary" data-value="Contract">Contract</span>
                                  <span class="edit-emp-type badge badge-pill badge-outline-dark" data-value="Internship">Internship</span>
                              </div>
                          </div>


                          <button type="button" class="btn btn-primary" onclick="editStepper.next()">Next</button>
                      </div>

                      <!-- STEP 2 -->
                      <div id="edit-information-part" class="content">
                          <!-- Experience -->
                          <div class="form-group">
                              <div class="row">
                                  <div class="col-md-6">
                                      <label>Min Experience*</label>
                                      <select id="edit_expMin" name="expMin" class="form-control">
                                          <option value="">Min</option>
                                      </select>
                                  </div>

                                  <div class="col-md-6">
                                      <label>Max Experience*</label>

                                      <select id="edit_expMax" name="expMax" class="form-control">
                                          <option value="">Max </option>
                                      </select>
                                  </div>
                              </div>
                          </div>
                          <div class="form-group">
                              <label>Location*</label>

                              <div class="position-relative">
                                  <input type="text"
                                      id="edit_jobLocationInput"
                                      class="form-control"
                                      autocomplete="off">

                                  <div class="dropdown-menu w-100"
                                      id="edit_jobLocationDropdown"></div>
                              </div>

                              <input type="hidden"
                                  name="jobLocation"
                                  id="edit_jobLocation">

                              <div class="chip-container mt-2"
                                  id="edit_jobLocationChips"></div>
                          </div>
                          <div class="form-group">
                              <label>Education*</label>
                              <div class="position-relative">
                                  <input type="text" id="edit_educationInput" class="form-control">
                                  <div class="dropdown-menu w-100" id="edit_educationDropdown"></div>
                              </div>
                              <input type="hidden" name="education" id="edit_education">
                              <div class="chip-container mt-2" id="edit_educationChips"></div>
                          </div>

                          <button type="button" class="btn btn-primary" onclick="editStepper.previous()">Previous</button>
                          <button type="button" class="btn btn-primary" onclick="editStepper.next()">Next <i class="fas fa-arrow-right ml-1"></i></button>
                      </div>

                      <!-- STEP 3 -->
                      <div id="edit-skill-part" class="content">

                          <div class="form-group">
                              <label>Positions*</label>
                              <input type="number" name="positions" id="edit_positions" class="form-control">
                          </div>

                          <div class="form-group">
                              <label class="font-weight-bold"><i class="fas fa-check-circle text-success mr-1"></i> Must-Have Skills <span class="text-danger">*</span></label>
                              <div class="position-relative">
                                  <input type="text" id="edit_mustHaveSkillsInput" class="form-control">
                                  <div class="dropdown-menu w-100" id="edit_mustHaveSkillsDropdown"></div>
                              </div>
                              <input type="hidden" name="mustHaveSkills" id="edit_mustHaveSkills">
                              <div class="chip-container mt-2" id="edit_mustHaveSkillsChips"></div>
                          </div>

                          <div class="form-group">
                              <label class="font-weight-bold"><i class="fas fa-star text-info mr-1"></i> Nice-to-Have Skills</label>
                              <div class="position-relative">
                                  <input type="text" id="edit_niceToHaveSkillsInput" class="form-control">
                                  <div class="dropdown-menu w-100" id="edit_niceToHaveSkillsDropdown"></div>
                              </div>
                              <input type="hidden" name="niceToHaveSkills" id="edit_niceToHaveSkills">
                              <div class="chip-container mt-2" id="edit_niceToHaveSkillsChips"></div>
                          </div>

                          <div class="form-group">
                              <label>Communication Language*</label>
                              <div class="position-relative">
                                  <input type="text" id="edit_languageInput" class="form-control">
                                  <div class="dropdown-menu w-100" id="edit_languageDropdown"></div>
                              </div>
                              <input type="hidden" name="comLanguage" id="edit_comLanguage">
                              <div class="chip-container mt-2" id="edit_languageChips"></div>
                          </div>

                          <div class="form-group">
                              <label>Job Description*</label>
                              <textarea name="JD" id="edit_JD" class="form-control"></textarea>
                          </div>
                          <div class="form-group">
                              <label>Roles & Responsibilities*</label>
                              <textarea name="RR" id="edit_RR" class="form-control"></textarea>
                          </div>

                          <button type="button" class="btn btn-secondary mr-1" onclick="editStepper.previous()"><i class="fas fa-arrow-left mr-1"></i> Previous</button>
                          <button type="button" class="btn btn-primary" onclick="editStepper.next()">Next <i class="fas fa-arrow-right ml-1"></i></button>

                      </div>

                      <!-- STEP 4: CTC -->
                       <div id="edit-ctc-part" class="content">

                              <!-- Salary / CTC (LPA) -->
                              <div class="form-group">
                                  <label class="font-weight-bold"><i class="fas fa-money-bill-wave text-success mr-1"></i> Salary / CTC (LPA)</label>
                                  <input type="text" name="salary" id="edit_salary" class="form-control" placeholder="e.g. 5 - 10 LPA">
                              </div>

                              <!-- CTC Approver (editable dropdown) -->
                              <div class="form-group">
                                  <label class="font-weight-bold"><i class="fas fa-user-check text-primary mr-1"></i> CTC Approver</label>
                                  <select name="CtcApproverId" id="edit_CtcApproverId" class="form-control">
                                      <option value="">Select CTC Approver</option>
                                      <?php if (!empty($ctc_approvers)): ?>
                                          <?php foreach ($ctc_approvers as $ca): ?>
                                              <option value="<?= $ca['IUid']; ?>"><?= htmlspecialchars($ca['EmpName']); ?> (<?= htmlspecialchars($ca['RoleName'] ? $ca['RoleName'] : 'Employee'); ?>)</option>
                                          <?php endforeach; ?>
                                      <?php endif; ?>
                                  </select>
                              </div>

                              <!-- Interviewer Panel (editable levels) -->
                              <div class="form-group border-top pt-3 mt-3">
                                  <div class="d-flex align-items-center justify-content-between mb-2">
                                      <label class="font-weight-bold text-primary mb-0">
                                          <i class="fas fa-users-cog mr-1"></i> Interview Panel Levels
                                      </label>
                                      <button type="button" class="btn btn-xs btn-outline-success font-weight-bold" id="addEditInterviewLevelBtn">
                                          <i class="fas fa-plus mr-1"></i> Add Level
                                      </button>
                                  </div>
                                  <small class="form-text text-muted mb-3">Level 1 & Level 2 are mandatory. Up to 4 levels maximum.</small>

                                  <div id="editInterviewPanelContainer">
                                      <div class="form-group mb-2" data-level="1">
                                          <label class="font-weight-bold">Level 1 Interviewer <span class="text-danger">*</span></label>
                                          <select name="interviewPanel[1]" id="edit_interviewPanel_1" class="form-control interview-panel-select">
                                              <option value="">Select Level 1 Interviewer</option>
                                              <?php if (!empty($ctc_approvers)): ?>
                                                  <?php foreach ($ctc_approvers as $u): ?>
                                                      <option value="<?= $u['IUid']; ?>"><?= htmlspecialchars($u['EmpName']); ?><?= !empty($u['RoleName']) ? ' (' . htmlspecialchars($u['RoleName']) . ')' : ''; ?></option>
                                                  <?php endforeach; ?>
                                              <?php endif; ?>
                                          </select>
                                      </div>

                                      <div class="form-group mb-2" data-level="2">
                                          <label class="font-weight-bold">Level 2 Interviewer <span class="text-danger">*</span></label>
                                          <select name="interviewPanel[2]" id="edit_interviewPanel_2" class="form-control interview-panel-select">
                                              <option value="">Select Level 2 Interviewer</option>
                                              <?php if (!empty($ctc_approvers)): ?>
                                                  <?php foreach ($ctc_approvers as $u): ?>
                                                      <option value="<?= $u['IUid']; ?>"><?= htmlspecialchars($u['EmpName']); ?><?= !empty($u['RoleName']) ? ' (' . htmlspecialchars($u['RoleName']) . ')' : ''; ?></option>
                                                  <?php endforeach; ?>
                                              <?php endif; ?>
                                          </select>
                                      </div>

                                      <div id="editDynamicLevelsContainer"></div>
                                  </div>
                              </div>

                          <button type="button" class="btn btn-secondary mr-1" onclick="editStepper.previous()"><i class="fas fa-arrow-left mr-1"></i> Previous</button>
                          <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Update</button>

                      </div>

                  </div>
              </div>
              </div>

          </form>
      </div>
      <!-- edit end -->



  </div>
  <!-- view modal -->
  <div class="modal fade" id="vacancyDetailsModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">

              <div class="modal-header">
                  <h5 class="modal-title">Vacancy Details</h5>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>

              <div class="modal-body" id="vacancyDetailsBody">
                  <div class="text-center">
                      <i class="fa fa-spinner fa-spin"></i> Loading...
                  </div>
              </div>

          </div>
      </div>
  </div>
  <!-- Overlay -->
  <div id="vacancyOverlay"></div>


  <!-- modal for alerts -->
   <!-- Job Status Confirm Modal -->
<div class="modal fade" id="jobStatusModal" tabindex="-1">
 <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-warning">
        <h5 class="modal-title">Confirm Action</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body text-center">
        <p id="jobStatusMessage"></p>
      </div>

      <div class="modal-footer justify-content-center">
        <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button class="btn btn-danger" id="confirmJobStatus">Yes Continue</button>
      </div>

    </div>
  </div>
</div>

<!-- Hold Date Modal -->
<div class="modal fade" id="holdDateModal" tabindex="-1" role="dialog" aria-labelledby="holdDateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content modal-content-rounded-lg">

      <div class="modal-header modal-header-gradient-amber">
        <h5 class="modal-title text-white font-weight-bold" id="holdDateModalLabel">
          <i class="fas fa-pause-circle mr-2"></i>Put Job On Hold
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <div class="icon-circle-amber">
            <i class="fas fa-calendar-alt text-white fa-lg"></i>
          </div>
        </div>
        <p class="text-center text-muted mb-3">Select the date until which this job should be held. A reminder email will be sent to the <strong>Recruiter Manager</strong> and the <strong>Assigned Recruiter</strong> 3 days before the hold date.</p>

        <div class="form-group">
          <label class="font-weight-bold"><i class="fas fa-calendar-check text-warning mr-1"></i>Hold Until Date <span class="text-danger">*</span></label>
          <input type="date" id="holdUntilDateInput" class="form-control form-control-lg border-warning rounded" required>
          <small class="text-muted">Choose a future date for the hold period.</small>
        </div>
      </div>

      <div class="modal-footer justify-content-center border-top">
        <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-warning px-4 font-weight-bold" id="confirmHoldDate">
          <i class="fas fa-pause-circle mr-1"></i>Confirm Hold
        </button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="jobExistsModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-danger">
        <h5 class="modal-title">Duplicate Job</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body text-center">
        <p>This Job Title already exists.</p>
      </div>

      <div class="modal-footer justify-content-center">
        <button class="btn btn-primary" data-dismiss="modal">OK</button>
      </div>

    </div>
  </div>
</div>
  <script>
      document.addEventListener('DOMContentLoaded', function() {
          const createStepper = document.querySelector('#vacancyPanel .bs-stepper');
          if (createStepper) {
              window.stepper = new Stepper(createStepper);
          }

          const editStepperEl = document.querySelector('#editVacancyPanel .bs-stepper');
          if (editStepperEl) {
              window.editStepper = new Stepper(editStepperEl);
          }
      });

      document.querySelectorAll('.emp-type').forEach(el => {
          el.addEventListener('click', function() {
              document.querySelectorAll('.emp-type').forEach(b => b.classList.remove('active'));
              this.classList.add('active');
              document.getElementById('employment_type').value = this.dataset.value;
          });
      });

      document.querySelectorAll('.work-mode').forEach(el => {
          el.addEventListener('click', function() {
              document.querySelectorAll('.work-mode').forEach(b => b.classList.remove('active'));
              this.classList.add('active');
              document.getElementById('work_mode').value = this.dataset.value;
          });
      });

      document.addEventListener('DOMContentLoaded', function() {

          const expMin = document.getElementById('expMin');
          const expMax = document.getElementById('expMax');

          for (let i = 0; i <= 20; i++) {
              expMin.add(new Option(i + ' Year' + (i > 1 ? 's' : ''), i));
          }
          expMin.add(new Option('20+ Years', '20+'));

          expMin.addEventListener('change', function() {
              expMax.innerHTML = '<option value="">Max</option>';
              if (this.value === '20+') {
                  expMax.add(new Option('30+ Years', '30+'));
                  return;
              }
              const min = parseInt(this.value);
              for (let i = min; i <= 30; i++) {
                  expMax.add(new Option(i + ' Year' + (i > 1 ? 's' : ''), i));
              }
              expMax.add(new Option('30+ Years', '30+'));
          });

          const salaryMin = document.getElementById('salaryMin');
          const salaryMax = document.getElementById('salaryMax');

          for (let i = 1; i <= 50; i++) {
              salaryMin.add(new Option(i + ' LPA', i));
          }
          salaryMin.add(new Option('50+ LPA', '50+'));

          salaryMin.addEventListener('change', function() {
              salaryMax.innerHTML = '<option value="">Max Salary</option>';
              if (this.value === '50+') {
                  salaryMax.add(new Option('100+ LPA', '100+'));
                  return;
              }
              const min = parseInt(this.value);
              for (let i = min; i <= 80; i++) {
                  salaryMax.add(new Option(i + ' LPA', i));
              }
              salaryMax.add(new Option('100+ LPA', '100+'));
          });

      });

      function populateEditExpMin() {
          const el = document.getElementById('edit_expMin');
          el.innerHTML = '<option value="">Min</option>';
          for (let i = 0; i <= 20; i++) {
              el.add(new Option(i + ' Year' + (i > 1 ? 's' : ''), i));
          }
          el.add(new Option('20+ Years', '20+'));
      }

       function populateEditSalMin() {
           // Text input field used instead of select dropdown
       }


      $('#edit_expMin').on('change', function() {

          const min = parseInt(this.value);
          const maxEl = document.getElementById('edit_expMax');

          maxEl.innerHTML = '<option value="">Max</option>';

          if (this.value === '20+') {
              maxEl.add(new Option('30+ Years', '30+'));
              return;
          }

          for (let i = min + 1; i <= 30; i++) {
              maxEl.add(new Option(i + ' Year' + (i > 1 ? 's' : ''), i));
          }

          maxEl.add(new Option('30+ Years', '30+'));
      });
      // const minusBtn = document.querySelector('.minus');
      // const plusBtn  = document.querySelector('.plus');
      // const qtyInput = document.getElementById('positions');

      // function getValue() { return parseInt(qtyInput.value) || 1; }
      // function setValue(val) { qtyInput.value = val < 1 ? 1 : val; }

      // plusBtn.addEventListener('click',  () => setValue(getValue() + 1));
      // minusBtn.addEventListener('click', () => setValue(getValue() - 1));
      // qtyInput.addEventListener('blur',  () => setValue(getValue()));
      // qtyInput.addEventListener('input', () => { qtyInput.value = qtyInput.value.replace(/[^0-9]/g, ''); });

      $(document).ready(function() {
          $('#openVacancyPanel').on('click', function() {
              $('#vacancyPanel').addClass('open');
              $('#vacancyOverlay').addClass('show');
          });
          $('#closeVacancyPanel').on('click', function() {
              $('#vacancyPanel').removeClass('open');
              $('#vacancyOverlay').removeClass('show');
          });
          $('#closeEditVacancyPanel').on('click', function() {
              $('#editVacancyPanel').removeClass('open');
              $('#vacancyOverlay').removeClass('show');
          });
          $('#vacancyOverlay').on('click', function() {
              $('#vacancyPanel, #editVacancyPanel').removeClass('open');
              $('#vacancyOverlay').removeClass('show');
          });
      });

      initChipAutocomplete({
          inputId: 'jobLocationInput',
          dropdownId: 'jobLocationDropdown',
          chipsId: 'jobLocationChips',
          hiddenId: 'jobLocation',
          url: '<?= base_url("admin/searchLocation") ?>',
          key: 'JobLocation'
      });
      initChipAutocomplete({
          inputId: 'educationInput',
          dropdownId: 'educationDropdown',
          chipsId: 'educationChips',
          hiddenId: 'education',
          url: '<?= base_url("admin/searchEducation") ?>',
          key: 'EducationRequired'
      });
      initChipAutocomplete({
          inputId: 'mustHaveSkillsInput',
          dropdownId: 'mustHaveSkillsDropdown',
          chipsId: 'mustHaveSkillsChips',
          hiddenId: 'mustHaveSkills',
          url: '<?= base_url("admin/searchSkills") ?>',
          key: 'SkillName'
      });
      initChipAutocomplete({
          inputId: 'niceToHaveSkillsInput',
          dropdownId: 'niceToHaveSkillsDropdown',
          chipsId: 'niceToHaveSkillsChips',
          hiddenId: 'niceToHaveSkills',
          url: '<?= base_url("admin/searchSkills") ?>',
          key: 'SkillName'
      });
      initChipAutocomplete({
          inputId: 'languageInput',
          dropdownId: 'languageDropdown',
          chipsId: 'languageChips',
          hiddenId: 'comLanguage',
          url: '<?= base_url("admin/searchLanguage") ?>',
          key: 'CommunicationLang'
      });

      initChipAutocomplete({
          inputId: 'edit_jobLocationInput',
          dropdownId: 'edit_jobLocationDropdown',
          chipsId: 'edit_jobLocationChips',
          hiddenId: 'edit_jobLocation',
          url: '<?= base_url("admin/searchLocation") ?>',
          key: 'JobLocation'
      });
      initChipAutocomplete({
          inputId: 'edit_educationInput',
          dropdownId: 'edit_educationDropdown',
          chipsId: 'edit_educationChips',
          hiddenId: 'edit_education',
          url: '<?= base_url("admin/searchEducation") ?>',
          key: 'EducationRequired'
      });
      initChipAutocomplete({
          inputId: 'edit_mustHaveSkillsInput',
          dropdownId: 'edit_mustHaveSkillsDropdown',
          chipsId: 'edit_mustHaveSkillsChips',
          hiddenId: 'edit_mustHaveSkills',
          url: '<?= base_url("admin/searchSkills") ?>',
          key: 'SkillName'
      });
      initChipAutocomplete({
          inputId: 'edit_niceToHaveSkillsInput',
          dropdownId: 'edit_niceToHaveSkillsDropdown',
          chipsId: 'edit_niceToHaveSkillsChips',
          hiddenId: 'edit_niceToHaveSkills',
          url: '<?= base_url("admin/searchSkills") ?>',
          key: 'SkillName'
      });
      initChipAutocomplete({
          inputId: 'edit_languageInput',
          dropdownId: 'edit_languageDropdown',
          chipsId: 'edit_languageChips',
          hiddenId: 'edit_comLanguage',
          url: '<?= base_url("admin/searchLanguage") ?>',
          key: 'CommunicationLang'
      });

      document.querySelectorAll('.edit-emp-type').forEach(el => {
          el.addEventListener('click', function() {
              document.querySelectorAll('.edit-emp-type').forEach(b => b.classList.remove('active'));
              this.classList.add('active');
              document.getElementById('edit_employment_type').value = this.dataset.value;
          });
      });

      document.querySelectorAll('.edit-work-mode').forEach(el => {
          el.addEventListener('click', function() {
              document.querySelectorAll('.edit-work-mode').forEach(b => b.classList.remove('active'));
              this.classList.add('active');
              document.getElementById('edit_work_mode').value = this.dataset.value;
          });
      });

      $(document).on('click', '.editJobBtn', function() {

          const jid = $(this).data('id');
          $('#edit_jid').val(jid);

          $.post('<?= base_url("admin/getJobDetails") ?>', {
              jid: jid
          }, function(res) {

              const d = JSON.parse(res);
              // $('#editJobCodeText').text('(' + d.JobCode + ')');
              $('#editJobCodeText').text(d.JobCode);

              $('#edit_jobCode').val(d.JobCode ?? '');
              $('#edit_jobTitle').val(d.JobTitle ?? '');

              $('#edit_department').val(d.Departmentname ?? '');
              $('#edit_role').val(d.RoleSummary ?? '');

              $('#edit_positions').val(d.NoofOpenings ?? '');
              $('#edit_JD').val(d.JobDescription ?? '');
              $('#edit_RR').val(d.Responsibilities ?? '');

              $('#edit_salaryMin').val(d.SalMin ?? '');
              $('#edit_salaryMax').val(d.SalMax ?? '');

              populateEditExpMin();
              const cleanMin = (d.ExpMin !== null && d.ExpMin !== undefined) ? (parseFloat(d.ExpMin) % 1 === 0 ? parseInt(d.ExpMin) : parseFloat(d.ExpMin)) : '';
              const cleanMax = (d.ExpMax !== null && d.ExpMax !== undefined) ? (parseFloat(d.ExpMax) % 1 === 0 ? parseInt(d.ExpMax) : parseFloat(d.ExpMax)) : '';

              $('#edit_expMin').val(cleanMin);

              const expMinVal = parseInt(d.ExpMin) || 0;
              const expMaxEl = document.getElementById('edit_expMax');
              if (expMaxEl) {
                  expMaxEl.innerHTML = '<option value="">Max</option>';
                  for (let i = expMinVal; i <= 30; i++) {
                      expMaxEl.add(new Option(i + ' Year' + (i > 1 ? 's' : ''), i));
                  }
                  expMaxEl.add(new Option('30+ Years', '30+'));
                  $('#edit_expMax').val(cleanMax);
              }

              console.log('SalMin:', d.SalMin, '| SalMax:', $('#edit_salaryMax').val());
              console.log('ExpMin:', d.ExpMin, '| ExpMax:', $('#edit_expMax').val());

              const workMode = (d.WorkMode || '').toString().trim();
              const empType = (d.EmploymentType || '').toString().trim();

              $('.edit-work-mode').removeClass('active');
              $('.edit-emp-type').removeClass('active');

              $('.edit-work-mode').each(function() {
                  if ($(this).data('value').trim() === workMode) {
                      $(this).addClass('active');
                      $('#edit_work_mode').val(workMode);
                  }
              });

              $('.edit-emp-type').each(function() {
                  if ($(this).data('value').trim() === empType) {
                      $(this).addClass('active');
                      $('#edit_employment_type').val(empType);
                  }
              });

              preloadChips(d.JobLocation, 'edit_jobLocationChips', 'edit_jobLocation');
              preloadChips(d.EducationRequired, 'edit_educationChips', 'edit_education');
              preloadChips(d.MustHaveSkills || d.Skills, 'edit_mustHaveSkillsChips', 'edit_mustHaveSkills');
              preloadChips(d.NiceToHaveSkills, 'edit_niceToHaveSkillsChips', 'edit_niceToHaveSkills');
              preloadChips(d.CommunicationLang, 'edit_languageChips', 'edit_comLanguage');

              // Populate Salary & CTC Approver
              $('#edit_salary').val(d.Salary ?? '');
              $('#edit_CtcApproverId').val(d.CtcApproverId || d.EffectiveCtcApproverId || '');

              // Reset & Populate Interview Panel dropdowns
              $('#editDynamicLevelsContainer').empty();
              $('#edit_interviewPanel_1').val('');
              $('#edit_interviewPanel_2').val('');
              updateEditAddLevelBtnState();

              const panels = d.interviewPanels || [];
              if (Array.isArray(panels) && panels.length > 0) {
                  panels.forEach(function(p) {
                      var lvl = parseInt(p.LevelOrder);
                      var uid = p.InterviewerId;
                      if (lvl === 1) {
                          $('#edit_interviewPanel_1').val(uid);
                      } else if (lvl === 2) {
                          $('#edit_interviewPanel_2').val(uid);
                      } else if (lvl === 3 || lvl === 4) {
                          addEditDynamicLevel(lvl, uid);
                      }
                  });
              }

              $('#editVacancyPanel').addClass('open');
              $('#vacancyOverlay').addClass('show');

          });
      });

var editUsersOptionsHtml = `<option value="">Select Interviewer</option><?php 
if (!empty($ctc_approvers)) {
    foreach ($ctc_approvers as $u) {
        echo '<option value="' . $u['IUid'] . '">' . htmlspecialchars($u['EmpName']) . (!empty($u['RoleName']) ? ' (' . htmlspecialchars($u['RoleName']) . ')' : '') . '</option>';
    }
}
?>`;

function getEditCurrentLevelCount() {
    return $('#editInterviewPanelContainer .form-group[data-level]').length;
}

function updateEditAddLevelBtnState() {
    if (getEditCurrentLevelCount() >= 4) {
        $('#addEditInterviewLevelBtn').hide();
    } else {
        $('#addEditInterviewLevelBtn').show();
    }
}

function addEditDynamicLevel(levelNum, selectedVal) {
    if ($('#edit-dynamic-level-' + levelNum).length) return;
    var html = `
        <div class="form-group mb-2 dynamic-level-row" id="edit-dynamic-level-${levelNum}" data-level="${levelNum}">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <label class="font-weight-bold mb-0">Level ${levelNum} Interviewer</label>
                <button type="button" class="btn btn-xs btn-outline-danger remove-edit-level-btn" data-target="edit-dynamic-level-${levelNum}">
                    <i class="fas fa-minus mr-1"></i> Remove
                </button>
            </div>
            <select name="interviewPanel[${levelNum}]" id="edit_interviewPanel_${levelNum}" class="form-control interview-panel-select">
                ${editUsersOptionsHtml}
            </select>
        </div>
    `;
    $('#editDynamicLevelsContainer').append(html);
    if (selectedVal) {
        $('#edit_interviewPanel_' + levelNum).val(selectedVal);
    }
    updateEditAddLevelBtnState();
}

$(document).on('click', '#addEditInterviewLevelBtn', function() {
    var count = getEditCurrentLevelCount();
    if (count < 4) {
        var nextLevel = count + 1;
        addEditDynamicLevel(nextLevel);
    }
});

$(document).on('click', '.remove-edit-level-btn', function() {
    var targetId = $(this).data('target');
    $('#' + targetId).remove();
    updateEditAddLevelBtnState();
});

   let selectedJobId = '';
let selectedStatus = '';

$(document).on('click', '.jobStatusBtn', function () {

    selectedJobId = $(this).data('id');
    selectedStatus = $(this).data('status');

    // If putting on hold, show dedicated date picker modal
    if (selectedStatus === 'On-Hold') {
        // Set minimum date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minDate = tomorrow.toISOString().split('T')[0];
        $('#holdUntilDateInput').attr('min', minDate).val('');
        $('#holdDateModal').modal('show');
        return;
    }

    let message = '';
    if (selectedStatus === 'Closed' || selectedStatus === 'Dropped') {
        message = "Are you sure you want to drop this job?";
    } else if (selectedStatus === 'Open') {
        message = "Are you sure you want to reopen this job?";
    } else if (selectedStatus === 'Re-Open') {
        message = "Are you sure you want to reopen this job?";
    } else {
        message = "Are you sure you want to change this job status?";
    }

    $('#jobStatusMessage').text(message);
    $('#jobStatusModal').modal('show');

});

// Confirm Hold Date
$('#confirmHoldDate').on('click', function () {
    const holdDate = $('#holdUntilDateInput').val();
    if (!holdDate) {
        toastr.warning('Please select a hold-until date.');
        return;
    }

    const today = new Date().toISOString().split('T')[0];
    if (holdDate <= today) {
        toastr.warning('Hold date must be a future date.');
        return;
    }

    $('#confirmHoldDate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...');

    $.ajax({
        url: '<?= base_url("admin/updateJobStatus") ?>',
        type: 'POST',
        dataType: 'json',
        data: {
            jid: selectedJobId,
            status: 'On-Hold',
            holdUntilDate: holdDate
        },
        success: function(res) {
            $('#holdDateModal').modal('hide');
            $('#confirmHoldDate').prop('disabled', false).html('<i class="fas fa-pause-circle mr-1"></i>Confirm Hold');
            if (res.status === 'success') {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1200);
            } else {
                toastr.error(res.message || 'Something went wrong');
            }
        },
        error: function() {
            $('#confirmHoldDate').prop('disabled', false).html('<i class="fas fa-pause-circle mr-1"></i>Confirm Hold');
            toastr.error('Server error occurred');
        }
    });
});

      $('#confirmJobStatus').click(function () {

    $.ajax({
        url: '<?= base_url("admin/updateJobStatus") ?>',
        type: 'POST',
        dataType: 'json',
        data: {
            jid: selectedJobId,
            status: selectedStatus
        },
        success: function(res) {

            $('#jobStatusModal').modal('hide');

            if (res.status === 'success') {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1200);
            } else {
                toastr.error(res.message || 'Something went wrong');
            }

        },
        error: function() {
            toastr.error('Server error occurred');
        }
    });

});
      $('#editVacancyForm').submit(function(e) {
          e.preventDefault();
          $.ajax({
              url: '<?= base_url("admin/updateVacancy") ?>',
              type: 'POST',
              data: $(this).serialize(),
              dataType: 'json',
              success: function(res) {
                  if (res.status == 'success') {
                      toastr.success('Vacancy updated');
                      location.reload();
                  } else {
                      toastr.error('Update failed');
                  }
              }
          });
      });

      $(document).on('click', '.viewVacancyBtn', function() {
          let jid = $(this).data('id');
          $('#vacancyDetailsModal').modal('show');
          $('#vacancyDetailsBody').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
          $.post('<?= base_url("admin/getJobDetails") ?>', {
              jid: jid
          }, function(res) {
              let d = JSON.parse(res);
              let html = `<div class="container-fluid">`;
              html += `<div class="card card-primary"><div class="card-header bg-primary"><h3 class="card-title">Basic Information</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div></div><div class="card-body"><div class="row"><div class="col-md-6"><p><b>Job Code:</b> ${d.JobCode}</p><p><b>Job Title:</b> ${d.JobTitle}</p><p><b>Department:</b> ${d.Departmentname}</p><p><b>Role:</b> ${d.RoleSummary}</p><p><b>Status:</b>
<span class="badge badge-pill
${d.JobStatus === 'Open' || d.JobStatus === 'Re-Open' ? 'badge-success' :
  (d.JobStatus === 'Closed' || d.JobStatus === 'Dropped') ? 'badge-danger' :
  d.JobStatus === 'On-Hold' ? 'badge-warning' :
  d.JobStatus === 'Draft' ? 'badge-secondary' :
  d.JobStatus === 'Not Required' ? 'badge-dark' :
  'badge-primary'}">
${(d.JobStatus === 'Closed' || d.JobStatus === 'Dropped') ? 'Dropped' : d.JobStatus}
</span>
</p></div><div class="col-md-6"><p><b>Posted By:</b> ${d.PostedByName}</p><p><b>Posted On:</b> ${d.PostedOn}</p><p><b>Work Mode:</b> ${d.WorkMode}</p><p><b>Employment:</b> ${d.EmploymentType}</p><p><b>Language:</b> ${d.CommunicationLang}</p></div></div></div></div>`;
              let salaryDisplay = (d.Salary && d.Salary.trim() !== '' && d.Salary !== '0 - 0 LPA') ? d.Salary : ((d.SalMin || d.SalMax) ? (d.SalMin + ' - ' + d.SalMax + ' LPA') : '-');
              let expMinDisplay = (d.ExpMin !== undefined && d.ExpMin !== null && d.ExpMin !== '') ? (parseFloat(d.ExpMin) % 1 === 0 ? parseInt(d.ExpMin) : parseFloat(d.ExpMin)) : 0;
              let expMaxDisplay = (d.ExpMax !== undefined && d.ExpMax !== null && d.ExpMax !== '') ? (parseFloat(d.ExpMax) % 1 === 0 ? parseInt(d.ExpMax) : parseFloat(d.ExpMax)) : 0;
              let expDisplay = `${expMinDisplay} - ${expMaxDisplay} Years`;

              html += `<div class="card card-info collapsed-card"><div class="card-header bg-info"><h3 class="card-title">Salary & Experience</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><div class="row"><div class="col-md-6"><p><b>Experience Required:</b> ${expDisplay}</p></div><div class="col-md-6"><p><b>Salary:</b> ${salaryDisplay}</p></div></div></div></div>`;
              html += `<div class="card card-secondary collapsed-card"><div class="card-header bg-secondary"><h3 class="card-title">Location & Education</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><p><b>Job Location:</b> ${d.JobLocation}</p><p><b>Education Required:</b> ${d.EducationRequired}</p></div></div>`;
              html += `<div class="card card-warning collapsed-card"><div class="card-header bg-warning"><h3 class="card-title">Skills</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><p><b>Must-Have Skills:</b> ${d.MustHaveSkills || d.Skills || '-'}</p><p><b>Nice-to-Have Skills:</b> ${d.NiceToHaveSkills || '-'}</p></div></div>`;
              html += `<div class="card card-dark collapsed-card"><div class="card-header bg-dark"><h3 class="card-title">Roles & Responsibilities</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body">${d.Responsibilities}</div></div>`;
              html += `<div class="card card-success collapsed-card"><div class="card-header bg-success"><h3 class="card-title">Job Description</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body">${d.JobDescription}</div></div>`;
              
              html += `</div>`;
              $('#vacancyDetailsBody').html(html);
          });
      });

      function preloadChips(values, chipsId, hiddenId) {
          if (!values) return;
          const arr = values.split(',');
          const chips = document.getElementById(chipsId);
          const hidden = hiddenId ? document.getElementById(hiddenId) : null;
          chips.innerHTML = '';
          arr.forEach(v => {
              v = v.trim();
              if (chipsId === 'edit_mustHaveSkillsChips') {
                  $('<input>').attr('type', 'hidden').attr('name', 'mustHaveSkills[]').val(v).appendTo('#editVacancyForm');
              }
              if (chipsId === 'edit_niceToHaveSkillsChips') {
                  $('<input>').attr('type', 'hidden').attr('name', 'niceToHaveSkills[]').val(v).appendTo('#editVacancyForm');
              }
              const chip = document.createElement('span');
              chip.className = chipsId.includes('mustHave') ? 'badge badge-pill badge-success mr-2 mb-2' : (chipsId.includes('niceToHave') ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
              chip.innerHTML = `${v} <span class="cursor-pointer">×</span>`;
              chip.querySelector('span').onclick = () => {
                  chip.remove();
                  if (chipsId === 'edit_mustHaveSkillsChips') {
                      $('#editVacancyForm input[name="mustHaveSkills[]"][value="' + v + '"]').remove();
                  }
                  if (chipsId === 'edit_niceToHaveSkillsChips') {
                      $('#editVacancyForm input[name="niceToHaveSkills[]"][value="' + v + '"]').remove();
                  }
                  if (hidden) {
                      hidden.value = [...chips.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
                  }
              };
              chips.appendChild(chip);
          });
          if (hidden) {
              hidden.value = arr.join(',');
          }
      }

      function initChipAutocomplete(config) {
          const input = document.getElementById(config.inputId);
          const dropdown = document.getElementById(config.dropdownId);
          const chipsContainer = document.getElementById(config.chipsId);
          const hiddenInput = config.hiddenId ? document.getElementById(config.hiddenId) : null;

          function syncHidden() {
              if (!hiddenInput) return;
              hiddenInput.value = [...chipsContainer.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
          }

          input.addEventListener('keyup', function() {
              const q = this.value.trim();
              if (q.length < 3) {
                  dropdown.style.display = 'none';
                  dropdown.innerHTML = '';
                  return;
              }
              fetch(`${config.url}?q=${encodeURIComponent(q)}`)
                  .then(res => res.json())
                  .then(data => {
                      dropdown.innerHTML = '';
                      if (!data.length) {
                          dropdown.innerHTML = '<span class="dropdown-item disabled">No results</span>';
                      } else {
                          data.forEach(item => {
                              const value = item[config.key];
                              const el = document.createElement('a');
                              el.className = 'dropdown-item';
                              el.textContent = value;
                              el.onclick = () => addChip(value);
                              dropdown.appendChild(el);
                          });
                      }
                      dropdown.style.display = 'block';
                  });
          });

          input.addEventListener('keydown', function(e) {
              if (e.key === 'Enter') {
                  e.preventDefault();
                  const value = input.value.trim();
                  if (value.length >= 2) addChip(value);
              }
          });


          function addChip(value) {
              if (!value) return;

              if (config.inputId === 'edit_mustHaveSkillsInput') {
                  if ($('input[name="mustHaveSkills[]"][value="' + value + '"]').length) return;
                  $('<input>').attr('type', 'hidden').attr('name', 'mustHaveSkills[]').val(value)
                      .appendTo(input.closest('form'));
              }

              if (config.inputId === 'edit_niceToHaveSkillsInput') {
                  if ($('input[name="niceToHaveSkills[]"][value="' + value + '"]').length) return;
                  $('<input>').attr('type', 'hidden').attr('name', 'niceToHaveSkills[]').val(value)
                      .appendTo(input.closest('form'));
              }

              if (config.inputId === 'mustHaveSkillsInput' || config.inputId === 'niceToHaveSkillsInput') {
                  const existing = [...chipsContainer.querySelectorAll('.badge')]
                      .map(x => x.textContent.replace('×', '').trim());
                  if (existing.includes(value)) return;
              }
              const chip = document.createElement('span');
              chip.className = config.inputId.includes('mustHave') ? 'badge badge-pill badge-success mr-2 mb-2' : (config.inputId.includes('niceToHave') ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
              chip.innerHTML = `${value} <span class="cursor-pointer">×</span>`;

              chip.querySelector('span').onclick = () => {
                  chip.remove();
                  if (config.inputId === 'edit_mustHaveSkillsInput') {
                      $('input[name="mustHaveSkills[]"][value="' + value + '"]').remove();
                  }
                  if (config.inputId === 'edit_niceToHaveSkillsInput') {
                      $('input[name="niceToHaveSkills[]"][value="' + value + '"]').remove();
                  }
                  syncHidden();
              };
              chipsContainer.appendChild(chip);
              input.value = '';
              dropdown.style.display = 'none';
              syncHidden();
          }

          document.addEventListener('click', e => {
              if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                  dropdown.style.display = 'none';
              }
          });
      }
      // $(document).ready(function () {

      //   var table = $('#example1').DataTable();


      //     // Department filter
      //     $('#filterDepartment').on('change', function () {
      //         table.column(3).search(this.value).draw();
      //     });

      //     // Status filter
      //     $('#filterStatus').on('change', function () {
      //         table.column(8).search(this.value).draw();
      //     });

      //     var startDate = '';
      //     var endDate = '';

      //     $('#dateRange').daterangepicker({
      //         autoUpdateInput: false,
      //         locale: { cancelLabel: 'Clear' }
      //     });

      //     $('#dateRange').on('apply.daterangepicker', function (ev, picker) {
      //         startDate = picker.startDate.format('YYYY-MM-DD');
      //         endDate = picker.endDate.format('YYYY-MM-DD');
      //         $(this).val(startDate + ' - ' + endDate);
      //         table.draw();
      //     });

      //     $('#dateRange').on('cancel.daterangepicker', function () {
      //         $(this).val('');
      //         startDate = '';
      //         endDate = '';
      //         table.draw();
      //     });

      //     $.fn.dataTable.ext.search.push(function (settings, data) {

      //         if (!startDate || !endDate) return true;

      //         var postedDate = data[9]; // Posted On column
      //         if (!postedDate) return false;

      //         var posted = moment(postedDate);

      //         return posted.isBetween(startDate, endDate, null, '[]');
      //     });

      //     $('#resetFilters').on('click', function () {

      //         $('#filterDepartment').val('');
      //         $('#filterStatus').val('');
      //         $('#dateRange').val('');

      //         startDate = '';
      //         endDate = '';

      //         table.search('').columns().search('').draw();
      //     });

      // });
      $(function() {

          $('#dateRange').daterangepicker({
              autoUpdateInput: false,
              locale: {
                  format: 'YYYY-MM-DD',
                  cancelLabel: 'Clear'
              }
          });

          $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
              $(this).val(
                  picker.startDate.format('YYYY-MM-DD') +
                  ' - ' +
                  picker.endDate.format('YYYY-MM-DD')
              );
          });

          $('#dateRange').on('cancel.daterangepicker', function() {
              $(this).val('');
          });

      });

      $(document).ready(function() {

          // Auto submit when department changes
        //   $('select[name="department"]').on('change', function() {
        //       $(this).closest('form').submit();
        //   });
        $('form[action="<?= base_url('admin/vacancies') ?>"] select[name="department"]').on('change', function() {
    $(this).closest('form').submit();
});

          // Auto submit when status changes
          $('select[name="status"]').on('change', function() {
              $(this).closest('form').submit();
          });

          // Auto submit when date selected
          $('#dateRange').on('apply.daterangepicker', function() {
              $(this).closest('form').submit();
          });

      });
      $(document).on('click', '#vacancyDetailsModal [data-card-widget="collapse"]', function() {

          let currentCard = $(this).closest('.card');

          if (currentCard.hasClass('collapsed-card')) {

              $('#vacancyDetailsModal .card').not(currentCard).each(function() {
                  if (!$(this).hasClass('collapsed-card')) {
                      $(this).CardWidget('collapse');
                  }
              });

          }

      });
      <?php if($this->session->flashdata('job_exists')){ ?>


$(document).ready(function(){
    $('#jobExistsModal').modal('show');
});


<?php } ?>
const minusBtn = document.querySelector('.minus');
const plusBtn  = document.querySelector('.plus');
const qtyInput = document.getElementById('positions');

function getValue() {
    return parseInt(qtyInput.value) || 1;
}

function setValue(val) {
    qtyInput.value = val < 1 ? 1 : val;
}

if (plusBtn && minusBtn) {
    plusBtn.addEventListener('click', () => setValue(getValue() + 1));
    minusBtn.addEventListener('click', () => setValue(getValue() - 1));
}

qtyInput.addEventListener('input', () => {
    qtyInput.value = qtyInput.value.replace(/[^0-9]/g, '');
});

// Calculate dynamic ATS marks total
function calculateTotalAtsMarks(containerSelector) {
    let total = 0;
    $(containerSelector).find('.ats-score-input').each(function() {
        let val = parseInt($(this).val()) || 0;
        if (val < 0) {
            val = 0;
            $(this).val(0);
        }
        total += val;
    });
    $(containerSelector).find('.total-ats-marks').text(total);
}

// Bind live total updates and validate inputs for non-negative
$(document).on('input change keyup', '.ats-score-input', function() {
    let val = $(this).val();
    if (val !== '' && parseInt(val) < 0) {
        $(this).val(0);
    }
    if ($(this).closest('#vacancyPanel').length) {
        calculateTotalAtsMarks('#vacancyPanel');
    } else if ($(this).closest('#editVacancyPanel').length) {
        calculateTotalAtsMarks('#editVacancyPanel');
    }
});

/* ===== View Job Life-Cycle History ===== */
$(document).on('click', '.viewJobHistoryBtn', function () {
    let jid = $(this).data('id');
    $('#jobHistoryModal').modal('show');
    $('#jobHistoryModalBody').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2 text-muted">Loading job life-cycle history...</p></div>');

    $.ajax({
        url: '<?= base_url("admin/getJobHistoryDetails") ?>',
        type: 'POST',
        data: { jid: jid },
        dataType: 'json',
        success: function(res) {
            if (res.status !== 'success') {
                $('#jobHistoryModalBody').html('<div class="alert alert-danger">Unable to load job history details.</div>');
                return;
            }

            let job = res.job;
            let rr  = res.resource_request;
            let timeline = res.timeline || [];

            let html = `<div class="container-fluid p-0">`;

            let m = res.milestones || {};
            let posFilledText = m.position_filled 
                ? `<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>${m.position_filled.candidate_name} (${m.position_filled.candidate_code}) on ${m.position_filled.filled_at}</span>`
                : `<span class="text-muted"><i class="fas fa-hourglass-half mr-1"></i>Not Filled Yet (${res.candidate_count} Applications)</span>`;

            // Job Summary & Milestone Card
            html += `
            <div class="card card-outline card-info mb-4" style="border-radius:8px;">
                <div class="card-header bg-light">
                    <h5 class="card-title text-info font-weight-bold mb-0">
                        <i class="fas fa-briefcase mr-2"></i>${job.JobTitle} (${job.JobCode})
                    </h5>
                    <span class="badge ${job.JobStatus === 'Open' ? 'badge-success' : (job.JobStatus === 'On-Hold' ? 'badge-warning' : 'badge-secondary')} float-right px-3 py-1 font-weight-bold">
                        Status: ${job.JobStatus}
                    </span>
                </div>
                <div class="card-body">
                    <!-- 1. People & Roles Grid -->
                    <div class="row text-sm mb-3">
                        <div class="col-md-3">
                            <div class="p-2 rounded bg-light border h-100">
                                <span class="text-muted d-block text-xs text-uppercase font-weight-bold"><i class="fas fa-user-edit text-info mr-1"></i> 1. Requested By</span>
                                <strong class="text-dark d-block mt-1">${m.requested_by ?? '-'}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 rounded bg-light border h-100">
                                <span class="text-muted d-block text-xs text-uppercase font-weight-bold"><i class="fas fa-user-shield text-primary mr-1"></i> 2. Approved By</span>
                                <strong class="text-dark d-block mt-1">${m.approved_by ?? '-'}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 rounded bg-light border h-100">
                                <span class="text-muted d-block text-xs text-uppercase font-weight-bold"><i class="fas fa-user-tag text-success mr-1"></i> 3. Assigned Recruiter</span>
                                <strong class="text-dark d-block mt-1">${m.assigned_to ?? 'Unassigned'}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 rounded bg-light border h-100">
                                <span class="text-muted d-block text-xs text-uppercase font-weight-bold"><i class="fas fa-file-signature text-warning mr-1"></i> 4. CTC Approver</span>
                                <strong class="text-dark d-block mt-1">${m.ctc_approver ?? 'Not Assigned'}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Job Life-Cycle Dates Grid -->
                    <div class="row text-sm pt-2 border-top">
                        <div class="col-md-3">
                            <p class="mb-1"><strong><i class="fas fa-pause-circle text-warning mr-1"></i>Got Hold Date:</strong> <br>${m.hold_at ?? '<span class="text-muted">N/A</span>'}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong><i class="fas fa-calendar-day text-danger mr-1"></i>Hold-Until Date:</strong> <br>${m.hold_until ?? '<span class="text-muted">N/A</span>'}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong><i class="fas fa-play-circle text-success mr-1"></i>Got Unhold Date:</strong> <br>${m.unhold_at ?? '<span class="text-muted">N/A (Active)</span>'}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong><i class="fas fa-times-circle text-dark mr-1"></i>Dropped Date:</strong> <br>${m.dropped_at ?? m.closed_at ?? '<span class="text-muted">N/A</span>'}</p>
                        </div>
                    </div>

                    <!-- 3. Position Filled Status -->
                    <div class="pt-2 mt-2 border-top text-sm">
                        <strong><i class="fas fa-trophy text-warning mr-1"></i>Position Filled Status:</strong> ${posFilledText}
                    </div>
                </div>
            </div>`;

            // Linked Resource Request Box
            if (rr) {
                html += `
                <div class="card bg-light mb-4" style="border-left:4px solid #17a2b8; border-radius:8px;">
                    <div class="card-body p-3">
                        <h6 class="font-weight-bold text-info mb-2"><i class="fas fa-file-alt mr-2"></i>Linked Resource Request (${rr.RequestCode ?? 'N/A'})</h6>
                        <div class="row text-sm">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Requested By:</strong> ${rr.RequestedByName ?? 'Hiring Manager'}</p>
                                <p class="mb-1"><strong>Requested On:</strong> ${rr.RequestedOn ?? '-'}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Assigned Recruiter:</strong> ${rr.AssignedManagerName ?? 'Unassigned'}</p>
                                <p class="mb-1"><strong>Target Onboarding:</strong> ${rr.TargetOnboardingDate ?? 'N/A'}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>CTC Approver:</strong> ${rr.CtcApproverName ?? 'Not Assigned'}</p>
                                <p class="mb-1"><strong>Request Status:</strong> <span class="badge badge-success">${rr.Status ?? 'ACCEPTED'}</span></p>
                            </div>
                        </div>
                    </div>
                </div>`;
            }

            // Life-Cycle Audit Timeline
            html += `<h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-stream mr-2 text-info"></i>Full Life-Cycle Audit Trail</h5>`;
            html += `<div class="timeline timeline-inverse">`;

            if (timeline.length > 0) {
                timeline.forEach(function(item) {
                    html += `
                    <div>
                        <i class="${item.icon}"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock mr-1"></i>${item.timestamp}</span>
                            <h3 class="timeline-header font-weight-bold text-dark">${item.title}</h3>
                            <div class="timeline-body text-sm">
                                <p class="mb-1"><strong>User / Actor:</strong> ${item.user}</p>
                                <p class="mb-0 text-muted">${item.description}</p>
                            </div>
                        </div>
                    </div>`;
                });
            } else {
                html += `<p class="text-muted p-2">No timeline events recorded for this job.</p>`;
            }

            html += `<div><i class="far fa-clock bg-gray"></i></div></div></div>`;

            $('#jobHistoryModalBody').html(html);
        },
        error: function() {
            $('#jobHistoryModalBody').html('<div class="alert alert-danger">Error loading history details.</div>');
        }
    });
});

/* ===== Bulk ATS Resume Upload Modal Script ===== */
$(document).on('click', '.uploadResumeBtn', function() {
    let jid = $(this).data('id');
    $('#upload_job_id').val(jid);
    $('#bulkResumeInput').val('');
    $('#selectedFilesList').empty();
    $('#selectedFilesContainer').addClass('d-none');
    $('#uploadStatusAlert').addClass('d-none').removeClass('alert-success alert-danger alert-info');
    $('#uploadProgressBarContainer').addClass('d-none');
    $('#btnSubmitBulkResumes').prop('disabled', true);
    $('#uploadModal').modal('show');
});

$(document).on('click', '#dropZoneArea', function(e) {
    if (e.target.id === 'bulkResumeInput') return;
    $('#bulkResumeInput').click();
});

$(document).on('dragover dragenter', '#dropZoneArea', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).css('background-color', '#e8f5e9').css('border-color', '#1e7e34');
});

$(document).on('dragleave drop', '#dropZoneArea', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).css('background-color', '#f8f9fa').css('border-color', '#28a745');
});

$(document).on('drop', '#dropZoneArea', function(e) {
    let files = e.originalEvent.dataTransfer.files;
    if (files && files.length > 0) {
        let input = document.getElementById('bulkResumeInput');
        input.files = files;
        updateSelectedFilesList();
    }
});

$(document).on('change', '#bulkResumeInput', function() {
    updateSelectedFilesList();
});

function updateSelectedFilesList() {
    let input = document.getElementById('bulkResumeInput');
    let files = input ? input.files : null;
    let container = $('#selectedFilesList');
    container.empty();

    if (files && files.length > 0) {
        $('#selectedFileCount').text(files.length);
        $('#selectedFilesContainer').removeClass('d-none');
        $('#btnSubmitBulkResumes').prop('disabled', false);

        for (let i = 0; i < files.length; i++) {
            let file = files[i];
            let size = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            let icon = file.name.endsWith('.pdf') ? 'fa-file-pdf text-danger' : 'fa-file-word text-primary';
            container.append(`
                <div class="d-flex justify-content-between align-items-center p-2 mb-1 bg-white rounded border">
                    <span class="text-truncate small"><i class="fas ${icon} mr-2"></i><strong>${file.name}</strong></span>
                    <span class="badge badge-light text-muted small ml-2">${size}</span>
                </div>
            `);
        }
    } else {
        $('#selectedFilesContainer').addClass('d-none');
        $('#btnSubmitBulkResumes').prop('disabled', true);
    }
}

$(document).on('click', '#btnClearSelectedFiles', function(e) {
    e.stopPropagation();
    $('#bulkResumeInput').val('');
    updateSelectedFilesList();
});

$(document).on('submit', '#bulkResumeUploadForm', function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    let submitBtn = $('#btnSubmitBulkResumes');

    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Analyzing Resumes...');
    $('#uploadProgressBarContainer').removeClass('d-none');
    $('#uploadProgressBar').css('width', '60%');

    $.ajax({
        url: '<?= base_url("admin/ats/analyzeResumeModal") ?>',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(res) {
            $('#uploadProgressBar').css('width', '100%');
            submitBtn.prop('disabled', false).html('<i class="fas fa-cogs mr-2"></i>Upload & Analyze Resumes');

            if (res.status === 'success') {
                $('#uploadStatusAlert')
                    .removeClass('d-none alert-danger alert-info')
                    .addClass('alert-success')
                    .html('<i class="fas fa-check-circle mr-2"></i>' + res.message);

                toastr.success(res.message);
                setTimeout(function() {
                    if (res.redirect) {
                        window.location.href = res.redirect;
                    } else {
                        location.reload();
                    }
                }, 1500);
            } else {
                $('#uploadStatusAlert')
                    .removeClass('d-none alert-success alert-info')
                    .addClass('alert-danger')
                    .html('<i class="fas fa-exclamation-triangle mr-2"></i>' + res.message);
                toastr.error(res.message || 'Failed to process resumes');
            }
        },
        error: function() {
            submitBtn.prop('disabled', false).html('<i class="fas fa-cogs mr-2"></i>Upload & Analyze Resumes');
            $('#uploadProgressBarContainer').addClass('d-none');
            $('#uploadStatusAlert')
                .removeClass('d-none alert-success alert-info')
                .addClass('alert-danger')
                .html('<i class="fas fa-exclamation-triangle mr-2"></i>Server error during resume processing');
            toastr.error('Server error during upload');
        }
    });
});
  </script>

<!-- Job Life-Cycle History Modal -->
<div class="modal fade" id="jobHistoryModal" tabindex="-1" role="dialog" aria-labelledby="jobHistoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content modal-content-rounded-lg">
      <div class="modal-header bg-info text-white rounded-top">
        <h5 class="modal-title font-weight-bold" id="jobHistoryModalLabel">
          <i class="fas fa-history mr-2"></i>Job Life-Cycle & Audit History
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4" id="jobHistoryModalBody">
        <div class="text-center p-5">
          <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
          <p class="mt-2 text-muted">Loading job life-cycle history...</p>
        </div>
      </div>
    </div>
  </div>
</div>