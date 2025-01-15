<?php
// Exit if accessed directly
if (!defined("ABSPATH")) {
    exit();
} ?>

<div class="wrap">
    <h1>PagePatrol</h1>

    <?php if (!get_option("pagepatrol_api_key")): ?>
        <!-- API Key Setup -->
        <div class="notice notice-info">
            <p>Welcome to PagePatrol! Let's get started by connecting your site.</p>
        </div>

        <div class="card">
            <h2>Connect Your Site</h2>
            <p>Enter your PagePatrol API key to start monitoring your site.</p>

            <form method="post" action="options.php">
                <?php settings_fields("pagepatrol_settings"); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">API Key</th>
                        <td>
                            <input type="password"
                                   name="pagepatrol_api_key"
                                   class="regular-text"
                                   value="<?php echo esc_attr(
                                       get_option("pagepatrol_api_key")
                                   ); ?>" />
                            <p class="description">
                                Don't have an API key? <a href="https://pagepatrol.net/api_keys" target="_blank">Get one here</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button("Connect Site"); ?>
            </form>
        </div>
    <?php else: ?>
        <!-- Dashboard View -->
        <div class="pagepatrol-dashboard">
            <div class="card">
                <h2>Current Status</h2>
                <div id="pagepatrol-status" class="loading">
                    <div class="status-indicator">
                        <span class="spinner is-active"></span>
                        <span class="status-text">Checking status...</span>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-box">
                            <label>Uptime (24h)</label>
                            <span class="uptime-24h">-</span>
                        </div>
                        <div class="stat-box">
                            <label>Uptime (7d)</label>
                            <span class="uptime-7d">-</span>
                        </div>
                        <div class="stat-box">
                            <label>Last Checked</label>
                            <span class="last-checked">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Recent Incidents</h2>
                <div id="pagepatrol-incidents" class="loading">
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Response Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="placeholder">
                                <td colspan="3">Loading incidents...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="view-all">
                    <a href="https://pagepatrol.net/dashboard" target="_blank" class="button">
                        View Full Dashboard
                    </a>
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>
