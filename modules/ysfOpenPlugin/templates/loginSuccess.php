<div class="login middle">

  <?php if ($form->hasErrors()): ?>
  <div class="yui-b">
    <?php echo $form->renderGlobalErrors(); ?>
    <?php echo $form['_csrf_token']->renderError(); ?>
    <?php echo $form['identity']->renderError(); ?>
  </div>
  <?php endif; ?>

  <div class="yui-b">
    <form action="<?php echo url_for('ysfOpenPlugin/login'); ?>" method="post">
      <fieldset>
        <legend><?php echo __('OpenID'); ?></legend>
        <input type="hidden" name="_csrf_token" value="<?php echo $form->getCSRFToken(); ?>" />
        <label for="identity"><?php echo __('Login with OpenID'); ?></label>
        <ul>
          <li><?php echo $form['identity']; ?><input type="submit" value="<?php echo __('Login'); ?>" /></li>
        </ul>
      </fieldset>
    </form>
  </div>

  <div class="yui-b">
    <a href="<?php echo url_for('ysfOpenPlugin/login'); ?>?_csrf_token=<?php echo $form->getCSRFToken(); ?>&amp;identity=https://me.yahoo.com" title="<?php echo __('Yahoo! OpenID'); ?>">
      <img src="http://www.yahoo.com/favicon.ico" alt="<?php echo __('Yahoo! OpenID'); ?>" height="16" width="16" />
      <?php echo __('Sign in with Yahoo! ID'); ?>
    </a>
  </div>

  <div class="yui-b">
    <a href="<?php echo url_for('ysfOpenPlugin/login'); ?>?_csrf_token=<?php echo $form->getCSRFToken(); ?>&amp;identity=https://www.google.com/accounts/o8/id" title="<?php echo __('Google OpenID'); ?>">
      <img src="http://www.google.com/favicon.ico" alt="<?php echo __('Google OpenID'); ?>" height="16" width="16" />
      <?php echo __('Sign in with a Google Account'); ?>
    </a>
  </div>

  <div class="yui-b">
    <h1><?php echo __("Don't have an OpenID?"); ?></h1>

    <a href="https://www.myopenid.com/affiliate_signup?openid.sreg.required=nickname,fullname,email&amp;openid.sreg.optional=dob,gender,postcode,country,language,timezone&amp;affiliate_id=<?php echo sfConfig::get('app_myopenid_affiliate_id'); ?>">
      <img src="http://www.myopenid.com/favicon.ico" alt="<?php echo __('MyOpenID'); ?>" height="16" width="16" />

      <?php echo __('Get one from myOpenID!'); ?>
    </a>

  </div>
</div>
