<?php
$ci = &get_instance();
$employee_det = (isset($this) && isset($this->session)) ? $this->session->userdata('logged_in') : (isset($ci->session) ? $ci->session->userdata('logged_in') : []);

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = (isset($this) && isset($this->config)) ? $this->config->item('theme_locations').$this->config->item('active_template') : '';

$parents = [];
$children = [];

// Separate parent & child
foreach ($menus as $menu) {
    if (empty($menu['ParentId']) || $menu['ParentId'] == 0) {
        $parents[] = $menu;
    } else {
        $children[$menu['ParentId']][] = $menu;
    }
}
?>

<style>
/* Modern High-Contrast Custom Toggle Switches */
.custom-switch {
    padding-left: 3.2rem !important;
}

.custom-switch .custom-control-label {
    cursor: pointer;
    position: relative;
    user-select: none;
    display: inline-flex;
    align-items: center;
}

.custom-switch .custom-control-label::before {
    left: -3.2rem !important;
    width: 2.8rem !important;
    height: 1.5rem !important;
    pointer-events: all;
    border-radius: 1rem !important;
    background-color: #cbd5e1 !important; /* OFF track: Light slate gray */
    border: 2px solid #94a3b8 !important;
    transition: background-color 0.25s ease-in-out, border-color 0.25s ease-in-out, box-shadow 0.25s ease-in-out !important;
}

.custom-switch .custom-control-label::after {
    top: calc(0.18rem) !important;
    left: calc(-3rem) !important;
    width: calc(1.15rem) !important;
    height: calc(1.15rem) !important;
    background-color: #ffffff !important; /* OFF handle: White circle */
    border-radius: 50% !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
    transition: transform 0.25s ease-in-out, background-color 0.25s ease-in-out !important;
}

/* ON State: Vibrant Emerald Green Track + White Handle Slide Right */
.custom-switch .custom-control-input:checked ~ .custom-control-label::before {
    background-color: #28a745 !important; /* ON track: Emerald Green */
    border-color: #1e7e34 !important;
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.4) !important;
}

.custom-switch .custom-control-input:checked ~ .custom-control-label::after {
    background-color: #ffffff !important;
    transform: translateX(1.3rem) !important;
}

/* Status Label Badge Next to Switch */
.switch-status-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 2px 8px;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.switch-status-label.status-on {
    background-color: #28a745;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(40,167,69,0.2);
}

.switch-status-label.status-off {
    background-color: #64748b;
    color: #ffffff;
}

/* Module Header & Item Container Styling */
.perm-module-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    overflow: hidden;
    height: 100%;
}

.perm-module-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.perm-module-body {
    padding: 16px;
}

.perm-item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    margin-bottom: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    transition: all 0.2s ease-in-out;
}

.perm-item-row.active-perm {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    border-left: 4px solid #28a745 !important;
}

.perm-item-label {
    display: flex;
    align-items: center;
    margin-bottom: 0;
    cursor: pointer;
    font-weight: 600;
    color: #334155;
}

.perm-icon-pill {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #e2e8f0;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.perm-item-row.active-perm .perm-icon-pill {
    background: #28a745;
    color: #ffffff;
}
</style>

<section class="content pt-3 pb-4">
  <div class="container-fluid">
    <div class="card card-primary card-outline shadow-sm border-0 modal-content-rounded-xl">
      
      <!-- CARD HEADER -->
      <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h4 class="font-weight-bold mb-1 text-dark" style="float:none !important; display:block !important;">
              <i class="fas fa-user-shield text-primary mr-2"></i> Role Access & Permissions Engine
            </h4>
            <div class="text-muted small" style="clear:both;">
              Manage system module access and feature permissions for organizational roles.
            </div>
          </div>

          <div class="d-flex gap-2 mt-2 mt-sm-0">
            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill font-weight-bold mr-2" id="selectAllBtn">
              <i class="fas fa-check-double mr-1"></i> Select All
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill font-weight-bold mr-2" id="clearAllBtn">
              <i class="fas fa-undo mr-1"></i> Clear All
            </button>
            <button class="btn btn-primary btn-sm rounded-pill font-weight-bold px-4 shadow-sm" id="savePermissions">
              <i class="fas fa-save mr-1"></i> Save Permissions
            </button>
          </div>
        </div>
      </div>

      <div class="card-body p-4">

        <!-- ROLE SELECTION BAR -->
        <div class="role-select-box mb-4">
          <div class="row align-items-center">
            <div class="col-md-6 col-sm-12">
              <label class="font-weight-bold text-dark mb-1 d-flex align-items-center">
                <i class="fas fa-user-tag text-primary mr-2"></i> Select Role to Configure
              </label>
              <select id="roleDropdown" class="form-control form-control-lg font-weight-bold shadow-sm" style="border-radius:12px;">
                <option value="">-- Choose Organizational Role --</option>
                <?php foreach($roles as $r){ ?>
                 <option value="<?= $r['Erid']; ?>" <?= (isset($selectedRole) && $selectedRole == $r['Erid']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($r['RoleName']); ?>
                  </option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-6 col-sm-12 mt-3 mt-md-0 text-md-right">
              <span class="badge badge-light border px-3 py-2 text-muted font-weight-normal" style="border-radius:20px; font-size:13px;">
                <i class="fas fa-info-circle text-info mr-1"></i> Changes apply immediately to users in selected role.
              </span>
            </div>
          </div>
        </div>

        <!-- MODULE PERMISSIONS GRID CARDS -->
        <div class="row">
          <?php foreach($parents as $parent): ?>
            <?php 
              $pId = $parent['IHMid'];
              $pName = $parent['MenuName'];
              $pNameLower = strtolower($pName);
              
              // Icon mapping
              $parentIcon = 'fas fa-folder';
              if (strpos($pNameLower, 'dashboard') !== false) $parentIcon = 'fas fa-tachometer-alt';
              elseif (strpos($pNameLower, 'admin') !== false) $parentIcon = 'fas fa-cogs';
              elseif (strpos($pNameLower, 'vaccancy') !== false || strpos($pNameLower, 'vacancy') !== false) $parentIcon = 'fas fa-briefcase';
              elseif (strpos($pNameLower, 'interview') !== false) $parentIcon = 'fas fa-calendar-alt';
              elseif (strpos($pNameLower, 'analytics') !== false || strpos($pNameLower, 'history') !== false) $parentIcon = 'fas fa-chart-line';

              $hasChildren = isset($children[$pId]) && !empty($children[$pId]);
            ?>

            <div class="col-lg-6 col-md-12 mb-4">
              <div class="perm-module-card">
                
                <!-- PARENT MODULE HEADER -->
                <div class="perm-module-header">
                  <div class="d-flex align-items-center">
                    <i class="<?= $parentIcon; ?> mr-2 text-info" style="font-size:18px;"></i>
                    <h5 class="font-weight-bold mb-0 text-white" style="font-size:16px; font-family:'Outfit',sans-serif;">
                      <?= htmlspecialchars($pName); ?>
                    </h5>
                  </div>

                  <div class="custom-control custom-switch">
                    <input type="checkbox"
                           class="custom-control-input menuCheckbox parentMenu"
                           value="<?= $pId; ?>"
                           id="menu_<?= $pId; ?>">
                    <label class="custom-control-label" for="menu_<?= $pId; ?>"></label>
                  </div>
                </div>

                <!-- CHILD MODULES BODY -->
                <div class="perm-module-body">
                  <?php if($hasChildren): ?>
                    <?php foreach($children[$pId] as $child): ?>
                      <?php 
                        $cId = $child['IHMid'];
                        $cName = $child['MenuName'];
                        $cNameLower = strtolower($cName);

                        $childIcon = 'fas fa-circle-notch';
                        if (strpos($cNameLower, 'user') !== false) $childIcon = 'fas fa-users-cog';
                        elseif (strpos($cNameLower, 'department') !== false) $childIcon = 'fas fa-building';
                        elseif (strpos($cNameLower, 'stage') !== false) $childIcon = 'fas fa-layer-group';
                        elseif (strpos($cNameLower, 'permission') !== false || strpos($cNameLower, 'role') !== false) $childIcon = 'fas fa-user-shield';
                        elseif (strpos($cNameLower, 'vaccancy') !== false || strpos($cNameLower, 'vacancy') !== false) $childIcon = 'fas fa-list-alt';
                        elseif (strpos($cNameLower, 'requested') !== false) $childIcon = 'fas fa-clipboard-list';
                        elseif (strpos($cNameLower, 'approved') !== false) $childIcon = 'fas fa-check-circle';
                        elseif (strpos($cNameLower, 'interview') !== false) $childIcon = 'fas fa-calendar-check';
                        elseif (strpos($cNameLower, 'candidate') !== false) $childIcon = 'fas fa-user-tie';
                      ?>

                      <div class="perm-item-row">
                        <label class="perm-item-label" for="menu_<?= $cId; ?>">
                          <span class="perm-icon-pill"><i class="<?= $childIcon; ?>"></i></span>
                          <span><?= htmlspecialchars($cName); ?></span>
                        </label>

                        <div class="custom-control custom-switch">
                          <input type="checkbox"
                                 class="custom-control-input menuCheckbox childMenu"
                                 data-parent="<?= $pId; ?>"
                                 value="<?= $cId; ?>"
                                 id="menu_<?= $cId; ?>">
                          <label class="custom-control-label" for="menu_<?= $cId; ?>"></label>
                        </div>
                      </div>

                    <?php endforeach; ?>
                  <?php else: ?>
                    <div class="text-muted small py-2 text-center">
                      <i class="fas fa-check-circle text-success mr-1"></i> Direct main page module (No sub-menus).
                    </div>
                  <?php endif; ?>
                </div>

              </div>
            </div>

          <?php endforeach; ?>
        </div>

      </div>

    </div>
  </div>
</section>

<!-- SAVE PERMISSION CONFIRM MODAL -->
<div class="modal fade" id="savePermissionModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">

      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-save mr-2"></i> Confirm Permission Update</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fas fa-shield-alt text-primary" style="font-size:48px;"></i>
        </div>
        <h6 class="font-weight-bold text-dark">Save Updated Permissions?</h6>
        <p class="text-muted small mb-0">Are you sure you want to update the system module access permissions for the selected role?</p>
      </div>

      <div class="modal-footer justify-content-center bg-light">
        <button class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" id="confirmSavePermissions">
          Yes, Save Permissions
        </button>
      </div>

    </div>
  </div>
</div>

<script>
var base_url = "<?= base_url(); ?>";

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
</script>