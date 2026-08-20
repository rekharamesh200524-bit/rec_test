<?php
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
                            <a href="<?= htmlspecialchars($nextLink) ?>" target="_blank" class="btn btn-sm btn-success ml-2 font-weight-bold"><i class="fas fa-video mr-1"></i>Join Video Meeting</a>
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
            <a href="<?= htmlspecialchars($meetLink) ?>" target="_blank" class="btn btn-xs btn-outline-success ml-1" title="Join Video Meeting"><i class="fas fa-video mr-1"></i>Join</a>
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

<script>

var base_url = "<?= base_url(); ?>";
var interviewTable = null;

function initInterviewDataTable() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
        $('#example1').DataTable().destroy();
    }
    if ($.fn.DataTable) {
        interviewTable = $('#example1').DataTable({
            responsive: false,
            autoWidth: false,
            columnDefs: [
                { orderable: false, targets: 0 }
            ]
        });
    }
}

$(document).ready(function () {

    setTimeout(function () {
        initInterviewDataTable();
    }, 100);

    $(window).on('resize orientationchange', function () {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().columns.adjust();
        }
    });

    $(document).on('click', '.interviewFilter', function (e) {
        e.preventDefault();

        $('.interviewFilter').removeClass('active');
        $(this).addClass('active');

        let status = $(this).data('status');

        $.ajax({
            url: "<?= base_url('admin/filterAssignedInterviews') ?>",
            type: "POST",
            data: { status: status },
            success: function (res) {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
                    $('#example1').DataTable().destroy();
                }
                $('#example1 tbody').html(res);
                initInterviewDataTable();
            },
            error: function (xhr) {
                console.log('ERROR:', xhr.responseText);
            }
        });
    });
    function calculateOverallScore() {
        var s1 = parseInt($('#evalSkillScore').val()) || 0;
        var s2 = parseInt($('#evalCommunicationScore').val()) || 0;
        var s3 = parseInt($('#evalProblemSolvingScore').val()) || 0;
        var s4 = parseInt($('#evalCultureFitScore').val()) || 0;
        var s5 = parseInt($('#evalLeadershipScore').val()) || 0;

        if (s1 > 0 && s2 > 0 && s3 > 0 && s4 > 0 && s5 > 0) {
            var avg = (s1 + s2 + s3 + s4 + s5) / 5.0;
            var pct = Math.round((avg / 5.0) * 100);
            $('#evalOverallDisplay').html('<span class="h5 font-weight-bold text-primary mb-0">' + avg.toFixed(2) + ' / 5</span> <span class="badge badge-success ml-2 px-2 py-1" style="font-size:12px;">' + pct + '%</span>');
        } else {
            $('#evalOverallDisplay').html('<span class="text-muted font-weight-normal" style="font-size:12px;">Select all 5 criteria to calculate</span>');
        }
    }

    $(document).on('change', '.eval-score-select', function() {
        calculateOverallScore();
    });

    $(document).on('click', '#saveInterviewResult', function (e) {
        e.preventDefault();

        let interviewId          = $('#interviewId').val();
        let result               = $('#interviewResult').val();
        let feedback             = $('#interviewFeedback').val();
        let skillScore           = $('#evalSkillScore').val();
        let communicationScore   = $('#evalCommunicationScore').val();
        let problemSolvingScore  = $('#evalProblemSolvingScore').val();
        let cultureFitScore      = $('#evalCultureFitScore').val();
        let leadershipScore      = $('#evalLeadershipScore').val();

        if (!result) {
            toastr.error('Please select an Opinion / Decision');
            return;
        }

        if (!skillScore || !communicationScore || !problemSolvingScore || !cultureFitScore || !leadershipScore) {
            toastr.warning('Please provide ratings for all interview evaluation criteria.');
            return;
        }

        let $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: "<?= base_url('admin/updateInterviewResult') ?>",
            type: "POST",
            dataType: "json",
            data: {
                interviewId:          interviewId,
                result:               result,
                feedback:             feedback,
                skillScore:          skillScore,
                communicationScore:  communicationScore,
                problemSolvingScore: problemSolvingScore,
                cultureFitScore:     cultureFitScore,
                leadershipScore:     leadershipScore
            },
            success: function (r) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Evaluation Result');

                if (r.status === 'success') {
                    toastr.success('Interview evaluation saved successfully!');
                    $('#interviewPanel').hide();
                    location.reload();
                } else {
                    toastr.error(r.msg || 'Error saving interview evaluation');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Evaluation Result');
                toastr.error('An error occurred while saving evaluation. Please try again.');
            }
        });
    });

    $(document).on('click', '.openInterviewUpdate', function () {
        let interviewId = $(this).data('interview');

        $('#interviewId').val(interviewId);
        $('#interviewResult').val('');
        $('#interviewFeedback').val('');
        $('#evalSkillScore').val('');
        $('#evalCommunicationScore').val('');
        $('#evalProblemSolvingScore').val('');
        $('#evalCultureFitScore').val('');
        $('#evalLeadershipScore').val('');
        $('#evalOverallDisplay').html('<span class="text-muted font-weight-normal" style="font-size:12px;">Loading...</span>');

        $('#interviewPanel').show();

        $.ajax({
            url: "<?= base_url('admin/getInterviewDetails') ?>",
            type: "POST",
            dataType: "json",
            data: { interviewId: interviewId },
            success: function(res) {
                if (res.status === 'success' && res.data) {
                    var d = res.data;
                    if (d.Result) $('#interviewResult').val(d.Result);
                    if (d.Feedback) $('#interviewFeedback').val(d.Feedback);
                    if (d.SkillScore) $('#evalSkillScore').val(d.SkillScore);
                    if (d.CommunicationScore) $('#evalCommunicationScore').val(d.CommunicationScore);
                    if (d.ProblemSolvingScore) $('#evalProblemSolvingScore').val(d.ProblemSolvingScore);
                    if (d.CultureFitScore) $('#evalCultureFitScore').val(d.CultureFitScore);
                    if (d.LeadershipScore) $('#evalLeadershipScore').val(d.LeadershipScore);
                    calculateOverallScore();
                } else {
                    calculateOverallScore();
                }
            },
            error: function() {
                calculateOverallScore();
            }
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
            url: "<?= base_url('admin/getCandidateIdDetails') ?>",
            type: "POST",
            data: { candidate_id: candidateId },
            dataType: "json",
            success: function (res) {
                if (res.status !== 'success') {
                    $('#candidateDetailsBody').html('<div class="alert alert-danger">No candidate data found</div>');
                    return;
                }

                let c = res.data.candidate;
                let stages = res.data.stages || [];
                let interviews = res.data.interviews || [];

                let html = `<div class="container-fluid">`;

                html += `
                <div class="card card-primary card-outline mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-id-card mr-2"></i>Candidate Profile & Position Info</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Name:</strong> ${c.Fullname ?? '-'}</p>
                                <p class="mb-1"><strong>Candidate Code:</strong> <span class="badge badge-secondary">${c.CandidateCode ?? '-'}</span></p>
                                <p class="mb-1"><strong>Job Title:</strong> <span class="badge badge-primary px-2 py-1 font-weight-bold"><i class="fas fa-briefcase mr-1"></i>${c.JobTitle ?? '-'}</span></p>
                                ${c.Role || c.RoleSummary ? `<p class="mb-1"><strong>Role:</strong> <span class="badge badge-info px-2 py-1 font-weight-bold"><i class="fas fa-user-tag mr-1"></i>${c.Role || c.RoleSummary}</span></p>` : ''}
                                <p class="mb-1"><strong>Email:</strong> ${c.Email ?? '-'}</p>
                                <p class="mb-1"><strong>Phone:</strong> ${c.PhoneNo ?? '-'}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Experience:</strong> ${c.ExpYrs ?? 0} Years</p>
                                <p class="mb-1"><strong>ATS Recommendation:</strong> <span class="badge badge-success">${c.ProfileMatchPer ?? 'Potential Match'}</span></p>
                                <p class="mb-1"><strong>Current Status:</strong> <span class="badge badge-info">${c.CurrentStatus ?? '-'}</span></p>
                                <p class="mb-1"><strong>Applied Date:</strong> ${c.AppliedOn ?? '-'}</p>
                            </div>
                        </div>
                    </div>
                </div>`;

                html += `<h4 class="mb-3 font-weight-bold text-dark"><i class="fas fa-route mr-2 text-warning"></i>Candidate Total Journey Track</h4>`;
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
                            <i class="fas fa-user-tag ${badgeColor}"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> ${s.ActionAt ?? '-'}</span>
                                <h3 class="timeline-header font-weight-bold text-primary">${s.StageName ?? 'Stage Update'}</h3>
                                <div class="timeline-body">
                                    <p class="mb-1"><strong>Action:</strong> <span class="badge ${badgeColor.replace('bg-', 'badge-')}">${s.Action ?? '-'}</span></p>
                                    <p class="mb-1"><strong>Updated By:</strong> ${s.ActionByName ?? 'HR Admin'}</p>
                                    <p class="mb-0"><strong>Remarks:</strong> ${s.Remarks ?? 'No remarks provided'}</p>
                                </div>
                            </div>
                        </div>`;
                    });
                }

                if (interviews.length > 0) {
                    interviews.forEach(function(iv) {
                        let ivMode = (iv.InterviewType || 'N/A');
                        let isOnline = ivMode.toLowerCase() === 'online';
                        let meetBtn = (isOnline && iv.MeetLink) 
                            ? `<br><a href="${iv.MeetLink}" target="_blank" class="btn btn-xs btn-primary mt-2"><i class="fas fa-video mr-1"></i>Join Video Meeting</a>` 
                            : '';

                        html += `
                        <div>
                            <i class="fas fa-calendar-check bg-warning"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="far fa-clock"></i> ${iv.ScheduledAt ?? '-'}</span>
                                <h3 class="timeline-header font-weight-bold text-dark">Interview Round ${iv.InterviewRound ?? 1} (${ivMode})</h3>
                                <div class="timeline-body">
                                    <p class="mb-1"><strong>Mode:</strong> ${ivMode}</p>
                                    <p class="mb-1"><strong>Scheduled Time:</strong> ${iv.ScheduledAt ?? '-'}</p>
                                    <p class="mb-1"><strong>Result / Status:</strong> <span class="badge badge-warning">${iv.Result || 'Assigned'}</span></p>
                                    ${meetBtn}
                                </div>
                            </div>
                        </div>`;
                    });
                }

                if (stages.length === 0 && interviews.length === 0) {
                    html += `<p class="text-muted p-2">No stage tracking or interview history found for this candidate.</p>`;
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
            url: "<?= base_url('admin/getCandidate360Details') ?>",
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

   
    let activeInterviewId = null;
    let loadedQuestions = [];

    function updateSkillCoverageAndSource(res) {
        if (res.source) {
            const isAi = (res.source === 'ai');
            $('#aiSourceBadge')
                .toggleClass('badge-success', isAi)
                .toggleClass('badge-secondary', !isAi)
                .html(`<i class="fas ${isAi ? 'fa-robot' : 'fa-cog'} mr-1"></i> Source: ${isAi ? 'AI Engine' : 'Fallback Engine'}`)
                .show();
        } else {
            $('#aiSourceBadge').hide();
        }

        const covered   = res.covered_must_have_skills || [];
        const uncovered = res.uncovered_must_have_skills || [];

        if (covered.length > 0 || uncovered.length > 0) {
            let tagsHtml = '';
            covered.forEach(s => {
                tagsHtml += `<span class="badge badge-success px-2 py-1 font-weight-bold mr-1"><i class="fas fa-check mr-1"></i>${s}</span>`;
            });
            $('#aiSkillCoverageTags').html(tagsHtml || '<span class="text-muted small">None specified</span>');

            if (uncovered.length > 0) {
                let unTags = '';
                uncovered.forEach(s => {
                    unTags += `<span class="badge badge-danger px-2 py-1 font-weight-bold mr-1">${s}</span>`;
                });
                $('#aiUncoveredTags').html(unTags);
                $('#aiUncoveredWarnWrap').show();
            } else {
                $('#aiUncoveredWarnWrap').hide();
            }
            $('#aiSkillCoverageBar').show();
        } else {
            $('#aiSkillCoverageBar').hide();
        }
    }

    function updateCategoryCounts() {
        if (!loadedQuestions) return;
        const total = loadedQuestions.length;
        const must  = loadedQuestions.filter(q => {
            const t = (q.question_type || '').toLowerCase();
            return t === 'must_have_skill' || t === 'technical';
        }).length;
        const cand  = loadedQuestions.filter(q => (q.question_type || '').toLowerCase() === 'candidate_specific').length;
        const scen  = loadedQuestions.filter(q => (q.question_type || '').toLowerCase() === 'scenario').length;
        const beh   = loadedQuestions.filter(q => (q.question_type || '').toLowerCase() === 'behavioral').length;

        $('#tabCatAll').html(`<i class="fas fa-layer-group mr-1"></i> All Questions (${total})`);
        $('#tabCatMustHave').html(`<i class="fas fa-star mr-1 text-warning"></i> Must-Have Skills (${must})`);
        $('#tabCatCand').html(`<i class="fas fa-user-check mr-1 text-success"></i> Candidate-Specific (${cand})`);
        $('#tabCatScen').html(`<i class="fas fa-lightbulb mr-1 text-info"></i> Scenario (${scen})`);
        $('#tabCatBeh').html(`<i class="fas fa-users mr-1 text-purple" style="color:#7c3aed;"></i> Behavioral (${beh})`);
    }

    function renderQuestionsList(catFilter = 'all') {
        const listEl = $('#aiQuestionsList');
        if (!loadedQuestions || loadedQuestions.length === 0) {
            listEl.html(`<div class="text-center py-5 text-muted font-weight-bold">No AI interview questions available for this candidate.</div>`);
            return;
        }

        let filtered = loadedQuestions;
        if (catFilter !== 'all') {
            filtered = loadedQuestions.filter(q => {
                const t = (q.question_type || '').toLowerCase();
                if (catFilter === 'must_have_skill') return t === 'must_have_skill' || t === 'technical';
                return t === catFilter.toLowerCase();
            });
        }

        if (filtered.length === 0) {
            listEl.html(`<div class="text-center py-4 text-muted font-weight-bold">No questions found under this category.</div>`);
            return;
        }

        let html = '';
        filtered.forEach((q, idx) => {
            const type = (q.question_type || 'technical').toLowerCase();
            let typeBadgeClass = 'badge-primary';
            let typeLabel = 'Technical';
            if (type === 'must_have_skill') {
                typeBadgeClass = 'badge-warning text-dark';
                typeLabel = 'Must-Have Skill';
            } else if (type === 'candidate_specific') {
                typeBadgeClass = 'badge-success';
                typeLabel = 'Candidate-Specific';
            } else if (type === 'scenario') {
                typeBadgeClass = 'badge-info';
                typeLabel = 'Scenario';
            } else if (type === 'behavioral') {
                typeBadgeClass = 'badge-purple';
                typeLabel = 'Behavioral';
            }

            const diff = (q.difficulty || 'medium').toLowerCase();
            let diffBadgeClass = 'badge-info';
            if (diff.includes('beginner')) diffBadgeClass = 'badge-light border text-muted';
            else if (diff.includes('advanced') || diff.includes('hard')) diffBadgeClass = 'badge-danger';
            else if (diff.includes('intermediate')) diffBadgeClass = 'badge-warning text-dark';

            const status = (q.status_notes || 'unasked').toLowerCase();
            let statusBtnUnasked  = status === 'unasked' ? 'btn-secondary active' : 'btn-outline-secondary';
            let statusBtnAsked    = status === 'asked' ? 'btn-info active' : 'btn-outline-info';
            let statusBtnAnswered = status === 'answered' ? 'btn-success active' : 'btn-outline-success';
            let statusBtnSkipped  = status === 'skipped' ? 'btn-danger active' : 'btn-outline-danger';

            html += `
            <div class="card mb-3 border shadow-sm style="border-radius:10px; overflow:hidden;">
              <div class="card-header bg-white d-flex align-items-center justify-content-between py-2 px-3">
                <div>
                  <span class="badge ${typeBadgeClass} font-weight-bold mr-2 px-2 py-1">${typeLabel}</span>
                  <span class="badge ${diffBadgeClass} font-weight-bold px-2 py-1">${q.difficulty || 'Medium'}</span>
                  ${q.skill ? `<span class="badge badge-light border text-primary font-weight-bold ml-2 px-2 py-1"><i class="fas fa-tag mr-1"></i>${q.skill}</span>` : ''}
                </div>
                <small class="text-muted font-weight-bold">#${idx + 1}</small>
              </div>
              <div class="card-body p-3">
                <p class="font-weight-bold text-dark mb-2" style="font-size:14.5px; line-height:1.45;">
                  ${q.question}
                </p>
                ${q.reason ? `<div class="p-2 mb-2 bg-light border-left border-primary rounded text-muted small"><i class="fas fa-info-circle text-primary mr-1"></i><strong>Reasoning:</strong> ${q.reason}</div>` : ''}
                <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top flex-wrap gap-2">
                  <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <button type="button" class="btn btn-xs ${statusBtnUnasked} q-status-btn" data-qid="${q.id}" data-status="unasked">Unasked</button>
                    <button type="button" class="btn btn-xs ${statusBtnAsked} q-status-btn" data-qid="${q.id}" data-status="asked">Asked</button>
                    <button type="button" class="btn btn-xs ${statusBtnAnswered} q-status-btn" data-qid="${q.id}" data-status="answered">Answered</button>
                    <button type="button" class="btn btn-xs ${statusBtnSkipped} q-status-btn" data-qid="${q.id}" data-status="skipped">Skipped</button>
                  </div>
                </div>
              </div>
            </div>`;
        });

        listEl.html(html);
    }

    function fetchAndLoadQuestions(interviewId, version = null, triggerBtn = null) {
        $('#aiQuestionsList').html(`
          <div class="text-center py-5 text-muted">
            <i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
            <p class="font-weight-bold mb-0">Loading AI questions...</p>
          </div>
        `);

        $.ajax({
            url: '<?= base_url('admin/getAiInterviewQuestions'); ?>',
            type: 'POST',
            data: { interviewId: interviewId, version: version },
            success: function (rawRes) {
                let res;
                try { res = (typeof rawRes === 'object') ? rawRes : JSON.parse(rawRes); }
                catch(e) {
                    $('#aiQuestionsList').html(`<div class="alert alert-danger font-weight-bold"><strong>Parse Error:</strong><pre style="font-size:11px;">${rawRes.substring(0,500)}</pre></div>`);
                    return;
                }
                if (res.status === 'success') {
                    loadedQuestions = res.questions || [];

                    if (loadedQuestions.length === 0 && version === null) {
                        generateAiQuestions(interviewId, false, triggerBtn);
                        return;
                    }

                    if (res.candidate_name) $('#aiCandName').text(res.candidate_name);
                    if (res.job_title) $('#aiCandJob').text(res.job_title);
                    if (res.ats_score) $('#aiAtsBadge').text(`ATS Fit Match: ${res.ats_score}`);

                    if (res.available_versions && res.available_versions.length > 1) {
                        $('#aiVerDropdownWrap').show();
                        let vMenu = '';
                        res.available_versions.forEach(v => {
                            vMenu += `<a class="dropdown-item ai-ver-item font-weight-bold" href="javascript:void(0);" data-ver="${v}">Version ${v}</a>`;
                        });
                        $('#aiVerMenu').html(vMenu);
                    } else {
                        $('#aiVerDropdownWrap').hide();
                    }

                    updateSkillCoverageAndSource(res);
                    updateCategoryCounts();
                    renderQuestionsList('all');
                    $('#aiCategoryTabs a[data-cat="all"]').tab('show');
                } else {
                    $('#aiQuestionsList').html(`<div class="alert alert-danger font-weight-bold">${res.message || 'Failed to load questions.'}</div>`);
                }
            },
            error: function (xhr) {
                $('#aiQuestionsList').html(`<div class="alert alert-danger font-weight-bold"><strong>HTTP Error ${xhr.status}:</strong><pre style="font-size:11px;">${xhr.responseText ? xhr.responseText.substring(0,500) : 'No response'}</pre></div>`);
            }
        });
    }

    function generateAiQuestions(interviewId, isRegeneration = false, triggerBtn = null) {
        $('#aiQuestionsList').html(`
          <div class="text-center py-5 text-muted">
            <i class="fas fa-brain fa-pulse fa-3x mb-3 text-warning"></i>
            <h5 class="font-weight-bold text-dark mb-1">Generating Candidate-Personalized Questions...</h5>
            <p class="text-muted small">Parsing resume claims, vacancy criteria, ATS analysis, and verifying cross-candidate non-duplication rules...</p>
          </div>
        `);

        $.ajax({
            url: '<?= base_url('admin/generateAiInterviewQuestions'); ?>',
            type: 'POST',
            data: { interviewId: interviewId, isRegeneration: isRegeneration ? 1 : 0 },
            success: function (rawRes) {
                let res;
                try { res = (typeof rawRes === 'object') ? rawRes : JSON.parse(rawRes); }
                catch(e) {
                    $('#aiQuestionsList').html(`<div class="alert alert-danger font-weight-bold"><strong>Parse Error:</strong><pre style="font-size:11px;">${rawRes.substring(0,500)}</pre></div>`);
                    return;
                }
                if (res.status === 'success') {
                    loadedQuestions = res.questions || [];
                    if (res.candidate_name) $('#aiCandName').text(res.candidate_name);
                    if (res.job_title) $('#aiCandJob').text(res.job_title);
                    if (res.ats_score) $('#aiAtsBadge').text(`ATS Fit Match: ${res.ats_score}`);

                    updateSkillCoverageAndSource(res);
                    updateCategoryCounts();
                    renderQuestionsList('all');

                    if (triggerBtn) {
                        triggerBtn.removeClass('btn-outline-primary btn-primary').addClass('btn-success').html('<i class="fas fa-list-ol mr-1"></i> View AI Questions');
                    } else if (activeInterviewId) {
                        $(`.openAiQuestionsModal[data-interview="${activeInterviewId}"]`).removeClass('btn-outline-primary btn-primary').addClass('btn-success').html('<i class="fas fa-list-ol mr-1"></i> View AI Questions');
                    }
                } else {
                    $('#aiQuestionsList').html(`<div class="alert alert-danger font-weight-bold">${res.message || 'Failed to generate AI questions.'}</div>`);
                }
            },
            error: function (xhr) {
                $('#aiQuestionsList').html(`<div class="alert alert-danger font-weight-bold"><strong>HTTP Error ${xhr.status}:</strong><pre style="font-size:11px;">${xhr.responseText ? xhr.responseText.substring(0,500) : 'No response'}</pre></div>`);
            }
        });
    }


  
    $(document).on('click', '.openAiQuestionsModal', function () {
        const btn = $(this);
        activeInterviewId = btn.attr('data-interview');
        const candName = btn.attr('data-candidate');
        const jobTitle = btn.attr('data-job');
        const roleName = btn.attr('data-role');
        const atsScore = btn.attr('data-score');

        $('#aiCandName').text(candName || 'Candidate');
        $('#aiCandJob').text(jobTitle || 'Job Title');
        if (roleName) {
            $('#aiCandRole').text(roleName);
            $('#aiCandRoleWrap').show();
        } else {
            $('#aiCandRoleWrap').hide();
        }
        $('#aiAtsBadge').text(`ATS Fit Match: ${atsScore || 'N/A'}`);

        $('#aiQuestionsModal').modal('show');
        fetchAndLoadQuestions(activeInterviewId, null, btn);
    });

    // CATEGORY TAB FILTERING
    $(document).on('click', '#aiCategoryTabs a[data-cat]', function () {
        $('#aiCategoryTabs a').removeClass('active');
        $(this).addClass('active');
        const cat = $(this).attr('data-cat');
        renderQuestionsList(cat);
    });

    
    $(document).on('click', '#btnRegenerateAi', function () {
        if (activeInterviewId && confirm("Are you sure you want to generate a fresh candidate-personalized question set? Previous versions will remain in audit history.")) {
            generateAiQuestions(activeInterviewId, true);
        }
    });


    $(document).on('click', '.ai-ver-item', function () {
        const ver = $(this).attr('data-ver');
        $('#aiVerBtn').text(`Version ${ver}`);
        fetchAndLoadQuestions(activeInterviewId, ver);
    });

 
    $(document).on('click', '.q-status-btn', function () {
        const btn = $(this);
        const qid = btn.attr('data-qid');
        const status = btn.attr('data-status');

        btn.siblings().removeClass('active btn-secondary btn-info btn-success btn-danger')
             .addClass(function() {
                const s = $(this).attr('data-status');
                if (s === 'unasked') return 'btn-outline-secondary';
                if (s === 'asked') return 'btn-outline-info';
                if (s === 'answered') return 'btn-outline-success';
                if (s === 'skipped') return 'btn-outline-danger';
             });

        btn.removeClass('btn-outline-secondary btn-outline-info btn-outline-success btn-outline-danger')
           .addClass('active');
        if (status === 'unasked') btn.addClass('btn-secondary');
        else if (status === 'asked') btn.addClass('btn-info');
        else if (status === 'answered') btn.addClass('btn-success');
        else if (status === 'skipped') btn.addClass('btn-danger');

        $.ajax({
            url: '<?= base_url('admin/updateQuestionStatus'); ?>',
            type: 'POST',
            data: { questionId: qid, status: status },
            dataType: 'json'
        });
    });

 
    $(document).on('click', '#btnPrintAiQuestions', function () {
        const printWindow = window.open('', '_blank');
        const candName = $('#aiCandName').text();
        const jobTitle = $('#aiCandJob').text();
        const atsScore = $('#aiAtsBadge').text();

        let qHtml = '';
        loadedQuestions.forEach((q, idx) => {
            qHtml += `
            <div style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid #e2e8f0; page-break-inside:avoid;">
              <div style="font-weight:bold; font-size:12px; color:#2563eb; text-transform:uppercase;">
                Question #${idx + 1} &bull; ${q.question_type ? q.question_type.toUpperCase() : 'TECHNICAL'} [${q.difficulty ? q.difficulty.toUpperCase() : 'MEDIUM'}]
              </div>
              <div style="font-size:14px; font-weight:bold; color:#0f172a; margin:6px 0;">
                ${q.question}
              </div>
              ${q.reason ? `<div style="font-size:11px; color:#64748b; font-style:italic;">Reasoning: ${q.reason}</div>` : ''}
              <div style="margin-top:8px; font-size:11px; color:#334155;">
                [ ] Asked &nbsp;&nbsp;&nbsp;&nbsp; [ ] Answered &nbsp;&nbsp;&nbsp;&nbsp; [ ] Skipped &nbsp;&nbsp;&nbsp;&nbsp; Rating/Notes: ____________________
              </div>
            </div>`;
        });

        printWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>AI Interview Questions - ${candName}</title>
            <style>
              body { font-family: 'Segoe UI', Arial, sans-serif; padding: 25px; color: #1f2937; }
              .header { border-bottom: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 20px; }
              .header h2 { margin: 0 0 6px 0; color: #0f172a; }
              .header p { margin: 0; color: #475569; font-size: 13px; }
            </style>
          </head>
          <body>
            <div class="header">
              <h2>AI Personalized Interview Questions</h2>
              <p><strong>Candidate:</strong> ${candName} &bull; <strong>Position:</strong> ${jobTitle} &bull; <strong>${atsScore}</strong></p>
            </div>
            ${qHtml}
          </body>
          </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function () { printWindow.print(); }, 400);
    });

});
</script>


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
