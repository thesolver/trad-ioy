( function ( blocks, blockEditor, components, i18n, element ) {
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;
	var __ = i18n.__;

	blocks.registerBlockType( 'traditores-in-one-year/reading-plan', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			return el(
				'div',
				blockProps,
				el( SelectControl, {
					label: __( 'Reading plan', 'traditores-in-one-year' ),
					value: attributes.planType,
					options: [
						{ label: __( 'Entire Bible (66 books)', 'traditores-in-one-year' ), value: 'all66' },
						{ label: __( 'New Testament', 'traditores-in-one-year' ), value: 'nt' },
					],
					onChange: function ( value ) {
						setAttributes( { planType: value } );
					},
				} ),
				el( ToggleControl, {
					label: __( 'Scroll to today on load', 'traditores-in-one-year' ),
					checked: attributes.scrollToToday,
					onChange: function ( value ) {
						setAttributes( { scrollToToday: value } );
					},
				} ),
				el(
					'p',
					{ className: 'description' },
					__(
						'The full reading plan appears on the front end.',
						'traditores-in-one-year'
					)
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.i18n, window.wp.element );
