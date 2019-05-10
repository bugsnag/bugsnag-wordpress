<div class="wrap">
  <h2>Bugsnag Settings</h2>
  <p>
    Bugsnag automatically detects errors &amp; crashes on your WordPress site, plugins &amp; themes.
  </p>
  <p>
    Errors are sent to your <a href="https://bugsnag.com">Bugsnag Dashboard</a> for you to view and debug, and we'll also notify you by email, chat, sms or create a ticket in your issue tracking system if you use one. We'll also show you exactly how many times each error occurred, and how many users were impacted by each crash.
  </p>

  <?php if (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network($this->pluginBase)) { ?>
  <form method='post'>
  <?php } else { ?>
  <form method="post">
  <?php } ?>
    <?php if(empty($this->apiKey)) { ?>

    <!-- API Key Prompt -->
    <div style="max-width: 560px; border: 1px solid #e6db55; padding: 0 10px 12px; background: #fffbcc">
      <h3>Please configure your Bugsnag API Key to enable this plugin</h3>

      <p>
        Sign up for a <a href="https://bugsnag.com/user/sign_up">Bugsnag Account</a> and create a project with type <i>WordPress</i>,<br>
        you'll then be shown your API Key, which you should paste here:
      </p>

      <div style="margin-bottom: 10px;">
        <input type="text" id="bugsnag_api_key" name="bugsnag_api_key" style="width: 80%; float: left;" placeholder="Bugsnag API Key" autofocus="autofocus" />
        <input type="submit" class="button-primary" value="<?php _e('Save') ?>" style="width: 15%; float: right;" />
      </div>

      <div style="clear: both"></div>
    </div>

    <?php } else { ?>

    <!-- Full Settings Form -->
    <table class="form-table">
      <!-- API Key -->
      <tr valign="top">
        <th scope="row">
          <label for="bugsnag_api_key">Bugsnag API Key</label>
        </th>
        <td>
          <input type="text" id="bugsnag_api_key" name="bugsnag_api_key" value="<?php echo $this->apiKey ?>" class="regular-text code" /><br>

          <p class="description">
            You can find your API Key on your <a href="https://bugsnag.com">Bugsnag Dashboard</a>.
          </p>
        </td>
      </tr>

      <!--  $selected = (get_option('start_of_week') == $day_index) ? 'selected="selected"' : ''; -->
      <tr valign="top">
        <th>
          <label for="bugsnag_notify_severities">Notify Bugsnag About</label>
        </th>
        <td>
          <select name="bugsnag_notify_severities" id="bugsnag_notify_severities">
            <?php $this->renderOption("Crashes &amp; errors", "fatal,error", $this->notifySeverities); ?>
            <?php $this->renderOption("Crashes, errors &amp; warnings", "fatal,error,warning", $this->notifySeverities); ?>
            <?php $this->renderOption("Crashes, errors, warnings &amp; info messages", "fatal,error,warning,info", $this->notifySeverities); ?>
          </select>
        </td>
      </tr>

      <!-- Filter Fields -->
      <tr valign="top">
        <th>
          <label for="bugsnag_filterfields">Bugsnag Field Filter</label>
        </th>
        <td>
          <textarea id="bugsnag_filterfields" name="bugsnag_filterfields" class="regular-text filterfields"  style="width: 355px; height: 150px;"><?php echo $this->filterFields; ?></textarea>
          <p class="description" style="max-width: 400px">
            The information to remove from Bugsnag reports, one per line. Use this if you want to ensure you don't send sensitive data such as passwords, and credit card numbers to our servers.

          </p>
        </td>
      </tr>
    </table>

    <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
      <input type="submit" id="bugsnag-test" class="button-secondary" value="<?php _e('Test Bugsnag') ?>" />
    </p>

    <?php } ?>

    <!-- Common form stuff -->
    <?php wp_nonce_field('update-options'); ?>
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="bugsnag_api_key,bugsnag_notify_severities,bugsnag_filterfields" />
  </form>
</div>

<script type="text/javascript" >
jQuery(document).ready(function($) {

    $('#bugsnag-test').click(function (e) {
        e.stopPropagation();
        e.preventDefault();

        var data = {
            action: 'test_bugsnag',
            bugsnag_api_key: $('#bugsnag_api_key').val(),
            bugsnag_notify_severities: $('#bugsnag_notify_severities').val(),
            bugsnag_filterfields: $('#bugsnag_filterfields').val()
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.post(ajaxurl, data, function(response) {
            alert('Sent notification. Visit https://bugsnag.com/ to see it in your dashboard');
        });

    });
});
</script>
