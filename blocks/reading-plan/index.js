( function ( blocks, blockEditor, components, i18n, element ) {
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	var SelectControl = components.SelectControl;
	var __ = i18n.__;

	function scrollChoice(value) {
		if (value === true || value === '1' || value === 'true' || value === 'yes' || value === 'y') {
			return '1';
		}
		if (value === '0' || value === 'no' || value === 'n') {
			return '0';
		}
		// Boolean false was the old implicit default, which means "use the site setting".
		return '';
	}

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
				el( SelectControl, {
					label: __( 'Scroll to today on load', 'traditores-in-one-year' ),
					value: scrollChoice( attributes.scrollToToday ),
					options: [
						{ label: __( 'Use site setting', 'traditores-in-one-year' ), value: '' },
						{ label: __( 'On', 'traditores-in-one-year' ), value: '1' },
						{ label: __( 'Off', 'traditores-in-one-year' ), value: '0' },
					],
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
