// /*
//   Note: We have included the plugin in the same JavaScript file as the TinyMCE
//   instance for display purposes only. Tiny recommends not maintaining the plugin
//   with the TinyMCE instance and using the `external_plugins` option.
// */
// tinymce.PluginManager.add('example', function(editor, url) {
//   var openDialog = function () {
//     return editor.windowManager.open({
//       title: 'Example plugin',
//       body: {
//         type: 'panel',
//         items: [
//           {
//             type: 'input',
//             name: 'title',
//             label: 'Title'
//           }
//         ]
//       },
//       buttons: [
//         {
//           type: 'cancel',
//           text: 'Close'
//         },
//         {
//           type: 'submit',
//           text: 'Save',
//           primary: true
//         }
//       ],
//       onSubmit: function (api) {
//         var data = api.getData();
//         /* Insert content when the window form is submitted */
//         editor.insertContent('Title: ' + data.title);
//         api.close();
//       }
//     });
//   };
//   /* Add a button that opens a window */
//   editor.ui.registry.addButton('example', {
//     text: 'My button',
//     onAction: function () {
//       /* Open window */
//       openDialog();
//     }
//   });
//   /* Adds a menu item, which can then be included in any menu via the menu/menubar configuration */
//   editor.ui.registry.addMenuItem('example', {
//     text: 'Example plugin',
//     onAction: function() {
//       /* Open window */
//       openDialog();
//     }
//   });
//   /* Return the metadata for the help plugin */
//   return {
//     getMetadata: function () {
//       return  {
//         name: 'Example plugin',
//         url: 'http://exampleplugindocsurl.com'
//       };
//     }
//   };
// });

// /*
//   The following is an example of how to use the new plugin and the new
//   toolbar button.
// */
// tinymce.init({
//   selector: 'textarea#custom-plugin',
//   plugins: 'example help',
//   toolbar: 'example | help'
// });


// // (function () {
// //     // Load plugin specific language pack
// //     tinymce.PluginManager.requireLangPack('Kaltura');

// // 	tinymce.create('tinymce.plugins.Kaltura', {
// // 		/**
// // 		 * Initializes the plugin, this will be executed after the plugin has been created.
// // 		 * This call is done before the editor instance has finished it's initialization so use the onInit event
// // 		 * of the editor instance to intercept that event.
// // 		 *
// // 		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
// // 		 * @param {string} url Absolute URL to where the plugin is located.
// // 		 */
// // 		init: function (ed, url) {

// //             ed.addCommand('mceKaltura', function() {
// //                 ed.windowManager.open({
// //                     file : url + '/dialog.htm',
// //                     width : 320 + ed.getLang('example.delta_width', 0),
// //                     height : 120 + ed.getLang('example.delta_height', 0),
// //                     inline : 1
// //                 }, {
// //                     plugin_url : url, // Plugin absolute URL
// //                     some_custom_arg : 'custom arg' // Custom argument
// //                 });
// //             });

// //             // Register example button
// //             ed.addButton('example', {
// //                 title : 'example.desc',
// //                 cmd : 'mceExample',
// //                 image : url + '/img/example.gif'
// //             });

// //             // Add a node change handler, selects the button in the UI when a image is selected
// //             ed.onNodeChange.add(function(ed, cm, n) {
// //                 cm.setActive('example', n.nodeName == 'IMG');
// //             });
            
// // 		},

// // 		/**
// // 		 * Creates control instances based in the incomming name. This method is normally not
// // 		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
// // 		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
// // 		 * method can be used to create those.
// // 		 *
// // 		 * @param {String} n Name of the control to create.
// // 		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
// // 		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
// // 		 */
// // 		createControl: function (n, cm) {
// // 			return null;
// // 		},

// // 		/**
// // 		 * Returns information about the plugin as a name/value array.
// // 		 * The current keys are longname, author, authorurl, infourl and version.
// // 		 *
// // 		 * @return {Object} Name/value array containing information about the plugin.
// // 		 */
// // 		getInfo: function () {
// // 			return {
// // 				longname : 'Kaltura Video',
// // 				author   : 'Kaltura',
// // 				authorurl: 'http://www.kaltura.com',
// // 				infourl  : 'http://corp.kaltura.com',
// // 				version  : "1.0"
// // 			};
// // 		},

// // 		_tagStart: '[kaltura-widget',

// // 		_tagEnd: ['/]', ']'],

// // 		_onBeforeSetContent: function (ed, obj) {
// // 			if (!obj.content)
// // 				return;

// // 			var contentData = obj.content;
// // 			var startPos = 0;

// // 			while ((startPos = contentData.indexOf(this._tagStart, startPos)) != -1) {
// // 				var endPos = null;
// // 				var endTagLength = 0;
// // 				for(var i = 0; i < this._tagEnd.length; i++) {
// // 					endPos = contentData.indexOf(this._tagEnd[i], startPos);
// // 					if (endPos != -1) {
// // 						endTagLength = this._tagEnd[i].length;
// // 						break;
// // 					}
// // 				}
// // 				if (endPos == -1) {
// // 					startPos++;
// // 					continue;
// // 				}
// // 				var attribs = this._parseAttributes(contentData.substring(startPos + this._tagStart.length, endPos));

// // 				// set defaults if not found
// // 				if (!attribs['wid'])
// // 					attribs['wid'] = '';


// // 				if (!attribs['size'])
// // 					attribs['size'] = 'custom';

// // 				if (!attribs['align'])
// // 					attribs['align'] = '';

// // 				if (!attribs['width'] || !attribs['height']) {
// // 					attribs['width'] = '410';
// // 					attribs['height'] = '364';
// // 				}

// // 				// for backward compatibility, when we used specific size
// // 				if (attribs['size'] == 'large') {
// // 					attribs['width'] = '410';
// // 					attribs['height'] = '364';
// // 				}

// // 				if (attribs['size'] == 'small') {
// // 					attribs['width'] = '250';
// // 					attribs['height'] = '244';
// // 				}

// // 				if (attribs['width'] == '410' && attribs['height'] == '364')
// // 					attribs['size'] = 'large';

// // 				if (attribs['width'] == '250' && attribs['height'] == '244')
// // 					attribs['size'] = 'small';

// // 				endPos += endTagLength;
// // 				var contentDataEnd = contentData.substr(endPos);
// // 				contentData = contentData.substr(0, startPos);

// // 				// build the image tag
// // 				var img = jQuery('<img />');
// // 				img.attr('src', this._url + '/../thumbnails/placeholder.gif');
// // 				img.attr('title', 'Kaltura');
// // 				img.attr('alt', 'Kaltura');
// // 				img.addClass('kaltura_item align' + attribs['align']);
// // 				if (attribs['wid'])
// // 					img.addClass('kaltura_id_' + attribs['wid']);
// // 				if (attribs['uiconfid'])
// // 					img.addClass('kaltura_uiconfid_' + attribs['uiconfid']);
// // 				if (attribs['entryid'])
// // 					img.addClass('kaltura_entryid_' + attribs['entryid']);
// // 				if (attribs['responsive'])
// // 					img.addClass('kaltura_responsive_' + attribs['responsive']);
// // 				if (attribs['hoveringcontrols'])
// // 					img.addClass('kaltura_hoveringControls_' + attribs['hoveringcontrols']);
// // 				if (attribs['isplaylist'])
// // 					img.addClass('kaltura_isplaylist_' + attribs['isplaylist']);

// // 				img.attr('name', 'mce_plugin_kaltura_desc');
// // 				img.attr('width', attribs['width']);
// // 				img.attr('height', attribs['height']);

// // 				if (attribs['style'])
// // 					img.attr('style', attribs['style']);

// // 				contentData += img[0].outerHTML;

// // 				contentData += contentDataEnd;
// // 			}

// // 			obj.content = contentData;
// // 		},

// // 		_onGetContent: function (ed, obj) {
// // 			if (!obj.content)
// // 				return;

// // 			var contentData = obj.content;
// // 			var $content = jQuery('<div />').append(contentData);
// // 			var tagStart = this._tagStart;
// // 			var tagEnd = this._tagEnd[0];
// // 			$content.find('img.kaltura_item').each(function (i, item) {
// // 				var $item = jQuery(item);
// // 				var widgetAttribs = {};
// // 				var classes = item.className.split(/\s+/);
// // 				jQuery.each(classes, function (i, value) {
// // 					switch (value)
// // 					{
// // 						case 'alignright':
// // 							widgetAttribs.align = 'right';
// // 							break;
// // 						case 'alignleft':
// // 							widgetAttribs.align = 'left';
// // 							break;
// // 						case 'aligncenter':
// // 							widgetAttribs.align = 'center';
// // 							break;
// // 						default:
// // 							var classAttrArr = value.match(/kaltura_([a-zA-Z]*)_([\w]*)/);
// // 							if (classAttrArr && classAttrArr.length == 3) {
// // 								var classAttrib = classAttrArr[1];
// // 								var classValue = classAttrArr[2];
// // 								switch(classAttrib) {
// // 									case 'id':
// // 										if (classValue)
// // 											widgetAttribs.wid = classValue;
// // 										break;
// // 									case 'uiconfid':
// // 										if (classValue)
// // 											widgetAttribs.uiconfid = classValue;
// // 										break;
// // 									case 'entryid':
// // 										if (classValue)
// // 											widgetAttribs.entryid = classValue;
// // 										break;
// // 									case 'responsive':
// // 										if (classValue)
// // 											widgetAttribs.responsive = classValue;
// // 										break;
// // 									case 'hoveringControls':
// // 										if(classValue) {
// // 											widgetAttribs.hoveringControls = classValue;
// // 										}
// // 										break;
// // 									case 'isplaylist':
// // 										if (classValue)
// // 											widgetAttribs.isplaylist = classValue;
// // 										break;
// // 								}
// // 							}
// // 							break;
// // 					}
// // 				});

// // 				widgetAttribs.width = $item.attr('width');
// // 				widgetAttribs.height = $item.attr('height');

// // 				if ($item.attr('style'))
// // 					widgetAttribs.style = $item.attr('style');

// // 				var widgetStr = tagStart;
// // 				jQuery.each(widgetAttribs, function(propName, propValue) {
// // 					widgetStr += (' ' + propName + '="' + propValue + '"');
// // 				});
// // 				widgetStr += (' ' + tagEnd);
// // 				$item.replaceWith(widgetStr)
// // 			});

// // 			obj.content = $content.html();
// // 		},

// // 		_parseAttributes: function (attribute_string) {
// // 			var attributeName = '';
// // 			var attributeValue = '';
// // 			var withInName;
// // 			var withInValue;
// // 			var attributes = new Array();
// // 			var whiteSpaceRegExp = new RegExp('^[ \n\r\t]+', 'g');

// // 			if (attribute_string == null || attribute_string.length < 2)
// // 				return null;

// // 			withInName = withInValue = false;

// // 			for (var i = 0; i < attribute_string.length; i++) {
// // 				var chr = attribute_string.charAt(i);

// // 				if ((chr == '"' || chr == "'") && !withInValue)
// // 					withInValue = true;
// // 				else if ((chr == '"' || chr == "'") && withInValue) {
// // 					withInValue = false;

// // 					var pos = attributeName.lastIndexOf(' ');
// // 					if (pos != -1)
// // 						attributeName = attributeName.substring(pos + 1);

// // 					attributes[attributeName.toLowerCase()] = attributeValue.substring(1);

// // 					attributeName = '';
// // 					attributeValue = '';
// // 				} else if (!whiteSpaceRegExp.test(chr) && !withInName && !withInValue)
// // 					withInName = true;

// // 				if (chr == '=' && withInName)
// // 					withInName = false;

// // 				if (withInName)
// // 					attributeName += chr;

// // 				if (withInValue)
// // 					attributeValue += chr;
// // 			}
// // 			return attributes;
// // 		}
// // 	});

// // 	// Register plugin
// // 	tinymce.PluginManager.add('kaltura', tinymce.plugins.Kaltura);
// // })();
