<?php
$employee_det = $this->session->userdata('logged_in');

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = $this->config->item('theme_locations').$this->config->item('active_template');
$today_count    = 0;
$upcoming_count = 0;
$total_count    = count($Candidatelist);
$now_ts         = time();
$upcoming_list  = [];

if(!empty($Candidatelist)) {
    foreach($Candidatelist as $iv) {
        if(empty($iv['ScheduledAt'])) continue;
        $ts = strtotime($iv['ScheduledAt']);
        if(date('Y-m-d', $ts) == date('Y-m-d')) $today_count++;
        if($ts > $now_ts) $upcoming_count++;
        
        if ($ts >= strtotime('today')) {
            $upcoming_list[] = $iv;
        }
    }
    
    usort($upcoming_list, function($a, $b) {
        return strtotime($a['ScheduledAt']) <=> strtotime($b['ScheduledAt']);
    });
}
?>
<link rel="stylesheet" href="<?= $theme_path ?>/assets/plugins/fullcalendar/main.min.css">

<section class="content">
<div class="container-fluid">

<div class="row mb-3">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $total_count ?></h3>
                <p>Total Assigned</p>
            </div>
            <div class="icon"><i class="fas fa-user-tie"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= $today_count ?></h3>
                <p>Today's Interviews</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $upcoming_count ?></h3>
                <p>Upcoming Interviews</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Interview Calendar</h3>
                <div class="card-tools">
                    <span class="badge badge-pill text-white font-weight-bold mr-1 px-3 py-1 shadow-sm" style="background-color:#007bff; font-size:12px;">Assigned</span>
                    <span class="badge badge-pill text-white font-weight-bold mr-1 px-3 py-1 shadow-sm" style="background-color:#28a745; font-size:12px;">Selected</span>
                    <span class="badge badge-pill text-white font-weight-bold mr-1 px-3 py-1 shadow-sm" style="background-color:#fd7e14; font-size:12px;">On Hold</span>
                    <span class="badge badge-pill text-white font-weight-bold px-3 py-1 shadow-sm"      style="background-color:#dc3545; font-size:12px;">Rejected</span>
                </div>
            </div>
            <div class="card-body">
                <div id="interviewCalendar" data-events='<?= htmlspecialchars($calendarEvents, ENT_QUOTES, "UTF-8"); ?>'></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-primary card-outline" style="display: flex; flex-direction: column; height: 100%;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Upcoming & Today</h3>
            </div>
            <div class="card-body" style="flex: 1; overflow-y: auto; max-height: 520px; padding: 0;">
                <?php if (!empty($upcoming_list)): ?>
                    <div class="list-group list-group-flush">
                        <?php 
                        $display_list = array_slice($upcoming_list, 0, 6);
                        foreach($display_list as $iv): 
                            $ts = strtotime($iv['ScheduledAt']);
                            $is_today = (date('Y-m-d', $ts) == date('Y-m-d'));
                            $time_str = date('h:i A', $ts);
                            $date_str = $is_today ? 'Today' : date('d-m-Y', $ts);
                            $result = strtolower(trim($iv['Result'] ?? ''));
                            
                            $badge_class = 'badge-primary';
                            $left_border_color = 'border-primary';
                            if($result == 'selected') {
                                $badge_class = 'badge-success';
                                $left_border_color = 'border-success';
                            } elseif($result == 'rejected') {
                                $badge_class = 'badge-danger';
                                $left_border_color = 'border-danger';
                            } elseif($result == 'on hold') {
                                $badge_class = 'badge-warning';
                                $left_border_color = 'border-warning';
                            } elseif($result == 'rescheduled') {
                                $badge_class = 'badge-warning text-dark';
                                $left_border_color = 'border-warning';
                            }
                        ?>
                            <a href="javascript:void(0)" class="list-group-item list-group-item-action border-left border-right-0 border-top-0 border-bottom-0 py-3" style="border-left-width: 4px !important; border-left-style: solid !important; border-bottom: 1px solid #e9ecef !important; <?= $result === 'rescheduled' ? 'background-color: #fff9e6;' : '' ?>" onclick="openInterviewDetail('<?= addslashes($iv['Fullname']) ?>', '<?= addslashes($iv['Email']) ?>', '<?= addslashes($iv['PhoneNo']) ?>', '<?= addslashes($iv['JobTitle']) ?>', '<?= $iv['ScheduledAt'] ?>', '<?= $iv['InterviewRound'] ?>', '<?= addslashes($iv['Result'] ?? 'Assigned') ?>', '<?= addslashes($iv['InterviewType'] ?? 'N/A') ?>', '<?= addslashes($iv['MeetLink'] ?? '') ?>')">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 14px;"><?= htmlspecialchars($iv['Fullname']) ?></h5>
                                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars(!empty($iv['Result']) ? $iv['Result'] : 'Assigned') ?></span>
                                </div>
                                <p class="mb-2 text-muted small"><i class="fas fa-briefcase mr-1"></i> <?= htmlspecialchars($iv['JobTitle']) ?></p>
                                <div class="d-flex w-100 justify-content-between align-items-center small">
                                    <span class="<?= $result === 'rescheduled' ? 'text-warning' : 'text-primary' ?> font-weight-bold">
                                        <i class="far fa-calendar-alt mr-1"></i> <?= $result === 'rescheduled' ? '<del class="text-muted">' . $date_str . ' at ' . $time_str . '</del>' : ($date_str . ' at ' . $time_str) ?>
                                    </span>
                                    <span class="text-muted">Round <?= $iv['InterviewRound'] ?? 1 ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-calendar-check fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                        <p class="mb-1 font-weight-bold">No interviews scheduled</p>
                        <p class="small text-muted mb-0">You are all caught up!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div>
</section>

<div class="modal fade" id="interviewDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-calendar-check mr-2"></i>Interview Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="interviewDetailBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="goToInterviewList">
                    <i class="fas fa-list mr-1"></i>Go to Interview List
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= $theme_path ?>/assets/plugins/fullcalendar/main.min.js"></script>

