<!DOCTYPE html>
<html>
<head>
    <title><?php 
        $this->load->helper('demo');
        echo !is_on_demo_host() ?  $this->config->item('company').' -- '.lang('common_powered_by').' '.$this->config->item('branding')['name'] : 'Demo - '.$this->config->item('branding')['name'].' | Easy to use Online POS Software' ?></title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <base href="<?php echo base_url();?>" />
    <link rel="icon" href="<?php echo base_url();?>favicon_<?php echo $this->config->item('branding_code');?>.ico" type="image/x-icon"/>
    
    <?php 
    $this->load->helper('assets');
    foreach(get_css_files() as $css_file) { ?>
        <link rel="stylesheet" type="text/css" href="<?php echo base_url().$css_file['path'].'?'.ASSET_TIMESTAMP;?>" />
    <?php } ?>

    <script src="<?php echo base_url();?>assets/js/jquery.js?<?php echo ASSET_TIMESTAMP; ?>" type="text/javascript" language="javascript" charset="UTF-8"></script>
    <style type="text/css">
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        html, body {
            height: 100%;
            width: 100%;
            overflow-x: hidden;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-image: url('<?php echo base_url();?>assets/img/vencedor-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .flip-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 30px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-logo img {
            max-width: 200px;
            height: auto;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #2c3e50;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1a252f;
        }

        .bottom_info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .version {
            margin-top: 20px;
            font-size: 12px;
            text-align: center;
            color: #666;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function() {
            if ($("#username").val() == '') {
                $("#username").focus();                   
            } else {
                $("#password").focus();
            }
                
            $(".checkForUpdate").click(function(event) {
                event.preventDefault();
                $('#spin').removeClass('hidden');
        
                $.getJSON($(this).attr('href'), function(update_available) {
                    $('#spin').addClass('hidden');
                    if(update_available) {
                        $(".checkForUpdate").parent().html(<?php echo json_encode(lang('common_update_available').' <a href="http://'.$this->config->item('branding')['domain'].'/downloads.php" target="_blank">'.lang('common_download_now').'</a>');?>);
                    } else {
                        $(".checkForUpdate").parent().html(<?php echo json_encode(lang('common_not_update_available')); ?>);
                    }
                });
            });
        });
    </script>
    <?php
    $this->load->helper('demo');
    if (is_on_demo_host()) { ?>        
        <script src="//<?php echo $this->config->item('branding')['domain']; ?>/js/iframeResizer.contentWindow.min.js"></script>
    <?php } ?>        
</head>
<body>    
    <?php if(isset($announcement)) { ?>
        <div class="text-center">
            <?php echo $announcement; ?>
        </div>
    <?php } ?>
    
    <div class="flip-container">
        <div class="flipper">
            <div class="front">
                <div class="holder">
                    <?php if ($ie_browser_warning) { ?>
                        <div class="alert alert-danger">
                            <strong><?php echo lang('login_unsupported_browser');?></strong>
                        </div>
                    <?php } ?>

                    <div class="login-logo">
                        <?php echo img(array('src' => $this->Appconfig->get_logo_image())); ?>
                    </div> 
                    
                    <p>
                        <?php echo lang('login_welcome_message'); ?>
                        
                        <?php if(($this->config->item('sso_protocol') == 'saml' && $this->config->item('saml_single_sign_on_service')) || ($this->config->item('sso_protocol') == 'oidc' && $this->config->item('oidc_host'))) { ?>                            
                            <button onclick="window.location='<?php echo site_url($this->config->item('sso_protocol') == 'saml' ? 'login/samlassertionconsumerservice?sso' : 'login/oidc') ?>'" type="button" class="btn btn-primary"><?php echo lang('common_sso_login'); ?></button>
                        <?php } ?>
                        
                        <?php if (is_on_demo_host()) { ?>
                            <h2 class="text-center"><?php echo lang('login_press_login_to_continue'); ?></h2>
                        <?php } ?>
                    </p>
                    
                    <?php if (!$this->config->item('only_allow_sso_logins')) { ?>
                        <?php echo form_open('login?continue='.rawurlencode($this->input->get('continue') ? $this->input->get('continue') : ''), array('class' => 'form login-form', 'id'=>'loginform', 'autocomplete'=> 'off')) ?>            
                        
                        <?php if (validation_errors()) { ?>
                            <div class="alert alert-danger">
                                <strong><?php echo lang('common_error'); ?></strong>
                                <?php echo validation_errors(); ?>
                            </div>
                        <?php } ?>
                        
                        <?php echo form_input(array(
                            'name'=>'username', 
                            'id'=>'username', 
                            'value'=> $username,
                            'class'=> 'form-control',
                            'placeholder'=> lang('login_username'),
                            'size'=>'20')); 
                        ?>

                        <?php echo form_password(array(
                            'name'=>'password', 
                            'id' => 'password',
                            'value'=>$password,
                            'class'=>'form-control',
                            'placeholder'=> lang('login_password'),
                            'size'=>'20')); 
                        ?>
                
                        <div class="bottom_info">                            
                            <a href="<?php echo site_url('login/reset_password') ?>" class="flip-link to-recover"><?php echo lang('login_reset_password').'?'; ?></a>
                            
                            <?php if (!is_on_phppos_host()) { ?>
                                <span><?php echo anchor('login/is_update_available', lang('common_check_for_update'), array('class' => 'checkForUpdate')); ?></span>
                                <span id="spin" class="hidden">
                                    <i class="ion ion-load-d ion-spin"></i>
                                </span>
                            <?php } ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><?php echo lang('login_login'); ?></button>
                        <?php echo form_close() ?>  
                    <?php } ?>
                    
                    <div class="version">
                        <p>
                            <span class="badge bg-success"><?php echo APPLICATION_VERSION; ?></span> <?php echo lang('common_built_on'). ' '.BUILT_ON_DATE;?>
                        </p>
                            
                        <?php if (isset($trial_on) && $trial_on === true && !isset($trial_over)) { ?>
                            <div class="alert alert-success">
                                <?php echo lang('login_trail_info'). ' '.date(get_date_format(), strtotime($cloud_customer_info['trial_end_date'])).'. '.lang('login_trial_info_2'); ?>
                            </div>
                            <a class="btn btn-success" href="https://<?php echo $this->config->item('branding')['domain']; ?>/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>" target="_blank"><?php echo lang('common_update_billing_info');?></a>
                        <?php } ?>
                        
                        <?php if (isset($subscription_payment_failed) && $subscription_payment_failed === true) { ?>
                            <div class="alert alert-danger">
                                <?php echo lang('login_payment_failed_text'); ?>
                            </div>
                            <a class="btn btn-success" href="https://<?php echo $this->config->item('branding')['domain']; ?>/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>" target="_blank"><?php echo lang('common_update_billing_info');?></a>
                        <?php } ?>
                                
                        <?php if (isset($subscription_cancelled_within_5_days) && $subscription_cancelled_within_5_days === true) { ?>
                            <div class="alert alert-danger">
                                <?php echo lang('login_resign_text'); ?>
                            </div>
                            <a class="btn btn-success" href="https://<?php echo $this->config->item('branding')['domain']; ?>/update_billing.php?store_username=<?php echo $cloud_customer_info['username'];?>" target="_blank"><?php echo lang('login_resignup');?></a>
                        <?php } ?>
                    </div>                
                </div>
            </div>          
        </div>      
    </div>
    
    <?php if ($this->input->get('demologin')) { ?>
        <script>    
            $("#loginform").submit();
        </script>
    <?php } ?>
</body>
</html>