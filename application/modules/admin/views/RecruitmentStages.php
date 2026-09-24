<?php
$employee_det = $this->session->userdata('logged_in');

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = $this->config->item('theme_locations').$this->config->item('active_template');
?>


<section class="content">
  <div class="container-fluid">

    <div class="card card-primary card-outline">

      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title mb-0">Recruitment Stages</h3>

          <a class="btn btn-sm btn-success" id="openAddForm">
            <i class="fas fa-plus"></i> Add Stage
          </a>
        </div>
      </div>

      <div class="card-body table-responsive">
        <table id="recruitmentStagesTable" class="table table-bordered table-striped">
          <thead class="bg-success text-white">
            <tr>
              <th>Stage Order</th>
              <th>Stage Name</th>
              <th>Stage Group</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <?php if(isset($stages) && !empty($stages)): ?>
              <?php 
              $currentGroup = '';
              foreach($stages as $st): 
                if ($st['StageGroup'] !== $currentGroup): 
                  $currentGroup = $st['StageGroup'];
              ?>
                <tr class="bg-light font-weight-bold text-navy">
                  <td colspan="5" class="py-2 pl-3">
                    <i class="fas fa-layer-group mr-1"></i> 
                    <?= htmlspecialchars($currentGroup ? $currentGroup : 'No Group'); ?>
                  </td>
                </tr>
              <?php endif; ?>
                <tr>
                  <td><?= htmlspecialchars($st['StageOrder'] ?? ''); ?></td>
                  <td><?= htmlspecialchars($st['StageName'] ?? ''); ?></td>
                  <td><?= htmlspecialchars($st['StageGroup'] ?? 'N/A'); ?></td>
                  <td>
                    <?= ($st['StageStatus'] == 1) ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>'; ?>
                  </td>

                  <td class="text-center">
                    <div class="btn-group">

                       <!-- Edit -->
                      <button type="button"
                              class="btn btn-sm btn-primary editStageBtn"
                              data-id="<?= $st['StageId']; ?>"
                              data-name="<?= htmlspecialchars($st['StageName'] ?? ''); ?>"
                              data-group="<?= htmlspecialchars($st['StageGroup'] ?? ''); ?>"
                              data-order="<?= $st['StageOrder']; ?>"
                              data-status="<?= $st['StageStatus']; ?>">
                        <i class="fas fa-edit"></i>
                      </button>

                    <?php if($st['StageStatus'] == 1): ?>
                      <button type="button"
                              class="btn btn-sm btn-danger stageStatusBtn"
                              data-url="<?= base_url('admin/ChangeStageStatus/'.$st['StageId'].'/deactivate'); ?>"
                              data-action="deactivate">
                          <i class="fas fa-times"></i>
                      </button>
                    <?php else: ?>
                      <button type="button"
                              class="btn btn-sm btn-success stageStatusBtn"
                              data-url="<?= base_url('admin/ChangeStageStatus/'.$st['StageId'].'/activate'); ?>"
                              data-action="activate">
                          <i class="fas fa-check"></i>
                      </button>
                    <?php endif; ?>

                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>

        </table>
      </div>

    </div>

  </div>
</section>
<div id="rightForm" class="right-form">
  <div class="right-form-header">
    <h5>Add Stage</h5>
    <button type="button" class="close-btn" id="closeAddForm">&times;</button>
  </div>

  <div class="right-form-body">

        <form action="<?= base_url('admin/SaveStage'); ?>" method="post">



      <div class="form-group">
        <label>Stage Group</label>
        <select name="StageGroup" id="add_StageGroup" class="form-control" required>
          <option value="">Select Group</option>
          <option value="Application">Application</option>
          <option value="Interview">Interview</option>
          <option value="Rejection">Rejection</option>
          <option value="Offer">Offer</option>
          <option value="Hiring">Hiring</option>
        </select>
      </div>

      <div class="form-group">
        <label>Stage Name</label>
        <input type="text" name="StageName" class="form-control" required>
      </div>

      <div class="form-group">
        <label>Stage Order</label>
        <input type="number" name="StageOrder" id="add_StageOrder" class="form-control" required min="1">
      </div>

      <div class="text-right">
        <button type="submit" class="btn btn-primary">Submit</button>
      </div>

    </form>

  </div>
</div>

<div id="rightFormOverlay"></div><div id="editRightForm" class="right-form">
  <div class="right-form-header">
    <h5>Edit Stage</h5>
    <button type="button" class="close-btn" id="closeEditForm">&times;</button>
  </div>

  <div class="right-form-body">

    <form action="<?= base_url('admin/UpdateStage'); ?>" method="post">

      <input type="hidden" name="StageId" id="edit_StageId">



      <div class="form-group">
        <label>Stage Group</label>
        <select name="StageGroup" id="edit_StageGroup" class="form-control" required>
          <option value="">Select Group</option>
          <option value="Application">Application</option>
          <option value="Interview">Interview</option>
          <option value="Rejection">Rejection</option>
          <option value="Offer">Offer</option>
          <option value="Hiring">Hiring</option>
        </select>
      </div>

      <div class="form-group">
        <label>Stage Name</label>
        <input type="text" name="StageName" id="edit_StageName" class="form-control">
      </div>

      <div class="form-group">
        <label>Stage Order</label>
        <input type="number" name="StageOrder" id="edit_StageOrder" class="form-control">
      </div>

      <div class="text-right">
        <button type="submit" class="btn btn-primary">Update</button>
      </div>

    </form>

  </div>
</div>


<div class="modal fade" id="stageStatusModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-warning">
        <h5 class="modal-title">Confirm Action</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body text-center">
        <p id="stageStatusMessage"></p>
      </div>

      <div class="modal-footer justify-content-center">
        <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <a href="#" class="btn btn-danger" id="confirmStageStatus">Yes Continue</a>
      </div>

    </div>
  </div>
</div>
