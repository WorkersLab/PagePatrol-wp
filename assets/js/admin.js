jQuery(document).ready(function ($) {
  function refreshStatus() {
    $.ajax({
      url: pagepatrol.ajax_url,
      type: 'POST',
      data: {
        action: 'pagepatrol_refresh_status',
        nonce: pagepatrol.nonce
      },
      success: function (response) {
        if (response.success) {
          updateDashboard(response.data);
        }
      },
      error: function () {
        console.error('Failed to refresh status');
      }
    });
  }

  function updateDashboard(data) {
    const status = data.status;
    const logs = data.logs;

    // Update status indicator
    $('.status-text').text(status.current_status.toUpperCase());

    // Update stats
    $('.uptime-24h').text(status.uptime_24h + '%');
    $('.uptime-7d').text(status.uptime_7d + '%');
    $('.last-checked').text(new Date(status.last_checked_at).toLocaleString());

    // Update incidents table
    const tbody = $('#pagepatrol-incidents tbody');
    tbody.empty();

    logs.forEach(log => {
      tbody.append(`
                <tr>
                    <td>${new Date(log.created_at).toLocaleString()}</td>
                    <td>${log.succeed ? 'Success' : 'Failed'}</td>
                    <td>${log.response_time ? log.response_time + 'ms' : '-'}</td>
                </tr>
            `);
    });

    // Remove loading states
    $('.loading').removeClass('loading');
  }

  // Initial load
  if ($('.pagepatrol-dashboard').length) {
    refreshStatus();
    // Refresh every 5 minutes
    setInterval(refreshStatus, 5 * 60 * 1000);
  }
});
