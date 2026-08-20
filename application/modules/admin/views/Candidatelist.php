<?php
    $employee_det = $this->session->userdata('logged_in');
         
     if(empty($employee_det)) { redirect($this->config->item('base_url').'admin/index'); }
    $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');
    if(empty($jobdetails)) { $jobdetails = array('Jid' => '', 'JobCode' => ''); }

?>


<section class="content pt-3">
  <div class="container-fluid">

    <div class="card card-warning card-outline shadow-sm">

      <div class="card-header bg-white border-bottom-0 py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <h5 class="font-weight-bold text-dark mb-0 my-1 d-flex align-items-center">
            <i class="fas fa-user-tie text-primary mr-2"></i> Candidate Directory List
            <a href="javascript:void(0)" class="viewVacancyBtn badge badge-pill badge-warning text-dark ml-2" data-id="<?= $jobdetails['Jid']; ?>" style="font-size: 13.5px;">
              <?= $jobdetails['JobCode']; ?>
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
        <div class="mb-3">
          <ul class="nav nav-pills nav-pills-sm nav-justified mb-3">
            <li class="nav-item">
              <a class="nav-link active filterPill rounded-pill" data-status="">All</a>
            </li>
            <li class="nav-item">
              <a class="nav-link filterPill rounded-pill" data-status="CV Uploaded">CV Uploaded</a>
            </li>
            <li class="nav-item">
              <a class="nav-link filterPill rounded-pill" data-status="Selected">Selected</a>
            </li>
            <li class="nav-item">
              <a class="nav-link filterPill rounded-pill" data-status="On Hold">On Hold</a>
            </li>
            <li class="nav-item">
              <a class="nav-link filterPill rounded-pill" data-status="Rejected">Rejected</a>
            </li>
          </ul>
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

<script>

    var base_url = "<?= base_url(); ?>";


    

   $(document).on('click', '.editCandidateDetails', function () {

    let candidateId = $(this).data('id');
    console.log(candidateId);
    $('#candidateupdateDetailsModal').modal('show');
    $('#candidateupdateDetailsBody').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');

   
    });



function loadNextStages(currentOrder){

 $.post('<?= base_url("admin/getNextStages") ?>',{
    currentOrder : currentOrder
 },function(res){

    let stages = JSON.parse(res);
    let dropdown = $('#stageId');
    dropdown.html('<option value="">Select Stage</option>');

    stages.forEach(function(stage){
        dropdown.append(
            `<option value="${stage.StageId}" data-name="${stage.StageName}" data-group="${stage.StageGroup}">
                ${stage.StageName}
            </option>`
        );
    });

 });

}




$(document).on('click','.openCandidateStage',function(){

   let cid = $(this).data('id');
   let currentOrder = $(this).data('stage');   
   let status = ($(this).data('status') || '').toString().trim().toLowerCase();

   $('#stageCandidateId').val(cid);

   $('.shortlistedOnly, .followupOnly').hide();
   $('#interviewDate').val('');
   $('#stageAction').val('');
   $('#stageRemarks').val('');

   window.currentCandidateJobPanels = [];
   $.post('<?= base_url("admin/getCandidateInterviewPanelInfo") ?>', { candidateId: cid }, function(res) {
       try {
           let d = JSON.parse(res);
           if (d.status === 'success' && d.panels) {
               window.currentCandidateJobPanels = d.panels;
           }
       } catch(e) {}
   });

   if (status === 'cv uploaded' || status === 'uploaded') {
       $('#stageGroup').show();
       $('#actionGroup').hide();
       $('#stageAction option[value="Screened"]').show();
   } else {
       $('#stageGroup').hide();
       $('#actionGroup').show();

      
       if (status.includes('screen') || (status !== 'cv uploaded' && status !== 'uploaded' && status !== '')) {
           $('#stageAction option[value="Screened"]').hide();
       } else {
           $('#stageAction option[value="Screened"]').show();
       }
   }

   $('#candidateStagePanel').addClass('open');
   $('#vacancyOverlay').addClass('show');

   loadNextStages(currentOrder);   

});

$('#saveCvScreeningBtn').on('click', function() {
    var $btn = $(this);
    var originalText = $btn.html();
    var cid = $('#cvCandidateId').val();
    var action = $('#cvScreeningAction').val();
    var remarks = $('#cvScreeningRemarks').val();

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.post('<?= base_url("admin/saveCandidateStage") ?>', {
        candidateId: cid,
        action: action,
        remarks: remarks
    }, function(res) {
        var data;
        try {
            data = JSON.parse(res);
        } catch(e) {
            data = { status: 'error', msg: 'Invalid server response' };
        }

        if (data.status === 'success' || data.status === 'rejected') {
            toastr.success(data.msg || 'Candidate stage updated successfully.');
            $('#cvScreeningModal').modal('hide');
            setTimeout(function() {
                location.reload();
            }, 1000);
        } else {
            $btn.prop('disabled', false).html(originalText);
            toastr.error(data.msg || 'Error updating stage.');
        }
    }).fail(function() {
        $btn.prop('disabled', false).html(originalText);
        toastr.error('Network or server error.');
    });
});

$('#closeCandidateStage').on('click',function(){
  $('#candidateStagePanel').removeClass('open');
  $('#vacancyOverlay').removeClass('show');
});



$('#saveCandidateStage').on('click',function(){
    console.log('SAVE CLICKED');

    var $btn = $(this);
    var originalText = $btn.html();

    if ($('#stageGroup').is(':visible') && !$('#stageId').val()) {
        toastr.error("Please select stage.");
        return;
    }

    if ($('#actionGroup').is(':visible')) {
        var action = $('#stageAction').val();
        if (!action) {
            toastr.error("Please select action.");
            return;
        }

        if (action === 'Shortlisted' || action === 'Reschedule') {
            var intDate = $('#interviewDate').val();
            var intType = $('#interviewType').val();
            var intLevel = $('#interviewLevel').val();
            var interviewer = $('#interviewerId').val();

            if (!intDate) {
                toastr.error("Please select interview schedule date.");
                return;
            }

            if (!intType) {
                toastr.error("Please select interview mode.");
                return;
            }

            if (!intLevel) {
                toastr.error("Please select interview level.");
                return;
            }

            if (!interviewer) {
                toastr.error("Please select interviewer.");
                return;
            }
        }
    }

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.post('<?= base_url("admin/saveCandidateStage") ?>',{
        candidateId: $('#stageCandidateId').val(),
        stageId: $('#stageId').val(),
        action: $('#stageAction').val(),
        remarks: $('#stageRemarks').val(),

        followupType: $('#followupType').val(),
        nextFollowupDate: $('#nextFollowupDate').val(),

        interviewDate: $('#interviewDate').val(),
        interviewLevel: $('#interviewLevel').val(),
        interviewType: $('#interviewType').val(),
        interviewerId: $('#interviewerId').val()
    },function(res){
        console.log('Save response:', res);
        var data;
        try {
            data = JSON.parse(res);
        } catch (e) {
            data = { status: 'error', msg: 'Invalid server response' };
        }

        if (data.status === 'success' || data.status === 'rejected') {
            toastr.success(data.msg || 'Candidate stage updated successfully.');
            
          
            $('#candidateStagePanel').removeClass('open');
            $('#vacancyOverlay').removeClass('show');

            
            setTimeout(function() {
                location.reload();
            }, 1000);
        } else if (data.status === 'failed') {
           
            toastr.warning(data.msg || 'Candidate stage updated, but email notification failed.');
            
         
            $('#candidateStagePanel').removeClass('open');
            $('#vacancyOverlay').removeClass('show');

            setTimeout(function() {
                location.reload();
            }, 1500);
        } else {
          
            toastr.error(data.msg || 'Error updating candidate stage.');
            $btn.prop('disabled', false).html(originalText);
        }
    }).fail(function() {
        toastr.error('Server error. Please try again.');
        $btn.prop('disabled', false).html(originalText);
    });
});
 

/////
$(document).on('click', '.viewVacancyBtn', function () {
    let jid = $(this).data('id');
    $('#vacancyDetailsModal').modal('show');
    $('#vacancyDetailsBody').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
    $.post('<?= base_url("admin/getJobDetails") ?>', {jid: jid}, function (res) {
        let d = JSON.parse(res);
        let html = `<div class="container-fluid">`;
        html += `<div class="card card-primary collapsed-card"><div class="card-header bg-primary"><h3 class="card-title">Basic Information</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><div class="row"><div class="col-md-6"><p><b>Job Code:</b> ${d.JobCode}</p><p><b>Job Title:</b> ${d.JobTitle}</p><p><b>Department:</b> ${d.Departmentname}</p><p><b>Role:</b> ${d.RoleSummary}</p><p><b>Status:</b> ${d.JobStatus}</p></div><div class="col-md-6"><p><b>Posted By:</b> ${d.PostedByName}</p><p><b>Posted On:</b> ${d.PostedOn}</p><p><b>Expiry Date:</b> ${d.ExpiryDate}</p><p><b>Work Mode:</b> ${d.WorkMode}</p><p><b>Employment:</b> ${d.EmploymentType}</p><p><b>Language:</b> ${d.CommunicationLang}</p></div></div></div></div>`;
        html += `<div class="card card-info collapsed-card"><div class="card-header bg-info"><h3 class="card-title">Salary & Experience</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><div class="row"><div class="col-md-6"><p><b>Experience Required:</b> ${d.ExpMin ?? 0} - ${d.ExpMax ?? 0} Years</p></div><div class="col-md-6"><p><b>Salary Required:</b> ${d.SalMin ?? 0} - ${d.SalMax ?? 0} LPA</p></div></div></div></div>`;
        html += `<div class="card card-secondary collapsed-card"><div class="card-header bg-secondary"><h3 class="card-title">Location & Education</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><p><b>Job Location:</b> ${d.JobLocation}</p><p><b>Education Required:</b> ${d.EducationRequired}</p></div></div>`;
        html += `<div class="card card-warning collapsed-card"><div class="card-header bg-warning"><h3 class="card-title">Skills</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><p>${d.Skills}</p></div></div>`;
        html += `<div class="card card-dark collapsed-card"><div class="card-header bg-dark"><h3 class="card-title">Roles & Responsibilities</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body">${d.Responsibilities}</div></div>`;
        html += `<div class="card card-success collapsed-card"><div class="card-header bg-success"><h3 class="card-title">Job Description</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body">${d.JobDescription}</div></div>`;
        html += `</div>`;
        $('#vacancyDetailsBody').html(html);
    });
});

$(document).on('change','#stageId',function(){

  let option = $(this).find('option:selected');
  let stageName = option.data('name') || '';

  $('.screenedOnly').hide();
  $('.followupOnly').hide();
  
  let action = $('#stageAction').val();
  if (action !== 'Shortlisted' && action !== 'Reschedule') {
      $('#interviewDate').val('');
  }
  
  $('#followupType').val('');
  $('#nextFollowupDate').val('');

  if(stageName.toLowerCase() === 'switch off' || stageName.toLowerCase() === 'rnr'){
       $('.followupOnly').slideDown();
   }

});

$(document).on('change','#stageAction',function(){

 let action = $(this).val();

 
 $('.screenedOnly').hide();
 $('.shortlistedOnly').hide();

 
 if(action == "Shortlisted"){

     $('.screenedOnly').slideDown();     
     $('.shortlistedOnly').slideDown(); 

     loadInterviewLevels();            
 }

 // Handle Reschedule action
 if(action == "Reschedule"){
     $('.screenedOnly').slideDown();     
     $('.shortlistedOnly').slideDown(); 

     loadInterviewLevels();            
 }

});
$('#increaseLevel').on('click',function(e){
 e.preventDefault();

 let lvl = parseInt($('#interviewLevel').val());

 if(lvl < 4){
    $('#interviewLevel').val(lvl+1);
 }
});

function autoSelectInterviewerForLevel() {
    if (!window.currentCandidateJobPanels || window.currentCandidateJobPanels.length === 0) return;

    let val = $('#interviewLevel').val();
    let selectedText = $('#interviewLevel option:selected').text();
    let levelNum = parseInt(val) || 1;

    let match = selectedText.match(/level\s*(\d+)/i);
    if (match && match[1]) {
        levelNum = parseInt(match[1]);
    }

    let panel = window.currentCandidateJobPanels.find(p => parseInt(p.LevelOrder) === levelNum);
    if (panel && panel.InterviewerId) {
        $('#interviewerId').val(panel.InterviewerId);
    }
}

function loadInterviewLevels(){
   let ddl = $('#interviewLevel');
   ddl.html('<option value="">Select Level</option>');

   if (window.currentCandidateJobPanels && window.currentCandidateJobPanels.length > 0) {
       window.currentCandidateJobPanels.forEach(function(p) {
           let lvlOrder = p.LevelOrder || 1;
           ddl.append(`<option value="${lvlOrder}">Level ${lvlOrder}</option>`);
       });
   } else {
       ddl.append('<option value="1">Level 1</option>');
       ddl.append('<option value="2">Level 2</option>');
       ddl.append('<option value="3">Level 3</option>');
       ddl.append('<option value="4">Level 4</option>');
   }

   if (ddl.find('option').length > 1) {
       ddl.prop('selectedIndex', 1);
   }

   autoSelectInterviewerForLevel();
}

$(document).on('change', '#interviewLevel', function() {
    autoSelectInterviewerForLevel();
});


$(document).on('click', '[data-card-widget="collapse"]', function () {
 
    let currentCard = $(this).closest('.card');
 
    if (currentCard.hasClass('collapsed-card')) {
 
        
        $('.modal .card').not(currentCard).each(function () {
            if (!$(this).hasClass('collapsed-card')) {
                $(this).CardWidget('collapse');
            }
        });
 
    }
 
});
$(document).ready(function () {

    let jid = "<?= $jobdetails['Jid']; ?>";

    $(document).on('click', '.filterPill', function (e) {
        e.preventDefault();

        $('.filterPill').removeClass('active');
        $(this).addClass('active');

        let status = $(this).data('status');

        $.ajax({
            url: base_url + 'admin/filterCandidates',
            type: 'POST',
            data: { status: status, jid: jid },
            success: function (res) {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
                    $('#example1').DataTable().destroy();
                }
                $('#example1 tbody').html(res);
                if (typeof window.initCandidateDataTable === 'function') {
                    window.initCandidateDataTable();
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            error: function (xhr) {
                console.log('ERROR:', xhr.responseText);
            }
        });
    });

});
$(document).on('click', '.openOnboardingModal', function () {

    let cid = $(this).data('id');

    $('#onboardCandidateId').val(cid);
    $('#documentsSubmitted').val('');
    $('#onboardRemarks').val('');

   
    $('#onboardingPanel').addClass('open');
$('#vacancyOverlay').addClass('show');

});
$('#closeOnboardingPanel').on('click', function(){
    $('#onboardingPanel').removeClass('open');
    $('#vacancyOverlay').removeClass('show');
});
$(document).on('click', '#saveOnboarding', function () {
 console.log("SAVE CLICKED");

   
    let candidateId = $('#onboardCandidateId').val();
    let documents = $('#documentsSubmitted').val();
    let remarks = $('#onboardRemarks').val();

    if (documents === "") {
        toastr.error("Please select documents submitted status");
        return;
    }

    $.post('<?= base_url("admin/saveOnboarding") ?>', {
        candidateId: candidateId,
        documentsSubmitted: documents,
        remarks: remarks
    }, function (res) {

        toastr.success("Onboarding updated successfully");
       
        $('#onboardingPanel').removeClass('open');
$('#vacancyOverlay').removeClass('show');
        location.reload();

    });

});


$(document).on('click','.openOfferModal',function(){

    let cid = $(this).data('id');

    $('#offerCandidateId').val(cid);
    $('#offerDate').val('');
    $('#noticeDays').val('');
    $('#offerRemarks').val('');
    $('#expectedJoinDate').val('');
    $('#offerStatus').val('');

    $.ajax({
        url: base_url + "admin/getCandidateIdDetails",
        type: "POST",
        data: { candidate_id: cid },
        dataType: "json",
        success: function (res) {
            if (res.status === 'success' && res.data.offers && res.data.offers.length > 0) {
                let latestOffer = res.data.offers[res.data.offers.length - 1];
                if (latestOffer.OfferDate) {
                    $('#offerDate').val(latestOffer.OfferDate);
                }
                if (latestOffer.NoticePeriodDays) {
                    $('#noticeDays').val(latestOffer.NoticePeriodDays);
                }
                if (latestOffer.ExpectedJoiningDate) {
                    $('#expectedJoinDate').val(latestOffer.ExpectedJoiningDate);
                }
                if (latestOffer.OfferStatus) {
                    $('#offerStatus').val(latestOffer.OfferStatus);
                }
            }
        }
    });

    $('#offerPanel').addClass('open');
    $('#vacancyOverlay').addClass('show');
});

$('#closeOfferPanel').on('click',function(){
    $('#offerPanel').removeClass('open');
    $('#vacancyOverlay').removeClass('show');
});
$(document).on('click','#saveOffer',function(){

    let candidateId = $('#offerCandidateId').val();
    let offerDate   = $('#offerDate').val();
    let noticeDays  = $('#noticeDays').val();
    let remarks     = $('#offerRemarks').val();
       let status      = $('#offerStatus').val(); 

    if(offerDate === '' || noticeDays === ''){
        toastr.error("Please fill all required fields");
        return;
    }

    $.post('<?= base_url("admin/saveOffer") ?>',{
        candidateId : candidateId,
        offerDate   : offerDate,
        noticeDays  : noticeDays,
          offerStatus : status,
        remarks     : remarks
    },function(res){

        toastr.success("Offer saved successfully");

        $('#offerPanel').removeClass('open');
        $('#vacancyOverlay').removeClass('show');

        location.reload();
    });

});
$('#offerDate, #noticeDays').on('change keyup', function(){

    let offerDate = $('#offerDate').val();
    let days = parseInt($('#noticeDays').val());

    if(offerDate && days){

        let date = new Date(offerDate);
        date.setDate(date.getDate() + days);

        let yyyy = date.getFullYear();
        let mm = String(date.getMonth()+1).padStart(2,'0');
        let dd = String(date.getDate()).padStart(2,'0');

        $('#expectedJoinDate').val(yyyy + '-' + mm + '-' + dd);
    }
});


$(document).on('click', '.openHiringModal', function () {

    let candidateId = $(this).data('id');

    $('#hiringCandidateId').val(candidateId);

    $('#hiringPanel').addClass('open');
    $('#vacancyOverlay').addClass('show');

});



$('#closeHiringPanel').on('click', function () {

    $('#hiringPanel').removeClass('open');
    $('#vacancyOverlay').removeClass('show');

});



$(document).on('click', '#saveHiring', function () {

    let candidateId    = $('#hiringCandidateId').val();
    let joiningDate    = $('#joiningDate').val();
    let salaryOffered  = $('#salaryOffered').val();
    let employmentType = $('#employmentType').val();
    let workLocation   = $('#workLocation').val();
    let remarks        = $('#hiringRemarks').val();

    if(joiningDate == '' || salaryOffered == ''){
        toastr.error("Please fill required fields");
        return;
    }

    $.post('<?= base_url("admin/saveHiring") ?>', {

        candidateId     : candidateId,
        joiningDate     : joiningDate,
        salaryOffered   : salaryOffered,
        employmentType  : employmentType,
        workLocation    : workLocation,
        remarks         : remarks

    }, function (res) {

        console.log(res);

        toastr.success("Hiring completed successfully");

        $('#hiringPanel').removeClass('open');
        $('#vacancyOverlay').removeClass('show');
});

});

   
    function renderCandidate360(data) {
        let c = data.candidate || {};
        let stages = data.stages || [];
        let interviews = data.interviews || [];
        let panelScores = data.panelScores || [];
        let disagreements = data.disagreements || [];
        let panelSummary = data.panelSummary || null;
        let aiQuestions = data.aiQuestions || [];
        let offers = data.offers || [];
        let hiring = data.hiring || null;

        let sb = c.score_breakdown || {};
        let expDetails = c.experience_details || [];

        let mustHaveStr = c.VacancyMustHaveSkills || 'React, Node.js, MongoDB, REST API';
        let mustHaveList = mustHaveStr.split(',').map(s => s.trim()).filter(Boolean);
        let matchedSkillsStr = c.MatchedSkills || '';
        let matchedSkillsList = matchedSkillsStr.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);

        let mustHaveCovered = 0;
        mustHaveList.forEach(sk => {
            if (matchedSkillsList.includes(sk.toLowerCase())) mustHaveCovered++;
        });

        let atsMatchScore = c.ProfileMatchPer ? c.ProfileMatchPer : 'N/A';
        let panelScoreDisplay = panelSummary ? panelSummary.overall_avg + ' / 5 (' + panelSummary.overall_pct + '%)' : 'Not Evaluated';

        let candidateName = (c.Fullname && c.Fullname.trim() !== '' && c.Fullname !== 'N/A') ? c.Fullname : ((c.CandidateCode && c.CandidateCode.trim() !== '') ? c.CandidateCode : 'Candidate Profile');

        let atsBadgeBg = 'style="background: #3b82f6; color: #fff;"';
        if (atsMatchScore.includes('Strong')) {
            atsBadgeBg = 'style="background: #10b981; color: #fff;"';
        } else if (atsMatchScore.includes('Potential')) {
            atsBadgeBg = 'style="background: #f59e0b; color: #fff;"';
        } else if (atsMatchScore.includes('Low') || atsMatchScore.includes('Not')) {
            atsBadgeBg = 'style="background: #ef4444; color: #fff;"';
        }

        let html = `<div class="candidate-360-container font-sans" style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">`;

        html += `
        <div class="card border-0 shadow-sm mb-3 rounded-lg p-3 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div style="max-width: 65%;">
              <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge px-2 py-1 font-weight-bold text-uppercase" style="font-size: 10px; background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.3); border-radius: 4px; letter-spacing: 0.5px;">
                  <i class="fas fa-certificate mr-1"></i>Candidate 360° Profile
                </span>
                <span class="badge px-2 py-1 font-weight-bold" style="font-size: 11px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: #cbd5e1;">
                  <i class="fas fa-barcode mr-1 text-info"></i>${c.CandidateCode || 'N/A'}
                </span>
              </div>
              <h4 class="font-weight-bold mb-1 text-white" style="font-size: 22px; letter-spacing: -0.3px;">${candidateName}</h4>
              <div class="d-flex flex-wrap align-items-center text-white-50 small gap-3 mt-1" style="font-size: 12px; line-height: 1.6;">
                <span class="mr-3"><i class="fas fa-briefcase text-primary mr-1"></i><strong>${c.JobTitle || 'Vacancy'}</strong> <span class="text-white-50">(${c.JobCode || 'N/A'})</span></span>
                ${c.Email ? `<span class="mr-3"><i class="fas fa-envelope text-info mr-1"></i>${c.Email}</span>` : ''}
                ${c.PhoneNo ? `<span class="mr-3" title="${c.PhoneNo}"><i class="fas fa-phone text-success mr-1"></i>${c.PhoneNo}</span>` : ''}
              </div>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
              <div class="rounded px-3 py-2 text-center shadow-sm" style="min-width: 105px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                <small class="text-uppercase font-weight-bold d-block text-white-50 mb-1" style="font-size: 10px; letter-spacing: 0.5px;">ATS Fit</small>
                <span class="badge px-2 py-1 font-weight-bold" ${atsBadgeBg} style="font-size: 11px;">${atsMatchScore}</span>
              </div>
              <div class="rounded px-3 py-2 text-center shadow-sm" style="min-width: 115px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                <small class="text-uppercase font-weight-bold d-block text-white-50 mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Panel Score</small>
                <span class="h6 font-weight-bold text-success mb-0" style="font-size: 13px;">${panelScoreDisplay}</span>
              </div>
              <div class="rounded px-3 py-2 text-center shadow-sm" style="min-width: 105px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                <small class="text-uppercase font-weight-bold d-block text-white-50 mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Stage</small>
                <span class="badge badge-info font-weight-bold" style="font-size: 11px;">${c.CurrentStage || 'Applied'}</span>
              </div>
            </div>
          </div>
        </div>`;

        html += `
        <ul class="nav nav-pills nav-justified bg-white p-1 rounded-lg mb-3 border shadow-sm" id="c360TabNav" role="tablist" style="border-radius: 10px; border-color: #e2e8f0 !important;">
          <li class="nav-item"><a class="nav-link active font-weight-bold py-2 px-2 small rounded-lg" id="t-overview" data-toggle="pill" href="#c360-overview"><i class="fas fa-chart-pie mr-1"></i> Overview</a></li>
          <li class="nav-item"><a class="nav-link font-weight-bold py-2 px-2 small rounded-lg" id="t-timeline" data-toggle="pill" href="#c360-timeline"><i class="fas fa-history mr-1"></i> Timeline</a></li>
          <li class="nav-item"><a class="nav-link font-weight-bold py-2 px-2 small rounded-lg" id="t-ats" data-toggle="pill" href="#c360-ats"><i class="fas fa-robot mr-1"></i> ATS Analysis</a></li>
          <li class="nav-item"><a class="nav-link font-weight-bold py-2 px-2 small rounded-lg" id="t-skills" data-toggle="pill" href="#c360-skills"><i class="fas fa-tasks mr-1"></i> Skills</a></li>
          <li class="nav-item"><a class="nav-link font-weight-bold py-2 px-2 small rounded-lg" id="t-evaluations" data-toggle="pill" href="#c360-evaluations"><i class="fas fa-user-check mr-1"></i> Evaluations (${panelScores.length})</a></li>
          <li class="nav-item"><a class="nav-link font-weight-bold py-2 px-2 small rounded-lg" id="t-aiquestions" data-toggle="pill" href="#c360-aiquestions"><i class="fas fa-brain mr-1"></i> AI Qs (${aiQuestions.length})</a></li>
          <li class="nav-item"><a class="nav-link font-weight-bold py-2 px-2 small rounded-lg" id="t-resume" data-toggle="pill" href="#c360-resume"><i class="fas fa-file-alt mr-1"></i> Resume</a></li>
          <li class="nav-item"><a class="nav-link font-weight-bold py-2 px-2 small rounded-lg" id="t-offer" data-toggle="pill" href="#c360-offer"><i class="fas fa-handshake mr-1"></i> Offer</a></li>
          <li class="nav-item"><a class="nav-link font-weight-bold py-2 px-2 small rounded-lg text-dark" id="t-decision" data-toggle="pill" href="#c360-decision" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.4);"><i class="fas fa-balance-scale text-warning mr-1"></i> Decision</a></li>
        </ul>`;

        html += `<div class="tab-content border-0 p-1" id="c360TabContent">`;

        html += `
        <div class="tab-pane fade show active" id="c360-overview" role="tabpanel">
          <div class="row">
            <div class="col-md-5 mb-3">
              <div class="card h-100 border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="card-header text-white font-weight-bold py-2 px-3" style="background: #0f172a;"><i class="fas fa-chart-bar text-primary mr-2"></i>Performance Metrics</div>
                <div class="card-body p-3 bg-white">
                  <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted font-weight-bold small">ATS Fit Score:</span>
                    <span class="badge px-2 py-1 font-weight-bold" ${atsBadgeBg}>${atsMatchScore}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted font-weight-bold small">Interview Panel Avg:</span>
                    <span class="badge badge-success font-weight-bold px-2 py-1">${panelScoreDisplay}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted font-weight-bold small">Must-Have Skills Coverage:</span>
                    <span class="badge badge-info font-weight-bold px-2 py-1">${mustHaveCovered} / ${mustHaveList.length} (${mustHaveList.length > 0 ? Math.round((mustHaveCovered/mustHaveList.length)*100) : 0}%)</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted font-weight-bold small">Relevant Experience:</span>
                    <span class="text-dark font-weight-bold small">${c.ExpYrs ? c.ExpYrs + ' Years' : 'Not Available'}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted font-weight-bold small">Current Application Status:</span>
                    <span class="badge badge-dark font-weight-bold px-2 py-1">${c.CurrentStatus || 'Applied'}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-7 mb-3">
              <div class="card h-100 border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
                <div class="card-header text-white font-weight-bold py-2 px-3" style="background: #0f172a;"><i class="fas fa-bullseye text-warning mr-2"></i>Requirements & Evidence</div>
                <div class="card-body p-3 bg-white">
                  <h6 class="font-weight-bold text-dark mb-2 small text-uppercase text-muted" style="letter-spacing: 0.5px;">Must-Have Role Requirements</h6>
                  <div class="d-flex flex-wrap gap-2 mb-3">
                    ${mustHaveList.map(sk => {
                      let isMatched = matchedSkillsList.includes(sk.toLowerCase());
                      return isMatched 
                        ? `<span class="badge px-3 py-2 mr-1 mb-1 font-weight-bold" style="background: #e6f4ea; color: #137333; border: 1px solid #ceead6; border-radius: 20px; font-size: 11px;"><i class="fas fa-check-circle mr-1 text-success"></i>${sk}</span>`
                        : `<span class="badge px-3 py-2 mr-1 mb-1 font-weight-normal" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; border-radius: 20px; font-size: 11px;"><i class="fas fa-minus-circle mr-1 text-muted"></i>${sk}</span>`;
                    }).join('')}
                  </div>
                  <hr style="border-top: 1px dashed #e2e8f0;">
                  <h6 class="font-weight-bold text-dark mb-2 small text-uppercase text-muted" style="letter-spacing: 0.5px;">Interviewer Consensus Highlights</h6>
                  ${panelSummary ? `
                    <p class="small text-dark mb-1"><i class="fas fa-user-check text-success mr-2"></i>Evaluated by <strong>${panelSummary.eval_count}</strong> interviewer(s). Panel score average: <strong>${panelSummary.overall_avg} / 5 (${panelSummary.overall_pct}%)</strong>.</p>
                    ${disagreements.length > 0 ? `<div class="alert alert-warning py-1 px-2 small mb-0 mt-2" style="border-radius: 6px;"><i class="fas fa-exclamation-triangle mr-1"></i>Note: ${disagreements.length} score variance item(s) detected between interviewers. See Evaluations tab.</div>` : `<div class="alert alert-success py-1 px-2 small mb-0 mt-2" style="border-radius: 6px;"><i class="fas fa-check-circle mr-1"></i>Interviewers evaluated with high consistency across criteria.</div>`}
                  ` : `<p class="text-muted small mb-0"><i class="fas fa-info-circle mr-1"></i>No structured human interview evaluations saved yet.</p>`}
                </div>
              </div>
            </div>
          </div>
        </div>`;

       
        html += `
        <div class="tab-pane fade" id="c360-timeline" role="tabpanel">
          <div class="card border-0 shadow-sm p-3">
            <h5 class="font-weight-bold text-primary mb-3"><i class="fas fa-route mr-2 text-warning"></i>Complete Recruitment Journey Pipeline</h5>
            <div class="timeline timeline-inverse">`;
        if (stages.length > 0) {
          stages.forEach(s => {
            let badgeColor = 'bg-info';
            let act = (s.Action || '').toLowerCase();
            if (act.includes('rejected')) badgeColor = 'bg-danger';
            else if (act.includes('shortlisted') || act.includes('selected') || act.includes('hired')) badgeColor = 'bg-success';
            else if (act.includes('hold')) badgeColor = 'bg-warning';

            html += `
            <div>
              <i class="fas fa-user-tag ${badgeColor}"></i>
              <div class="timeline-item">
                <span class="time"><i class="far fa-clock mr-1"></i>${s.ActionAt || '-'}</span>
                <h3 class="timeline-header font-weight-bold text-primary">${s.StageName || 'Stage Update'}</h3>
                <div class="timeline-body">
                  <p class="mb-1"><strong>Action:</strong> <span class="badge ${badgeColor.replace('bg-', 'badge-')}">${s.Action || '-'}</span></p>
                  <p class="mb-1"><strong>Performed By:</strong> ${s.ActionByName || 'HR Admin'}</p>
                  <p class="mb-0"><strong>Remarks:</strong> ${s.Remarks || 'No remarks recorded'}</p>
                </div>
              </div>
            </div>`;
          });
        } else {
          html += `<div class="p-3 text-muted">No stage tracking events recorded yet.</div>`;
        }
        html += `</div></div></div>`;

        
        html += `
        <div class="tab-pane fade" id="c360-ats" role="tabpanel">
          <div class="card border-0 shadow-sm p-3">
            <h5 class="font-weight-bold text-primary mb-3"><i class="fas fa-robot mr-2 text-info"></i>ATS Screening Engine Breakdown</h5>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="border rounded p-3 bg-light">
                  <h6 class="font-weight-bold text-dark border-bottom pb-2">Must-Have Skills Audit</h6>
                  ${mustHaveList.map(sk => {
                    let isMatched = matchedSkillsList.includes(sk.toLowerCase());
                    return `<div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="font-weight-bold text-secondary">${sk}</span>
                      <span class="badge ${isMatched ? 'badge-success' : 'badge-danger'} px-2 py-1">${isMatched ? '✓ Matched' : '⚠ Missing / Weak'}</span>
                    </div>`;
                  }).join('')}
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="border rounded p-3 bg-light">
                  <h6 class="font-weight-bold text-dark border-bottom pb-2">Match Parameters</h6>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold text-muted">Experience Match:</span>
                    <span class="badge badge-info">${c.ExperienceMatch || 'Not Specified'}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold text-muted">Education Match:</span>
                    <span class="badge badge-info">${c.EducationMatch || 'Not Specified'}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold text-muted">ATS Fit Recommendation:</span>
                    <span class="badge badge-success">${c.ProfileMatchPer || 'Review Required'}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>`;

       
        html += `
        <div class="tab-pane fade" id="c360-skills" role="tabpanel">
          <div class="card border-0 shadow-sm p-3">
            <h5 class="font-weight-bold text-primary mb-3"><i class="fas fa-tasks mr-2 text-success"></i>Extracted Candidate Skills & Evidence</h5>
            <div class="table-responsive">
              <table class="table table-bordered table-striped align-middle">
                <thead class="bg-secondary text-white">
                  <tr>
                    <th>Skill Name</th>
                    <th>Category</th>
                    <th>Evidence Level</th>
                    <th>Project Context / Source</th>
                  </tr>
                </thead>
                <tbody>`;
        let allExtractedSkills = mustHaveList.concat((c.MatchedSkills || '').split(',').map(s=>s.trim()).filter(Boolean));
        let uniqueSkills = Array.from(new Set(allExtractedSkills));
        if (uniqueSkills.length > 0) {
          uniqueSkills.forEach(sk => {
            let isCovered = matchedSkillsList.includes(sk.toLowerCase());
            html += `
            <tr>
              <td class="font-weight-bold text-dark">${sk}</td>
              <td><span class="badge badge-light border">Role Keyword</span></td>
              <td><span class="badge ${isCovered ? 'badge-success' : 'badge-warning'} px-2 py-1">${isCovered ? '✓ Strong Evidence' : '⚠ Weak / Missing'}</span></td>
              <td class="small text-muted">${isCovered ? 'Extracted from Resume Experience & Projects' : 'Skill missing in uploaded resume text'}</td>
            </tr>`;
          });
        } else {
          html += `<tr><td colspan="4" class="text-center text-muted">No extracted skill evidence found.</td></tr>`;
        }
        html += `</tbody></table></div></div></div>`;

     
        html += `
        <div class="tab-pane fade" id="c360-evaluations" role="tabpanel">
          <div class="card border-0 shadow-sm p-3">
            <h5 class="font-weight-bold text-primary mb-3"><i class="fas fa-user-check mr-2 text-warning"></i>Interviewer Evaluation Reports & Panel Consensus</h5>`;

        if (panelSummary) {
          html += `
          <div class="card border-success bg-light mb-4 shadow-sm">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <h6 class="font-weight-bold text-success mb-1"><i class="fas fa-award mr-1"></i>Panel Consensus Summary (${panelSummary.eval_count} Interviewers)</h6>
                  <span class="text-muted small">Category averages across all structured human evaluations</span>
                </div>
                <div class="h5 font-weight-bold text-success mb-0 bg-white px-3 py-2 rounded border">
                  Panel Average: ${panelSummary.overall_avg} / 5 (${panelSummary.overall_pct}%)
                </div>
              </div>
              <div class="row mt-3 text-center">
                <div class="col"><small class="text-muted d-block">Skill</small><strong class="h6 text-dark">${panelSummary.avg_skill} / 5</strong></div>
                <div class="col"><small class="text-muted d-block">Communication</small><strong class="h6 text-dark">${panelSummary.avg_comm} / 5</strong></div>
                <div class="col"><small class="text-muted d-block">Problem Solving</small><strong class="h6 text-dark">${panelSummary.avg_prob} / 5</strong></div>
                <div class="col"><small class="text-muted d-block">Culture Fit</small><strong class="h6 text-dark">${panelSummary.avg_cult} / 5</strong></div>
                <div class="col"><small class="text-muted d-block">Leadership</small><strong class="h6 text-dark">${panelSummary.avg_lead} / 5</strong></div>
              </div>
            </div>
          </div>`;
        }

        if (disagreements.length > 0) {
          html += `
          <div class="alert alert-warning border-warning shadow-sm mb-4">
            <h6 class="font-weight-bold text-dark mb-1"><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Interviewer Score Discrepancies Detected</h6>
            <ul class="mb-0 pl-3 small text-dark">`;
          disagreements.forEach(d => {
            html += `<li><strong>${d.category}:</strong> Ratings range from ${d.min}/5 to ${d.max}/5 (${d.diff} point variance). Interviewers provided different assessments. HR review recommended.</li>`;
          });
          html += `</ul></div>`;
        }

        if (panelScores.length > 0) {
          html += `<h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-id-card mr-1"></i>Individual Interviewer Reports</h6><div class="row">`;
          panelScores.forEach((ps, idx) => {
            html += `
            <div class="col-md-6 mb-3">
              <div class="card h-100 border-secondary shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                  <span class="font-weight-bold">${ps.interviewer} <small class="text-white-50">(${ps.designation || 'Interviewer'})</small></span>
                  <span class="badge badge-warning text-dark font-weight-bold">${ps.round}</span>
                </div>
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Interviewer Decision:</span>
                    <span class="badge ${ps.result.toLowerCase().includes('selected') ? 'badge-success' : (ps.result.toLowerCase().includes('reject') ? 'badge-danger' : 'badge-warning')} font-weight-bold px-2 py-1">${ps.result}</span>
                  </div>
                  <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1"><span>Technical Skill:</span><strong>${ps.skill} / 5</strong></div>
                    <div class="d-flex justify-content-between small mb-1"><span>Communication:</span><strong>${ps.comm} / 5</strong></div>
                    <div class="d-flex justify-content-between small mb-1"><span>Problem Solving:</span><strong>${ps.prob} / 5</strong></div>
                    <div class="d-flex justify-content-between small mb-1"><span>Culture Fit:</span><strong>${ps.cult} / 5</strong></div>
                    <div class="d-flex justify-content-between small mb-1"><span>Leadership:</span><strong>${ps.lead} / 5</strong></div>
                  </div>
                  <div class="bg-light p-2 rounded text-center mb-2 border">
                    <small class="text-uppercase font-weight-bold text-muted d-block" style="font-size:10px;">Interviewer Overall</small>
                    <span class="h6 font-weight-bold text-primary mb-0">${ps.overall.toFixed(2)} / 5 (${Math.round((ps.overall/5)*100)}%)</span>
                  </div>
                  <p class="small text-muted mb-0"><strong>Feedback:</strong> <em>"${ps.feedback || 'No qualitative feedback recorded.'}"</em></p>
                </div>
              </div>
            </div>`;
          });
          html += `</div>`;

          if (panelScores.length >= 2) {
            html += `
            <h6 class="font-weight-bold text-dark mt-3 mb-2"><i class="fas fa-columns mr-1"></i>Interviewer Score Matrix</h6>
            <div class="table-responsive mb-3">
              <table class="table table-bordered table-sm text-center">
                <thead class="bg-primary text-white">
                  <tr>
                    <th class="text-left">Evaluation Criteria</th>
                    ${panelScores.map(ps => `<th>${ps.interviewer}</th>`).join('')}
                  </tr>
                </thead>
                <tbody>
                  <tr><td class="text-left font-weight-bold">Skill</td>${panelScores.map(ps => `<td>${ps.skill}/5</td>`).join('')}</tr>
                  <tr><td class="text-left font-weight-bold">Communication</td>${panelScores.map(ps => `<td>${ps.comm}/5</td>`).join('')}</tr>
                  <tr><td class="text-left font-weight-bold">Problem Solving</td>${panelScores.map(ps => `<td>${ps.prob}/5</td>`).join('')}</tr>
                  <tr><td class="text-left font-weight-bold">Culture Fit</td>${panelScores.map(ps => `<td>${ps.cult}/5</td>`).join('')}</tr>
                  <tr><td class="text-left font-weight-bold">Leadership</td>${panelScores.map(ps => `<td>${ps.lead}/5</td>`).join('')}</tr>
                  <tr class="bg-light font-weight-bold"><td class="text-left">Overall Score</td>${panelScores.map(ps => `<td class="text-primary">${Math.round((ps.overall/5)*100)}%</td>`).join('')}</tr>
                </tbody>
              </table>
            </div>`;
          }

        } else {
          html += `<div class="alert alert-secondary text-center py-4 mb-0"><i class="fas fa-info-circle mr-2"></i>No structured interviewer evaluations have been completed for this candidate yet.</div>`;
        }

        html += `</div></div>`;

        
        html += `
        <div class="tab-pane fade" id="c360-aiquestions" role="tabpanel">
          <div class="card border-0 shadow-sm p-3">
            <h5 class="font-weight-bold text-primary mb-3"><i class="fas fa-brain mr-2 text-purple"></i>AI Personalised Questions Audit</h5>`;
        if (aiQuestions.length > 0) {
          html += `
          <p class="small text-muted mb-3"><i class="fas fa-check-circle text-success mr-1"></i>Total <strong>${aiQuestions.length}</strong> AI personalized questions generated for candidate assessment.</p>
          <div class="list-group mb-3">`;
          aiQuestions.forEach((q, idx) => {
            html += `
            <div class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                <h6 class="mb-0 font-weight-bold text-dark">Q${idx+1}. ${q.question}</h6>
                <span class="badge badge-primary px-2 py-1">${q.question_type || 'General'}</span>
              </div>
              <p class="mb-1 small text-muted"><strong>Skill Assessed:</strong> <span class="badge badge-light border">${q.skill || 'Core'}</span> | <strong>Difficulty:</strong> ${q.difficulty || 'Medium'}</p>
              <small class="text-info"><strong>AI Rationale:</strong> ${q.reason || 'Personalized role assessment'}</small>
            </div>`;
          });
          html += `</div>`;
        } else {
          html += `<div class="p-3 text-muted">No AI interview questions generated for this candidate yet.</div>`;
        }
        html += `</div></div>`;

     
        html += `
        <div class="tab-pane fade" id="c360-resume" role="tabpanel">
          <div class="card border-0 shadow-sm p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="font-weight-bold text-primary mb-0"><i class="fas fa-file-alt mr-2"></i>Extracted Resume & Project History</h5>
              ${c.ResumePath ? `<a href="${base_url + c.ResumePath}" target="_blank" class="btn btn-sm btn-outline-primary font-weight-bold"><i class="fas fa-external-link-alt mr-1"></i>View Original Resume PDF</a>` : ''}
            </div>
            <div class="border rounded p-3 bg-light mb-3">
              <h6 class="font-weight-bold text-dark border-bottom pb-2">Candidate Experience Summary</h6>
              <p class="mb-1"><strong>Total Experience:</strong> ${c.ExpYrs ? c.ExpYrs + ' Years' : 'Not Specified'}</p>
              <p class="mb-1"><strong>Source:</strong> ${c.Source || 'Recruitment Portal'}</p>
              <p class="mb-0"><strong>Verified Date:</strong> ${c.VerifiedAt || '-'}</p>
            </div>
          </div>
        </div>`;

       
        html += `
        <div class="tab-pane fade" id="c360-offer" role="tabpanel">
          <div class="card border-0 shadow-sm p-3">
            <h5 class="font-weight-bold text-primary mb-3"><i class="fas fa-handshake mr-2 text-success"></i>Offer & Hiring Records</h5>`;
        if (offers.length > 0 || hiring) {
          if (offers.length > 0) {
            let of = offers[0];
            html += `
            <div class="border rounded p-3 bg-light mb-3">
              <h6 class="font-weight-bold text-success border-bottom pb-2">Candidate Offer Details</h6>
              <p class="mb-1"><strong>Offer Status:</strong> <span class="badge badge-success">${of.OfferStatus || 'Issued'}</span></p>
              <p class="mb-1"><strong>Offer Date:</strong> ${of.OfferDate || '-'}</p>
              <p class="mb-1"><strong>Expected Joining Date:</strong> ${of.ExpectedJoiningDate || '-'}</p>
              <p class="mb-0"><strong>Notice Period:</strong> ${of.NoticePeriodDays ? of.NoticePeriodDays + ' Days' : '-'}</p>
            </div>`;
          }
          if (hiring) {
            html += `
            <div class="border rounded p-3 bg-light">
              <h6 class="font-weight-bold text-primary border-bottom pb-2">Final Hiring Record</h6>
              <p class="mb-1"><strong>Salary Offered:</strong> ₹${hiring.SalaryOffered || '-'}</p>
              <p class="mb-1"><strong>Joining Date:</strong> ${hiring.JoiningDate || '-'}</p>
              <p class="mb-0"><strong>Hiring Remarks:</strong> ${hiring.Remarks || '-'}</p>
            </div>`;
          }
        } else {
          html += `<div class="alert alert-light text-center py-4 border"><i class="fas fa-info-circle mr-2 text-info"></i>No offer or hiring record created yet for this candidate.</div>`;
        }
        html += `</div></div>`;

       
        html += `
        <div class="tab-pane fade" id="c360-decision" role="tabpanel">
          <div class="card border-warning shadow-sm p-3">
            <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-balance-scale text-warning mr-2"></i>HR Final Hiring Decision & Advisory Executive Summary</h5>

            <div class="row mb-3">
              <div class="col-md-6 mb-2">
                <div class="border rounded p-3 bg-light">
                  <h6 class="font-weight-bold text-success mb-2"><i class="fas fa-thumbs-up mr-1"></i>Key Strengths</h6>
                  <ul class="mb-0 pl-3 small text-dark">
                    <li>Strong ATS role fit score (<strong>${atsMatchScore}</strong>)</li>
                    <li>Must-Have Skill coverage: <strong>${mustHaveCovered} / ${mustHaveList.length}</strong></li>
                    ${panelSummary ? `<li>Interview Panel Average score: <strong>${panelSummary.overall_avg} / 5 (${panelSummary.overall_pct}%)</strong></li>` : ''}
                  </ul>
                </div>
              </div>
              <div class="col-md-6 mb-2">
                <div class="border rounded p-3 bg-light">
                  <h6 class="font-weight-bold text-danger mb-2"><i class="fas fa-exclamation-circle mr-1"></i>Key Considerations</h6>
                  <ul class="mb-0 pl-3 small text-dark">
                    ${disagreements.length > 0 ? `<li>${disagreements.length} interviewer score discrepancy item(s) detected.</li>` : `<li>High consistency across interviewer evaluation scores.</li>`}
                    <li>Ensure expected joining date and notice period alignment.</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card border-info bg-light mb-4 shadow-sm">
              <div class="card-body p-3">
                <h6 class="font-weight-bold text-info mb-1"><i class="fas fa-robot mr-2"></i>AI Advisory Executive Summary <small class="text-muted">(Fact-Based Advisory Only)</small></h6>
                <p class="small text-dark mb-0">
                  "Candidate <strong>${c.Fullname || 'Candidate'}</strong> demonstrates technical alignment for the <strong>${c.JobTitle || 'vacancy'}</strong> position with an ATS match score of <strong>${atsMatchScore}</strong> and ${mustHaveCovered} of ${mustHaveList.length} must-have skills verified. ${panelSummary ? `Human interviewers evaluated the candidate with a panel score average of ${panelSummary.overall_avg}/5 (${panelSummary.overall_pct}%).` : 'Human interview evaluation pending.'} ${disagreements.length > 0 ? 'Note: Score variance exists between interviewers on certain soft skills and should be reviewed by HR before final decision.' : 'Interviewer evaluations are consistent across criteria.'}"
                </p>
              </div>
            </div>

            <div class="border rounded p-3 bg-white shadow-sm">
              <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-gavel text-primary mr-2"></i>Record HR Final Decision</h6>
              <input type="hidden" id="c360AppId" value="${c.ApplicationId || ''}">
              <div class="form-group mb-3">
                <label class="font-weight-bold small text-dark">Final Decision Option <span class="text-danger">*</span></label>
                <select id="c360DecisionVal" class="form-control">
                  <option value="">Select HR Decision</option>
                  <option value="Hire">Hire (Approve Candidate)</option>
                  <option value="Keep in Consideration">Keep in Consideration</option>
                  <option value="On Hold">On Hold</option>
                  <option value="Reject">Reject Candidate</option>
                </select>
              </div>
              <div class="form-group mb-3">
                <label class="font-weight-bold small text-dark">Decision Rationale & Remarks</label>
                <textarea id="c360Remarks" class="form-control" rows="3" placeholder="Enter HR rationale for final hiring decision..."></textarea>
              </div>
              <button type="button" class="btn btn-primary font-weight-bold btn-block shadow-sm" id="btnSaveC360Decision">
                <i class="fas fa-save mr-1"></i> Save Final Hiring Decision
              </button>
            </div>

          </div>
        </div>`;

        html += `</div></div>`;
        return html;
    }

   
    $(document).on('click', '.viewCandidateDetails', function () {
        let candidateId = $(this).data('id');

        $('#candidateDetailsModal').modal('show');
        $('#candidateDetailsBody').html(
            '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>'
        );

        $.ajax({
            url: base_url + "admin/getCandidateIdDetails",
            type: "POST",
            data: { candidate_id: candidateId },
            dataType: "json",
            success: function (res) {
                if (res.status !== 'success') {
                    $('#candidateDetailsBody').html('<div class="alert alert-danger">No data found</div>');
                    return;
                }

                let c = res.data.candidate;
                let stages = res.data.stages || [];
                let interviews = res.data.interviews || [];

                let html = `<div class="container-fluid">`;

                html += `
                <div class="card card-primary">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-id-card mr-2"></i>Basic Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Name:</strong> ${c.Fullname ?? '-'}</p>
                                <p class="mb-1"><strong>Job Title:</strong> <span class="badge badge-primary px-2 py-1 font-weight-bold"><i class="fas fa-briefcase mr-1"></i>${c.JobTitle ?? '-'}</span></p>
                                ${c.Role || c.RoleSummary ? `<p class="mb-1"><strong>Role:</strong> <span class="badge badge-info px-2 py-1 font-weight-bold"><i class="fas fa-user-tag mr-1"></i>${c.Role || c.RoleSummary}</span></p>` : ''}
                                <p class="mb-1"><strong>Email:</strong> ${c.Email ?? '-'}</p>
                                <p class="mb-1"><strong>Phone:</strong> ${c.PhoneNo ?? '-'}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Experience:</strong> ${c.ExpYrs ?? 0} Years</p>
                                <p class="mb-1"><strong>ATS Recommendation:</strong> <span class="badge badge-success">${c.ProfileMatchPer ?? 'Potential Match'}</span></p>
                                <p class="mb-1"><strong>Current Status:</strong> <span class="badge badge-info">${c.CurrentStatus ?? '-'}</span></p>
                            </div>
                        </div>
                    </div>
                </div>`;

                html += `<h5 class="mb-3 font-weight-bold text-dark"><i class="fas fa-route mr-2 text-info"></i>Stage Timeline Track</h5>`;
                html += `<div class="timeline timeline-inverse">`;

                if (stages.length > 0) {
                    stages.forEach(function (s) {
                        let badgeColor = 'bg-info';
                        let act = (s.Action || '').toLowerCase();
                        if (act.includes('rejected')) badgeColor = 'bg-danger';
                        else if (act.includes('shortlisted')) badgeColor = 'bg-success';
                        else if (act.includes('hold')) badgeColor = 'bg-warning';

                        html += `
                        <div>
                            <i class="fas fa-user ${badgeColor}"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> ${s.ActionAt ?? '-'}</span>
                                <h3 class="timeline-header">${s.StageName ?? 'Stage Update'}</h3>
                                <div class="timeline-body">
                                    <strong>Action:</strong> ${s.Action ?? '-'}<br>
                                    <strong>By:</strong> ${s.ActionByName ?? 'System'}<br>
                                    <strong>Remarks:</strong> ${s.Remarks ?? '-'}
                                </div>
                            </div>
                        </div>`;
                    });
                } else {
                    html += `
                    <div>
                        <i class="fas fa-info bg-secondary"></i>
                        <div class="timeline-item">
                            <div class="timeline-body">No stage tracking found</div>
                        </div>
                    </div>`;
                }

                html += `<div><i class="far fa-clock bg-gray"></i></div></div></div>`;

                $('#candidateDetailsBody').html(html);
            }
        });
    });

   
    $(document).on('click', '.btn-candidate-360', function (e) {
        e.preventDefault();
        let candidateId = $(this).data('id');

        $('#candidate360Modal').modal('show');
        $('#candidate360Body').html(
            '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x text-warning"></i><p class="mt-2 font-weight-bold text-dark">Loading Candidate 360° Profile...</p></div>'
        );

        $.ajax({
            url: base_url + "admin/getCandidate360Details",
            type: "POST",
            data: { candidate_id: candidateId },
            dataType: "json",
            success: function (res) {
                if (res.status !== 'success') {
                    $('#candidate360Body').html('<div class="alert alert-danger font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i>Unable to load Candidate 360° data.</div>');
                    return;
                }
                let html = renderCandidate360(res.data);
                $('#candidate360Body').html(html);
            },
            error: function (xhr) {
                console.log('Error loading candidate 360 details:', xhr.responseText);
                $('#candidate360Body').html('<div class="alert alert-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Unable to load Candidate 360° data. Please try again.</div>');
            }
        });
    });

    $(document).on('click', '#btnSaveC360Decision', function(e) {
        e.preventDefault();
        let appId = $('#c360AppId').val();
        let decision = $('#c360DecisionVal').val();
        let remarks = $('#c360Remarks').val();

        if (!appId || !decision) {
            if (typeof toastr !== 'undefined') toastr.error('Please select a Final Decision option.');
            else alert('Please select a Final Decision option.');
            return;
        }

        $.ajax({
            url: base_url + "admin/saveCandidate360Decision",
            type: "POST",
            data: { application_id: appId, decision: decision, remarks: remarks },
            dataType: "json",
            success: function(res) {
                if (res.status === 'success') {
                    if (typeof toastr !== 'undefined') toastr.success(res.msg);
                    else alert(res.msg);
                    $('#candidate360Modal').modal('hide');
                    location.reload();
                } else {
                    if (typeof toastr !== 'undefined') toastr.error(res.msg || 'Failed to save decision.');
                    else alert(res.msg || 'Failed to save decision.');
                }
            },
            error: function(xhr) {
                console.log('Error saving decision:', xhr.responseText);
            }
        });
    });
$(document).on('click', '.btnScoreHelp', function () {

    let btn = $(this);
    let raw = btn.attr('data-analysis') || '{}';
    let analysis = {};

    try {
        analysis = JSON.parse(raw);
    } catch (e) {
        console.error('ATS analysis parse error:', e);
        analysis = {};
    }

    let recommendation = analysis.recommendation || 'Potential Match';
    if (recommendation === 'Recommended') recommendation = 'Strong Match';
    if (recommendation === 'Review Required') recommendation = 'Potential Match';
    if (recommendation === 'Not Recommended') recommendation = 'Low Match';

    let reason = analysis.recommendation_reason || 'No recommendation reason is available.';
    let evidence = analysis.relevant_evidence || [];
    let missing = analysis.missing_requirements || [];
    let domain = analysis.domain || analysis.candidate_domain || 'Not identified';
    let candidateDomain = analysis.candidate_domain || analysis.domain || 'Not identified';
    let jobDomain = analysis.job_domain || 'Not identified';
    let domainStatus = (analysis.domain_status || 'UNCLEAR').toUpperCase();
    let domainAnalysis = analysis.domain_analysis || {};
    let matchedSkills = analysis.matched_skills || 'Not identified';
    let missingSkills = analysis.missing_skills || 'None identified';
    let detectedDegree = analysis.detected_degree || 'Not identified';
    let experience = analysis.experience || 'Not verified';

    let profile = analysis.candidate_profile || {};
    let headline = profile.headline || 'Candidate';
    let currentRole = profile.current_role || 'Not specified in resume';
    let currentCompany = profile.current_company || 'Not specified in resume';
    let degree = profile.degree || detectedDegree;
    let institution = profile.institution || 'Not specified in resume';
    let gradYear = profile.grad_year || 'Not specified in resume';
    let training = profile.training || 'Not specified in resume';
    let summary = profile.summary || 'Summary not available.';
    let categorizedSkills = profile.categorized_skills || {};
    let workHistory = profile.work_history || [];
    let projects = profile.projects || [];

    if (!Array.isArray(evidence)) evidence = [evidence];
    if (!Array.isArray(missing)) missing = [missing];

    let badgeClass = 'badge-secondary';
    if (recommendation === 'Strong Match' || recommendation === 'Strongly Match' || recommendation === 'Recommended') {
        badgeClass = 'badge-success';
    } else if (recommendation === 'Potential Match' || recommendation === 'Review Required') {
        badgeClass = 'badge-warning';
    } else if (recommendation === 'Low Match' || recommendation === 'Not Recommended' || recommendation === 'Not Suitable') {
        badgeClass = 'badge-danger';
    }

    
    let skillsCatHtml = '';
    if (Object.keys(categorizedSkills).length > 0) {
        for (let cat in categorizedSkills) {
            let skillsArr = categorizedSkills[cat].split(', ');
            let badgesStr = skillsArr.map(function(s) { return '<span class="badge badge-info mr-1 mb-1">' + s + '</span>'; }).join(' ');
            skillsCatHtml += '<div class="mb-2"><strong class="text-secondary">' + cat + ':</strong><br>' + badgesStr + '</div>';
        }
    } else {
        skillsCatHtml = '<p class="text-muted mb-0">No specific technical skills categorized.</p>';
    }

    
    let workHistHtml = '';
    if (workHistory.length > 0) {
        let seenWorkKeys = {};
        workHistory.forEach(function(w) {
            let roleStr = w.role || 'Role';
            let companyStr = w.company || 'Company';
            let key = roleStr.toLowerCase() + '|' + companyStr.toLowerCase() + '|' + (w.period || '');
            if (!seenWorkKeys[key]) {
                seenWorkKeys[key] = true;
                workHistHtml += '<li class="mb-2"><i class="fas fa-briefcase text-primary mr-2"></i><strong>' + roleStr + '</strong> — ' + companyStr + '<small class="text-muted d-block ml-4">' + w.period + ' (' + w.duration + ')</small></li>';
            }
        });
    } else {
        workHistHtml = '<li class="text-muted">No employment history extracted.</li>';
    }

    
    let projectsHtml = '';
    if (projects.length > 0) {
        projects.forEach(function(p) {
            let techStr = p.technology ? ' <span class="badge badge-light text-primary border ml-2" style="font-weight: 500;"><i class="fas fa-code text-info mr-1"></i>' + p.technology + '</span>' : '';
            projectsHtml += '<li class="mb-2"><i class="fas fa-folder-open text-info mr-2"></i><strong>' + p.title + '</strong>' + techStr + '</li>';
        });
    } else {
        projectsHtml = '<li class="text-muted">No explicit projects identified in resume.</li>';
    }

    
    let evidenceHtml = '';
    evidence.forEach(function(item) {
        if (item && item.toString().trim() !== '') {
            evidenceHtml += '<li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i>' + item + '</li>';
        }
    });
    if (evidenceHtml === '') evidenceHtml = '<li class="text-muted">No specific supporting evidence recorded.</li>';

    
    let missingHtml = '';
    missing.forEach(function(item) {
        if (item && item.toString().trim() !== '') {
            missingHtml += '<li class="mb-2"><i class="fas fa-exclamation-triangle text-warning mr-2"></i>' + item + '</li>';
        }
    });
    if (missingHtml === '') missingHtml = '<li class="text-success"><i class="fas fa-check-circle mr-2"></i>No major missing requirements identified.</li>';

    let domainWarningHtml = '';
    if (domainStatus === 'WRONG_DOMAIN') {
        domainWarningHtml = `
            <div class="callout callout-danger mb-4">
                <h5 class="text-danger font-weight-bold mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Wrong Domain Resume</h5>
                <p class="mb-2"><strong>Candidate Domain:</strong> ${candidateDomain}</p>
                <p class="mb-2"><strong>Job Domain:</strong> ${jobDomain}</p>
                <p class="mb-0 text-dark">Candidate's professional background is ${candidateDomain}, while this vacancy is ${jobDomain}.</p>
                <p class="mb-0 mt-2"><strong>Final Recommendation:</strong> <span class="badge badge-danger p-2">Not Suitable</span></p>
            </div>
        `;
    }

    let html = `
        <div class="container-fluid">
            ${domainWarningHtml}
            
            <!-- PART A: CANDIDATE PROFILE LAYER -->
            <div class="card card-outline card-info mb-4">
                <div class="card-header bg-light">
                    <h3 class="card-title text-info font-weight-bold mb-0">
                        <i class="fas fa-user mr-2"></i>PART A: CANDIDATE PROFILE OVERVIEW
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h4 class="font-weight-bold text-dark mb-1">${headline}</h4>
                            <p class="text-muted mb-2"><i class="fas fa-building mr-1"></i> ${currentRole} at ${currentCompany}</p>
                            <p class="mb-1"><strong><i class="fas fa-graduation-cap mr-1"></i> Education:</strong> ${degree} ${gradYear !== 'Not specified in resume' ? '(' + gradYear + ')' : ''}</p>
                            <p class="mb-1"><strong><i class="fas fa-university mr-1"></i> Institution:</strong> ${institution}</p>
                            <p class="mb-1"><strong><i class="fas fa-certificate mr-1"></i> Training / Specialization:</strong> ${training}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="callout callout-info">
                                <h5><i class="fas fa-file-alt mr-2"></i>Professional Summary</h5>
                                <p class="mb-0 text-dark">${summary}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary h-100">
                                <div class="card-header">
                                    <h5 class="card-title font-weight-bold"><i class="fas fa-layer-group mr-2"></i>Categorized Technical Stack</h5>
                                </div>
                                <div class="card-body">
                                    ${skillsCatHtml}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary h-100">
                                <div class="card-header">
                                    <h5 class="card-title font-weight-bold"><i class="fas fa-history mr-2"></i>Work History & Projects</h5>
                                </div>
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-secondary">Work History:</h6>
                                    <ul class="list-unstyled mb-3">${workHistHtml}</ul>
                                    <h6 class="font-weight-bold text-secondary">Projects:</h6>
                                    <ul class="list-unstyled mb-0">${projectsHtml}</ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PART B: EXISTING ATS JOB MATCH EVALUATION -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-header bg-light">
                    <h3 class="card-title text-primary font-weight-bold mb-0">
                        <i class="fas fa-tasks mr-2"></i>PART B: ATS VACANCY MATCH EVALUATION
                    </h3>
                </div>
                <div class="card-body">
                    <div class="callout callout-warning mb-3">
                        <h5 class="mb-2">
                            <strong>Final Recommendation:</strong> 
                            <span class="badge ${badgeClass} p-2 ml-2" style="font-size: 14px;">${recommendation}</span>
                        </h5>
                        <p class="mb-0 text-dark"><strong>Reason:</strong> ${reason}</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Candidate Domain:</strong> ${candidateDomain}</p>
                            <p class="mb-1"><strong>Job Domain:</strong> ${jobDomain}</p>
                            <p class="mb-1"><strong>Domain Status:</strong> ${domainStatus}</p>
                            <p class="mb-1"><strong>Experience Match:</strong> ${experience}</p>
                            <p class="mb-1"><strong>Education Match:</strong> ${detectedDegree}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Matched Core Skills:</strong> ${matchedSkills || 'None identified'}</p>
                            <p class="mb-1"><strong>Missing Core Skills:</strong> ${missingSkills || 'None identified'}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-success card-outline mb-0">
                                <div class="card-header">
                                    <h5 class="card-title text-success font-weight-bold"><i class="fas fa-check-circle mr-2"></i>Relevant Evidence</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">${evidenceHtml}</ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-warning card-outline mb-0">
                                <div class="card-header">
                                    <h5 class="card-title text-warning font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Missing / Needs Verification</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">${missingHtml}</ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    `;

    $('#scoreBreakdownModalBody').html(html);
    $('#scoreBreakdownModal').modal('show');
});
</script>
 
<script>
window.initCandidateDataTable = function() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
        $('#example1').DataTable().destroy();
    }
    if ($.fn.DataTable) {
        $('#example1').DataTable({
            "responsive": false,
            "autoWidth": false,
            "columnDefs": [
                { "orderable": false, "targets": [8] }
            ],
            "drawCallback": function() {
                if (typeof reapplyCheckboxStates === 'function') {
                    reapplyCheckboxStates();
                }
            }
        });
    }
};

$(document).ready(function() {
    setTimeout(function() {
        window.initCandidateDataTable();

        $(window).on('resize orientationchange', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
                $('#example1').DataTable().columns.adjust();
            }
        });
    }, 100);

  
    $(document).on('click', '.filterPill', function (e) {
        e.preventDefault();

        $('.filterPill').removeClass('active');
        $(this).addClass('active');

        let status = $(this).data('status');
        let jid = "<?= $jobdetails['Jid']; ?>";

        $.ajax({
            url: base_url + 'admin/filterCandidates',
            type: 'POST',
            data: { status: status, jid: jid },
            success: function (res) {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
                    $('#example1').DataTable().destroy();
                }
                $('#example1 tbody').html(res);
                if (typeof window.initCandidateDataTable === 'function') {
                    window.initCandidateDataTable();
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            error: function (xhr) {
                console.log('ERROR:', xhr.responseText);
            }
        });
    });
});
</script>

<script>

var selectedCandidateIds = new Set();

function updateCompareUI() {
    var count = selectedCandidateIds.size;
    $('#compareSelectedCount').text(count);

    if (count >= 2) {
        $('#btnCompareCandidates')
            .removeClass('btn-secondary disabled')
            .addClass('btn-primary')
            .css('cursor', 'pointer')
            .attr('title', 'Click to compare ' + count + ' selected candidates');
        $('#compareCountBadge')
            .removeClass('badge-light text-dark')
            .addClass('badge-primary text-white');
    } else {
        $('#btnCompareCandidates')
            .removeClass('btn-primary')
            .addClass('btn-secondary disabled')
            .css('cursor', 'not-allowed')
            .attr('title', 'Please select at least 2 candidates using the checkboxes below to compare');
        $('#compareCountBadge')
            .removeClass('badge-primary text-white')
            .addClass('badge-light text-dark');
    }
    syncSelectAllCheckbox();
}

function syncSelectAllCheckbox() {
    var visibleChks = $('.candidate-select-chk');
    if (visibleChks.length === 0) {
        $('#selectAllCandidates').prop('checked', false);
        $('#btnSelectAllCandidates').html('<i class="far fa-check-square mr-1"></i> Select All');
        return;
    }
    var allChecked = true;
    visibleChks.each(function() {
        if (!$(this).prop('checked')) {
            allChecked = false;
            return false;
        }
    });
    $('#selectAllCandidates').prop('checked', allChecked);
    if (allChecked) {
        $('#btnSelectAllCandidates').html('<i class="fas fa-check-square mr-1"></i> Deselect All');
    } else {
        $('#btnSelectAllCandidates').html('<i class="far fa-check-square mr-1"></i> Select All');
    }
}

function reapplyCheckboxStates() {
    if ($('#btnToggleCompareMode').hasClass('d-none')) {
        $('.chk-input').removeClass('d-none');
    } else {
        $('.chk-input').addClass('d-none');
    }
    $('.candidate-select-chk').each(function() {
        var cid = $(this).val();
        if (selectedCandidateIds.has(cid)) {
            $(this).prop('checked', true);
        } else {
            $(this).prop('checked', false);
        }
    });
    syncSelectAllCheckbox();
}

$(document).ready(function() {
  
    $(document).on('click', '#btnToggleCompareMode', function(e) {
        e.preventDefault();
        $('.chk-input').removeClass('d-none');
        $('#btnToggleCompareMode').addClass('d-none');
        $('#compareActiveBar').removeClass('d-none').addClass('d-flex');
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().columns.adjust();
        }
    });

 
    $(document).on('click', '#btnCancelCompareMode', function(e) {
        e.preventDefault();
        selectedCandidateIds.clear();
        $('.candidate-select-chk, #selectAllCandidates').prop('checked', false);
        updateCompareUI();
        $('.chk-input').addClass('d-none');
        $('#compareActiveBar').removeClass('d-flex').addClass('d-none');
        $('#btnToggleCompareMode').removeClass('d-none');
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().columns.adjust();
        }
    });

   
    $(document).on('click', '#btnSelectAllCandidates', function(e) {
        e.preventDefault();
        var visibleChks = $('.candidate-select-chk');
        if (visibleChks.length === 0) return;

        var allChecked = true;
        visibleChks.each(function() {
            if (!$(this).is(':checked')) {
                allChecked = false;
                return false;
            }
        });

        if (allChecked) {
            visibleChks.each(function() {
                var cid = $(this).val();
                $(this).prop('checked', false);
                selectedCandidateIds.delete(cid);
            });
        } else {
            visibleChks.each(function() {
                var cid = $(this).val();
                selectedCandidateIds.add(cid);
                $(this).prop('checked', true);
            });
        }
        updateCompareUI();
    });

    
    $(document).on('click change', '.candidate-select-chk', function(e) {
        var cid = $(this).val();
        if ($(this).is(':checked')) {
            selectedCandidateIds.add(cid);
        } else {
            selectedCandidateIds.delete(cid);
        }
        updateCompareUI();
    });

   
    $(document).on('click', '#selectAllCandidates', function(e) {
        e.stopPropagation();
    });

    $(document).on('change', '#selectAllCandidates', function(e) {
        var isChecked = $(this).is(':checked');
        var visibleChks = $('.candidate-select-chk');

        if (!isChecked) {
            visibleChks.each(function() {
                var cid = $(this).val();
                $(this).prop('checked', false);
                selectedCandidateIds.delete(cid);
            });
        } else {
            visibleChks.each(function() {
                var cid = $(this).val();
                selectedCandidateIds.add(cid);
                $(this).prop('checked', true);
            });
        }
        updateCompareUI();
    });


    $(document).on('click', '#btnCompareCandidates', function(e) {
        e.preventDefault();
        var selectedArray = Array.from(selectedCandidateIds);
        var vacancyId = $(this).data('vacancy-id') || '<?= $jobdetails['Jid']; ?>';

        if (selectedArray.length < 2) {
            if (typeof toastr !== 'undefined') {
                toastr.warning('Please select at least 2 candidates using the checkboxes below to compare.');
            } else {
                alert('Please select at least 2 candidates using the checkboxes below to compare.');
            }
            return false;
        }


        $('#candidateComparisonModalBody').html(`
          <div class="text-center py-5 text-muted">
            <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i>
            <h5 class="font-weight-bold text-dark mb-1">Generating Candidate Comparison Matrix...</h5>
            <p class="small text-muted mb-0">Comparing ATS fit match, competencies, experience, and extracted skills side-by-side.</p>
          </div>
        `);
        $('#candidateComparisonModal').modal('show');


        $.ajax({
            url: '<?= base_url('admin/compareCandidates'); ?>',
            type: 'POST',
            data: {
                candidate_ids: selectedArray,
                vacancy_id: vacancyId
            },
            success: function(rawRes) {
                var res;
                try { res = (typeof rawRes === 'object') ? rawRes : JSON.parse(rawRes); }
                catch(err) {
                    $('#candidateComparisonModalBody').html('<div class="alert alert-danger font-weight-bold">Error parsing comparison response.</div>');
                    return;
                }
                if (res.status === 'success') {
                    renderComparisonView(res);
                } else {
                    $('#candidateComparisonModalBody').html('<div class="alert alert-danger font-weight-bold">' + (res.message || 'Failed to compare candidates.') + '</div>');
                }
            },
            error: function(xhr) {
                $('#candidateComparisonModalBody').html('<div class="alert alert-danger font-weight-bold">HTTP Error ' + xhr.status + ': Failed to connect to server.</div>');
            }
        });
    });

    $(document).ajaxComplete(function() {
        setTimeout(function() {
            reapplyCheckboxStates();
        }, 50);
    });
});

function renderComparisonView(res) {
    if (!res || res.status !== 'success' || !res.candidates || res.candidates.length === 0) {
        $('#candidateComparisonModalBody').html('<div class="alert alert-danger font-weight-bold">Failed to load candidate comparison details.</div>');
        return;
    }

    $('#compJobCode').text(res.job_code || 'JOB');
    $('#compJobTitle').text(res.job_title || 'Vacancy');

    var cList = res.candidates;
    var colWidth = Math.max(18, Math.floor(82 / cList.length));

    var html = '';

    if (res.ai_summary) {
        var ai = res.ai_summary;
        var leadingBadgeCls = (ai.top_choice && ai.top_choice.indexOf('No Suitable') !== -1) ? 'badge-danger' : 'badge-success';
        var leadingIcon = (ai.top_choice && ai.top_choice.indexOf('No Suitable') !== -1) ? 'fa-exclamation-triangle' : 'fa-trophy';
        html += `
        <div class="card border-0 shadow-sm mb-4" style="border-radius:10px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-left: 5px solid #0284c7 !important;">
          <div class="card-body p-3">
            <div class="d-flex align-items-center mb-2 flex-wrap gap-2">
              <span class="badge ${leadingBadgeCls} px-3 py-2 font-weight-bold mr-2" style="font-size:13.5px;">
                <i class="fas ${leadingIcon} mr-1"></i> Leading Fit: ${ai.top_choice}
              </span>
              <span class="text-muted small font-weight-bold"><i class="fas fa-robot text-primary mr-1"></i> AI Executive Differentiator Summary</span>
            </div>
            <p class="text-dark font-weight-bold mb-2" style="font-size:14px; line-height:1.4;">${ai.recommendation}</p>
            <div class="row">
              ${ai.differentiators ? ai.differentiators.map(function(d) {
                  var isNotMatch = d.toLowerCase().indexOf('not suitable') !== -1 || d.toLowerCase().indexOf('mismatch') !== -1;
                  var isWarning = d.toLowerCase().indexOf('matches 0/') !== -1 || d.toLowerCase().indexOf('missing:') !== -1;
                  var icon = isNotMatch ? '<i class="fas fa-times-circle text-danger mr-1"></i>' : (isWarning ? '<i class="fas fa-exclamation-triangle text-warning mr-1"></i>' : '<i class="fas fa-check-circle text-info mr-1"></i>');
                  return '<div class="col-md-6 mb-1 text-muted small">' + icon + d + '</div>';
              }).join('') : ''}
            </div>
          </div>
        </div>`;
    }

    var tableMinWidth = Math.max(750, (cList.length * 240) + 180);

    html += `
    <div class="table-responsive bg-white rounded shadow-sm border p-2">
      <table class="table table-bordered align-middle mb-0 comparison-matrix-table" style="table-layout: fixed; min-width: ${tableMinWidth}px;">
        <thead>
          <tr>
            <th style="width: 18%; vertical-align: middle; background: #f1f5f9 !important; background-image: none !important; color: #0f172a !important;" class="font-weight-bold comparison-metrics-header">Comparison Metrics</th>
            ${cList.map(function(c) {
              var init = c.fullname ? c.fullname.charAt(0).toUpperCase() : 'C';
              return `
              <th style="width: ${colWidth}%; vertical-align: top; background-color: #ffffff !important; color: #1e293b !important;" class="text-center comparison-candidate-cell">
                <div class="p-2">
                  <div class="avatar-circle mx-auto mb-2 bg-primary text-white font-weight-bold rounded-circle d-flex align-items-center justify-content-center" style="width:46px; height:46px; margin:0 auto; font-size:18px; line-height:46px; box-shadow: 0 4px 10px rgba(59,130,246,0.3);">
                    ${init}
                  </div>
                  <h6 class="font-weight-bold mb-1 text-truncate" style="font-size:15px; color:#1e3a8a !important;" title="${c.fullname}">${c.fullname}</h6>
                  <span class="badge badge-pill badge-primary font-weight-bold mb-1 px-2 py-1" style="font-size:11px;">${c.candidate_code}</span>
                  <div class="small font-weight-bold text-truncate mt-1" style="color:#334155 !important;"><i class="fas fa-envelope text-primary mr-1"></i>${c.email}</div>
                  <div class="small font-weight-bold mt-1" style="color:#334155 !important;"><i class="fas fa-phone text-success mr-1"></i>${c.phone}</div>
                </div>
              </th>`;
            }).join('')}
          </tr>
        </thead>
        <tbody>
          <!-- Row 1: ATS Recommendation & Match Score -->
          <tr>
            <td class="font-weight-bold" style="background-color: #f8fafc !important; color: #1e293b !important;"><i class="fas fa-chart-line text-info mr-2"></i>ATS Fit Match</td>
            ${cList.map(function(c) {
                var badgeCls = 'badge-primary';
                var scoreText = c.ats_score || 'N/A';
                var sLower = String(scoreText).toLowerCase();

                if (sLower.indexOf('strong') !== -1) {
                    badgeCls = 'badge-success';
                } else if (sLower.indexOf('potential') !== -1 || sLower.indexOf('review') !== -1) {
                    badgeCls = 'badge-warning';
                } else if (sLower.indexOf('not suitable') !== -1 || sLower.indexOf('wrong domain') !== -1 || sLower.indexOf('mismatch') !== -1 || sLower.indexOf('not recommended') !== -1 || sLower.indexOf('low') !== -1) {
                    badgeCls = 'badge-danger';
                } else if (!isNaN(parseFloat(scoreText))) {
                    var val = parseFloat(scoreText);
                    if (val < 40) badgeCls = 'badge-danger';
                    else if (val < 75) badgeCls = 'badge-warning';
                    else badgeCls = 'badge-success';
                    scoreText = val.toFixed(0) + '%';
                }
                return `
                <td class="text-center" style="background-color: #ffffff !important;">
                  <span class="badge ${badgeCls} px-3 py-2 font-weight-bold" style="font-size:13px;">${scoreText}</span>
                </td>`;
            }).join('')}
          </tr>

          <!-- Row 2: Total Experience -->
          <tr>
            <td class="font-weight-bold" style="background-color: #f8fafc !important; color: #1e293b !important;"><i class="fas fa-briefcase text-primary mr-2"></i>Total Experience</td>
            ${cList.map(function(c) {
              var expTot = (c.experience_details && c.experience_details.total) ? ' (' + c.experience_details.total + ')' : '';
              return `
              <td class="text-center font-weight-bold" style="background-color: #ffffff !important; color: #1e293b !important;">
                ${c.experience_years} Years${expTot}
              </td>`;
            }).join('')}
          </tr>

          <!-- Row 3: Experience & Education Match -->
          <tr>
            <td class="font-weight-bold" style="background-color: #f8fafc !important; color: #1e293b !important;"><i class="fas fa-user-graduate text-success mr-2"></i>Requirement Fit</td>
            ${cList.map(function(c) {
              var expB = (c.experience_match === 'Yes') ? 'badge-success' : 'badge-secondary';
              var eduB = (c.education_match === 'Yes') ? 'badge-success' : 'badge-secondary';
              return `
              <td class="text-center small" style="background-color: #ffffff !important; color: #1e293b !important;">
                <div>Experience Match: <span class="badge ${expB}">${c.experience_match}</span></div>
                <div class="mt-1">Education Match: <span class="badge ${eduB}">${c.education_match}</span></div>
              </td>`;
            }).join('')}
          </tr>

          <!-- Row 4: Must-Have Skills Match Matrix -->
          <tr>
            <td class="font-weight-bold" style="background-color: #f8fafc !important; color: #1e293b !important;"><i class="fas fa-star text-warning mr-2"></i>Must-Have Skills</td>
            ${cList.map(function(c) {
                var mHtml = '';
                if (c.matched_must_have && c.matched_must_have.length > 0) {
                    c.matched_must_have.forEach(function(s) {
                        mHtml += '<span class="badge badge-success p-1 font-weight-bold mr-1 mb-1" style="font-size:11px;"><i class="fas fa-check mr-1"></i>' + s + '</span>';
                    });
                }
                if (c.missing_must_have && c.missing_must_have.length > 0) {
                    c.missing_must_have.forEach(function(s) {
                        mHtml += '<span class="badge badge-warning text-dark p-1 font-weight-bold mr-1 mb-1" style="font-size:11px;"><i class="fas fa-exclamation-triangle mr-1"></i>' + s + '</span>';
                    });
                }
                return '<td class="text-center" style="background-color: #ffffff !important;">' + (mHtml || '<span class="text-muted small">None evaluated</span>') + '</td>';
            }).join('')}
          </tr>

          <!-- Row 5: Extracted Resume Skills -->
          <tr>
            <td class="font-weight-bold" style="background-color: #f8fafc !important; color: #1e293b !important;"><i class="fas fa-tags text-purple mr-2" style="color:#7c3aed;"></i>Extracted Skills</td>
            ${cList.map(function(c) {
                var sHtml = '';
                if (c.all_candidate_skills && c.all_candidate_skills.length > 0) {
                    c.all_candidate_skills.slice(0, 6).forEach(function(s) {
                        sHtml += '<span class="badge badge-light border text-primary p-1 font-weight-bold mr-1 mb-1" style="font-size:10.5px;">' + s + '</span>';
                    });
                } else {
                    sHtml = '<span class="text-muted small">None listed</span>';
                }
                return '<td class="text-center" style="background-color: #ffffff !important;">' + sHtml + '</td>';
            }).join('')}
          </tr>

          <!-- Row 6: Quick Action Column -->
          <tr>
            <td class="font-weight-bold" style="background-color: #f8fafc !important; color: #1e293b !important;"><i class="fas fa-cogs text-secondary mr-2"></i>Candidate Actions</td>
            ${cList.map(function(c) {
                var resBtn = c.resume_path ? '<a href="<?= base_url(); ?>' + c.resume_path + '" target="_blank" class="btn btn-xs btn-outline-warning font-weight-bold mb-1"><i class="fas fa-file-pdf mr-1"></i> Resume</a>' : '';
                return `
                <td class="text-center" style="background-color: #ffffff !important;">
                  <button type="button" class="btn btn-xs btn-info viewCandidateSimple mr-1 mb-1" data-id="${c.candidate_id}">
                    <i class="fas fa-eye mr-1"></i> Profile
                  </button>
                  ${resBtn}
                </td>`;
            }).join('')}
          </tr>
        </tbody>
      </table>
    </div>`;

    $('#candidateComparisonModalBody').html(html);
}
</script>