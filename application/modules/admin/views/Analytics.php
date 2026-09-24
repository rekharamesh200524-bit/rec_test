<?php
$employee_det = $this->session->userdata('logged_in');

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = $this->config->item('theme_locations').$this->config->item('active_template');

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


<script type="application/json" id="analyticsRawData"><?= json_encode(array(
    'rawJobs' => $all_jobs_history,
    'rawCandidates' => $all_candidates_history,
    'rawRecruiters' => $recruiter_analytics,
    'rawInterviewsSummary' => $interviewer_summary,
    'rawInterviewsDetail' => $interviewer_details,
    'rawDepartments' => array_values(array_unique(array_filter(array_column($departments, 'Departmentname'))))
)); ?></script>
