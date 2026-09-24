<?php
$employee_det = $this->session->userdata('logged_in');

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = $this->config->item('theme_locations').$this->config->item('active_template');

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
