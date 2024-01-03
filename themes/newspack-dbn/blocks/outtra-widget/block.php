<?php
/**
 * Outtra Script Widget
 *
 */

$script_source  = '';
$iframe_source  = '';
$data_attribute = '';
$div_id         = '';

// prepare the view
if ( block_value( 'widget_type' ) == 'pct' ) {
	// price table
	$script_source  = '//funke.code.outtra.com/outtra-custompctc4.js';
	$iframe_source  = sprintf( '//www.outtra.de/amp/custom_pct/%s/%s', block_value( 'publisher_id' ), block_value( 'widget_id' ) );
	$data_attribute = 'data-custompct';
	$div_id         = sprintf( 'outtra-pct-container-%s', block_value( 'widget_id' ) );
} elseif ( block_value( 'widget_type' ) == 'pdw' ) {
	// product wall
	$script_source  = '//funke.code.outtra.com/outtra-739c4.js';
	$iframe_source  = sprintf( '//www.outtra.de/amp/pdw/%s/%s', block_value( 'publisher_id' ), block_value( 'widget_id' ) );
	$data_attribute = 'data-outtrapwwdgt';
	$div_id         = sprintf( 'outtra-pdw-container-%s', block_value( 'widget_id' ) );
} elseif ( block_value( 'widget_type' ) == 'wtb' ) {
	// where to buy
	$script_source  = sprintf( '//funke.code.outtra.com/outtra-982c4.js?publisher_id=%s&widget_id=%s', block_value( 'publisher_id' ), block_value( 'widget_id' ) );
	$iframe_source  = sprintf( '//www.outtra.de/amp/wtb/%s/%s', block_value( 'publisher_id' ), block_value( 'widget_id' ) );
	$data_attribute = 'data-outtrapcwdgt';
	$div_id         = sprintf( 'outtra-wtb-container-%s', block_value( 'widget_id' ) );
}

if ( amp_is_request() ) {
	// for amp we use amp-iframe (copied from futurezone)
	printf( '<amp-iframe width="200" height="300"
	            		sandbox="allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox" resizable
	            		layout="responsive" src="%s">
						<div overflow tabindex="0" role="button" aria-label="Show more"></div>
						<amp-img width="93" height="19" src="https://imgcdn.outtra.de/cdn/outtra/OUTTRA-Logo.png"
								 style="width:93px;height:19px;"></amp-img>
					</amp-iframe>',
			$iframe_source );
} else {
	printf( '<div id="%s">
						<script async
							type="text/plain"
					        class="cmplazyload"
					        data-cmp-src="%s"
					        data-cmp-vendor="1016"
							data-publisherid="%s"
							%s="%s"></script>
					</div>',
		$div_id,
		$script_source,
		block_value( 'publisher_id' ),
		$data_attribute,
		block_value( 'widget_id' )
	);
}

