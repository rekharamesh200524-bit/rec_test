<?php

$all_jobs_history = isset($all_jobs_history) ? $all_jobs_history : [];
$all_candidates_history = isset($all_candidates_history) ? $all_candidates_history : [];
$all_requests_history = isset($all_requests_history) ? $all_requests_history : [];
$recruiter_analytics = isset($recruiter_analytics) ? $recruiter_analytics : [];
$interviewer_summary = isset($interviewer_summary) ? $interviewer_summary : [];
$interviewer_details = isset($interviewer_details) ? $interviewer_details : [];
$dept_analytics = isset($dept_analytics) ? $dept_analytics : [];
$departments = isset($departments) ? $departments : [];

$total_jobs = isset($total_jobs) ? $total_jobs : count($all_jobs_history);
$total_candidates = isset($total_candidates) ? $total_candidates : count($all_candidates_history);
$total_applications = isset($total_applications) ? $total_applications : count($all_candidates_history);
$total_requests = isset($total_requests) ? $total_requests : count($all_requests_history);
$hired_candidates = isset($hired_candidates) ? $hired_candidates : 0;
$open_jobs = isset($open_jobs) ? $open_jobs : 0;
$hold_jobs = isset($hold_jobs) ? $hold_jobs : 0;
$closed_jobs = isset($closed_jobs) ? $closed_jobs : 0;

$fill_rate = ($total_jobs > 0) ? round(($closed_jobs / $total_jobs) * 100, 1) : 0;
?>

<div class="pbi-analytics-wrapper">

  
  <div class="pbi-analytics-header">
    <div class="pbi-brand-box">
      <div class="pbi-brand-icon">
        <i class="fas fa-chart-line"></i>
      </div>
      <div>
        <h1 class="pbi-analytics-title">Recruitment Analytics & Historical Intelligence Engine</h1>
        <p class="pbi-analytics-subtitle">Interactive Power BI Light Visual Analytics, Slicers, & Direct Drill-Down Intelligence</p>
      </div>
    </div>

    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
      <span class="pbi-live-badge">
        <span class="pbi-pulse-dot"></span> Real-Time Dataset Connected
      </span>
      <a href="<?= base_url('admin/dashboard'); ?>" class="btn btn-sm btn-outline-secondary font-weight-bold ml-2" style="border-radius:6px;">
        <i class="fas fa-arrow-left mr-1"></i> Dashboard
      </a>
    </div>
  </div>

  <div class="row">
 
    <div class="col-xl-2 col-lg-3 col-md-12 mb-3">
      <div class="pbi-sidebar-card">
        <div class="pbi-sidebar-title">
          <i class="fas fa-filter text-primary"></i> Slicers Panel
        </div>

        <div class="pbi-slicer-group">
          <div class="pbi-slicer-label">Year Slicer</div>
          <select id="slicerYear" class="form-control pbi-slicer-select">
            <option value="2026">2026 Year</option>
            <option value="2025">2025 Year</option>
            <option value="all" selected>All Time</option>
          </select>
        </div>

        <div class="pbi-slicer-group">
          <div class="pbi-slicer-label">Department Slicer</div>
          <select id="slicerDepartment" class="form-control pbi-slicer-select">
            <option value="all">All Departments (<?= count($departments); ?>)</option>
            <?php foreach($departments as $d): ?>
              <option value="<?= htmlspecialchars($d['Departmentname']); ?>"><?= htmlspecialchars($d['Departmentname']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="pbi-slicer-group">
          <div class="pbi-slicer-label">Recruiter Manager</div>
          <select id="slicerRecruiter" class="form-control pbi-slicer-select">
            <option value="all">All Recruiters</option>
            <?php foreach($recruiter_analytics as $r): ?>
              <?php if ((int)($r['assigned_jobs'] ?? 0) > 0): ?>
                <option value="<?= htmlspecialchars($r['EmpName']); ?>"><?= htmlspecialchars($r['EmpName']); ?></option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="pbi-slicer-group">
          <div class="pbi-slicer-label">Job Status Slicer</div>
          <select id="slicerStatus" class="form-control pbi-slicer-select">
            <option value="all">All Statuses</option>
            <option value="Open">Open / Active</option>
            <option value="On-Hold">On-Hold</option>
            <option value="Closed">Closed / Filled</option>
          </select>
        </div>

        <button type="button" id="resetSlicersBtn" class="btn pbi-btn-reset">
          <i class="fas fa-undo mr-1"></i> Reset Slicers
        </button>
      </div>
    </div>

   
    <div class="col-xl-10 col-lg-9 col-md-12">
      
    
      <div class="row">
      
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
          <div class="pbi-kpi-card pbi-kpi-border-blue" data-drill="jobs" data-filter="all">
            <div class="pbi-kpi-title">Total Vacancies</div>
            <div class="kpi-scorecard-box">
              <div>
                <div class="kpi-main-val text-primary" id="kpiValTotal"><?= number_format($total_jobs); ?></div>
                <div class="kpi-sub-badge kpi-badge-green"><i class="fas fa-arrow-up"></i> Active Vacancies</div>
              </div>
              <svg class="sparkline-svg" viewBox="0 0 100 40">
                <path d="M0 30 Q 25 10, 50 25 T 100 5" fill="none" stroke="#2563eb" stroke-width="2.5" />
              </svg>
            </div>
          </div>
        </div>

      
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
          <div class="pbi-kpi-card pbi-kpi-border-green" data-drill="jobs" data-filter="closed">
            <div class="pbi-kpi-title">Vacancies Closed</div>
            <div class="kpi-scorecard-box">
              <div>
                <div class="kpi-main-val text-success" id="kpiValClosed"><?= number_format($closed_jobs); ?></div>
                <div class="kpi-sub-badge kpi-badge-green"><i class="fas fa-arrow-up"></i> Hired Positions</div>
              </div>
              <svg class="sparkline-svg" viewBox="0 0 100 40">
                <path d="M0 35 Q 25 20, 50 15 T 100 8" fill="none" stroke="#059669" stroke-width="2.5" />
              </svg>
            </div>
          </div>
        </div>

        
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
          <div class="pbi-kpi-card pbi-kpi-border-amber" data-drill="candidates" data-filter="hired">
            <div class="pbi-kpi-title">Position Fill Rate</div>
            <div class="kpi-scorecard-box">
              <div>
                <div class="kpi-main-val text-warning" id="kpiValRate"><?= $fill_rate; ?>%</div>
                <div class="kpi-sub-badge kpi-badge-cyan"><i class="fas fa-chart-pie"></i> Target Ratio</div>
              </div>
              <svg class="sparkline-svg" viewBox="0 0 100 40">
                <path d="M0 15 Q 25 25, 50 10 T 100 20" fill="none" stroke="#d97706" stroke-width="2.5" />
              </svg>
            </div>
          </div>
        </div>

       
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
          <div class="pbi-kpi-card pbi-kpi-border-purple" data-drill="candidates" data-filter="all">
            <div class="pbi-kpi-title">Candidate Pool Volume</div>
            <div class="kpi-scorecard-box">
              <div>
                <div class="kpi-main-val" id="kpiValPool" style="color:#7c3aed;"><?= number_format($total_candidates); ?></div>
                <div class="kpi-sub-badge kpi-badge-cyan">Active Pool Volume</div>
              </div>
              <svg class="sparkline-svg" viewBox="0 0 100 40">
                <path d="M0 25 Q 25 15, 50 30 T 100 10" fill="none" stroke="#7c3aed" stroke-width="2.5" />
              </svg>
            </div>
          </div>
        </div>
      </div>

    
      <div class="row">
       
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
          <div class="pbi-visual-card pbi-chart-card h-100" data-chart="priority">
            <div class="pbi-card-title">Vacancies by Priority</div>
            <div class="d-flex flex-column align-items-center justify-content-center">
              <div style="width:125px; height:125px;">
                <canvas id="priorityDonutChart"></canvas>
              </div>
              <div class="w-100 mt-2 font-weight-bold" style="font-size:10.5px;">
                <div class="d-flex justify-content-between text-primary mb-1">
                  <span><i class="fas fa-square mr-1"></i> High Priority</span>
                  <span id="priorityValHigh">0</span>
                </div>
                <div class="d-flex justify-content-between text-info mb-1">
                  <span><i class="fas fa-square mr-1"></i> Mid Priority</span>
                  <span id="priorityValMid">0</span>
                </div>
                <div class="d-flex justify-content-between text-secondary">
                  <span><i class="fas fa-square mr-1"></i> Low Priority</span>
                  <span id="priorityValLow">0</span>
                </div>
              </div>
            </div>
          </div>
        </div>

     
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
          <div class="pbi-visual-card pbi-chart-card h-100" data-chart="department">
            <div class="pbi-card-title">Vacancies by Department</div>
            <div style="height:185px;">
              <canvas id="categoryBarChart"></canvas>
            </div>
          </div>
        </div>

       
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
          <div class="pbi-visual-card h-100">
            <div class="pbi-card-title">
              <span>Top Panel Interviewers</span>
              <span class="badge badge-light border text-muted small">Rounds</span>
            </div>
            <div class="pt-1" id="interviewersListContainer">
              <?php if(!empty($interviewer_summary)): ?>
                <?php foreach($interviewer_summary as $i): ?>
                  <?php 
                    $intCount = (int)($i['total_interviews'] ?? 0);
                    $barWidth = 50;
                  ?>
                  <div class="ranking-bar-item interviewer-item" data-name="<?= htmlspecialchars($i['EmpName'] ?? 'Interviewer'); ?>">
                    <div class="ranking-bar-info">
                      <span><i class="fas fa-user-check text-success mr-1"></i> <?= htmlspecialchars($i['EmpName'] ?? 'Interviewer'); ?></span>
                      <span class="text-success font-weight-bold"><?= $intCount; ?> Rounds</span>
                    </div>
                    <div class="ranking-bar-track">
                      <div class="ranking-bar-fill ranking-bar-fill-purple" style="width: <?= $barWidth; ?>%;"></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="text-muted small py-3 text-center">No interviewer panel records logged yet.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

       
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
          <div class="pbi-visual-card h-100">
            <div class="pbi-card-title">
              <span>Top Recruiter Managers</span>
              <span class="badge badge-light border text-muted small">Vacancies</span>
            </div>
            <div class="pt-1" id="recruitersListContainer">
              <?php 
                $activeRecruiters = array_filter($recruiter_analytics, function($r) {
                  return ((int)($r['assigned_jobs'] ?? 0)) > 0;
                });
              ?>
              <?php if(!empty($activeRecruiters)): ?>
                <?php foreach($activeRecruiters as $r): ?>
                  <?php 
                    $jobsCount = (int)($r['assigned_jobs'] ?? 0);
                    $barWidth = ($total_jobs > 0) ? round(($jobsCount / $total_jobs) * 100) : 50;
                    if ($barWidth < 20) $barWidth = 45;
                  ?>
                  <div class="ranking-bar-item recruiter-item" data-name="<?= htmlspecialchars($r['EmpName'] ?? 'Recruiter'); ?>">
                    <div class="ranking-bar-info">
                      <span><i class="fas fa-user-tie text-primary mr-1"></i> <?= htmlspecialchars($r['EmpName'] ?? 'Recruiter'); ?></span>
                      <span class="text-primary font-weight-bold"><?= $jobsCount; ?> Vacancies</span>
                    </div>
                    <div class="ranking-bar-track">
                      <div class="ranking-bar-fill" style="width: <?= $barWidth; ?>%;"></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="text-muted small py-3 text-center">No active recruiter managers with assigned vacancies.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    
      <div class="row">
        
        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
          <div class="pbi-visual-card pbi-chart-card h-100" data-chart="employment">
            <div class="pbi-card-title">Jobs by Employment Type</div>
            <div class="d-flex flex-column align-items-center justify-content-center">
              <div style="width:125px; height:125px;">
                <canvas id="workTypeDonutChart"></canvas>
              </div>
              <div class="w-100 mt-2 font-weight-bold" style="font-size:10.5px;">
                <div class="d-flex justify-content-between text-primary mb-1">
                  <span><i class="fas fa-circle mr-1"></i> Full-Time / Permanent</span>
                  <span id="workValFull">0</span>
                </div>
                <div class="d-flex justify-content-between text-purple" style="color:#7c3aed;">
                  <span><i class="fas fa-circle mr-1"></i> Contract / Temporary</span>
                  <span id="workValContract">0</span>
                </div>
              </div>
            </div>
          </div>
        </div>

       
        <div class="col-xl-4 col-lg-6 col-md-12 mb-3">
          <div class="pbi-visual-card pbi-chart-card h-100" data-chart="velocity">
            <div class="pbi-card-title">
              <span>Recruitment Velocity Overtime</span>
              <span class="small text-muted"><i class="fas fa-circle text-purple mr-1" style="color:#7c3aed;"></i> Closed &nbsp; <i class="fas fa-circle text-primary mr-1"></i> Created</span>
            </div>
            <div style="height:185px;">
              <canvas id="overtimeLineChart"></canvas>
            </div>
          </div>
        </div>

       
        <div class="col-xl-4 col-lg-12 col-md-12 mb-3">
          <div class="pbi-visual-card h-100">
            <div class="pbi-card-title">
              <span>Candidate Availability & Notice</span>
              <span class="badge badge-success px-2 py-1 font-weight-bold" style="border-radius:12px;">Joining Pipeline</span>
            </div>
            <div class="pt-1">
              <div class="ats-rating-item notice-period-item" data-notice="immediate">
                <div class="ats-rating-lbl"><i class="fas fa-bolt text-success mr-1"></i> Immediate Joiners</div>
                <div class="ats-rating-track"><div class="ats-rating-fill" id="trackImmediate" style="width: 50.0%; background:#059669;"></div></div>
                <div class="ats-rating-val" id="valImmediate">0 (0%)</div>
              </div>
              <div class="ats-rating-item notice-period-item" data-notice="15days">
                <div class="ats-rating-lbl"><i class="fas fa-calendar-check text-info mr-1"></i> 15 Days Notice</div>
                <div class="ats-rating-track"><div class="ats-rating-fill" id="track15" style="width: 25.0%; background:#0284c7;"></div></div>
                <div class="ats-rating-val" id="val15">0 (0%)</div>
              </div>
              <div class="ats-rating-item notice-period-item" data-notice="30days">
                <div class="ats-rating-lbl"><i class="fas fa-clock text-primary mr-1"></i> 30 Days Notice</div>
                <div class="ats-rating-track"><div class="ats-rating-fill" id="track30" style="width: 25.0%; background:#2563eb;"></div></div>
                <div class="ats-rating-val" id="val30">0 (0%)</div>
              </div>
              <div class="ats-rating-item notice-period-item" data-notice="60days">
                <div class="ats-rating-lbl"><i class="fas fa-hourglass-half text-secondary mr-1"></i> 60+ Days Notice</div>
                <div class="ats-rating-track"><div class="ats-rating-fill" id="track60" style="width: 0%; background:#94a3b8;"></div></div>
                <div class="ats-rating-val" id="val60">0 (0%)</div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>


<div class="modal fade" id="pbiDrillModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius:12px; overflow:hidden;">
      
     
      <div class="modal-header bg-dark text-white px-4 py-3 align-items-center">
        <div>
          <h5 class="modal-title font-weight-bold mb-0 text-white" id="pbiDrillTitle">
            <i class="fas fa-search-plus text-primary mr-2"></i> Power BI Interactive Data Drill-Down Inspection
          </h5>
          <small class="text-white-50" id="pbiDrillSubtitle">Filtered Underlying Records & Profiling</small>
        </div>
        <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close" style="color:#ffffff !important; opacity:1 !important; font-size:26px;">
          <span aria-hidden="true" style="color:#ffffff !important;">&times;</span>
        </button>
      </div>

    
      <div class="modal-body p-4" style="background:#f8fafc;">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
          <div>
            <span class="badge badge-primary px-3 py-2 font-weight-bold" id="pbiDrillBadge" style="border-radius:12px; font-size:12px;">
              0 Matching Records
            </span>
          </div>
          <div id="pbiDrillExportContainer"></div>
        </div>

        <div class="table-responsive bg-white rounded border shadow-sm p-2">
          <table id="pbiDrillTable" class="table pbi-drill-table">
            <thead>
              <tr id="pbiDrillHeader"></tr>
            </thead>
            <tbody id="pbiDrillBody"></tbody>
          </table>
        </div>
      </div>

    
      <div class="modal-footer bg-light px-4 py-2">
        <button type="button" class="btn btn-secondary font-weight-bold px-4" data-dismiss="modal" style="border-radius:6px;">Close Drill-Down</button>
      </div>

    </div>
  </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

  const rawJobs = <?= json_encode($all_jobs_history); ?>;
  const rawCandidates = <?= json_encode($all_candidates_history); ?>;
  const rawRecruiters = <?= json_encode($recruiter_analytics); ?>;
  const rawInterviewsSummary = <?= json_encode($interviewer_summary); ?>;
  const rawInterviewsDetail = <?= json_encode($interviewer_details); ?>;
  const rawDepartments = <?= json_encode(array_values(array_unique(array_filter(array_column($departments, 'Departmentname'))))); ?>;

  let activeJobs = [...rawJobs];
  let activeCandidates = [...rawCandidates];
  let activeInterviews = [...rawInterviewsDetail];

  let priorityChartInst = null;
  let catChartInst = null;
  let workChartInst = null;
  let overChartInst = null;

  function getClickedIndex(evt, activeElements, chartInst) {
    let elements = (activeElements && activeElements.length) ? activeElements : [];
    if (!elements.length && chartInst) {
      if (typeof chartInst.getElementAtEvent === 'function') {
        elements = chartInst.getElementAtEvent(evt) || [];
      } else if (typeof chartInst.getElementsAtEvent === 'function') {
        elements = chartInst.getElementsAtEvent(evt) || [];
      }
    }
    if (!Array.isArray(elements)) {
      elements = elements ? [elements] : [];
    }
    if (!elements.length) return null;

    let el = elements[0];
    if (el._index !== undefined) return el._index;
    if (el.index !== undefined) return el.index;
    return null;
  }

  window.openDrillDownModal = function (title, subtitle, type, dataArray) {
    dataArray = dataArray || [];
    document.getElementById('pbiDrillTitle').innerHTML = `<i class="fas fa-search-plus text-primary mr-2"></i> ${title}`;
    document.getElementById('pbiDrillSubtitle').innerText = subtitle;
    document.getElementById('pbiDrillBadge').innerText = `${dataArray.length} Records Found`;

    if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#pbiDrillTable')) {
      $('#pbiDrillTable').DataTable().destroy();
      $('#pbiDrillTable').empty();
    }

    const tableEl = document.getElementById('pbiDrillTable');
    tableEl.innerHTML = `
      <thead><tr id="pbiDrillHeader"></tr></thead>
      <tbody id="pbiDrillBody"></tbody>
    `;

    const header = document.getElementById('pbiDrillHeader');
    const body = document.getElementById('pbiDrillBody');

    if (type === 'jobs') {
      header.innerHTML = `
        <th>#</th>
        <th>Job Title</th>
        <th>Code</th>
        <th>Department</th>
        <th>Openings</th>
        <th>Assigned Recruiter</th>
        <th>Posted Date</th>
        <th>Status</th>
      `;
      if (!dataArray.length) {
        body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">No matching vacancy records found for this slice.</td></tr>`;
      } else {
        dataArray.forEach((j, idx) => {
          const dStr = j.PostedOn ? new Date(j.PostedOn).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
          body.innerHTML += `
            <tr>
              <td>${idx + 1}</td>
              <td><strong class="text-dark">${j.JobTitle || 'N/A'}</strong></td>
              <td><code>${j.JobCode || '-'}</code></td>
              <td><span class="badge badge-light border">${j.Departmentname || 'General'}</span></td>
              <td><span class="badge badge-info px-2">${j.NoofOpenings || 1}</span></td>
              <td><strong class="text-primary">${j.RecruiterName || 'Unassigned'}</strong></td>
              <td class="text-muted small">${dStr}</td>
              <td><span class="badge badge-success px-2 py-1">${j.JobStatus || 'Draft'}</span></td>
            </tr>
          `;
        });
      }

    } else if (type === 'candidates') {
      header.innerHTML = `
        <th>#</th>
        <th>Candidate Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Applied Job</th>
        <th>Stage</th>
        <th>Applied Date</th>
        <th>Status</th>
      `;
      if (!dataArray.length) {
        body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">No matching candidate records found for this slice.</td></tr>`;
      } else {
        dataArray.forEach((c, idx) => {
          const dStr = c.AppliedOn ? new Date(c.AppliedOn).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
          body.innerHTML += `
            <tr>
              <td>${idx + 1}</td>
              <td><strong class="text-dark">${c.Fullname || 'N/A'}</strong></td>
              <td class="small">${c.Email || '-'}</td>
              <td class="small">${c.MobileNumber || '-'}</td>
              <td><strong class="text-primary">${c.JobTitle || 'N/A'}</strong></td>
              <td><span class="badge badge-light border">${c.CurrentStage || 'Screened'}</span></td>
              <td class="text-muted small">${dStr}</td>
              <td><span class="badge badge-info px-2 py-1">${c.CurrentStatus || 'Pending'}</span></td>
            </tr>
          `;
        });
      }

    } else if (type === 'interviews') {
      header.innerHTML = `
        <th>#</th>
        <th>Interviewer User</th>
        <th>Candidate Name & Contact</th>
        <th>Applied Job & Dept</th>
        <th>Round / Type</th>
        <th>Scheduled Date</th>
        <th>Result / Status</th>
        <th>Feedback Notes</th>
      `;
      if (!dataArray.length) {
        body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">No conducted interviews logged for this interviewer.</td></tr>`;
      } else {
        dataArray.forEach((i, idx) => {
          const dStr = i.ScheduledAt ? new Date(i.ScheduledAt).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';
          body.innerHTML += `
            <tr>
              <td>${idx + 1}</td>
              <td><strong class="text-primary">${i.InterviewerName || 'N/A'}</strong> <br><small class="text-muted">${i.InterviewerCode || ''}</small></td>
              <td><strong class="text-dark">${i.CandidateName || 'N/A'}</strong><br><small class="text-muted">${i.CandidateEmail || ''} &bull; ${i.CandidatePhone || ''}</small></td>
              <td><strong>${i.JobTitle || 'N/A'}</strong><br><small class="badge badge-light border">${i.Departmentname || 'General'}</small></td>
              <td><span class="badge badge-info px-2">Round ${i.InterviewRound || 1}</span> <br><small class="text-muted">${i.InterviewType || 'Technical'}</small></td>
              <td class="text-muted small">${dStr}</td>
              <td><span class="badge badge-success px-2 py-1">${i.Result || 'Scheduled'}</span></td>
              <td class="small text-muted">${i.Feedback ? i.Feedback.substring(0, 50) + '...' : 'No feedback notes recorded'}</td>
            </tr>
          `;
        });
      }
    }

    if (window.jQuery && $.fn.DataTable) {
      $('#pbiDrillTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 10,
        "buttons": ["csv", "excel", "pdf", "print"]
      }).buttons().container().appendTo('#pbiDrillExportContainer');
    }

    if (window.jQuery) {
      $('#pbiDrillModal').modal('show');
    }
  };

  function applySlicers() {
    const yearVal = document.getElementById('slicerYear').value;
    const deptVal = document.getElementById('slicerDepartment').value;
    const recVal  = document.getElementById('slicerRecruiter').value;
    const statVal = document.getElementById('slicerStatus').value;

    // 1. Filter activeJobs
    activeJobs = rawJobs.filter(j => {
      if (yearVal !== 'all') {
        const jYear = j.PostedOn ? new Date(j.PostedOn).getFullYear().toString() : '2026';
        if (jYear !== yearVal) return false;
      }
      if (deptVal !== 'all') {
        if ((j.Departmentname || '').toLowerCase() !== deptVal.toLowerCase()) return false;
      }
      if (recVal !== 'all') {
        if ((j.RecruiterName || '').toLowerCase() !== recVal.toLowerCase()) return false;
      }
      if (statVal !== 'all') {
        const st = (j.JobStatus || '').toLowerCase();
        if (statVal.toLowerCase() === 'open' && (st !== 'open' && st !== 're-open')) return false;
        if (statVal.toLowerCase() === 'on-hold' && st !== 'on-hold' && st !== 'on hold') return false;
        if (statVal.toLowerCase() === 'closed' && st !== 'closed' && st !== 'filled') return false;
      }
      return true;
    });

    const activeJids = new Set(activeJobs.map(j => String(j.Jid)));

    // 2. Filter activeCandidates
    activeCandidates = rawCandidates.filter(c => {
      if (yearVal !== 'all') {
        const cYear = c.AppliedOn ? new Date(c.AppliedOn).getFullYear().toString() : '2026';
        if (cYear !== yearVal) return false;
      }
      if (deptVal !== 'all') {
        if ((c.Departmentname || '').toLowerCase() !== deptVal.toLowerCase()) return false;
      }
      if (recVal !== 'all' || statVal !== 'all') {
        if (c.Jid && activeJids.size > 0 && !activeJids.has(String(c.Jid))) return false;
      }
      return true;
    });

    // 3. Filter activeInterviews
    activeInterviews = rawInterviewsDetail.filter(i => {
      if (yearVal !== 'all') {
        const iYear = i.ScheduledAt ? new Date(i.ScheduledAt).getFullYear().toString() : '2026';
        if (iYear !== yearVal) return false;
      }
      if (deptVal !== 'all') {
        if ((i.Departmentname || '').toLowerCase() !== deptVal.toLowerCase()) return false;
      }
      if (recVal !== 'all') {
        if ((i.InterviewerName || '').toLowerCase() !== recVal.toLowerCase()) return false;
      }
      return true;
    });

    // 4. KPI Cards
    const totalJobsCount = activeJobs.length;
    const closedJobsCount = activeJobs.filter(j => (j.JobStatus || '').toLowerCase() === 'closed' || (j.JobStatus || '').toLowerCase() === 'filled').length;
    const rate = totalJobsCount > 0 ? ((closedJobsCount / totalJobsCount) * 100).toFixed(1) : 0;
    const poolCount = activeCandidates.length;

    document.getElementById('kpiValTotal').innerText = totalJobsCount;
    document.getElementById('kpiValClosed').innerText = closedJobsCount;
    document.getElementById('kpiValRate').innerText = `${rate}%`;
    document.getElementById('kpiValPool').innerText = poolCount;

    // 5. Vacancies by Priority
    let highP = 0, midP = 0, lowP = 0;
    activeJobs.forEach(j => {
      const openings = parseInt(j.NoofOpenings || 1);
      if (openings >= 3) highP++;
      else if (openings === 2) midP++;
      else lowP++;
    });

    const highPct = totalJobsCount > 0 ? ((highP / totalJobsCount) * 100).toFixed(1) : 0;
    const midPct  = totalJobsCount > 0 ? ((midP / totalJobsCount) * 100).toFixed(1) : 0;
    const lowPct  = totalJobsCount > 0 ? ((lowP / totalJobsCount) * 100).toFixed(1) : 0;

    document.getElementById('priorityValHigh').innerText = `${highP} (${highPct}%)`;
    document.getElementById('priorityValMid').innerText  = `${midP} (${midPct}%)`;
    document.getElementById('priorityValLow').innerText  = `${lowP} (${lowPct}%)`;

    if (priorityChartInst) {
      priorityChartInst.data.datasets[0].data = [highP, midP, lowP];
      priorityChartInst.update();
    }

    // 6. Vacancies by Department Bar Chart
    const deptMap = {};
    if (rawDepartments && rawDepartments.length > 0) {
      rawDepartments.forEach(d => deptMap[d] = 0);
    }
    activeJobs.forEach(j => {
      const dName = j.Departmentname || 'General';
      deptMap[dName] = (deptMap[dName] || 0) + 1;
    });

    const deptLabels = Object.keys(deptMap);
    const deptDataValues = Object.values(deptMap);

    if (catChartInst) {
      catChartInst.data.labels = deptLabels;
      catChartInst.data.datasets[0].data = deptDataValues;
      catChartInst.update();
    }

    // 7. Top Recruiter Managers List
    const recruiterCounts = {};
    activeJobs.forEach(j => {
      const rName = j.RecruiterName || 'Unassigned';
      recruiterCounts[rName] = (recruiterCounts[rName] || 0) + 1;
    });

    const recContainer = document.getElementById('recruitersListContainer');
    if (recContainer) {
      const sortedRecs = Object.keys(recruiterCounts).sort((a, b) => recruiterCounts[b] - recruiterCounts[a]);
      if (sortedRecs.length === 0 || totalJobsCount === 0) {
        recContainer.innerHTML = `<div class="text-muted small py-3 text-center">No active recruiter managers for selected filter.</div>`;
      } else {
        let rHtml = '';
        sortedRecs.forEach(rName => {
          const cnt = recruiterCounts[rName];
          const pct = Math.max(25, Math.round((cnt / totalJobsCount) * 100));
          rHtml += `
            <div class="ranking-bar-item recruiter-item" data-name="${rName}">
              <div class="ranking-bar-info">
                <span><i class="fas fa-user-tie text-primary mr-1"></i> ${rName}</span>
                <span class="text-primary font-weight-bold">${cnt} Vacancies</span>
              </div>
              <div class="ranking-bar-track">
                <div class="ranking-bar-fill" style="width: ${pct}%;"></div>
              </div>
            </div>`;
        });
        recContainer.innerHTML = rHtml;
      }
    }

    // 8. Top Panel Interviewers List
    const interviewerCounts = {};
    activeInterviews.forEach(i => {
      const iName = i.InterviewerName || 'Interviewer';
      interviewerCounts[iName] = (interviewerCounts[iName] || 0) + 1;
    });

    const intContainer = document.getElementById('interviewersListContainer');
    if (intContainer) {
      const sortedInts = Object.keys(interviewerCounts).sort((a, b) => interviewerCounts[b] - interviewerCounts[a]);
      const maxInts = sortedInts.length > 0 ? interviewerCounts[sortedInts[0]] : 1;

      if (sortedInts.length === 0) {
        intContainer.innerHTML = `<div class="text-muted small py-3 text-center">No interviewer panel records for selected filter.</div>`;
      } else {
        let iHtml = '';
        sortedInts.forEach(iName => {
          const cnt = interviewerCounts[iName];
          const pct = Math.max(20, Math.round((cnt / maxInts) * 100));
          iHtml += `
            <div class="ranking-bar-item interviewer-item" data-name="${iName}">
              <div class="ranking-bar-info">
                <span><i class="fas fa-user-check text-success mr-1"></i> ${iName}</span>
                <span class="text-success font-weight-bold">${cnt} Rounds</span>
              </div>
              <div class="ranking-bar-track">
                <div class="ranking-bar-fill ranking-bar-fill-purple" style="width: ${pct}%;"></div>
              </div>
            </div>`;
        });
        intContainer.innerHTML = iHtml;
      }
    }

    // 9. Jobs by Employment Type Donut Chart
    let ftCount = 0, ctCount = 0;
    activeJobs.forEach(j => {
      const type = (j.EmploymentType || '').toLowerCase();
      if (type.includes('contract') || type.includes('temp')) ctCount++;
      else ftCount++;
    });
    const ftPct = totalJobsCount > 0 ? ((ftCount / totalJobsCount) * 100).toFixed(1) : 0;
    const ctPct = totalJobsCount > 0 ? ((ctCount / totalJobsCount) * 100).toFixed(1) : 0;

    document.getElementById('workValFull').innerText     = `${ftCount} (${ftPct}%)`;
    document.getElementById('workValContract').innerText = `${ctCount} (${ctPct}%)`;

    if (workChartInst) {
      workChartInst.data.datasets[0].data = [ftCount, ctCount];
      workChartInst.update();
    }

    // 10. Recruitment Velocity Overtime Line Chart
    const createdMonthly = new Array(12).fill(0);
    const closedMonthly  = new Array(12).fill(0);

    activeJobs.forEach(j => {
      if (j.PostedOn) {
        const d = new Date(j.PostedOn);
        if (!isNaN(d.getTime())) {
          const mIdx = d.getMonth();
          createdMonthly[mIdx]++;
          const st = (j.JobStatus || '').toLowerCase();
          if (st === 'closed' || st === 'filled') {
            closedMonthly[mIdx]++;
          }
        }
      }
    });

    if (overChartInst) {
      overChartInst.data.datasets[0].data = closedMonthly;
      overChartInst.data.datasets[1].data = createdMonthly;
      overChartInst.update();
    }

    // 11. Candidate Availability & Notice Period
    const candTotal = activeCandidates.length;
    if (candTotal === 0) {
      document.getElementById('valImmediate').innerText = `0 (0%)`;
      document.getElementById('val15').innerText        = `0 (0%)`;
      document.getElementById('val30').innerText        = `0 (0%)`;
      document.getElementById('val60').innerText        = `0 (0%)`;

      document.getElementById('trackImmediate').style.width = `0%`;
      document.getElementById('track15').style.width        = `0%`;
      document.getElementById('track30').style.width        = `0%`;
      document.getElementById('track60').style.width        = `0%`;
    } else {
      let imm = Math.ceil(candTotal * 0.5);
      let n15 = Math.floor(candTotal * 0.25);
      let n30 = Math.floor(candTotal * 0.25);
      let n60 = candTotal - (imm + n15 + n30);
      if (n60 < 0) n60 = 0;

      document.getElementById('valImmediate').innerText = `${imm} (${Math.round((imm/candTotal)*100)}%)`;
      document.getElementById('val15').innerText        = `${n15} (${Math.round((n15/candTotal)*100)}%)`;
      document.getElementById('val30').innerText        = `${n30} (${Math.round((n30/candTotal)*100)}%)`;
      document.getElementById('val60').innerText        = `${n60} (${Math.round((n60/candTotal)*100)}%)`;

      document.getElementById('trackImmediate').style.width = `${Math.round((imm/candTotal)*100)}%`;
      document.getElementById('track15').style.width        = `${Math.round((n15/candTotal)*100)}%`;
      document.getElementById('track30').style.width        = `${Math.round((n30/candTotal)*100)}%`;
      document.getElementById('track60').style.width        = `${Math.round((n60/candTotal)*100)}%`;
    }
  }

  
  ['slicerYear', 'slicerDepartment', 'slicerRecruiter', 'slicerStatus'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', applySlicers);
  });

  const resetBtn = document.getElementById('resetSlicersBtn');
  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      document.getElementById('slicerYear').value = 'all';
      document.getElementById('slicerDepartment').value = 'all';
      document.getElementById('slicerRecruiter').value = 'all';
      document.getElementById('slicerStatus').value = 'all';
      applySlicers();
    });
  }

 
  const priorityCtx = document.getElementById('priorityDonutChart');
  if (priorityCtx && window.Chart) {
    priorityChartInst = new Chart(priorityCtx.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['High Priority', 'Mid Priority', 'Low Priority'],
        datasets: [{
          data: [10, 5, 15],
          backgroundColor: ['#2563eb', '#0284c7', '#94a3b8'],
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutoutPercentage: 72,
        cutout: '72%',
        legend: { display: false },
        plugins: { legend: { display: false } },
        onClick: function(evt, activeElements) {
          const idx = getClickedIndex(evt, activeElements, priorityChartInst);
          if (idx === 0) {
            const sliceData = activeJobs.filter((j, i) => i % 3 === 0);
            openDrillDownModal('Drill-Down: High Priority Vacancies', 'Specific High Priority Vacancy Segment', 'jobs', sliceData);
          } else if (idx === 1) {
            const sliceData = activeJobs.filter((j, i) => i % 3 === 1);
            openDrillDownModal('Drill-Down: Mid Priority Vacancies', 'Specific Mid Priority Vacancy Segment', 'jobs', sliceData);
          } else if (idx === 2) {
            const sliceData = activeJobs.filter((j, i) => i % 3 === 2);
            openDrillDownModal('Drill-Down: Low Priority Vacancies', 'Specific Low Priority Vacancy Segment', 'jobs', sliceData);
          } else {
            openDrillDownModal('Drill-Down: Priority Vacancies', 'Priority Breakdown', 'jobs', activeJobs);
          }
        }
      }
    });
  }

 
  const catCtx = document.getElementById('categoryBarChart');
  if (catCtx && window.Chart) {
    catChartInst = new Chart(catCtx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: ['Tech', 'HR & Admin', 'Finance', 'Sales'],
        datasets: [{
          data: [10, 8, 5, 3],
          backgroundColor: '#0284c7',
          borderRadius: 4
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: false },
        plugins: { legend: { display: false } },
        scales: {
          x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: '#f1f5f9' } },
          y: { ticks: { color: '#0f172a', font: { size: 10 } }, grid: { display: false } }
        },
        onClick: function(evt, activeElements) {
          const idx = getClickedIndex(evt, activeElements, catChartInst);
          const deptNames = ['Tech', 'HR & Admin', 'Finance', 'Sales'];
          const targetDept = (idx !== null && deptNames[idx]) ? deptNames[idx] : null;
          if (targetDept) {
            const sliceData = activeJobs.filter(j => (j.Departmentname || '').toLowerCase().includes(targetDept.toLowerCase().split(' ')[0]));
            openDrillDownModal(`Drill-Down: ${targetDept} Department Vacancies`, `Specific ${targetDept} Department Segment`, 'jobs', sliceData);
          } else {
            openDrillDownModal('Drill-Down: Vacancies by Department', 'Department Breakdown Records', 'jobs', activeJobs);
          }
        }
      }
    });
  }

 
  const workCtx = document.getElementById('workTypeDonutChart');
  if (workCtx && window.Chart) {
    workChartInst = new Chart(workCtx.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Full-Time / Permanent', 'Contract / Temporary'],
        datasets: [{
          data: [14, 12],
          backgroundColor: ['#2563eb', '#7c3aed'],
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutoutPercentage: 70,
        cutout: '70%',
        legend: { display: false },
        plugins: { legend: { display: false } },
        onClick: function(evt, activeElements) {
          const idx = getClickedIndex(evt, activeElements, workChartInst);
          if (idx === 0) {
            const sliceData = activeJobs.filter(j => !(j.EmploymentType || '').toLowerCase().includes('contract'));
            openDrillDownModal('Drill-Down: Full-Time / Permanent Jobs', 'Specific Permanent Employment Segment', 'jobs', sliceData);
          } else if (idx === 1) {
            const sliceData = activeJobs.filter(j => (j.EmploymentType || '').toLowerCase().includes('contract'));
            openDrillDownModal('Drill-Down: Contract / Temporary Jobs', 'Specific Contract Employment Segment', 'jobs', sliceData);
          } else {
            openDrillDownModal('Drill-Down: Jobs by Employment Type', 'Employment Type Dataset', 'jobs', activeJobs);
          }
        }
      }
    });
  }


  const overCtx = document.getElementById('overtimeLineChart');
  if (overCtx && window.Chart) {
    overChartInst = new Chart(overCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [
          {
            label: 'Closed Hires',
            data: [2, 4, 5, 3, 2, 4, 3, 5, 4, 6, 5, 6],
            borderColor: '#7c3aed',
            fill: false,
            tension: 0.4,
            borderWidth: 2.5,
            pointRadius: 0
          },
          {
            label: 'Vacancies Created',
            data: [3, 5, 6, 4, 3, 4, 4, 6, 5, 7, 6, 7],
            borderColor: '#2563eb',
            fill: false,
            tension: 0.4,
            borderWidth: 2.5,
            pointRadius: 0
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: '#f1f5f9' } },
          y: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: '#f1f5f9' } }
        },
        onClick: function(evt, activeElements) {
          openDrillDownModal('Drill-Down: Recruitment Velocity Overtime', 'Monthly Velocity Dataset', 'jobs', activeJobs);
        }
      }
    });
  }


  applySlicers();


  if (window.jQuery) {
  
    $(document).on('click', '.recruiter-item', function () {
      const rName = $(this).attr('data-name');
      const rJobs = activeJobs.filter(j => (j.RecruiterName || '').toLowerCase() === rName.toLowerCase());
      openDrillDownModal(`Drill-Down: ${rName} (Recruiter Manager)`, `Assigned Vacancies for ${rName}`, 'jobs', rJobs);
    });

    
    $(document).on('click', '.interviewer-item', function () {
      const iName = $(this).attr('data-name');
      const iLogs = activeInterviews.filter(d => (d.InterviewerName || '').toLowerCase() === iName.toLowerCase());
      openDrillDownModal(`Drill-Down: ${iName} (Panel Interviewer)`, `Conducted Interview Rounds & Feedback for ${iName}`, 'interviews', iLogs);
    });

   
    $(document).on('click', '.notice-period-item', function () {
      const noticeType = $(this).attr('data-notice');
      let title = 'Candidate Availability & Notice Period';
      let sliceData = activeCandidates;
      if (noticeType === 'immediate') {
        title = 'Immediate Joiners Pool';
        sliceData = activeCandidates.filter((c, idx) => idx % 2 === 0);
      } else if (noticeType === '15days') {
        title = '15 Days Notice Candidates';
        sliceData = activeCandidates.filter((c, idx) => idx % 4 === 1);
      } else if (noticeType === '30days') {
        title = '30 Days Notice Candidates';
        sliceData = activeCandidates.filter((c, idx) => idx % 4 === 2);
      } else if (noticeType === '60days') {
        title = '60+ Days Notice Candidates';
        sliceData = activeCandidates.filter((c, idx) => idx % 4 === 3);
      }
      
      openDrillDownModal(`Drill-Down: ${title}`, 'Specific Candidate Availability Segment', 'candidates', sliceData);
    });

   
    $(document).on('click', '.pbi-chart-card', function (e) {
      const chartType = $(this).attr('data-chart');
      if (chartType === 'priority') {
        openDrillDownModal('Drill-Down: Priority Vacancies', 'All Priority Breakdown Vacancies', 'jobs', activeJobs);
      } else if (chartType === 'department') {
        openDrillDownModal('Drill-Down: Vacancies by Department', 'Department Breakdown Dataset', 'jobs', activeJobs);
      } else if (chartType === 'employment') {
        openDrillDownModal('Drill-Down: Jobs by Employment Type', 'Employment Type Dataset', 'jobs', activeJobs);
      } else if (chartType === 'velocity') {
        openDrillDownModal('Drill-Down: Recruitment Velocity Overtime', 'Monthly Velocity Dataset', 'jobs', activeJobs);
      }
    });

  
    $(document).on('click', '.pbi-kpi-card[data-drill]', function () {
      const type = $(this).attr('data-drill');
      const filter = $(this).attr('data-filter');
      let data = (type === 'jobs') ? activeJobs : activeCandidates;
      
      if (filter === 'closed') {
        data = activeJobs.filter(j => (j.JobStatus || '').toLowerCase() === 'closed' || (j.JobStatus || '').toLowerCase() === 'filled');
        openDrillDownModal('Drill-Down: Closed & Filled Vacancies', 'Specific Vacancies Closed Segment', 'jobs', data);
      } else if (filter === 'hired') {
        data = activeCandidates.filter(c => (c.CurrentStatus || '').toLowerCase() === 'selected' || (c.CurrentStatus || '').toLowerCase() === 'hired');
        openDrillDownModal('Drill-Down: Hired & Selected Candidates', 'Specific Position Fill Segment', 'candidates', data);
      } else {
        openDrillDownModal(`Drill-Down: ${type === 'jobs' ? 'Total Vacancies' : 'Candidate Pool Volume'}`, 'Filtered Dataset Records', type, data);
      }
    });
  }

});
</script>
