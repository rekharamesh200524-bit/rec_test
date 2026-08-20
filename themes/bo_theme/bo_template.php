<!DOCTYPE html>
<html lang="en">
<?php
    $employee_det = $this->session->userdata('logged_in');
         $currentUrl = strtolower(uri_string());
         if (!isset($currentUrlArray)) {
             $currentUrlArray = ['parent' => null, 'child' => null];
         }
         $isActive   = !empty($currentUrlArray['child'])
              && strtolower($currentUrlArray['child']['Menuurl']) === $currentUrl;
       // echo "<pre>cur_view isActive"; print_r($employee_det); exit;       
     if(empty($employee_det)) { redirect($this->config->item('base_url').'admin/index'); }
?>
 
<?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template'); ?>

<head>
<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>INET - RECRUITMENT PORTAL</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">

    <!-- daterange picker -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/daterangepicker/daterangepicker.css">

  <!-- Bootstrap Color Picker -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet"
    href="<?=$theme_path?>/assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
     <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

  
  <!-- Bootstrap4 Duallistbox -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
    <!-- DataTables -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- BS Stepper -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/bs-stepper/css/bs-stepper.min.css">

   <!-- dropzonejs -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/dropzone/min/dropzone.min.css">
    <!-- Toastr -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/toastr/toastr.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?=$theme_path?>/assets/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?=$theme_path?>/css/canvas-modal.css">
  <link rel="stylesheet" href="<?=$theme_path?>/css/custom-style.css">

 <script src="<?= $theme_path ?>/js/jquery-1.8.2.js" type="text/javascript"></script>
<script type="text/javascript" src="<?= $theme_path ?>/js/jquery-ui.js"></script>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader hrms-preloader">
    <div class="radar-loader">
      <i class="fas fa-users radar-icon"></i>
    </div>
    <div class="radar-text">Analyzing</div>
  </div>     
 <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-dark">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto align-items-center">
      <!-- Welcome User -->
      <li class="nav-item d-flex align-items-center mr-3">
        <span class="text-white font-weight-bold" style="font-size: 13.5px; line-height: 1;">
          <i class="fas fa-user-circle text-info mr-1"></i> Welcome <?= htmlspecialchars(!empty($employee_det['EmpName']) ? $employee_det['EmpName'] : 'User'); ?>
        </span>
      </li>

      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#" id="notifDropdownToggle">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge" id="notifBadge" style="display:none;">0</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header"><span id="notifCountText">0</span> Unread Notifications</span>
          <div class="dropdown-divider"></div>
          <div id="notifListContainer" style="max-height: 300px; overflow-y: auto;">
             <!-- Injected via AJAX -->
             <a href="#" class="dropdown-item text-center text-muted">Loading...</a>
          </div>
          <div class="dropdown-divider"></div>
          <a href="javascript:void(0);" class="dropdown-item dropdown-footer" id="markAllReadBtn">Mark All as Read</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button" title="Settings">
          <i class="fas fa-cog"></i>
        </a>
      </li>


    <!--added by reka -->
    </ul>
  </nav>
  <!-- /.navbar -->


<!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4" style="display: flex !important; flex-direction: column !important; position: fixed !important; top: 0 !important; bottom: 0 !important; left: 0 !important; height: 100vh !important; z-index: 1038 !important;">
    <!-- Brand Logo -->
    <a href="#" class="brand-link" style="flex: 0 0 auto;">
      <img src="<?=$theme_path?>/assets/dist/img/favicon.png" alt="AdminLTE Logo" style="opacity: .8">
      <span class="brand-text font-weight-thick">HRMS</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar" style="flex: 1 1 auto !important; overflow-y: auto !important; height: auto !important;">
      <!-- Sidebar user panel (optional) -->
     <!--  <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="<?=$theme_path?>/assets/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo $employee_det['EmpName'];?></a>
         </div>
      </div>
 -->
      <!-- SidebarSearch Form -->
      <div class="form-inline mt-3 pb-3 mb-3 d-flex">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
          
            <?php if (!empty($menuTree)): ?>
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                    <?php foreach ($menuTree as $parent): ?>
                        <?php 
                        // Determine if this parent menu item should be active and expanded
                        $isParentActive = false;
                        if (!empty($currentUrlArray['parent']) && $currentUrlArray['parent']['IHMid'] == $parent['IHMid']) {
                            $isParentActive = true;
                        } else {
                            // Fallback: check if any child of this parent is active
                            if (!empty($parent['children']) && !empty($currentUrlArray['child'])) {
                                foreach ($parent['children'] as $child) {
                                    if ($child['IHMid'] == $currentUrlArray['child']['IHMid']) {
                                        $isParentActive = true;
                                        break;
                                    }
                                }
                            }
                        }
                        ?>

                        <?php if (!empty($parent['children'])): ?>
                            <!-- Parent with submenu -->
                            <li class="nav-item <?= $isParentActive ? 'menu-open' : ''; ?>">
                                <a href="#" class="nav-link">
                                    <?php 
                                    $parentIcon = !empty($parent['MenuIcon']) ? trim($parent['MenuIcon']) : '';
                                    if ($parentIcon === 'fa-dashboard') {
                                        $parentIcon = 'fa-tachometer-alt';
                                    }
                                    if (empty($parentIcon)) {
                                        $pNameLower = strtolower($parent['MenuName'] ?? '');
                                        if (strpos($pNameLower, 'vaccancy') !== false || strpos($pNameLower, 'vacancy') !== false || strpos($pNameLower, 'resource') !== false) {
                                            $parentIcon = 'fas fa-briefcase';
                                        } elseif (strpos($pNameLower, 'administration') !== false || strpos($pNameLower, 'admin') !== false) {
                                            $parentIcon = 'fas fa-cogs';
                                        } elseif (strpos($pNameLower, 'interview') !== false) {
                                            $parentIcon = 'fas fa-calendar-alt';
                                        } elseif (strpos($pNameLower, 'dashboard') !== false) {
                                            $parentIcon = 'fas fa-tachometer-alt';
                                        } else {
                                            $parentIcon = 'fas fa-folder';
                                        }
                                    } else {
                                        if (strpos($parentIcon, ' ') === false) {
                                            $parentIcon = 'fas ' . $parentIcon;
                                        }
                                    }
                                    ?>
                                    <i class="nav-icon <?= $parentIcon; ?>"></i>
                                    <p>
                                        <?= $parent['MenuName']; ?>
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>

                                <ul class="nav nav-treeview">
                                    <?php foreach ($parent['children'] as $child): ?>
                                        <?php 
                                        $isChildActive = !empty($currentUrlArray['child']) && $currentUrlArray['child']['IHMid'] == $child['IHMid'];
                                        ?>
                                        <li class="nav-item">
                                            <a href="<?= base_url($child['Menuurl']); ?>" class="nav-link <?= $isChildActive ? 'active' : ''; ?>">
                                                <?php 
                                                $childIcon = !empty($child['MenuIcon']) ? trim($child['MenuIcon']) : '';
                                                if ($childIcon === 'fa-dashboard') {
                                                    $childIcon = 'fa-tachometer-alt';
                                                }
                                                if (empty($childIcon) || $childIcon === 'far fa-circle') {
                                                    $urlLower  = strtolower($child['Menuurl'] ?? '');
                                                    $nameLower = strtolower($child['MenuName'] ?? '');
                                                    if (strpos($urlLower, 'approvedresources') !== false || strpos($nameLower, 'approved resource') !== false) {
                                                        $childIcon = 'fas fa-check-circle';
                                                    } elseif (strpos($urlLower, 'requestedresources') !== false || strpos($nameLower, 'requested resource') !== false) {
                                                        $childIcon = 'fas fa-clipboard-list';
                                                    } elseif (strpos($urlLower, 'vaccancylist') !== false || strpos($nameLower, 'vacancy') !== false) {
                                                        $childIcon = 'fas fa-list-alt';
                                                    } elseif (strpos($urlLower, 'candidatelist') !== false || strpos($nameLower, 'candidate') !== false) {
                                                        $childIcon = 'fas fa-users';
                                                    } elseif (strpos($urlLower, 'manageusers') !== false || strpos($nameLower, 'user') !== false) {
                                                        $childIcon = 'fas fa-user-cog';
                                                    } elseif (strpos($urlLower, 'managedepartments') !== false || strpos($nameLower, 'department') !== false) {
                                                        $childIcon = 'fas fa-building';
                                                    } elseif (strpos($urlLower, 'recruitmentstages') !== false || strpos($nameLower, 'stage') !== false) {
                                                        $childIcon = 'fas fa-layer-group';
                                                    } elseif (strpos($urlLower, 'my_interviews') !== false || strpos($nameLower, 'my interview') !== false) {
                                                        $childIcon = 'fas fa-calendar-check';
                                                    } elseif (strpos($urlLower, 'interview_calendar') !== false || strpos($nameLower, 'calendar') !== false) {
                                                        $childIcon = 'fas fa-calendar-alt';
                                                    } else {
                                                        $childIcon = 'far fa-circle';
                                                    }
                                                } else {
                                                    if (strpos($childIcon, ' ') === false) {
                                                        $childIcon = 'fas ' . $childIcon;
                                                    }
                                                }
                                                ?>
                                                <i class="<?= $childIcon; ?> nav-icon"></i>
                                                <p><?= $child['MenuName']; ?></p>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>

                        <?php else: ?>
                            <!-- Single menu item -->
                            <?php 
                            $isSingleActive = (!empty($currentUrlArray['child']) && $currentUrlArray['child']['IHMid'] == $parent['IHMid'])
                                || (strtolower($parent['Menuurl']) === $currentUrl);
                            ?>
                            <li class="nav-item">
                                <a href="<?= base_url($parent['Menuurl']); ?>" class="nav-link <?= $isSingleActive ? 'active' : ''; ?>">
                                    <?php 
                                    $parentIcon = !empty($parent['MenuIcon']) ? trim($parent['MenuIcon']) : '';
                                    if ($parentIcon === 'fa-dashboard') {
                                        $parentIcon = 'fa-tachometer-alt';
                                    }
                                    if (empty($parentIcon)) {
                                        $urlLower  = strtolower($parent['Menuurl'] ?? '');
                                        $nameLower = strtolower($parent['MenuName'] ?? '');
                                        if (strpos($urlLower, 'approvedresources') !== false || strpos($nameLower, 'approved resource') !== false) {
                                            $parentIcon = 'fas fa-check-circle';
                                        } elseif (strpos($urlLower, 'requestedresources') !== false || strpos($nameLower, 'requested resource') !== false) {
                                            $parentIcon = 'fas fa-clipboard-list';
                                        } elseif (strpos($nameLower, 'dashboard') !== false) {
                                            $parentIcon = 'fas fa-tachometer-alt';
                                        } else {
                                            $parentIcon = 'fas fa-th';
                                        }
                                    } else {
                                        if (strpos($parentIcon, ' ') === false) {
                                            $parentIcon = 'fas ' . $parentIcon;
                                        }
                                    }
                                    ?>
                                    <i class="nav-icon <?= $parentIcon; ?>"></i>
                                    <p><?= $parent['MenuName']; ?></p>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
             
        </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->

    <!-- Sidebar Bottom Footer (Pinned to absolute bottom of dark sidebar) -->
    <div class="sidebar-bottom-footer p-2" style="flex: 0 0 auto; background: rgba(0,0,0,0.25); border-top: 1px solid rgba(255,255,255,0.08);">
      <!-- Logout -->
      <div class="mb-2">
        <a href="<?= base_url('admin/logout'); ?>" class="btn btn-sm btn-block" style="background:rgba(220,53,69,0.2); border:1px solid rgba(220,53,69,0.4); color:#ff6b6b; font-weight:600; letter-spacing:.3px;">
          <i class="fas fa-power-off mr-1"></i> Logout
        </a>
      </div>

      <!-- Tip of the Day -->
      <div class="p-2 rounded" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.10);">
        <div class="d-flex align-items-center mb-1">
          <span style="font-size:13px; margin-right:6px;">&#128161;</span>
          <span style="color:#ffd166; font-size:10px; font-weight:700; letter-spacing:.5px; text-transform:uppercase;">Tip of the Day</span>
        </div>
        <p id="sidebarHrTip" style="color:rgba(255,255,255,0.75); font-size:10.5px; margin:0; line-height:1.45;">Loading...</p>
      </div>
    </div>
  </aside>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">

            <!-- Page Title -->
            <div class="col-sm-6">
              <h1 class="m-0 font-weight-bold text-dark">
                <?php 
                $hdrIcon = '';
                if (!empty($currentUrlArray['child'])) {
                    $titleText = $currentUrlArray['child']['MenuName'];
                    $iconClass = !empty($currentUrlArray['child']['MenuIcon']) ? trim($currentUrlArray['child']['MenuIcon']) : '';
                    if (empty($iconClass) || $iconClass === 'far fa-circle') {
                        $uLow = strtolower($currentUrlArray['child']['Menuurl'] ?? '');
                        $nLow = strtolower($titleText);
                        if (strpos($uLow, 'approvedresources') !== false || strpos($nLow, 'approved resource') !== false) {
                            $iconClass = 'fas fa-check-circle text-success';
                        } elseif (strpos($uLow, 'requestedresources') !== false || strpos($nLow, 'requested resource') !== false) {
                            $iconClass = 'fas fa-clipboard-list text-primary';
                        } elseif (strpos($uLow, 'vaccancylist') !== false || strpos($nLow, 'vacancy') !== false) {
                            $iconClass = 'fas fa-list-alt text-info';
                        }
                    }
                    if (!empty($iconClass)) {
                        if (strpos($iconClass, ' ') === false) {
                            $iconClass = 'fas ' . $iconClass;
                        }
                        $hdrIcon = '<i class="' . $iconClass . ' mr-2"></i>';
                    }
                } elseif (!empty($currentUrlArray['parent'])) {
                    $titleText = $currentUrlArray['parent']['MenuName'];
                } else {
                    $titleText = 'Dashboard';
                }
                echo $hdrIcon . htmlspecialchars($titleText);
                ?>
              </h1>
            </div>

            <!-- Breadcrumb -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">

                <!-- Home -->
                <li class="breadcrumb-item">
                  <a href="<?= base_url('admin/dashboard'); ?>">Home</a>
                </li>

                <!-- Parent -->
                <?php if (!empty($currentUrlArray['parent'])): ?>
                  <?php $pUrl = !empty($currentUrlArray['parent']['Menuurl']) ? $currentUrlArray['parent']['Menuurl'] : ($currentUrlArray['parent']['MenuUrl'] ?? '#'); ?>
                  <li class="breadcrumb-item">
                    <?php if ($pUrl !== '#'): ?>
                      <a href="<?= base_url($pUrl); ?>">
                        <?= $currentUrlArray['parent']['MenuName']; ?>
                      </a>
                    <?php else: ?>
                      <?= $currentUrlArray['parent']['MenuName']; ?>
                    <?php endif; ?>
                  </li>
                <?php endif; ?>

                <!-- Child -->
                <?php if (!empty($currentUrlArray['child'])): ?>
                  <li class="breadcrumb-item active">
                    <?= $currentUrlArray['child']['MenuName']; ?>
                  </li>
                <?php endif; ?>

              </ol>
              </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
      </div>
    <!-- /.content-header -->

     <?php echo $content;?>

  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <!-- Main Footer -->
  <footer class="main-footer">
    Copyright <script>document.write(new Date().getFullYear());</script> Designed &amp; Developed by <a href="https://www.i-net.in/" target="_blank">I-NET Secure Labs Pvt. Ltd</a>
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0
    </div>
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS --> 
<!-- jQuery -->
  <script src="<?=$theme_path?>/assets/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="<?=$theme_path?>/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
   <!-- overlayScrollbars -->
<script src="<?=$theme_path?>/assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>

  <!-- Select2 -->
  <script src="<?=$theme_path?>/assets/plugins/select2/js/select2.full.min.js"></script>
  <!-- Bootstrap4 Duallistbox -->
  <script src="<?=$theme_path?>/assets/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
  <!-- InputMask -->
  <script src="<?=$theme_path?>/assets/plugins/moment/moment.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/inputmask/jquery.inputmask.min.js"></script>
  <!-- date-range-picker -->
  <script src="<?=$theme_path?>/assets/plugins/daterangepicker/daterangepicker.js"></script>
  <!-- bootstrap color picker -->
  <script src="<?=$theme_path?>/assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
  <!-- Tempusdominus Bootstrap 4 -->
  <script src="<?=$theme_path?>/assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
  <!-- Bootstrap Switch -->
  <script src="<?=$theme_path?>/assets/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>

  <!-- dropzonejs -->
  <script src="<?=$theme_path?>/assets/plugins/dropzone/min/dropzone.min.js"></script>
  <!-- Toastr -->
<script src="<?=$theme_path?>/assets/plugins/toastr/toastr.min.js"></script>
  <!-- BS-Stepper -->
  <script src="<?=$theme_path?>/assets/plugins/bs-stepper/js/bs-stepper.min.js"></script>
  <!-- DataTables  & Plugins -->
  <script src="<?=$theme_path?>/assets/plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/jszip/jszip.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/pdfmake/pdfmake.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/pdfmake/vfs_fonts.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/datatables-buttons/js/buttons.print.min.js"></script>
  <script src="<?=$theme_path?>/assets/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
   <script src="<?= $theme_path ?>/assets/plugins/chart.js/Chart.min.js"></script>

                <!---->
  <!-- AdminLTE App -->
  <script src="<?= $theme_path ?>/assets/dist/js/adminlte.min.js"></script>
  <!-- AdminLTE for demo purposes -->
<script src="<?=$theme_path?>/assets/dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="<?=$theme_path?>/assets/dist/js/pages/dashboard2.js"></script>
<script>
    var base_url = "<?= base_url(); ?>";
</script>
<script>
$(function () {

  <?php if ($this->session->flashdata('success')): ?>
    toastr.success("<?= $this->session->flashdata('success'); ?>");
  <?php endif; ?>

  <?php if ($this->session->flashdata('true')): ?>
    toastr.success("<?= $this->session->flashdata('true'); ?>");
  <?php endif; ?>

  <?php if ($this->session->flashdata('error')): ?>
    toastr.error("<?= $this->session->flashdata('error'); ?>");
  <?php endif; ?>

  <?php if ($this->session->flashdata('info')): ?>
    toastr.info("<?= $this->session->flashdata('info'); ?>");
  <?php endif; ?>

  <?php if ($this->session->flashdata('warning')): ?>
    toastr.warning("<?= $this->session->flashdata('warning'); ?>");
  <?php endif; ?>

});
</script>
<script src="<?=$theme_path?>/js/custom-script.js"></script>

<!-- REAL-TIME NAVBAR NOTIFICATIONS SCRIPT -->
<script>
$(document).ready(function() {
  function fetchHeaderNotifications() {
    $.ajax({
      url: base_url + "admin/get_notifications",
      type: "GET",
      dataType: "json",
      success: function(res) {
        if (res.status === "success") {
          var count = res.unread_count || 0;
          var notifs = res.data || [];
          
          if (count > 0) {
            $("#notifBadge").text(count).show();
            $("#notifCountText").text(count);
          } else {
            $("#notifBadge").hide();
            $("#notifCountText").text(0);
          }

          var html = "";
          if (notifs.length > 0) {
            $.each(notifs, function(i, n) {
              var iconClass = "fa-info-circle text-info";
              if (n.Type === "success") iconClass = "fa-check-circle text-success";
              else if (n.Type === "warning") iconClass = "fa-exclamation-triangle text-warning";
              else if (n.Type === "danger") iconClass = "fa-times-circle text-danger";

              html += '<a href="javascript:void(0);" class="dropdown-item py-2 mark-single-notif" data-id="' + n.NotificationId + '">' +
                '<div class="media">' +
                  '<i class="fas ' + iconClass + ' mr-2 mt-1 fa-lg"></i>' +
                  '<div class="media-body">' +
                    '<h3 class="dropdown-item-title font-weight-bold" style="font-size: 13px;">' + (n.Title || "Notification") + '</h3>' +
                    '<p class="text-sm mb-1 text-wrap" style="font-size: 12px; line-height: 1.3;">' + (n.Message || "") + '</p>' +
                    '<p class="text-sm text-muted mb-0" style="font-size: 10px;"><i class="far fa-clock mr-1"></i>' + (n.CreatedAt || "") + '</p>' +
                  '</div>' +
                '</div>' +
              '</a>' +
              '<div class="dropdown-divider"></div>';
            });
          } else {
            html = '<div class="dropdown-item text-center text-muted py-3">No unread notifications</div>';
          }
          $("#notifListContainer").html(html);
        }
      }
    });
  }

  fetchHeaderNotifications();
  setInterval(fetchHeaderNotifications, 15000);

  $(document).on("click", ".mark-single-notif", function() {
    var nid = $(this).data("id");
    $.post(base_url + "admin/mark_notification_read", { notification_id: nid }, function() {
      fetchHeaderNotifications();
    });
  });

  $("#markAllReadBtn").on("click", function(e) {
    e.preventDefault();
    $.post(base_url + "admin/mark_all_notifications_read", function() {
      fetchHeaderNotifications();
    });
  });
});
</script>

<!-- ===== GLOBAL CUSTOM ALERT MODAL ===== -->
<div class="modal fade" id="globalAlertModal" tabindex="-1" role="dialog" aria-labelledby="globalAlertModalLabel" aria-modal="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content shadow-lg" style="border-radius:10px; overflow:hidden; border:none;">

      <div class="modal-header py-2 px-3" id="globalAlertHeader" style="border-bottom:none;">
        <div class="d-flex align-items-center">
          <span id="globalAlertIcon" class="mr-2" style="font-size:1.3rem;"></span>
          <h6 class="modal-title font-weight-bold mb-0" id="globalAlertTitle"></h6>
        </div>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:0.8;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body py-3 px-4 text-center">
        <p id="globalAlertMessage" class="mb-0" style="font-size:0.95rem; line-height:1.5;"></p>
      </div>

      <div class="modal-footer justify-content-center py-2" style="border-top:none;">
        <button type="button" class="btn btn-sm px-4" id="globalAlertOkBtn" data-dismiss="modal" style="border-radius:20px; font-weight:600; min-width:90px;">OK</button>
      </div>

    </div>
  </div>
</div>

<script>
/**
 * showAlert(message, type, title)
 * Replaces native browser alert() with a styled Bootstrap modal.
 * @param {string} message  - The message to display
 * @param {string} type     - 'success' | 'danger' | 'warning' | 'info' | 'primary' (default: 'info')
 * @param {string} title    - Optional title (auto-derived from type if not given)
 */
function showAlert(message, type, title) {
    type = type || 'info';

    var configs = {
        success: { bg: '#28a745', icon: 'fas fa-check-circle', label: 'Success',  btn: '#1e7e34' },
        danger:  { bg: '#dc3545', icon: 'fas fa-times-circle',  label: 'Error',    btn: '#bd2130' },
        error:   { bg: '#dc3545', icon: 'fas fa-times-circle',  label: 'Error',    btn: '#bd2130' },
        warning: { bg: '#ffc107', icon: 'fas fa-exclamation-triangle', label: 'Warning', btn: '#d39e00' },
        info:    { bg: '#17a2b8', icon: 'fas fa-info-circle',   label: 'Info',     btn: '#117a8b' },
        primary: { bg: '#007bff', icon: 'fas fa-bell',          label: 'Notice',   btn: '#0062cc' }
    };

    var cfg = configs[type] || configs['info'];
    var displayTitle = title || cfg.label;

    $('#globalAlertHeader').css('background-color', cfg.bg);
    $('#globalAlertTitle').text(displayTitle).css('color', '#fff');
    $('#globalAlertIcon').html('<i class="' + cfg.icon + ' text-white"></i>');
    $('#globalAlertMessage').text(message);
    $('#globalAlertOkBtn').css({ 'background-color': cfg.bg, 'border-color': cfg.bg, 'color': '#fff' });

    $('#globalAlertModal').modal('show');
}

// Sidebar HR Tip of the Day
(function() {
  var tips = [
    "Always send a personalised rejection email — candidates remember how you made them feel.",
    "Structured interviews reduce bias by 40%. Use the same questions for every candidate.",
    "Follow up within 48 hours of an interview. Speed signals respect and company culture.",
    "Job descriptions with inclusive language attract 42% more diverse applicants.",
    "Employee referrals produce the highest quality hires. Invest in your referral programme.",
    "Video interviews save 60% of scheduling time. Embrace async screening for early stages.",
    "Use ATS data to identify your best-performing sourcing channels every quarter.",
    "Set clear hiring SLAs: target 30 days from JD approval to offer letter.",
    "Onboarding doesn't end on day one. A 90-day plan dramatically improves retention."
  ];
  var tip = tips[Math.floor(Math.random() * tips.length)];
  var sidebar = document.getElementById('sidebarHrTip');
  var footer  = document.getElementById('footerHrTip');
  if (sidebar) sidebar.textContent = tip;
  if (footer)  footer.textContent  = tip;
})();
</script>

</html>