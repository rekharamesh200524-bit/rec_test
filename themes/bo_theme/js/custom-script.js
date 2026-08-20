$(function () {
  $("#example1").DataTable({
    "responsive": false, "lengthChange": false, "autoWidth": false,
    "buttons": ["copy", "csv", "excel", "colvis"]
  }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
  $('#example2').DataTable({
    "paging": true,
    "lengthChange": false,
    "searching": false,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": false,
  });
});

$(function () {
  //Date picker
  $('#reservationdate').datetimepicker({
      format: 'L'
  });
});

Dropzone.autoDiscover = false;
let resumeDropzone = null;

$(document).on('click', '.uploadResumeBtn', function () {
  $('#jobId').val($(this).data('id'));
  $('#uploadModal').modal('show');

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
                  formData.append("job_id", $('#jobId').val());
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

// Initial fetch & set interval (e.g. 30 seconds)
$(document).ready(function() {
  fetchNotifications();
  setInterval(fetchNotifications, 30000);
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
