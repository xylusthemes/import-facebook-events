const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { dateI18n, getSettings } = wp.date;
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
} = wp.components;
var InspectorControls = wp.blockEditor.InspectorControls;

registerBlockType( 'ife-block/facebook-events', {
	title: __( 'Facebook Events' ),
    icon: {
		foreground: '#3b5998',
		src: <svg viewBox="0 0 24 24"><path d="M20 3H4c-.6 0-1 .4-1 1v16c0 .5.4 1 1 1h8.6v-7h-2.3v-2.7h2.3v-2c0-2.3 1.4-3.6 3.5-3.6 1 0 1.8.1 2.1.1v2.4h-1.4c-1.1 0-1.3.5-1.3 1.3v1.7h2.7l-.4 2.8h-2.3v7H20c.5 0 1-.4 1-1V4c0-.6-.4-1-1-1z" /></svg>,
	},
	category: 'widgets',
	keywords: [
		__( 'Events' ),
		__( 'Facebook' ),
		__( 'Facebook events' ),
	],
	description: 'Block for Display Facebook Events',
    attributes: {
        col: {
			type: 'number',
			default: 2,
		},
		posts_per_page: {
			type: 'number',
			default: 12,
		},
		past_events: {
			type: 'boolean',
     		default: false
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
		layout: {
			type: 'string',
			default: '',
		},
    },
    edit: ( { attributes, setAttributes } ) => {
        const { col, posts_per_page, past_events, start_date, end_date, order, orderby, layout } = attributes;
		const settings = getSettings();
		const dateClassName = past_events === true ? 'ife_hidden' : '';
		const { serverSideRender: ServerSideRender } = wp;

		const is12HourTime = /a(?!\\)/i.test(
			settings.formats.time
				.toLowerCase() // Test only the lower case a
				.replace( /\\\\/g, '' ) // Replace "//" with empty strings
				.split( '' ).reverse().join( '' ) // Reverse the string and test for "a" not followed by a slash
		);
        return (
            <div>
                <InspectorControls>
					<PanelBody title={ __( 'Facebook Events Setting' ) }>
						<RangeControl
								label={ __( 'Columns' ) }
								value={ col || 2 }	
								onChange={ ( value ) => setAttributes( { col: value } ) }
								min={ 1 }
								max={ 4 }
							/>
						<RangeControl
							label={ __( 'Events per page' ) }
							value={ posts_per_page || 12 }
							onChange={ ( value ) => setAttributes( { posts_per_page: value } ) }
							min={ 1 }
							max={ 100 }
						/>
						<ToggleControl
							label={ __( 'Display past events' ) }
							checked={ past_events }
							onChange={ value => {
								return setAttributes( { 
									start_date: '',
									end_date: '',
									past_events: value
								} );
							}
							}
						/>
						<SelectControl
							label="Event Grid View Layout"
							value={ layout }
							options={ [
								{ label: 'Default', value: '' },
								{ label: 'Style 2', value: 'style2' },
							] }
							onChange={ ( value ) => setAttributes( { layout: value } ) }
						/>
						<SelectControl
							label="Order By"
							value={ orderby }
							options={ [
								{ label: 'Event Start Date', value: 'event_start_date' },
								{ label: 'Event End Date', value: 'event_end_date' },
								{ label: 'Event Title', value: 'title' },
							] }
							onChange={ ( value ) => setAttributes( { orderby: value } ) }
						/>
						<RadioControl
							label={ __( 'Order' ) }
							selected={ order }
							options={ [
								{ label: __( 'Ascending' ), value: 'ASC' },
								{ label: __( 'Descending' ), value: 'DESC' },
							] }
							onChange={ value => setAttributes( { order: value } ) }
						/>
						<PanelRow className={ `ife-start-date ${ dateClassName }` }>
							<span>{ __( 'Event Start Date' ) }</span>
							<Dropdown
								label={ __( 'Start Date' ) }
								position="bottom left"
								contentClassName="ife-start-date__dialog"
								popoverProps={ { placement: 'bottom-start' } }
								renderToggle={ ( { isOpen, onToggle } ) => (
									<Button
										type="button"
										className="ife-start-date__toggle"
										onClick={ onToggle }
										aria-expanded={ isOpen }
										isLink
									>
										{ eventDateLabel( start_date, true ) }
									</Button>
								) }
								renderContent={ () =>
									<DateTimePicker
										currentDate={ start_date !== '' ? start_date : new Date() }
										onChange={ ( value ) => setAttributes( { start_date: value } ) }
										locale={ settings.l10n.locale }
										is12Hour={ is12HourTime }
										__nextRemoveHelpButton
										__nextRemoveResetButton
									/>
								}
							/>
						</PanelRow>
						<PanelRow className={ `ife-end-date ${ dateClassName }` }>
							<span>{ __( 'Event End Date' ) }</span>
							<Dropdown
								label={ __( 'End Date' ) }
								position="bottom left"
								contentClassName="ife-end-date__dialog"
								popoverProps={ { placement: 'bottom-start' } }
								renderToggle={ ( { isOpen, onToggle } ) => (
									<Button
										type="button"
										className="ife-end-date__toggle"
										onClick={ onToggle }
										aria-expanded={ isOpen }
										isLink
									>
										{ eventDateLabel( end_date ) }
									</Button>
								) }
								renderContent={ () =>
									<DateTimePicker
										currentDate={ end_date !== '' ? end_date : new Date() }
										onChange={ ( value ) => setAttributes( { end_date: value } ) }
										locale={ settings.l10n.locale }
										is12Hour={ is12HourTime }
										__nextRemoveHelpButton
										__nextRemoveResetButton
									/>
								}
							/>
						</PanelRow>
					</PanelBody>
                </InspectorControls>
				<ServerSideRender
					block="ife-block/facebook-events"
					attributes={attributes}
					key={JSON.stringify(attributes)}
				/>
            </div>
        );
    },
	save: function() {
		// Rendering in PHP.
		return null;
	},
});
function eventDateLabel( date, start ) {
	const settings = getSettings();
	const defaultLabel = start ? __( 'Select Start Date' ) : __( 'Select End Date' );
	return date ?
		dateI18n( settings.formats.datetime, date ) :
		defaultLabel;
}