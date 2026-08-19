<?php
    $employee_det = $this->session->userdata('logged_in');
         
     if(empty($employee_det)) { redirect($this->config->item('base_url').'admin/index'); }
    $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');
    
    $fv         = $this->session->flashdata('form_values');
    $fv         = is_array($fv) ? $fv : [];
    $hasError   = (bool) $this->session->flashdata('error');
    $hasSuccess = (bool) $this->session->flashdata('success');
?>
 
       
    <section class="content">
      <div class="container-fluid">

            <div class="card card-primary card-outline">

              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="fas fa-users-cog text-primary mr-2"></i>User Management</h3>

                        <a class="btn btn-sm btn-success"  id="openAddForm">
                            <i class="fas fa-users"></i> Add User
                        </a>
                    </div>
              </div>
             
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>S.No</th>
                    <th>Emp Code</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Designation</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                                <tbody>
                            <?php
                            

                            if (isset($users) && !empty($users)) {
                                $i = 1;
                                foreach ($users as $usr) {
                            ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= $usr['EmpCode']; ?></td>
                                    <td><?= $usr['EmpName']; ?></td>
                                    <td><?= $usr['EmpEmail']; ?></td>
                                    <td><?= $usr['EmpPhone']; ?></td>
                                    <td><?= $usr['EmpGender']; ?></td>
                                    <td><?= $usr['EmpDesignation']; ?></td>
                                    <td>
                                        <?= ($usr['UStatus'] == 1) ? 'Active' : 'Inactive'; ?>
                                    </td>
                                              <td class="text-center"> 

                                          <div class="btn-group" role="group">

                                               
                                                <button type="button"
                                                        class="btn btn-sm btn-primary editUserBtn"
                                                        title="Edit User"
                                                        data-id="<?= $usr['IUid']; ?>"
                                                        data-name="<?= $usr['EmpName']; ?>"
                                                        data-email="<?= $usr['EmpEmail']; ?>"
                                                        data-phone="<?= $usr['EmpPhone']; ?>"
                                                        data-dob="<?= $usr['EmpDOB']; ?>"
                                                        data-gender="<?= $usr['EmpGender']; ?>"
                                                        data-designation="<?= $usr['EmpDesignation']; ?>"
                                                        data-role="<?= $usr['Erid']; ?>"
                                                        data-department="<?= $usr['Did']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <?php if ($usr['UStatus'] == 1) { ?>
                                                    
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger userStatusBtn"
                                                            data-id="<?= $usr['IUid']; ?>"
                                                            data-action="deactivate"
                                                            title="Deactivate User">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                <?php } else { ?>
                                                   
                                                    <button type="button"
                                                            class="btn btn-sm btn-success userStatusBtn"
                                                            data-id="<?= $usr['IUid']; ?>"
                                                            data-action="activate"
                                                            title="Activate User">
                                                        <i class="fas fa-user-check"></i>
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

            <form action="<?= base_url('admin/UpdateUser'); ?>" method="post">

                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="IUid" id="edit_IUid">

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="val-username" id="edit_name" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="val-email" id="edit_email" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="val-phoneus" id="edit_phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>DOB</label>
                        <input type="date" name="val-dob" id="edit_dob" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Gender</label>
                        <select name="val-gender" id="edit_gender" class="form-control">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <select name="val-department" id="edit_department" class="form-control">
                            <option value="">Select</option>
                            <?php foreach ($department as $dep) { ?>
                                <option value="<?= $dep['Did']; ?>">
                                    <?= $dep['Departmentname']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <select name="val-Role" id="edit_role" class="form-control">
                            <option value="">Select</option>
                            <?php foreach ($role as $rl) { ?>
                                <option value="<?= $rl['Erid']; ?>">
                                    <?= $rl['RoleName']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>


                    <div class="form-group">
                        <label>Designation</label>
                        <input type="text" name="val-designation" id="edit_designation" class="form-control">
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
        <h5>Add User</h5>
        <button type="button" class="close-btn" id="closeAddForm">&times;</button>
    </div>

    <div class="right-form-body">


        <form class="form-valide" id="addUserForm"
              action="<?= base_url('admin/SaveUser'); ?>"
              method="post">

            <div class="row">
                <div class="col-xl-12">

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Employee ID <span class="text-danger">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="val-empid"
                                   value="<?= htmlspecialchars(isset($fv['val-empid']) ? $fv['val-empid'] : '', ENT_QUOTES) ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Name <span class="text-danger">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="val-username"
                                   value="<?= htmlspecialchars(isset($fv['val-username']) ? $fv['val-username'] : '', ENT_QUOTES) ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Email <span class="text-danger">*</span></label>
                        <div class="col-lg-8">
                            <input type="email" class="form-control" name="val-email"
                                   value="<?= htmlspecialchars(isset($fv['val-email']) ? $fv['val-email'] : '', ENT_QUOTES) ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Phone <span class="text-danger">*</span></label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="val-phoneus"
                                   value="<?= htmlspecialchars(isset($fv['val-phoneus']) ? $fv['val-phoneus'] : '', ENT_QUOTES) ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Department <span class="text-danger">*</span></label>
                        <div class="col-lg-8">
                            <select class="form-control" name="val-department">
                                <option value="">Please select</option>
                                <?php foreach ($department as $dep) { ?>
                                    <option value="<?= $dep['Did'] ?>"
                                        <?= (isset($fv['val-department']) && $fv['val-department'] == $dep['Did']) ? 'selected' : '' ?>>
                                        <?= $dep['Departmentname'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Role <span class="text-danger">*</span></label>
                        <div class="col-lg-8">
                            <select class="form-control" name="val-Role">
                                <option value="">Please Select</option>
                                <?php foreach ($role as $rl) { ?>
                                    <option value="<?= $rl['Erid'] ?>"
                                        <?= (isset($fv['val-Role']) && $fv['val-Role'] == $rl['Erid']) ? 'selected' : '' ?>>
                                        <?= $rl['RoleName'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">DOB</label>
                        <div class="col-lg-8">
                            <input type="date" class="form-control" name="val-dob"
                                   value="<?= htmlspecialchars(isset($fv['val-dob']) ? $fv['val-dob'] : '', ENT_QUOTES) ?>">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Gender</label>
                        <div class="col-lg-8">
                            <select class="form-control" name="val-gender">
                                <option value="">Select</option>
                                <option value="Male"   <?= (isset($fv['val-gender']) && $fv['val-gender'] === 'Male')   ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= (isset($fv['val-gender']) && $fv['val-gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">Designation</label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="val-designation"
                                   value="<?= htmlspecialchars(isset($fv['val-designation']) ? $fv['val-designation'] : '', ENT_QUOTES) ?>">
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
        url = "<?= base_url('admin/DeactivateUser/'); ?>" + userId;
        title = "Deactivate User";
        message = "Are you sure you want to deactivate this user?";
        btnClass = "btn-danger";
    } else {
        url = "<?= base_url('admin/ActivateUser/'); ?>" + userId;
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

    
    <?php if ($hasError): ?>
    $('#rightForm').addClass('open');
    $('#rightFormOverlay').addClass('show');
    <?php endif; ?>

});
</script>
