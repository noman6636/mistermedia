<script>
    // Load non-critical CSS for print media
    window.addEventListener('load', function() {
      const printCSS = document.createElement('link');
      printCSS.rel = 'stylesheet';
      printCSS.href = '<?=$system->getCurrentPageURL(false)?>/app-assets/css/printed.min.css';
      printCSS.media = 'print';
      document.head.appendChild(printCSS);
    });
  </script>
<!-- Critical JS that must load early -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="app-assets/js/core/app-menu.js" defer></script>
<script src="app-assets/js/core/app.js" defer></script>

<!-- Main optimized script block -->
<script>
// ==============================================
// OPTIMIZED SCRIPT LOADER
// ==============================================
function loadScript(src, isDefer = true) {
  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = src;
    if(isDefer) script.defer = true;
    script.onload = resolve;
    script.onerror = reject;
    document.body.appendChild(script);
  });
}

// ==============================================
// DOM READY HANDLER
// ==============================================
document.addEventListener('DOMContentLoaded', function() {
  // Initialize essential components
  initEssentialComponents();
  
  // Load remaining scripts based on page needs
  loadConditionalScripts();
});

// ==============================================
// ESSENTIAL COMPONENTS INIT
// ==============================================
function initEssentialComponents() {
  // Select2 initialization if needed
  if(document.querySelector('.select2')) {
    loadScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js')
      .then(() => $('.select2').select2());
  }

  // Feather icons if needed
  if(typeof feather !== 'undefined' && document.querySelector('[data-feather]')) {
    feather.replace({ width: 14, height: 14 });
  }

  // Checkbox handling
  initCheckboxHandlers();
}

// ==============================================
// CONDITIONAL SCRIPT LOADING
// ==============================================
function loadConditionalScripts() {
  // DataTables and dependencies
  if(document.querySelector('.dataTable')) {
    const datatableScripts = [
      'app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js',
      'app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js',
      'app-assets/js/scripts/tables/table-datatables-basic.js'
    ];
    
    datatableScripts.forEach(src => loadScript(src));
  }

  // Multiselect if needed
  if(document.querySelector('.multiselect')) {
    loadScript('app-assets/js/jquery.multiselect.js')
      .then(initMultiselect);
  }

  // Export functionality if needed
  if(document.querySelector('.export-buttons')) {
    const exportScripts = [
      'app-assets/vendors/js/tables/datatable/jszip.min.js',
      'app-assets/vendors/js/tables/datatable/pdfmake.min.js',
      'app-assets/vendors/js/tables/datatable/vfs_fonts.js',
      'app-assets/vendors/js/tables/datatable/buttons.html5.min.js',
      'https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js'
    ];
    exportScripts.forEach(src => loadScript(src));
  }

  // jQuery UI if needed
  if(document.querySelector('.has-ui-components')) {
    loadScript('https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js');
  }
}

// ==============================================
// COMPONENT INITIALIZERS
// ==============================================
function initCheckboxHandlers() {
  const selectAll = document.getElementById('selectall');
  if(!selectAll) return;

  selectAll.addEventListener('click', function() {
    const checkAll = this.checked;
    document.querySelectorAll('.case').forEach(el => {
      el.checked = checkAll;
    });
  });

  document.querySelectorAll('.case').forEach(el => {
    el.addEventListener('click', function() {
      const cases = document.querySelectorAll('.case');
      selectAll.checked = cases.length === document.querySelectorAll('.case:checked').length;
    });
  });
}

function initMultiselect() {
  if(document.getElementById('account_id_io')) {
    $('#account_id_io').multiselect({
      columns: 1,
      placeholder: 'Select Accounts',
      search: true,
      selectAll: true
    });
  }
  
  if(document.getElementById('item_id_o')) {
    $('#item_id_o').multiselect({
      columns: 1,
      placeholder: 'Select Items',
      search: true,
      selectAll: true
    });
  }
}

// ==============================================
// WINDOW FUNCTIONS
// ==============================================
function initWindowFunctions() {
  window.createPop = function(url, name) {
    const newwindow = window.open(url, name, 'width=760,height=540,toolbar=0,menubar=0,location=0');
    if (window.focus) newwindow.focus();
  };

  window.generateSentLedger = function(type) {
    const account_id = document.getElementById('account_id_sl').value;
    const frmdate = document.getElementById('frmdate_sl').value;
    const todate = document.getElementById('todate_sl').value;
    
    if (type == 1) {
      window.open(`printSentLedger.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}`, 
                "Generate Sent Ledger", 'width=1024,height=720,toolbar=0,menubar=0,location=0');
    } else {
      location.href = `csvSentLedger.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}`;
    }
  };

  window.generateLedger = function(typeview) {
    const account_id = document.getElementById('account_id_l').value;
    const frmdate = document.getElementById('frmdate_l').value;
    const todate = document.getElementById('todate_l').value;
    const type = document.getElementById('type_l').value;

    if (typeview == 1) {
      window.open(`printLedger.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}&type=${type}`, 
                "Generate Ledger", 'width=1024,height=720,toolbar=0,menubar=0,location=0');
    } else {
      location.href = `csvLedger.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}&type=${type}`;
    }
  };

  window.generateOrders = function(typeview) {
    const account_id = document.getElementById('account_id_o').value;
    const frmdate = document.getElementById('frmdate_o').value;
    const todate = document.getElementById('todate_o').value;
    const type = document.getElementById('type_o').value;
    
    if (typeview == 1) {
      window.open(`printOrders.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}&type=${type}`, 
                "Order List", 'width=1024,height=720,toolbar=0,menubar=0,location=0');
    } else {
      location.href = `csvOrders.php?account_id=${account_id}&frmdate=${frmdate}&todate=${todate}&type=${type}`;
    }
  };

  window.generateItemOrders = function(typeview) {
    const item_id = $('#item_id_o').val();
    const account_id = $('#account_id_io').val();
    const frmdate = document.getElementById('frmdate_io').value;
    const todate = document.getElementById('todate_io').value;
    const type = document.getElementById('type_io').value;

    if (typeview == 1) {
      const param = {
        'item_id': item_id,
        'frmdate': frmdate,
        'todate': todate,
        'account_id': account_id,
        'type': type
      };
      OpenWindowWithPost('printItemLedger', "width=1024,height=720,toolbar=0,menubar=0,location=0", "Items Order List", param);
    } else {
      location.href = `csvItemLedger.php?item_id=${item_id}&frmdate=${frmdate}&todate=${todate}&account_id=${account_id}&type=${type}`;
    }
  };

  window.print = function() {
    const values = [];
    document.querySelectorAll('input[name="case[]"]:checked').forEach(el => {
      values.push(el.value);
    });

    if (values.length > 0) {
      fetch("getPrintData.php?value=" + values.join(','), {
        method: 'POST',
        body: JSON.stringify(values)
      })
      .then(response => response.text())
      .then(html => {
        document.getElementById('printableArea').innerHTML = html;
        const table = document.getElementById('ptbl');
        TableToExcel.convert(table, {
          name: `export.xlsx`,
          sheet: { name: 'Sheet 1' }
        });
      })
      .catch(error => {
        new Noty({
          text: "Some error while registration.",
          timeout: 15000,
          layout: 'bottomRight',
          theme: "metroui",
          type: 'warning',
          killer: true
        }).show();
      });
    } else {
      alert("Please select at least one order.");
    }
  };

  window.OpenWindowWithPost = function(url, windowoption, name, params) {
    const form = document.createElement("form");
    form.method = "post";
    form.action = url;
    form.target = name;
    
    Object.entries(params).forEach(([key, value]) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = value;
      form.appendChild(input);
    });
    
    document.body.appendChild(form);
    window.open("post.htm", name, windowoption);
    form.submit();
    document.body.removeChild(form);
  };
}

// Initialize window functions if their elements exist
if(document.getElementById('account_id_sl') || 
   document.getElementById('account_id_l') || 
   document.getElementById('account_id_o')) {
  initWindowFunctions();
}
</script>