<script>

    function statusChangeCallback(response) {
        if (response.status === 'connected') {
            document.getElementById('message-connected').style.display = "block";
            document.getElementById('message-not-connected').style.display = "none";
        }
    }

    window.fbAsyncInit = function() {
        FB.init({
            appId      : '<?= $fbc_app_id ?>',
            xfbml      : true,
            version    : 'v2.5'
        });

        FB.getLoginStatus(function(response) {
            statusChangeCallback(response);
        });
    };

    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

</script>

<style>
    .checkbox-checked:before {
        display: inline;
        color: #7baf55;
        content: '\e801';
        position: relative;
        font-family: 'solspace-fa';
    }

    .checkbox-not-checked:before {
        display: inline;
        color: #bc4848;
        content: '\e802';
        position: relative;
        font-family: 'solspace-fa';
    }

    .setting-field {
        display: inline;
    }
</style>

<div class="box">
    <h1><?= lang('diagnostics_exp'); ?></h1>
    <form class="settings">
        <fieldset class="col-group">
            <div class="setting-txt col w-8">
                <h3><?= lang('api_credentials_present'); ?></h3>
                <em><?= lang('api_credentials_present_exp'); ?></em>
            </div>
            <div class="setting-field col w-8 last">
                <span class="<?= $credentials_present ? 'checkbox-checked' : 'checkbox-not-checked' ?>">
                    <?= $api_credentials_present; ?>
                </span>
            </div>
        </fieldset>
        <fieldset class="col-group">
            <div class="setting-txt col w-8">
                <h3><?= lang('logged_in_to_facebook'); ?></h3>
                <em><?= lang('logged_in_to_facebook_exp'); ?></em>
            </div>
            <div class="setting-field col w-8 last">
                <fb:login-button autologoutlink="true" onlogin="window.location.reload()"></fb:login-button>
            </div>
        </fieldset>
        <fieldset class="col-group">
            <div class="setting-txt col w-8">
                <h3><?= lang('api_successful_connect'); ?></h3>
                <em><?= lang('api_successful_connect_exp'); ?></em>
            </div>
            <div class="setting-field col w-8 last">
                <div id="message-connected" class="checkbox-checked" style="display: none;">
                    <?= $api_successful_connect ?>
                </div>
                <div id="message-not-connected" class="checkbox-not-checked">
                    <?= $api_not_connected; ?>
                </div>
            </div>
        </fieldset>
    </form>
</div>
