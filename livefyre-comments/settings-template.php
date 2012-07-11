<?php
global $livefyre_quill_url;
if (function_exists( 'home_url' )) {
    $home_url=home_url();
} else {
    $home_url=get_option('home');
}

$site_id=get_option('livefyre_blogname',''); 

?>

<script type="text/javascript">
/*{"status": "created", "conversations_processed_count": 0, "last_modified": "2011-03-01T07:54:31", "aborted": false, "messages": "2011-03-01 07:54:31.910405\nCreated\n--------------------------------------------------\n", "conversation_count": 0, "import_queue": 1}*/
//Lightweight JSONP fetcher - www.nonobtrusive.com
var JSONP=(function(){var a=0,c,f,b,d=this;function e(j){var i=document.createElement("script"),h=false;i.src=j;i.async=true;i.onload=i.onreadystatechange=function(){if(!h&&(!this.readyState||this.readyState==="loaded"||this.readyState==="complete")){h=true;i.onload=i.onreadystatechange=null;if(i&&i.parentNode){i.parentNode.removeChild(i)}}};if(!c){c=document.getElementsByTagName("head")[0]}c.appendChild(i)}function g(h,j,k){f="?";j=j||{};for(b in j){if(j.hasOwnProperty(b)){f+=b+"="+j[b]+"&"}}var i="json"+(++a);d[i]=function(l){k(l);d[i]=null;try{delete d[i]}catch(m){}};e(h+f+"callback="+i);return i}return{get:g}}());
var livefyre_wp_plugin_polled=false;
function checkStatusLF(){
    JSONP.get( '<?php echo $livefyre_quill_url ?>/import/wordpress/<?php echo get_option("livefyre_blogname") ?>/status', {param1:'none'}, function(data){
        if (data['status']=='does-not-exist' || data['status']=='complete') {
            clearInterval(checkStatusInterval);
            document.getElementById('livefyre_results').innerHTML='<br/>Import status: completed';
            document.getElementById('working').style.display = 'none';
            document.getElementById('showresults').style.display = 'none';
        } else {
            if ((data['status']=='failed' || data['aborted']) && livefyre_wp_plugin_polled) {
                //existing job failed. retry!
                var _html = '<br/>Import status: failed!';
                if (data['import_failure'] !== undefined){
                    _html += '<br/>Reason: ' + data['import_failure']['message'];
                }
                _html += '<br/><br/>If you have questions, please contact <a href="mailto:support@livefyre.com">support@livefyre.com</a>';
                document.getElementById('livefyre_results').innerHTML = _html;
                document.getElementById('working').style.display = 'none';
                document.getElementById('showresults').style.display = 'none';
                clearInterval(checkStatusInterval);
                return;
            }
            if ( typeof(data['import_queue'])!='undefined' && (parseInt(data['import_queue'])>1 || document.getElementById('show_import_queue').style.display=='') ) {
                document.getElementById('show_import_queue').style.display = '';
            }
            document.getElementById('livefyre_results').innerHTML='Import status: '+(data['status']=='assembling' ? 'assembling - this may take several minutes if you have a lot of comments' : data['status']);
            document.getElementById('showresults').style.display = 'block';
            if (typeof(data['conversations_processed_count'])!='undefined' && parseInt(data['conversations_processed_count'])!=0) {
                document.getElementById('show_conv_processed').style.display = 'block';
                document.getElementById( 'convs_processed' ).innerHTML=data['conversations_processed_count'];
            }
            if (typeof(data['conversation_count'])!='undefined' && parseInt(data['conversation_count'])!=0) {
                document.getElementById('show_conv_count').style.display = 'block';
                document.getElementById( 'convs' ).innerHTML=data['conversation_count'];
            }
        }
        livefyre_wp_plugin_polled=true;
    });
}

function wordpress_start_ajax() {
    window.checkWPStatusInterval=setInterval(
        checkStatusWP, 
        5000
    );
    checkStatusWP();    
}
function livefyre_start_ajax() {
    window.checkStatusInterval=setInterval(
        checkStatusLF, 
        5000
    );
    checkStatusLF();
}
</script>


<?php
if (get_option('livefyre_import_status','')=='started') {
    //only report status of the import
    ?>
    <script type="text/javascript">
        livefyre_start_ajax();
    </script>
    <?php
}
?>



<div class="fyre-container-base">
    <div class="fyre-container">
        <ul class="fyre-list">
            <li class="fyre-list">Livefyre Site ID <input class="fyre-textfield fyre-user" type="text" placeholder="" name="livefyre_site_id" value="<?php echo get_option('livefyre_site_id') ?>"></li>
            <li class="fyre-list">Livefyre Site Key <input class="fyre-textfield fyre-user" type="text" placeholder="" name="livefyre_site_key" value="<?php echo get_option('livefyre_site_key') ?>"></li>
        </ul>
        <div class="fyre-footer"><a href="#" class="green fyre-button">Saved</a></div>
    </div>
</div>
<div class="fyre-container-base" id="fyre-start">
    <div class="fyre-container">
        <div class="fyre-header">
            <div class="fyre-status"></div>

            <span class="fyre-subtext">
                Import your existing WordPress comments so that they show up in Livefyre Comments and in the Livefyre Admin.
            </span>
            <a href="?page=livefyre&livefyre_import_begin=1" class="green fyre-button">Import comments</a>
        </div>
    </div>
</div>

<div class="fyre-container-base" id="fyre-in-progres" style="display:none;">
    <div class="fyre-container">
        <div class="fyre-header">
            <div class="fyre-status slate"></div>
            <span class="fyre-title">12/1200 Conversations</span>
            <span class="fyre-subtext">
                Import your existing WordPress comments so that they show up in Livefyre Comments and in the Livefyre Admin.
            </span>
            <div class="fyre-progress-bar-container">
                <div class="fyre-progress-bar"></div>
            </div>
        </div>
    </div>
</div>
<div class="fyre-container-base" id='fyre-success' style="display:none;">
    <div class="fyre-container">
        <div class="fyre-header">
            <div class="fyre-status green"></div>
            <span class="fyre-title">Successfully Imported</span>
            <span class="fyre-subtext">
                Completed on 6/26/12 at 5:43pm
            </span>
        </div>
    </div>
</div>
<?php
if (get_option('livefyre_import_status','') == 'error') {
    ?>
    <div class="fyre-container-base" id="fyre-failure">
        <div class="fyre-container">
            <div class="fyre-header">
                <div class="fyre-status red"></div>
                <span class="fyre-title">Initialization Error</span>
                <span class="fyre-subtext">
                    <?php echo get_option('livefyre_import_message','') ?>
                </span>
            </div>
        </div>
    </div>
    <?php
} else {
    ?>
    <div class="fyre-container-base" id="fyre-failure" style="display:none;">
        <div class="fyre-container">
            <div class="fyre-header">
                <div class="fyre-status red"></div>
                <span class="fyre-title">Error Text</span>
                <span class="fyre-subtext">
                    Error text
                </span>
            </div>
        </div>
    </div>
    <?php
}
?>

<style>
    <?php echo file_get_contents( dirname( __FILE__ ) . '/settings-template.css' )  ?>
</style>
