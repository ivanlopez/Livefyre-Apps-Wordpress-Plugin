<?php
if (function_exists( 'home_url' )) {
    $home_url=home_url();
} else {
    $home_url=get_option('home');
}

$site_id=get_option('livefyre_site_id',''); 

if (isset($_GET['status'])) {
    update_option('livefyre_import_status', $_GET['status']);
    if (isset($_GET['message'])) {
        update_option('livefyre_import_message', urldecode($_GET['message']));
    }
}
?>

<script type="text/javascript">
/*{"status": "created", "conversations_processed_count": 0, "last_modified": "2011-03-01T07:54:31", "aborted": false, "messages": "2011-03-01 07:54:31.910405\nCreated\n--------------------------------------------------\n", "conversation_count": 0, "import_queue": 1}*/
//Lightweight JSONP fetcher - www.nonobtrusive.com
var JSONP=(function(){var a=0,c,f,b,d=this;function e(j){var i=document.createElement("script"),h=false;i.src=j;i.async=true;i.onload=i.onreadystatechange=function(){if(!h&&(!this.readyState||this.readyState==="loaded"||this.readyState==="complete")){h=true;i.onload=i.onreadystatechange=null;if(i&&i.parentNode){i.parentNode.removeChild(i)}}};if(!c){c=document.getElementsByTagName("head")[0]}c.appendChild(i)}function g(h,j,k){f="?";j=j||{};for(b in j){if(j.hasOwnProperty(b)){f+=b+"="+j[b]+"&"}}var i="json"+(++a);d[i]=function(l){k(l);d[i]=null;try{delete d[i]}catch(m){}};e(h+f+"callback="+i);return i}return{get:g}}());
var livefyre_wp_plugin_polled = false;

function formatDate(date_) {
    var formatted = 'Completed on ';
    formatted += d.getMonth() + 1 + '/' + d.getDate() + '/' + d.getFullYear();
    formatted += ' at ' + (d.getHours() > 12 ? d.getHours() - 12 : d.getHours()) + ':' + d.getMinutes() + (d.getHours() > 12 ? 'pm' : 'am');
    return formatted;
}

function checkStatusLF(){
    JSONP.get( '<?php echo $this->lf_core->quill_url ?>/import/wordpress/<?php echo get_option("livefyre_site_id") ?>/status', {param1:'none'}, function(data){
        console.log('REPSONSE:', data);
        var status = data['status'],
            loc = '?page=livefyre';

        if (status === 'aborted' || status === 'failed') {
            // Statuses that signal a stopping point in the process.
            loc += '&status=error';
            if (data['import_failure'] && data['import_failure']['message']) {
                loc += '&message=' + data['import_failure']['message'];
            }
            // TODO: UNCOMMENT
            //window.location.href = loc;
        } else if (status === 'complete') {
            // TODO: UNCOMMENT
            //window.location.href = '&status=complete&message=' + formatDate(new Date(data['last_modified']));
        } else {
            // Update the process bar.
            var conv_count = data['conversation_count'],
                convs_processed = data['conversations_processed_count'],
                percentage_complete = 0;
            if (typeof(conv_count) !== 'number') {
                conv_count = 0;
            }
            if (typeof(convs_processed) !== 'number') {
                convs_processed = 0;
            }
            document.getElementById('fyre-progress-title').innerHTML = convs_processed + '/' + conv_count + ' Conversations';
            percentage_complete = convs_processed / conv_count;
            if (!isNaN(percentage_complete)) {
                var prog = document.getElementById('fyre-progress-bar');
                prog.style.width = percentage_complete + '%';
                prog.style.border = '1px solid #58891c';
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
if (get_option('livefyre_import_status','') == 'started') {
    //only report status of the import
    ?>
    <script type="text/javascript">
        livefyre_start_ajax();
    </script>
    <?php
}
?>


<div class="fyre-settings">
    <div class="fyre-container-base">
        <div class="fyre-container">
            <ul class="fyre-list">
                <li class="fyre-list">Livefyre Site ID: <strong><?php echo get_option('livefyre_site_id') ?></strong></li>
                <li class="fyre-list">Livefyre Site Key: <strong><?php echo get_option('livefyre_site_key') ?></strong></li>
            </ul>
        </div>
    </div>

    <?php
    $import_status = get_option('livefyre_import_status','');
    if (get_option('livefyre_site_id','') == '') {
        // Don't allow the status sections if there isn't a site.
    } else if ($import_status == '') {
        ?>
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
    <?php
    } else if ($import_status == 'started') {
        ?>
        <div class="fyre-container-base" id="fyre-in-progress">
            <div class="fyre-container">
                <div class="fyre-header">
                    <div class="fyre-status slate"></div>
                    <span id="fyre-progress-title" class="fyre-title"></span>
                    <span class="fyre-subtext">
                        Import your existing WordPress comments so that they show up in Livefyre Comments and in the Livefyre Admin.
                    </span>
                    <div class="fyre-progress-bar-container">
                        <div id="fyre-progress-bar" class="fyre-progress-bar"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    } else if ($import_status == 'error') {
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
    } else if ($import_status == 'complete') {
        ?>
        <div class="fyre-container-base" id='fyre-success'>
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
    }
    ?>
</div>
<style>
    <?php echo file_get_contents( dirname( __FILE__ ) . '/settings-template.css' )  ?>
</style>
