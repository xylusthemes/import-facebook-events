const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const {
	PanelBody,
	PanelRow,
	Button,
	Dropdown,
	RangeControl,
	SelectControl,
	ToggleControl,
	RadioControl,
	DateTimePicker,
	ServerSideRender,
} = wp.components;
const { InspectorControls } = wp.editor;
const { dateI18n, __experimentalGetSettings } = wp.date;
const { createElement } = wp.element;

/**
 * Register: Facebook Events Gutenberg Block.
 */
registerBlockType( 'ife-block/facebook-events', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Facebook Events' ),
	description: __( 'Block for Display Facebook Events' ),
	icon: {
		foreground: '#3b5998',
		src: <svg viewBox="0 0 24 24"><path d="M20 3H4c-.6 0-1 .4-1 1v16c0 .5.4 1 1 1h8.6v-7h-2.3v-2.7h2.3v-2c0-2.3 1.4-3.6 3.5-3.6 1 0 1.8.1 2.1.1v2.4h-1.4c-1.1 0-1.3.5-1.3 1.3v1.7h2.7l-.4 2.8h-2.3v7H20c.5 0 1-.4 1-1V4c0-.6-.4-1-1-1z" /></svg>,
	},
	category: 'widgets',
	keywords: [
		__( 'Events' ),
		__( 'Facebook' ),
		__( 'facebook events' ),
	],

	// Enable or disable support for features
	supports: {
		html: false,
	},

	// Set for each piece of dynamic data used in your block
	attributes: {
		col: {
			type: 'number',
			default: 3,
		},
		posts_per_page: {
			type: 'number',
			default: 12,
		},
		past_events: {
			type: 'string',
		},
		start_date: {
			type: 'string',
			default: '',
		},
		end_date: {
			type: 'string',
			default: '',
		},
		order: {
			type: 'string',
			default: 'ASC',
		},
		orderby: {
			type: 'string',
			default: 'event_start_date',
		},
	},

	// Determines what is displayed in the editor
	edit: function( props ) {
		const { attributes, isSelected, setAttributes } = props;
		const settings = __experimentalGetSettings();
		const dateClassName = attributes.past_events === 'yes' ? 'ife_hidden' : '';

		// To know if the current timezone is a 12 hour time with look for "a" in the time format
		// We also make sure this a is not escaped by a "/"
		const is12HourTime = /a(?!\\)/i.test(
			settings.formats.time
				.toLowerCase() // Test only the lower case a
				.replace( /\\\\/g, '' ) // Replace "//" with empty strings
				.split( '' ).reverse().join( '' ) // Reverse the string and test for "a" not followed by a slash
		);

		return [
			isSelected && (
				<InspectorControls key="inspector">
					<PanelBody title={ __( 'Facebook Events Setting' ) }>
						<RangeControl
							label={ __( 'Columns' ) }
							value={ attributes.col || 3 }
							onChange={ ( value ) => setAttributes( { col: value } ) }
							min={ 1 }
							max={ 4 }
						/>
						<RangeControl
							label={ __( 'Events per page' ) }
							value={ attributes.posts_per_page || 12 }
							onChange={ ( value ) => setAttributes( { posts_per_page: value } ) }
							min={ 1 }
							max={ 100 }
						/>
						<SelectControl
							label="Order By"
							value={ attributes.orderby }
							options={ [
								{ label: 'Event Start Date', value: 'event_start_date' },
								{ label: 'Event End Date', value: 'event_end_date' },
								{ label: 'Event Title', value: 'title' },
							] }
							onChange={ ( value ) => setAttributes( { orderby: value } ) }
						/>
						<RadioControl
							label={ __( 'Order' ) }
							selected={ attributes.order }
							options={ [
								{ label: __( 'Ascending' ), value: 'ASC' },
								{ label: __( 'Descending' ), value: 'DESC' },
							] }
							onChange={ value => setAttributes( { order: value } ) }
						/>
						<ToggleControl
							label={ __( 'Display past events' ) }
							checked={ attributes.past_events }
							onChange={ value => {
								attributes.start_date = '';
								attributes.end_date = '';
								return setAttributes( { past_events: value ? 'yes' : false } );
							}
							}
						/>
						<PanelRow className={ `ife-start-date ${ dateClassName }` }>
							<span>{ __( 'Start Date' ) }</span>
							<Dropdown
								position="bottom left"
								contentClassName="ife-start-date__dialog"
								renderToggle={ ( { onToggle, isOpen } ) => (
									<Button
										type="button"
										className="ife-start-date__toggle"
										onClick={ onToggle }
										aria-expanded={ isOpen }
										isLink
									>
										{ eventDateLabel( attributes.start_date, true ) }
									</Button>
								) }
								renderContent={ () =>
									<DateTimePicker
										currentDate={ attributes.start_date !== '' ? attributes.start_date : new Date() }
										onChange={ ( value ) => setAttributes( { start_date: value } ) }
										locale={ settings.l10n.locale }
										is12Hour={ is12HourTime }
									/>
								}
							/>
						</PanelRow>
						<PanelRow className={ `ife-end-date ${ dateClassName }` }>
							<span>{ __( 'End Date' ) }</span>
							<Dropdown
								position="bottom left"
								contentClassName="ife-end-date__dialog"
								renderToggle={ ( { onToggle, isOpen } ) => (
									<Button
										type="button"
										className="ife-end-date__toggle"
										onClick={ onToggle }
										aria-expanded={ isOpen }
										isLink
									>
										{ eventDateLabel( attributes.end_date ) }
									</Button>
								) }
								renderContent={ () =>
									<DateTimePicker
										currentDate={ attributes.end_date !== '' ? attributes.end_date : new Date() }
										onChange={ ( value ) => setAttributes( { end_date: value } ) }
										locale={ settings.l10n.locale }
										is12Hour={ is12HourTime }
									/>
								}
							/>
						</PanelRow>
					</PanelBody>
				</InspectorControls>
			),

			createElement( ServerSideRender, {
				block: 'ife-block/facebook-events',
				attributes: attributes,
			} ),
		];
	},

	save: function() {
		// Rendering in PHP.
		return null;
	},
} );

function eventDateLabel( date, start ) {
	const settings = __experimentalGetSettings();
	const defaultLabel = start ? __( 'Select Start Date' ) : __( 'Select End Date' );
	return date ?
		dateI18n( settings.formats.datetime, date ) :
		defaultLabel;
}
