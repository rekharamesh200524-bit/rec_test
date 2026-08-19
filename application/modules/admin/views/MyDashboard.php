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
          <canvas id="vacancyDonutChart" height="220"></canvas>
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

<script>
document.addEventListener("DOMContentLoaded", function () {

  var donutLabels = <?= $donut_labels ?>;
  var donutValues = <?= $donut_values ?>;

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
</script>