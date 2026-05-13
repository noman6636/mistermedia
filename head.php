<!-- Preload critical resources -->
  <link rel="preload" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/bootstrap.css" as="style">
  <link rel="preload" href="<?=$system->getCurrentPageURL(false)?>/app-assets/vendors/css/vendors.min.css" as="style">
  <link rel="preload" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/components.css" as="style">
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" as="font" crossorigin>
  
  <!-- Inline critical CSS -->
  <style>
    /* Critical above-the-fold styles */
    body {
      visibility: hidden;
      opacity: 0;
      font-family: 'Montserrat', sans-serif;
      margin: 0;
      padding: 0;
    }
    .app-content {
      display: block !important; /* Ensure content area is visible */
    }
    /* Add other critical styles from your components.css here */
    
    /* Minimal layout styles to prevent layout shifts */
    .card-shadow {
      display: block;
      position: relative;
      overflow: hidden;
    }
    .content-wrapper {
      min-height: 100vh;
    }
  </style>

  <!-- Load core CSS with media query trick -->
  <link rel="stylesheet" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/bootstrap.css" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="<?=$system->getCurrentPageURL(false)?>/app-assets/vendors/css/vendors.min.css" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/components.css" media="print" onload="this.media='all'">

  <!-- Preconnect to external domains -->
  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://cdnjs.cloudflare.com">
  
  <!-- Preload critical CSS first -->
<link rel="preload" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<link rel="preload" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/bootstrap-extended.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">

<!-- Load non-critical CSS with print media trick -->
<link rel="stylesheet" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/components.min.css" media="print" onload="this.media='all'">

<!-- Fallback for browsers without JavaScript -->
<noscript>
  <link rel="stylesheet" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/bootstrap-extended.min.css">
  <link rel="stylesheet" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/components.min.css">
</noscript>

  <!-- Defer non-critical CSS -->
  <noscript>
    <!-- Fallback for when JS is disabled -->
    <link rel="stylesheet" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/bootstrap.css">
    <link rel="stylesheet" href="<?=$system->getCurrentPageURL(false)?>/app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/components.css">
  </noscript>

  <!-- Load remaining stylesheets with low priority -->
  <link rel="preload" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/bootstrap-extended.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/colors.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/themes/dark-layout.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/themes/bordered-layout.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="<?=$system->getCurrentPageURL(false)?>/app-assets/css/themes/semi-dark-layout.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  
  <!-- Page-specific CSS (load after DOMContentLoaded) -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Load remaining CSS files
      const loadCSS = (href) => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        document.head.appendChild(link);
      };

      // Page CSS
      loadCSS('<?=$system->getCurrentPageURL(false)?>/app-assets/css/core/menu/menu-types/horizontal-menu.css');
      loadCSS('<?=$system->getCurrentPageURL(false)?>/app-assets/css/pages/dashboard-ecommerce.css');
      loadCSS('<?=$system->getCurrentPageURL(false)?>/app-assets/css/plugins/charts/chart-apex.css');
      loadCSS('<?=$system->getCurrentPageURL(false)?>/app-assets/css/plugins/extensions/ext-component-toastr.css');
      loadCSS('<?=$system->getCurrentPageURL(false)?>/app-assets/vendors/css/charts/apexcharts.css');
      loadCSS('<?=$system->getCurrentPageURL(false)?>/app-assets/vendors/css/extensions/toastr.min.css');
      
      // Conditional CSS
      if(document.querySelector('.multiselect')) {
        loadCSS('<?=$system->getCurrentPageURL(false)?>/app-assets/css/jquery.multiselect.css');
      }
      
      // Font Awesome (if needed)
      if(document.querySelector('.fa')) {
        loadCSS('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css');
      }
      
      // Custom CSS
      loadCSS('<?=$system->getCurrentPageURL(false)?>/assets/css/style.css');
      
      // Make body visible after CSS loads
      document.body.style.visibility = 'visible';
      document.body.style.opacity = 1;
    });
  </script>
</head>