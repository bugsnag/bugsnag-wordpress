<div class="wrap">
  <?php screen_icon(); ?>
  <h2>Bugsnag Settings</h2>

  <form method="post" action="options.php"> 
    <?php wp_nonce_field('update-options'); ?>

    <p>
      <a href="https://bugsnag.com">Bugsnag</a> captures crashes and other errors in real-time from your WordPress site, helping you to understand and resolve them as fast as possible.
    </p>

    <p>
      To use Bugsnag on your WordPress site, you'll need to follow these steps:
    </p>

    <ol>
      <li><a href="https://bugsnag.com/user/sign_up">Create an account</a> on bugsnag.com</li>
      <li>Create a Bugsnag Project, choose the "Wordpress" project type</li>
      <li>Copy your Bugsnag API Key, and paste it in the form below</li>
    </ol>

    <h3>Bugsnag Settings</h3>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Bugsnag API Key</th>
        <td><input type="text" name="bugsnag_api_key" value="<?php echo get_option('bugsnag_api_key'); ?>" /></td>
      </tr>
    </table>

    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="bugsnag_api_key" />

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
  </form>
</div>