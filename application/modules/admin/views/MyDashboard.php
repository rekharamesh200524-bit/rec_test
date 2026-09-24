<?php
$employee_det = $this->session->userdata('logged_in');

if (empty($employee_det)) {
    redirect($this->config->item('base_url').'admin/index');
}

$theme_path = $this->config->item('theme_locations').$this->config->item('active_template');
?>

<section class="content">
  <div class="container-fluid">

    
    <div class="row">

      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="small-box bg-primary">
          <div class="inner">
            <h3><?= $dept_total_vacancies ?></h3>
            <p>Total Vacancies</p>
          </div>
          <div class="icon"><i class="fas fa-briefcase"></i></div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3><?= $dept_onhold ?></h3>
            <p>On Hold</p>
          </div>
          <div class="icon"><i class="fas fa-pause"></i></div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3><?= $dept_rejected ?></h3>
            <p>Rejected</p>
          </div>
          <div class="icon"><i class="fas fa-times"></i></div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="small-box bg-success">
          <div class="inner">
            <h3><?= $dept_selected ?></h3>
            <p>Selected</p>
          </div>
          <div class="icon"><i class="fas fa-check"></i></div>
        </div>
      </div>

    </div>


   
   <div class="row mt-4">

  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">Department Overview</h3>
      </div>

      <div class="card-body">

        <div class="text-center">
          <canvas id="vacancyDonutChart" height="220" data-labels="<?= htmlspecialchars($donut_labels, ENT_QUOTES, 'UTF-8'); ?>" data-values="<?= htmlspecialchars($donut_values, ENT_QUOTES, 'UTF-8'); ?>"></canvas>
        </div>

        <hr>

        <div id="vacancyDetails"></div>

      </div>
    </div>
  </div>

</div>
    </div>

  </div>
</section>