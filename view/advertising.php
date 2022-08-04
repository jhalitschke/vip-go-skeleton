<?php 

//&& e.vendor.consents[1016] === true

function get_headerbidding_scripts(){
    //$return_html = "var pbjs = pbjs || {};".PHP_EOL;
    $return_html= 'hb_targets = \'\'; 
            Spark.cmd.push(function() {
                Spark.addEventListener(\'consentReceived\', function(e) { 
                    if ((e.eventStatus === \'useractioncomplete\' || e.eventStatus === \'tcloaded\') && e.vendor.consents[1016] === true) {
                            initPlayers(hb_targets,true);
                    } else {
                            initPlayers(hb_targets,false);
                    }
                }); 
        }); 
    '.PHP_EOL;  

    $return_html.= 'function getBidResponse(bidResponses, timedOut, auctionId) {
        hb_targets = \'\';
        console.log("targets:"+bidResponses); 
        if (typeof bidResponses !== \'undefined\' && typeof bidResponses.adTagUrl !== \'undefined\' ) {
            var adtag = bidResponses.adTagUrl.split(\'&\'),
                hb_targets = \'\';
            adtag.forEach ((a, i) => {
                if (/cust_params=/.test(a)) {
                    hb_targets = a.split(\'=\')[1];
                }
            });
            console.log("targets:"+hb_targets); 
		}
	};';
    return $return_html;
} 
?> 