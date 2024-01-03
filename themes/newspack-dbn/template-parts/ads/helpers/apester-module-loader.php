<?php
/**
 * Only load apester ES6 module dynamically if CMP consent is given
 *
 * @usedby themes/newspack-dbn/blocks/apester-poll/apester-poll.json
 * @usedby themes/newspack-dbn/template-parts/ads/apester-strip.php
 *
 */
?>

<script>
	Spark.cmd.push(function () {
		Spark.addEventListener('consentReceived', function (e) {
			if ((e.eventStatus === 'useractioncomplete' || e.eventStatus === 'tcloaded') && e.vendor.consents[354] === true) {
				let h = document.getElementsByTagName('head')[0],
					s = document.createElement('script');

				s.type = 'module';
				s.src = 'https://sdk.apester.com/web-sdk.core.min.js';

				h.appendChild(s);
			}
		})
	});
</script>
