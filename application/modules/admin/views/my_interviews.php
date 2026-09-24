<?php
$employee_det = $this->session->userdata('logged_in');

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = $this->config->item('theme_locations').$this->config->item('active_template');

$next_interview = null;
$min_diff       = PHP_INT_MAX;
$now            = time();
$today_start    = strtotime('today');

if(!empty($Candidatelist)) {
    foreach($Candidatelist as $cl) {
        $resultLower = strtolower(trim($cl['Result'] ?? ''));
        if(($resultLower === 'assigned' || $resultLower === '') && !empty($cl['ScheduledAt']) && $cl['ScheduledAt'] !== '0000-00-00 00:00:00') {
            $scheduled_time = strtotime($cl['ScheduledAt']);

           
            if($scheduled_time >= $today_start && $scheduled_time > 0) {
                $diff = abs($scheduled_time - $now);
                if($diff < $min_diff) {
                    $min_diff       = $diff;
                    $next_interview = $cl;
                }
            }
        }
    }
}
?>
<section class="content">
<div class="container-fluid">

<div class="card card-warning card-outline">

<div class="card-header">
<div class="d-flex justify-content-between align-items-center">

<h3 class="card-title mb-0">
Assigned Interview
</h3>

</div>
</div>

<div class="card-body">

<?php if($next_interview): ?>
    <?php
    $interview_ts = strtotime($next_interview['ScheduledAt']);
    $today_date = new DateTime('today');
    $interview_date = new DateTime(date('Y-m-d', $interview_ts));
    $interval = $today_date->diff($interview_date);
    $days = (int)$interval->format('%r%a');
    if ($days < 0) {
        $days = 0;
    }
    $remaining_text = "Remaining: " . $days . " Day" . ($days === 1 ? "" : "s");
    ?>
    <div class="interview-summary-card">
        <div class="interview-summary-title">
            <i class="fas fa-calendar-check mr-2 text-primary"></i>Next Upcoming Interview
        </div>
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="interview-summary-item">
                    <span class="label">Candidate Name:</span>
                    <span class="value"><?= htmlspecialchars($next_interview['Fullname']) ?></span>
                </div>
                <div class="interview-summary-item">
                    <span class="label">Job Title:</span>
                    <span class="badge badge-primary px-2 py-1 font-weight-bold"><i class="fas fa-briefcase mr-1"></i><?= htmlspecialchars($next_interview['JobTitle'] ?? 'N/A') ?></span>
                </div>
                <?php if (!empty($next_interview['Role'])): ?>
                <div class="interview-summary-item">
                    <span class="label">Role:</span>
                    <span class="badge badge-info px-2 py-1 font-weight-bold"><i class="fas fa-user-tag mr-1"></i><?= htmlspecialchars($next_interview['Role']) ?></span>
                </div>
                <?php endif; ?>
                <div class="interview-summary-item">
                    <span class="label">Candidate Code:</span>
                    <span class="badge badge-secondary"><?= htmlspecialchars($next_interview['CandidateCode']) ?></span>
                </div>
                <div class="interview-summary-item">
                    <span class="label">Interview Date:</span>
                    <span class="value"><?= date('d-m-Y - h:i A', $interview_ts) ?></span>
                </div>
                <div class="interview-summary-item mt-1">
                    <span class="label">Interview Mode:</span>
                    <?php
                    $nextMode = !empty($next_interview['InterviewType']) ? trim($next_interview['InterviewType']) : 'N/A';
                    $nextLink = !empty($next_interview['MeetLink']) ? trim($next_interview['MeetLink']) : '';
                    if (strtolower($nextMode) === 'online'):
                    ?>
                        <span class="badge badge-primary"><i class="fas fa-video mr-1"></i>Online</span>
                        <?php if (!empty($nextLink)): ?>
                            <a href="<?= htmlspecialchars($nextLink) ?>" target="_blank" class="btn btn-sm btn-success ml-2 font-weight-bold"><i class="fas fa-video mr-1"></i>Join Teams Meeting</a>
                        <?php endif; ?>
                    <?php elseif (strtolower($nextMode) === 'offline'): ?>
                        <span class="badge badge-secondary"><i class="fas fa-building mr-1"></i>Offline (In-Person)</span>
                    <?php else: ?>
                        <span class="badge badge-light"><?= htmlspecialchars($nextMode) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <span class="badge badge-info interview-remaining-badge mb-2">
                    <i class="fas fa-hourglass-half mr-1"></i><?= $remaining_text ?>
                </span>
                <br>
                <button type="button" class="btn btn-sm btn-info viewCandidateDetails font-weight-bold mt-1" data-id="<?= $next_interview['CandidateId'] ?>">
                    <i class="fas fa-eye mr-1"></i>View Full Track
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card card-success card-outline mt-3">

<div class="card-header p-2">

<ul class="nav nav-pills" id="interviewStatusTabs">

<li class="nav-item">
<a class="nav-link interviewFilter active font-weight-bold" href="javascript:void(0);" data-status="">
<i class="fas fa-list mr-1"></i> All
</a>
</li>

<li class="nav-item">
<a class="nav-link interviewFilter font-weight-bold" href="javascript:void(0);" data-status="Assigned">
<i class="fas fa-user-check mr-1"></i> Assigned
</a>
</li>

<li class="nav-item">
<a class="nav-link interviewFilter font-weight-bold" href="javascript:void(0);" data-status="Selected">
<i class="fas fa-check-circle mr-1"></i> Selected
</a>
</li>

<li class="nav-item">
<a class="nav-link interviewFilter font-weight-bold" href="javascript:void(0);" data-status="Rejected">
<i class="fas fa-times-circle mr-1"></i> Rejected
</a>
</li>

</ul>

</div>

<div class="card-body table-responsive p-0">
<table id="example1" class="table table-bordered table-striped align-middle mb-0 table-full-width">

<thead class="bg-success text-white">
<tr>
<th class="w-auto">S.No</th>
<th>Code</th>
<th>Name</th>
<th>Job Title / Role</th>
<th>Mobile No</th>
<th>Email</th>
<th class="text-center">Score</th>
<th class="text-center">Mode</th>
<th>Scheduled Time</th>
<th class="text-center">Current Status</th>
<th>Verified On</th>
<th class="text-center">Action</th>
</tr>
</thead>

<tbody>

<?php if(!empty($Candidatelist)){ $i=1; foreach($Candidatelist as $cl){ 
    $resultVal   = !empty($cl['Result']) ? trim($cl['Result']) : 'Assigned';
    $resultLower = strtolower($resultVal);
    $isRescheduledRow = ($resultLower === 'rescheduled');
    $trClass = $isRescheduledRow ? 'class="rescheduled-row-bg"' : '';
?>

<tr <?= $trClass ?>>
<td class="text-center font-weight-bold"><?= $i++; ?></td>
<td>
  <a href="<?= base_url('admin/viewResume/'.$cl['CandidateId']); ?>" target="_blank" class="text-dark font-weight-bold">
    <?= $cl['CandidateCode']; ?>
  </a>
</td>

<td>
  <a href="javascript:void(0);" class="viewCandidateDetails text-dark font-weight-bold" data-id="<?= $cl['CandidateId']; ?>">
    <?= htmlspecialchars($cl['Fullname']); ?>
  </a>
</td>

<td>
  <div class="font-weight-bold text-dark mb-0"><?= !empty($cl['JobTitle']) ? htmlspecialchars($cl['JobTitle']) : 'N/A'; ?></div>
  <?php if (!empty($cl['Role'])): ?>
    <div class="small text-muted"><?= htmlspecialchars($cl['Role']); ?></div>
  <?php endif; ?>
</td>

<td>
  <span class="text-dark font-weight-bold"><?= htmlspecialchars($cl['PhoneNo']); ?></span>
</td>

<td>
  <span class="text-muted small"><?= htmlspecialchars($cl['Email']); ?></span>
</td>

<td class="text-center">
    <?php 
    $recVal = !empty($cl['ProfileMatchPer']) ? $cl['ProfileMatchPer'] : 'Potential Match';
    if ($recVal === 'Recommended') $recVal = 'Strong Match';
    if ($recVal === 'Review Required') $recVal = 'Potential Match';
    if ($recVal === 'Not Recommended') $recVal = 'Low Match';
    $badgeClass = (in_array($recVal, ['Strong Match', 'Strongly Match', 'Recommended'])) ? 'badge-success' : (in_array($recVal, ['Low Match', 'Not Recommended']) ? 'badge-danger' : 'badge-warning');
    ?>
    <span class="badge <?= $badgeClass ?> font-weight-bold px-2 py-1"><?= htmlspecialchars($recVal) ?></span>
</td>

<td class="text-center">
    <?php
    $mode = !empty($cl['InterviewType']) ? trim($cl['InterviewType']) : '';
    $meetLink = !empty($cl['MeetLink']) ? trim($cl['MeetLink']) : '';
    if (strtolower($mode) === 'online'):
    ?>
        <span class="badge badge-success px-2 py-1"><i class="fas fa-video mr-1"></i>Online</span>
        <?php if (!empty($meetLink) && !$isRescheduledRow): ?>
            <br><a href="<?= htmlspecialchars($meetLink) ?>" target="_blank" class="btn btn-xs btn-success mt-1 px-2 font-weight-bold" title="Join Teams Meeting"><i class="fas fa-video mr-1"></i>Join</a>
        <?php endif; ?>
    <?php elseif (strtolower($mode) === 'offline'): ?>
        <span class="badge badge-primary px-2 py-1"><i class="fas fa-building mr-1"></i>Offline</span>
    <?php else: ?>
        <span class="badge badge-light px-2 py-1"><?= !empty($mode) ? htmlspecialchars($mode) : 'N/A' ?></span>
    <?php endif; ?>
</td>

<td>
    <?php
    $scheduledAt = $cl['ScheduledAt'] ?? '';
    if (!empty($scheduledAt) && $scheduledAt !== '0000-00-00 00:00:00') {
        $ts = strtotime($scheduledAt);
        $dateFormatted = ($ts && $ts > 0) ? date('d-m-Y, h:i A', $ts) : '-';
        if ($isRescheduledRow) {
            echo '<del class="text-muted">' . $dateFormatted . '</del> <span class="badge badge-warning text-dark ml-1"><i class="fas fa-history mr-1"></i>Rescheduled</span>';
        } else {
            echo '<span class="text-dark font-weight-bold"><i class="fas fa-calendar-alt text-info mr-1"></i>' . $dateFormatted . '</span>';
        }
    } else {
        echo '<span class="text-muted small">Not Scheduled</span>';
    }
    ?>
</td>

<td class="text-center">
    <?php
    if ($isRescheduledRow) {
        echo '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-history mr-1"></i>Rescheduled</span>';
    } elseif ($resultLower === 'selected') {
        echo '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Selected</span>';
    } elseif ($resultLower === 'rejected') {
        echo '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Rejected</span>';
    } elseif ($resultLower === 'on hold') {
        echo '<span class="badge badge-warning px-2 py-1"><i class="fas fa-pause-circle mr-1"></i>On Hold</span>';
    } else {
        echo '<span class="badge badge-primary px-2 py-1"><i class="fas fa-clock mr-1"></i>' . htmlspecialchars($resultVal) . '</span>';
    }
    ?>
</td>

<td>
    <span class="small text-muted"><?= !empty($cl['AppliedOn']) ? date('d-m-Y, h:i A', strtotime($cl['AppliedOn'])) : '-'; ?></span>
</td>

<td class="text-center text-nowrap">
    <div class="d-inline-flex align-items-center justify-content-center" style="gap: 4px;">
      <button type="button" class="btn btn-xs btn-info viewCandidateDetails" data-id="<?= $cl['CandidateId']; ?>" title="View Candidate Track Timeline">
        <i class="fas fa-eye"></i>
      </button>
      <button type="button" class="btn btn-xs btn-warning btn-candidate-360" data-id="<?= $cl['CandidateId']; ?>" title="Candidate 360° Profile">
        <i class="fas fa-user-circle"></i>
      </button>
      <!-- <button type="button" class="btn btn-xs btn-primary openAiQuestionsModal" data-interview="<?= (int)($cl['InterviewId'] ?? 0); ?>" data-candidate="<?= htmlspecialchars($cl['Fullname'] ?? ''); ?>" data-job="<?= htmlspecialchars($cl['JobTitle'] ?? ''); ?>" data-role="<?= htmlspecialchars($cl['Role'] ?? ''); ?>" data-score="<?= htmlspecialchars($cl['ProfileMatchPer'] ?? 'N/A'); ?>" title="AI Personalized Interview Questions">
        <i class="fas fa-brain"></i> -->
      </button>
      <?php if(($resultLower == '' || $resultLower == 'assigned' || $resultLower == 'on hold') && !$isRescheduledRow): ?>
        <button type="button" class="btn btn-xs btn-warning openInterviewUpdate" data-interview="<?= $cl['InterviewId']; ?>" title="Update Interview Status">
          <i class="fas fa-edit"></i>
        </button>
      <?php endif; ?>
    </div>
</td>
</tr>

<?php }} ?>

</tbody>
</table>
</div>



<div id="interviewPanel" class="right-form" style="width: 420px; max-width: 90%;">

<div class="right-form-header bg-warning text-dark d-flex justify-content-between align-items-center p-3">
<h5 class="mb-0 font-weight-bold"><i class="fas fa-clipboard-check mr-2"></i>Update Interview Result</h5>
<button type="button" class="close-btn border-0 bg-transparent text-dark h4 mb-0" id="closeInterviewPanel">&times;</button>
</div>

<div class="right-form-body p-3" style="max-height: calc(100vh - 80px); overflow-y: auto;">

<input type="hidden" id="interviewId">

<div class="form-group mb-3">
<label class="font-weight-bold text-dark mb-1"><i class="fas fa-user-check text-primary mr-1"></i>Opinion / Decision <span class="text-danger">*</span></label>
<select id="interviewResult" class="form-control">
<option value="">Select Decision</option>
<option value="Selected">Shortlisted</option>
<option value="Rejected">Rejected</option>
<option value="On Hold">On Hold</option>
</select>
</div>

<hr class="my-3">

<h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-star text-warning mr-1"></i>Interview Evaluation Criteria (1-5)</h6>
<p class="small text-muted mb-3" style="font-size: 11.5px;">1 = Poor | 2 = Below Avg | 3 = Average | 4 = Good | 5 = Excellent</p>

<div class="form-group mb-2">
<label class="font-weight-bold text-secondary small mb-1">1. Technical & Role Skill <span class="text-danger">*</span></label>
<select id="evalSkillScore" class="form-control form-control-sm eval-score-select">
<option value="">Select Rating (1-5)</option>
<option value="1">1 - Poor</option>
<option value="2">2 - Below Average</option>
<option value="3">3 - Average</option>
<option value="4">4 - Good</option>
<option value="5">5 - Excellent</option>
</select>
</div>

<div class="form-group mb-2">
<label class="font-weight-bold text-secondary small mb-1">2. Communication Skill <span class="text-danger">*</span></label>
<select id="evalCommunicationScore" class="form-control form-control-sm eval-score-select">
<option value="">Select Rating (1-5)</option>
<option value="1">1 - Poor</option>
<option value="2">2 - Below Average</option>
<option value="3">3 - Average</option>
<option value="4">4 - Good</option>
<option value="5">5 - Excellent</option>
</select>
</div>

<div class="form-group mb-2">
<label class="font-weight-bold text-secondary small mb-1">3. Problem Solving & Aptitude <span class="text-danger">*</span></label>
<select id="evalProblemSolvingScore" class="form-control form-control-sm eval-score-select">
<option value="">Select Rating (1-5)</option>
<option value="1">1 - Poor</option>
<option value="2">2 - Below Average</option>
<option value="3">3 - Average</option>
<option value="4">4 - Good</option>
<option value="5">5 - Excellent</option>
</select>
</div>

<div class="form-group mb-2">
<label class="font-weight-bold text-secondary small mb-1">4. Culture Fit & Adaptability <span class="text-danger">*</span></label>
<select id="evalCultureFitScore" class="form-control form-control-sm eval-score-select">
<option value="">Select Rating (1-5)</option>
<option value="1">1 - Poor</option>
<option value="2">2 - Below Average</option>
<option value="3">3 - Average</option>
<option value="4">4 - Good</option>
<option value="5">5 - Excellent</option>
</select>
</div>

<div class="form-group mb-3">
<label class="font-weight-bold text-secondary small mb-1">5. Leadership & Initiative <span class="text-danger">*</span></label>
<select id="evalLeadershipScore" class="form-control form-control-sm eval-score-select">
<option value="">Select Rating (1-5)</option>
<option value="1">1 - Poor</option>
<option value="2">2 - Below Average</option>
<option value="3">3 - Average</option>
<option value="4">4 - Good</option>
<option value="5">5 - Excellent</option>
</select>
</div>

<div class="card border-primary mb-3 bg-light">
<div class="card-body p-2 text-center">
<small class="text-uppercase font-weight-bold text-muted d-block" style="letter-spacing: 0.5px;">Overall Score</small>
<div id="evalOverallDisplay" class="h6 font-weight-bold text-primary mb-0 mt-1">Select ratings above</div>
</div>
</div>

<div class="form-group mb-3">
<label class="font-weight-bold text-dark mb-1"><i class="fas fa-comment-alt text-info mr-1"></i>Interviewer Feedback</label>
<textarea id="interviewFeedback" class="form-control" rows="3" placeholder="Provide qualitative feedback on candidate performance..."></textarea>
</div>

<button class="btn btn-warning font-weight-bold btn-block shadow-sm" id="saveInterviewResult">
<i class="fas fa-save mr-1"></i> Save Evaluation Result
</button>

</div>
</div>

</div>
</div>
</div>
</section>



<div class="modal fade" id="candidateDetailsModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header bg-info text-white">
<h5 class="modal-title"><i class="fas fa-route mr-2"></i>Candidate Total Journey Track</h5>
<button type="button" class="close text-white" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body" id="candidateDetailsBody">
<div class="text-center">
<i class="fa fa-spinner fa-spin"></i> Loading...
</div>
</div>
</div>
</div>
</div>

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

<div class="modal fade" id="aiQuestionsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content modal-content-rounded-lg">
      
     
      <div class="modal-header text-white px-4 py-3 align-items-center bg-dark border-bottom-0">
        <div>
          <h5 class="modal-title font-weight-bold mb-0 text-white" id="aiModalTitle">
            <i class="fas fa-brain text-warning mr-2"></i> AI Personalized Interview Questions Engine
          </h5>
          <small class="text-white-50" id="aiModalSubtitle">Candidate-Tailored Competency & Non-Duplicative Assessment Set</small>
        </div>
        <button type="button" class="close text-white opacity-100 h4 mb-0" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" class="text-white">&times;</span>
        </button>
      </div>

     
      <div class="modal-body p-4" style="background:#f8fafc;">
 
        <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-white border rounded shadow-sm flex-wrap gap-2">
          <div class="d-flex align-items-center gap-3">
            <div class="p-2 bg-light border rounded text-center" style="min-width:45px;">
              <i class="fas fa-user-tie fa-2x text-primary"></i>
            </div>
            <div>
              <h5 class="font-weight-bold mb-1 text-dark" id="aiCandName">-</h5>
              <div class="mt-1 d-flex flex-wrap gap-2">
                <span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 12.5px; border-radius: 6px;">
                  <i class="fas fa-briefcase mr-1"></i>Job Title: <span id="aiCandJob">Job Title</span>
                </span>
                <span class="badge badge-info px-2 py-1 font-weight-bold" id="aiCandRoleWrap" style="font-size: 12.5px; border-radius: 6px; display:none;">
                  <i class="fas fa-user-tag mr-1"></i>Role: <span id="aiCandRole">Role</span>
                </span>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge badge-success px-3 py-2 font-weight-bold" id="aiSourceBadge" style="border-radius:12px; font-size:12px; display:none;">
              <i class="fas fa-robot mr-1"></i> Source: AI
            </span>
            <span class="badge badge-info px-3 py-2 font-weight-bold" id="aiAtsBadge" style="border-radius:12px; font-size:12px;">
              ATS Match: N/A
            </span>
            <div class="dropdown ml-2" id="aiVerDropdownWrap" style="display:none;">
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle font-weight-bold" type="button" id="aiVerBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Version 1
              </button>
              <div class="dropdown-menu dropdown-menu-right" id="aiVerMenu"></div>
            </div>
          </div>
        </div>


        <div id="aiSkillCoverageBar" class="mb-3 p-3 bg-white border rounded shadow-sm" style="display:none;">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="font-weight-bold text-dark style="font-size:13px;">
              <i class="fas fa-check-circle text-success mr-1"></i> Must-Have Skill Coverage:
            </div>
            <div id="aiSkillCoverageTags" class="d-flex flex-wrap gap-1"></div>
          </div>
          <div id="aiUncoveredWarnWrap" class="mt-2 text-danger small font-weight-bold" style="display:none;">
            <i class="fas fa-exclamation-triangle mr-1"></i> Uncovered Must-Have Skills: <span id="aiUncoveredTags"></span>
          </div>
        </div>

      
        <ul class="nav nav-pills mb-3 bg-white p-2 border rounded shadow-sm" id="aiCategoryTabs">
          <li class="nav-item">
            <a class="nav-link active font-weight-bold py-1 px-3 rounded-pill" href="javascript:void(0);" data-cat="all" id="tabCatAll">
              <i class="fas fa-layer-group mr-1"></i> All Questions
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link font-weight-bold py-1 px-3 rounded-pill" href="javascript:void(0);" data-cat="must_have_skill" id="tabCatMustHave">
              <i class="fas fa-star mr-1 text-warning"></i> Must-Have Skills
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link font-weight-bold py-1 px-3 rounded-pill" href="javascript:void(0);" data-cat="candidate_specific" id="tabCatCand">
              <i class="fas fa-user-check mr-1 text-success"></i> Candidate-Specific
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link font-weight-bold py-1 px-3 rounded-pill" href="javascript:void(0);" data-cat="scenario" id="tabCatScen">
              <i class="fas fa-lightbulb mr-1 text-info"></i> Scenario
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link font-weight-bold py-1 px-3 rounded-pill" href="javascript:void(0);" data-cat="behavioral" id="tabCatBeh">
              <i class="fas fa-users mr-1 text-purple" style="color:#7c3aed;"></i> Behavioral
            </a>
          </li>
        </ul>

   
        <div id="aiQuestionsList" style="min-height:220px;">
          <div class="text-center py-5 text-muted">
            <i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
            <p class="font-weight-bold mb-0">Loading candidate-personalized questions...</p>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-white px-4 py-3 justify-content-between">
        <div>
          <button type="button" id="btnRegenerateAi" class="btn btn-warning font-weight-bold px-3" style="border-radius:6px;">
            <i class="fas fa-sync-alt mr-1"></i> Regenerate Fresh Questions
          </button>
        </div>
        <div class="d-flex gap-2">
          <button type="button" id="btnPrintAiQuestions" class="btn btn-outline-secondary font-weight-bold px-3" style="border-radius:6px;">
            <i class="fas fa-print mr-1"></i> Print Question Sheet
          </button>
          <button type="button" class="btn btn-secondary font-weight-bold px-4 ml-2" data-dismiss="modal" style="border-radius:6px;">Close</button>
        </div>
      </div>

    </div>
  </div>
</div>
