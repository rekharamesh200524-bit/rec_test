  <?php
    $employee_det = $this->session->userdata('logged_in'); 
     if(empty($employee_det)) { redirect($this->config->item('base_url').'admin/index'); }
         $theme_path = $this->config->item('theme_locations').$this->config->item('active_template'); 

?>
    <section class="content">
      <div class="container-fluid">
            <div class="card card-info card-outline">

              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="fas fa-building text-primary mr-2"></i>Department Management</h3>

                        <a class="btn btn-sm btn-secondary"  id="openAddForm">
                            <i class="fas fa-users"></i> Add Department
                        </a>
                    </div>
              </div>
              <div class="card-body table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead class="bg-success text-white">
                  <tr>
                    <th>S.No</th>
                    <th>Department Name</th> 
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                                <tbody>
                            <?php
                            

                            if (isset($department) && !empty($department)) {
                                $i = 1;
                                foreach ($department as $dep) {
                            ?>
                                <tr>
                                            <td><?= $i++; ?></td>
                                            <td><?= $dep['Departmentname']; ?></td> 
                                            <td>
                                                <?php if ($dep['Status'] == 1): ?>
                                                    <small class="badge badge-success">Active</small>
                                                <?php else: ?>
                                                    <small class="badge badge-danger">Inactive</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"> 

                                          <div class="btn-group" role="group">

                                                
                                                <button type="button"
                                                        class="btn btn-sm btn-primary editUserBtn"
                                                        title="Edit Department"
                                                        data-id="<?= $dep['Did']; ?>"
                                                        data-name="<?= $dep['Departmentname']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <?php if ($dep['Status'] == 1) { ?>
                                                   
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger userStatusBtn"
                                                            data-id="<?= $dep['Did']; ?>"
                                                            data-action="deactivate"
                                                            title="Deactivate Department">
                                                       <i class="fas fa-times"></i>
                                                    </button>
                                                <?php } else { ?>
                                                    
                                                    <button type="button"
                                                            class="btn btn-sm btn-success userStatusBtn"
                                                            data-id="<?= $dep['Did']; ?>"
                                                            data-action="activate"
                                                            title="Activate Department">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                <?php } ?>

                                            </div>

                                            </td> 

                                        </tr>
                                    <?php
                                        }
                                    }    
                                    ?>
                                       
                            </tbody> 
                    </table>
                  </div>
                </div>
      </div>
    </section>
   

<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form action="<?= base_url('admin/UpdateDepartment'); ?>" method="post">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Department</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="Did" id="edit_Did">

                    <div class="form-group">
                        <label>Department Name</label>
                        <input type="text" name="val-username" id="edit_Departmentname" class="form-control">
                    </div>  
                </div> 
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>

            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="userStatusModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="userStatusTitle"></h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <p id="userStatusMessage"></p>
      </div>

      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
        <a href="#" id="confirmUserStatusBtn" class="btn btn-danger btn-sm">Confirm</a>
      </div>

    </div>
  </div>
</div>

<div id="rightForm" class="right-form">
    <div class="right-form-header">
        <h5>Add Department</h5>
        <button type="button" class="close-btn" id="closeAddForm">&times;</button>
    </div>

    <div class="right-form-body">
              
        <form class="form-valide" id="addDepartmentForm"
              action="<?= base_url('admin/SaveDepartment'); ?>"
              method="post">

            <div class="row">
                <div class="col-xl-12">

                    
                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Name <span class="text-danger">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="val-depname" required>
                        </div>
                    </div> 

                    <div class="form-group row">
                        <div class="col-lg-12 text-right">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="reset" class="btn btn-dark">Clear</button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
       
    </div>
</div>

<div id="rightFormOverlay"></div>

<script src="<?= $theme_path ?>/assets/plugins/jquery/jquery.min.js"></script>

<script>
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
        url = "<?= base_url('admin/DeactivateDepartment/'); ?>" + userId;
        title = "Deactivate Department";
        message = "Are you sure you want to deactivate this Department?";
        btnClass = "btn-danger";
    } else {
        url = "<?= base_url('admin/ActivateDepartment/'); ?>" + userId;
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
</script>