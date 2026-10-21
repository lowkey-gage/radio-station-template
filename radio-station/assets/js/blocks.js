(function (blocks, element, blockEditor, components, serverSideRender, data) {
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;
	var ServerSideRender = serverSideRender.default || serverSideRender;
	var options = window.radioStationBlockData || { days: [], djs: [], categories: [] };

	function preview(name, attributes) {
		var postId = 0;

		if (data.select('core/editor')) {
			postId = data.select('core/editor').getCurrentPostId() || 0;
		}

		return el(ServerSideRender, {
			block: name,
			attributes: attributes,
			urlQueryArgs: { post_id: postId }
		});
	}

	blocks.registerBlockType('radio-station/show-schedule', {
		title: 'Show Schedule',
		icon: 'calendar-alt',
		category: 'radio-station',
		attributes: {
			day: { type: 'string', default: 'week' }
		},
		edit: function (props) {
			return el(
				'div',
				useBlockProps(),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: 'Schedule', initialOpen: true },
						el(SelectControl, {
							label: 'Days to display',
							value: props.attributes.day,
							options: options.days,
							onChange: function (value) {
								props.setAttributes({ day: value });
							}
						})
					)
				),
				preview('radio-station/show-schedule', props.attributes)
			);
		},
		save: function () {
			return null;
		}
	});

	blocks.registerBlockType('radio-station/dj-info', {
		title: 'DJ Information',
		icon: 'admin-users',
		category: 'radio-station',
		attributes: {
			djId: { type: 'number', default: 0 },
			mode: { type: 'string', default: 'selected' }
		},
		edit: function (props) {
			var controls = [
				el(SelectControl, {
					label: 'Whose profile',
					value: props.attributes.mode,
					options: [
						{ value: 'selected', label: 'One selected DJ' },
						{ value: 'assigned', label: 'DJs assigned to this show' }
					],
					onChange: function (value) {
						props.setAttributes({ mode: value });
					}
				})
			];

			if (props.attributes.mode !== 'assigned') {
				controls.push(
					el(SelectControl, {
						label: 'DJ',
						value: String(props.attributes.djId || 0),
						options: options.djs.map(function (dj) {
							return { value: String(dj.value), label: dj.label };
						}),
						onChange: function (value) {
							props.setAttributes({ djId: parseInt(value, 10) || 0 });
						}
					})
				);
			}

			return el(
				'div',
				useBlockProps(),
				el(
					InspectorControls,
					null,
					el(PanelBody, { title: 'DJ', initialOpen: true }, controls)
				),
				preview('radio-station/dj-info', props.attributes)
			);
		},
		save: function () {
			return null;
		}
	});

	blocks.registerBlockType('radio-station/show-listings', {
		title: 'Show Listings',
		icon: 'playlist-audio',
		category: 'radio-station',
		attributes: {
			category: { type: 'string', default: '' },
			useProfile: { type: 'boolean', default: false }
		},
		edit: function (props) {
			var controls = [
				el(ToggleControl, {
					label: 'Show this DJ’s related shows',
					help: 'Use this on a DJ profile. Leave it off to list shows by category.',
					checked: !!props.attributes.useProfile,
					onChange: function (value) {
						props.setAttributes({ useProfile: !!value });
					}
				})
			];

			if (!props.attributes.useProfile) {
				controls.push(
					el(SelectControl, {
						label: 'Show category',
						value: props.attributes.category,
						options: options.categories,
						onChange: function (value) {
							props.setAttributes({ category: value });
						}
					})
				);
			}

			return el(
				'div',
				useBlockProps(),
				el(
					InspectorControls,
					null,
					el(PanelBody, { title: 'Shows', initialOpen: true }, controls)
				),
				preview('radio-station/show-listings', props.attributes)
			);
		},
		save: function () {
			return null;
		}
	});
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender,
	window.wp.data
);
