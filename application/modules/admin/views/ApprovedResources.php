<?php
$employee_det = $this->session->userdata('logged_in');

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = $this->config->item('theme_locations').$this->config->item('active_template');
?>



<section class="content">
  <div class="container-fluid">
    
    <!-- Right-aligned modern toggle switch -->
    <div class="d-flex justify-content-end align-items-center mb-3">
      <div class="tab-switch-container shadow-sm">
        <button type="button" class="tab-switch-btn active" id="btnTogglePendingAssign">
          <i class="fas fa-user-check mr-1"></i> Pending Assigned Recruiter
        </button>
        <button type="button" class="tab-switch-btn" id="btnTogglePendingRequest">
          <i class="fas fa-clock mr-1"></i> Pending Requests
        </button>
      </div>
    </div>

    <!-- Section 1: Pending Assigned Recruiter -->
    <div id="pendingAssignSection">
      <div class="card card-success card-outline shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
          <h3 class="card-title font-weight-bold text-success mb-0">
            <i class="fas fa-list mr-2"></i>Approved Resource Requests Waiting for Recruiter Assignment
          </h3>
          <div class="card-tools d-flex align-items-center mt-2 mt-sm-0">
            <label class="mb-0 mr-2 font-weight-bold text-muted small"><i class="fas fa-filter mr-1"></i>Status Filter:</label>
            <div class="btn-group btn-group-toggle shadow-sm" data-toggle="buttons" id="assignFilterGroup">
              <label class="btn btn-sm btn-outline-secondary active mb-0">
                <input type="radio" name="assign_filter" value="ALL" checked> All
              </label>
              <label class="btn btn-sm btn-outline-success mb-0">
                <input type="radio" name="assign_filter" value="ASSIGNED"> Assigned
              </label>
              <label class="btn btn-sm btn-outline-warning mb-0">
                <input type="radio" name="assign_filter" value="UNASSIGNED"> Unassigned
              </label>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="approvedTable" class="table table-bordered table-striped align-middle">
              <thead class="bg-success text-white">
                <tr>
                  <th style="width: 50px;">S.No</th>
                  <th>Request Code</th>
                  <th>Job Title</th>
                  <th>Department</th>
                  <th style="width: 80px;">Position</th>
                  <th>Target Onboarding Date</th>
                  <th>Requested By</th>
                  <th>CTC Approver</th>
                  <th>Assigned Manager</th>
                  <th>Status</th>
                  <th style="width: 210px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($approved_resources)): ?>
                  <?php $i = 1; foreach ($approved_resources as $row): ?>
                    <?php
                    $salaryVal = !empty($row['EffectiveSalary']) ? trim($row['EffectiveSalary']) : (!empty($row['Salary']) ? trim($row['Salary']) : '');
                    $locVal    = !empty($row['EffectiveLocation']) ? trim($row['EffectiveLocation']) : (!empty($row['JobLocation']) ? trim($row['JobLocation']) : '');
                    $eduVal    = !empty($row['EffectiveEducation']) ? trim($row['EffectiveEducation']) : (!empty($row['EducationRequired']) ? trim($row['EducationRequired']) : '');
                    $ctcVal    = !empty($row['EffectiveCtcApproverId']) ? (int)$row['EffectiveCtcApproverId'] : (!empty($row['CtcApproverId']) ? (int)$row['CtcApproverId'] : 0);

                    $isAllFieldsFilled = !empty($row['JobTitle']) &&
                                         (!empty($row['Did']) || !empty($row['Departmentname'])) &&
                                         !empty($locVal) &&
                                         !empty($eduVal) &&
                                         (!empty($row['MustHaveSkills']) || !empty($row['Skills'])) &&
                                         !empty($row['CommunicationLang']) &&
                                         !empty($row['JobDescription']) &&
                                         !empty($row['Responsibilities']) &&
                                         !empty($salaryVal) &&
                                         $ctcVal > 0;
                    ?>
                    <tr>
                      <td><?= $i++; ?></td>
                      <td><span class="badge badge-pill badge-primary"><?= htmlspecialchars($row['RequestCode']); ?></span></td>
                      <td class="font-weight-bold"><?= htmlspecialchars($row['JobTitle']); ?></td>
                      <td><?= htmlspecialchars($row['Departmentname'] ? $row['Departmentname'] : '-'); ?></td>
                      <td class="text-center"><?= (int)$row['NoofOpenings']; ?></td>
                      <td><?= !empty($row['TargetOnboardingDate']) ? date('d-m-Y', strtotime($row['TargetOnboardingDate'])) : '-'; ?></td>
                      <td><?= htmlspecialchars($row['RequestedByName'] ? $row['RequestedByName'] : 'Hiring Manager'); ?></td>
                      <td><?= htmlspecialchars($row['CtcApproverName'] ? $row['CtcApproverName'] : '-'); ?></td>
                      <td>
                        <?php if (!empty($row['AssignedRecruiterManagerName'])): ?>
                          <span class="badge badge-pill badge-outline-success font-weight-bold"><i class="fas fa-user-check mr-1"></i><?= htmlspecialchars($row['AssignedRecruiterManagerName']); ?></span>
                        <?php else: ?>
                          <span class="badge badge-pill badge-warning text-dark"><i class="fas fa-clock mr-1"></i>Unassigned</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($row['Status'] === 'ASSIGNED'): ?>
                          <span class="badge badge-success">ASSIGNED</span>
                        <?php else: ?>
                          <span class="badge badge-info">APPROVED</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="btn-group" role="group">
                         
                          <!-- <button type="button" 
                                  class="btn btn-sm btn-primary editJobBtn" 
                                  title="Edit Job" 
                                  data-id="<?= (int)$row['ConvertedJid']; ?>" 
                                  data-req='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>'>
                            <i class="fas fa-edit"></i>
                          </button> -->

                         
                          <button type="button" 
                                  class="btn btn-sm btn-secondary btn-view-details" 
                                  title="View Details" 
                                  data-req='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>'>
                            <i class="fas fa-eye"></i>
                          </button>

                          <?php if (!empty($row['AssignedRecruiterManagerId']) || $row['Status'] === 'ASSIGNED'): ?>
                            <button type="button" 
                                    class="btn btn-sm btn-warning btn-assign" 
                                    title="Reassign Recruiter" 
                                    data-id="<?= $row['RequestId']; ?>" 
                                    data-code="<?= htmlspecialchars($row['RequestCode']); ?>" 
                                    data-title="<?= htmlspecialchars($row['JobTitle']); ?>" 
                                    data-assigned="<?= (int)$row['AssignedRecruiterManagerId']; ?>">
                              <i class="fas fa-user-edit"></i>
                            </button>
                          <?php else: ?>
                            <button type="button" 
                                    class="btn btn-sm btn-warning btn-assign text-dark" 
                                    title="Assign Recruiter" 
                                    data-id="<?= $row['RequestId']; ?>" 
                                    data-code="<?= htmlspecialchars($row['RequestCode']); ?>" 
                                    data-title="<?= htmlspecialchars($row['JobTitle']); ?>" 
                                    data-assigned="<?= (int)$row['AssignedRecruiterManagerId']; ?>">
                              <i class="fas fa-user-plus"></i>
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="11" class="text-center text-muted font-weight-bold py-4">No approved resource requests waiting for assignment.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 2: Pending Requests -->
    <div id="pendingRequestSection" style="display: none;">
      <div class="card card-warning card-outline shadow-sm">
        <div class="card-header bg-white">
          <h3 class="card-title font-weight-bold text-warning mb-0"><i class="fas fa-clock mr-2"></i>Pending Resource Requests</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="pendingRequestsTable" class="table table-bordered table-striped align-middle">
              <thead class="bg-warning text-dark">
                <tr>
                  <th style="width: 50px;">S.No</th>
                  <th>Request Code</th>
                  <th>Job Title</th>
                  <th>Functional Role</th>
                  <th>Department</th>
                  <th style="width: 80px;">Position</th>
                  <th>Requested By</th>
                  <th>Approver</th>
                  <th>Target Onboarding Date</th>
                  <th>Request Date</th>
                  <th>Status</th>
                  <th style="width: 150px;" class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($pending_resources)): ?>
                  <?php $j = 1; foreach ($pending_resources as $pReq): ?>
                    <tr>
                      <td><?= $j++; ?></td>
                      <td><span class="badge badge-pill badge-primary"><?= htmlspecialchars($pReq['RequestCode']); ?></span></td>
                      <td class="font-weight-bold"><?= htmlspecialchars($pReq['JobTitle']); ?></td>
                      <td><?= htmlspecialchars($pReq['FunctionalRole'] ? $pReq['FunctionalRole'] : '-'); ?></td>
                      <td><?= htmlspecialchars($pReq['Departmentname'] ? $pReq['Departmentname'] : '-'); ?></td>
                      <td class="text-center"><?= (int)$pReq['NoofOpenings']; ?></td>
                      <td><?= htmlspecialchars($pReq['RequestedByName'] ? $pReq['RequestedByName'] : '-'); ?></td>
                      <td><?= htmlspecialchars($pReq['ApproverName'] ? $pReq['ApproverName'] : '-'); ?></td>
                      <td><?= !empty($pReq['TargetOnboardingDate']) ? date('d-m-Y', strtotime($pReq['TargetOnboardingDate'])) : '-'; ?></td>
                      <td><?= !empty($pReq['CreatedAt']) ? date('d-m-Y', strtotime($pReq['CreatedAt'])) : '-'; ?></td>
                      <td>
                        <?php if ($pReq['Status'] === 'PENDING APPROVAL' || $pReq['Status'] === 'PENDING'): ?>
                          <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i>PENDING APPROVAL</span>
                        <?php elseif ($pReq['Status'] === 'ACCEPTED'): ?>
                          <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>ACCEPTED</span>
                        <?php elseif ($pReq['Status'] === 'REJECTED'): ?>
                          <span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>REJECTED</span>
                        <?php else: ?>
                          <span class="badge badge-secondary"><?= htmlspecialchars($pReq['Status']); ?></span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <div class="btn-group" role="group">
                          <button type="button" class="btn btn-sm btn-secondary btn-view-details" title="View Details" data-req='<?= htmlspecialchars(json_encode($pReq), ENT_QUOTES, 'UTF-8'); ?>'>
                            <i class="fas fa-eye"></i>
                          </button>

                          <?php if ($pReq['Status'] === 'PENDING APPROVAL' || $pReq['Status'] === 'PENDING'): ?>
                            <button type="button" class="btn btn-sm btn-success btn-open-approval" title="Accept / Approve Request"
                              data-id="<?= !empty($pReq['RequestId']) ? $pReq['RequestId'] : htmlspecialchars($pReq['RequestCode']); ?>"
                              data-code="<?= htmlspecialchars($pReq['RequestCode']); ?>"
                              data-status="ACCEPTED">
                              <i class="fas fa-check"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger btn-open-approval" title="Reject Request"
                              data-id="<?= !empty($pReq['RequestId']) ? $pReq['RequestId'] : htmlspecialchars($pReq['RequestCode']); ?>"
                              data-code="<?= htmlspecialchars($pReq['RequestCode']); ?>"
                              data-status="REJECTED">
                              <i class="fas fa-times"></i>
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="12" class="text-center text-muted font-weight-bold py-4">No pending resource requests found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>


<div class="modal fade" id="assignRecruiterModal" tabindex="-1" role="dialog" aria-labelledby="assignRecruiterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold" id="assignRecruiterModalLabel"><i class="fas fa-user-tag mr-2"></i>Assign to Recruitment Manager / Recruiter</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="assignRecruiterForm">
        <div class="modal-body">
          <input type="hidden" name="requestId" id="assign_requestId" value="0">
          
          <div class="callout callout-info mb-3">
            <h5><strong id="assign_reqCode"></strong></h5>
            <p class="mb-0 text-muted" id="assign_reqTitle"></p>
          </div>

          <div class="form-group">
            <label class="font-weight-bold">Select Recruitment Manager / Recruiter <span class="text-danger">*</span></label>
            <select name="assignedManagerId" id="assign_recruiterSelect" class="form-control form-control-lg" required>
              <option value="">-- Select Recruitment Manager / Recruiter --</option>
              <?php if (!empty($recruitment_managers)): ?>
                <?php foreach ($recruitment_managers as $rm): ?>
                  <option value="<?= $rm['IUid']; ?>">
                    <?= htmlspecialchars($rm['EmpName']); ?><?= !empty($rm['EmpDesignation']) ? ' (' . htmlspecialchars($rm['EmpDesignation']) . ')' : (' (' . htmlspecialchars($rm['RoleName'] ? $rm['RoleName'] : 'Recruiter') . ')'); ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
            <small class="form-text text-muted">The assigned Recruiter/Manager will receive exclusive access to this vacancy in their Vacancy List and receive a push notification.</small>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold" id="btnConfirmAssign"><i class="fas fa-save mr-1"></i> Save Assignment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Decision Approval / Rejection Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header" id="approvalModalHeader">
        <h5 class="modal-title font-weight-bold" id="approvalModalTitle">Decision Confirmation</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="approvalForm" onsubmit="submitApproval(event)">
        <input type="hidden" name="RequestId" id="approvalRequestId">
        <input type="hidden" name="RequestCode" id="approvalRequestCode">
        <input type="hidden" name="Status" id="approvalStatus">

        <div class="modal-body">
          <p id="approvalTargetText" class="font-weight-bold mb-3"></p>

          <div class="form-group">
            <label class="font-weight-bold">Approval Comments <span class="text-danger">*</span></label>
            <textarea name="ApprovalComment" id="approvalComment" class="form-control" rows="4" placeholder="Enter reason or comments for this decision..." required></textarea>
          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success font-weight-bold" id="approvalSubmitBtn"><i class="fas fa-check mr-1"></i> Confirm Decision</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="modal fade" id="approvedDetailsModal" tabindex="-1" role="dialog" aria-labelledby="approvedDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title font-weight-bold" id="approvedDetailsModalLabel"><i class="fas fa-info-circle mr-2"></i>Approved Resource Request Details</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body" id="detailsModalBody">
     
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<div id="editVacancyPanel" class="right-form">
  <form id="editVacancyForm" action="<?= base_url('admin/updateVacancy') ?>" method="post">
      <input type="hidden" name="jid" id="edit_jid">
      <input type="hidden" name="requestId" id="edit_requestId" value="0">
      <input type="hidden" name="requestCode" id="edit_requestCode">

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
                 
                  <div id="edit-logins-part" class="content">
                      <div class="form-group">
                          <label>Job Code*</label>
                          <input type="text" name="jobCode" id="edit_jobCode" class="form-control" readonly>
                      </div>
                      <div class="form-group">
                          <label>Job Title*</label>
                          <input type="text" name="jobTitle" id="edit_jobTitle" class="form-control" placeholder="Enter Job Title">
                      </div>
                      <div class="form-group">
                          <label>Department*</label>
                          <select name="department" id="edit_department" class="form-control">
                              <option value="">Select Department</option>
                              <?php if (!empty($department)): ?>
                                  <?php foreach ($department as $dept): ?>
                                      <option value="<?= htmlspecialchars($dept['Departmentname']); ?>"><?= htmlspecialchars($dept['Departmentname']); ?></option>
                                  <?php endforeach; ?>
                              <?php endif; ?>
                          </select>
                      </div>
                      <div class="form-group">
                          <label>Functional Role / Role <span class="text-danger">*</span></label>
                          <input type="text" name="role" id="edit_role" class="form-control" placeholder="Enter Functional Role (e.g. Senior Software Engineer)">
                      </div>
                      <div class="form-group">
                          <label>Target Onboarding Date*</label>
                          <input type="date" name="targetOnboardingDate" id="edit_targetOnboardingDate" class="form-control">
                      </div>
                      <button type="button" class="btn btn-primary" onclick="editStepper.next()">Next</button>
                  </div>

                 
                  <div id="edit-information-part" class="content">
                      <div class="form-group">
                          <label>Work Mode*</label><br>
                          <input type="hidden" name="workMode" id="edit_work_mode">
                          <button type="button" class="btn btn-outline-primary edit-work-mode" data-value="Onsite">Onsite</button>
                          <button type="button" class="btn btn-outline-primary edit-work-mode" data-value="Remote">Remote</button>
                          <button type="button" class="btn btn-outline-primary edit-work-mode" data-value="Hybrid">Hybrid</button>
                      </div>
                      <div class="form-group">
                          <label>Employment Type*</label><br>
                          <input type="hidden" name="employmentType" id="edit_employment_type">
                          <button type="button" class="btn btn-outline-primary edit-emp-type" data-value="Full-Time">Full Time</button>
                          <button type="button" class="btn btn-outline-primary edit-emp-type" data-value="Part-Time">Part Time</button>
                          <button type="button" class="btn btn-outline-primary edit-emp-type" data-value="Contract">Contract</button>
                      </div>
                      <div class="form-group">
                          <label>Minimum Experience (Years)*</label>
                          <select name="expMin" id="edit_expMin" class="form-control"></select>
                      </div>
                      <div class="form-group">
                          <label>Maximum Experience (Years)*</label>
                          <select name="expMax" id="edit_expMax" class="form-control"></select>
                      </div>
                      <div class="form-group">
                          <label>Job Location*</label>
                          <div class="position-relative">
                              <input type="text" id="edit_jobLocationInput" class="form-control" autocomplete="off">
                              <div class="dropdown-menu w-100" id="edit_jobLocationDropdown"></div>
                          </div>
                          <input type="hidden" name="jobLocation" id="edit_jobLocation">
                          <div class="chip-container mt-2" id="edit_jobLocationChips"></div>
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
                      <button type="button" class="btn btn-secondary mr-1" onclick="editStepper.previous()">Previous</button>
                      <button type="button" class="btn btn-primary" onclick="editStepper.next()">Next</button>
                  </div>

                  
                  <div id="edit-skill-part" class="content">
                      <div class="form-group">
                          <label>Positions*</label>
                          <input type="number" name="positions" id="edit_positions" class="form-control">
                      </div>
                      <div class="form-group">
                          <label class="font-weight-bold"><i class="fas fa-check-circle text-success mr-1"></i> Must-Have Skills <span class="text-danger">*</span></label>
                          <div class="position-relative">
                              <input type="text" id="edit_mustHaveSkillsInput" class="form-control search-input" placeholder="Type mandatory skill..." autocomplete="off">
                              <input type="hidden" name="mustHaveSkills" id="edit_mustHaveSkills">
                              <div class="dropdown-menu w-100" id="edit_mustHaveSkillsDropdown"></div>
                          </div>
                          <div class="chip-container mt-2" id="edit_mustHaveSkillsChips"></div>
                      </div>
                      <div class="form-group">
                          <label class="font-weight-bold"><i class="fas fa-star text-info mr-1"></i> Nice-to-Have Skills</label>
                          <div class="position-relative">
                              <input type="text" id="edit_niceToHaveSkillsInput" class="form-control search-input" placeholder="Type optional skill..." autocomplete="off">
                              <input type="hidden" name="niceToHaveSkills" id="edit_niceToHaveSkills">
                              <div class="dropdown-menu w-100" id="edit_niceToHaveSkillsDropdown"></div>
                          </div>
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
                          <textarea name="JD" id="edit_JD" class="form-control" rows="3"></textarea>
                      </div>
                      <div class="form-group">
                          <label>Roles & Responsibilities*</label>
                          <textarea name="RR" id="edit_RR" class="form-control" rows="3"></textarea>
                      </div>
                      <button type="button" class="btn btn-secondary mr-1" onclick="editStepper.previous()"><i class="fas fa-arrow-left mr-1"></i> Previous</button>
                      <button type="button" class="btn btn-primary" onclick="editStepper.next()">Next <i class="fas fa-arrow-right ml-1"></i></button>
                  </div>

               
                  <div id="edit-ctc-part" class="content">
                      <div class="form-group">
                          <label class="font-weight-bold"><i class="fas fa-money-bill-wave text-success mr-1"></i> Salary / CTC (LPA) <span class="text-danger">*</span></label>
                          <input type="text" name="salary" id="edit_salary" class="form-control" placeholder="e.g. 5 - 10 LPA" required>
                      </div>

                      <div class="form-group">
                          <label class="font-weight-bold">CTC Approver</label>
                          <select name="CtcApproverId" id="edit_CtcApproverId" class="form-control">
                              <option value="">Select CTC Approver</option>
                              <?php if (!empty($ctc_approvers)): ?>
                                  <?php foreach ($ctc_approvers as $ca): ?>
                                      <option value="<?= $ca['IUid']; ?>"><?= htmlspecialchars($ca['EmpName']); ?> (<?= htmlspecialchars($ca['RoleName'] ? $ca['RoleName'] : 'Employee'); ?>)</option>
                                  <?php endforeach; ?>
                              <?php endif; ?>
                          </select>
                      </div>

                     
                      <div class="form-group border-top pt-3 mt-3">
                          <div class="d-flex align-items-center justify-content-between mb-2">
                              <label class="font-weight-bold text-primary mb-0">
                                  <i class="fas fa-users-cog mr-1"></i> Interview Panel Levels
                              </label>
                              <button type="button" class="btn btn-xs btn-outline-success font-weight-bold" id="addInterviewLevelBtn">
                                  <i class="fas fa-plus mr-1"></i> Add Level
                              </button>
                          </div>
                          <small class="form-text text-muted mb-3">Level 1 & Level 2 are mandatory. Up to 4 levels maximum.</small>

                          <div id="interviewPanelContainer">
                           
                              <div class="form-group mb-2" data-level="1">
                                  <label class="font-weight-bold">Level 1 Interviewer <span class="text-danger">*</span></label>
                                  <select name="interviewPanel[1]" id="edit_interviewPanel_1" class="form-control interview-panel-select" required>
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
                                  <select name="interviewPanel[2]" id="edit_interviewPanel_2" class="form-control interview-panel-select" required>
                                      <option value="">Select Level 2 Interviewer</option>
                                      <?php if (!empty($ctc_approvers)): ?>
                                          <?php foreach ($ctc_approvers as $u): ?>
                                              <option value="<?= $u['IUid']; ?>"><?= htmlspecialchars($u['EmpName']); ?><?= !empty($u['RoleName']) ? ' (' . htmlspecialchars($u['RoleName']) . ')' : ''; ?></option>
                                          <?php endforeach; ?>
                                      <?php endif; ?>
                                  </select>
                              </div>

                            
                              <div id="dynamicLevelsContainer"></div>
                          </div>
                      </div>

                      <button type="button" class="btn btn-secondary mr-1" onclick="editStepper.previous()"><i class="fas fa-arrow-left mr-1"></i> Previous</button>
                      <button type="submit" class="btn btn-primary" id="btnUpdateVacancySubmit"><i class="fas fa-save mr-1"></i> Update</button>
                  </div>
              </div>
          </div>
      </div>
  </form>
</div>


<div id="vacancyOverlay"></div>
