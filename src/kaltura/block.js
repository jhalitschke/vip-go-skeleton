/**
 * 
 * kaltura-block.js
 * to register Kaltura/Video Gutenberg-Block
 * 
 */

//  Import CSS.
import './editor.scss';
import './style.scss';
import React, {
	useEffect,
	useRef
} from "react";

const {
	__
} = wp.i18n; // Import __() from wp.i18n
const {
	registerBlockType
} = wp.blocks; // Import registerBlockType() from wp.blocks

const {
	InspectorControls,
	InspectorAdvancedControls,
	BlockControls,
	useBlockProps,
	BlockAlignmentToolbar,
	RichText,
	AlignmentToolbar,
	Fragment
} = wp.blockEditor;
const {
	Toolbar,
	ToolbarButton,
	TextControl,
	CheckboxControl,
	RadioControl,
	SelectControl,
	TextareaControl,
	ToggleControl,
	RangeControl,
	Panel,
	PanelBody,
	PanelRow,
	Disabled
} = wp.components;

/**
 * Register: a Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully registered; otherwise `undefined`.
 * 
 */
registerBlockType(
	'kaltura/video', {
		title: __('Kaltura Video'),
		description: __('Embed Video via Kaltura'),
		icon: 'format-video', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
		category: 'embed', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
		attributes: {
			content: {
				type: 'string',
				default: '[kaltura-widget /]',
			},
			entryid: {
				type: 'string',
				default: '',
				source: 'text',
				selector: 'entryid',
			},
			uiconfid: {
				type: 'number',
				default: 0,
				source: 'text',
				selector: 'uiconfid',
			},
			responsive: {
				type: 'boolean',
				source: 'text',
				default: true,
				selector: 'responsive',
			},
			hoveringControls: {
				type: 'boolean',
				default: true,
				source: 'text',
				selector: 'hoveringControls',
			},
			isplaylist: {
				type: 'boolean',
				default: false,
				source: 'text',
				selector: 'isplaylist',
			},
			width: {
				type: 'number',
				default: 100,
				source: 'text',
				selector: 'width',
			},
			height: {
				type: 'number',
				default: 56.25,
				source: 'text',
				selector: 'height',
			},
		},
		keywords: [
			'Kaltura',
			'Video',
			'KMC',
		],

		/**
		 * The edit function describes the structure of your block in the context of the editor.
		 * This represents what the editor will render when the block is used.
		 *
		 * The "edit" property must be a valid function.
		 *
		 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
		 *
		 * @param {Object} props Props.
		 * @returns {Mixed} JSX Component.
		 */
		edit: (props) => {
			//console.log('KALTURA VIDEO BLOCK EDIT', props);
			const {
				attributes,
				setAttributes
			} = props;

			var updateFieldValue = function (val, field) {
				switch (field) {
					case 'entryid':
						attributes.entryid = val;
						break;

					case 'uiconfid':
						attributes.uiconfid = val;
						break;

					case 'responsive':
						attributes.responsive = val;
						break;

					case 'hoveringControls':
						attributes.hoveringControls = val;
						break;

					case 'isplaylist':
						attributes.isplaylist = val;
						break;

					case 'width':
						attributes.width = val;
						break;

					case 'height':
						attributes.height = val;
						break;


				}
				console.log('VALUE on Field ' + field, val);

				//rebuild attributes content
				attributes.content = '[kaltura-widget uiconfid="' + attributes.uiconfid + '" entryid="' + attributes.entryid + '" width="' + attributes.width + '%" height="' + attributes.height + '%" responsive="' + attributes.responsive + '" hoveringControls="' + attributes.hoveringControls + '" isplaylist="' + attributes.isplaylist + '" /]';
				
				var _sca = {};
				var _sc = attributes.content.match(/[\w-]+=".+?"/g);
				if (_sc !== null && _sc.length > 0) {
					_sc.forEach(function (attribute) {
						attribute = attribute.match(/([\w-]+)="(.+?)"/);
						_sca[attribute[1]] = attribute[2];
					});
					console.log('PARSED FROM SHORTCODE', _sca);
					//feature: lässt sich in inspectorcontrols nicht mehr abändern, da das hier immer wieder geladen wird!
					attributes.entryid = _sca.entryid;
					attributes.uiconfid = _sca.uiconfid;
					attributes.responsive = _sca.responsive;
					attributes.hoveringControls = _sca.hoveringControls;
					attributes.isplaylist = _sca.isplaylist;
					attributes.width = parseFloat(_sca.width);
					attributes.height = parseFloat(_sca.height);
				}


			}

			/*if (props.isSelected) {
        var sel = 'iframe-lightbox-link-' + props.clientId;
        console.log('IS SELECTED', sel);

        var _sca = {};
        var _sc = attributes.content.match(/[\w-]+=".+?"/g);
        if (_sc !== null && _sc.length>0) {
          _sc.forEach(function(attribute) {
            attribute = attribute.match(/([\w-]+)="(.+?)"/);
            _sca[attribute[1]] = attribute[2];
          });
          console.log('PARSED FROM SHORTCODE',_sca);
          //feature: lässt sich in inspectorcontrols nicht mehr abändern, da das hier immer wieder geladen wird!
          attributes.entryid = _sca.entryid;
          attributes.uiconfid = _sca.uiconfid;
          attributes.responsive = _sca.responsive;
          attributes.hoveringControls = _sca.hoveringControls;
          attributes.isplaylist = _sca.isplaylist;
          attributes.width = parseFloat(_sca.width);
          attributes.height = parseFloat(_sca.height);
        }

        useEffect(() => {

          const timer = setTimeout(() => {
            var elems = document.getElementsByClassName(sel);
            console.log('...ON', elems);
   
            if (elems !== undefined && elems.length > 0) {
              [].forEach.call(elems, function(el) {
                el.lightbox = new IframeLightbox(el, {
                  onLoaded: function (iframe) {
                    console.log('LIGHTBOX onLoaded', iframe);
                  },
                  onCreated: function (instance) {
                    console.log('LIGHTBOX onCreated', instance);
                  },
                  onOpened: function (instance) {
                    console.log('LIGHTBOX onOpened', instance);
                  },
                  onClosed: function (instance) {
                    console.log('LIGHTBOX onClosed', instance);
                  }
                });
              });
            }
          }, 333);

        }, []);

      }*/

			var post_id = wp.data.select("core/editor").getCurrentPostId();
			var _href
			if (attributes.entryid == '') {
				_href = 'media-upload.php?chromeless=1&post_id=' + post_id + '&tab=kaltura_browse&contentId=' + props.clientId;
			} else {
				_href = 'media-upload.php?tab=kaltura_browse&kaction=sendtoeditor&contentId=' + props.clientId + '&entryIds[0]=' + attributes.entryid;
			}
			return ( 
				<div id = "blocks-shortcode-input-0" className = { 'block-editor-plain-text blocks-shortcode__textarea ' + props.clientId} >
					<BlockControls key = "controls" >
					</BlockControls> 
					<InspectorControls key = "setting" >
						<PanelBody title = { __('Embedded Video')} initialOpen = { true } >
							<PanelRow >
								<button class = "kaltura-video" >
									<a class = {'iframe-lightbox-link-' + props.clientId + ' kaltura-video'} id = "kalturavideo" href = { _href } > { __('Edit Video')} </a>
								</button> 
							</PanelRow> 
						</PanelBody>
						<PanelBody title = { __('Einstellungen') } initialOpen = { true } >
							<PanelRow >
								<ToggleControl 
									label = {__('Responsive')}
									help = { __('responsive Darstellung') }
									checked = { attributes.responsive }
									onChange = { (val) => {
										updateFieldValue(val, 'responsive')
									} }
								/> 
							</PanelRow> 
							<PanelRow >
								<ToggleControl 
									label = { __('Videosteuerung') }
									help = { __('Anzeige der Videosteuerungsebene ') }
									checked = { attributes.hoveringControls }
									onChange = { (val) => {
										updateFieldValue(val, 'hoveringControls')
									} }
								/>
							</PanelRow> 
							<PanelRow >
								<ToggleControl 
									label = { __('Playlist') }
									help = { __('ist eine Playliste') }
									checked = { attributes.isplaylist }
									onChange = { (val) => {
										updateFieldValue(val, 'isplaylist')
									} }
								/>
							</PanelRow>
							<PanelRow >
								<RangeControl 
									label = { __('Breite') }
									help = { __('Breite des Video-Players') }
									value = { attributes.width }
									min = { 0 }
									max = { 2 * attributes.width }
									onChange = { (val) => {
										updateFieldValue(val, 'width')
									} }
								/>
							</PanelRow>
							<PanelRow >
								<RangeControl 
									label = { __('Höhe') }
									help = { __('Höhe des Video-Players') }
									value = { attributes.height }
									min = { 0 }
									max = { 2 * attributes.height }
									onChange = { (val) => {
										updateFieldValue(val, 'height')
									} }
								/>
							</PanelRow>
							<PanelRow >
								<TextControl 
									label = { __('VideoID') }
									value = { attributes.entryid }
									onChange = { (val) => {
										updateFieldValue(val, 'entryid')
									} }
								/>
							</PanelRow>
							<PanelRow >
								<TextControl 
									label = { __('UIConfID') }
									help = { __('PlayerID') }
									value = { attributes.uiconfid }
									onChange = { (val) => {
										updateFieldValue(val, 'uiconfid')
									} }
								/> 
							</PanelRow>    
						</PanelBody>     
					</InspectorControls> 
					<InspectorAdvancedControls >
					</InspectorAdvancedControls> 
					{ attributes.content }
				</div>
			);
		},

		/**
		 * The save function defines the way in which the different attributes should be combined
		 * into the final markup, which is then serialized by Gutenberg into post_content.
		 *
		 * The "save" property must be specified and must be a valid function.
		 *
		 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
		 *
		 * @param {Object} props Props.
		 * @returns {Mixed} JSX Frontend HTML.
		 */
		save: (props) => {
			//console.log('KALTURA VIDEO BLOCK SAVE', props);
			const {
				attributes
			} = props;
			return ( <div id = "blocks-shortcode-input-0"
				className = "block-editor-plain-text blocks-shortcode__textarea" > {
					attributes.content
				} </div>
			);
		},
	}
);