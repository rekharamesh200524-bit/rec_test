<section class="content">
  <div class="container-fluid">

    <div class="card card-primary card-outline">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-list-alt mr-2"></i>Resource Requests List</h3>
        <div class="card-tools ml-auto">
          <button type="button" class="btn btn-sm btn-warning font-weight-bold" id="openRequestResourcePanel" onclick="openCreateRequestModal()">
            <i class="fas fa-plus-circle mr-1"></i> Request Resource
          </button>
        </div>
      </div>

      <div class="card-body p-0 table-responsive">
        <table class="table table-bordered table-striped align-middle" id="requestsTable">
          <thead class="bg-success text-white">
            <tr>
              <th>Request Code</th>
              <th>Job Title / Designation</th>
              <th>Functional Role</th>
              <th>Department</th>
              <th>Position</th>
              <th>Requested By</th>
              <th>Approver</th>
              <th>Target Onboarding</th>
              <th>Request Date</th>
              <th>Status</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($requests)): ?>
              <?php foreach ($requests as $req): ?>
                <tr>
                  <td class="font-weight-bold text-primary"><?= htmlspecialchars($req['RequestCode']); ?></td>
                  <td><?= htmlspecialchars($req['JobTitle']); ?></td>
                  <td><?= htmlspecialchars($req['FunctionalRole'] ? $req['FunctionalRole'] : '-'); ?></td>
                  <td><?= htmlspecialchars($req['Departmentname'] ? $req['Departmentname'] : '-'); ?></td>
                  <td><?= (int)$req['NoofOpenings']; ?></td>
                  <td><?= htmlspecialchars($req['RequestedByName'] ? $req['RequestedByName'] : '-'); ?></td>
                  <td><?= htmlspecialchars($req['ApproverName'] ? $req['ApproverName'] : '-'); ?></td>
                  <td><?= $req['TargetOnboardingDate'] ? date('d-m-Y', strtotime($req['TargetOnboardingDate'])) : '-'; ?></td>
                  <td><?= date('d-m-Y', strtotime($req['CreatedAt'])); ?></td>
                  <td>
                    <?php if ($req['Status'] === 'PENDING APPROVAL'): ?>
                      <span class="badge badge-warning px-2"><i class="fas fa-clock mr-1"></i>Pending Approval</span>
                    <?php elseif ($req['Status'] === 'ACCEPTED'): ?>
                      <span class="badge badge-success px-2"><i class="fas fa-check-circle mr-1"></i>Accepted</span>
                    <?php elseif ($req['Status'] === 'REJECTED'): ?>
                      <span class="badge badge-danger px-2"><i class="fas fa-times-circle mr-1"></i>Rejected</span>
                    <?php else: ?>
                      <span class="badge badge-secondary px-2"><?= htmlspecialchars($req['Status']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                 
                      <button type="button" class="btn btn-sm btn-info" title="View Details" onclick="viewRequestDetails(<?= htmlspecialchars(json_encode($req)); ?>)">
                        <i class="fas fa-eye"></i>
                      </button>

                      <?php
                      $sessionUserId = isset($employee_det['IUid']) ? (int)$employee_det['IUid'] : 0;
                      $canUpdate = ($isHiringManager && (int)$req['RequestedBy'] === $sessionUserId) || in_array($sessionRole, [1, 3, 10]);
                      ?>
                      <?php if ($canUpdate && $req['Status'] === 'PENDING APPROVAL'): ?>
                        <button type="button" class="btn btn-sm btn-primary" title="Edit Request"
                          onclick='openEditRequestModal(<?= json_encode($req, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                          <i class="fas fa-edit"></i>
                        </button>
                      <?php endif; ?>

                     
                      <?php
                      $sessionRole = isset($employee_det['EmpRoleId']) ? (int)$employee_det['EmpRoleId'] : 0;
                      $isHiringManager = ($sessionRole === 9);
                      $isApproverRole  = in_array($sessionRole, [1, 3, 10, 12]); 
                      ?>

                      <?php if ($req['Status'] === 'PENDING APPROVAL'): ?>

                        <?php if ($isApproverRole): ?>
                        
                          <button type="button" class="btn btn-sm btn-success" title="Accept Request"
                            onclick="openApprovalModal('<?= !empty($req['RequestId']) ? $req['RequestId'] : htmlspecialchars($req['RequestCode']); ?>', 'ACCEPTED', '<?= htmlspecialchars($req['RequestCode']); ?>')">
                            <i class="fas fa-check"></i>
                          </button>
                          <button type="button" class="btn btn-sm btn-danger" title="Reject Request"
                            onclick="openApprovalModal('<?= !empty($req['RequestId']) ? $req['RequestId'] : htmlspecialchars($req['RequestCode']); ?>', 'REJECTED', '<?= htmlspecialchars($req['RequestCode']); ?>')">
                            <i class="fas fa-times"></i>
                          </button>
                        <?php endif; ?>

                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="11" class="text-center text-muted py-4">
                  <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary"></i>
                  No resource requests found.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>


<div id="requestResourcePanel" class="right-form">
  <form action="<?= base_url('admin/saveResourceRequest'); ?>" method="post" id="resourceRequestForm" style="display:flex; flex-direction:column; height:100%;">
    <input type="hidden" name="RequestId" id="res_RequestId" value="0">
    
    <div class="right-form-header">
      <h5 class="mb-0 font-weight-bold" id="panelHeaderTitle"><i class="fas fa-user-plus mr-2"></i>Request Resource</h5>
      <button type="button" class="close-btn" id="closeRequestResourcePanel">&times;</button>
    </div>

    <div class="right-form-body">
      <div class="bs-stepper">
                
                <div class="bs-stepper-header" role="tablist">
                  <div class="step active" data-target="#res-job-part">
                    <button type="button" class="step-trigger" role="tab" aria-controls="res-job-part" id="res-job-part-trigger">
                      <span class="bs-stepper-circle">1</span>
                      <span class="bs-stepper-label">JOB INFO</span>
                    </button>
                  </div>
                  <div class="line"></div>
                  <div class="step" data-target="#res-salary-part">
                    <button type="button" class="step-trigger" role="tab" aria-controls="res-salary-part" id="res-salary-part-trigger">
                      <span class="bs-stepper-circle">2</span>
                      <span class="bs-stepper-label">SALARY INFO</span>
                    </button>
                  </div>
                  <div class="line"></div>
                  <div class="step" data-target="#res-desc-part">
                    <button type="button" class="step-trigger" role="tab" aria-controls="res-desc-part" id="res-desc-part-trigger">
                      <span class="bs-stepper-circle">3</span>
                      <span class="bs-stepper-label">SKILL INFO</span>
                    </button>
                  </div>
                </div>

                <div class="bs-stepper-content mt-3">
                  
                  
                  <div id="res-job-part" class="content active" role="tabpanel" aria-labelledby="res-job-part-trigger">
                    
                    <div class="form-group">
                      <label class="text-label font-weight-bold">Job Title <span class="text-danger">*</span></label>
                      <input type="text" name="JobTitle" class="form-control" required>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold">Role <span class="text-danger">*</span></label>
                      <input type="text" name="FunctionalRole" class="form-control" required>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold">Department <span class="text-danger">*</span></label>
                      <select name="Did" class="form-control" required>
                        <option value="">Select Department</option>
                        <?php if (!empty($department)): ?>
                          <?php foreach ($department as $d): ?>
                            <option value="<?= $d['Did']; ?>"><?= htmlspecialchars($d['Departmentname']); ?></option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold">Job Location</label>
                      <div class="position-relative">
                        <input type="text" id="resLocationInput" class="form-control search-input" autocomplete="off">
                        <input type="hidden" name="JobLocation" id="resJobLocation">
                        <div class="dropdown-menu w-100" id="resLocationDropdown"></div>
                      </div>
                      <div class="chip-container mt-2" id="resLocationChips"></div>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold"> Education Required</label>
                      <div class="position-relative">
                        <input type="text" id="resEducationInput" class="form-control search-input" autocomplete="off">
                        <input type="hidden" name="EducationRequired" id="resEducationRequired">
                        <div class="dropdown-menu w-100" id="resEducationDropdown"></div>
                      </div>
                      <div class="chip-container mt-2" id="resEducationChips"></div>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold">Position Type <span class="text-danger">*</span></label>
                      <select name="PositionType" class="form-control" required>
                        <option value="New Position">New Position</option>
                        <option value="Replacement">Replacement</option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold">Approver Name <span class="text-danger">*</span></label>
                      <select name="ApproverId" class="form-control" required>
                        <option value="">Select Approver</option>
                        <?php if (!empty($approvers)): ?>
                          <?php foreach ($approvers as $app): ?>
                            <option value="<?= $app['IUid']; ?>"><?= htmlspecialchars($app['EmpName']); ?> (<?= htmlspecialchars($app['RoleName'] ? $app['RoleName'] : 'Approver'); ?>)</option>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </select>
                    </div>



                    <div class="form-group">
                      <label class="text-label font-weight-bold">Reason for Requirement</label>
                      <textarea name="ReasonForRequirement" class="form-control" rows="3"></textarea>
                    </div>

                    <button type="button" class="btn btn-primary" onclick="resStepperNext()">Next <i class="fas fa-arrow-right ml-1"></i></button>
                  </div>

                  
                  <div id="res-salary-part" class="content" role="tabpanel" aria-labelledby="res-salary-part-trigger">
                    
                    <div class="form-group">
                      <div class="row">
                        <div class="col-md-6">
                          <label class="text-label font-weight-bold">Min Experience (Years)</label>
                          <input type="number" step="any" min="0" name="ExpMin" id="res_ExpMin" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                          <label class="text-label font-weight-bold">Max Experience (Years)</label>
                          <input type="number" step="any" min="0" name="ExpMax" id="res_ExpMax" class="form-control" value="0">
                        </div>
                      </div>
                    </div>

                    

                    <div class="form-group">
                      <label class="text-label font-weight-bold">Target Onboarding Date</label>
                      <input type="date" name="TargetOnboardingDate" class="form-control">
                    </div>



                    <button type="button" class="btn btn-secondary mr-1" onclick="resStepperPrev()"><i class="fas fa-arrow-left mr-1"></i> Previous</button>
                    <button type="button" class="btn btn-primary" onclick="resStepperNext()">Next <i class="fas fa-arrow-right ml-1"></i></button>
                  </div>

                 
                  <div id="res-desc-part" class="content" role="tabpanel" aria-labelledby="res-desc-part-trigger">
                    
                    <div class="form-group">
                      <label class="text-label font-weight-bold">Number of Positions <span class="text-danger">*</span></label>
                      <input type="number" name="NoofOpenings" class="form-control" value="1" min="1" required>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold">Must-Have Skills <span class="text-danger">*</span></label>
                      <div class="position-relative">
                        <input type="text" id="resMustHaveSkillsInput" class="form-control search-input" autocomplete="off">
                        <input type="hidden" name="MustHaveSkills" id="resMustHaveSkills">
                        <div class="dropdown-menu w-100" id="resMustHaveSkillsDropdown"></div>
                      </div>
                      <div class="chip-container mt-2" id="resMustHaveSkillsChips"></div>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold">Nice-to-Have Skills</label>
                      <div class="position-relative">
                        <input type="text" id="resNiceToHaveSkillsInput" class="form-control search-input" autocomplete="off">
                        <input type="hidden" name="NiceToHaveSkills" id="resNiceToHaveSkills">
                        <div class="dropdown-menu w-100" id="resNiceToHaveSkillsDropdown"></div>
                      </div>
                      <div class="chip-container mt-2" id="resNiceToHaveSkillsChips"></div>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold"><i class="fas fa-language text-secondary mr-1"></i> Communication Languages <span class="text-danger">*</span></label>
                      <div class="position-relative">
                        <input type="text" id="resLanguageInput" class="form-control search-input" autocomplete="off">
                        <input type="hidden" name="CommunicationLang" id="resCommunicationLang">
                        <div class="dropdown-menu w-100" id="resLanguageDropdown"></div>
                      </div>
                      <div class="chip-container mt-2" id="resLanguageChips"></div>
                    </div>

                    <div class="mb-3">
                      <button type="button" class="btn btn-outline-primary font-weight-bold" id="btnGenerateJobContent">
                        <i class="fas fa-magic mr-1"></i> Generate Job Description & Responsibilities
                      </button>
                      <small class="form-text text-muted">Auto-generates professional Job Description and Roles & Responsibilities based on vacancy details.</small>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold">Job Description <span class="text-danger">*</span></label>
                      <textarea name="JobDescription" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                      <label class="text-label font-weight-bold">Roles & Responsibilities <span class="text-danger">*</span></label>
                      <textarea name="Responsibilities" class="form-control" rows="4" required></textarea>
                    </div>

                    <button type="button" class="btn btn-secondary mr-1" onclick="resStepperPrev()"><i class="fas fa-arrow-left mr-1"></i> Previous</button>
                    <button type="submit" class="btn btn-success" id="resSubmitBtn"><i class="fas fa-paper-plane mr-1"></i> Submit Request</button>
                  </div>

                </div>
              </div>
            </div>
  </form>
</div>


<div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); padding: 16px 24px;">
        <h5 class="modal-title font-weight-bold" id="viewDetailsModalLabel"><i class="fas fa-file-alt mr-2"></i>Resource Request Details</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4" id="detailsModalContent" style="max-height: 78vh; overflow-y: auto;">
        
      </div>
      <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-secondary font-weight-bold px-4 rounded-pill" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
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
            <textarea name="ApprovalComment" id="approvalComment" class="form-control" rows="4" required></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn" id="approvalSubmitBtn">Confirm</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
var resStepperObj = null;

$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#requestsTable')) {
        $('#requestsTable').DataTable({
            "responsive": false,
            "autoWidth": false,
            "order": [[0, "asc"]]
        });
    }
    $(window).on('resize orientationchange', function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#requestsTable')) {
            $('#requestsTable').DataTable().columns.adjust();
        }
    });

  $('#openRequestResourcePanel').on('click', function() {
    openCreateRequestModal();
  });

  $('#closeRequestResourcePanel').on('click', function() {
    $('#requestResourcePanel').removeClass('open');
  });

 
  var stepperEl = document.querySelector('#requestResourcePanel .bs-stepper');
  if (stepperEl && typeof Stepper !== 'undefined') {
    try {
      window.resStepperObj = new Stepper(stepperEl);
    } catch (e) {}
  }

  
  $(document).on('click', '#requestResourcePanel .bs-stepper-header .step', function(e) {
    e.preventDefault();
    var target = $(this).data('target');
    if (target === '#res-job-part') goToResStep(1);
    else if (target === '#res-salary-part') goToResStep(2);
    else if (target === '#res-desc-part') goToResStep(3);
  });

  initResChipAutocomplete({
      inputId: 'resLocationInput',
      dropdownId: 'resLocationDropdown',
      chipsId: 'resLocationChips',
      hiddenId: 'resJobLocation',
      url: '<?= base_url("admin/searchLocation") ?>',
      key: 'JobLocation'
  });
  initResChipAutocomplete({
      inputId: 'resEducationInput',
      dropdownId: 'resEducationDropdown',
      chipsId: 'resEducationChips',
      hiddenId: 'resEducationRequired',
      url: '<?= base_url("admin/searchEducation") ?>',
      key: 'EducationRequired'
  });
  initResChipAutocomplete({
      inputId: 'resMustHaveSkillsInput',
      dropdownId: 'resMustHaveSkillsDropdown',
      chipsId: 'resMustHaveSkillsChips',
      hiddenId: 'resMustHaveSkills',
      url: '<?= base_url("admin/searchSkills") ?>',
      key: 'SkillName'
  });
  initResChipAutocomplete({
      inputId: 'resNiceToHaveSkillsInput',
      dropdownId: 'resNiceToHaveSkillsDropdown',
      chipsId: 'resNiceToHaveSkillsChips',
      hiddenId: 'resNiceToHaveSkills',
      url: '<?= base_url("admin/searchSkills") ?>',
      key: 'SkillName'
  });
  initResChipAutocomplete({
      inputId: 'resLanguageInput',
      dropdownId: 'resLanguageDropdown',
      chipsId: 'resLanguageChips',
      hiddenId: 'resCommunicationLang',
      url: '<?= base_url("admin/searchLanguage") ?>',
      key: 'CommunicationLang'
  });

  $('#resourceRequestForm').on('submit', function(e) {
      e.preventDefault();

      var jobTitle = $('input[name="JobTitle"]').val().trim();
      var did = $('select[name="Did"]').val();
      var approverId = $('select[name="ApproverId"]').val();
      var mustHave = $('#resMustHaveSkills').val().trim();
      var commLang = $('#resCommunicationLang').val().trim();
      var jd = $('textarea[name="JobDescription"]').val().trim();
      var rr = $('textarea[name="Responsibilities"]').val().trim();

      if (!jobTitle || !did || !approverId) {
          goToResStep(1);
          toastr.warning('Please complete Job Title, Department, and Approver Name in Step 1.');
          return false;
      }

      if (!mustHave) {
          goToResStep(3);
          toastr.warning('Please add at least one Must-Have Skill.');
          return false;
      }

      if (!commLang) {
          goToResStep(3);
          toastr.warning('Please add at least one Communication Language.');
          return false;
      }

      if (!jd || !rr) {
          goToResStep(3);
          toastr.warning('Please provide Job Description and Roles & Responsibilities.');
          return false;
      }

      var $btn = $('#resSubmitBtn');
      $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...');

      $.ajax({
          url: $(this).attr('action'),
          type: 'POST',
          data: $(this).serialize(),
          dataType: 'json',
          success: function(res) {
              if (res.status === 'success') {
                  toastr.success(res.message || 'Resource Request submitted successfully.');
                  setTimeout(function() {
                      location.reload();
                  }, 1000);
              } else {
                  $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Submit Request');
                  toastr.error(res.message || 'Failed to submit request.');
              }
          },
          error: function() {
              $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Submit Request');
              toastr.error('Server or network error occurred.');
          }
      });
  });
});

var currentResStep = 1;

function goToResStep(stepNum) {
  stepNum = parseInt(stepNum) || 1;
  if (stepNum < 1) stepNum = 1;
  if (stepNum > 3) stepNum = 3;
  currentResStep = stepNum;

  var targets = {
    1: '#res-job-part',
    2: '#res-salary-part',
    3: '#res-desc-part'
  };
  var targetId = targets[stepNum];

 
  $('#requestResourcePanel .bs-stepper-header .step').removeClass('active');
  $('#requestResourcePanel .bs-stepper-header .step[data-target="' + targetId + '"]').addClass('active');

  
  $('#requestResourcePanel .bs-stepper-content .content').removeClass('active').hide();
  $(targetId).addClass('active').fadeIn(150);


  if (window.resStepperObj) {
    try { window.resStepperObj.to(stepNum); } catch (e) {}
  }
}

function resStepperNext() {
  goToResStep(currentResStep + 1);
}

function resStepperPrev() {
  goToResStep(currentResStep - 1);
}

function viewRequestDetails(req) {
  var statusClass = 'badge-warning';
  var statusIcon = 'fa-clock';
  var statusText = req.Status || 'PENDING APPROVAL';

  if (statusText === 'ACCEPTED' || statusText === 'APPROVED') {
    statusClass = 'badge-success';
    statusIcon = 'fa-check-circle';
  } else if (statusText === 'REJECTED') {
    statusClass = 'badge-danger';
    statusIcon = 'fa-times-circle';
  }

  function makeChips(str, colorClass) {
    if (!str || str.trim() === '-' || str.trim() === '') return '<span class="text-muted small">None specified</span>';
    var arr = str.split(',');
    return arr.map(function(s) {
      return '<span class="badge badge-pill ' + colorClass + ' mr-1 mb-1 px-3 py-1 font-weight-normal" style="font-size:12px;">' + s.trim() + '</span>';
    }).join(' ');
  }

  var mustSkills = makeChips(req.MustHaveSkills, 'badge-success');
  var niceSkills = makeChips(req.NiceToHaveSkills, 'badge-info');
  var languages  = makeChips(req.CommunicationLang, 'badge-primary');

  function formatProjectDate(dInput) {
    if (!dInput || dInput === '0000-00-00' || dInput === '0000-00-00 00:00:00') return '-';
    let d = new Date(dInput);
    if (isNaN(d.getTime())) return dInput;
    let day = String(d.getDate()).padStart(2, '0');
    let month = String(d.getMonth() + 1).padStart(2, '0');
    let year = d.getFullYear();
    return `${day}-${month}-${year}`;
  }

  var targetDateStr = req.TargetOnboardingDate ? formatProjectDate(req.TargetOnboardingDate) : '-';
  var reqDateStr = req.CreatedAt ? formatProjectDate(req.CreatedAt) : '-';

  var html = `
    <div class="card border-0 shadow-none mb-0">
      <!-- Top Overview Header Banner -->
      <div class="p-3 mb-3 rounded-lg" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border-left: 5px solid #0d9488;">
        <div class="row align-items-center">
          <div class="col-md-8">
            <span class="badge badge-secondary px-2 py-1 small font-weight-bold mb-1"><i class="fas fa-hashtag mr-1"></i>${req.RequestCode || 'REQ'}</span>
            <h4 class="mb-0 font-weight-bold text-dark">${req.JobTitle || 'N/A'}</h4>
            <div class="text-muted small mt-1">
              <span class="mr-3"><i class="fas fa-building text-secondary mr-1"></i>${req.Departmentname || 'N/A'}</span>
              <span class="mr-3"><i class="fas fa-map-marker-alt text-danger mr-1"></i>${req.JobLocation || 'N/A'}</span>
              <span><i class="fas fa-briefcase text-info mr-1"></i>${req.PositionType || 'New Position'}</span>
            </div>
          </div>
          <div class="col-md-4 text-md-right mt-2 mt-md-0">
            <span class="badge ${statusClass} px-3 py-2 font-weight-bold" style="font-size:13px; border-radius:20px;">
              <i class="fas ${statusIcon} mr-1"></i>${statusText}
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Metrics Grid -->
      <div class="row text-center mb-3">
        <div class="col-6 col-md-3 mb-2">
          <div class="p-2 border rounded bg-white shadow-sm">
            <small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Positions</small>
            <span class="font-weight-bold text-dark h6 mb-0">${req.NoofOpenings || 1}</span>
          </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
          <div class="p-2 border rounded bg-white shadow-sm">
            <small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Experience</small>
            <span class="font-weight-bold text-dark h6 mb-0">${req.ExpMin || 0} - ${req.ExpMax || 0} Yrs</span>
          </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
          <div class="p-2 border rounded bg-white shadow-sm">
            <small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Target Onboarding</small>
            <span class="font-weight-bold text-teal h6 mb-0">${targetDateStr}</span>
          </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
          <div class="p-2 border rounded bg-white shadow-sm">
            <small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Request Date</small>
            <span class="font-weight-bold text-dark h6 mb-0">${reqDateStr}</span>
          </div>
        </div>
      </div>

      <!-- Main Info Cards -->
      <div class="row">
        <!-- Requirements & Stakeholders -->
        <div class="col-md-6 mb-3">
          <div class="card h-100 border-light shadow-sm">
            <div class="card-header bg-light py-2">
              <h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-list-ul mr-1 text-teal"></i>Requirements & Stakeholders</h6>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless mb-0 small">
                <tr><th class="text-muted pl-0" style="width:42%">Functional Role:</th><td class="font-weight-bold text-dark">${req.FunctionalRole || '-'}</td></tr>
                <tr><th class="text-muted pl-0">Education Required:</th><td class="text-dark">${req.EducationRequired || '-'}</td></tr>
                <tr><th class="text-muted pl-0">Salary Range:</th><td class="text-dark">${req.Salary || '-'}</td></tr>
                <tr><th class="text-muted pl-0">Reason for Request:</th><td class="text-dark">${req.ReasonForRequirement || '-'}</td></tr>
                <tr class="border-top"><th class="text-muted pl-0 pt-2">Requested By:</th><td class="font-weight-bold text-dark pt-2">${req.RequestedByName || '-'}</td></tr>
                <tr><th class="text-muted pl-0">Approver:</th><td class="font-weight-bold text-dark">${req.ApproverName || '-'}</td></tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Skills & Languages -->
        <div class="col-md-6 mb-3">
          <div class="card h-100 border-light shadow-sm">
            <div class="card-header bg-light py-2">
              <h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-tags mr-1 text-teal"></i>Skills & Languages</h6>
            </div>
            <div class="card-body p-3">
              <div class="mb-3">
                <small class="text-muted font-weight-bold d-block mb-1">Must-Have Skills:</small>
                <div>${mustSkills}</div>
              </div>
              <div class="mb-3">
                <small class="text-muted font-weight-bold d-block mb-1">Nice-to-Have Skills:</small>
                <div>${niceSkills}</div>
              </div>
              <div>
                <small class="text-muted font-weight-bold d-block mb-1">Communication Languages:</small>
                <div>${languages}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Job Description -->
      ${req.JobDescription ? `
      <div class="card border-light shadow-sm mb-3">
        <div class="card-header bg-light py-2">
          <h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-align-left mr-1 text-teal"></i>Job Description</h6>
        </div>
        <div class="card-body p-3 small text-dark" style="white-space:pre-wrap; line-height:1.6; background-color:#fafafa; border-radius: 0 0 8px 8px;">${req.JobDescription}</div>
      </div>` : ''}

      <!-- Roles & Responsibilities -->
      ${req.Responsibilities ? `
      <div class="card border-light shadow-sm mb-3">
        <div class="card-header bg-light py-2">
          <h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-tasks mr-1 text-teal"></i>Roles & Responsibilities</h6>
        </div>
        <div class="card-body p-3 small text-dark" style="white-space:pre-wrap; line-height:1.6; background-color:#fafafa; border-radius: 0 0 8px 8px;">${req.Responsibilities}</div>
      </div>` : ''}

      <!-- Approver Remark (if any) -->
      ${req.ApprovalComment ? `
      <div class="alert alert-warning border-0 shadow-sm p-3 mb-0" style="border-left: 5px solid #f59e0b !important; border-radius: 8px;">
        <h6 class="font-weight-bold mb-1 small text-dark"><i class="fas fa-comment-alt text-warning mr-1"></i>Approver Remark:</h6>
        <p class="mb-1 small text-dark">${req.ApprovalComment}</p>
        ${req.ActionedAt ? `<small class="text-muted"><i class="far fa-clock mr-1"></i>Actioned on: ${req.ActionedAt}</small>` : ''}
      </div>` : ''}
    </div>
  `;

  $('#detailsModalContent').html(html);
  $('#viewDetailsModal').modal('show');
}

function openApprovalModal(requestId, status, requestCode) {
  var finalReqId = (requestId !== null && requestId !== undefined && requestId !== '') ? requestId : (requestCode || '');
  $('#approvalRequestId').val(finalReqId);
  $('#approvalRequestCode').val(requestCode || '');
  $('#approvalStatus').val(status || 'ACCEPTED');
  $('#approvalComment').val('');

  var header = $('#approvalModalHeader');
  var btn = $('#approvalSubmitBtn');

  if (status === 'ACCEPTED') {
    header.attr('class', 'modal-header bg-success text-white');
    $('#approvalModalTitle').text('Accept Resource Request [' + requestCode + ']');
    $('#approvalTargetText').html('You are about to <span class="text-success font-weight-bold">ACCEPT</span> request <code>' + requestCode + '</code>.');
    btn.attr('class', 'btn btn-success').html('<i class="fas fa-check mr-1"></i> Confirm Acceptance');
  } else {
    header.attr('class', 'modal-header bg-danger text-white');
    $('#approvalModalTitle').text('Reject Resource Request [' + requestCode + ']');
    $('#approvalTargetText').html('You are about to <span class="text-danger font-weight-bold">REJECT</span> request <code>' + requestCode + '</code>.');
    btn.attr('class', 'btn btn-danger').html('<i class="fas fa-times mr-1"></i> Confirm Rejection');
  }

  $('#approvalModal').modal('show');
}

function submitApproval(e) {
  e.preventDefault();
  var comment = $('#approvalComment').val().trim();
  if (!comment) {
    showAlert('Approval Comments are mandatory.', 'warning');
    return;
  }

  $.ajax({
    url: '<?= base_url("admin/updateResourceRequestStatus"); ?>',
    type: 'POST',
    data: $('#approvalForm').serialize(),
    dataType: 'json',
    success: function(res) {
      if (res.status === 'success') {
        $('#approvalModal').modal('hide');
        location.reload();
      } else {
        showAlert(res.message || 'Error updating status', 'danger');
      }
    },
    error: function(xhr) {
      var msg = 'Network or server error.';
      try {
        var errRes = JSON.parse(xhr.responseText);
        if (errRes.message) msg = errRes.message;
      } catch (e) {}
      showAlert(msg, 'danger');
    }
  });
}

function openCreateRequestModal() {
  $("#res_RequestId").val("0");
  $("#panelHeaderTitle").html('<i class="fas fa-user-plus mr-2"></i>Request Resource');
  $("#resSubmitBtn").html('<i class="fas fa-paper-plane mr-1"></i> Submit Request');
  if ($("#resourceRequestForm").length) {
    $("#resourceRequestForm")[0].reset();
  }
  preloadResChips('', 'resLocationChips', 'resJobLocation');
  preloadResChips('', 'resEducationChips', 'resEducationRequired');
  preloadResChips('', 'resMustHaveSkillsChips', 'resMustHaveSkills');
  preloadResChips('', 'resNiceToHaveSkillsChips', 'resNiceToHaveSkills');
  preloadResChips('', 'resLanguageChips', 'resCommunicationLang');
  goToResStep(1);
  $("#requestResourcePanel").addClass("open");
}

function openEditRequestModal(req) {
  $("#res_RequestId").val(req.RequestId || "0");
  $("#panelHeaderTitle").html('<i class="fas fa-edit mr-2"></i>Edit Resource Request [' + (req.RequestCode || "") + ']');
  $("#resSubmitBtn").html('<i class="fas fa-save mr-1"></i> Update Request');


  $('input[name="JobTitle"]').val(req.JobTitle || "");
  $('input[name="FunctionalRole"]').val(req.FunctionalRole || "");
  $('select[name="Did"]').val(req.Did || "");
  $('select[name="PositionType"]').val(req.PositionType || "New Position");
  $('select[name="ApproverId"]').val(req.ApproverId || "");
  $('textarea[name="ReasonForRequirement"]').val(req.ReasonForRequirement || "");

  $('input[name="ExpMin"]').val(req.ExpMin || 0);
  $('input[name="ExpMax"]').val(req.ExpMax || 0);
  $('input[name="RecruitmentStartDate"]').val((req.RecruitmentStartDate || "").split(" ")[0]);
  $('input[name="TargetOnboardingDate"]').val((req.TargetOnboardingDate || "").split(" ")[0]);

  $('input[name="NoofOpenings"]').val(req.NoofOpenings || 1);
  $('textarea[name="JobDescription"]').val(req.JobDescription || "");
  $('textarea[name="Responsibilities"]').val(req.Responsibilities || "");

  preloadResChips(req.JobLocation || '', 'resLocationChips', 'resJobLocation');
  preloadResChips(req.EducationRequired || '', 'resEducationChips', 'resEducationRequired');
  preloadResChips(req.MustHaveSkills || '', 'resMustHaveSkillsChips', 'resMustHaveSkills');
  preloadResChips(req.NiceToHaveSkills || '', 'resNiceToHaveSkillsChips', 'resNiceToHaveSkills');
  preloadResChips(req.CommunicationLang || '', 'resLanguageChips', 'resCommunicationLang');
  goToResStep(1);
  $("#requestResourcePanel").addClass("open");
}

function addResChipDirect(value, inputId, chipsId, hiddenId) {
    value = (value || '').replace(/,/g, '').trim();
    if (!value) return;
    const chipsContainer = document.getElementById(chipsId);
    const hiddenInput = hiddenId ? document.getElementById(hiddenId) : null;
    const input = document.getElementById(inputId);
    if (!chipsContainer) return;

    function syncHidden() {
        if (!hiddenInput) return;
        hiddenInput.value = [...chipsContainer.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
    }

    const existing = [...chipsContainer.querySelectorAll('.badge')]
        .map(x => x.textContent.replace('×', '').trim());
    if (existing.includes(value)) {
        if (input) input.value = '';
        return;
    }

    const chip = document.createElement('span');
    chip.className = inputId.toLowerCase().includes('musthave') ? 'badge badge-pill badge-success mr-2 mb-2' : (inputId.toLowerCase().includes('nicetohave') ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
    chip.style.fontSize = '13px';
    chip.style.padding = '6px 12px';
    chip.style.display = 'inline-flex';
    chip.style.alignItems = 'center';
    chip.innerHTML = `${value} <span style="cursor:pointer; margin-left:6px; font-weight:bold; font-size:14px;">×</span>`;

    chip.querySelector('span').onclick = (e) => {
        e.stopPropagation();
        chip.remove();
        syncHidden();
    };
    chipsContainer.appendChild(chip);
    if (input) input.value = '';
    const dropdownId = inputId.replace('Input', 'Dropdown');
    const dropdown = document.getElementById(dropdownId);
    if (dropdown) dropdown.style.display = 'none';
    syncHidden();
}


$(document).on('keydown', '#resLocationInput, #resEducationInput, #resMustHaveSkillsInput, #resNiceToHaveSkillsInput, #resLanguageInput', function(e) {
    if (e.which === 13 || e.keyCode === 13 || e.key === 'Enter' || e.which === 188 || e.keyCode === 188 || e.key === ',') {
        e.preventDefault();
        e.stopPropagation();
        var $input = $(this);
        var val = $input.val().replace(/,/g, '').trim();
        if (val.length >= 1) {
            var inputId = $input.attr('id');
            if (inputId === 'resLocationInput') {
                addResChipDirect(val, 'resLocationInput', 'resLocationChips', 'resJobLocation');
            } else if (inputId === 'resEducationInput') {
                addResChipDirect(val, 'resEducationInput', 'resEducationChips', 'resEducationRequired');
            } else if (inputId === 'resMustHaveSkillsInput') {
                addResChipDirect(val, 'resMustHaveSkillsInput', 'resMustHaveSkillsChips', 'resMustHaveSkills');
            } else if (inputId === 'resNiceToHaveSkillsInput') {
                addResChipDirect(val, 'resNiceToHaveSkillsInput', 'resNiceToHaveSkillsChips', 'resNiceToHaveSkills');
            } else if (inputId === 'resLanguageInput') {
                addResChipDirect(val, 'resLanguageInput', 'resLanguageChips', 'resCommunicationLang');
            }
        }
        return false;
    }
});

$(document).on('blur', '#resLocationInput, #resEducationInput, #resMustHaveSkillsInput, #resNiceToHaveSkillsInput, #resLanguageInput', function() {
    var $input = $(this);
    setTimeout(function() {
        var val = $input.val().replace(/,/g, '').trim();
        if (val.length >= 1) {
            var inputId = $input.attr('id');
            if (inputId === 'resLocationInput') {
                addResChipDirect(val, 'resLocationInput', 'resLocationChips', 'resJobLocation');
            } else if (inputId === 'resEducationInput') {
                addResChipDirect(val, 'resEducationInput', 'resEducationChips', 'resEducationRequired');
            } else if (inputId === 'resMustHaveSkillsInput') {
                addResChipDirect(val, 'resMustHaveSkillsInput', 'resMustHaveSkillsChips', 'resMustHaveSkills');
            } else if (inputId === 'resNiceToHaveSkillsInput') {
                addResChipDirect(val, 'resNiceToHaveSkillsInput', 'resNiceToHaveSkillsChips', 'resNiceToHaveSkills');
            } else if (inputId === 'resLanguageInput') {
                addResChipDirect(val, 'resLanguageInput', 'resLanguageChips', 'resCommunicationLang');
            }
        }
    }, 200);
});

function preloadResChips(values, chipsId, hiddenId) {
    const chips = document.getElementById(chipsId);
    const hidden = hiddenId ? document.getElementById(hiddenId) : null;
    if (!chips) return;
    chips.innerHTML = '';
    if (!values) {
        if (hidden) hidden.value = '';
        return;
    }
    const arr = values.split(',');
    arr.forEach(v => {
        v = v.trim();
        if (!v) return;
        const chip = document.createElement('span');
        chip.className = chipsId.toLowerCase().includes('musthave') ? 'badge badge-pill badge-success mr-2 mb-2' : (chipsId.toLowerCase().includes('nicetohave') ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
        chip.style.fontSize = '13px';
        chip.style.padding = '6px 12px';
        chip.style.display = 'inline-flex';
        chip.style.alignItems = 'center';
        chip.innerHTML = `${v} <span style="cursor:pointer; margin-left:6px; font-weight:bold; font-size:14px;">×</span>`;
        chip.querySelector('span').onclick = (e) => {
            e.stopPropagation();
            chip.remove();
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

function initResChipAutocomplete(config) {
    const input = document.getElementById(config.inputId);
    const dropdown = document.getElementById(config.dropdownId);
    const chipsContainer = document.getElementById(config.chipsId);
    const hiddenInput = config.hiddenId ? document.getElementById(config.hiddenId) : null;
    if (!input || !dropdown || !chipsContainer) return;

    function syncHidden() {
        if (!hiddenInput) return;
        hiddenInput.value = [...chipsContainer.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
    }

    input.addEventListener('keyup', function(e) {
        if (e.key === ',' || e.keyCode === 188) {
            e.preventDefault();
            const value = this.value.replace(/,/g, '').trim();
            if (value.length >= 1) {
                addResChipDirect(value, config.inputId, config.chipsId, config.hiddenId);
            }
            return;
        }
        const q = this.value.trim();
        if (q.length < 2) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            return;
        }
        fetch(`${config.url}?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                dropdown.innerHTML = '';
                if (!data || !data.length) {
                    dropdown.innerHTML = '<span class="dropdown-item disabled">No results</span>';
                } else {
                    data.forEach(item => {
                        const value = item[config.key];
                        const el = document.createElement('a');
                        el.className = 'dropdown-item';
                        el.style.cursor = 'pointer';
                        el.textContent = value;
                        el.onclick = (evt) => {
                            evt.preventDefault();
                            evt.stopPropagation();
                            addResChipDirect(value, config.inputId, config.chipsId, config.hiddenId);
                        };
                        dropdown.appendChild(el);
                    });
                }
                dropdown.style.display = 'block';
            })
            .catch(() => {
                dropdown.style.display = 'none';
            });
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

$(document).ready(function() {
    $('#btnGenerateJobContent').on('click', function(e) {
        e.preventDefault();

      
        $('#resLocationInput, #resEducationInput, #resMustHaveSkillsInput, #resNiceToHaveSkillsInput, #resLanguageInput').trigger('blur');

        setTimeout(function() {
            const jobTitle       = $('input[name="JobTitle"]').val().trim();
            const functionalRole = $('input[name="FunctionalRole"]').val().trim();
            const deptSelect     = $('select[name="Did"] option:selected');
            const deptText       = deptSelect.length ? deptSelect.text().trim() : '';
            const department     = (deptText && !deptText.toLowerCase().includes('select')) ? deptText : '';
            const expMin         = $('#res_ExpMin').val() || 0;
            const expMax         = $('#res_ExpMax').val() || 0;

            const mustSkills     = $('#resMustHaveSkills').val() || $('#resMustHaveSkillsInput').val() || '';
            const niceSkills     = $('#resNiceToHaveSkills').val() || $('#resNiceToHaveSkillsInput').val() || '';
            const location       = $('#resJobLocation').val() || $('#resLocationInput').val() || '';
            const commLang       = $('#resCommunicationLang').val() || $('#resLanguageInput').val() || '';

            if (!jobTitle && !functionalRole) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please enter a Job Title or Functional Role before generating.');
                } else {
                    alert('Please enter a Job Title or Functional Role before generating.');
                }
                return;
            }

            const btn = $('#btnGenerateJobContent');
            const origHtml = btn.html();

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating...');

            $.ajax({
                url: base_url + 'admin/generateJobContent',
                type: 'POST',
                data: {
                    JobTitle: jobTitle,
                    FunctionalRole: functionalRole,
                    Department: department,
                    ExpMin: expMin,
                    ExpMax: expMax,
                    MustHaveSkills: mustSkills,
                    NiceToHaveSkills: niceSkills,
                    JobLocation: location,
                    CommunicationLang: commLang
                },
                dataType: 'json',
                success: function(res) {
                    btn.prop('disabled', false).html(origHtml);
                    if (res && res.status === 'success') {
                        if (res.job_description) {
                            $('textarea[name="JobDescription"]').val(res.job_description);
                        }
                        if (res.responsibilities) {
                            $('textarea[name="Responsibilities"]').val(res.responsibilities);
                        }
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Job Description & Responsibilities auto-generated successfully!');
                        }
                    } else {
                        const errorMsg = (res && res.message) ? res.message : 'Unable to generate job content. Please enter the details manually.';
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errorMsg);
                        } else {
                            alert(errorMsg);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    btn.prop('disabled', false).html(origHtml);
                    console.error('Job content generation error:', xhr.responseText);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Unable to generate job content. Please enter the details manually.');
                    } else {
                        alert('Unable to generate job content. Please enter the details manually.');
                    }
                }
            });
        }, 250);
    });
});

</script>
