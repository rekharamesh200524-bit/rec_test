$(function () {
  if ($.fn.DataTable) {
    if ($("#example1").length) {
      $("#example1").DataTable({
        "responsive": false, "lengthChange": false, "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "colvis"]
      }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    }
    if ($('#example2').length) {
      $('#example2').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": false,
      });
    }
  }
});

$(function () {
  if ($('#reservationdate').length && $.fn.datetimepicker) {
    $('#reservationdate').datetimepicker({
        format: 'L'
    });
  }
});

if (typeof Dropzone !== 'undefined') {
  Dropzone.autoDiscover = false;
}
let resumeDropzone = null;

$(document).on('click', '.uploadResumeBtn', function () {
  let jid = $(this).data('id') || $(this).closest('.uploadResumeBtn').data('id') || $(this).attr('data-id') || $(this).closest('.uploadResumeBtn').attr('data-id');
  $('#jobId').val(jid);
  $('#upload_job_id').val(jid);
  $('#uploadModal').modal('show');

  if ($('#resumeDropzone').length && typeof Dropzone !== 'undefined') {
      if (!resumeDropzone) {
          resumeDropzone = new Dropzone("#resumeDropzone", {
              url: base_url + "admin/ats/analyzeResumeModal",
              paramName: "resume",
              maxFiles: 1,
              maxFilesize: 5,
              acceptedFiles: ".pdf,.doc,.docx",
              autoProcessQueue: true,

              init: function () {
                  this.on("sending", function (file, xhr, formData) {
                      formData.append("job_id", $('#jobId').val() || $('#upload_job_id').val());
                  });

                  this.on("success", function (file, res) {
                      console.log("Server Response:", res);
                      if (res.status === 'success') {
                          let detailStr = '';
                          if (res.data.score !== undefined && res.data.score !== null) {
                              detailStr = ` (${res.data.score}%)`;
                          } else if (res.data.recommendation) {
                              detailStr = ` (${res.data.recommendation})`;
                          }
                          toastr.success(
                              `${res.data.name} → ${res.data.status}${detailStr}`
                          );
                          setTimeout(() => {
                              window.location.href = res.redirect;
                          }, 1500);
                      } else {
                          toastr.error(res.message || 'Analysis failed');
                      }
                  });

                  this.on("error", function (file, errorMessage) {
                      console.error(errorMessage);
                      toastr.error('Upload failed');
                  });
              }
          });
      }
  }
});

$(document).on('click','.openInterviewUpdate',function(){
  let interviewId = $(this).data('interview');

  $('#interviewId').val(interviewId);
  $('#interviewResult').val('');
  $('#interviewFeedback').val('');

  $('#interviewPanel').addClass('open');
  $('#vacancyOverlay').addClass('show');
});

$('#closeInterviewPanel').on('click',function(){
  $('#interviewPanel').removeClass('open');
  $('#vacancyOverlay').removeClass('show');
});

$('#vacancyOverlay').on('click',function(){
  $('#interviewPanel').removeClass('open');
});

$(document).on('click','#saveInterviewResult',function(){
  $.post(base_url + "admin/updateInterviewResult",{
      interviewId: $('#interviewId').val(),
      result: $('#interviewResult').val(),
      feedback: $('#interviewFeedback').val()
  },function(res){
      let r = JSON.parse(res);
      if(r.status == 'success'){
          toastr.success('Interview Updated');
          $('#interviewPanel').removeClass('open');
          $('#vacancyOverlay').removeClass('show');
          location.reload();
      }else{
          toastr.error('Error updating');
      }
  });
});

// ================= NOTIFICATION SYSTEM AJAX =================
function fetchNotifications() {
  $.ajax({
      url: base_url + "admin/get_notifications",
      type: "GET",
      dataType: "json",
      success: function(res) {
          if (res.status === 'success') {
              let count = res.count;
              
              if (count > 0) {
                  $('#notifBadge').text(count).show();
                  $('#notifCountText').text(count);
              } else {
                  $('#notifBadge').hide();
                  $('#notifCountText').text('0');
              }

              let container = $('#notifListContainer');
              container.empty();

              if (count > 0) {
                  res.data.forEach(function(notif) {
                      let icon = 'fa-info-circle text-info';
                      if (notif.Type === 'success') icon = 'fa-check-circle text-success';
                      else if (notif.Type === 'warning') icon = 'fa-exclamation-triangle text-warning';
                      else if (notif.Type === 'danger') icon = 'fa-times-circle text-danger';

                      let html = `
                      <a href="javascript:void(0);" class="dropdown-item read-notification-btn" data-id="${notif.NotificationId}" style="white-space: normal;">
                          <div class="media">
                              <div class="media-body">
                                  <h3 class="dropdown-item-title font-weight-bold" style="font-size:14px;">
                                      <i class="fas ${icon} mr-2"></i>${notif.Title}
                                  </h3>
                                  <p class="text-sm text-muted mt-1" style="font-size:12px;">${notif.Message}</p>
                                  <p class="text-sm text-muted mb-0"><i class="far fa-clock mr-1"></i> ${notif.CreatedAt}</p>
                              </div>
                          </div>
                      </a>
                      <div class="dropdown-divider"></div>`;
                      container.append(html);
                  });
              } else {
                  container.html('<span class="dropdown-item text-center text-muted">No new notifications</span>');
              }
          }
      }
  });
}

// Initial fetch & set interval — only on authenticated pages where base_url is defined
$(document).ready(function() {
  if (typeof base_url !== 'undefined') {
    fetchNotifications();
    setInterval(fetchNotifications, 30000);
  }
});

// Mark single notification as read
$(document).on('click', '.read-notification-btn', function(e) {
  e.preventDefault();
  e.stopPropagation();
  let nid = $(this).data('id');
  let elem = $(this);

  $.post(base_url + "admin/mark_notification_read", { notification_id: nid }, function(res) {
      let r = JSON.parse(res);
      if (r.status === 'success') {
          elem.next('.dropdown-divider').remove();
          elem.remove();
          fetchNotifications(); // Refresh count
      }
  });
});

// Mark all as read
$('#markAllReadBtn').on('click', function(e) {
  e.preventDefault();
  e.stopPropagation();
  $.post(base_url + "admin/mark_all_notifications_read", {}, function(res) {
      let r = JSON.parse(res);
      if (r.status === 'success') {
          fetchNotifications(); // Refresh list
      }
  });
});



/* =========================================================================
 * MANAGE DEPARTMENTS
 * ========================================================================= */
if ($('#edit_Did').length || ($('#rightForm #Departmentname').length && !$('#rightForm #EmpName').length)) {
document.querySelectorAll('.editUserBtn').forEach(function(btn) {

    btn.addEventListener('click', function () {

        document.getElementById('edit_Did').value = this.dataset.id;
        document.getElementById('edit_Departmentname').value = this.dataset.name;  
        $('#editUserModal').modal('show');
    });

});
 
$(document).on('click', '.userStatusBtn', function () {

    let userId = $(this).data('id');
    let action = $(this).data('action');
    let url = '';
    let title = '';
    let message = '';
    let btnClass = '';

    if (action === 'deactivate') {
        url = base_url + 'admin/DeactivateDepartment/' + userId;
        title = "Deactivate Department";
        message = "Are you sure you want to deactivate this Department?";
        btnClass = "btn-danger";
    } else {
        url = base_url + 'admin/ActivateDepartment/' + userId;
        title = "Activate Department";
        message = "Are you sure you want to activate this Department?";
        btnClass = "btn-success";
    }

    $('#userStatusTitle').text(title);
    $('#userStatusMessage').text(message);
    $('#confirmUserStatusBtn')
        .attr('href', url)
        .removeClass('btn-danger btn-success')
        .addClass(btnClass);

    $('#userStatusModal').modal('show');
});

$(document).ready(function () {

    $('#openAddForm').on('click', function () {
        $('#rightForm').addClass('open');
        $('#rightFormOverlay').addClass('show');
    });

    $('#closeAddForm, #rightFormOverlay').on('click', function () {
        $('#rightForm').removeClass('open');
        $('#rightFormOverlay').removeClass('show');
    });

    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#example1')) {
        $('#example1').DataTable({
            "responsive": false,
            "autoWidth": false
        });
    }

    $(window).on('resize orientationchange', function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
            $('#example1').DataTable().columns.adjust();
        }
    });

});
}


/* =========================================================================
 * MANAGE USERS
 * ========================================================================= */
if ($('#edit_IUid').length || $('#rightForm #EmpName').length) {
document.querySelectorAll('.editUserBtn').forEach(function(btn) {

    btn.addEventListener('click', function () {

        document.getElementById('edit_IUid').value = this.dataset.id;
        document.getElementById('edit_name').value = this.dataset.name;
        document.getElementById('edit_email').value = this.dataset.email;
        document.getElementById('edit_phone').value = this.dataset.phone;
        document.getElementById('edit_dob').value = this.dataset.dob;
        document.getElementById('edit_gender').value = this.dataset.gender;
        document.getElementById('edit_designation').value = this.dataset.designation;
            document.getElementById('edit_department').value = this.dataset.department;
        document.getElementById('edit_role').value = this.dataset.role;

        $('#editUserModal').modal('show');
    });

});
 
$(document).on('click', '.userStatusBtn', function () {

    let userId = $(this).data('id');
    let action = $(this).data('action');
    let url = '';
    let title = '';
    let message = '';
    let btnClass = '';

    if (action === 'deactivate') {
        url = base_url + 'admin/DeactivateUser/' + userId;
        title = "Deactivate User";
        message = "Are you sure you want to deactivate this user?";
        btnClass = "btn-danger";
    } else {
        url = base_url + 'admin/ActivateUser/' + userId;
        title = "Activate User";
        message = "Are you sure you want to activate this user?";
        btnClass = "btn-success";
    }

    $('#userStatusTitle').text(title);
    $('#userStatusMessage').text(message);
    $('#confirmUserStatusBtn')
        .attr('href', url)
        .removeClass('btn-danger btn-success')
        .addClass(btnClass);

    $('#userStatusModal').modal('show');
});

$(document).ready(function () {

    $('#openAddForm').on('click', function () {
        $('#rightForm').addClass('open');
        $('#rightFormOverlay').addClass('show');
    });

    $('#closeAddForm, #rightFormOverlay').on('click', function () {
        $('#rightForm').removeClass('open');
        $('#rightFormOverlay').removeClass('show');
    });

    
    if ($("#hasErrorMarker").length) { $("#rightForm").addClass("open"); $("#rightFormOverlay").addClass("show"); }

});
}


/* =========================================================================
 * RECRUITMENT STAGES
 * ========================================================================= */
if ($('#recruitmentStagesTable').length || $('#add_StageGroup').length) {
$(document).ready(function () {

  $('#openAddForm').on('click', function () {
    $('#rightForm form')[0].reset();
    $('#add_StageOrder').val('');
    $('#rightForm').addClass('open');
    $('#rightFormOverlay').addClass('show');
  });

  $('#closeAddForm, #rightFormOverlay').on('click', function () {
    $('#rightForm').removeClass('open');
    $('#rightFormOverlay').removeClass('show');
  });

  $(document).on('change', '#add_StageGroup', function () {
      let group = $(this).val();
      if (group) {
          $.post(base_url + "admin/getNextStageOrder", { StageGroup: group }, function (res) {
              let data = JSON.parse(res);
              if (data.status === 'success') {
                  $('#add_StageOrder').val(data.nextOrder);
              } else {
                  $('#add_StageOrder').val('');
              }
          });
      } else {
          $('#add_StageOrder').val('');
      }
  });

});


$(document).on('click', '.editStageBtn', function () {

    $('#edit_StageId').val($(this).data('id'));
    $('#edit_StageName').val($(this).data('name'));
    $('#edit_StageOrder').val($(this).data('order'));
    $('#edit_StageGroup').val($(this).data('group'));

    $('#editRightForm').addClass('open');
    $('#rightFormOverlay').addClass('show');
});


$('#closeEditForm, #rightFormOverlay').on('click', function () {
    $('#editRightForm').removeClass('open');
    $('#rightFormOverlay').removeClass('show');
});



$(document).on('click', '.stageStatusBtn', function () {

    let url = $(this).data('url');
    let action = $(this).data('action');

    let message = '';

    if (action === 'activate') {
        message = "Are you sure you want to activate this stage?";
    } else {
        message = "Are you sure you want to deactivate this stage?";
    }

    $('#stageStatusMessage').text(message);

    $('#confirmStageStatus').attr('href', url);

    $('#stageStatusModal').modal('show');

});
$(document).ready(function() {
    setTimeout(function() {
        if ($.fn.DataTable.isDataTable('#recruitmentStagesTable')) {
            $('#recruitmentStagesTable').DataTable().destroy();
        }
        $('#recruitmentStagesTable').DataTable({
            "responsive": false,
            "autoWidth": false
        });
        $(window).on('resize orientationchange', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#recruitmentStagesTable')) {
                $('#recruitmentStagesTable').DataTable().columns.adjust();
            }
        });
    }, 100);
});
}


/* =========================================================================
 * ROLE PERMISSIONS
 * ========================================================================= */
if (document.getElementById('roleDropdown') && document.getElementById('savePermissions')) {
document.addEventListener('DOMContentLoaded', function(){

    var roleDropdown = document.getElementById('roleDropdown');
    var saveBtn      = document.getElementById('savePermissions');

    function syncSwitchUI(cb) {
        if (!cb) return;
        var itemRow = cb.closest('.perm-item-row');
        if (cb.checked) {
            if (itemRow) itemRow.classList.add('active-perm');
        } else {
            if (itemRow) itemRow.classList.remove('active-perm');
        }
    }

    function syncAllSwitchesUI() {
        document.querySelectorAll('.menuCheckbox').forEach(function(cb){
            syncSwitchUI(cb);
        });
    }

    // Attach change listener to all checkboxes
    document.querySelectorAll('.menuCheckbox').forEach(function(cb){
        cb.addEventListener('change', function(){
            syncSwitchUI(this);
        });
    });

    // LOAD PERMISSIONS
    function loadPermissions(roleId){

        document.querySelectorAll('.menuCheckbox').forEach(function(cb){
            cb.checked = false;
        });
        syncAllSwitchesUI();

        var formData = new FormData();
        formData.append('roleId', roleId);

        fetch(base_url + "admin/getRolePermissions", {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            data.forEach(function(menuId){
                var cb = document.querySelector('.menuCheckbox[value="'+menuId+'"]');
                if(cb) cb.checked = true;
            });
            syncAllSwitchesUI();
        })
        .catch(err => console.error('Load error:', err));
    }

    // ROLE CHANGE
    roleDropdown.addEventListener('change', function(){
        var roleId = this.value;
        if(roleId != ""){
            loadPermissions(roleId);
        } else {
            document.querySelectorAll('.menuCheckbox').forEach(function(cb){
                cb.checked = false;
            });
            syncAllSwitchesUI();
        }
    });

    // AUTO LOAD ROLE FROM URL
    var urlParams = new URLSearchParams(window.location.search);
    var selectedRole = urlParams.get('role');

    if(selectedRole){
        roleDropdown.value = selectedRole;
        loadPermissions(selectedRole);
    } else {
        syncAllSwitchesUI();
    }

    // OPEN CONFIRM MODAL
    saveBtn.addEventListener('click', function(){
        var roleId = roleDropdown.value;
        if(roleId == ""){
            toastr.warning("Please select a role first.");
            return;
        }
        $('#savePermissionModal').modal('show');
    });

    // PARENT → CHILD CHECK
    document.querySelectorAll('.parentMenu').forEach(function(parent){
        parent.addEventListener('change', function(){
            let parentId = this.value;
            document.querySelectorAll('.childMenu').forEach(function(child){
                if(child.dataset.parent == parentId){
                    child.checked = parent.checked;
                    syncSwitchUI(child);
                }
            });
            syncSwitchUI(this);
        });
    });

    // SELECT ALL BUTTON
    var selectAllBtn = document.getElementById('selectAllBtn');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function(){
            document.querySelectorAll('.menuCheckbox').forEach(function(cb){
                cb.checked = true;
            });
            syncAllSwitchesUI();
        });
    }

    // CLEAR ALL BUTTON
    var clearAllBtn = document.getElementById('clearAllBtn');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function(){
            document.querySelectorAll('.menuCheckbox').forEach(function(cb){
                cb.checked = false;
            });
            syncAllSwitchesUI();
        });
    }

    // SAVE PERMISSIONS
    document.getElementById('confirmSavePermissions').addEventListener('click', function(){

        var roleId = roleDropdown.value;
        var formData = new FormData();
        formData.append('roleId', roleId);

        document.querySelectorAll('.menuCheckbox:checked').forEach(function(cb){
            formData.append('menus[]', cb.value);
        });

        fetch(base_url + "admin/saveRolePermissions", {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            $('#savePermissionModal').modal('hide');

            if (data.status === 'success') {
                toastr.success('Role permissions updated successfully.');
            } else {
                toastr.error(data.msg || 'Something went wrong.');
            }

            setTimeout(function(){
                window.location.href = base_url + "admin/RolePermissions?role=" + roleId;
            }, 1200);

        })
        .catch(err => {
            console.error('Save error:', err);
            toastr.error("Something went wrong! Please try again.");
        });

    });

});
}


/* =========================================================================
 * INTERVIEW CALENDAR
 * ========================================================================= */
if (document.getElementById('interviewCalendar')) {
function openInterviewDetail(candidateName, email, phone, jobTitle, scheduledAtStr, round, result, interviewType, meetLink) {
    var resultBadge = '';
    var r = (result || 'Assigned').toLowerCase();
    if(r === 'selected')          resultBadge = '<span class="badge badge-success">' + result + '</span>';
    else if(r === 'rejected')     resultBadge = '<span class="badge badge-danger">'  + result + '</span>';
    else if(r === 'on hold')      resultBadge = '<span class="badge badge-warning">' + result + '</span>';
    else if(r === 'rescheduled')   resultBadge = '<span class="badge badge-warning text-dark"><i class="fas fa-history mr-1"></i>Rescheduled</span>';
    else                          resultBadge = '<span class="badge badge-primary">Assigned</span>';

    var modeBadge = '-';
    var modeStr = (interviewType || '').toLowerCase();
    if (modeStr === 'online') {
        modeBadge = '<span class="badge badge-success"><i class="fas fa-video mr-1"></i>Online</span>';
    } else if (modeStr === 'offline') {
        modeBadge = '<span class="badge badge-secondary"><i class="fas fa-building mr-1"></i>Offline</span>';
    } else if (interviewType) {
        modeBadge = '<span class="badge badge-light">' + interviewType + '</span>';
    }

    var timeStr = 'Not Scheduled';
    if(scheduledAtStr) {
        var dateObj = new Date(scheduledAtStr);
        if(!isNaN(dateObj.getTime())) {
            timeStr = dateObj.toLocaleString('en-IN', {
                weekday: 'long',
                year:    'numeric',
                month:   'long',
                day:     'numeric',
                hour:    '2-digit',
                minute:  '2-digit',
                hour12:  true
            });
        } else {
            timeStr = scheduledAtStr;
        }
    }

    var html = '<table class="table table-sm table-bordered mb-0">' +
        '<tr><th style="width:38%">Candidate</th><td>' + candidateName + '</td></tr>' +
        '<tr><th>Job Title</th><td>'    + (jobTitle  || '-') + '</td></tr>' +
        '<tr><th>Email</th><td>'        + (email     || '-') + '</td></tr>' +
        '<tr><th>Phone</th><td>'        + (phone     || '-') + '</td></tr>' +
        '<tr><th>Scheduled At</th><td>' + timeStr               + '</td></tr>' +
        '<tr><th>Interview Round</th><td>' + (round   || 1)  + '</td></tr>' +
        '<tr><th>Interview Type</th><td>' + modeBadge           + '</td></tr>';

    if (modeStr === 'online') {
        html += '<tr><th>Meeting Platform</th><td><span class="badge badge-info"><i class="fab fa-windows mr-1"></i>Microsoft Teams</span></td></tr>';
        if (meetLink) {
            html += '<tr><th>Teams Meeting Link</th><td><a href="' + meetLink + '" target="_blank" class="btn btn-xs btn-success font-weight-bold"><i class="fas fa-video mr-1"></i>Join Teams Meeting</a></td></tr>';
        } else {
            html += '<tr><th>Teams Meeting Link</th><td><span class="text-muted small">No link provided</span></td></tr>';
        }
    }

    html += '<tr><th>Result</th><td>'       + resultBadge           + '</td></tr>' +
        '</table>';

    document.getElementById('interviewDetailBody').innerHTML = html;
    $('#interviewDetailModal').modal('show');
}

document.addEventListener('DOMContentLoaded', function () {

    var calendarEl = document.getElementById('interviewCalendar');
    var events = []; try { events = JSON.parse(calendarEl.getAttribute("data-events") || "[]"); } catch(e) { events = []; }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 580,
        contentHeight: 520,
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        themeSystem: 'bootstrap',
        nowIndicator: true,
        selectable: false,
        dayMaxEvents: 3,
        events: events,
        eventTimeFormat: {
            hour:   '2-digit',
            minute: '2-digit',
            meridiem: 'short'
        },
        eventClick: function(info) {
            var p = info.event.extendedProps;
            var start = info.event.start;
            var startStr = start ? start.toISOString() : '';
            openInterviewDetail(
                info.event.title,
                p.email,
                p.phone,
                p.jobTitle,
                startStr,
                p.round,
                p.result,
                p.interviewType,
                p.meetLink
            );
        },

        dayCellDidMount: function(info) {
            if(info.date.toDateString() === new Date().toDateString()) {
                info.el.style.background = 'rgba(255, 193, 7, 0.08)';
            }
        }
    });

    calendar.render();

    document.getElementById('goToInterviewList').addEventListener('click', function(){
        window.location.href = base_url + 'admin/MyInterviews';
    });
});
}


/* =========================================================================
 * MY DASHBOARD
 * ========================================================================= */
if (document.getElementById('vacancyDonutChart')) {
document.addEventListener("DOMContentLoaded", function () {

  var donutLabels = []; try { donutLabels = JSON.parse(document.getElementById("vacancyDonutChart").getAttribute("data-labels") || "[]"); } catch(e) {}
  var donutValues = []; try { donutValues = JSON.parse(document.getElementById("vacancyDonutChart").getAttribute("data-values") || "[]"); } catch(e) {}

  var colors = [
    '#28a745',  
    '#007bff', 
    '#ffc107',  
    '#dc3545'  
  ];

  var total = donutValues.reduce((a, b) => a + b, 0);

  var ctx = document.getElementById('vacancyDonutChart').getContext('2d');

  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: donutLabels,
      datasets: [{
        data: donutValues,
        backgroundColor: colors
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

 
  var detailsContainer = document.getElementById('vacancyDetails');
  detailsContainer.innerHTML = '';

  donutLabels.forEach(function(label, index) {

    var value = donutValues[index];
    var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
    var color = colors[index];

    detailsContainer.innerHTML += `
      <div class="progress-group mb-3">
        ${label}
        <span class="float-right">
          <b>${value}</b> / ${total}
        </span>
        <div class="progress progress-sm">
          <div class="progress-bar" style="width:${percentage}%; background-color:${color}">
          </div>
        </div>
      </div>
    `;
  });

});
}



/* =========================================================================
 * DASHBOARD
 * ========================================================================= */
if (document.getElementById('dashboardDataStore') || $('#dashboardToggle').length) {
document.addEventListener("DOMContentLoaded", function () {



  const dsEl = document.getElementById("dashboardDataStore");
  let dsData = {};
  if (dsEl) { try { dsData = JSON.parse(dsEl.textContent || "{}"); } catch(e){} }
  const isHiringManager = dsEl ? (dsEl.getAttribute("data-is-hm") === "1") : false;
  let activeToggle = isHiringManager ? 'candidate' : 'job';
  let selectedDept = '';
  let selectedMonth = '';
  let myChartInstance = null;

  const jobsData = dsData.jobs || [];
  const candidatesData = dsData.candidates || [];

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
    let droppedCount = 0;
    let closedCount = 0;

    if (activeToggle === 'job') {
      summaryTitle.innerHTML = '<i class="fas fa-briefcase text-primary mr-2"></i>Jobs Summary';
      chartTitle.innerHTML = '<i class="fas fa-chart-pie text-primary mr-2"></i>Job Status';

      document.getElementById('label-total').innerText = 'Total Jobs';
      document.getElementById('label-open').innerText = 'Open';
      document.getElementById('label-hold').innerText = 'Hold';
      document.getElementById('label-dropped').innerText = 'Dropped';
      document.getElementById('label-closed').innerText = 'Closed';

      filteredData.forEach(job => {
        const status = (job.JobStatus || '').toLowerCase();
        if (status === 'open' || status === 're-open' || status === 'active') {
          openCount++;
        } else if (status === 'on-hold' || status === 'hold') {
          holdCount++;
        } else if (status === 'not required' || status === 'dropped' || status === 'cancelled') {
          droppedCount++;
        } else if (status === 'closed') {
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
      document.getElementById('label-dropped').innerText = 'Rejected';
      document.getElementById('label-closed').innerText = 'Hired';

      filteredData.forEach(cand => {
        const status = (cand.CurrentStatus || '').toLowerCase();
        if (status.includes('rejected') || status.includes('drop')) {
          droppedCount++;
        } else if (status.includes('hired') || status.includes('closed') || status.includes('select')) {
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
    animateCounter('kpi-dropped', droppedCount);
    animateCounter('kpi-closed', closedCount);

    renderTable(filteredData);
    renderChart(openCount, holdCount, droppedCount, closedCount);
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

    if (lower === 'hr') {
      displayText = 'Level 1';
    } else if (lower === 'open' || lower === 're-open' || lower === 'active') {
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

  function formatProjectDate(dInput) {
    if (!dInput || dInput === '0000-00-00' || dInput === '0000-00-00 00:00:00') return 'N/A';
    let d = new Date(dInput);
    if (isNaN(d.getTime())) return dInput;
    let day = String(d.getDate()).padStart(2, '0');
    let month = String(d.getMonth() + 1).padStart(2, '0');
    let year = d.getFullYear();
    return `${day}-${month}-${year}`;
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
        <th>Position</th>
        <th>Posted Date</th>
        <th>Status</th>
      `;

      if (data.length === 0) {
        body.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block text-secondary mb-2"></i>No jobs found matching selected filters.</td></tr>`;
        return;
      }

      data.forEach((job, index) => {
        const dateStr = job.PostedOn ? formatProjectDate(job.PostedOn) : 'N/A';
        let badge = getStatusBadge(job.JobStatus || 'Draft');

        body.innerHTML += `
          <tr>
            <td><strong>${index + 1}</strong></td>
            <td><strong class="text-dark">${job.JobTitle || 'N/A'}</strong></td>
            <td><span class="badge badge-light border">${job.Departmentname || '-'}</span></td>
            <td>${job.NoofOpenings || 1}</td>
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
        const dateStr = cand.AppliedOn ? formatProjectDate(cand.AppliedOn) : 'N/A';
        let badge = getStatusBadge(cand.CurrentStatus || 'Pending');
        let jid = cand.Jid || '';
        let candLink = jid ? (base_url + "admin/Candidatelist/" + jid) : '#';

        let hasInterviewRecord = (cand.InterviewId && String(cand.InterviewId) !== '' && String(cand.InterviewId) !== '0');
        let intBadge = '';
        if (hasInterviewRecord) {
          let interviewerName = cand.InterviewerName ? cand.InterviewerName : 'Interviewer';
          let schedStr = cand.ScheduledAt ? formatProjectDate(cand.ScheduledAt) : '';
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
        "responsive": false,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 10,
        "buttons": ["copy", "csv", "excel"]
      }).buttons().container().appendTo('#summaryTable_wrapper .col-md-6:eq(0)');
    }
  }

  function renderChart(open, hold, dropped, closed) {
    const canvas = document.getElementById('dynamicDistributionChart');
    const pipelineEl = document.getElementById('candidatePipeline');
    const detailsContainer = document.getElementById('chartDetails');
    if (!canvas || !pipelineEl) return;

    if (myChartInstance) {
      myChartInstance.destroy();
    }

    const total = open + hold + dropped + closed;
    detailsContainer.innerHTML = '';

    if (activeToggle === 'job') {
      canvas.style.display = 'block';
      pipelineEl.style.display = 'none';

      const labels     = ['Open', 'Hold', 'Dropped', 'Closed'];
      const dataValues = [open, hold, dropped, closed];
      const bgColors   = ['#3b82f6', '#f59e0b', '#ef4444', '#06b6d4'];

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
            <div class="progress dist-progress" style="height:6px;">
              <div class="progress-bar dist-progress-bar" style="width: ${percentage}%; background-color: ${color};"></div>
            </div>
          </div>
        `;
      });

      if (window.Chart) {
        const chartData   = (total === 0) ? [1] : dataValues;
        const chartColors = (total === 0) ? ['#e2e8f0'] : bgColors;

        myChartInstance = new Chart(canvas.getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: (total === 0) ? ['No Data'] : labels,
            datasets: [{
              data: chartData,
              backgroundColor: chartColors,
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

      const totalCand = open + hold + dropped + closed;
      const activePercent = totalCand > 0 ? ((open / totalCand) * 100).toFixed(0) : 0;
      const holdPercent = totalCand > 0 ? ((hold / totalCand) * 100).toFixed(0) : 0;
      const hiredPercent = totalCand > 0 ? ((closed / totalCand) * 100).toFixed(0) : 0;

      pipelineEl.innerHTML = `
        <div class="pipeline-funnel p-2">
          <div class="pipeline-step mb-3">
            <div class="d-flex justify-content-between font-weight-bold mb-1 small">
              <span><i class="fas fa-inbox text-teal mr-2"></i>1. Total Applications</span>
              <span class="badge badge-secondary px-2">${totalCand}</span>
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
            Out of <strong>${totalCand}</strong> applicants, <strong>${closed}</strong> candidates have been hired (Conversion: <strong>${totalCand > 0 ? ((closed/totalCand)*100).toFixed(1) : 0}%</strong>).
          </p>
        </div>
      `;
    }
  }

  init();
});
}


/* =========================================================================
 * ANALYTICS
 * ========================================================================= */
if (document.getElementById('analyticsRawData') || $('#analyticsContainer').length) {
document.addEventListener("DOMContentLoaded", function () {

    const rawDataEl = document.getElementById('analyticsRawData');
  let rawData = {};
  if (rawDataEl) {
    try { rawData = JSON.parse(rawDataEl.textContent || '{}'); } catch (e) { rawData = {}; }
  }

  const rawJobs = rawData.rawJobs || [];
  const rawCandidates = rawData.rawCandidates || [];
  const rawRecruiters = rawData.rawRecruiters || [];
  const rawInterviewsSummary = rawData.rawInterviewsSummary || [];
  const rawInterviewsDetail = rawData.rawInterviewsDetail || [];
  const rawDepartments = rawData.rawDepartments || [];

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
        <th>Position</th>
        <th>Assigned Recruiter</th>
        <th>Posted Date</th>
        <th>Status</th>
      `;
      function formatProjectDate(dInput) {
        if (!dInput || dInput === '0000-00-00' || dInput === '0000-00-00 00:00:00') return 'N/A';
        let d = new Date(dInput);
        if (isNaN(d.getTime())) return dInput;
        let day = String(d.getDate()).padStart(2, '0');
        let month = String(d.getMonth() + 1).padStart(2, '0');
        let year = d.getFullYear();
        return `${day}-${month}-${year}`;
      }

      if (!dataArray.length) {
        body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">No matching vacancy records found for this slice.</td></tr>`;
      } else {
        dataArray.forEach((j, idx) => {
          const dStr = j.PostedOn ? formatProjectDate(j.PostedOn) : 'N/A';
          body.innerHTML += `
            <tr>
              <td>${idx + 1}</td>
              <td><strong class="text-dark">${j.JobTitle || 'N/A'}</strong></td>
              <td><code>${j.JobCode || '-'}</code></td>
              <td><span class="badge badge-light border">${j.Departmentname || 'General'}</span></td>
              <td>${j.NoofOpenings || 1}</td>
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
          const dStr = c.AppliedOn ? formatProjectDate(c.AppliedOn) : 'N/A';
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
          const dStr = i.ScheduledAt ? formatProjectDate(i.ScheduledAt) : 'N/A';
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
        "responsive": false,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 10,
        "buttons": ["copy", "csv", "excel"]
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
}


/* =========================================================================
 * APPROVED RESOURCES
 * ========================================================================= */
if ($('#approvedResourcesTable').length || $('#pendingAssignSection').length || $('#pendingRequestSection').length) {
document.addEventListener('DOMContentLoaded', function() {
    const editStepperEl = document.querySelector('#editVacancyPanel .bs-stepper');
    if (editStepperEl) {
        window.editStepper = new Stepper(editStepperEl);
    }
});

function preloadChips(values, chipsId, hiddenId) {
    const chips = document.getElementById(chipsId);
    const hidden = hiddenId ? document.getElementById(hiddenId) : null;
    if (!chips) return;
    chips.innerHTML = '';
    if (!values) {
        if (hidden) hidden.value = '';
        return;
    }
    const arr = values.split(',');
    arr.forEach(v => {
        v = v.trim();
        if (!v) return;
        const chip = document.createElement('span');
        chip.className = chipsId.includes('MustHave') ? 'badge badge-pill badge-success mr-2 mb-2' : (chipsId.includes('NiceToHave') ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
        chip.innerHTML = `${v} <span style="cursor:pointer; margin-left:4px;">×</span>`;
        chip.querySelector('span').onclick = () => {
            chip.remove();
            if (hidden) {
                hidden.value = [...chips.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
            }
        };
        chips.appendChild(chip);
    });
    if (hidden) {
        hidden.value = arr.join(',');
    }
}

function initChipAutocomplete(config) {
    const input = document.getElementById(config.inputId);
    const dropdown = document.getElementById(config.dropdownId);
    const chipsContainer = document.getElementById(config.chipsId);
    const hiddenInput = config.hiddenId ? document.getElementById(config.hiddenId) : null;
    if (!input || !dropdown || !chipsContainer) return;

    function syncHidden() {
        if (!hiddenInput) return;
        hiddenInput.value = [...chipsContainer.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
    }

    input.addEventListener('keyup', function() {
        const q = this.value.trim();
        if (q.length < 2) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            return;
        }
        fetch(`${config.url}?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                dropdown.innerHTML = '';
                if (!data || !data.length) {
                    dropdown.innerHTML = '<span class="dropdown-item disabled">No results</span>';
                } else {
                    data.forEach(item => {
                        const value = item[config.key];
                        const el = document.createElement('a');
                        el.className = 'dropdown-item';
                        el.style.cursor = 'pointer';
                        el.textContent = value;
                        el.onclick = () => addChip(value);
                        dropdown.appendChild(el);
                    });
                }
                dropdown.style.display = 'block';
            });
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = input.value.trim();
            if (value.length >= 1) addChip(value);
        }
    });

    function addChip(value) {
        if (!value) return;
        const existing = [...chipsContainer.querySelectorAll('.badge')]
            .map(x => x.textContent.replace('×', '').trim());
        if (existing.includes(value)) return;

        const chip = document.createElement('span');
        chip.className = config.inputId.includes('MustHave') ? 'badge badge-pill badge-success mr-2 mb-2' : (config.inputId.includes('NiceToHave') ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
        chip.innerHTML = `${value} <span style="cursor:pointer; margin-left:4px;">×</span>`;

        chip.querySelector('span').onclick = () => {
            chip.remove();
            syncHidden();
        };
        chipsContainer.appendChild(chip);
        input.value = '';
        dropdown.style.display = 'none';
        syncHidden();
    }

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

$(document).ready(function() {
    // Restore tab state on page load
    try {
        var savedTab = sessionStorage.getItem('approvedRes_tab');
        if (savedTab === 'pendingRequest') {
            $('#btnTogglePendingRequest').addClass('active');
            $('#btnTogglePendingAssign').removeClass('active');
            $('#pendingRequestSection').show();
            $('#pendingAssignSection').hide();
        } else if (savedTab === 'pendingAssign') {
            $('#btnTogglePendingAssign').addClass('active');
            $('#btnTogglePendingRequest').removeClass('active');
            $('#pendingAssignSection').show();
            $('#pendingRequestSection').hide();
        }
    } catch (e) {}

    // Safe DataTables initialization (only if table exists and does not contain colspan empty message)
    if ($.fn.DataTable && $('#approvedTable').length) {
        var hasColspanRow = $('#approvedTable tbody tr td[colspan]').length > 0;
        var hasRows = $('#approvedTable tbody tr').length > 0;
        if (!hasColspanRow && hasRows) {
            try {
                if ($.fn.DataTable.isDataTable('#approvedTable')) {
                    $('#approvedTable').DataTable().destroy();
                }
                $('#approvedTable').DataTable({
                    "responsive": false,
                    "autoWidth": false,
                    "order": [[0, "asc"]]
                });
            } catch (dtErr) {
                console.warn('[ApprovedResources] DataTables init error:', dtErr);
            }
        }
    }

    $(window).on('resize orientationchange', function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#approvedTable')) {
            $('#approvedTable').DataTable().columns.adjust();
        }
    });

    initChipAutocomplete({
        inputId: 'edit_mustHaveSkillsInput',
        dropdownId: 'edit_mustHaveSkillsDropdown',
        chipsId: 'edit_mustHaveSkillsChips',
        hiddenId: 'edit_mustHaveSkills',
        url: base_url + "admin/searchSkills",
        key: 'SkillName'
    });
    initChipAutocomplete({
        inputId: 'edit_niceToHaveSkillsInput',
        dropdownId: 'edit_niceToHaveSkillsDropdown',
        chipsId: 'edit_niceToHaveSkillsChips',
        hiddenId: 'edit_niceToHaveSkills',
        url: base_url + "admin/searchSkills",
        key: 'SkillName'
    });
    initChipAutocomplete({
        inputId: 'edit_languageInput',
        dropdownId: 'edit_languageDropdown',
        chipsId: 'edit_languageChips',
        hiddenId: 'edit_comLanguage',
        url: base_url + "admin/searchLanguage",
        key: 'CommunicationLang'
    });
    initChipAutocomplete({
        inputId: 'edit_jobLocationInput',
        dropdownId: 'edit_jobLocationDropdown',
        chipsId: 'edit_jobLocationChips',
        hiddenId: 'edit_jobLocation',
        url: base_url + "admin/searchLocation",
        key: 'JobLocation'
    });
    initChipAutocomplete({
        inputId: 'edit_educationInput',
        dropdownId: 'edit_educationDropdown',
        chipsId: 'edit_educationChips',
        hiddenId: 'edit_education',
        url: base_url + "admin/searchEducation",
        key: 'EducationRequired'
    });


    $('.edit-work-mode').on('click', function() {
        $('.edit-work-mode').removeClass('active');
        $(this).addClass('active');
        $('#edit_work_mode').val($(this).data('value'));
    });
    $('.edit-emp-type').on('click', function() {
        $('.edit-emp-type').removeClass('active');
        $(this).addClass('active');
        $('#edit_employment_type').val($(this).data('value'));
    });

    function cleanExpVal(val) {
        if (val === null || val === undefined || val === '') return '';
        let num = parseFloat(val);
        if (isNaN(num)) return '';
        return (num % 1 === 0) ? num.toFixed(0) : num.toString();
    }

  
    function populateEditExpMin() {
        let html = '<option value="">Select Min Exp</option>';
        for (let i = 0; i <= 20; i++) {
            html += `<option value="${i}">${i} ${i === 1 ? 'Year' : 'Years'}</option>`;
        }
        $('#edit_expMin').html(html);
    }
    function populateEditExpMax(minVal) {
        let html = '<option value="">Select Max Exp</option>';
        let start = (minVal !== '' && minVal !== null && minVal !== undefined) ? parseInt(minVal) : 0;
        if (isNaN(start)) start = 0;
        for (let i = start; i <= 30; i++) {
            html += `<option value="${i}">${i} ${i === 1 ? 'Year' : 'Years'}</option>`;
        }
        $('#edit_expMax').html(html);
    }
    $('#edit_expMin').on('change', function() {
        populateEditExpMax($(this).val());
    });

   
    $('#closeEditVacancyPanel, #vacancyOverlay').on('click', function() {
        $('#editVacancyPanel').removeClass('open');
        $('#vacancyOverlay').removeClass('show');
    });


var ihUsersOptionsHtml = '<option value="">Select Interviewer</option>';
if ($('#edit_interviewPanel_1').length) {
    ihUsersOptionsHtml = $('#edit_interviewPanel_1').html();
}

function getCurrentLevelCount() {
    return 2 + $('#dynamicLevelsContainer .dynamic-level-row').length;
}

function updateAddLevelBtnState() {
    if (getCurrentLevelCount() >= 4) {
        $('#addInterviewLevelBtn').hide();
    } else {
        $('#addInterviewLevelBtn').show();
    }
}

function addDynamicLevel(levelNum, selectedVal) {
    if ($('#dynamic-level-' + levelNum).length) return;
    
    var html = `
        <div class="form-group mb-2 dynamic-level-row" id="dynamic-level-${levelNum}" data-level="${levelNum}">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <label class="font-weight-bold mb-0">Level ${levelNum} Interviewer</label>
                <button type="button" class="btn btn-xs btn-outline-danger remove-level-btn" data-target="dynamic-level-${levelNum}">
                    <i class="fas fa-minus mr-1"></i> Remove
                </button>
            </div>
            <select name="interviewPanel[${levelNum}]" id="edit_interviewPanel_${levelNum}" class="form-control interview-panel-select">
                ${ihUsersOptionsHtml}
            </select>
        </div>
    `;
    $('#dynamicLevelsContainer').append(html);
    if (selectedVal) {
        $('#edit_interviewPanel_' + levelNum).val(selectedVal);
    }
    updateAddLevelBtnState();
}

$(document).on('click', '#addInterviewLevelBtn', function() {
    var count = getCurrentLevelCount();
    if (count < 4) {
        var nextLevel = count + 1;
        addDynamicLevel(nextLevel);
    }
});

$(document).on('click', '.remove-level-btn', function() {
    var targetId = $(this).data('target');
    $('#' + targetId).remove();
    updateAddLevelBtnState();
});

    
    $(document).on('click', '.editJobBtn', function() {
        let jid = $(this).data('id');
        let reqData = $(this).data('req');
        if (typeof reqData === 'string') {
            reqData = JSON.parse(reqData);
        }

        populateEditExpMin();
        $('#dynamicLevelsContainer').empty();
        $('#edit_interviewPanel_1').val('');
        $('#edit_interviewPanel_2').val('');
        updateAddLevelBtnState();

        if (jid && parseInt(jid) > 0) {
            $.post(base_url + "admin/getJobDetails", { jid: jid }, function(res) {
                let d = JSON.parse(res);
                $('#edit_jid').val(d.Jid || 0);
                $('#edit_requestId').val(reqData.RequestId || reqData.RequestCode || 0);
                $('#edit_requestCode').val(d.JobCode || reqData.RequestCode || '');
                $('#editJobCodeText').text(d.JobCode || '');
                $('#edit_jobCode').val(d.JobCode || '');
                $('#edit_jobTitle').val(d.JobTitle || '');
                $('#edit_department').val(d.Departmentname || '');
                $('#edit_role').val(d.RoleSummary || '');
                $('#edit_positions').val(d.NoofOpenings || '');
                $('#edit_targetOnboardingDate').val((d.TargetOnboardingDate || '').split(' ')[0]);
                let salaryVal = d.Salary || (reqData ? reqData.Salary : '') || '';
                let ctcApproverVal = d.CtcApproverId || (reqData ? reqData.CtcApproverId : '') || '';
                let jdVal = d.JobDescription || (reqData ? reqData.JobDescription : '') || '';
                let rrVal = d.Responsibilities || (reqData ? reqData.Responsibilities : '') || '';

                $('#edit_JD').val(jdVal);
                $('#edit_RR').val(rrVal);
                $('#edit_CtcApproverId').val(ctcApproverVal);
                $('#edit_salary').val(salaryVal);

                if (d.interviewPanels && Array.isArray(d.interviewPanels) && d.interviewPanels.length > 0) {
                    d.interviewPanels.forEach(function(p) {
                        var lvl = parseInt(p.LevelOrder);
                        var uid = p.InterviewerId;
                        if (lvl === 1) {
                            $('#edit_interviewPanel_1').val(uid);
                        } else if (lvl === 2) {
                            $('#edit_interviewPanel_2').val(uid);
                        } else if (lvl === 3 || lvl === 4) {
                            addDynamicLevel(lvl, uid);
                        }
                    });
                }

                let minExp = cleanExpVal(d.ExpMin !== undefined && d.ExpMin !== null ? d.ExpMin : (reqData ? reqData.ExpMin : ''));
                let maxExp = cleanExpVal(d.ExpMax !== undefined && d.ExpMax !== null ? d.ExpMax : (reqData ? reqData.ExpMax : ''));
                $('#edit_expMin').val(minExp);
                populateEditExpMax(minExp);
                $('#edit_expMax').val(maxExp);

                let workMode = (d.WorkMode || (reqData ? reqData.WorkMode : '') || 'Onsite').trim();
                $('.edit-work-mode').removeClass('active');
                $(`.edit-work-mode[data-value="${workMode}"]`).addClass('active');
                $('#edit_work_mode').val(workMode);

                let empType = (d.EmploymentType || (reqData ? reqData.EmploymentType : '') || 'Full-Time').trim();
                $('.edit-emp-type').removeClass('active');
                $(`.edit-emp-type[data-value="${empType}"]`).addClass('active');
                $('#edit_employment_type').val(empType);

                preloadChips(d.JobLocation || (reqData ? reqData.JobLocation : ''), 'edit_jobLocationChips', 'edit_jobLocation');
                preloadChips(d.EducationRequired || (reqData ? reqData.EducationRequired : ''), 'edit_educationChips', 'edit_education');
                preloadChips(d.MustHaveSkills || d.Skills || (reqData ? (reqData.MustHaveSkills || reqData.Skills) : '') || '', 'edit_mustHaveSkillsChips', 'edit_mustHaveSkills');
                preloadChips(d.NiceToHaveSkills || (reqData ? reqData.NiceToHaveSkills : '') || '', 'edit_niceToHaveSkillsChips', 'edit_niceToHaveSkills');
                preloadChips(d.CommunicationLang || (reqData ? reqData.CommunicationLang : ''), 'edit_languageChips', 'edit_comLanguage');

                $('#editVacancyPanel').addClass('open');
                $('#vacancyOverlay').addClass('show');
            });
        } else {
            
            $('#edit_jid').val(0);
            $('#edit_requestId').val(reqData.RequestId || reqData.RequestCode || 0);
            $('#edit_requestCode').val(reqData.RequestCode || '');
            $('#editJobCodeText').text(reqData.RequestCode || '');
            $('#edit_jobCode').val(reqData.RequestCode || '');
            $('#edit_jobTitle').val(reqData.JobTitle || '');
            $('#edit_department').val(reqData.Departmentname || '');
            $('#edit_role').val(reqData.FunctionalRole || '');
            $('#edit_positions').val(reqData.NoofOpenings || 1);
            $('#edit_targetOnboardingDate').val((reqData.TargetOnboardingDate || '').split(' ')[0]);
            $('#edit_JD').val(reqData.JobDescription || '');
            $('#edit_RR').val(reqData.Responsibilities || '');
            $('#edit_CtcApproverId').val(reqData.CtcApproverId || '');
            $('#edit_salary').val(reqData.Salary || '');

            let minExp = cleanExpVal(reqData.ExpMin);
            let maxExp = cleanExpVal(reqData.ExpMax);
            $('#edit_expMin').val(minExp);
            populateEditExpMax(minExp);
            $('#edit_expMax').val(maxExp);

            $('.edit-work-mode').removeClass('active');
            $('.edit-work-mode[data-value="Onsite"]').addClass('active');
            $('#edit_work_mode').val('Onsite');

            $('.edit-emp-type').removeClass('active');
            $('.edit-emp-type[data-value="Full-Time"]').addClass('active');
            $('#edit_employment_type').val('Full-Time');

            preloadChips(reqData.JobLocation || '', 'edit_jobLocationChips', 'edit_jobLocation');
            preloadChips(reqData.EducationRequired || '', 'edit_educationChips', 'edit_education');
            preloadChips(reqData.MustHaveSkills || '', 'edit_mustHaveSkillsChips', 'edit_mustHaveSkills');
            preloadChips(reqData.NiceToHaveSkills || '', 'edit_niceToHaveSkillsChips', 'edit_niceToHaveSkills');
            preloadChips(reqData.CommunicationLang || '', 'edit_languageChips', 'edit_comLanguage');

            $('#editVacancyPanel').addClass('open');
            $('#vacancyOverlay').addClass('show');
        }
    });

    
    $('#editVacancyForm').on('submit', function(e) {
        e.preventDefault();
        $('#btnUpdateVacancySubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');

      
        function syncChips(chipsId, hiddenId) {
            var chips = document.getElementById(chipsId);
            var hidden = document.getElementById(hiddenId);
            if (chips && hidden) {
                hidden.value = [...chips.querySelectorAll('.badge')].map(function(x) {
                    return x.textContent.replace('\u00d7', '').trim();
                }).join(',');
            }
        }
        syncChips('edit_jobLocationChips', 'edit_jobLocation');
        syncChips('edit_educationChips', 'edit_education');
        syncChips('edit_mustHaveSkillsChips', 'edit_mustHaveSkills');
        syncChips('edit_niceToHaveSkillsChips', 'edit_niceToHaveSkills');
        syncChips('edit_languageChips', 'edit_comLanguage');

        var formData = $(this).serialize();
        console.log('[EditVacancy] Submitting POST data:', formData);

        $.ajax({
            url: base_url + "admin/updateVacancy",
            type: 'POST',
            data: formData,
            success: function(rawResponse) {
                $('#btnUpdateVacancySubmit').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update');
                var res;
                try { res = (typeof rawResponse === 'string') ? JSON.parse(rawResponse) : rawResponse; } catch(e) { res = {}; }
                if (res && res.status === 'success') {
                    showAlert(res.msg || 'Vacancy updated successfully.', 'success');
                    location.reload();
                } else {
                    console.error('[EditVacancy] Server error response:', rawResponse);
                    showAlert((res && res.msg) ? res.msg : 'Update failed. Check console for details.', 'danger');
                }
            },
            error: function(xhr, status, err) {
                $('#btnUpdateVacancySubmit').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update');
                console.error('[EditVacancy] AJAX error:', status, err, xhr.responseText ? xhr.responseText.substring(0, 500) : '');
                showAlert('Server error during update. Check browser console for details.', 'danger');
            }
        });
    });

    
    $(document).on('click', '.btn-assign', function() {
        let reqId = $(this).data('id');
        let code  = $(this).data('code');
        let title = $(this).data('title');
        let currentAssigned = $(this).data('assigned');

        $('#assign_requestId').val(reqId);
        $('#assign_reqCode').text(code);
        $('#assign_reqTitle').text(title);
        $('#assign_recruiterSelect').val(currentAssigned || '');

        if (currentAssigned) {
            $('#assignRecruiterModalLabel').html('<i class="fas fa-user-edit mr-2"></i>Reassign Recruiter / Manager');
        } else {
            $('#assignRecruiterModalLabel').html('<i class="fas fa-user-tag mr-2"></i>Assign to Recruiter / Manager');
        }

        $('#assignRecruiterModal').modal('show');
    });

    
    $('#assignRecruiterForm').on('submit', function(e) {
        e.preventDefault();
        let reqId = $('#assign_requestId').val();
        let managerId = $('#assign_recruiterSelect').val();

        if (!managerId) {
            showAlert('Please select a Recruitment Manager / Recruiter.', 'warning');
            return;
        }

        $('#btnConfirmAssign').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: base_url + "admin/assignResourceToRecruiter",
            type: 'POST',
            data: { requestId: reqId, assignedManagerId: managerId },
            dataType: 'json',
            success: function(res) {
                $('#btnConfirmAssign').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Assignment');
                if (res.status === 'success') {
                    $('#assignRecruiterModal').modal('hide');
                    showAlert(res.message, 'success');
                    location.reload();
                } else {
                    showAlert(res.message || 'Failed to assign resource.', 'danger');
                }
            },
            error: function() {
                $('#btnConfirmAssign').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Assignment');
                showAlert('An error occurred while connecting to the server.', 'danger');
            }
        });
    });

   
    function formatExpectedSalaryRange(min, max) {
        var minVal = (min !== null && min !== undefined && min !== '' && !isNaN(min)) ? parseFloat(min) : null;
        var maxVal = (max !== null && max !== undefined && max !== '' && !isNaN(max)) ? parseFloat(max) : null;
        if (minVal !== null && maxVal !== null) {
            return '₹' + minVal + ' LPA - ₹' + maxVal + ' LPA';
        } else if (minVal !== null) {
            return 'Min ₹' + minVal + ' LPA';
        } else if (maxVal !== null) {
            return 'Max ₹' + maxVal + ' LPA';
        }
        return '-';
    }

    $(document).on('click', '.btn-view-details', function() {
        let d = $(this).data('req');
        if (!d) return;
        if (typeof d === 'string') {
            try {
                d = JSON.parse(d);
            } catch (e) {
                console.error("Invalid JSON string in view details", e);
            }
        }
        if (!d || typeof d !== 'object') return;

        let html = `
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <p><b>Request Code:</b> ${d.RequestCode || '-'}</p>
                        <p><b>Job Title:</b> ${d.JobTitle || '-'}</p>
                        <p><b>Functional Role / Role:</b> ${d.FunctionalRole || d.RoleSummary || '-'}</p>
                        <p><b>Department:</b> ${d.Departmentname || '-'}</p>
                        <p><b>Position:</b> ${d.NoofOpenings || '1'}</p>
                        <p><b>Position Type:</b> ${d.PositionType || '-'}</p>
                        <p><b>Experience:</b> ${parseInt(d.ExpMin) || 0} - ${parseInt(d.ExpMax) || 0} Years</p>
                        <p><b>Education Required:</b> ${d.EducationRequired || '-'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><b>CTC / Budget:</b> ${d.Salary || 'N/A'}</p>
                        <p><b>Expected Salary Range:</b> ${formatExpectedSalaryRange(d.ExpectedSalaryMin, d.ExpectedSalaryMax)}</p>
                        <p><b>Target Onboarding:</b> ${d.TargetOnboardingDate || '-'}</p>
                        <p><b>Requested By:</b> ${d.RequestedByName || 'Hiring Manager'}</p>
                        <p><b>Approver:</b> ${d.ApproverName || '-'}</p>
                        <p><b>CTC Approver:</b> ${d.CtcApproverName || '-'}</p>
                        <p><b>Assigned Manager:</b> ${d.AssignedRecruiterManagerName || 'Unassigned'}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5><b>Must-Have Skills:</b></h5>
                        <p class="bg-light p-2 rounded">${d.MustHaveSkills || d.Skills || '-'}</p>
                        <h5><b>Nice-to-Have Skills:</b></h5>
                        <p class="bg-light p-2 rounded">${d.NiceToHaveSkills || '-'}</p>
                        <h5><b>Job Description:</b></h5>
                        <p class="bg-light p-2 rounded" style="white-space: pre-wrap;">${d.JobDescription || '-'}</p>
                        <h5><b>Roles & Responsibilities:</b></h5>
                        <p class="bg-light p-2 rounded" style="white-space: pre-wrap;">${d.Responsibilities || '-'}</p>
                    </div>
                </div>
            </div>
        `;

        $('#detailsModalBody').html(html);
        $('#approvedDetailsModal').modal('show');
    });

    // Custom DataTables Filter for Assigned / Unassigned / All
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'approvedTable') {
            return true;
        }
        var selectedFilter = $('input[name="assign_filter"]:checked').val();
        if (!selectedFilter || selectedFilter === 'ALL') {
            return true;
        }
        var assignedCellText = data[8] || ''; // Assigned Manager column (index 8)
        var statusCellText   = data[9] || ''; // Status column (index 9)

        var isAssigned = (statusCellText.indexOf('ASSIGNED') !== -1 || (assignedCellText.indexOf('Unassigned') === -1 && assignedCellText.trim() !== '' && assignedCellText.trim() !== '-'));

        if (selectedFilter === 'ASSIGNED') {
            return isAssigned;
        } else if (selectedFilter === 'UNASSIGNED') {
            return !isAssigned;
        }
        return true;
    });

    $(document).on('change', 'input[name="assign_filter"]', function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#approvedTable')) {
            $('#approvedTable').DataTable().draw();
        } else {
            var selectedFilter = $('input[name="assign_filter"]:checked').val();
            $('#approvedTable tbody tr').each(function() {
                var $row = $(this);
                if ($row.find('td[colspan]').length) return;
                var assignedCell = $row.find('td').eq(8).text().trim();
                var statusCell   = $row.find('td').eq(9).text().trim();
                var isAssigned = (statusCell.indexOf('ASSIGNED') !== -1 || (assignedCell.indexOf('Unassigned') === -1 && assignedCell !== '' && assignedCell !== '-'));
                if (!selectedFilter || selectedFilter === 'ALL') {
                    $row.show();
                } else if (selectedFilter === 'ASSIGNED') {
                    isAssigned ? $row.show() : $row.hide();
                } else if (selectedFilter === 'UNASSIGNED') {
                    !isAssigned ? $row.show() : $row.hide();
                }
            });
        }
    });

    $(document).on('click', '#btnTogglePendingAssign', function() {
        $(this).addClass('active');
        $('#btnTogglePendingRequest').removeClass('active');
        $('#pendingAssignSection').fadeIn(150);
        $('#pendingRequestSection').hide();
        sessionStorage.setItem('approvedRes_tab', 'pendingAssign');
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#approvedTable')) {
            $('#approvedTable').DataTable().columns.adjust();
        }
    });

    $(document).on('click', '#btnTogglePendingRequest', function() {
        $(this).addClass('active');
        $('#btnTogglePendingAssign').removeClass('active');
        $('#pendingRequestSection').fadeIn(150);
        $('#pendingAssignSection').hide();
        sessionStorage.setItem('approvedRes_tab', 'pendingRequest');
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#pendingRequestsTable')) {
            $('#pendingRequestsTable').DataTable().columns.adjust();
        }
    });

    $(document).on('click', '.btn-open-approval', function(e) {
        e.preventDefault();
        let requestId   = $(this).data('id');
        let requestCode = $(this).data('code');
        let status      = $(this).data('status');
        openApprovalModal(requestId, status, requestCode);
    });
});

function showAlert(msg, type) {
    if (typeof toastr !== 'undefined') {
        if (type === 'success') toastr.success(msg);
        else if (type === 'danger' || type === 'error') toastr.error(msg);
        else if (type === 'warning') toastr.warning(msg);
        else toastr.info(msg);
    } else {
        alert(msg);
    }
}

function openApprovalModal(requestId, status, requestCode) {
  var finalReqId = (requestId !== null && requestId !== undefined && requestId !== '') ? requestId : (requestCode || '');
  $('#approvalRequestId').val(finalReqId);
  $('#approvalRequestCode').val(requestCode || '');
  $('#approvalStatus').val(status || 'ACCEPTED');
  $('#approvalComment').val('');

  var header = $('#approvalModalHeader');
  var btn = $('#approvalSubmitBtn');

  if (status === 'ACCEPTED') {
    header.attr('class', 'modal-header bg-success text-white');
    $('#approvalModalTitle').html('<i class="fas fa-check-circle mr-2"></i>Accept Resource Request [' + requestCode + ']');
    $('#approvalTargetText').html('You are about to <span class="text-success font-weight-bold">ACCEPT</span> request <code>' + requestCode + '</code>.');
    btn.attr('class', 'btn btn-success font-weight-bold').html('<i class="fas fa-check mr-1"></i> Confirm Acceptance');
  } else {
    header.attr('class', 'modal-header bg-danger text-white');
    $('#approvalModalTitle').html('<i class="fas fa-times-circle mr-2"></i>Reject Resource Request [' + requestCode + ']');
    $('#approvalTargetText').html('You are about to <span class="text-danger font-weight-bold">REJECT</span> request <code>' + requestCode + '</code>.');
    btn.attr('class', 'btn btn-danger font-weight-bold').html('<i class="fas fa-times mr-1"></i> Confirm Rejection');
  }

  $('#approvalModal').modal('show');
}

function submitApproval(e) {
  e.preventDefault();
  var comment = $('#approvalComment').val().trim();
  if (!comment) {
    showAlert('Approval Comments are mandatory.', 'warning');
    return;
  }

  $('#approvalSubmitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

  $.ajax({
    url: base_url + "admin/updateResourceRequestStatus",
    type: 'POST',
    data: $('#approvalForm').serialize(),
    dataType: 'json',
    success: function(res) {
      $('#approvalSubmitBtn').prop('disabled', false);
      if (res.status === 'success') {
        $('#approvalModal').modal('hide');
        // After approval/rejection, switch to Pending Assigned Recruiter tab on reload
        sessionStorage.setItem('approvedRes_tab', 'pendingAssign');
        toastr.success(res.message || 'Status updated successfully.');
        setTimeout(function() { location.reload(); }, 1200);
      } else {
        showAlert(res.message || 'Error updating status', 'danger');
      }
    },
    error: function(xhr) {
      $('#approvalSubmitBtn').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Confirm Decision');
      var msg = 'Network or server error.';
      try {
        var errRes = JSON.parse(xhr.responseText);
        if (errRes.message) msg = errRes.message;
      } catch (e) {}
      showAlert(msg, 'danger');
    }
  });
}
window.openApprovalModal = openApprovalModal;
window.submitApproval = submitApproval;
}


/* =========================================================================
 * REQUESTED RESOURCES
 * ========================================================================= */
if ($('#requestsTable').length || $('#requestResourcePanel').length || $('#openRequestResourcePanel').length || $('#requestedResourcesTable').length || $('#createResourceRequestModal').length) {
var resStepperObj = null;

$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#requestsTable')) {
        $('#requestsTable').DataTable({
            "responsive": false,
            "autoWidth": false,
            "order": [[0, "asc"]]
        });
    }
    $(window).on('resize orientationchange', function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#requestsTable')) {
            $('#requestsTable').DataTable().columns.adjust();
        }
    });

    var stepperEl = document.querySelector('#requestResourcePanel .bs-stepper');
    if (stepperEl && typeof Stepper !== 'undefined') {
        try {
            window.resStepperObj = new Stepper(stepperEl);
        } catch (e) {}
    }
});

$(document).on('click', '#openRequestResourcePanel', function(e) {
    if (e) e.preventDefault();
    openCreateRequestModal();
});

$(document).on('click', '#closeRequestResourcePanel', function(e) {
    if (e) e.preventDefault();
    $('#requestResourcePanel').removeClass('open');
});

$(document).on('click', '#requestResourcePanel .bs-stepper-header .step', function(e) {
    e.preventDefault();
    var target = $(this).data('target');
    if (target === '#res-job-part') goToResStep(1);
    else if (target === '#res-salary-part') goToResStep(2);
    else if (target === '#res-desc-part') goToResStep(3);
});

  initResChipAutocomplete({
      inputId: 'resLocationInput',
      dropdownId: 'resLocationDropdown',
      chipsId: 'resLocationChips',
      hiddenId: 'resJobLocation',
      url: base_url + "admin/searchLocation",
      key: 'JobLocation'
  });
  initResChipAutocomplete({
      inputId: 'resEducationInput',
      dropdownId: 'resEducationDropdown',
      chipsId: 'resEducationChips',
      hiddenId: 'resEducationRequired',
      url: base_url + "admin/searchEducation",
      key: 'EducationRequired'
  });
  initResChipAutocomplete({
      inputId: 'resMustHaveSkillsInput',
      dropdownId: 'resMustHaveSkillsDropdown',
      chipsId: 'resMustHaveSkillsChips',
      hiddenId: 'resMustHaveSkills',
      url: base_url + "admin/searchSkills",
      key: 'SkillName'
  });
  initResChipAutocomplete({
      inputId: 'resNiceToHaveSkillsInput',
      dropdownId: 'resNiceToHaveSkillsDropdown',
      chipsId: 'resNiceToHaveSkillsChips',
      hiddenId: 'resNiceToHaveSkills',
      url: base_url + "admin/searchSkills",
      key: 'SkillName'
  });
  initResChipAutocomplete({
      inputId: 'resLanguageInput',
      dropdownId: 'resLanguageDropdown',
      chipsId: 'resLanguageChips',
      hiddenId: 'resCommunicationLang',
      url: base_url + "admin/searchLanguage",
      key: 'CommunicationLang'
  });

  $(document).off('submit', '#resourceRequestForm').on('submit', '#resourceRequestForm', function(e) {
      e.preventDefault();

      var $form = $(this);
      if ($form.data('submitting')) {
          e.stopImmediatePropagation();
          return false;
      }
      $form.data('submitting', true);

      var $btn = $('#resSubmitBtn');
      var originalBtnHtml = $btn.html();
      $btn.prop('disabled', true).addClass('disabled');

      function unlockAndFail(step, msg, isWarning, focusId) {
          $form.data('submitting', false);
          $btn.prop('disabled', false).removeClass('disabled').html(originalBtnHtml);
          if (step) goToResStep(step);
          if (msg) {
              if (isWarning) toastr.warning(msg);
              else toastr.error(msg);
          }
          if (focusId) $(focusId).focus();
          return false;
      }

      // Auto-sync any text remaining in chip inputs before validating
      ['resLocation', 'resEducation', 'resMustHaveSkills', 'resNiceToHaveSkills', 'resLanguage'].forEach(function(p) {
          var $inp = $('#' + p + 'Input');
          if ($inp.length && $inp.val().trim()) {
              var v = $inp.val().replace(/,/g, '').trim();
              if (v) {
                  var cId = p === 'resLanguage' ? 'resLanguageChips' : p + 'Chips';
                  var hId = p === 'resLocation' ? 'resJobLocation' : (p === 'resEducation' ? 'resEducationRequired' : (p === 'resLanguage' ? 'resCommunicationLang' : p));
                  addResChipDirect(v, p + 'Input', cId, hId);
              }
          }
      });

      if (!$('#resMustHaveSkills').val()) {
          var mhChips = $('#resMustHaveSkillsChips .badge').map(function() { return $(this).text().replace('×', '').trim(); }).get().filter(Boolean).join(',');
          $('#resMustHaveSkills').val(mhChips);
      }
      if (!$('#resCommunicationLang').val()) {
          var clChips = $('#resLanguageChips .badge').map(function() { return $(this).text().replace('×', '').trim(); }).get().filter(Boolean).join(',');
          $('#resCommunicationLang').val(clChips);
      }

      var jobTitle   = ($('#resourceRequestForm input[name="JobTitle"]').val() || '').trim();
      var did        = $('#resourceRequestForm select[name="Did"]').val();
      var approverId = $('#resourceRequestForm select[name="ApproverId"]').val();
      var mustHave   = ($('#resMustHaveSkills').val() || '').trim();
      var commLang   = ($('#resCommunicationLang').val() || '').trim();
      var jd         = ($('#resourceRequestForm textarea[name="JobDescription"]').val() || '').trim();
      var rr         = ($('#resourceRequestForm textarea[name="Responsibilities"]').val() || '').trim();

      if (!jobTitle || !did || !approverId) {
          return unlockAndFail(1, 'Please complete Job Title, Department, and Approver Name in Step 1.', true);
      }

      if (!mustHave) {
          return unlockAndFail(3, 'Please add at least one Must-Have Skill.', true, '#resMustHaveSkillsInput');
      }

      if (!commLang) {
          return unlockAndFail(3, 'Please add at least one Communication Language.', true, '#resLanguageInput');
      }

      if (!jd || !rr) {
          return unlockAndFail(3, 'Please provide Job Description and Roles & Responsibilities.', true);
      }

      var expSalMinStr = $('#res_ExpectedSalaryMin').val() ? $('#res_ExpectedSalaryMin').val().trim() : '';
      var expSalMaxStr = $('#res_ExpectedSalaryMax').val() ? $('#res_ExpectedSalaryMax').val().trim() : '';
      if (expSalMinStr !== '') {
          var expSalMinVal = parseFloat(expSalMinStr);
          if (isNaN(expSalMinVal) || expSalMinVal < 0) {
              return unlockAndFail(2, 'Minimum expected salary cannot be negative.', false);
          }
      }
      if (expSalMaxStr !== '') {
          var expSalMaxVal = parseFloat(expSalMaxStr);
          if (isNaN(expSalMaxVal) || expSalMaxVal < 0) {
              return unlockAndFail(2, 'Maximum expected salary cannot be negative.', false);
          }
      }
      if (expSalMinStr !== '' && expSalMaxStr !== '') {
          var expSalMinVal = parseFloat(expSalMinStr);
          var expSalMaxVal = parseFloat(expSalMaxStr);
          if (expSalMinVal > expSalMaxVal) {
              return unlockAndFail(2, 'Minimum expected salary cannot be greater than maximum expected salary.', false);
          }
      }

      $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...');

      $.ajax({
          url: $form.attr('action'),
          type: 'POST',
          data: $form.serialize(),
          dataType: 'json',
          success: function(res) {
              if (res.status === 'success') {
                  toastr.success(res.message || 'Resource Request submitted successfully.');
                  // Keep submitting flag true and button disabled until page reloads
                  setTimeout(function() {
                      location.reload();
                  }, 1000);
              } else {
                  $form.data('submitting', false);
                  $btn.prop('disabled', false).removeClass('disabled').html(originalBtnHtml);
                  toastr.error(res.message || 'Failed to submit request.');
              }
          },
          error: function(xhr) {
              $form.data('submitting', false);
              $btn.prop('disabled', false).removeClass('disabled').html(originalBtnHtml);
              var errMsg = 'Server or network error occurred.';
              try {
                  var errObj = JSON.parse(xhr.responseText);
                  if (errObj && errObj.message) errMsg = errObj.message;
              } catch(ex) {}
              toastr.error(errMsg);
          }
      });
  });

var currentResStep = 1;

function goToResStep(stepNum) {
  stepNum = parseInt(stepNum) || 1;
  if (stepNum < 1) stepNum = 1;
  if (stepNum > 3) stepNum = 3;
  currentResStep = stepNum;

  var targets = {
    1: '#res-job-part',
    2: '#res-salary-part',
    3: '#res-desc-part'
  };
  var targetId = targets[stepNum];

 
  $('#requestResourcePanel .bs-stepper-header .step').removeClass('active');
  $('#requestResourcePanel .bs-stepper-header .step[data-target="' + targetId + '"]').addClass('active');

  
  $('#requestResourcePanel .bs-stepper-content .content').removeClass('active').hide();
  $(targetId).addClass('active').fadeIn(150);


  if (window.resStepperObj) {
    try { window.resStepperObj.to(stepNum); } catch (e) {}
  }
}

function resStepperNext() {
  goToResStep(currentResStep + 1);
}

function resStepperPrev() {
  goToResStep(currentResStep - 1);
}

function viewRequestDetails(req) {
  var statusClass = 'badge-warning';
  var statusIcon = 'fa-clock';
  var statusText = req.Status || 'PENDING APPROVAL';

  if (statusText === 'ACCEPTED' || statusText === 'APPROVED') {
    statusClass = 'badge-success';
    statusIcon = 'fa-check-circle';
  } else if (statusText === 'REJECTED') {
    statusClass = 'badge-danger';
    statusIcon = 'fa-times-circle';
  }

  function makeChips(str, colorClass) {
    if (!str || str.trim() === '-' || str.trim() === '') return '<span class="text-muted small">None specified</span>';
    var arr = str.split(',');
    return arr.map(function(s) {
      return '<span class="badge badge-pill ' + colorClass + ' mr-1 mb-1 px-3 py-1 font-weight-normal" style="font-size:12px;">' + s.trim() + '</span>';
    }).join(' ');
  }

  var mustSkills = makeChips(req.MustHaveSkills, 'badge-success');
  var niceSkills = makeChips(req.NiceToHaveSkills, 'badge-info');
  var languages  = makeChips(req.CommunicationLang, 'badge-primary');

  function formatProjectDate(dInput) {
    if (!dInput || dInput === '0000-00-00' || dInput === '0000-00-00 00:00:00') return '-';
    let d = new Date(dInput);
    if (isNaN(d.getTime())) return dInput;
    let day = String(d.getDate()).padStart(2, '0');
    let month = String(d.getMonth() + 1).padStart(2, '0');
    let year = d.getFullYear();
    return `${day}-${month}-${year}`;
  }

  var targetDateStr = req.TargetOnboardingDate ? formatProjectDate(req.TargetOnboardingDate) : '-';
  var reqDateStr = req.CreatedAt ? formatProjectDate(req.CreatedAt) : '-';

  var html = `
    <div class="card border-0 shadow-none mb-0">
      <!-- Top Overview Header Banner -->
      <div class="p-3 mb-3 rounded-lg" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border-left: 5px solid #0d9488;">
        <div class="row align-items-center">
          <div class="col-md-8">
            <span class="badge badge-secondary px-2 py-1 small font-weight-bold mb-1"><i class="fas fa-hashtag mr-1"></i>${req.RequestCode || 'REQ'}</span>
            <h4 class="mb-0 font-weight-bold text-dark">${req.JobTitle || 'N/A'}</h4>
            <div class="text-muted small mt-1">
              <span class="mr-3"><i class="fas fa-building text-secondary mr-1"></i>${req.Departmentname || 'N/A'}</span>
              <span class="mr-3"><i class="fas fa-map-marker-alt text-danger mr-1"></i>${req.JobLocation || 'N/A'}</span>
              <span><i class="fas fa-briefcase text-info mr-1"></i>${req.PositionType || 'New Position'}</span>
            </div>
          </div>
          <div class="col-md-4 text-md-right mt-2 mt-md-0">
            <span class="badge ${statusClass} px-3 py-2 font-weight-bold" style="font-size:13px; border-radius:20px;">
              <i class="fas ${statusIcon} mr-1"></i>${statusText}
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Metrics Grid -->
      <div class="row text-center mb-3">
        <div class="col-6 col-md-3 mb-2">
          <div class="p-2 border rounded bg-white shadow-sm">
            <small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Positions</small>
            <span class="font-weight-bold text-dark h6 mb-0">${req.NoofOpenings || 1}</span>
          </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
          <div class="p-2 border rounded bg-white shadow-sm">
            <small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Experience</small>
            <span class="font-weight-bold text-dark h6 mb-0">${parseInt(req.ExpMin) || 0} - ${parseInt(req.ExpMax) || 0} Yrs</span>
          </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
          <div class="p-2 border rounded bg-white shadow-sm">
            <small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Target Onboarding</small>
            <span class="font-weight-bold text-teal h6 mb-0">${targetDateStr}</span>
          </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
          <div class="p-2 border rounded bg-white shadow-sm">
            <small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Request Date</small>
            <span class="font-weight-bold text-dark h6 mb-0">${reqDateStr}</span>
          </div>
        </div>
      </div>

      <!-- Main Info Cards -->
      <div class="row">
        <!-- Requirements & Stakeholders -->
        <div class="col-md-6 mb-3">
          <div class="card h-100 border-light shadow-sm">
            <div class="card-header bg-light py-2">
              <h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-list-ul mr-1 text-teal"></i>Requirements & Stakeholders</h6>
            </div>
            <div class="card-body p-3">
              <table class="table table-sm table-borderless mb-0 small">
                <tr><th class="text-muted pl-0" style="width:42%">Functional Role:</th><td class="font-weight-bold text-dark">${req.FunctionalRole || '-'}</td></tr>
                <tr><th class="text-muted pl-0">Education Required:</th><td class="text-dark">${req.EducationRequired || '-'}</td></tr>
                <tr><th class="text-muted pl-0">CTC / Budget:</th><td class="text-dark">${req.Salary || '-'}</td></tr>
                <tr><th class="text-muted pl-0">Expected Salary Range:</th><td class="font-weight-bold text-dark">${typeof formatExpectedSalaryRange === 'function' ? formatExpectedSalaryRange(req.ExpectedSalaryMin, req.ExpectedSalaryMax) : '-'}</td></tr>
                <tr><th class="text-muted pl-0">Reason for Request:</th><td class="text-dark">${req.ReasonForRequirement || '-'}</td></tr>
                <tr class="border-top"><th class="text-muted pl-0 pt-2">Requested By:</th><td class="font-weight-bold text-dark pt-2">${req.RequestedByName || '-'}</td></tr>
                <tr><th class="text-muted pl-0">Approver:</th><td class="font-weight-bold text-dark">${req.ApproverName || '-'}</td></tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Skills & Languages -->
        <div class="col-md-6 mb-3">
          <div class="card h-100 border-light shadow-sm">
            <div class="card-header bg-light py-2">
              <h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-tags mr-1 text-teal"></i>Skills & Languages</h6>
            </div>
            <div class="card-body p-3">
              <div class="mb-3">
                <small class="text-muted font-weight-bold d-block mb-1">Must-Have Skills:</small>
                <div>${mustSkills}</div>
              </div>
              <div class="mb-3">
                <small class="text-muted font-weight-bold d-block mb-1">Nice-to-Have Skills:</small>
                <div>${niceSkills}</div>
              </div>
              <div>
                <small class="text-muted font-weight-bold d-block mb-1">Communication Languages:</small>
                <div>${languages}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Job Description -->
      ${req.JobDescription ? `
      <div class="card border-light shadow-sm mb-3">
        <div class="card-header bg-light py-2">
          <h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-align-left mr-1 text-teal"></i>Job Description</h6>
        </div>
        <div class="card-body p-3 small text-dark" style="white-space:pre-wrap; line-height:1.6; background-color:#fafafa; border-radius: 0 0 8px 8px;">${req.JobDescription}</div>
      </div>` : ''}

      <!-- Roles & Responsibilities -->
      ${req.Responsibilities ? `
      <div class="card border-light shadow-sm mb-3">
        <div class="card-header bg-light py-2">
          <h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-tasks mr-1 text-teal"></i>Roles & Responsibilities</h6>
        </div>
        <div class="card-body p-3 small text-dark" style="white-space:pre-wrap; line-height:1.6; background-color:#fafafa; border-radius: 0 0 8px 8px;">${req.Responsibilities}</div>
      </div>` : ''}

      <!-- Approver Remark (if any) -->
      ${req.ApprovalComment ? `
      <div class="alert alert-warning border-0 shadow-sm p-3 mb-0" style="border-left: 5px solid #f59e0b !important; border-radius: 8px;">
        <h6 class="font-weight-bold mb-1 small text-dark"><i class="fas fa-comment-alt text-warning mr-1"></i>Approver Remark:</h6>
        <p class="mb-1 small text-dark">${req.ApprovalComment}</p>
        ${req.ActionedAt ? `<small class="text-muted"><i class="far fa-clock mr-1"></i>Actioned on: ${req.ActionedAt}</small>` : ''}
      </div>` : ''}
    </div>
  `;

  $('#detailsModalContent').html(html);
  $('#viewDetailsModal').modal('show');
}

function openApprovalModal(requestId, status, requestCode) {
  var finalReqId = (requestId !== null && requestId !== undefined && requestId !== '') ? requestId : (requestCode || '');
  $('#approvalRequestId').val(finalReqId);
  $('#approvalRequestCode').val(requestCode || '');
  $('#approvalStatus').val(status || 'ACCEPTED');
  $('#approvalComment').val('');

  var header = $('#approvalModalHeader');
  var btn = $('#approvalSubmitBtn');

  if (status === 'ACCEPTED') {
    header.attr('class', 'modal-header bg-success text-white');
    $('#approvalModalTitle').text('Accept Resource Request [' + requestCode + ']');
    $('#approvalTargetText').html('You are about to <span class="text-success font-weight-bold">ACCEPT</span> request <code>' + requestCode + '</code>.');
    btn.attr('class', 'btn btn-success').html('<i class="fas fa-check mr-1"></i> Confirm Acceptance');
  } else {
    header.attr('class', 'modal-header bg-danger text-white');
    $('#approvalModalTitle').text('Reject Resource Request [' + requestCode + ']');
    $('#approvalTargetText').html('You are about to <span class="text-danger font-weight-bold">REJECT</span> request <code>' + requestCode + '</code>.');
    btn.attr('class', 'btn btn-danger').html('<i class="fas fa-times mr-1"></i> Confirm Rejection');
  }

  $('#approvalModal').modal('show');
}

function submitApproval(e) {
  e.preventDefault();
  var comment = $('#approvalComment').val().trim();
  if (!comment) {
    showAlert('Approval Comments are mandatory.', 'warning');
    return;
  }

  $.ajax({
    url: base_url + "admin/updateResourceRequestStatus",
    type: 'POST',
    data: $('#approvalForm').serialize(),
    dataType: 'json',
    success: function(res) {
      if (res.status === 'success') {
        $('#approvalModal').modal('hide');
        location.reload();
      } else {
        showAlert(res.message || 'Error updating status', 'danger');
      }
    },
    error: function(xhr) {
      var msg = 'Network or server error.';
      try {
        var errRes = JSON.parse(xhr.responseText);
        if (errRes.message) msg = errRes.message;
      } catch (e) {}
      showAlert(msg, 'danger');
    }
  });
}

function syncCcUI() {
    var chipsHtml = '';
    var selectedCount = 0;
    
    $('#resExtraCcUsers option').prop('selected', false);

    $('.cc-user-row').each(function() {
        var $row = $(this);
        var uid = $row.data('uid');
        var name = $row.data('name');
        var role = $row.data('role');
        var isDefault = $row.data('default') == '1';
        var isChecked = $row.find('.cc-user-chk').is(':checked');

        if (isChecked) {
            selectedCount++;
            $('#resExtraCcUsers option[value="' + uid + '"]').prop('selected', true);
            $row.css('background-color', '#f1f5f9');

            var badgeClass = isDefault ? 'badge-primary' : 'badge-success';
            var iconClass = isDefault ? 'fa-star' : 'fa-user-check';
            var defaultTag = isDefault ? ' (Default)' : '';

            chipsHtml += '<span class="badge ' + badgeClass + ' px-2 py-1 font-weight-normal shadow-sm d-inline-flex align-items-center" style="font-size: 11.5px; border-radius: 6px;">' +
                '<i class="fas ' + iconClass + ' mr-1" style="font-size: 9px;"></i>' +
                '<strong>' + name + '</strong>&nbsp;<span style="opacity: 0.85;">(' + role + defaultTag + ')</span>' +
                '<span class="remove-cc-chip ml-2 font-weight-bold" data-uid="' + uid + '" style="cursor: pointer; font-size: 13px; opacity: 0.8;" title="Remove">&times;</span>' +
            '</span>';
        } else {
            $row.css('background-color', 'transparent');
        }
    });

    if (chipsHtml === '') {
        chipsHtml = '<span class="text-muted small italic p-1"><i class="fas fa-user-slash mr-1"></i>No CC recipients selected</span>';
    }

    $('#activeCcChipsContainer').html(chipsHtml);
    $('#selectedCcCountBadge').text(selectedCount + ' Recipient' + (selectedCount === 1 ? '' : 's'));
}

$(document).ready(function() {
    syncCcUI();
});

$(document).on('click', '.cc-user-row', function(e) {
    if ($(e.target).is('input[type="checkbox"]') || $(e.target).is('label')) {
        return;
    }
    var $chk = $(this).find('.cc-user-chk');
    $chk.prop('checked', !$chk.is(':checked'));
    syncCcUI();
});

$(document).on('change', '.cc-user-chk', function() {
    syncCcUI();
});

$(document).on('click', '.remove-cc-chip', function(e) {
    e.stopPropagation();
    var uid = $(this).data('uid');
    $('#cc_chk_' + uid).prop('checked', false);
    syncCcUI();
});

$(document).on('keyup', '#ccUserSearchInput', function() {
    var q = $(this).val().toLowerCase().trim();
    $('.cc-user-row').each(function() {
        var name = ($(this).data('name') || '').toLowerCase();
        var email = ($(this).data('email') || '').toLowerCase();
        var role = ($(this).data('role') || '').toLowerCase();
        if (name.indexOf(q) !== -1 || email.indexOf(q) !== -1 || role.indexOf(q) !== -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

function openCreateRequestModal() {
  $("#res_RequestId").val("0");
  $("#panelHeaderTitle").html('<i class="fas fa-user-plus mr-2"></i>Request Resource');
  $("#resSubmitBtn").prop('disabled', false).removeClass('disabled').html('<i class="fas fa-paper-plane mr-1"></i> Submit Request');
  if ($("#resourceRequestForm").length) {
    $("#resourceRequestForm").data('submitting', false);
    $("#resourceRequestForm")[0].reset();
  }
  $('.cc-user-row').each(function() {
    var isDefault = $(this).data('default') == '1';
    $(this).find('.cc-user-chk').prop('checked', isDefault);
  });
  syncCcUI();
  preloadResChips('', 'resLocationChips', 'resJobLocation');
  preloadResChips('', 'resEducationChips', 'resEducationRequired');
  preloadResChips('', 'resMustHaveSkillsChips', 'resMustHaveSkills');
  preloadResChips('', 'resNiceToHaveSkillsChips', 'resNiceToHaveSkills');
  preloadResChips('', 'resLanguageChips', 'resCommunicationLang');
  goToResStep(1);
  $("#requestResourcePanel").addClass("open");
}

function openEditRequestModal(req) {
  $("#res_RequestId").val(req.RequestId || "0");
  $("#panelHeaderTitle").html('<i class="fas fa-edit mr-2"></i>Edit Resource Request [' + (req.RequestCode || "") + ']');
  $("#resSubmitBtn").prop('disabled', false).removeClass('disabled').html('<i class="fas fa-save mr-1"></i> Update Request');
  if ($("#resourceRequestForm").length) {
    $("#resourceRequestForm").data('submitting', false);
  }


  $('input[name="JobTitle"]').val(req.JobTitle || "");
  $('input[name="FunctionalRole"]').val(req.FunctionalRole || "");
  $('select[name="Did"]').val(req.Did || "");
  $('select[name="PositionType"]').val(req.PositionType || "New Position");
  $('select[name="ApproverId"]').val(req.ApproverId || "");
  $('textarea[name="ReasonForRequirement"]').val(req.ReasonForRequirement || "");

  var extraArr = [];
  if (req.ExtraCcUsers) {
    try {
      if (typeof req.ExtraCcUsers === 'string') {
        extraArr = JSON.parse(req.ExtraCcUsers);
      } else if (Array.isArray(req.ExtraCcUsers)) {
        extraArr = req.ExtraCcUsers;
      }
    } catch(e) {
      if (typeof req.ExtraCcUsers === 'string') {
        extraArr = req.ExtraCcUsers.split(',').map(function(x) { return x.trim(); });
      }
    }
  }

  if (extraArr && extraArr.length > 0) {
    var strArr = extraArr.map(String);
    $('.cc-user-row').each(function() {
      var uid = String($(this).data('uid'));
      $(this).find('.cc-user-chk').prop('checked', strArr.indexOf(uid) !== -1);
    });
  } else {
    $('.cc-user-row').each(function() {
      var isDefault = $(this).data('default') == '1';
      $(this).find('.cc-user-chk').prop('checked', isDefault);
    });
  }
  syncCcUI();

  $('input[name="ExpMin"]').val(req.ExpMin || 0);
  $('input[name="ExpMax"]').val(req.ExpMax || 0);
  $('input[name="RecruitmentStartDate"]').val((req.RecruitmentStartDate || "").split(" ")[0]);
  $('input[name="TargetOnboardingDate"]').val((req.TargetOnboardingDate || "").split(" ")[0]);

  $('input[name="NoofOpenings"]').val(req.NoofOpenings || 1);
  $('textarea[name="JobDescription"]').val(req.JobDescription || "");
  $('textarea[name="Responsibilities"]').val(req.Responsibilities || "");

  preloadResChips(req.JobLocation || '', 'resLocationChips', 'resJobLocation');
  preloadResChips(req.EducationRequired || '', 'resEducationChips', 'resEducationRequired');
  preloadResChips(req.MustHaveSkills || '', 'resMustHaveSkillsChips', 'resMustHaveSkills');
  preloadResChips(req.NiceToHaveSkills || '', 'resNiceToHaveSkillsChips', 'resNiceToHaveSkills');
  preloadResChips(req.CommunicationLang || '', 'resLanguageChips', 'resCommunicationLang');
  goToResStep(1);
  $("#requestResourcePanel").addClass("open");
}

function addResChipDirect(value, inputId, chipsId, hiddenId) {
    value = (value || '').replace(/,/g, '').trim();
    if (!value) return;
    const chipsContainer = document.getElementById(chipsId);
    const hiddenInput = hiddenId ? document.getElementById(hiddenId) : null;
    const input = document.getElementById(inputId);
    if (!chipsContainer) return;

    function syncHidden() {
        if (!hiddenInput) return;
        hiddenInput.value = [...chipsContainer.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
    }

    const existing = [...chipsContainer.querySelectorAll('.badge')]
        .map(x => x.textContent.replace('×', '').trim());
    if (existing.includes(value)) {
        if (input) input.value = '';
        return;
    }

    const chip = document.createElement('span');
    chip.className = inputId.toLowerCase().includes('musthave') ? 'badge badge-pill badge-success mr-2 mb-2' : (inputId.toLowerCase().includes('nicetohave') ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
    chip.style.fontSize = '13px';
    chip.style.padding = '6px 12px';
    chip.style.display = 'inline-flex';
    chip.style.alignItems = 'center';
    chip.innerHTML = `${value} <span style="cursor:pointer; margin-left:6px; font-weight:bold; font-size:14px;">×</span>`;

    chip.querySelector('span').onclick = (e) => {
        e.stopPropagation();
        chip.remove();
        syncHidden();
    };
    chipsContainer.appendChild(chip);
    if (input) input.value = '';
    const dropdownId = inputId.replace('Input', 'Dropdown');
    const dropdown = document.getElementById(dropdownId);
    if (dropdown) dropdown.style.display = 'none';
    syncHidden();
}


$(document).on('keydown', '#resLocationInput, #resEducationInput, #resMustHaveSkillsInput, #resNiceToHaveSkillsInput, #resLanguageInput', function(e) {
    if (e.which === 13 || e.keyCode === 13 || e.key === 'Enter' || e.which === 188 || e.keyCode === 188 || e.key === ',') {
        e.preventDefault();
        e.stopPropagation();
        var $input = $(this);
        var val = $input.val().replace(/,/g, '').trim();
        if (val.length >= 1) {
            var inputId = $input.attr('id');
            if (inputId === 'resLocationInput') {
                addResChipDirect(val, 'resLocationInput', 'resLocationChips', 'resJobLocation');
            } else if (inputId === 'resEducationInput') {
                addResChipDirect(val, 'resEducationInput', 'resEducationChips', 'resEducationRequired');
            } else if (inputId === 'resMustHaveSkillsInput') {
                addResChipDirect(val, 'resMustHaveSkillsInput', 'resMustHaveSkillsChips', 'resMustHaveSkills');
            } else if (inputId === 'resNiceToHaveSkillsInput') {
                addResChipDirect(val, 'resNiceToHaveSkillsInput', 'resNiceToHaveSkillsChips', 'resNiceToHaveSkills');
            } else if (inputId === 'resLanguageInput') {
                addResChipDirect(val, 'resLanguageInput', 'resLanguageChips', 'resCommunicationLang');
            }
        }
        return false;
    }
});

$(document).on('blur', '#resLocationInput, #resEducationInput, #resMustHaveSkillsInput, #resNiceToHaveSkillsInput, #resLanguageInput', function() {
    var $input = $(this);
    setTimeout(function() {
        var val = $input.val().replace(/,/g, '').trim();
        if (val.length >= 1) {
            var inputId = $input.attr('id');
            if (inputId === 'resLocationInput') {
                addResChipDirect(val, 'resLocationInput', 'resLocationChips', 'resJobLocation');
            } else if (inputId === 'resEducationInput') {
                addResChipDirect(val, 'resEducationInput', 'resEducationChips', 'resEducationRequired');
            } else if (inputId === 'resMustHaveSkillsInput') {
                addResChipDirect(val, 'resMustHaveSkillsInput', 'resMustHaveSkillsChips', 'resMustHaveSkills');
            } else if (inputId === 'resNiceToHaveSkillsInput') {
                addResChipDirect(val, 'resNiceToHaveSkillsInput', 'resNiceToHaveSkillsChips', 'resNiceToHaveSkills');
            } else if (inputId === 'resLanguageInput') {
                addResChipDirect(val, 'resLanguageInput', 'resLanguageChips', 'resCommunicationLang');
            }
        }
    }, 200);
});

function preloadResChips(values, chipsId, hiddenId) {
    const chips = document.getElementById(chipsId);
    const hidden = hiddenId ? document.getElementById(hiddenId) : null;
    if (!chips) return;
    chips.innerHTML = '';
    if (!values) {
        if (hidden) hidden.value = '';
        return;
    }
    const arr = values.split(',');
    arr.forEach(v => {
        v = v.trim();
        if (!v) return;
        const chip = document.createElement('span');
        chip.className = chipsId.toLowerCase().includes('musthave') ? 'badge badge-pill badge-success mr-2 mb-2' : (chipsId.toLowerCase().includes('nicetohave') ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
        chip.style.fontSize = '13px';
        chip.style.padding = '6px 12px';
        chip.style.display = 'inline-flex';
        chip.style.alignItems = 'center';
        chip.innerHTML = `${v} <span style="cursor:pointer; margin-left:6px; font-weight:bold; font-size:14px;">×</span>`;
        chip.querySelector('span').onclick = (e) => {
            e.stopPropagation();
            chip.remove();
            if (hidden) {
                hidden.value = [...chips.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
            }
        };
        chips.appendChild(chip);
    });
    if (hidden) {
        hidden.value = arr.join(',');
    }
}

function initResChipAutocomplete(config) {
    const input = document.getElementById(config.inputId);
    const dropdown = document.getElementById(config.dropdownId);
    const chipsContainer = document.getElementById(config.chipsId);
    const hiddenInput = config.hiddenId ? document.getElementById(config.hiddenId) : null;
    if (!input || !dropdown || !chipsContainer) return;

    function syncHidden() {
        if (!hiddenInput) return;
        hiddenInput.value = [...chipsContainer.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
    }

    input.addEventListener('keyup', function(e) {
        if (e.key === ',' || e.keyCode === 188) {
            e.preventDefault();
            const value = this.value.replace(/,/g, '').trim();
            if (value.length >= 1) {
                addResChipDirect(value, config.inputId, config.chipsId, config.hiddenId);
            }
            return;
        }
        const q = this.value.trim();
        if (q.length < 2) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            return;
        }
        fetch(`${config.url}?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                dropdown.innerHTML = '';
                if (!data || !data.length) {
                    dropdown.innerHTML = '<span class="dropdown-item disabled">No results</span>';
                } else {
                    data.forEach(item => {
                        const value = item[config.key];
                        const el = document.createElement('a');
                        el.className = 'dropdown-item';
                        el.style.cursor = 'pointer';
                        el.textContent = value;
                        el.onclick = (evt) => {
                            evt.preventDefault();
                            evt.stopPropagation();
                            addResChipDirect(value, config.inputId, config.chipsId, config.hiddenId);
                        };
                        dropdown.appendChild(el);
                    });
                }
                dropdown.style.display = 'block';
            })
            .catch(() => {
                dropdown.style.display = 'none';
            });
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

$(document).on('click', '#btnGenerateJobContent', function(e) {
    e.preventDefault();

    // Trigger blur on active inputs to sync any typed chip values
    $('#resLocationInput, #resEducationInput, #resMustHaveSkillsInput, #resNiceToHaveSkillsInput, #resLanguageInput').trigger('blur');

    setTimeout(function() {
        var jobTitle       = $('input[name="JobTitle"]').val().trim();
        var functionalRole = $('input[name="FunctionalRole"]').val().trim();
        var deptSelect     = $('select[name="Did"] option:selected');
        var deptText       = deptSelect.length ? deptSelect.text().trim() : '';
        var department     = (deptText && !deptText.toLowerCase().includes('select')) ? deptText : '';
        var expMin         = $('#res_ExpMin').val() || 0;
        var expMax         = $('#res_ExpMax').val() || 0;

        var mustSkills     = $('#resMustHaveSkills').val() || $('#resMustHaveSkillsInput').val() || '';
        var niceSkills     = $('#resNiceToHaveSkills').val() || $('#resNiceToHaveSkillsInput').val() || '';
        var location       = $('#resJobLocation').val() || $('#resLocationInput').val() || '';
        var commLang       = $('#resCommunicationLang').val() || $('#resLanguageInput').val() || '';

        if (!jobTitle && !functionalRole) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Please enter a Job Title or Functional Role before generating.');
            } else {
                alert('Please enter a Job Title or Functional Role before generating.');
            }
            return;
        }

        var $btn = $('#btnGenerateJobContent');
        var origHtml = $btn.html();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating...');

        $.ajax({
            url: base_url + 'admin/generateJobContent',
            type: 'POST',
            data: {
                JobTitle: jobTitle,
                FunctionalRole: functionalRole,
                Department: department,
                ExpMin: expMin,
                ExpMax: expMax,
                MustHaveSkills: mustSkills,
                NiceToHaveSkills: niceSkills,
                JobLocation: location,
                CommunicationLang: commLang
            },
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html(origHtml);
                if (res && res.status === 'success') {
                    if (res.job_description) {
                        $('textarea[name="JobDescription"]').val(res.job_description);
                    }
                    if (res.responsibilities) {
                        $('textarea[name="Responsibilities"]').val(res.responsibilities);
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Job Description & Responsibilities auto-generated successfully!');
                    }
                } else {
                    var errorMsg = (res && res.message) ? res.message : 'Unable to generate job content. Please enter the details manually.';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html(origHtml);
                console.error('Job content generation error:', xhr.responseText);
                if (typeof toastr !== 'undefined') {
                    toastr.error('Unable to generate job content. Please enter the details manually.');
                } else {
                    alert('Unable to generate job content. Please enter the details manually.');
                }
            }
        });
    }, 250);
});

// Expose functions globally for inline HTML event handlers (onclick / onsubmit)
window.openCreateRequestModal = openCreateRequestModal;
window.openEditRequestModal = openEditRequestModal;
window.viewRequestDetails = viewRequestDetails;
window.openApprovalModal = openApprovalModal;
window.submitApproval = submitApproval;
window.resStepperNext = resStepperNext;
window.resStepperPrev = resStepperPrev;
window.goToResStep = goToResStep;
window.addResChipDirect = addResChipDirect;
window.preloadResChips = preloadResChips;
}



/* =========================================================================
 * VACANCY LIST
 * ========================================================================= */
if ($('#vacancyTable').length || $('#createVacancyModal').length || $('#editVacancyPanel').length || $('#openAddJobForm').length) {
document.addEventListener('DOMContentLoaded', function() {
          const createStepper = document.querySelector('#vacancyPanel .bs-stepper');
          if (createStepper) {
              window.stepper = new Stepper(createStepper);
          }

          const editStepperEl = document.querySelector('#editVacancyPanel .bs-stepper');
          if (editStepperEl) {
              window.editStepper = new Stepper(editStepperEl);
          }
      });

      document.querySelectorAll('.emp-type').forEach(el => {
          el.addEventListener('click', function() {
              document.querySelectorAll('.emp-type').forEach(b => b.classList.remove('active'));
              this.classList.add('active');
              document.getElementById('employment_type').value = this.dataset.value;
          });
      });

      document.querySelectorAll('.work-mode').forEach(el => {
          el.addEventListener('click', function() {
              document.querySelectorAll('.work-mode').forEach(b => b.classList.remove('active'));
              this.classList.add('active');
              document.getElementById('work_mode').value = this.dataset.value;
          });
      });

      document.addEventListener('DOMContentLoaded', function() {

          const expMin = document.getElementById('expMin');
          const expMax = document.getElementById('expMax');

          for (let i = 0; i <= 20; i++) {
              expMin.add(new Option(i + ' Year' + (i > 1 ? 's' : ''), i));
          }
          expMin.add(new Option('20+ Years', '20+'));

          expMin.addEventListener('change', function() {
              expMax.innerHTML = '<option value="">Max</option>';
              if (this.value === '20+') {
                  expMax.add(new Option('30+ Years', '30+'));
                  return;
              }
              const min = parseInt(this.value);
              for (let i = min; i <= 30; i++) {
                  expMax.add(new Option(i + ' Year' + (i > 1 ? 's' : ''), i));
              }
              expMax.add(new Option('30+ Years', '30+'));
          });

          const salaryMin = document.getElementById('salaryMin');
          const salaryMax = document.getElementById('salaryMax');

          for (let i = 1; i <= 50; i++) {
              salaryMin.add(new Option(i + ' LPA', i));
          }
          salaryMin.add(new Option('50+ LPA', '50+'));

          salaryMin.addEventListener('change', function() {
              salaryMax.innerHTML = '<option value="">Max Salary</option>';
              if (this.value === '50+') {
                  salaryMax.add(new Option('100+ LPA', '100+'));
                  return;
              }
              const min = parseInt(this.value);
              for (let i = min; i <= 80; i++) {
                  salaryMax.add(new Option(i + ' LPA', i));
              }
              salaryMax.add(new Option('100+ LPA', '100+'));
          });

      });

      function populateEditExpMin() {
          const el = document.getElementById('edit_expMin');
          el.innerHTML = '<option value="">Min</option>';
          for (let i = 0; i <= 20; i++) {
              el.add(new Option(i + ' Year' + (i > 1 ? 's' : ''), i));
          }
          el.add(new Option('20+ Years', '20+'));
      }

       function populateEditSalMin() {
           // Text input field used instead of select dropdown
       }


      $('#edit_expMin').on('change', function() {

          const min = parseInt(this.value);
          const maxEl = document.getElementById('edit_expMax');

          maxEl.innerHTML = '<option value="">Max</option>';

          if (this.value === '20+') {
              maxEl.add(new Option('30+ Years', '30+'));
              return;
          }

          for (let i = min + 1; i <= 30; i++) {
              maxEl.add(new Option(i + ' Year' + (i > 1 ? 's' : ''), i));
          }

          maxEl.add(new Option('30+ Years', '30+'));
      });
      // const minusBtn = document.querySelector('.minus');
      // const plusBtn  = document.querySelector('.plus');
      // const qtyInput = document.getElementById('positions');

      // function getValue() { return parseInt(qtyInput.value) || 1; }
      // function setValue(val) { qtyInput.value = val < 1 ? 1 : val; }

      // plusBtn.addEventListener('click',  () => setValue(getValue() + 1));
      // minusBtn.addEventListener('click', () => setValue(getValue() - 1));
      // qtyInput.addEventListener('blur',  () => setValue(getValue()));
      // qtyInput.addEventListener('input', () => { qtyInput.value = qtyInput.value.replace(/[^0-9]/g, ''); });

      $(document).ready(function() {
          $('#openVacancyPanel').on('click', function() {
              $('#vacancyPanel').addClass('open');
              $('#vacancyOverlay').addClass('show');
          });
          $('#closeVacancyPanel').on('click', function() {
              $('#vacancyPanel').removeClass('open');
              $('#vacancyOverlay').removeClass('show');
          });
          $('#closeEditVacancyPanel').on('click', function() {
              $('#editVacancyPanel').removeClass('open');
              $('#vacancyOverlay').removeClass('show');
          });
          $('#vacancyOverlay').on('click', function() {
              $('#vacancyPanel, #editVacancyPanel').removeClass('open');
              $('#vacancyOverlay').removeClass('show');
          });
      });

      initChipAutocomplete({
          inputId: 'jobLocationInput',
          dropdownId: 'jobLocationDropdown',
          chipsId: 'jobLocationChips',
          hiddenId: 'jobLocation',
          url: base_url + "admin/searchLocation",
          key: 'JobLocation'
      });
      initChipAutocomplete({
          inputId: 'educationInput',
          dropdownId: 'educationDropdown',
          chipsId: 'educationChips',
          hiddenId: 'education',
          url: base_url + "admin/searchEducation",
          key: 'EducationRequired'
      });
      initChipAutocomplete({
          inputId: 'mustHaveSkillsInput',
          dropdownId: 'mustHaveSkillsDropdown',
          chipsId: 'mustHaveSkillsChips',
          hiddenId: 'mustHaveSkills',
          url: base_url + "admin/searchSkills",
          key: 'SkillName'
      });
      initChipAutocomplete({
          inputId: 'niceToHaveSkillsInput',
          dropdownId: 'niceToHaveSkillsDropdown',
          chipsId: 'niceToHaveSkillsChips',
          hiddenId: 'niceToHaveSkills',
          url: base_url + "admin/searchSkills",
          key: 'SkillName'
      });
      initChipAutocomplete({
          inputId: 'languageInput',
          dropdownId: 'languageDropdown',
          chipsId: 'languageChips',
          hiddenId: 'comLanguage',
          url: base_url + "admin/searchLanguage",
          key: 'CommunicationLang'
      });

      initChipAutocomplete({
          inputId: 'edit_jobLocationInput',
          dropdownId: 'edit_jobLocationDropdown',
          chipsId: 'edit_jobLocationChips',
          hiddenId: 'edit_jobLocation',
          url: base_url + "admin/searchLocation",
          key: 'JobLocation'
      });
      initChipAutocomplete({
          inputId: 'edit_educationInput',
          dropdownId: 'edit_educationDropdown',
          chipsId: 'edit_educationChips',
          hiddenId: 'edit_education',
          url: base_url + "admin/searchEducation",
          key: 'EducationRequired'
      });
      initChipAutocomplete({
          inputId: 'edit_mustHaveSkillsInput',
          dropdownId: 'edit_mustHaveSkillsDropdown',
          chipsId: 'edit_mustHaveSkillsChips',
          hiddenId: 'edit_mustHaveSkills',
          url: base_url + "admin/searchSkills",
          key: 'SkillName'
      });
      initChipAutocomplete({
          inputId: 'edit_niceToHaveSkillsInput',
          dropdownId: 'edit_niceToHaveSkillsDropdown',
          chipsId: 'edit_niceToHaveSkillsChips',
          hiddenId: 'edit_niceToHaveSkills',
          url: base_url + "admin/searchSkills",
          key: 'SkillName'
      });
      initChipAutocomplete({
          inputId: 'edit_languageInput',
          dropdownId: 'edit_languageDropdown',
          chipsId: 'edit_languageChips',
          hiddenId: 'edit_comLanguage',
          url: base_url + "admin/searchLanguage",
          key: 'CommunicationLang'
      });

      document.querySelectorAll('.edit-emp-type').forEach(el => {
          el.addEventListener('click', function() {
              document.querySelectorAll('.edit-emp-type').forEach(b => b.classList.remove('active'));
              this.classList.add('active');
              document.getElementById('edit_employment_type').value = this.dataset.value;
          });
      });

      document.querySelectorAll('.edit-work-mode').forEach(el => {
          el.addEventListener('click', function() {
              document.querySelectorAll('.edit-work-mode').forEach(b => b.classList.remove('active'));
              this.classList.add('active');
              document.getElementById('edit_work_mode').value = this.dataset.value;
          });
      });

      $(document).on('click', '.editJobBtn', function() {

          const jid = $(this).data('id');
          $('#edit_jid').val(jid);

          $.post(base_url + "admin/getJobDetails", {
              jid: jid
          }, function(res) {

              const d = JSON.parse(res);
              // $('#editJobCodeText').text('(' + d.JobCode + ')');
              $('#editJobCodeText').text(d.JobCode);

              $('#edit_jobCode').val(d.JobCode ?? '');
              $('#edit_jobTitle').val(d.JobTitle ?? '');

              $('#edit_department').val(d.Departmentname ?? '');
              $('#edit_role').val(d.RoleSummary ?? '');

              $('#edit_positions').val(d.NoofOpenings ?? '');
              $('#edit_JD').val(d.JobDescription ?? '');
              $('#edit_RR').val(d.Responsibilities ?? '');

              $('#edit_salaryMin').val(d.SalMin ?? '');
              $('#edit_salaryMax').val(d.SalMax ?? '');

              populateEditExpMin();
              const cleanMin = (d.ExpMin !== null && d.ExpMin !== undefined) ? (parseFloat(d.ExpMin) % 1 === 0 ? parseInt(d.ExpMin) : parseFloat(d.ExpMin)) : '';
              const cleanMax = (d.ExpMax !== null && d.ExpMax !== undefined) ? (parseFloat(d.ExpMax) % 1 === 0 ? parseInt(d.ExpMax) : parseFloat(d.ExpMax)) : '';

              $('#edit_expMin').val(cleanMin);

              const expMinVal = parseInt(d.ExpMin) || 0;
              const expMaxEl = document.getElementById('edit_expMax');
              if (expMaxEl) {
                  expMaxEl.innerHTML = '<option value="">Max</option>';
                  for (let i = expMinVal; i <= 30; i++) {
                      expMaxEl.add(new Option(i + ' Year' + (i > 1 ? 's' : ''), i));
                  }
                  expMaxEl.add(new Option('30+ Years', '30+'));
                  $('#edit_expMax').val(cleanMax);
              }

              console.log('SalMin:', d.SalMin, '| SalMax:', $('#edit_salaryMax').val());
              console.log('ExpMin:', d.ExpMin, '| ExpMax:', $('#edit_expMax').val());

              const workMode = (d.WorkMode || '').toString().trim();
              const empType = (d.EmploymentType || '').toString().trim();

              $('.edit-work-mode').removeClass('active');
              $('.edit-emp-type').removeClass('active');

              $('.edit-work-mode').each(function() {
                  if ($(this).data('value').trim() === workMode) {
                      $(this).addClass('active');
                      $('#edit_work_mode').val(workMode);
                  }
              });

              $('.edit-emp-type').each(function() {
                  if ($(this).data('value').trim() === empType) {
                      $(this).addClass('active');
                      $('#edit_employment_type').val(empType);
                  }
              });

              preloadChips(d.JobLocation, 'edit_jobLocationChips', 'edit_jobLocation');
              preloadChips(d.EducationRequired, 'edit_educationChips', 'edit_education');
              preloadChips(d.MustHaveSkills || d.Skills, 'edit_mustHaveSkillsChips', 'edit_mustHaveSkills');
              preloadChips(d.NiceToHaveSkills, 'edit_niceToHaveSkillsChips', 'edit_niceToHaveSkills');
              preloadChips(d.CommunicationLang, 'edit_languageChips', 'edit_comLanguage');

              // Populate Salary & CTC Approver
              $('#edit_salary').val(d.Salary ?? '');
              $('#edit_CtcApproverId').val(d.CtcApproverId || d.EffectiveCtcApproverId || '');

              // Reset & Populate Interview Panel dropdowns
              $('#editDynamicLevelsContainer').empty();
              $('#edit_interviewPanel_1').val('');
              $('#edit_interviewPanel_2').val('');
              updateEditAddLevelBtnState();

              const panels = d.interviewPanels || [];
              if (Array.isArray(panels) && panels.length > 0) {
                  panels.forEach(function(p) {
                      var lvl = parseInt(p.LevelOrder);
                      var uid = p.InterviewerId;
                      if (lvl === 1) {
                          $('#edit_interviewPanel_1').val(uid);
                      } else if (lvl === 2) {
                          $('#edit_interviewPanel_2').val(uid);
                      } else if (lvl === 3 || lvl === 4) {
                          addEditDynamicLevel(lvl, uid);
                      }
                  });
              }

              $('#editVacancyPanel').addClass('open');
              $('#vacancyOverlay').addClass('show');

          });
      });

var editUsersOptionsHtml = `<option value="">Select Interviewer</option><?php 
if (!empty($ctc_approvers)) {
    foreach ($ctc_approvers as $u) {
        echo '<option value="' . $u['IUid'] . '">' . htmlspecialchars($u['EmpName']) . (!empty($u['RoleName']) ? ' (' . htmlspecialchars($u['RoleName']) . ')' : '') . '</option>';
    }
}
?>`;

function getEditCurrentLevelCount() {
    return $('#editInterviewPanelContainer .form-group[data-level]').length;
}

function updateEditAddLevelBtnState() {
    if (getEditCurrentLevelCount() >= 4) {
        $('#addEditInterviewLevelBtn').hide();
    } else {
        $('#addEditInterviewLevelBtn').show();
    }
}

function addEditDynamicLevel(levelNum, selectedVal) {
    if ($('#edit-dynamic-level-' + levelNum).length) return;
    var html = `
        <div class="form-group mb-2 dynamic-level-row" id="edit-dynamic-level-${levelNum}" data-level="${levelNum}">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <label class="font-weight-bold mb-0">Level ${levelNum} Interviewer</label>
                <button type="button" class="btn btn-xs btn-outline-danger remove-edit-level-btn" data-target="edit-dynamic-level-${levelNum}">
                    <i class="fas fa-minus mr-1"></i> Remove
                </button>
            </div>
            <select name="interviewPanel[${levelNum}]" id="edit_interviewPanel_${levelNum}" class="form-control interview-panel-select">
                ${editUsersOptionsHtml}
            </select>
        </div>
    `;
    $('#editDynamicLevelsContainer').append(html);
    if (selectedVal) {
        $('#edit_interviewPanel_' + levelNum).val(selectedVal);
    }
    updateEditAddLevelBtnState();
}

$(document).on('click', '#addEditInterviewLevelBtn', function() {
    var count = getEditCurrentLevelCount();
    if (count < 4) {
        var nextLevel = count + 1;
        addEditDynamicLevel(nextLevel);
    }
});

$(document).on('click', '.remove-edit-level-btn', function() {
    var targetId = $(this).data('target');
    $('#' + targetId).remove();
    updateEditAddLevelBtnState();
});

   let selectedJobId = '';
let selectedStatus = '';

$(document).on('click', '.jobStatusBtn', function () {

    selectedJobId = $(this).data('id');
    selectedStatus = $(this).data('status');

    // If putting on hold, show dedicated date picker modal
    if (selectedStatus === 'On-Hold') {
        // Set minimum date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minDate = tomorrow.toISOString().split('T')[0];
        $('#holdUntilDateInput').attr('min', minDate).val('');
        $('#holdDateModal').modal('show');
        return;
    }

    let message = '';
    if (selectedStatus === 'Closed' || selectedStatus === 'Dropped') {
        message = "Are you sure you want to drop this job?";
    } else if (selectedStatus === 'Open') {
        message = "Are you sure you want to reopen this job?";
    } else if (selectedStatus === 'Re-Open') {
        message = "Are you sure you want to reopen this job?";
    } else {
        message = "Are you sure you want to change this job status?";
    }

    $('#jobStatusMessage').text(message);
    $('#jobStatusModal').modal('show');

});

// Confirm Hold Date
$('#confirmHoldDate').on('click', function () {
    const holdDate = $('#holdUntilDateInput').val();
    if (!holdDate) {
        toastr.warning('Please select a hold-until date.');
        return;
    }

    const today = new Date().toISOString().split('T')[0];
    if (holdDate <= today) {
        toastr.warning('Hold date must be a future date.');
        return;
    }

    $('#confirmHoldDate').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...');

    $.ajax({
        url: base_url + "admin/updateJobStatus",
        type: 'POST',
        dataType: 'json',
        data: {
            jid: selectedJobId,
            status: 'On-Hold',
            holdUntilDate: holdDate
        },
        success: function(res) {
            $('#holdDateModal').modal('hide');
            $('#confirmHoldDate').prop('disabled', false).html('<i class="fas fa-pause-circle mr-1"></i>Confirm Hold');
            if (res.status === 'success') {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1200);
            } else {
                toastr.error(res.message || 'Something went wrong');
            }
        },
        error: function() {
            $('#confirmHoldDate').prop('disabled', false).html('<i class="fas fa-pause-circle mr-1"></i>Confirm Hold');
            toastr.error('Server error occurred');
        }
    });
});

      $('#confirmJobStatus').click(function () {

    $.ajax({
        url: base_url + "admin/updateJobStatus",
        type: 'POST',
        dataType: 'json',
        data: {
            jid: selectedJobId,
            status: selectedStatus
        },
        success: function(res) {

            $('#jobStatusModal').modal('hide');

            if (res.status === 'success') {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1200);
            } else {
                toastr.error(res.message || 'Something went wrong');
            }

        },
        error: function() {
            toastr.error('Server error occurred');
        }
    });

});
      $('#editVacancyForm').submit(function(e) {
          e.preventDefault();
          $.ajax({
              url: base_url + "admin/updateVacancy",
              type: 'POST',
              data: $(this).serialize(),
              dataType: 'json',
              success: function(res) {
                  if (res.status == 'success') {
                      toastr.success('Vacancy updated');
                      location.reload();
                  } else {
                      toastr.error('Update failed');
                  }
              }
          });
      });

      $(document).on('click', '.viewVacancyBtn', function() {
          let jid = $(this).data('id');
          $('#vacancyDetailsModal').modal('show');
          $('#vacancyDetailsBody').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
          $.post(base_url + "admin/getJobDetails", {
              jid: jid
          }, function(res) {
              let d = JSON.parse(res);
              let html = `<div class="container-fluid">`;
              html += `<div class="card card-primary"><div class="card-header bg-primary"><h3 class="card-title">Basic Information</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div></div><div class="card-body"><div class="row"><div class="col-md-6"><p><b>Job Code:</b> ${d.JobCode}</p><p><b>Job Title:</b> ${d.JobTitle}</p><p><b>Department:</b> ${d.Departmentname}</p><p><b>Role:</b> ${d.RoleSummary}</p><p><b>Status:</b>
<span class="badge badge-pill
${d.JobStatus === 'Open' || d.JobStatus === 'Re-Open' ? 'badge-success' :
  (d.JobStatus === 'Closed' || d.JobStatus === 'Dropped') ? 'badge-danger' :
  d.JobStatus === 'On-Hold' ? 'badge-warning' :
  d.JobStatus === 'Draft' ? 'badge-secondary' :
  d.JobStatus === 'Not Required' ? 'badge-dark' :
  'badge-primary'}">
${(d.JobStatus === 'Closed' || d.JobStatus === 'Dropped') ? 'Dropped' : d.JobStatus}
</span>
</p></div><div class="col-md-6"><p><b>Posted By:</b> ${d.PostedByName}</p><p><b>Posted On:</b> ${d.PostedOn}</p><p><b>Work Mode:</b> ${d.WorkMode}</p><p><b>Employment:</b> ${d.EmploymentType}</p><p><b>Language:</b> ${d.CommunicationLang}</p></div></div></div></div>`;
              let salaryDisplay = (d.Salary && d.Salary.trim() !== '' && d.Salary !== '0 - 0 LPA') ? d.Salary : ((d.SalMin || d.SalMax) ? (d.SalMin + ' - ' + d.SalMax + ' LPA') : '-');
              let expMinDisplay = parseInt(d.ExpMin) || 0;
              let expMaxDisplay = parseInt(d.ExpMax) || 0;
              let expDisplay = `${expMinDisplay} - ${expMaxDisplay} Years`;

              html += `<div class="card card-info collapsed-card"><div class="card-header bg-info"><h3 class="card-title">Salary & Experience</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><div class="row"><div class="col-md-6"><p><b>Experience Required:</b> ${expDisplay}</p></div><div class="col-md-6"><p><b>Salary:</b> ${salaryDisplay}</p></div></div></div></div>`;
              html += `<div class="card card-secondary collapsed-card"><div class="card-header bg-secondary"><h3 class="card-title">Location & Education</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><p><b>Job Location:</b> ${d.JobLocation}</p><p><b>Education Required:</b> ${d.EducationRequired}</p></div></div>`;
              html += `<div class="card card-warning collapsed-card"><div class="card-header bg-warning"><h3 class="card-title">Skills</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><p><b>Must-Have Skills:</b> ${d.MustHaveSkills || d.Skills || '-'}</p><p><b>Nice-to-Have Skills:</b> ${d.NiceToHaveSkills || '-'}</p></div></div>`;
              html += `<div class="card card-dark collapsed-card"><div class="card-header bg-dark"><h3 class="card-title">Roles & Responsibilities</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body">${d.Responsibilities}</div></div>`;
              html += `<div class="card card-success collapsed-card"><div class="card-header bg-success"><h3 class="card-title">Job Description</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body">${d.JobDescription}</div></div>`;
              
              html += `</div>`;
              $('#vacancyDetailsBody').html(html);
          });
      });

      function preloadChips(values, chipsId, hiddenId) {
          if (!values) return;
          const arr = values.split(',');
          const chips = document.getElementById(chipsId);
          const hidden = hiddenId ? document.getElementById(hiddenId) : null;
          chips.innerHTML = '';
          arr.forEach(v => {
              v = v.trim();
              if (chipsId === 'edit_mustHaveSkillsChips') {
                  $('<input>').attr('type', 'hidden').attr('name', 'mustHaveSkills[]').val(v).appendTo('#editVacancyForm');
              }
              if (chipsId === 'edit_niceToHaveSkillsChips') {
                  $('<input>').attr('type', 'hidden').attr('name', 'niceToHaveSkills[]').val(v).appendTo('#editVacancyForm');
              }
              const chip = document.createElement('span');
              chip.className = chipsId.includes('mustHave') ? 'badge badge-pill badge-success mr-2 mb-2' : (chipsId.includes('niceToHave') ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
              chip.innerHTML = `${v} <span class="cursor-pointer">×</span>`;
              chip.querySelector('span').onclick = () => {
                  chip.remove();
                  if (chipsId === 'edit_mustHaveSkillsChips') {
                      $('#editVacancyForm input[name="mustHaveSkills[]"][value="' + v + '"]').remove();
                  }
                  if (chipsId === 'edit_niceToHaveSkillsChips') {
                      $('#editVacancyForm input[name="niceToHaveSkills[]"][value="' + v + '"]').remove();
                  }
                  if (hidden) {
                      hidden.value = [...chips.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
                  }
              };
              chips.appendChild(chip);
          });
          if (hidden) {
              hidden.value = arr.join(',');
          }
      }

      function initChipAutocomplete(config) {
          const input = document.getElementById(config.inputId);
          const dropdown = document.getElementById(config.dropdownId);
          const chipsContainer = document.getElementById(config.chipsId);
          const hiddenInput = config.hiddenId ? document.getElementById(config.hiddenId) : null;

          function syncHidden() {
              if (!hiddenInput) return;
              hiddenInput.value = [...chipsContainer.querySelectorAll('.badge')].map(x => x.textContent.replace('×', '').trim()).join(',');
          }

          input.addEventListener('keyup', function() {
              const q = this.value.trim();
              if (q.length < 3) {
                  dropdown.style.display = 'none';
                  dropdown.innerHTML = '';
                  return;
              }
              fetch(`${config.url}?q=${encodeURIComponent(q)}`)
                  .then(res => res.json())
                  .then(data => {
                      dropdown.innerHTML = '';
                      if (!data.length) {
                          dropdown.innerHTML = '<span class="dropdown-item disabled">No results</span>';
                      } else {
                          data.forEach(item => {
                              const value = item[config.key];
                              const el = document.createElement('a');
                              el.className = 'dropdown-item';
                              el.textContent = value;
                              el.onclick = () => addChip(value);
                              dropdown.appendChild(el);
                          });
                      }
                      dropdown.style.display = 'block';
                  });
          });

          input.addEventListener('keydown', function(e) {
              if (e.key === 'Enter') {
                  e.preventDefault();
                  const value = input.value.trim();
                  if (value.length >= 2) addChip(value);
              }
          });


          function addChip(value) {
              if (!value) return;

              if (config.inputId === 'edit_mustHaveSkillsInput') {
                  if ($('input[name="mustHaveSkills[]"][value="' + value + '"]').length) return;
                  $('<input>').attr('type', 'hidden').attr('name', 'mustHaveSkills[]').val(value)
                      .appendTo(input.closest('form'));
              }

              if (config.inputId === 'edit_niceToHaveSkillsInput') {
                  if ($('input[name="niceToHaveSkills[]"][value="' + value + '"]').length) return;
                  $('<input>').attr('type', 'hidden').attr('name', 'niceToHaveSkills[]').val(value)
                      .appendTo(input.closest('form'));
              }

              if (config.inputId === 'mustHaveSkillsInput' || config.inputId === 'niceToHaveSkillsInput') {
                  const existing = [...chipsContainer.querySelectorAll('.badge')]
                      .map(x => x.textContent.replace('×', '').trim());
                  if (existing.includes(value)) return;
              }
              const chip = document.createElement('span');
              chip.className = config.inputId.includes('mustHave') ? 'badge badge-pill badge-success mr-2 mb-2' : (config.inputId.includes('niceToHave') ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
              chip.innerHTML = `${value} <span class="cursor-pointer">×</span>`;

              chip.querySelector('span').onclick = () => {
                  chip.remove();
                  if (config.inputId === 'edit_mustHaveSkillsInput') {
                      $('input[name="mustHaveSkills[]"][value="' + value + '"]').remove();
                  }
                  if (config.inputId === 'edit_niceToHaveSkillsInput') {
                      $('input[name="niceToHaveSkills[]"][value="' + value + '"]').remove();
                  }
                  syncHidden();
              };
              chipsContainer.appendChild(chip);
              input.value = '';
              dropdown.style.display = 'none';
              syncHidden();
          }

          document.addEventListener('click', e => {
              if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                  dropdown.style.display = 'none';
              }
          });
      }
      // $(document).ready(function () {

      //   var table = $('#example1').DataTable();


      //     // Department filter
      //     $('#filterDepartment').on('change', function () {
      //         table.column(3).search(this.value).draw();
      //     });

      //     // Status filter
      //     $('#filterStatus').on('change', function () {
      //         table.column(8).search(this.value).draw();
      //     });

      //     var startDate = '';
      //     var endDate = '';

      //     $('#dateRange').daterangepicker({
      //         autoUpdateInput: false,
      //         locale: { cancelLabel: 'Clear' }
      //     });

      //     $('#dateRange').on('apply.daterangepicker', function (ev, picker) {
      //         startDate = picker.startDate.format('YYYY-MM-DD');
      //         endDate = picker.endDate.format('YYYY-MM-DD');
      //         $(this).val(startDate + ' - ' + endDate);
      //         table.draw();
      //     });

      //     $('#dateRange').on('cancel.daterangepicker', function () {
      //         $(this).val('');
      //         startDate = '';
      //         endDate = '';
      //         table.draw();
      //     });

      //     $.fn.dataTable.ext.search.push(function (settings, data) {

      //         if (!startDate || !endDate) return true;

      //         var postedDate = data[9]; // Posted On column
      //         if (!postedDate) return false;

      //         var posted = moment(postedDate);

      //         return posted.isBetween(startDate, endDate, null, '[]');
      //     });

      //     $('#resetFilters').on('click', function () {

      //         $('#filterDepartment').val('');
      //         $('#filterStatus').val('');
      //         $('#dateRange').val('');

      //         startDate = '';
      //         endDate = '';

      //         table.search('').columns().search('').draw();
      //     });

      // });
      $(function() {

          $('#dateRange').daterangepicker({
              autoUpdateInput: false,
              locale: {
                  format: 'YYYY-MM-DD',
                  cancelLabel: 'Clear'
              }
          });

          $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
              $(this).val(
                  picker.startDate.format('YYYY-MM-DD') +
                  ' - ' +
                  picker.endDate.format('YYYY-MM-DD')
              );
          });

          $('#dateRange').on('cancel.daterangepicker', function() {
              $(this).val('');
          });

      });

      $(document).ready(function() {

          // Auto submit when department changes
        //   $('select[name="department"]').on('change', function() {
        //       $(this).closest('form').submit();
        //   });
        $('form[action*="admin/vacancies"] select[name="department"]').on('change', function() {
    $(this).closest('form').submit();
});

          // Auto submit when status changes
          $('select[name="status"]').on('change', function() {
              $(this).closest('form').submit();
          });

          // Auto submit when date selected
          $('#dateRange').on('apply.daterangepicker', function() {
              $(this).closest('form').submit();
          });

      });
      $(document).on('click', '#vacancyDetailsModal [data-card-widget="collapse"]', function() {

          let currentCard = $(this).closest('.card');

          if (currentCard.hasClass('collapsed-card')) {

              $('#vacancyDetailsModal .card').not(currentCard).each(function() {
                  if (!$(this).hasClass('collapsed-card')) {
                      $(this).CardWidget('collapse');
                  }
              });

          }

      });
      if ($("#jobExistsModal.auto-show-modal").length) { $("#jobExistsModal").modal("show"); }
const minusBtn = document.querySelector('.minus');
const plusBtn  = document.querySelector('.plus');
const qtyInput = document.getElementById('positions');

function getValue() {
    return parseInt(qtyInput.value) || 1;
}

function setValue(val) {
    qtyInput.value = val < 1 ? 1 : val;
}

if (plusBtn && minusBtn) {
    plusBtn.addEventListener('click', () => setValue(getValue() + 1));
    minusBtn.addEventListener('click', () => setValue(getValue() - 1));
}

qtyInput.addEventListener('input', () => {
    qtyInput.value = qtyInput.value.replace(/[^0-9]/g, '');
});

// Calculate dynamic ATS marks total
function calculateTotalAtsMarks(containerSelector) {
    let total = 0;
    $(containerSelector).find('.ats-score-input').each(function() {
        let val = parseInt($(this).val()) || 0;
        if (val < 0) {
            val = 0;
            $(this).val(0);
        }
        total += val;
    });
    $(containerSelector).find('.total-ats-marks').text(total);
}

// Bind live total updates and validate inputs for non-negative
$(document).on('input change keyup', '.ats-score-input', function() {
    let val = $(this).val();
    if (val !== '' && parseInt(val) < 0) {
        $(this).val(0);
    }
    if ($(this).closest('#vacancyPanel').length) {
        calculateTotalAtsMarks('#vacancyPanel');
    } else if ($(this).closest('#editVacancyPanel').length) {
        calculateTotalAtsMarks('#editVacancyPanel');
    }
});

/* ===== View Job Life-Cycle History ===== */
$(document).on('click', '.viewJobHistoryBtn', function () {
    let jid = $(this).data('id');
    $('#jobHistoryModal').modal('show');
    $('#jobHistoryModalBody').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2 text-muted">Loading job life-cycle history...</p></div>');

    $.ajax({
        url: base_url + "admin/getJobHistoryDetails",
        type: 'POST',
        data: { jid: jid },
        dataType: 'json',
        success: function(res) {
            if (res.status !== 'success') {
                $('#jobHistoryModalBody').html('<div class="alert alert-danger">Unable to load job history details.</div>');
                return;
            }

            let job = res.job;
            let rr  = res.resource_request;
            let timeline = res.timeline || [];

            let html = `<div class="container-fluid p-0">`;

            let m = res.milestones || {};
            let posFilledText = m.position_filled 
                ? `<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>${m.position_filled.candidate_name} (${m.position_filled.candidate_code}) on ${m.position_filled.filled_at}</span>`
                : `<span class="text-muted"><i class="fas fa-hourglass-half mr-1"></i>Not Filled Yet (${res.candidate_count} Applications)</span>`;

            // Job Summary & Milestone Card
            html += `
            <div class="card bg-light mb-4" style="border-left:4px solid #007bff; border-radius:8px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold text-primary mb-0">
                            <i class="fas fa-briefcase mr-2"></i>${job.JobTitle} (${job.JobCode})
                        </h6>
                        <span class="badge ${job.JobStatus === 'Open' ? 'badge-success' : (job.JobStatus === 'On-Hold' ? 'badge-warning' : 'badge-secondary')} px-3 py-1 font-weight-bold">
                            Status: ${job.JobStatus}
                        </span>
                    </div>
                    <div class="row text-sm">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>1. Requested By:</strong> ${m.requested_by ?? '-'}</p>
                            <p class="mb-1"><strong>2. Approved By:</strong> ${m.approved_by ?? '-'}</p>
                            <p class="mb-1"><strong>Got Hold Date:</strong> ${m.hold_at ?? '<span class="text-muted">N/A</span>'}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>3. Assigned Recruiter:</strong> ${m.assigned_to ?? 'Unassigned'}</p>
                            <p class="mb-1"><strong>4. CTC Approver:</strong> ${m.ctc_approver ?? 'Not Assigned'}</p>
                            <p class="mb-1"><strong>Hold-Until Date:</strong> ${m.hold_until ?? '<span class="text-muted">N/A</span>'}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Got Unhold Date:</strong> ${m.unhold_at ?? '<span class="text-muted">N/A (Active)</span>'}</p>
                            <p class="mb-1"><strong>Dropped Date:</strong> ${m.dropped_at ?? m.closed_at ?? '<span class="text-muted">N/A</span>'}</p>
                        </div>
                    </div>
                    <div class="pt-2 mt-2 border-top text-sm">
                        <p class="mb-0"><strong>Position Filled Status:</strong> ${posFilledText}</p>
                    </div>
                </div>
            </div>`;

            // Linked Resource Request Box
            if (rr) {
                html += `
                <div class="card bg-light mb-4" style="border-left:4px solid #17a2b8; border-radius:8px;">
                    <div class="card-body p-3">
                        <h6 class="font-weight-bold text-info mb-2"><i class="fas fa-file-alt mr-2"></i>Linked Resource Request (${rr.RequestCode ?? 'N/A'})</h6>
                        <div class="row text-sm">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Requested By:</strong> ${rr.RequestedByName ?? 'Hiring Manager'}</p>
                                <p class="mb-1"><strong>Requested On:</strong> ${rr.RequestedOn ?? '-'}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Assigned Recruiter:</strong> ${rr.AssignedManagerName ?? 'Unassigned'}</p>
                                <p class="mb-1"><strong>Target Onboarding:</strong> ${rr.TargetOnboardingDate ?? 'N/A'}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>CTC Approver:</strong> ${rr.CtcApproverName ?? 'Not Assigned'}</p>
                                <p class="mb-1"><strong>Expected Salary Range:</strong> ${typeof formatExpectedSalaryRange === 'function' ? formatExpectedSalaryRange(rr.ExpectedSalaryMin, rr.ExpectedSalaryMax) : '-'}</p>
                                <p class="mb-1"><strong>Request Status:</strong> <span class="badge badge-success">${rr.Status ?? 'ACCEPTED'}</span></p>
                            </div>
                        </div>
                    </div>
                </div>`;
            }

            // Life-Cycle Audit Timeline
            html += `<h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-stream mr-2 text-info"></i>Full Life-Cycle Audit Trail</h5>`;
            html += `<div class="timeline timeline-inverse">`;

            if (timeline.length > 0) {
                timeline.forEach(function(item) {
                    html += `
                    <div>
                        <i class="${item.icon}"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock mr-1"></i>${item.timestamp}</span>
                            <h3 class="timeline-header font-weight-bold text-dark">${item.title}</h3>
                            <div class="timeline-body text-sm">
                                <p class="mb-1"><strong>User / Actor:</strong> ${item.user}</p>
                                <p class="mb-0 text-muted">${item.description}</p>
                            </div>
                        </div>
                    </div>`;
                });
            } else {
                html += `<p class="text-muted p-2">No timeline events recorded for this job.</p>`;
            }

            html += `<div><i class="far fa-clock bg-gray"></i></div></div></div>`;

            $('#jobHistoryModalBody').html(html);
        },
        error: function() {
            $('#jobHistoryModalBody').html('<div class="alert alert-danger">Error loading history details.</div>');
        }
    });
});

/* ===== Bulk ATS Resume Upload Modal Script ===== */
$(document).on('click', '.uploadResumeBtn', function() {
    let jid = $(this).data('id') || $(this).closest('.uploadResumeBtn').data('id') || $(this).attr('data-id') || $(this).closest('.uploadResumeBtn').attr('data-id');
    $('#upload_job_id').val(jid);
    $('#jobId').val(jid);
    $('#bulkResumeInput').val('');
    $('#selectedFilesList').empty();
    $('#selectedFilesContainer').addClass('d-none');
    $('#uploadStatusAlert').addClass('d-none').removeClass('alert-success alert-danger alert-info');
    $('#uploadProgressBarContainer').addClass('d-none');
    $('#btnSubmitBulkResumes').prop('disabled', true);
    $('#uploadModal').modal('show');
});

$(document).on('click', '#dropZoneArea', function(e) {
    if (e.target.id === 'bulkResumeInput') return;
    $('#bulkResumeInput').click();
});

$(document).on('dragover dragenter', '#dropZoneArea', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).css('background-color', '#e8f5e9').css('border-color', '#1e7e34');
});

$(document).on('dragleave drop', '#dropZoneArea', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).css('background-color', '#f8f9fa').css('border-color', '#28a745');
});

$(document).on('drop', '#dropZoneArea', function(e) {
    let files = e.originalEvent.dataTransfer.files;
    if (files && files.length > 0) {
        let input = document.getElementById('bulkResumeInput');
        input.files = files;
        updateSelectedFilesList();
    }
});

$(document).on('change', '#bulkResumeInput', function() {
    updateSelectedFilesList();
});

function updateSelectedFilesList() {
    let input = document.getElementById('bulkResumeInput');
    let files = input ? input.files : null;
    let container = $('#selectedFilesList');
    container.empty();

    if (files && files.length > 0) {
        $('#selectedFileCount').text(files.length);
        $('#selectedFilesContainer').removeClass('d-none');
        $('#btnSubmitBulkResumes').prop('disabled', false);

        for (let i = 0; i < files.length; i++) {
            let file = files[i];
            let size = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            let icon = file.name.endsWith('.pdf') ? 'fa-file-pdf text-danger' : 'fa-file-word text-primary';
            container.append(`
                <div class="d-flex justify-content-between align-items-center p-2 mb-1 bg-white rounded border">
                    <span class="text-truncate small"><i class="fas ${icon} mr-2"></i><strong>${file.name}</strong></span>
                    <span class="badge badge-light text-muted small ml-2">${size}</span>
                </div>
            `);
        }
    } else {
        $('#selectedFilesContainer').addClass('d-none');
        $('#btnSubmitBulkResumes').prop('disabled', true);
    }
}

$(document).on('click', '#btnClearSelectedFiles', function(e) {
    e.stopPropagation();
    $('#bulkResumeInput').val('');
    updateSelectedFilesList();
});

$(document).on('submit', '#bulkResumeUploadForm', function(e) {
    e.preventDefault();

    let jid = $('#upload_job_id').val() || $('#jobId').val();
    if (!jid) {
        toastr.error('Job ID missing. Please close the modal and click upload again.');
        return;
    }

    let formData = new FormData(this);
    if (!formData.get('job_id') || formData.get('job_id') === '') {
        formData.set('job_id', jid);
    }
    let submitBtn = $('#btnSubmitBulkResumes');

    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Analyzing Resumes...');
    $('#uploadProgressBarContainer').removeClass('d-none');
    $('#uploadProgressBar').css('width', '60%');

    $.ajax({
        url: base_url + "admin/ats/analyzeResumeModal",
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(res) {
            $('#uploadProgressBar').css('width', '100%');
            submitBtn.prop('disabled', false).html('<i class="fas fa-cogs mr-2"></i>Upload & Analyze Resumes');

            if (res.status === 'success') {
                $('#uploadStatusAlert')
                    .removeClass('d-none alert-danger alert-info')
                    .addClass('alert-success')
                    .html('<i class="fas fa-check-circle mr-2"></i>' + res.message);

                toastr.success(res.message);
                setTimeout(function() {
                    if (res.redirect) {
                        window.location.href = res.redirect;
                    } else {
                        location.reload();
                    }
                }, 1500);
            } else {
                $('#uploadStatusAlert')
                    .removeClass('d-none alert-success alert-info')
                    .addClass('alert-danger')
                    .html('<i class="fas fa-exclamation-triangle mr-2"></i>' + res.message);
                toastr.error(res.message || 'Failed to process resumes');
            }
        },
        error: function() {
            submitBtn.prop('disabled', false).html('<i class="fas fa-cogs mr-2"></i>Upload & Analyze Resumes');
            $('#uploadProgressBarContainer').addClass('d-none');
            $('#uploadStatusAlert')
                .removeClass('d-none alert-success alert-info')
                .addClass('alert-danger')
                .html('<i class="fas fa-exclamation-triangle mr-2"></i>Server error during resume processing');
            toastr.error('Server error during upload');
        }
    });
});
}


/* =========================================================================
 * MY INTERVIEWS
 * ========================================================================= */
if ($('#interviewsTable').length || $('#interviewPanel').length) {
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
            url: base_url + 'admin/filterAssignedInterviews',
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
            url: base_url + 'admin/updateInterviewResult',
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
            url: base_url + 'admin/getInterviewDetails',
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
            url: base_url + 'admin/getCandidateIdDetails',
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
            url: base_url + 'admin/getCandidate360Details',
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
            url: base_url + 'admin/getAiInterviewQuestions',
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
            url: base_url + 'admin/generateAiInterviewQuestions',
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
            url: base_url + 'admin/updateQuestionStatus',
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
}


/* =========================================================================
 * CANDIDATE LIST
 * ========================================================================= */
if ($('#candidateListContainer').length || $('#example1').length || $('#compareActiveBar').length) {
$(document).on('click', '.editCandidateDetails', function () {

    let candidateId = $(this).data('id');
    console.log(candidateId);
    $('#candidateupdateDetailsModal').modal('show');
    $('#candidateupdateDetailsBody').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');

   
    });



function loadNextStages(currentOrder){

 $.post(base_url + 'admin/getNextStages',{
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

   $('.shortlistedOnly, .followupOnly, .onlineOnly').hide();
   $('#interviewDate').val('');
   $('#interviewType').val('');
   $('#meetingPlatform').val('Microsoft Teams');
   $('#teamsMeetingLink').val('');
   $('#stageAction').val('');
   $('#stageRemarks').val('');

   window.currentCandidateJobPanels = [];
   $.post(base_url + 'admin/getCandidateInterviewPanelInfo', { candidateId: cid }, function(res) {
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

    $.post(base_url + 'admin/saveCandidateStage', {
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

            if (intType === 'Online') {
                var meetingPlatform = $('#meetingPlatform').val();
                var teamsMeetingLink = $.trim($('#teamsMeetingLink').val());

                if (!meetingPlatform) {
                    toastr.error("Please select a meeting platform.");
                    return;
                }

                if (!teamsMeetingLink) {
                    toastr.error("Teams meeting link is required for Online interviews.");
                    return;
                }

                try {
                    var parsedUrl = new URL(teamsMeetingLink);
                    if (parsedUrl.protocol !== 'http:' && parsedUrl.protocol !== 'https:') {
                        toastr.error("Please enter a valid Teams meeting URL starting with http:// or https://");
                        return;
                    }
                } catch (e) {
                    toastr.error("Please enter a valid Teams meeting URL.");
                    return;
                }
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

    $.post(base_url + 'admin/saveCandidateStage',{
        candidateId: $('#stageCandidateId').val(),
        stageId: $('#stageId').val(),
        action: $('#stageAction').val(),
        remarks: $('#stageRemarks').val(),

        followupType: $('#followupType').val(),
        nextFollowupDate: $('#nextFollowupDate').val(),

        interviewDate: $('#interviewDate').val(),
        interviewLevel: $('#interviewLevel').val(),
        interviewType: $('#interviewType').val(),
        interviewerId: $('#interviewerId').val(),
        meetingPlatform: $('#meetingPlatform').val(),
        teamsMeetingLink: $.trim($('#teamsMeetingLink').val())
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
    $.post(base_url + 'admin/getJobDetails', {jid: jid}, function (res) {
        let d = JSON.parse(res);
        let html = `<div class="container-fluid">`;
        html += `<div class="card card-primary collapsed-card"><div class="card-header bg-primary"><h3 class="card-title">Basic Information</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><div class="row"><div class="col-md-6"><p><b>Job Code:</b> ${d.JobCode}</p><p><b>Job Title:</b> ${d.JobTitle}</p><p><b>Department:</b> ${d.Departmentname}</p><p><b>Role:</b> ${d.RoleSummary}</p><p><b>Status:</b> ${d.JobStatus}</p></div><div class="col-md-6"><p><b>Posted By:</b> ${d.PostedByName}</p><p><b>Posted On:</b> ${d.PostedOn}</p><p><b>Expiry Date:</b> ${d.ExpiryDate}</p><p><b>Work Mode:</b> ${d.WorkMode}</p><p><b>Employment:</b> ${d.EmploymentType}</p><p><b>Language:</b> ${d.CommunicationLang}</p></div></div></div></div>`;
        html += `<div class="card card-info collapsed-card"><div class="card-header bg-info"><h3 class="card-title">Salary & Experience</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><div class="row"><div class="col-md-6"><p><b>Experience Required:</b> ${parseInt(d.ExpMin) || 0} - ${parseInt(d.ExpMax) || 0} Years</p></div><div class="col-md-6"><p><b>Salary Required:</b> ${d.SalMin ?? 0} - ${d.SalMax ?? 0} LPA</p></div></div></div></div>`;        html += `<div class="card card-secondary collapsed-card"><div class="card-header bg-secondary"><h3 class="card-title">Location & Education</h3><div class="card-tools"><button class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div></div><div class="card-body"><p><b>Job Location:</b> ${d.JobLocation}</p><p><b>Education Required:</b> ${d.EducationRequired}</p></div></div>`;
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
 $('.onlineOnly').hide();
 $('#teamsMeetingLink').val('');
 $('#interviewType').val('');

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

$(document).on('change', '#interviewType', function() {
    let mode = $(this).val();
    if (mode === 'Online') {
        $('.onlineOnly').slideDown();
    } else {
        $('.onlineOnly').slideUp();
        $('#teamsMeetingLink').val('');
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
// NOTE: filterPill click handler is handled in the combined filter block below (with source support)
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

    $.post(base_url + 'admin/saveOnboarding', {
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

    $.post(base_url + 'admin/saveOffer',{
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

    $.post(base_url + 'admin/saveHiring', {

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
            
            <!-- CANDIDATE PROFILE LAYER -->
            <div class="card card-outline card-info mb-4">
                <div class="card-header bg-light">
                    <h3 class="card-title text-info font-weight-bold mb-0">
                        <i class="fas fa-user mr-2"></i>CANDIDATE PROFILE OVERVIEW
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

            <!-- ATS JOB MATCH EVALUATION -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-header bg-light">
                    <h3 class="card-title text-primary font-weight-bold mb-0">
                        <i class="fas fa-tasks mr-2"></i>ATS VACANCY MATCH EVALUATION
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
window.initCandidateDataTable = function() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
        $('#example1').DataTable().destroy();
    }
    if ($.fn.DataTable) {
        $('#example1').DataTable({
            "responsive": false,
            "autoWidth": false,
            "columnDefs": [
                { "orderable": false, "targets": [8, 9] }
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

  
    var currentSourceFilter = $("#sourceSelectFilter").val() || $("#candidateListContainer").data("selected-source") || "all";
    var currentStatusFilter = $('#statusSelectFilter').val() || "";

    function executeCombinedCandidateFilter() {
        let jid = ($("#candidateListContainer").data("jid") || "");

        $.ajax({
            url: base_url + 'admin/filterCandidates',
            type: 'POST',
            data: { 
                status: currentStatusFilter, 
                source: currentSourceFilter,
                jid: jid 
            },
            success: function (res) {
                // Always safely destroy DataTable first
                try {
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
                        $('#example1').DataTable().destroy();
                    }
                } catch (e) {}

                // Replace table body content
                $('#example1 tbody').html(res);

                // Only re-init DataTable when there are real data rows (not the "no data" message)
                // The "no data" row has a colspan attribute; real rows do not
                var hasRealData = ($('#example1 tbody tr').length > 0) &&
                                  ($('#example1 tbody tr:first td[colspan]').length === 0);

                if (hasRealData && typeof window.initCandidateDataTable === 'function') {
                    window.initCandidateDataTable();
                }
            },
            error: function (xhr) {
                console.log('ERROR:', xhr.responseText);
            }
        });
    }

    $(document).on('change', '#sourceSelectFilter', function () {
        currentSourceFilter = $(this).val();
        executeCombinedCandidateFilter();
    });

    $(document).on('change', '#statusSelectFilter', function () {
        currentStatusFilter = $(this).val();
        executeCombinedCandidateFilter();
    });

    $(document).on('click', '#btnResetCandidateFilters', function () {
        // Reload the page cleanly to restore all candidates (avoids JobApplications join mismatch)
        var currentUrl = window.location.pathname;
        window.location.href = currentUrl;
    });
});
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
        var vacancyId = $(this).data('vacancy-id') || ($("#candidateListContainer").data("jid") || "");

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
            url: base_url + 'admin/compareCandidates',
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
                var resBtn = c.resume_path ? '<a href="' + base_url + c.resume_path + '" target="_blank" class="btn btn-xs btn-outline-warning font-weight-bold mb-1"><i class="fas fa-file-pdf mr-1"></i> Resume</a>' : '';
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
}

/* ==========================================================================
   Candidate Portal: Application & Feedback Handlers
   ========================================================================== */
$(function() {
  // ===== Candidate Application Form & Resume Upload Handler =====
  if ($('#candidateApplyForm').length || $('#dragDropZone').length) {
    var currentFileToken = '';
    var currentOriginalName = '';
    var currentFileSize = '';

    // Skills Chips Array Controller
    var skillsList = [];

    function escapeHtml(str) {
      return $('<div>').text(str).html();
    }

    function renderSkillsChips() {
      $('#skillsChipWrapper .skill-chip').remove();

      $.each(skillsList, function(index, skill) {
        var chipHtml =
          '<span class="skill-chip" data-index="' + index + '">' +
            '<span>' + escapeHtml(skill) + '</span>' +
            '<button type="button" class="btn-remove-chip" data-index="' + index + '" title="Remove skill">&times;</button>' +
          '</span>';
        $('#skill_input').before(chipHtml);
      });

      $('#skills_hidden').val(skillsList.join(', '));
    }

    function addSkill(skillName) {
      if (!skillName) return;
      var trimmed = $.trim(skillName);
      if (trimmed === '') return;

      var lowerTrimmed = trimmed.toLowerCase();
      var exists = false;
      $.each(skillsList, function(i, val) {
        if (val.toLowerCase() === lowerTrimmed) {
          exists = true;
          return false;
        }
      });

      if (!exists) {
        skillsList.push(trimmed);
        renderSkillsChips();
      }
    }

    function removeSkill(index) {
      if (index >= 0 && index < skillsList.length) {
        skillsList.splice(index, 1);
        renderSkillsChips();
      }
    }

    function setSkillsFromString(skillsStr) {
      skillsList = [];
      if (skillsStr) {
        var parts = skillsStr.split(/[,;\n]+/);
        $.each(parts, function(i, part) {
          var trimmed = $.trim(part);
          if (trimmed !== '') {
            addSkill(trimmed);
          }
        });
      }
      renderSkillsChips();
    }

    // Skills input keydown handler (Enter or Comma)
    $('#skill_input').on('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ',' || e.keyCode === 13 || e.keyCode === 188) {
        e.preventDefault();
        var val = $(this).val();
        addSkill(val);
        $(this).val('');
      } else if (e.key === 'Backspace' && $(this).val() === '' && skillsList.length > 0) {
        skillsList.pop();
        renderSkillsChips();
      }
    });

    $('#skill_input').on('blur', function() {
      var val = $(this).val();
      if (val && $.trim(val) !== '') {
        addSkill(val);
        $(this).val('');
      }
      $('#skillsChipWrapper').removeClass('focus');
    });

    $('#skill_input').on('focus', function() {
      $('#skillsChipWrapper').addClass('focus');
    });

    $('#skillsChipWrapper').on('click', function(e) {
      if ($(e.target).is('#skillsChipWrapper')) {
        $('#skill_input').focus();
      }
    });

    $(document).on('click', '.btn-remove-chip', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var idx = $(this).data('index');
      removeSkill(idx);
    });

    // Base URLs from form data attributes
    var $applyForm = $('#candidateApplyForm');
    var uploadUrl = $applyForm.data('upload-url') || (typeof base_url !== 'undefined' ? base_url + 'recruitment/user/upload_resume' : '/recruitment/user/upload_resume');
    var parseUrl  = $applyForm.data('parse-url')  || (typeof base_url !== 'undefined' ? base_url + 'recruitment/user/parse_resume' : '/recruitment/user/parse_resume');
    var submitUrl = $applyForm.data('submit-url') || (typeof base_url !== 'undefined' ? base_url + 'recruitment/user/submitApplication' : '/recruitment/user/submitApplication');

    // Initialize Select2 for Position dropdown
    if ($.fn.select2) {
      $('#position').select2({
        theme: 'bootstrap4',
        placeholder: '-- Select Open Position --',
        allowClear: true,
        width: 'resolve'
      });
    }

    // Handle Drag and Drop events
    var dragZone = $('#dragDropZone');

    dragZone.on('dragover dragenter', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).addClass('dragover');
    });

    dragZone.on('dragleave dragend drop', function(e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).removeClass('dragover');
    });

    dragZone.on('drop', function(e) {
      var files = e.originalEvent.dataTransfer.files;
      if (files.length > 0) {
        handleFileUpload(files[0]);
      }
    });

    // Handle Browse button click
    $('#btnChooseResume, #dragDropZone').on('click', function(e) {
      if (e.target.id === 'btnChooseResume' || e.target.id === 'dragDropZone' || $(e.target).closest('#btnChooseResume').length) {
        $('#resumeFileInput').trigger('click');
      }
    });

    $('#resumeFileInput').on('change', function() {
      if (this.files.length > 0) {
        handleFileUpload(this.files[0]);
      }
    });

    // Function: Handle Resume File Upload (AJAX)
    function handleFileUpload(file) {
      var fileName = file.name;
      var ext = fileName.split('.').pop().toLowerCase();
      if (['pdf', 'doc', 'docx'].indexOf(ext) === -1) {
        if (typeof toastr !== 'undefined') {
          toastr.error('Invalid file format. Please upload a PDF, DOC, or DOCX resume.');
        }
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        if (typeof toastr !== 'undefined') {
          toastr.error('File size exceeds the 5MB limit.');
        }
        return;
      }

      $('#dragDropZone').addClass('d-none');
      $('#uploadSpinner').removeClass('d-none');

      var formData = new FormData();
      formData.append('resume_file', file);

      // CSRF token if present
      if ($applyForm.find('input[type="hidden"]').length) {
        $applyForm.find('input[type="hidden"]').each(function() {
          if ($(this).attr('name') && $(this).attr('name') !== 'resume_file_token') {
            formData.append($(this).attr('name'), $(this).val());
          }
        });
      }

      $.ajax({
        url: uploadUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
          $('#uploadSpinner').addClass('d-none');

          if (res.status === 'success') {
            currentFileToken    = res.file_token;
            currentOriginalName = res.original_name;
            currentFileSize     = res.file_size;

            $('#resume_file_token').val(currentFileToken);

            var iconClass = (ext === 'pdf') ? 'fa-file-pdf text-danger' : 'fa-file-word text-primary';
            $('#uploadedFileSummary').html(
              '<i class="fas ' + iconClass + ' fa-lg mr-2"></i>' +
              '<span>' + res.original_name + '</span> ' +
              '<span class="badge badge-secondary ml-2">' + res.file_size + '</span>'
            );

            $('#attachedResumeDisplayName').text(res.original_name);
            $('#attachedResumeDisplaySize').text(res.file_size);

            $('#step1UploadSection').addClass('d-none');
            $('#step2ChoiceSection').removeClass('d-none');

            if (typeof toastr !== 'undefined') {
              toastr.success('Resume uploaded successfully.');
            }
          } else {
            $('#dragDropZone').removeClass('d-none');
            if (typeof toastr !== 'undefined') {
              toastr.error(res.message || 'Failed to upload resume.');
            }
          }
        },
        error: function() {
          $('#uploadSpinner').addClass('d-none');
          $('#dragDropZone').removeClass('d-none');
          if (typeof toastr !== 'undefined') {
            toastr.error('An error occurred while uploading. Please try again.');
          }
        }
      });
    }

    // Remove / Change Resume (Step 2)
    $('#btnRemoveResume, #btnChangeAttachedResume').on('click', function() {
      currentFileToken    = '';
      currentOriginalName = '';
      currentFileSize     = '';
      $('#resume_file_token').val('');
      $('#resumeFileInput').val('');
      setSkillsFromString('');

      $('#step2ChoiceSection').addClass('d-none');
      $('#step3FormSection').addClass('d-none');
      $('#step1UploadSection').removeClass('d-none');
      $('#dragDropZone').removeClass('d-none');
    });

    // Action 1: "Yes, Auto-fill" (Step 2 -> Step 3A)
    $('#btnAutoFill').on('click', function() {
      if (!currentFileToken) {
        if (typeof toastr !== 'undefined') {
          toastr.error('Resume session missing. Please re-upload your resume.');
        }
        return;
      }

      var btn = $(this);
      var originalHtml = btn.html();
      btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Extracting details...').prop('disabled', true);

      var postData = { file_token: currentFileToken };
      $applyForm.find('input[type="hidden"]').each(function() {
        if ($(this).attr('name') && $(this).attr('name') !== 'resume_file_token') {
          postData[$(this).attr('name')] = $(this).val();
        }
      });

      $.ajax({
        url: parseUrl,
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function(res) {
          btn.html(originalHtml).prop('disabled', false);

          if (res.status === 'success' && res.data) {
            var d = res.data;

            if (d.full_name) $('#full_name').val(d.full_name);
            if (d.email) $('#email').val(d.email);
            if (d.phone) $('#phone').val(d.phone);
            if (d.experience_years !== '' && d.experience_years !== null) $('#experience_years').val(d.experience_years);

            // Auto-fill skills into interactive chips!
            if (d.skills) {
              setSkillsFromString(d.skills);
            } else {
              setSkillsFromString('');
            }

            // Show Auto-fill Indication Alert
            $('#autoFillNoticeAlert').removeClass('d-none');

            // Transition to Step 3
            $('#step2ChoiceSection').addClass('d-none');
            $('#step3FormSection').removeClass('d-none');

            if (typeof toastr !== 'undefined') {
              toastr.info('Details extracted from resume. Please review before submitting.');
            }
          } else {
            if (typeof toastr !== 'undefined') {
              toastr.warning(res.message || 'Could not auto-fill all details. Please complete the form manually.');
            }

            setSkillsFromString('');
            $('#autoFillNoticeAlert').addClass('d-none');
            $('#step2ChoiceSection').addClass('d-none');
            $('#step3FormSection').removeClass('d-none');
          }
        },
        error: function() {
          btn.html(originalHtml).prop('disabled', false);
          if (typeof toastr !== 'undefined') {
            toastr.warning('Resume parsing service unavailable. You can enter details manually.');
          }

          setSkillsFromString('');
          $('#autoFillNoticeAlert').addClass('d-none');
          $('#step2ChoiceSection').addClass('d-none');
          $('#step3FormSection').removeClass('d-none');
        }
      });
    });

    // Action 2: "Enter Details Manually" (Step 2 -> Step 3B)
    $('#btnManualEntry').on('click', function() {
      $('#full_name, #email, #phone, #experience_years, #cover_letter').val('');
      setSkillsFromString('');

      $('#autoFillNoticeAlert').addClass('d-none');
      $('#step2ChoiceSection').addClass('d-none');
      $('#step3FormSection').removeClass('d-none');
    });

    // Back button (Step 3 -> Step 2)
    $('#btnBackToChoice').on('click', function() {
      $('#step3FormSection').addClass('d-none');
      $('#step2ChoiceSection').removeClass('d-none');
    });

    // ===== "Apply for Another Position" / "Apply for Different Position" buttons =====
    $('#btnApplyAnother, #btnApplyDifferent').on('click', function() {
      // Reset everything and go back to Step 1
      currentFileToken    = '';
      currentOriginalName = '';
      currentFileSize     = '';
      $('#resume_file_token').val('');
      $('#resumeFileInput').val('');
      $('#full_name, #email, #phone, #experience_years, #cover_letter').val('');
      setSkillsFromString('');
      if ($.fn.select2) { $('#position').val(null).trigger('change'); }

      $('#stepSuccessSection, #stepAlreadyAppliedSection, #step2ChoiceSection, #step3FormSection').addClass('d-none');
      $('#step1UploadSection').removeClass('d-none');
      $('#dragDropZone').removeClass('d-none');
      $('#uploadSpinner').addClass('d-none');
      $('#candidateApplyForm').removeClass('was-validated');
      $('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i> Submit Application');
    });

    // ===== Final Form Submission Handler (AJAX) =====
    $('#candidateApplyForm').on('submit', function(e) {
      e.preventDefault();

      var form = this;

      // Client-side HTML5 validation
      if (form.checkValidity() === false) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        if (typeof toastr !== 'undefined') {
          toastr.error('Please fill in all required fields marked with *');
        }
        return false;
      }
      $(form).addClass('was-validated');

      // Ensure resume token is present
      if (!currentFileToken) {
        if (typeof toastr !== 'undefined') {
          toastr.error('Resume file is missing. Please re-upload your resume.');
        }
        return false;
      }

      var $submitBtn = $('button[type="submit"]', form);
      var originalBtnHtml = $submitBtn.html();
      $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...');

      // Collect form data
      var formData = new FormData(form);
      formData.set('resume_file_token', currentFileToken);

      $.ajax({
        url:         submitUrl,
        type:        'POST',
        data:        formData,
        processData: false,
        contentType: false,
        dataType:    'json',
        success: function(res) {
          $submitBtn.prop('disabled', false).html(originalBtnHtml);

          if (res.status === 'success') {
            // Populate success card
            $('#successAppId').text(res.application_id   || '—');
            $('#successJobTitle').text(res.job_title      || '—');
            $('#successStatus').text(res.current_status   || 'CV Uploaded');
            $('#successAppliedOn').text(res.applied_on    || new Date().toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'}));

            if (res.redirect_url) {
              $('#btnGoToFeedback').attr('href', res.redirect_url).removeClass('d-none');
            }

            // Show success card, hide form
            $('#step3FormSection').addClass('d-none');
            $('#stepSuccessSection').removeClass('d-none');

            // Scroll to top of card
            $('html, body').animate({ scrollTop: $('.card-apply').offset().top - 80 }, 400);

            if (typeof toastr !== 'undefined') {
              toastr.success('Application submitted successfully!');
            }

            if (res.redirect_url) {
              setTimeout(function() {
                window.location.href = res.redirect_url;
              }, 1800);
            }

          } else if (res.status === 'already_applied') {
            // Populate already-applied card
            $('#dupAppId').text(res.application_id   || '—');
            $('#dupJobTitle').text(res.job_title      || '—');
            $('#dupStatus').text(res.current_status   || 'Under Review');
            $('#dupAppliedOn').text(res.applied_on    || '—');

            if (res.redirect_url) {
              $('#btnDupGoToFeedback').attr('href', res.redirect_url).removeClass('d-none');
            }

            // Show already-applied card, hide form
            $('#step3FormSection').addClass('d-none');
            $('#stepAlreadyAppliedSection').removeClass('d-none');

            $('html, body').animate({ scrollTop: $('.card-apply').offset().top - 80 }, 400);

            if (typeof toastr !== 'undefined') {
              toastr.warning('You have already applied for this position.');
            }

          } else {
            // General error
            if (typeof toastr !== 'undefined') {
              toastr.error(res.message || 'Submission failed. Please try again.');
            }
          }
        },
        error: function(xhr) {
          $submitBtn.prop('disabled', false).html(originalBtnHtml);
          var errMsg = 'An unexpected error occurred. Please try again.';
          try {
            var resp = JSON.parse(xhr.responseText);
            if (resp && resp.message) errMsg = resp.message;
          } catch(ex) {}
          if (typeof toastr !== 'undefined') {
            toastr.error(errMsg);
          }
        }
      });
    });
  }

  // ===== Candidate Feedback Submission Handler =====
  if ($('#candidateFeedbackForm').length) {
    var $fbForm = $('#candidateFeedbackForm');
    var submitFeedbackUrl = $fbForm.data('submit-url') || (typeof base_url !== 'undefined' ? base_url + 'recruitment/user/submit_feedback' : '/recruitment/user/submit_feedback');

    $fbForm.on('submit', function(e) {
      e.preventDefault();

      var form = this;
      var $btn = $('#btnSubmitFeedback');
      var originalHtml = $btn.html();

      $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...');

      var formData = $(form).serialize();

      $.ajax({
        url: submitFeedbackUrl,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
          $btn.prop('disabled', false).html(originalHtml);

          if (res.status === 'success') {
            $('#feedbackSubmittedAtText').text(res.submitted_at || new Date().toLocaleString());
            $('#feedbackFormCard').addClass('d-none');
            $('#feedbackSubmittedSuccessCard').removeClass('d-none');

            if (typeof toastr !== 'undefined') {
              toastr.success('Thank you! Your feedback has been recorded.');
            }
          } else if (res.status === 'already_submitted') {
            $('#feedbackSubmittedAtText').text(res.submitted_at || '');
            $('#feedbackFormCard').addClass('d-none');
            $('#feedbackSubmittedSuccessCard').removeClass('d-none');

            if (typeof toastr !== 'undefined') {
              toastr.info('Your feedback has already been recorded.');
            }
          } else {
            if (typeof toastr !== 'undefined') {
              toastr.error(res.message || 'Failed to submit feedback.');
            }
          }
        },
        error: function() {
          $btn.prop('disabled', false).html(originalHtml);
          if (typeof toastr !== 'undefined') {
            toastr.error('An unexpected error occurred. Please try again.');
          }
        }
      });
    });
  }

  // ===== OTP Verification & Resend Handler (customscript.js) =====
  if ($('#otpContainer').length || $('#otpForm').length || $('#otpInput').length) {
    (function() {
      var $container = $('#otpContainer');
      var $form = $('#otpForm');
      var $otpInput = $('#otpInput');
      var $countdownEl = $('#countdownDisplay');
      var $timerBadge = $('#timerBadge');
      var $timerDescription = $('#timerDescription');
      var $timerIcon = $('#timerIcon');
      var $btnResend = $('#btnResend');
      var $dynamicAlert = $('#dynamicAlert');

      var resendUrl = $btnResend.data('resend-url') || ($container.length ? $container.data('resend-url') : '') || (typeof site_url !== 'undefined' ? site_url('admin/ResendOtp') : '/rec/admin/ResendOtp');
      var initialSeconds = $container.length && $container.data('remaining') !== undefined ? parseInt($container.data('remaining'), 10) : ($countdownEl.length ? parseInt($countdownEl.text(), 10) : 60);
      if (isNaN(initialSeconds) || initialSeconds < 0) {
        initialSeconds = 60;
      }

      var remainingSeconds = initialSeconds;
      var timerInterval = null;

      // Numeric only and max 6 digits input restriction
      $otpInput.on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
      });

      function updateTimerUI() {
        if (remainingSeconds > 0) {
          var formatted = remainingSeconds < 10 ? '0' + remainingSeconds : remainingSeconds;
          $countdownEl.text(formatted);
          $timerBadge.removeClass('expired');
          $timerDescription.text('OTP expires in ' + formatted + ' seconds');
          $timerIcon.attr('class', 'fas fa-clock');
          $btnResend.prop('disabled', true);
        } else {
          $countdownEl.text('00');
          $timerBadge.addClass('expired');
          $timerDescription.text('OTP has expired. Please request a new OTP.');
          $timerIcon.attr('class', 'fas fa-triangle-exclamation text-danger');
          $btnResend.prop('disabled', false);

          // When expired, show red expiry alert and clear prior success
          if ($dynamicAlert.length) {
            $dynamicAlert.attr('class', 'alert alert-danger')
              .html('<i class="fas fa-circle-exclamation"></i> <span>OTP has expired. Please request a new OTP.</span>')
              .show();
          }
        }
      }

      function startCountdown(seconds) {
        if (timerInterval) {
          clearInterval(timerInterval);
          timerInterval = null;
        }
        remainingSeconds = parseInt(seconds, 10);
        if (isNaN(remainingSeconds) || remainingSeconds < 0) {
          remainingSeconds = 0;
        }

        updateTimerUI();

        if (remainingSeconds > 0) {
          timerInterval = setInterval(function() {
            if (remainingSeconds > 0) {
              remainingSeconds--;
              updateTimerUI();
            } else {
              clearInterval(timerInterval);
              timerInterval = null;
            }
          }, 1000);
        }
      }

      // Initialize countdown
      startCountdown(remainingSeconds);

      // Resend OTP Action Handler
      function executeResendOtp() {
        $btnResend.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

        $.ajax({
          url: resendUrl,
          type: 'POST',
          dataType: 'json',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          success: function(res) {
            $btnResend.html('<i class="fas fa-rotate-right"></i> Resend OTP');

            if (res && res.status === 'success') {
              // 1. Remove ALL previous error alerts (server-rendered and dynamic)
              $('.alert-danger').remove();
              $('#serverErrorAlert').remove();
              $('#serverSuccessAlert').remove();

              // 2. Display ONLY the new success alert
              if ($dynamicAlert.length) {
                $dynamicAlert.attr('class', 'alert alert-success')
                  .html('<i class="fas fa-circle-check"></i> <span>' + (res.message || 'A new OTP has been sent to your registered email address.') + '</span>')
                  .show();
              }

              // 3. Clear and focus OTP input
              $otpInput.val('').focus();

              // 4. Reset countdown timer back to 60 seconds
              startCountdown(res.remaining_seconds || 60);
            } else {
              if ($dynamicAlert.length) {
                $dynamicAlert.attr('class', 'alert alert-danger')
                  .html('<i class="fas fa-circle-exclamation"></i> <span>' + (res && res.message ? res.message : 'Failed to resend OTP.') + '</span>')
                  .show();
              }
              $btnResend.prop('disabled', false);
            }
          },
          error: function() {
            $btnResend.html('<i class="fas fa-rotate-right"></i> Resend OTP');
            window.location.href = resendUrl;
          }
        });
      }

      $btnResend.on('click', function(e) {
        e.preventDefault();
        executeResendOtp();
      });

      window.triggerResendOtp = executeResendOtp;
    })();
  }
});


/* =============================================================================
 * SECTION: Requested Resources Page
 * View: application/modules/admin/views/RequestedResources.php
 * PHP base_url("admin/...") calls → replaced with the global JS `base_url`
 * variable (set in bo_template.php as: var base_url = "<?= base_url(); ?>";)
 * ============================================================================= */

// ---------- Global stepper state (needed by onclick attributes in the HTML) ----------
var resStepperObj = null;
var currentResStep = 1;

// ---------- Step navigation (called from onclick in the HTML) ----------
function goToResStep(stepNum) {
    stepNum = parseInt(stepNum) || 1;
    if (stepNum < 1) stepNum = 1;
    if (stepNum > 3) stepNum = 3;
    currentResStep = stepNum;

    var targets = { 1: '#res-job-part', 2: '#res-salary-part', 3: '#res-desc-part' };
    var targetId = targets[stepNum];

    $('#requestResourcePanel .bs-stepper-header .step').removeClass('active');
    $('#requestResourcePanel .bs-stepper-header .step[data-target="' + targetId + '"]').addClass('active');
    $('#requestResourcePanel .bs-stepper-content .content').removeClass('active').hide();
    $(targetId).addClass('active').fadeIn(150);

    if (window.resStepperObj) {
        try { window.resStepperObj.to(stepNum); } catch (e) {}
    }
}
function resStepperNext() { goToResStep(currentResStep + 1); }
function resStepperPrev() { goToResStep(currentResStep - 1); }

// ---------- View request details modal (called from onclick in the HTML) ----------
function viewRequestDetails(req) {
    var statusClass = 'badge-warning', statusIcon = 'fa-clock';
    var statusText = req.Status || 'PENDING APPROVAL';
    if (statusText === 'ACCEPTED' || statusText === 'APPROVED') { statusClass = 'badge-success'; statusIcon = 'fa-check-circle'; }
    else if (statusText === 'REJECTED') { statusClass = 'badge-danger'; statusIcon = 'fa-times-circle'; }

    function makeChips(str, colorClass) {
        if (!str || str.trim() === '-' || str.trim() === '') return '<span class="text-muted small">None specified</span>';
        return str.split(',').map(function(s) {
            return '<span class="badge badge-pill ' + colorClass + ' mr-1 mb-1 px-3 py-1 font-weight-normal" style="font-size:12px;">' + s.trim() + '</span>';
        }).join(' ');
    }
    function formatExpectedSalary(min, max) {
        var minVal = (min !== null && min !== undefined && min !== '' && !isNaN(min)) ? parseFloat(min) : null;
        var maxVal = (max !== null && max !== undefined && max !== '' && !isNaN(max)) ? parseFloat(max) : null;
        if (minVal !== null && maxVal !== null) return '₹' + minVal + ' LPA - ₹' + maxVal + ' LPA';
        if (minVal !== null) return 'Min ₹' + minVal + ' LPA';
        if (maxVal !== null) return 'Max ₹' + maxVal + ' LPA';
        return '-';
    }
    function formatProjectDate(dInput) {
        if (!dInput || dInput === '0000-00-00' || dInput === '0000-00-00 00:00:00') return '-';
        var d = new Date(dInput);
        if (isNaN(d.getTime())) return dInput;
        return String(d.getDate()).padStart(2,'0') + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + d.getFullYear();
    }

    var mustSkills = makeChips(req.MustHaveSkills, 'badge-success');
    var niceSkills = makeChips(req.NiceToHaveSkills, 'badge-info');
    var languages  = makeChips(req.CommunicationLang, 'badge-primary');
    var targetDateStr = req.TargetOnboardingDate ? formatProjectDate(req.TargetOnboardingDate) : '-';
    var reqDateStr = req.CreatedAt ? formatProjectDate(req.CreatedAt) : '-';

    var html = '<div class="card border-0 shadow-none mb-0">' +
        '<div class="p-3 mb-3 rounded-lg" style="background:linear-gradient(135deg,#f8fafc 0%,#edf2f7 100%);border-left:5px solid #0d9488;">' +
        '<div class="row align-items-center">' +
        '<div class="col-md-8"><span class="badge badge-secondary px-2 py-1 small font-weight-bold mb-1"><i class="fas fa-hashtag mr-1"></i>' + (req.RequestCode||'REQ') + '</span>' +
        '<h4 class="mb-0 font-weight-bold text-dark">' + (req.JobTitle||'N/A') + '</h4>' +
        '<div class="text-muted small mt-1"><span class="mr-3"><i class="fas fa-building text-secondary mr-1"></i>' + (req.Departmentname||'N/A') + '</span>' +
        '<span class="mr-3"><i class="fas fa-map-marker-alt text-danger mr-1"></i>' + (req.JobLocation||'N/A') + '</span>' +
        '<span><i class="fas fa-briefcase text-info mr-1"></i>' + (req.PositionType||'New Position') + '</span></div></div>' +
        '<div class="col-md-4 text-md-right mt-2 mt-md-0"><span class="badge ' + statusClass + ' px-3 py-2 font-weight-bold" style="font-size:13px;border-radius:20px;"><i class="fas ' + statusIcon + ' mr-1"></i>' + statusText + '</span></div>' +
        '</div></div>' +
        '<div class="row text-center mb-3">' +
        '<div class="col-6 col-md-3 mb-2"><div class="p-2 border rounded bg-white shadow-sm"><small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Positions</small><span class="font-weight-bold text-dark h6 mb-0">' + (req.NoofOpenings||1) + '</span></div></div>' +
        '<div class="col-6 col-md-3 mb-2"><div class="p-2 border rounded bg-white shadow-sm"><small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Experience</small><span class="font-weight-bold text-dark h6 mb-0">' + (req.ExpMin||0) + ' - ' + (req.ExpMax||0) + ' Yrs</span></div></div>' +
        '<div class="col-6 col-md-3 mb-2"><div class="p-2 border rounded bg-white shadow-sm"><small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Target Onboarding</small><span class="font-weight-bold text-teal h6 mb-0">' + targetDateStr + '</span></div></div>' +
        '<div class="col-6 col-md-3 mb-2"><div class="p-2 border rounded bg-white shadow-sm"><small class="text-muted font-weight-bold d-block text-uppercase" style="font-size:10px;">Request Date</small><span class="font-weight-bold text-dark h6 mb-0">' + reqDateStr + '</span></div></div>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6 mb-3"><div class="card h-100 border-light shadow-sm"><div class="card-header bg-light py-2"><h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-list-ul mr-1 text-teal"></i>Requirements &amp; Stakeholders</h6></div>' +
        '<div class="card-body p-3"><table class="table table-sm table-borderless mb-0 small">' +
        '<tr><th class="text-muted pl-0" style="width:42%">Functional Role:</th><td class="font-weight-bold text-dark">' + (req.FunctionalRole||'-') + '</td></tr>' +
        '<tr><th class="text-muted pl-0">Education Required:</th><td class="text-dark">' + (req.EducationRequired||'-') + '</td></tr>' +
        '<tr><th class="text-muted pl-0">CTC / Budget:</th><td class="text-dark">' + (req.Salary||'-') + '</td></tr>' +
        '<tr><th class="text-muted pl-0">Expected Salary Range:</th><td class="font-weight-bold text-dark">' + formatExpectedSalary(req.ExpectedSalaryMin, req.ExpectedSalaryMax) + '</td></tr>' +
        '<tr><th class="text-muted pl-0">Reason for Request:</th><td class="text-dark">' + (req.ReasonForRequirement||'-') + '</td></tr>' +
        '<tr class="border-top"><th class="text-muted pl-0 pt-2">Requested By:</th><td class="font-weight-bold text-dark pt-2">' + (req.RequestedByName||'-') + '</td></tr>' +
        '<tr><th class="text-muted pl-0">Approver:</th><td class="font-weight-bold text-dark">' + (req.ApproverName||'-') + '</td></tr>' +
        '</table></div></div></div>' +
        '<div class="col-md-6 mb-3"><div class="card h-100 border-light shadow-sm"><div class="card-header bg-light py-2"><h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-tags mr-1 text-teal"></i>Skills &amp; Languages</h6></div>' +
        '<div class="card-body p-3"><div class="mb-3"><small class="text-muted font-weight-bold d-block mb-1">Must-Have Skills:</small><div>' + mustSkills + '</div></div>' +
        '<div class="mb-3"><small class="text-muted font-weight-bold d-block mb-1">Nice-to-Have Skills:</small><div>' + niceSkills + '</div></div>' +
        '<div><small class="text-muted font-weight-bold d-block mb-1">Communication Languages:</small><div>' + languages + '</div></div>' +
        '</div></div></div></div>' +
        (req.JobDescription ? '<div class="card border-light shadow-sm mb-3"><div class="card-header bg-light py-2"><h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-align-left mr-1 text-teal"></i>Job Description</h6></div><div class="card-body p-3 small text-dark" style="white-space:pre-wrap;line-height:1.6;background-color:#fafafa;border-radius:0 0 8px 8px;">' + req.JobDescription + '</div></div>' : '') +
        (req.Responsibilities ? '<div class="card border-light shadow-sm mb-3"><div class="card-header bg-light py-2"><h6 class="mb-0 font-weight-bold text-secondary small"><i class="fas fa-tasks mr-1 text-teal"></i>Roles &amp; Responsibilities</h6></div><div class="card-body p-3 small text-dark" style="white-space:pre-wrap;line-height:1.6;background-color:#fafafa;border-radius:0 0 8px 8px;">' + req.Responsibilities + '</div></div>' : '') +
        (req.ApprovalComment ? '<div class="alert alert-warning border-0 shadow-sm p-3 mb-0" style="border-left:5px solid #f59e0b !important;border-radius:8px;"><h6 class="font-weight-bold mb-1 small text-dark"><i class="fas fa-comment-alt text-warning mr-1"></i>Approver Remark:</h6><p class="mb-1 small text-dark">' + req.ApprovalComment + '</p>' + (req.ActionedAt ? '<small class="text-muted"><i class="far fa-clock mr-1"></i>Actioned on: ' + req.ActionedAt + '</small>' : '') + '</div>' : '') +
        '</div>';

    $('#detailsModalContent').html(html);
    $('#viewDetailsModal').modal('show');
}

// ---------- Approval modal (called from onclick in the HTML) ----------
function openApprovalModal(requestId, status, requestCode) {
    var finalReqId = (requestId !== null && requestId !== undefined && requestId !== '') ? requestId : (requestCode || '');
    $('#approvalRequestId').val(finalReqId);
    $('#approvalRequestCode').val(requestCode || '');
    $('#approvalStatus').val(status || 'ACCEPTED');
    $('#approvalComment').val('');
    var header = $('#approvalModalHeader'), btn = $('#approvalSubmitBtn');
    if (status === 'ACCEPTED') {
        header.attr('class', 'modal-header bg-success text-white');
        $('#approvalModalTitle').text('Accept Resource Request [' + requestCode + ']');
        $('#approvalTargetText').html('You are about to <span class="text-success font-weight-bold">ACCEPT</span> request <code>' + requestCode + '</code>.');
        btn.attr('class', 'btn btn-success').html('<i class="fas fa-check mr-1"></i> Confirm Acceptance');
    } else {
        header.attr('class', 'modal-header bg-danger text-white');
        $('#approvalModalTitle').text('Reject Resource Request [' + requestCode + ']');
        $('#approvalTargetText').html('You are about to <span class="text-danger font-weight-bold">REJECT</span> request <code>' + requestCode + '</code>.');
        btn.attr('class', 'btn btn-danger').html('<i class="fas fa-times mr-1"></i> Confirm Rejection');
    }
    $('#approvalModal').modal('show');
}

// ---------- Approval submit (called via onsubmit="submitApproval(event)" on the form) ----------
function submitApproval(e) {
    e.preventDefault();
    var comment = $('#approvalComment').val().trim();
    if (!comment) { showAlert('Approval Comments are mandatory.', 'warning'); return; }
    $.ajax({
        url: base_url + 'admin/updateResourceRequestStatus',
        type: 'POST',
        data: $('#approvalForm').serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') { $('#approvalModal').modal('hide'); location.reload(); }
            else { showAlert(res.message || 'Error updating status', 'danger'); }
        },
        error: function(xhr) {
            var msg = 'Network or server error.';
            try { var r = JSON.parse(xhr.responseText); if (r.message) msg = r.message; } catch(e) {}
            showAlert(msg, 'danger');
        }
    });
}

// ---------- Panel open helpers (called from onclick in the HTML) ----------
function syncCcUI() {
    var chipsHtml = '';
    var selectedCount = 0;
    
    $('#resExtraCcUsers option').prop('selected', false);

    $('.cc-user-row').each(function() {
        var $row = $(this);
        var uid = $row.data('uid');
        var name = $row.data('name');
        var role = $row.data('role');
        var isDefault = $row.data('default') == '1';
        var isChecked = $row.find('.cc-user-chk').is(':checked');

        if (isChecked) {
            selectedCount++;
            $('#resExtraCcUsers option[value="' + uid + '"]').prop('selected', true);
            $row.css('background-color', '#f1f5f9');

            var badgeClass = isDefault ? 'badge-primary' : 'badge-success';
            var iconClass = isDefault ? 'fa-star' : 'fa-user-check';
            var defaultTag = isDefault ? ' (Default)' : '';

            chipsHtml += '<span class="badge ' + badgeClass + ' px-2 py-1 font-weight-normal shadow-sm d-inline-flex align-items-center mr-1 mb-1" style="font-size: 11.5px; border-radius: 6px;">' +
                '<i class="fas ' + iconClass + ' mr-1" style="font-size: 9px;"></i>' +
                '<strong>' + name + '</strong>&nbsp;<span style="opacity: 0.85;">(' + role + defaultTag + ')</span>' +
                '<span class="remove-cc-chip ml-2 font-weight-bold" data-uid="' + uid + '" style="cursor: pointer; font-size: 13px; opacity: 0.8;" title="Remove">&times;</span>' +
            '</span>';
        } else {
            $row.css('background-color', 'transparent');
        }
    });

    if (chipsHtml === '') {
        chipsHtml = '<span class="text-muted small italic p-1"><i class="fas fa-user-slash mr-1"></i>No CC recipients selected</span>';
    }

    $('#activeCcChipsContainer').html(chipsHtml);
    $('#selectedCcCountBadge').text(selectedCount + ' Recipient' + (selectedCount === 1 ? '' : 's'));
}

$(document).off('click', '.cc-user-row').on('click', '.cc-user-row', function(e) {
    if ($(e.target).is('input[type="checkbox"]') || $(e.target).is('label')) {
        return;
    }
    var $chk = $(this).find('.cc-user-chk');
    $chk.prop('checked', !$chk.is(':checked'));
    syncCcUI();
});

$(document).off('change', '.cc-user-chk').on('change', '.cc-user-chk', function() {
    syncCcUI();
});

$(document).off('click', '.remove-cc-chip').on('click', '.remove-cc-chip', function(e) {
    e.stopPropagation();
    var uid = $(this).data('uid');
    $('#cc_chk_' + uid).prop('checked', false);
    syncCcUI();
});

$(document).off('keyup', '#ccUserSearchInput').on('keyup', '#ccUserSearchInput', function() {
    var q = $(this).val().toLowerCase().trim();
    $('.cc-user-row').each(function() {
        var name = ($(this).data('name') || '').toLowerCase();
        var email = ($(this).data('email') || '').toLowerCase();
        var role = ($(this).data('role') || '').toLowerCase();
        if (name.indexOf(q) !== -1 || email.indexOf(q) !== -1 || role.indexOf(q) !== -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

function openCreateRequestModal() {
    $('#res_RequestId').val('0');
    $('#panelHeaderTitle').html('<i class="fas fa-user-plus mr-2"></i>Request Resource');
    $('#resSubmitBtn').prop('disabled', false).removeClass('disabled').html('<i class="fas fa-paper-plane mr-1"></i> Submit Request');
    if ($('#resourceRequestForm').length) { $('#resourceRequestForm').data('submitting', false); $('#resourceRequestForm')[0].reset(); }
    $('#res_ExpectedSalaryMin').val(''); $('#res_ExpectedSalaryMax').val('');
    $('.cc-user-row').each(function() {
        var isDefault = $(this).data('default') == '1';
        $(this).find('.cc-user-chk').prop('checked', isDefault);
    });
    if (typeof syncCcUI === 'function') { syncCcUI(); }
    preloadResChips('', 'resLocationChips', 'resJobLocation');
    preloadResChips('', 'resEducationChips', 'resEducationRequired');
    preloadResChips('', 'resMustHaveSkillsChips', 'resMustHaveSkills');
    preloadResChips('', 'resNiceToHaveSkillsChips', 'resNiceToHaveSkills');
    preloadResChips('', 'resLanguageChips', 'resCommunicationLang');
    goToResStep(1);
    $('#requestResourcePanel').addClass('open');
}

function openEditRequestModal(req) {
    $('#res_RequestId').val(req.RequestId || '0');
    $('#panelHeaderTitle').html('<i class="fas fa-edit mr-2"></i>Edit Resource Request [' + (req.RequestCode || '') + ']');
    $('#resSubmitBtn').prop('disabled', false).removeClass('disabled').html('<i class="fas fa-save mr-1"></i> Update Request');
    if ($('#resourceRequestForm').length) { $('#resourceRequestForm').data('submitting', false); }
    $('input[name="JobTitle"]').val(req.JobTitle || '');
    $('input[name="FunctionalRole"]').val(req.FunctionalRole || '');
    $('select[name="Did"]').val(req.Did || '');
    $('select[name="PositionType"]').val(req.PositionType || 'New Position');
    $('select[name="ApproverId"]').val(req.ApproverId || '');
    $('textarea[name="ReasonForRequirement"]').val(req.ReasonForRequirement || '');

    var extraArr = [];
    if (req.ExtraCcUsers) {
        try {
            if (typeof req.ExtraCcUsers === 'string') {
                extraArr = JSON.parse(req.ExtraCcUsers);
            } else if (Array.isArray(req.ExtraCcUsers)) {
                extraArr = req.ExtraCcUsers;
            }
        } catch(e) {
            if (typeof req.ExtraCcUsers === 'string') {
                extraArr = req.ExtraCcUsers.split(',').map(function(x) { return x.trim(); });
            }
        }
    }

    if (extraArr && extraArr.length > 0) {
        var strArr = extraArr.map(String);
        $('.cc-user-row').each(function() {
            var uid = String($(this).data('uid'));
            $(this).find('.cc-user-chk').prop('checked', strArr.indexOf(uid) !== -1);
        });
    } else {
        $('.cc-user-row').each(function() {
            var isDefault = $(this).data('default') == '1';
            $(this).find('.cc-user-chk').prop('checked', isDefault);
        });
    }
    if (typeof syncCcUI === 'function') { syncCcUI(); }

    $('input[name="ExpMin"]').val(req.ExpMin || 0);
    $('input[name="ExpMax"]').val(req.ExpMax || 0);
    $('input[name="ExpectedSalaryMin"]').val(req.ExpectedSalaryMin !== null && req.ExpectedSalaryMin !== undefined ? req.ExpectedSalaryMin : '');
    $('input[name="ExpectedSalaryMax"]').val(req.ExpectedSalaryMax !== null && req.ExpectedSalaryMax !== undefined ? req.ExpectedSalaryMax : '');
    $('input[name="RecruitmentStartDate"]').val((req.RecruitmentStartDate || '').split(' ')[0]);
    $('input[name="TargetOnboardingDate"]').val((req.TargetOnboardingDate || '').split(' ')[0]);
    $('input[name="NoofOpenings"]').val(req.NoofOpenings || 1);
    $('textarea[name="JobDescription"]').val(req.JobDescription || '');
    $('textarea[name="Responsibilities"]').val(req.Responsibilities || '');
    preloadResChips(req.JobLocation || '', 'resLocationChips', 'resJobLocation');
    preloadResChips(req.EducationRequired || '', 'resEducationChips', 'resEducationRequired');
    preloadResChips(req.MustHaveSkills || '', 'resMustHaveSkillsChips', 'resMustHaveSkills');
    preloadResChips(req.NiceToHaveSkills || '', 'resNiceToHaveSkillsChips', 'resNiceToHaveSkills');
    preloadResChips(req.CommunicationLang || '', 'resLanguageChips', 'resCommunicationLang');
    goToResStep(1);
    $('#requestResourcePanel').addClass('open');
}

// ---------- Chip utility helpers ----------
function addResChipDirect(value, inputId, chipsId, hiddenId) {
    value = (value || '').replace(/,/g, '').trim();
    if (!value) return;
    var chipsContainer = document.getElementById(chipsId);
    var hiddenInput = hiddenId ? document.getElementById(hiddenId) : null;
    var input = document.getElementById(inputId);
    if (!chipsContainer) return;
    function syncHidden() {
        if (!hiddenInput) return;
        hiddenInput.value = Array.prototype.slice.call(chipsContainer.querySelectorAll('.badge')).map(function(x){ return x.textContent.replace('×','').trim(); }).join(',');
    }
    var existing = Array.prototype.slice.call(chipsContainer.querySelectorAll('.badge')).map(function(x){ return x.textContent.replace('×','').trim(); });
    if (existing.indexOf(value) !== -1) { if (input) input.value = ''; return; }
    var chip = document.createElement('span');
    chip.className = inputId.toLowerCase().indexOf('musthave') !== -1 ? 'badge badge-pill badge-success mr-2 mb-2' : (inputId.toLowerCase().indexOf('nicetohave') !== -1 ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
    chip.style.cssText = 'font-size:13px;padding:6px 12px;display:inline-flex;align-items:center;';
    chip.innerHTML = value + ' <span style="cursor:pointer;margin-left:6px;font-weight:bold;font-size:14px;">\u00d7</span>';
    chip.querySelector('span').onclick = function(e) { e.stopPropagation(); chip.remove(); syncHidden(); };
    chipsContainer.appendChild(chip);
    if (input) input.value = '';
    var dd = document.getElementById(inputId.replace('Input','Dropdown'));
    if (dd) dd.style.display = 'none';
    syncHidden();
}

function preloadResChips(values, chipsId, hiddenId) {
    var chips = document.getElementById(chipsId);
    var hidden = hiddenId ? document.getElementById(hiddenId) : null;
    if (!chips) return;
    chips.innerHTML = '';
    if (!values) { if (hidden) hidden.value = ''; return; }
    var arr = values.split(',');
    arr.forEach(function(v) {
        v = v.trim(); if (!v) return;
        var chip = document.createElement('span');
        chip.className = chipsId.toLowerCase().indexOf('musthave') !== -1 ? 'badge badge-pill badge-success mr-2 mb-2' : (chipsId.toLowerCase().indexOf('nicetohave') !== -1 ? 'badge badge-pill badge-info mr-2 mb-2' : 'badge badge-pill badge-primary mr-2 mb-2');
        chip.style.cssText = 'font-size:13px;padding:6px 12px;display:inline-flex;align-items:center;';
        chip.innerHTML = v + ' <span style="cursor:pointer;margin-left:6px;font-weight:bold;font-size:14px;">\u00d7</span>';
        chip.querySelector('span').onclick = function(e) {
            e.stopPropagation(); chip.remove();
            if (hidden) { hidden.value = Array.prototype.slice.call(chips.querySelectorAll('.badge')).map(function(x){ return x.textContent.replace('×','').trim(); }).join(','); }
        };
        chips.appendChild(chip);
    });
    if (hidden) hidden.value = arr.join(',');
}

function initResChipAutocomplete(config) {
    var input = document.getElementById(config.inputId);
    var dropdown = document.getElementById(config.dropdownId);
    var chipsContainer = document.getElementById(config.chipsId);
    var hiddenInput = config.hiddenId ? document.getElementById(config.hiddenId) : null;
    if (!input || !dropdown || !chipsContainer) return;
    function syncHidden() {
        if (!hiddenInput) return;
        hiddenInput.value = Array.prototype.slice.call(chipsContainer.querySelectorAll('.badge')).map(function(x){ return x.textContent.replace('×','').trim(); }).join(',');
    }
    input.addEventListener('keyup', function(e) {
        if (e.key === ',' || e.keyCode === 188) {
            e.preventDefault();
            var value = this.value.replace(/,/g,'').trim();
            if (value.length >= 1) addResChipDirect(value, config.inputId, config.chipsId, config.hiddenId);
            return;
        }
        var q = this.value.trim();
        if (q.length < 2) { dropdown.style.display = 'none'; dropdown.innerHTML = ''; return; }
        fetch(config.url + '?q=' + encodeURIComponent(q))
            .then(function(res){ return res.json(); })
            .then(function(data) {
                dropdown.innerHTML = '';
                if (!data || !data.length) { dropdown.innerHTML = '<span class="dropdown-item disabled">No results</span>'; }
                else {
                    data.forEach(function(item) {
                        var value = item[config.key];
                        var el = document.createElement('a');
                        el.className = 'dropdown-item'; el.style.cursor = 'pointer'; el.textContent = value;
                        el.onclick = function(evt) { evt.preventDefault(); evt.stopPropagation(); addResChipDirect(value, config.inputId, config.chipsId, config.hiddenId); };
                        dropdown.appendChild(el);
                    });
                }
                dropdown.style.display = 'block';
            })
            .catch(function(){ dropdown.style.display = 'none'; });
    });
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) dropdown.style.display = 'none';
    });
}

// ---------- Page-specific initialisation — runs only on the RequestedResources page ----------
$(function() {
    if (!$('#requestsTable').length && !$('#requestResourcePanel').length) { return; }

    // DataTable
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#requestsTable')) {
        $('#requestsTable').DataTable({ 'responsive': false, 'autoWidth': false, 'order': [[0, 'asc']] });
    }
    $(window).on('resize orientationchange', function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#requestsTable')) { $('#requestsTable').DataTable().columns.adjust(); }
    });

    // Panel toggle
    $('#openRequestResourcePanel').on('click', function() { openCreateRequestModal(); });
    $('#closeRequestResourcePanel').on('click', function() { $('#requestResourcePanel').removeClass('open'); });

    // Stepper init
    var stepperEl = document.querySelector('#requestResourcePanel .bs-stepper');
    if (stepperEl && typeof Stepper !== 'undefined') {
        try { window.resStepperObj = new Stepper(stepperEl); } catch (e) {}
    }
    $(document).on('click', '#requestResourcePanel .bs-stepper-header .step', function(e) {
        e.preventDefault();
        var t = $(this).data('target');
        if (t === '#res-job-part') goToResStep(1);
        else if (t === '#res-salary-part') goToResStep(2);
        else if (t === '#res-desc-part') goToResStep(3);
    });

    // Chip autocomplete init (URLs use the global JS base_url from bo_template.php)
    initResChipAutocomplete({ inputId:'resLocationInput',       dropdownId:'resLocationDropdown',       chipsId:'resLocationChips',       hiddenId:'resJobLocation',       url: base_url+'admin/searchLocation',  key:'JobLocation'       });
    initResChipAutocomplete({ inputId:'resEducationInput',      dropdownId:'resEducationDropdown',      chipsId:'resEducationChips',      hiddenId:'resEducationRequired', url: base_url+'admin/searchEducation', key:'EducationRequired' });
    initResChipAutocomplete({ inputId:'resMustHaveSkillsInput', dropdownId:'resMustHaveSkillsDropdown', chipsId:'resMustHaveSkillsChips', hiddenId:'resMustHaveSkills',    url: base_url+'admin/searchSkills',    key:'SkillName'         });
    initResChipAutocomplete({ inputId:'resNiceToHaveSkillsInput',dropdownId:'resNiceToHaveSkillsDropdown',chipsId:'resNiceToHaveSkillsChips',hiddenId:'resNiceToHaveSkills',url: base_url+'admin/searchSkills',    key:'SkillName'         });
    initResChipAutocomplete({ inputId:'resLanguageInput',       dropdownId:'resLanguageDropdown',       chipsId:'resLanguageChips',       hiddenId:'resCommunicationLang', url: base_url+'admin/searchLanguage',  key:'CommunicationLang' });

    // Resource request form submit
    $(document).off('submit','#resourceRequestForm').on('submit','#resourceRequestForm', function(e) {
        e.preventDefault();
        var $form = $(this);
        if ($form.data('submitting')) { e.stopImmediatePropagation(); return false; }
        $form.data('submitting', true);
        var $btn = $('#resSubmitBtn'), originalBtnHtml = $btn.html();
        $btn.prop('disabled', true).addClass('disabled');

        function unlockAndFail(step, msg, isWarning, focusId) {
            $form.data('submitting', false);
            $btn.prop('disabled', false).removeClass('disabled').html(originalBtnHtml);
            if (step) goToResStep(step);
            if (msg) { if (isWarning) toastr.warning(msg); else toastr.error(msg); }
            if (focusId) $(focusId).focus();
            return false;
        }

        // Auto-sync text remaining in chip inputs
        ['resLocation','resEducation','resMustHaveSkills','resNiceToHaveSkills','resLanguage'].forEach(function(p) {
            var $inp = $('#' + p + 'Input');
            if ($inp.length && $inp.val().trim()) {
                var v = $inp.val().replace(/,/g,'').trim();
                if (v) {
                    var cId = p === 'resLanguage' ? 'resLanguageChips' : p + 'Chips';
                    var hId = p === 'resLocation' ? 'resJobLocation' : (p === 'resEducation' ? 'resEducationRequired' : (p === 'resLanguage' ? 'resCommunicationLang' : p));
                    addResChipDirect(v, p + 'Input', cId, hId);
                }
            }
        });
        if (!$('#resMustHaveSkills').val()) {
            $('#resMustHaveSkills').val($('#resMustHaveSkillsChips .badge').map(function(){ return $(this).text().replace('×','').trim(); }).get().filter(Boolean).join(','));
        }
        if (!$('#resCommunicationLang').val()) {
            $('#resCommunicationLang').val($('#resLanguageChips .badge').map(function(){ return $(this).text().replace('×','').trim(); }).get().filter(Boolean).join(','));
        }

        var jobTitle   = ($('#resourceRequestForm input[name="JobTitle"]').val()||'').trim();
        var did        = $('#resourceRequestForm select[name="Did"]').val();
        var approverId = $('#resourceRequestForm select[name="ApproverId"]').val();
        var mustHave   = ($('#resMustHaveSkills').val()||'').trim();
        var commLang   = ($('#resCommunicationLang').val()||'').trim();
        var jd         = ($('#resourceRequestForm textarea[name="JobDescription"]').val()||'').trim();
        var rr         = ($('#resourceRequestForm textarea[name="Responsibilities"]').val()||'').trim();

        if (!jobTitle||!did||!approverId) return unlockAndFail(1,'Please complete Job Title, Department, and Approver Name in Step 1.',true);
        if (!mustHave) return unlockAndFail(3,'Please add at least one Must-Have Skill.',true,'#resMustHaveSkillsInput');
        if (!commLang) return unlockAndFail(3,'Please add at least one Communication Language.',true,'#resLanguageInput');
        if (!jd||!rr) return unlockAndFail(3,'Please provide Job Description and Roles & Responsibilities.',true);

        var eMinStr = ($('#res_ExpectedSalaryMin').val()||'').trim();
        var eMaxStr = ($('#res_ExpectedSalaryMax').val()||'').trim();
        if (eMinStr !== '') { var eMin = parseFloat(eMinStr); if (isNaN(eMin)||eMin<0) return unlockAndFail(2,'Minimum expected salary cannot be negative.',false); }
        if (eMaxStr !== '') { var eMax = parseFloat(eMaxStr); if (isNaN(eMax)||eMax<0) return unlockAndFail(2,'Maximum expected salary cannot be negative.',false); }
        if (eMinStr !== '' && eMaxStr !== '') { if (parseFloat(eMinStr)>parseFloat(eMaxStr)) return unlockAndFail(2,'Minimum expected salary cannot be greater than maximum expected salary.',false); }

        $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    toastr.success(res.message || 'Resource Request submitted successfully.');
                    setTimeout(function(){ location.reload(); }, 1000);
                } else {
                    $form.data('submitting', false);
                    $btn.prop('disabled', false).removeClass('disabled').html(originalBtnHtml);
                    toastr.error(res.message || 'Failed to submit request.');
                }
            },
            error: function(xhr) {
                $form.data('submitting', false);
                $btn.prop('disabled', false).removeClass('disabled').html(originalBtnHtml);
                var errMsg = 'Server or network error occurred.';
                try { var o = JSON.parse(xhr.responseText); if (o&&o.message) errMsg = o.message; } catch(ex){}
                toastr.error(errMsg);
            }
        });
    });

    // Chip keydown: Enter / comma adds chip
    $(document).on('keydown','#resLocationInput,#resEducationInput,#resMustHaveSkillsInput,#resNiceToHaveSkillsInput,#resLanguageInput', function(e) {
        if (e.which===13||e.keyCode===13||e.key==='Enter'||e.which===188||e.keyCode===188||e.key===',') {
            e.preventDefault(); e.stopPropagation();
            var $i=$(this), val=$i.val().replace(/,/g,'').trim(), id=$i.attr('id');
            if (val.length>=1) {
                if (id==='resLocationInput')        addResChipDirect(val,'resLocationInput','resLocationChips','resJobLocation');
                else if (id==='resEducationInput')  addResChipDirect(val,'resEducationInput','resEducationChips','resEducationRequired');
                else if (id==='resMustHaveSkillsInput') addResChipDirect(val,'resMustHaveSkillsInput','resMustHaveSkillsChips','resMustHaveSkills');
                else if (id==='resNiceToHaveSkillsInput') addResChipDirect(val,'resNiceToHaveSkillsInput','resNiceToHaveSkillsChips','resNiceToHaveSkills');
                else if (id==='resLanguageInput')   addResChipDirect(val,'resLanguageInput','resLanguageChips','resCommunicationLang');
            }
            return false;
        }
    });

    // Chip blur: auto-add on focus-out
    $(document).on('blur','#resLocationInput,#resEducationInput,#resMustHaveSkillsInput,#resNiceToHaveSkillsInput,#resLanguageInput', function() {
        var $i=$(this);
        setTimeout(function() {
            var val=$i.val().replace(/,/g,'').trim(), id=$i.attr('id');
            if (val.length>=1) {
                if (id==='resLocationInput')         addResChipDirect(val,'resLocationInput','resLocationChips','resJobLocation');
                else if (id==='resEducationInput')   addResChipDirect(val,'resEducationInput','resEducationChips','resEducationRequired');
                else if (id==='resMustHaveSkillsInput') addResChipDirect(val,'resMustHaveSkillsInput','resMustHaveSkillsChips','resMustHaveSkills');
                else if (id==='resNiceToHaveSkillsInput') addResChipDirect(val,'resNiceToHaveSkillsInput','resNiceToHaveSkillsChips','resNiceToHaveSkills');
                else if (id==='resLanguageInput')    addResChipDirect(val,'resLanguageInput','resLanguageChips','resCommunicationLang');
            }
        }, 200);
    });

    // AI Generate Job Description & Responsibilities
    $('#btnGenerateJobContent').on('click', function(e) {
        e.preventDefault();
        $('#resLocationInput,#resEducationInput,#resMustHaveSkillsInput,#resNiceToHaveSkillsInput,#resLanguageInput').trigger('blur');
        setTimeout(function() {
            var jobTitle       = $('input[name="JobTitle"]').val().trim();
            var functionalRole = $('input[name="FunctionalRole"]').val().trim();
            var deptText       = $('select[name="Did"] option:selected').text().trim();
            var department     = (deptText && !deptText.toLowerCase().includes('select')) ? deptText : '';
            var expMin         = $('#res_ExpMin').val() || 0;
            var expMax         = $('#res_ExpMax').val() || 0;
            var mustSkills     = $('#resMustHaveSkills').val() || $('#resMustHaveSkillsInput').val() || '';
            var niceSkills     = $('#resNiceToHaveSkills').val() || $('#resNiceToHaveSkillsInput').val() || '';
            var location       = $('#resJobLocation').val() || $('#resLocationInput').val() || '';
            var commLang       = $('#resCommunicationLang').val() || $('#resLanguageInput').val() || '';
            if (!jobTitle && !functionalRole) {
                toastr ? toastr.error('Please enter a Job Title or Functional Role before generating.') : alert('Please enter a Job Title or Functional Role before generating.');
                return;
            }
            var btn = $('#btnGenerateJobContent'), origHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating...');
            $.ajax({
                url: base_url + 'admin/generateJobContent',
                type: 'POST',
                data: { JobTitle:jobTitle, FunctionalRole:functionalRole, Department:department, ExpMin:expMin, ExpMax:expMax, MustHaveSkills:mustSkills, NiceToHaveSkills:niceSkills, JobLocation:location, CommunicationLang:commLang },
                dataType: 'json',
                success: function(res) {
                    btn.prop('disabled', false).html(origHtml);
                    if (res && res.status === 'success') {
                        if (res.job_description) $('textarea[name="JobDescription"]').val(res.job_description);
                        if (res.responsibilities) $('textarea[name="Responsibilities"]').val(res.responsibilities);
                        toastr ? toastr.success('Job Description & Responsibilities auto-generated successfully!') : null;
                    } else {
                        var msg = (res&&res.message) ? res.message : 'Unable to generate job content. Please enter the details manually.';
                        toastr ? toastr.error(msg) : alert(msg);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(origHtml);
                    console.error('Job content generation error:', xhr.responseText);
                    toastr ? toastr.error('Unable to generate job content. Please enter the details manually.') : alert('Unable to generate job content. Please enter the details manually.');
                }
            });
        }, 250);
    });
});
