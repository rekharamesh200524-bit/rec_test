<?php
$employee_det = $this->session->userdata('logged_in');

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = $this->config->item('theme_locations').$this->config->item('active_template');
if(empty($jobdetails)) { $jobdetails = array('Jid' => '', 'JobCode' => '', 'JobTitle' => ''); }
?>


<section class="content pt-3">
  <div class="container-fluid">

    <div class="card card-warning card-outline shadow-sm" id="candidateListContainer" data-jid="<?= $jobdetails['Jid']; ?>" data-selected-source="<?= !empty($selectedSource) ? $selectedSource : 'all'; ?>">

      <div class="card-header bg-white border-bottom-0 py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <h5 class="font-weight-bold text-dark mb-0 my-1 d-flex align-items-center">
            <i class="fas fa-user-tie text-primary mr-2"></i> Candidate Directory List
            <a href="javascript:void(0)" class="viewVacancyBtn badge badge-pill badge-warning text-dark ml-2" data-id="<?= $jobdetails['Jid']; ?>" style="font-size: 13.5px;">
              <?= htmlspecialchars($jobdetails['JobCode']); ?><?= !empty($jobdetails['JobTitle']) ? ' - ' . htmlspecialchars($jobdetails['JobTitle']) : ''; ?>
            </a>
          </h5>

         
          <div class="d-flex align-items-center flex-wrap gap-2 my-1">
            
            <!-- <button type="button" id="btnToggleCompareMode" class="btn btn-outline-primary btn-sm font-weight-bold px-3 py-2 shadow-sm">
              <i class="fas fa-balance-scale mr-1"></i> Compare Candidates
            </button> -->

          
            <div id="compareActiveBar" class="d-none align-items-center flex-wrap gap-2">
              <button type="button" id="btnSelectAllCandidates" class="btn btn-sm btn-outline-info font-weight-bold px-3 py-2">
                <i class="far fa-check-square mr-1"></i> Select All
              </button>
              <span class="badge badge-pill badge-primary text-white font-weight-bold px-3 py-2 shadow-sm ml-1" id="compareCountBadge" style="font-size: 13px;">
                <i class="fas fa-user-check mr-1"></i> <span id="compareSelectedCount">0</span> Selected
              </span>
              <button type="button" id="btnCompareCandidates" class="btn btn-primary disabled btn-sm font-weight-bold px-3 py-2 shadow-sm" data-vacancy-id="<?= $jobdetails['Jid']; ?>" title="Select at least 2 candidates using the checkboxes below to compare">
                <i class="fas fa-columns mr-1"></i> Compare Selected
              </button>
              <button type="button" id="btnCancelCompareMode" class="btn btn-sm btn-outline-danger font-weight-bold px-3 py-2 ml-1">
                <i class="fas fa-times mr-1"></i> Cancel
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card-body pb-0 pt-1">
        <!-- Candidate Dropdown Filter Model -->
        <div class="card card-light mb-3 border shadow-sm" style="border-radius: 8px; background-color: #f8fafc;">
          <div class="card-body p-3">
            <div class="row align-items-end">
              
              <!-- Candidate Source Dropdown -->
              <div class="col-md-5 col-sm-6 mb-2 mb-md-0">
                <div class="form-group mb-0">
                  <label class="font-weight-bold text-dark mb-1" style="font-size: 0.85rem;">
                    <i class="fas fa-filter text-primary mr-1"></i> Candidate Source
                  </label>
                  <select id="sourceSelectFilter" class="form-control font-weight-bold">
                    <option value="all" <?= (empty($selectedSource) || $selectedSource === 'all') ? 'selected' : ''; ?>>All Sources</option>
                    <option value="walkin" <?= (isset($selectedSource) && $selectedSource === 'walkin') ? 'selected' : ''; ?>>Online</option>
                    <option value="online" <?= (isset($selectedSource) && $selectedSource === 'online') ? 'selected' : ''; ?>>Walk-in</option>
                  </select>
                </div>
              </div>

              <!-- Recruitment Status Dropdown -->
              <div class="col-md-5 col-sm-6 mb-2 mb-md-0">
                <div class="form-group mb-0">
                  <label class="font-weight-bold text-dark mb-1" style="font-size: 0.85rem;">
                    <i class="fas fa-tasks text-secondary mr-1"></i> Recruitment Status
                  </label>
                  <select id="statusSelectFilter" class="form-control font-weight-bold">
                    <option value="">All Statuses</option>
                    <option value="CV Uploaded">CV Uploaded</option>
                    <option value="Selected">Selected</option>
                    <option value="On Hold">On Hold</option>
                    <option value="Rejected">Rejected</option>
                  </select>
                </div>
              </div>

              <!-- Reset Button -->
              <div class="col-md-2 col-sm-12 d-flex align-items-end">
                <button type="button" id="btnResetCandidateFilters" class="btn btn-outline-secondary btn-block font-weight-bold shadow-sm">
                  <i class="fas fa-undo mr-1"></i> Reset
                </button>
              </div>

            </div>
          </div>
        </div>
      </div>

      <div class="card-body pt-0 table-responsive">
        <table id="example1" class="table table-bordered table-striped align-middle mb-0 table-full-width">
          <thead class="bg-success text-white">
            <tr>
              <th style="width: 65px;" class="text-center">S.No <input type="checkbox" id="selectAllCandidates" class="chk-input d-none ml-1" title="Select All"></th>
              <th>Code</th>
              <th>Name</th>
              <th>Mobile No</th>
              <th>Email</th>
              <th>ATS Recommendation</th>
              <th>Current Status</th>
              <th>Verified On</th>
              <th>Source</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (isset($Candidatelist) && !empty($Candidatelist)) {
                $i = 1;
                foreach ($Candidatelist as $cl) {
            ?>
                <tr data-candidate-id="<?= $cl['CandidateId']; ?>">
                    <td class="text-center font-weight-bold"><?= $i++; ?> <input type="checkbox" class="candidate-select-chk chk-input d-none ml-1" data-candidate-id="<?= $cl['CandidateId']; ?>" value="<?= $cl['CandidateId']; ?>"></td>
                               <td>
<!-- <a href="javascript:void(0)"
   class="text-warning font-weight-bold viewVacancyBtn"
   data-id="<?= $cl['Jid'] ?>">
   <?= $cl['CandidateCode'] ?>
</a> -->
 
<a href="<?= base_url($cl['ResumePath']); ?>"
   target="_blank"
   class="text-warning font-weight-bold">
   <?= $cl['CandidateCode'] ?>
</a>
</td>
                                    <td>
<a href="javascript:void(0);"
   class="viewCandidateSimple text-primary font-weight-bold"
   data-id="<?= $cl['CandidateId']; ?>">
   <?= $cl['Fullname']; ?>
</a>
</td>

                                    <td><?= $cl['PhoneNo'] ?></td>
                                    <td><?= $cl['Email'] ?></td>
                                    <td>
<?php
    $recommendation = trim($cl['ProfileMatchPer'] ?? '');

    if (in_array($recommendation, ['Strong Match', 'Strongly Match', 'Recommended'])) {
        $badgeClass = 'badge-success';
        $recommendation = 'Strong Match';
    } elseif (in_array($recommendation, ['Potential Match', 'Review Required'])) {
        $badgeClass = 'badge-warning';
        $recommendation = 'Potential Match';
    } elseif (in_array($recommendation, ['Low Match', 'Not Recommended'])) {
        $badgeClass = 'badge-danger';
        $recommendation = 'Low Match';
    } else {
        $badgeClass = 'badge-secondary';
        $recommendation = $recommendation !== '' ? $recommendation : 'Potential Match';
    }

    $analysisData = [];

    if (!empty($cl['ScoreBreakdown'])) {
        if (is_string($cl['ScoreBreakdown'])) {
            $decoded = json_decode($cl['ScoreBreakdown'], true);
            if (is_array($decoded)) {
                $analysisData = $decoded;
            }
        } elseif (is_array($cl['ScoreBreakdown'])) {
            $analysisData = $cl['ScoreBreakdown'];
        }
    }

    $analysisData['recommendation'] =
        $analysisData['recommendation'] ?? $recommendation;

    $analysisData['recommendation_reason'] =
        $analysisData['recommendation_reason'] ?? '';

    $analysisData['relevant_evidence'] =
        $analysisData['relevant_evidence'] ?? [];

    $analysisData['missing_requirements'] =
        $analysisData['missing_requirements'] ?? [];

    $analysisData['domain'] =
        $analysisData['domain'] ?? '';

    $analysisData['matched_skills'] =
        $analysisData['matched_skills'] ?? ($cl['MatchedSkills'] ?? '');

    $analysisData['missing_skills'] =
        $analysisData['missing_skills'] ?? '';

    $analysisData['detected_degree'] =
        $analysisData['detected_degree'] ?? '';

    $analysisData['experience'] =
        $analysisData['experience'] ?? ($cl['ExperienceMatch'] ?? '');

    if (empty($analysisData['candidate_profile'])) {
        $expDetails = [];
        if (!empty($cl['ExperienceDetails'])) {
            $expDetails = is_string($cl['ExperienceDetails']) ? json_decode($cl['ExperienceDetails'], true) : $cl['ExperienceDetails'];
        }

        $wHist = [];
        $eduPattern = '/\b(bachelor|master|b\.?tech|m\.?tech|b\.?e|m\.?e|b\.?sc|m\.?sc|b\.?com|m\.?com|bba|mba|bca|mca|phd|diploma|degree|college|university|institute|school|academy|sslc|hsc|10th|12th|education|academic|passed out|cgpa|percentage)\b/i';
        if (!empty($expDetails['jobs'])) {
            $jIdx = 1;
            foreach ($expDetails['jobs'] as $jItem) {
                $rStr = !empty($jItem['role']) ? $jItem['role'] : (!empty($cl['RoleSummary']) ? $cl['RoleSummary'] : "Position #{$jIdx}");
                $cStr = !empty($jItem['company']) ? $jItem['company'] : "Company";
                if (preg_match($eduPattern, $rStr) || preg_match($eduPattern, $cStr)) {
                    continue;
                }
                $pStr = ($jItem['from'] ?? '') . ' - ' . ($jItem['to'] ?? '');
                $dur = ($jItem['years'] ?? 0) . ' Yrs ' . ($jItem['months'] ?? 0) . ' Mos';
                $wHist[] = [
                    'role' => $rStr,
                    'company' => $cStr,
                    'period' => $pStr,
                    'duration' => $dur
                ];
                $jIdx++;
            }
        }

        $expStr = (!empty($cl['ExpYrs']) && is_numeric($cl['ExpYrs']) && $cl['ExpYrs'] > 0) ? ($cl['ExpYrs'] . ' years of total professional experience') : 'hands-on experience';

        $analysisData['candidate_profile'] = [
            'headline' => !empty($cl['RoleSummary']) ? $cl['RoleSummary'] : (!empty($cl['JobTitle']) ? $cl['JobTitle'] : 'Candidate'),
            'current_role' => !empty($cl['RoleSummary']) ? $cl['RoleSummary'] : 'Not specified in resume',
            'current_company' => 'Not specified in resume',
            'summary' => (!empty($cl['Fullname']) ? $cl['Fullname'] : 'Candidate') . " with " . $expStr . (!empty($cl['MatchedSkills']) ? " and skills in " . $cl['MatchedSkills'] : '') . '.',
            'degree' => (!empty($cl['EducationMatch']) && $cl['EducationMatch'] !== 'Yes' && $cl['EducationMatch'] !== 'No') ? $cl['EducationMatch'] : 'Qualifications identified',
            'institution' => 'Not specified in resume',
            'grad_year' => 'Not specified in resume',
            'training' => 'Not specified in resume',
            'categorized_skills' => !empty($cl['MatchedSkills']) ? ['Core Skills' => $cl['MatchedSkills']] : [],
            'work_history' => $wHist,
            'projects' => []
        ];
    }

    $analysisJson = htmlspecialchars(
        json_encode($analysisData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ENT_QUOTES,
        'UTF-8'
    );
?>

    <span class="badge <?= $badgeClass ?> p-2">
        <?= htmlspecialchars($recommendation, ENT_QUOTES, 'UTF-8') ?>
    </span>

    <button type="button"
            class="btn btn-xs btn-outline-info btnScoreHelp d-block mt-1"
            title="View ATS Recommendation Analysis"
            data-analysis="<?= $analysisJson ?>">
        <i class="fas fa-search"></i> View Analysis
    </button>
</td>
<td class="align-middle">
    <div>
        <?php 
        $stVal = trim($cl['CurrentStatus'] ?? ''); 
        $stDisp = (strtoupper($stVal) === 'HR') ? 'Level 1' : $stVal;
        ?>
        <span class="font-weight-bold text-dark d-block"><?= htmlspecialchars($stDisp); ?></span>
        <?php if (!empty($cl['ResumePath'])): ?>
            <a href="<?= (strpos($cl['ResumePath'], 'http') === 0) ? htmlspecialchars($cl['ResumePath']) : base_url(htmlspecialchars($cl['ResumePath'])); ?>"
               download
               target="_blank"
               class="btn btn-xs btn-outline-primary shadow-sm font-weight-bold mt-1 d-inline-block"
               title="Download Candidate Resume">
                <i class="fas fa-download mr-1"></i>Resume
            </a>
        <?php endif; ?>
    </div>
</td>
                                    <td><?= $cl['AppliedOn'] ?></td>
<?php
$sourceVal  = trim($cl['Source'] ?? '');
$sourceLow  = strtolower($sourceVal);
if (in_array($sourceLow, ['online', 'portal', 'web'])) {
    $srcLabel = 'Walk-In';
    $srcClass = 'badge-warning';
    $srcIcon  = 'fa-walking';
} elseif (in_array($sourceLow, ['walk-in', 'walk_in', 'walkin', 'upload', 'manual', 'ats'])) {
    $srcLabel = 'Online';
    $srcClass = 'badge-info';
    $srcIcon  = 'fa-globe';
} else {
    $srcLabel = htmlspecialchars($sourceVal) ?: '—';
    $srcClass = 'badge-secondary';
    $srcIcon  = 'fa-question-circle';
}
?>
<td class="text-center">
  <span class="badge <?= $srcClass ?> p-2">
    <i class="fas <?= $srcIcon ?> mr-1"></i><?= $srcLabel ?>
  </span>
</td>
<td class="text-center text-nowrap">
<div class="d-inline-flex align-items-center justify-content-center" style="gap: 4px;">

<!-- View -->
<!-- <button type="button"
        class="btn btn-sm btn-success viewCandidateDetails"
        data-id="<?= $cl['CandidateId']; ?>">""
        title="View Candidate">
<i class="fas fa-eye"></i>
</button> -->
<button type="button"
        class="btn btn-sm btn-success viewCandidateDetails"
        data-id="<?= $cl['CandidateId']; ?>"
        title="View Candidate">
<i class="fas fa-eye"></i>
</button>


<!-- <button class="btn btn-sm btn-primary openCandidateStage"
        data-id="<?= $cl['CandidateId']; ?>"
        data-stage="<?= $cl['CurrentStageOrder'] ?? 1 ?>">
    <i class="fas fa-edit"></i>
</button> -->


<button class="btn btn-sm btn-primary openCandidateStage"
        data-id="<?= $cl['CandidateId']; ?>"
        data-stage="<?= $cl['CurrentStageOrder'] ?? 1 ?>"
        data-status="<?= htmlspecialchars($cl['CurrentStatus'] ?? '', ENT_QUOTES); ?>"
        title="Update Stage">
<i class="fas fa-edit"></i>
</button>

<button type="button"
        class="btn btn-sm btn-warning btn-candidate-360 ml-1"
        data-id="<?= $cl['CandidateId']; ?>"
        title="Candidate 360° Profile">
<i class="fas fa-user-circle"></i>
</button>


<?php 
$currentStatus = strtolower(trim($cl['CurrentStatus'] ?? ''));
$showOffer = false;
$showOnboarding = false;
$showHiring = false;
$isHired = false;

if (strpos($currentStatus, 'selected') !== false || 
    $currentStatus == 'offer pending' || 
    $currentStatus == 'offer accepted' || 
    $currentStatus == 'offer rejected' ||
    $currentStatus == 'offer released') {
    $showOffer = true;
}

if ($currentStatus == 'offer accepted') {
    $showOnboarding = true;
}

if ($currentStatus == 'on boarding') {
    $showHiring = true;
}

if (strpos($currentStatus, 'hired') !== false) {
    $isHired = true;
}

if ($showOffer): ?>
    <button type="button"
        class="btn btn-sm btn-info openOfferModal"
        data-id="<?= $cl['CandidateId']; ?>"
        title="Release Offer">
        <i class="fas fa-phone"></i>
    </button>
<?php endif; ?>

<?php if ($showOnboarding): ?>
    <button type="button"
        class="btn btn-sm btn-warning openOnboardingModal"
        data-id="<?= $cl['CandidateId']; ?>"
        title="Start Onboarding">
        <i class="fas fa-user-check"></i>
    </button>
<?php endif; ?>

<?php if ($showHiring): ?>
    <button type="button"
        class="btn btn-sm btn-success openHiringModal"
        data-id="<?= $cl['CandidateId']; ?>"
        title="Hiring">
        <i class="fas fa-user-plus"></i>
    </button>
<?php endif; ?>

<?php if ($isHired): ?>
    <span class="badge badge-success p-2">
        HIRED
    </span>
<?php endif; ?>


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
                  
      </div>
    </div>
  </section>

<div class="modal fade" id="candidateComparisonModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 92%;">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      
     
      <div class="modal-header text-white py-3 px-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
        <div>
          <h5 class="modal-title font-weight-bold mb-0">
            <i class="fas fa-balance-scale mr-2"></i> Side-by-Side Candidate Comparison Engine
          </h5>
          <small class="text-white-50">
            Vacancy: <span id="compJobCode" class="badge badge-warning text-dark font-weight-bold ml-1">JOB</span> 
            <span id="compJobTitle" class="font-weight-bold ml-1">Title</span>
          </small>
        </div>
        <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close" style="font-size:24px;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

     
      <div class="modal-body p-4 bg-light" id="candidateComparisonModalBody" style="max-height: 80vh; overflow-y: auto;">
        <div class="text-center py-5 text-muted">
          <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i>
          <h5 class="font-weight-bold text-dark mb-1">Generating Candidate Comparison Matrix...</h5>
          <p class="small text-muted mb-0">Comparing ATS fit match, competencies, experience, and extracted skills side-by-side.</p>
        </div>
      </div>

      
      <div class="modal-footer bg-white py-2 px-4 d-flex justify-content-between">
        <small class="text-muted"><i class="fas fa-robot text-primary mr-1"></i> Multi-Candidate Comparative Analytics Powered by HRMS AI Engine</small>
        <button type="button" class="btn btn-secondary btn-sm px-4 font-weight-bold" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
   



<div id="offerPanel" class="right-form">

    <div class="right-form-header">
        <h5>Offer Release</h5>
        <button type="button" class="close-btn" id="closeOfferPanel">&times;</button>
    </div>

    <div class="right-form-body">

        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Offer Information</h3>
            </div>

            <div class="card-body">

                <input type="hidden" id="offerCandidateId">

                <div class="form-group">
                    <label>Offer Issued Date</label>
                    <input type="date" class="form-control" id="offerDate">
                </div>

                <div class="form-group">
                    <label>Notice Period (Days)</label>
                    <input type="number" class="form-control" id="noticeDays">
                </div>

                <div class="form-group">
                    <label>Expected Joining Date</label>
                    <input type="text" class="form-control" id="expectedJoinDate">
                </div>
                 
                <div class="form-group">
    <label>Offer Status</label>
    <select class="form-control" id="offerStatus">
        <option value="">Select Status</option>
        <option value="Pending">Pending</option>
        <option value="Accepted">Accepted</option>
        <option value="Rejected">Rejected</option>
    </select>
</div>

                <div class="form-group">
                    <label>Remarks</label>
                    <textarea class="form-control" id="offerRemarks"></textarea>
                </div>

                <button class="btn btn-success" id="saveOffer">
                    Save
                </button>

            </div>
        </div>

    </div>

</div>
 <div id="hiringPanel" class="right-form">

    <div class="right-form-header">
        <h5>Hiring Details</h5>
        <button type="button" class="close-btn" id="closeHiringPanel">&times;</button>
    </div>

    <div class="right-form-body">

        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Employee Hiring Information</h3>
            </div>

            <div class="card-body">

                <input type="hidden" id="hiringCandidateId">

                <div class="form-group">
                    <label>Joining Date</label>
                    <input type="date" class="form-control" id="joiningDate">
                </div>

                <div class="form-group">
                    <label>Salary Offered</label>
                    <input type="number" class="form-control" id="salaryOffered">
                </div>

                <div class="form-group">
                    <label>Employment Type</label>
                    <input type="text" class="form-control" id="employmentType">
                </div>

                <div class="form-group">
                    <label>Work Location</label>
                    <input type="text" class="form-control" id="workLocation">
                </div>

                <div class="form-group">
                    <label>Remarks</label>
                    <textarea class="form-control" id="hiringRemarks"></textarea>
                </div>

                <button class="btn btn-success" id="saveHiring">
                    Save Hiring
                </button>

            </div>
        </div>

    </div>
</div>

 <div id="candidateStagePanel" class="right-form">

<div class="right-form-header">
<h5>Candidate Stage Update</h5>
<button type="button" class="close-btn" id="closeCandidateStage">&times;</button>
</div>

<div class="right-form-body">

<div class="card card-default">
<div class="card-header">
<h3 class="card-title">Stage Information</h3>
</div>

<div class="card-body">

<input type="hidden" id="stageCandidateId">

<div class="form-group" id="stageGroup">
<label>Stage</label>
<select id="stageId" class="form-control">
<option value="">Select Stage</option>
</select>
</div>

<div class="form-group" id="actionGroup">
    <label>Action</label>
    <select id="stageAction" class="form-control">
        <option value="">Select Action</option>
        <option value="Screened">Screened</option>
        <option value="Shortlisted">Shortlisted</option>
        <option value="Rejected">Rejected</option>
        <option value="On Hold">On Hold</option>
        <option value="Not Qualifed">Not Qualifed</option>
        <option value="Not Intrested">Not Intersted</option>
        <option value="Reschedule">Reschedule</option>
    </select>
</div>

<div class="shortlistedOnly" style="display:none">

  
    <div class="form-group">
        <label>Interview Schedule</label>
        <input type="date" id="interviewDate" class="form-control">
    </div>

   
    <div class="form-group">
        <label>Interview Mode </label>
        <select id="interviewType" class="form-control">
            <option value="">Select Type</option>
            <option value="Online">Online</option>
            <option value="Offline">Offline</option>
        </select>
    </div>

    <div class="onlineOnly" style="display:none">
        <div class="form-group">
            <label>Meeting Platform <span class="text-danger">*</span></label>
            <select id="meetingPlatform" name="meetingPlatform" class="form-control">
                <option value="Microsoft Teams">Microsoft Teams</option>
            </select>
        </div>

        <div class="form-group">
            <label>Teams Meeting Link <span class="text-danger">*</span></label>
            <input type="url" id="teamsMeetingLink" name="teamsMeetingLink" class="form-control" placeholder="Paste Teams meeting link here">
            <small class="form-text text-muted">Example: https://teams.microsoft.com/l/meetup-join/...</small>
        </div>
    </div>

   
    <div class="form-group">
        <label>Interview Level</label>
        <select id="interviewLevel" class="form-control">
            <option value="">Select Level</option>
        </select>
    </div>

 
    <div class="form-group">
        <label>Interviewer</label>
        <select id="interviewerId" class="form-control">
            <option value="">Select Interviewer</option>
            <?php foreach($this->db->get('IHUsers')->result_array() as $u){ ?>
            <option value="<?= $u['IUid'] ?>"><?= $u['EmpName'] ?></option>
            <?php } ?>
        </select>
    </div>

</div>

<div class="followupOnly" style="display:none">

<div class="form-group">
<label>Follow Up Type</label>
<select id="followupType" class="form-control">
<option value="">Select</option>
<option value="Whatsapp">Whatsapp</option>
<option value="Email">Email</option>
<option value="Message">Meeting</option>
<option value="Call">Call</option>
</select>
</div>

<div class="form-group">
<label>Next Follow Up Date</label>
<input type="datetime-local" id="nextFollowupDate" class="form-control">
</div>

</div>


<div class="form-group">
<label>Remarks</label>
<textarea id="stageRemarks" class="form-control"></textarea>
</div>

<button class="btn btn-success" id="saveCandidateStage">
Save
</button>

</div>
</div>

</div>
</div>



<div class="modal fade" id="candidateDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-route mr-2 text-info"></i>Candidate Details</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="candidateDetailsBody">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Candidate 360° Modal (New Separate Feature) -->
<div class="modal fade" id="candidate360Modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header text-white py-3 px-4" style="background: #0f172a; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.4);">
                        <i class="fas fa-user-astronaut text-primary" style="font-size: 16px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0 text-white" style="letter-spacing: -0.2px; font-size: 17px;">Candidate 360° Profile</h5>
                        <small class="text-white-50" style="font-size: 11px;">Complete Candidate Analytics & Evaluation Hub</small>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-75 hover-opacity-100" data-dismiss="modal" aria-label="Close" style="outline: none; font-size: 22px;">&times;</button>
            </div>
            <div class="modal-body p-3 bg-light" id="candidate360Body">
                <div class="text-center p-5">
                    <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 font-weight-bold text-dark">Loading Candidate 360° Profile...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="candidateupdateDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Candidate Followup Details</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" id="candidateupdateDetailsBody">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin"></i> Loading...
                </div>
            </div>

        </div>
    </div>
        
</div><div class="modal fade" id="vacancyDetailsModal" tabindex="-1">
 <div class="modal-dialog modal-lg">
  <div class="modal-content">

   <div class="modal-header">
    <h5 class="modal-title">Vacancy Details</h5>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
   </div>

   <div class="modal-body" id="vacancyDetailsBody">
    <div class="text-center p-5">
     <i class="fa fa-spinner fa-spin fa-2x"></i>
    </div>
   </div>

  </div>
 </div>
</div>  

<div id="vacancyOverlay"></div>


 <div id="onboardingPanel" class="right-form">

    <div class="right-form-header">
        <h5>Onboarding Update</h5>
        <button type="button" class="close-btn" id="closeOnboardingPanel">&times;</button>
    </div>

    <div class="right-form-body">

        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Onboarding Information</h3>
            </div>

            <div class="card-body">

                <input type="hidden" id="onboardCandidateId">

                <div class="form-group">
                    <label>Documents Submitted?</label>
                    <select id="documentsSubmitted" class="form-control">
                        <option value="">Select</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Remarks</label>
                    <textarea id="onboardRemarks" class="form-control"></textarea>
                </div>

                <button class="btn btn-success" id="saveOnboarding">
                    Save
                </button>

            </div>
        </div>

    </div>

</div>

<div class="modal fade" id="scoreBreakdownModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-check mr-2"></i>
                    ATS Recommendation Analysis
                </h5>

                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">
                    &times;
                </button>
            </div>

            <div class="modal-body" id="scoreBreakdownModalBody">
                <div class="text-center p-5">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>

        </div>
    </div>
</div>
