<?php
$employee_det = $this->session->userdata('logged_in');

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = $this->config->item('theme_locations').$this->config->item('active_template');
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
                      <input type="hidden" id="jobId" value="">
                      
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

<div class="modal fade <?= $this->session->flashdata('job_exists') ? 'auto-show-modal' : '' ?>" id="jobExistsModal" tabindex="-1">
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