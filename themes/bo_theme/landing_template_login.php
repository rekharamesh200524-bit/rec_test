<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <meta http-equiv="Cache-control" content="public"/>
        <meta http-equiv="expires" content="Tue, 29 Sep 2015 1:00:00 GMT" />
        <?php $theme_path = $this->config->item('theme_locations').$this->config->item('active_template'); ?>
		<link href="<?=$theme_path?>/images/favicon.png" rel="shortcut icon">
       <title>INET - HRMS | Login - 2026</title>
        <!-- <link type="text/css" href="<?= $theme_path; ?>/css/style.default_login.css" rel="stylesheet"> -->
        <script type="text/javascript" src="<?= $theme_path; ?>/js/jquery-1.11.1.min.js"></script>
        <link href="<?=$theme_path?>/css/jquery-ui.css" rel="stylesheet" />
        <script type="text/javascript" src="<?= $theme_path; ?>/js/jquery-ui.js"></script>

        <!-- Toastr -->
        <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/toastr/toastr.min.css">
        <script src="<?=$theme_path?>/assets/plugins/toastr/toastr.min.js"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?=$theme_path?>/assets/plugins/fontawesome-free/css/all.min.css">
        <link rel="stylesheet" href="<?=$theme_path?>/css/custom-style.css">
    </head>

   <body class="h-100">
       <div class="authincation h-100">
             	<?php echo $content; ?>
       </div>
       <script src="<?=$theme_path?>/js/custom-script.js"></script>

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
    </body>
</html>
