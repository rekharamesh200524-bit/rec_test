<?php
$all_jobs = isset($all_jobs) ? $all_jobs : [];
$all_candidates = isset($all_candidates) ? $all_candidates : [];
$departments = isset($departments) ? $departments : [];
$resource_requests_list = isset($resource_requests_list) ? $resource_requests_list : [];
$recruitment_stages = isset($recruitment_stages) ? $recruitment_stages : [];

$ci = &get_instance();
$employee_det = (isset($this) && isset($this->session)) ? $this->session->userdata('logged_in') : (isset($ci->session) ? $ci->session->userdata('logged_in') : []);
$roleId = isset($employee_det['EmpRoleId']) ? (int)$employee_det['EmpRoleId'] : 1;
$isHiringManager = ($roleId === 9);

$dashboardTitle = $isHiringManager 
    ? 'Hiring Manager Candidate Evaluation Portal' 
    : (($roleId == 1 || $roleId == 2) ? 'Executive Recruitment Operations Dashboard' : 'Department HR Portal');

$dashboardSubtitle = $isHiringManager 
    ? 'Review candidate applications, interview evaluations, and hiring progress for your department.' 
    : 'Real-time talent acquisition tracking, candidate stages, and predictive analytics.';

$emp_name = isset($employee_det['EmpName']) ? $employee_det['EmpName'] : 'HR Manager';
?>

<link rel="stylesheet" href="<?= base_url('themes/bo_theme/css/dashboard-theme.css') ?>">

<div id="dashboard-wrapper" class="<?= $isHiringManager ? 'theme-candidate' : 'theme-job' ?>">
<section class="content pt-3 pb-4">
  <div class="container-fluid">

    <div id="quoteBanner">
      <div class="row align-items-center">
        <div class="col-lg-8 col-md-8 col-sm-12">
          <div class="status-badge-live">
            <span class="pulse-dot"></span> System Operational &bull; Recruitment Portal Live
          </div>
          <span class="quote-icon">&ldquo;</span>
          <p class="quote-text" id="quoteText">Loading recruitment inspiration...</p>
          <p class="quote-author" id="quoteAuthor"></p>
          <div class="quote-dots" id="quoteDots"></div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12 mt-3 mt-md-0">
          <div class="banner-greeting">
            <div class="greeting-time" id="liveClock">--:--:--</div>
            <div class="greeting-name">&#128075; Welcome back, <?= htmlspecialchars($emp_name) ?></div>
            <div class="greeting-date" id="liveDate"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
      <div>
        <h2 class="h4 mb-1 font-weight-bold text-dark d-flex align-items-center">
          <i class="fas fa-user-check text-primary mr-2 page-title-icon"></i><?= $dashboardTitle ?>
        </h2>
        <p class="text-muted small mb-0"><?= $dashboardSubtitle ?></p>
      </div>

      <div class="d-flex align-items-center flex-wrap gap-2 mt-3 mt-sm-0 ml-auto">
        <a href="<?= base_url('admin/RequestedResources') ?>" class="btn btn-primary action-pill-btn shadow-sm mr-2">
          <i class="fas fa-plus-circle"></i> Request Resource
        </a>
        <?php if (!$isHiringManager): ?>
        <div class="btn-group btn-group-toggle shadow-sm ml-2" id="dashboardToggle" role="group" aria-label="Dashboard toggle">
          <button type="button" class="btn btn-toggle-job active" data-toggle-target="job">
            <i class="fas fa-briefcase mr-1"></i> Jobs
          </button>
          <button type="button" class="btn btn-toggle-candidate" data-toggle-target="candidate">
            <i class="fas fa-users mr-1"></i> Candidates
          </button>
        </div>
        <?php endif; ?>
      </div>
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
                  The vacancy <strong><?= htmlspecialchars($rem['JobTitle']) ?></strong> (<code><?= htmlspecialchars($rem['JobCode']) ?></code>) hold period ended on <strong><?= date('d M Y', strtotime($rem['HoldUntilDate'])) ?></strong>. Please review and resume recruitment.
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
      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-total">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-layer-group"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-total">0</h3>
              <p class="mb-0 kpi-label" id="label-total">Total</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-open">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-folder-open"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-open">0</h3>
              <p class="mb-0 kpi-label" id="label-open">Open</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-pending">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-file-invoice"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-pending"><?= (int)($pending_resource_requests ?? 0); ?></h3>
              <p class="mb-0 kpi-label" id="label-pending">Pending Requests</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-hold">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-pause-circle"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-hold">0</h3>
              <p class="mb-0 kpi-label" id="label-hold">Hold</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-rejected">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-times-circle"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-rejected">0</h3>
              <p class="mb-0 kpi-label" id="label-rejected">Cancelled</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 mb-3">
        <div class="card kpi-card shadow-sm h-100 border-0">
          <div class="card-body d-flex align-items-center kpi-card-body kpi-bg-closed">
            <span class="kpi-icon-box text-white mr-2">
              <i class="fas fa-check-circle"></i>
            </span>
            <div class="info-box-content">
              <h3 class="mb-0 kpi-value" id="kpi-closed">0</h3>
              <p class="mb-0 kpi-label" id="label-closed">Hired</p>
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
                <?php if ($roleId == 1 || $roleId == 2): ?>
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
            <div class="d-flex align-items-center justify-content-center mb-3 style-chart-box" style="min-height:220px; position: relative;">
              <canvas id="dynamicDistributionChart" class="chart-canvas"></canvas>
              <div id="candidatePipeline" class="w-100" style="display:none;"></div>
              <div id="noChartDataState" class="text-center p-3 w-100" style="display:none;">
                <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; border-radius: 50%; background-color: #f8fafc; border: 2px dashed #cbd5e1; color: #94a3b8; font-size: 24px;">
                  <i class="fas fa-chart-pie"></i>
                </div>
                <h6 class="font-weight-bold text-dark mb-1">No Data on Chart</h6>
                <p class="text-muted small mb-0" id="noChartDataMsg">No distribution data available for the selected filters.</p>
              </div>
            </div>
            <div id="chartDetails" class="mt-2 overflow-auto"></div>
          </div>
        </div>

        <div class="card shadow-sm hr-tip-card border-0">
          <div class="card-body p-3">
            <div class="d-flex align-items-center mb-2">
              <span class="hr-tip-icon">&#128161;</span>
              <span class="hr-tip-title">HR Tip of the Day</span>
            </div>
            <p class="mb-0 hr-tip-text" id="hrTipText">Loading tip...</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

  const hiringQuotes = [
    { text: "Hiring is not just about filling roles \u2014 it\u2019s about shaping the future of your organization.", author: "\u2014 HR Excellence" },
    { text: "Great vision without great people is irrelevant. The right hire changes everything.", author: "\u2014 Jim Collins" },
    { text: "Every great business is built by exceptional people. Hire character, train skill.", author: "\u2014 Peter Schutz" },
    { text: "Culture eats strategy for breakfast. Hire for culture fit, and your team will thrive.", author: "\u2014 Peter Drucker" },
    { text: "The secret of my success is that we have gone to exceptional lengths to hire the best people in the world.", author: "\u2014 Steve Jobs" },
    { text: "You need to have a collaborative hiring process. Hire thoughtfully, not urgently.", author: "\u2014 Laszlo Bock, Google" },
    { text: "Your employees are your company\u2019s real competitive advantage. Hire wisely.", author: "\u2014 Anne Mulcahy" },
    { text: "Talent wins games, but teamwork and intelligence win championships. Hire both.", author: "\u2014 Michael Jordan" },
    { text: "A company\u2019s most valuable asset is not its people \u2014 it\u2019s the hiring process that attracts them.", author: "\u2014 Talent Insight" }
  ];
  const hrTips = [
    "Always send a personalised rejection email \u2014 candidates remember how you made them feel.",
    "Structured interviews reduce bias by 40%. Use the same questions for every candidate.",
    "Follow up within 48 hours of an interview. Speed signals respect and company culture.",
    "Job descriptions with inclusive language attract 42% more diverse applicants.",
    "Employee referrals produce the highest quality hires. Invest in your referral programme.",
    "Video interviews save 60% of scheduling time. Embrace async screening for early stages.",
    "Use ATS data to identify your best-performing sourcing channels every quarter.",
    "Set clear hiring SLAs: target 30 days from JD approval to offer letter.",
    "Onboarding doesn\u2019t end on day one. A 90-day plan dramatically improves retention."
  ];

  const tipEl = document.getElementById('hrTipText');
  if (tipEl) tipEl.textContent = hrTips[Math.floor(Math.random() * hrTips.length)];

  let currentQuote = 0;
  const quoteTextEl = document.getElementById('quoteText');
  const quoteAuthEl = document.getElementById('quoteAuthor');
  const quoteDotsEl = document.getElementById('quoteDots');

  function buildDots() {
    if (!quoteDotsEl) return;
    quoteDotsEl.innerHTML = '';
    hiringQuotes.forEach((_, i) => {
      const dot = document.createElement('span');
      dot.className = 'quote-dot' + (i === 0 ? ' active' : '');
      dot.onclick = () => showQuote(i);
      quoteDotsEl.appendChild(dot);
    });
  }
  function showQuote(idx) {
    currentQuote = idx;
    if (quoteTextEl) {
      quoteTextEl.textContent = hiringQuotes[idx].text;
    }
    if (quoteAuthEl) quoteAuthEl.textContent = hiringQuotes[idx].author;
    document.querySelectorAll('.quote-dot').forEach((d, i) => d.classList.toggle('active', i === idx));
  }
  buildDots();
  showQuote(0);
  setInterval(() => showQuote((currentQuote + 1) % hiringQuotes.length), 6000);

  function updateClock() {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const clockEl = document.getElementById('liveClock');
    if (clockEl) clockEl.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    const days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const dateEl = document.getElementById('liveDate');
    if (dateEl) dateEl.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
  }
  updateClock();
  setInterval(updateClock, 1000);

  const isHiringManager = <?= $isHiringManager ? 'true' : 'false' ?>;
  let activeToggle = isHiringManager ? 'candidate' : 'job';
  let selectedDept = '';
  let selectedMonth = '';
  let myChartInstance = null;

  const jobsData = <?= json_encode($all_jobs) ?>;
  const candidatesData = <?= json_encode($all_candidates) ?>;

  function init() {
    const toggleContainer = document.getElementById('dashboardToggle');
    if (toggleContainer) {
      const buttons = toggleContainer.querySelectorAll('button[data-toggle-target]');
      buttons.forEach(btn => {
        btn.addEventListener('click', function () {
          const target = this.getAttribute('data-toggle-target');
          activeToggle = (target === 'job') ? 'job' : 'candidate';
          updateDashboard();
        });
      });
    }

    const deptFilterEl = document.getElementById('deptFilter');
    if (deptFilterEl) {
      deptFilterEl.addEventListener('change', function () {
        selectedDept = this.value;
        updateDashboard();
      });
    }

    const monthFilterEl = document.getElementById('monthFilter');
    if (monthFilterEl) {
      monthFilterEl.addEventListener('change', function () {
        selectedMonth = this.value;
        updateDashboard();
      });
    }

    updateDashboard();
  }

  function updateDashboard() {
    const wrapper = document.getElementById('dashboard-wrapper');
    if (wrapper) {
      wrapper.className = (activeToggle === 'job') ? 'theme-job' : 'theme-candidate';
    }
    let filteredData = [];

    if (activeToggle === 'job') {
      filteredData = jobsData.filter(item => {
        if (selectedDept && String(item.Did) !== String(selectedDept)) return false;
        if (selectedMonth) {
          if (!item.PostedOn) return false;
          const month = new Date(item.PostedOn).getMonth() + 1;
          if (String(month) !== String(selectedMonth)) return false;
        }
        return true;
      });
    } else {
      filteredData = candidatesData.filter(item => {
        if (selectedDept && String(item.Did) !== String(selectedDept)) return false;
        if (selectedMonth) {
          if (!item.AppliedOn) return false;
          const month = new Date(item.AppliedOn).getMonth() + 1;
          if (String(month) !== String(selectedMonth)) return false;
        }
        return true;
      });
    }

    const summaryTitle = document.getElementById('summary-title');
    const chartTitle = document.getElementById('chart-title');

    let totalCount = filteredData.length;
    let openCount = 0;
    let holdCount = 0;
    let rejectedCount = 0;
    let closedCount = 0;

    if (activeToggle === 'job') {
      summaryTitle.innerHTML = '<i class="fas fa-briefcase text-primary mr-2"></i>Jobs Summary';
      chartTitle.innerHTML = '<i class="fas fa-chart-pie text-primary mr-2"></i>Job Status Distribution';

      document.getElementById('label-total').innerText = 'Total Jobs';
      document.getElementById('label-open').innerText = 'Open Jobs';
      document.getElementById('label-hold').innerText = 'Hold Jobs';
      document.getElementById('label-rejected').innerText = 'Cancelled';
      document.getElementById('label-closed').innerText = 'Closed';

      filteredData.forEach(job => {
        const status = (job.JobStatus || '').toLowerCase();
        if (status === 'open' || status === 're-open') {
          openCount++;
        } else if (status === 'on-hold') {
          holdCount++;
        } else if (status === 'not required') {
          rejectedCount++;
        } else if (status === 'closed' || status === 'dropped') {
          closedCount++;
        } else {
          openCount++;
        }
      });
    } else {
      summaryTitle.innerHTML = '<i class="fas fa-users text-teal mr-2"></i>Candidates Summary';
      chartTitle.innerHTML = '<i class="fas fa-project-diagram text-teal mr-2"></i>Recruitment Pipeline';

      document.getElementById('label-total').innerText = 'Total Applicants';
      document.getElementById('label-open').innerText = 'Active Applicants';
      document.getElementById('label-hold').innerText = 'On Hold';
      document.getElementById('label-rejected').innerText = 'Rejected';
      document.getElementById('label-closed').innerText = 'Hired';

      filteredData.forEach(cand => {
        const status = (cand.CurrentStatus || '').toLowerCase();
        if (status.includes('rejected')) {
          rejectedCount++;
        } else if (status.includes('accepted') || status.includes('boarding') || status.includes('hired')) {
          closedCount++;
        } else if (status.includes('hold') || status.includes('pending')) {
          holdCount++;
        } else {
          openCount++;
        }
      });
    }

    animateCounter('kpi-total', totalCount);
    animateCounter('kpi-open', openCount);
    animateCounter('kpi-hold', holdCount);
    animateCounter('kpi-rejected', rejectedCount);
    animateCounter('kpi-closed', closedCount);

    renderTable(filteredData);
    renderChart(openCount, holdCount, rejectedCount, closedCount);
    refreshToggleButtons();
  }

  function animateCounter(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    const current = parseInt(el.innerText) || 0;
    if (current === target) {
      el.innerText = target;
      return;
    }
    let start = current;
    const duration = 250;
    const stepTime = 25;
    const steps = duration / stepTime;
    const increment = (target - current) / steps;
    let step = 0;

    const timer = setInterval(() => {
      step++;
      start += increment;
      if (step >= steps) {
        clearInterval(timer);
        el.innerText = target;
      } else {
        el.innerText = Math.round(start);
      }
    }, stepTime);
  }

  function refreshToggleButtons() {
    const buttons = document.querySelectorAll('#dashboardToggle button[data-toggle-target]');
    buttons.forEach(btn => {
      const target = btn.getAttribute('data-toggle-target');
      if (target === activeToggle) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });
  }

  function getStatusBadge(status) {
    let s = (status || '').trim();
    let lower = s.toLowerCase();
    let displayText = s;

    if (lower === 'open' || lower === 're-open' || lower === 'active') {
      displayText = 'Active';
    } else {
      displayText = s.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
    }

    let bgColor = '#e2e8f0';
    let textColor = '#475569';

    if (lower === 'open' || lower === 're-open' || lower === 'active') {
      bgColor = '#dcfce7';
      textColor = '#15803d';
    } else if (lower === 'on-hold' || lower === 'hold' || lower === 'pending') {
      bgColor = '#fef3c7';
      textColor = '#b45309';
    } else if (lower === 'not required' || lower === 'cancelled' || lower.includes('reject') || lower === 'cancel') {
      bgColor = '#fee2e2';
      textColor = '#b91c1c';
    } else if (lower === 'closed' || lower.includes('hired') || lower.includes('accept') || lower.includes('board') || lower.includes('select')) {
      bgColor = '#e0e7ff';
      textColor = '#4338ca';
    }

    return `<span class="badge status-badge-custom" style="background-color: ${bgColor}; color: ${textColor};">${displayText}</span>`;
  }

  function renderTable(data) {
    if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#summaryTable')) {
      $('#summaryTable').DataTable().destroy();
      $('#summaryTable').empty();
    }

    const tableEl = document.getElementById('summaryTable');
    if (tableEl) {
      tableEl.innerHTML = `
        <thead class="bg-light text-dark">
          <tr id="tableHeader"></tr>
        </thead>
        <tbody id="tableBody"></tbody>
      `;
    }

    const header = document.getElementById('tableHeader');
    const body = document.getElementById('tableBody');
    const info = document.getElementById('tableInfo');

    if (!header || !body) return;

    header.innerHTML = '';
    body.innerHTML = '';

    if (activeToggle === 'job') {
      header.innerHTML = `
        <th>S. No</th>
        <th>Job Title</th>
        <th>Department</th>
        <th>Openings</th>
        <th>Posted Date</th>
        <th>Status</th>
      `;

      if (data.length === 0) {
        body.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block text-secondary mb-2"></i>No jobs found matching selected filters.</td></tr>`;
        return;
      }

      data.forEach((job, index) => {
        const dateStr = job.PostedOn ? new Date(job.PostedOn).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
        let badge = getStatusBadge(job.JobStatus || 'Draft');

        body.innerHTML += `
          <tr>
            <td><strong>${index + 1}</strong></td>
            <td><strong class="text-dark">${job.JobTitle || 'N/A'}</strong></td>
            <td><span class="badge badge-light border">${job.Departmentname || '-'}</span></td>
            <td><span class="badge badge-info px-2 py-1">${job.NoofOpenings || 1}</span></td>
            <td class="small text-muted">${dateStr}</td>
            <td>${badge}</td>
          </tr>
        `;
      });

    } else {
      header.innerHTML = `
        <th>S. No</th>
        <th>Candidate Name</th>
        <th>Email / Phone</th>
        <th>Vacancy / Role</th>
        <th>Interview Assignment</th>
        <th>Applied Date</th>
        <th>Status</th>
        <th class="text-center">Action</th>
      `;

      if (data.length === 0) {
        body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block text-secondary mb-2"></i>No candidates found matching selected filters.</td></tr>`;
        return;
      }

      data.forEach((cand, index) => {
        const dateStr = cand.AppliedOn ? new Date(cand.AppliedOn).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A';
        let badge = getStatusBadge(cand.CurrentStatus || 'Pending');
        let jid = cand.Jid || '';
        let candLink = jid ? `<?= base_url('admin/Candidatelist/') ?>${jid}` : '#';

        let hasInterviewRecord = (cand.InterviewId && String(cand.InterviewId) !== '' && String(cand.InterviewId) !== '0');
        let intBadge = '';
        if (hasInterviewRecord) {
          let interviewerName = cand.InterviewerName ? cand.InterviewerName : 'Interviewer';
          let schedStr = cand.ScheduledAt ? new Date(cand.ScheduledAt).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '';
          let schedText = schedStr ? ` (${schedStr})` : '';
          intBadge = `<span class="badge badge-success px-2 py-1"><i class="fas fa-calendar-check mr-1"></i>Assigned (${interviewerName}${schedText})</span>`;
        } else {
          intBadge = `<span class="badge badge-secondary px-2 py-1"><i class="fas fa-user-clock mr-1"></i>Not Assigned</span>`;
        }

        body.innerHTML += `
          <tr>
            <td><strong>${index + 1}</strong></td>
            <td><strong class="text-dark"><i class="fas fa-user-circle text-primary mr-1"></i>${cand.Fullname || 'N/A'}</strong></td>
            <td class="small">${cand.Email || '-'}<br><span class="text-muted">${cand.PhoneNo || ''}</span></td>
            <td><strong>${cand.JobTitle || 'N/A'}</strong> ${cand.JobCode ? `<code class="small">(${cand.JobCode})</code>` : ''}</td>
            <td>${intBadge}</td>
            <td class="small text-muted">${dateStr}</td>
            <td>${badge}</td>
            <td class="text-center">
              <a href="${candLink}" class="btn btn-outline-primary btn-xs font-weight-bold rounded-pill px-3">
                <i class="fas fa-eye mr-1"></i> Review Candidate
              </a>
            </td>
          </tr>
        `;
      });
    }

    if (info) info.style.display = 'none';

    if (window.jQuery && $.fn.DataTable) {
      $('#summaryTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 10,
        "buttons": ["csv", "excel", "pdf", "print"]
      }).buttons().container().appendTo('#summaryTable_wrapper .col-md-6:eq(0)');
    }
  }

  function renderChart(open, hold, rejected, closed) {
    const canvas = document.getElementById('dynamicDistributionChart');
    const pipelineEl = document.getElementById('candidatePipeline');
    const noDataEl = document.getElementById('noChartDataState');
    const noDataMsg = document.getElementById('noChartDataMsg');
    const detailsContainer = document.getElementById('chartDetails');
    if (!canvas || !pipelineEl) return;

    if (myChartInstance) {
      myChartInstance.destroy();
      myChartInstance = null;
    }

    const total = open + hold + rejected + closed;
    detailsContainer.innerHTML = '';

    if (total === 0) {
      canvas.style.display = 'none';
      pipelineEl.style.display = 'none';
      if (noDataEl) {
        noDataEl.style.display = 'block';
        if (noDataMsg) {
          noDataMsg.textContent = (activeToggle === 'job')
            ? 'No job status distribution data available for the selected filters.'
            : 'No candidate pipeline data available for the selected filters.';
        }
      }
      return;
    }

    if (noDataEl) {
      noDataEl.style.display = 'none';
    }

    if (activeToggle === 'job') {
      canvas.style.display = 'block';
      pipelineEl.style.display = 'none';

      const labels = ['Open Jobs', 'On Hold', 'Cancelled', 'Closed'];
      const dataValues = [open, hold, rejected, closed];
      const bgColors = ['#10b981', '#f59e0b', '#ef4444', '#64748b'];

      labels.forEach((label, idx) => {
        const val = dataValues[idx];
        const percentage = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
        const color = bgColors[idx];

        detailsContainer.innerHTML += `
          <div class="mb-2">
            <div class="d-flex justify-content-between mb-1 small font-weight-bold">
              <span><i class="fas fa-circle mr-2" style="color: ${color}; font-size:10px;"></i>${label}</span>
              <span>${val} <small class="text-muted font-weight-normal">(${percentage}%)</small></span>
            </div>
            <div class="progress dist-progress">
              <div class="progress-bar dist-progress-bar" style="width: ${percentage}%; background-color: ${color};"></div>
            </div>
          </div>
        `;
      });

      if (window.Chart) {
        myChartInstance = new Chart(canvas.getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: labels,
            datasets: [{
              data: dataValues,
              backgroundColor: bgColors,
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
              legend: { display: false }
            }
          }
        });
      }
    } else {
      canvas.style.display = 'none';
      pipelineEl.style.display = 'block';

      const activePercent = total > 0 ? ((open / total) * 100).toFixed(0) : 0;
      const holdPercent = total > 0 ? ((hold / total) * 100).toFixed(0) : 0;
      const hiredPercent = total > 0 ? ((closed / total) * 100).toFixed(0) : 0;

      pipelineEl.innerHTML = `
        <div class="pipeline-funnel p-2">
          <div class="pipeline-step mb-3">
            <div class="d-flex justify-content-between font-weight-bold mb-1 small">
              <span><i class="fas fa-inbox text-teal mr-2"></i>1. Total Applications</span>
              <span class="badge badge-secondary px-2">${total}</span>
            </div>
            <div class="progress dist-progress"><div class="progress-bar" style="width: 100%; background-color: #0d9488;"></div></div>
          </div>
          <div class="pipeline-step mb-3">
            <div class="d-flex justify-content-between font-weight-bold mb-1 small">
              <span><i class="fas fa-user-check text-indigo mr-2"></i>2. Active / In Screening</span>
              <span class="badge badge-info px-2">${open} (${activePercent}%)</span>
            </div>
            <div class="progress dist-progress"><div class="progress-bar" style="width: ${activePercent}%; background-color: #6366f1;"></div></div>
          </div>
          <div class="pipeline-step mb-3">
            <div class="d-flex justify-content-between font-weight-bold mb-1 small">
              <span><i class="fas fa-pause-circle text-warning mr-2"></i>3. Shortlisted / On Hold</span>
              <span class="badge badge-warning px-2">${hold} (${holdPercent}%)</span>
            </div>
            <div class="progress dist-progress"><div class="progress-bar" style="width: ${holdPercent}%; background-color: #f97316;"></div></div>
          </div>
          <div class="pipeline-step mb-2">
            <div class="d-flex justify-content-between font-weight-bold mb-1 small">
              <span><i class="fas fa-handshake text-success mr-2"></i>4. Hired</span>
              <span class="badge badge-success px-2">${closed} (${hiredPercent}%)</span>
            </div>
            <div class="progress dist-progress"><div class="progress-bar" style="width: ${hiredPercent}%; background-color: #10b981;"></div></div>
          </div>
        </div>
      `;

      detailsContainer.innerHTML = `
        <div class="alert alert-light border-0 mb-0" style="background-color: rgba(13, 148, 136, 0.05); border-radius:12px;">
          <h6 class="font-weight-bold text-teal mb-1 small"><i class="fas fa-bullseye mr-1"></i>Hiring Conversion</h6>
          <p class="mb-0 text-muted small">
            Out of <strong>${total}</strong> applicants, <strong>${closed}</strong> candidates have been hired (Conversion: <strong>${total > 0 ? ((closed/total)*100).toFixed(1) : 0}%</strong>).
          </p>
        </div>
      `;
    }
  }

  init();
});
</script>
