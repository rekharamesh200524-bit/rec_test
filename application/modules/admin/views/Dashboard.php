<?php
$employee_det = $this->session->userdata('logged_in');

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = $this->config->item('theme_locations').$this->config->item('active_template');

$all_jobs = isset($all_jobs) ? $all_jobs : [];
$all_candidates = isset($all_candidates) ? $all_candidates : [];
$departments = isset($departments) ? $departments : [];
//reka....
$Resource_Requests_list = isset($Resource_Requests_list) ? $Resource_Requests_list : [];
$recruitment_stages = isset($recruitment_stages) ? $recruitment_stages : [];

$roleId = isset($employee_det['EmpRoleId']) ? (int)$employee_det['EmpRoleId'] : 1;
$isHiringManager = isset($isHiringManager) ? $isHiringManager : false;
$isManagement = isset($isManagement) ? $isManagement : false;

$dashboardTitle = $isHiringManager 
    ? 'Hiring Manager Candidate Evaluation Portal' 
    : ($isManagement ? 'Executive Recruitment Operations Dashboard' : 'Department HR Portal');

$dashboardSubtitle = $isHiringManager 
    ? 'Review candidate applications, interview evaluations, and hiring progress for your department.' 
    : 'Real-time talent acquisition tracking, candidate stages, and predictive analytics.';

$emp_name = isset($employee_det['EmpName']) ? $employee_det['EmpName'] : 'HR Manager';
?>

<link rel="stylesheet" href="<?= base_url('themes/bo_theme/css/dashboard-theme.css') ?>">

<div id="dashboard-wrapper" class="<?= $isHiringManager ? 'theme-candidate' : 'theme-job' ?>">
<section class="content pt-3 pb-4">
  <div class="container-fluid">




    <div class="d-flex justify-content-end align-items-center flex-wrap mb-4">
      <?php if (!$isHiringManager): ?>
      <div class="dash-switch-container shadow-sm ml-auto" id="dashboardToggle">
        <button type="button" class="dash-switch-btn btn-toggle-job active" data-toggle-target="job">
          <i class="fas fa-briefcase mr-1"></i> Jobs
        </button>
        <button type="button" class="dash-switch-btn btn-toggle-candidate" data-toggle-target="candidate">
          <i class="fas fa-users mr-1"></i> Candidates
        </button>
      </div>
      <?php endif; ?>
    </div>



    <?php if (!empty($onhold_reminders)): ?>
      <div class="mb-4">
        <?php foreach ($onhold_reminders as $rem): ?>
          <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 glass-card" role="alert">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
              <div class="py-1">
                <h6 class="alert-heading font-weight-bold mb-1 text-dark d-flex align-items-center">
                  <i class="fas fa-bell text-warning mr-2 animate-pulse"></i> Vacancy Hold Expiry Notification
                </h6>
                <p class="mb-0 text-dark small">
                  The vacancy <strong><?= htmlspecialchars($rem['JobTitle']) ?></strong> (<code><?= htmlspecialchars($rem['JobCode']) ?></code>) hold period ended on <strong><?= date('d-m-Y', strtotime($rem['HoldUntilDate'])) ?></strong>. Please review and resume recruitment.
                </p>
              </div>
              <div class="mt-2 mt-sm-0">
                <a href="<?= base_url('admin/VaccancyList') ?>" class="btn btn-sm btn-warning text-dark font-weight-bold shadow-sm rounded-pill">
                  <i class="fas fa-eye mr-1"></i> Manage Vacancies
                </a>
              </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>


    <div class="row mb-4">
      <!-- Card 1: Total Jobs -->
      <div class="col-xl col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-total">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-layer-group"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-total">0</h3>
              <p class="mb-0 kpi-label" id="label-total">Total Jobs</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 2: Open Jobs -->
      <div class="col-xl col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-open">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-folder-open"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-open">0</h3>
              <p class="mb-0 kpi-label" id="label-open">Open Jobs</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 3: Hold Jobs -->
      <div class="col-xl col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-hold">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-pause-circle"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-hold">0</h3>
              <p class="mb-0 kpi-label" id="label-hold">Hold Jobs</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 4: Dropped -->
      <div class="col-xl col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-rejected">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-times-circle"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-dropped">0</h3>
              <p class="mb-0 kpi-label" id="label-dropped">Dropped</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 5: Closed -->
      <div class="col-xl col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-closed">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-archive"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-closed">0</h3>
              <p class="mb-0 kpi-label" id="label-closed">Closed</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row analytics-row">
      <div class="col-lg-8 mb-4">
        <div class="card dashboard-card h-100 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
              <h5 class="card-title mb-0" id="summary-title">
                <i class="fas fa-users text-primary mr-2"></i>Candidates Summary
              </h5>

              <div class="d-flex flex-wrap align-items-center gap-2">
                <?php if ($isManagement): ?>
                <div class="form-group mb-0 mr-2">
                  <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fas fa-building text-primary"></i></span>
                    </div>
                    <select id="deptFilter" class="form-control form-control-sm font-weight-bold">
                      <option value="">All Departments</option>
                      <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['Did'] ?>"><?= htmlspecialchars($dept['Departmentname']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <?php endif; ?>

                <div class="form-group mb-0">
                  <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="far fa-calendar-alt text-primary"></i></span>
                    </div>
                    <select id="monthFilter" class="form-control form-control-sm font-weight-bold">
                      <option value="">All Months</option>
                      <option value="1">January</option>
                      <option value="2">February</option>
                      <option value="3">March</option>
                      <option value="4">April</option>
                      <option value="5">May</option>
                      <option value="6">June</option>
                      <option value="7">July</option>
                      <option value="8">August</option>
                      <option value="9">September</option>
                      <option value="10">October</option>
                      <option value="11">November</option>
                      <option value="12">December</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table id="summaryTable" class="table table-bordered table-striped w-100">
                <thead class="bg-light text-dark">
                  <tr id="tableHeader"></tr>
                </thead>
                <tbody id="tableBody"></tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 text-muted small" id="tableInfo">
              <span>Showing 0 entries</span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 mb-4">
        <div class="card dashboard-card shadow-sm mb-4">
          <div class="card-header bg-light">
            <h5 class="card-title mb-0" id="chart-title">
              <i class="fas fa-chart-pie text-primary mr-2"></i>Status Distribution
            </h5>
          </div>
          <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-center justify-content-center mb-3 style-chart-box" style="min-height:220px;">
              <canvas id="dynamicDistributionChart" class="chart-canvas"></canvas>
              <div id="candidatePipeline" class="w-100" style="display:none;"></div>
            </div>
            <div id="chartDetails" class="mt-2 overflow-auto"></div>
          </div>
        </div>


      </div>
    </div>

  </div>
</section>
</div>

<script type="application/json" id="dashboardDataStore" data-is-hm="<?= $isHiringManager ? '1' : '0' ?>"><?= json_encode(['jobs' => $all_jobs, 'candidates' => $all_candidates]); ?></script>
