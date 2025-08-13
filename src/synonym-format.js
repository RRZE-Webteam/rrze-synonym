// synonym-format.js
import { __ } from '@wordpress/i18n';
import {
	registerFormatType,
	toggleFormat,
	applyFormat,
	removeFormat,
	getActiveFormat,
} from '@wordpress/rich-text';
import {
	RichTextToolbarButton,
	RichTextShortcut,
} from '@wordpress/block-editor';
import {
	Popover,
	TextControl,
	SelectControl,
	Button,
	Flex,
	FlexItem,
} from '@wordpress/components';
import { useState, useRef } from '@wordpress/element';

const FORMAT_NAME = 'rrze/synonym';
const TAG_NAME = 'abbr';
const CLASS_NAME = 'rrze-syn';

const SynonymUI = ( props ) => {
	const { value, onChange, isActive } = props;

	// Active format instance (if the cursor is already inside an <abbr>)
	const current = getActiveFormat( value, FORMAT_NAME );
	const currentAttrs = ( current && current.attributes ) || {};

	const [ title, setTitle ] = useState( currentAttrs.title || '' );
	const [ lang, setLang ] = useState( currentAttrs.lang || '' );
	const [ pron, setPron ] = useState( currentAttrs['data-pron'] || '' );
	const [ isOpen, setIsOpen ] = useState( false );
	const anchorRef = useRef();

	const LANG_OPTIONS = [
		{ label: __('— none —','rrze-synonym'), value: '' },
		{ label: __('German','rrze-synonym'), value: 'de' },
		{ label: __('English','rrze-synonym'), value: 'en' },
		{ label: __('French','rrze-synonym'), value: 'fr' },
		{ label: __('Spanish','rrze-synonym'), value: 'es' },
		{ label: __('Russian','rrze-synonym'), value: 'ru' },
		{ label: __('Chinese','rrze-synonym'), value: 'zh' },
	];

	const apply = () => {
		const attrs = { title: title || undefined };
		if ( lang ) attrs.lang = lang;
		if ( pron ) attrs['data-pron'] = pron;

		onChange(
			applyFormat( value, {
				type: FORMAT_NAME,
				attributes: attrs,
			} )
		);
		setIsOpen( false );
	};

	const remove = () => {
		onChange( removeFormat( value, FORMAT_NAME ) );
		setIsOpen( false );
	};

	return (
		<>
			<RichTextShortcut
				type="primaryShift"
				character="S"
				onUse={ () => setIsOpen( true ) }
			/>
			<span ref={ anchorRef }>
				<RichTextToolbarButton
					icon="admin-site"
					title={ __('Synonym/Acronym','rrze-synonym') }
					onClick={ () => setIsOpen( (o) => !o ) }
					isActive={ isActive }
				/>
			</span>

			{ isOpen && (
				<Popover
					anchorRef={ anchorRef.current }
					variant="toolbar"
					onClose={ () => setIsOpen( false ) }
				>
					<div style={{ padding: 12, maxWidth: 320 }}>
						<TextControl
							label={ __('Long form (title attribute)','rrze-synonym') }
							value={ title }
							onChange={ setTitle }
							placeholder="Universal Resource Locator"
						/>
						<SelectControl
							label={ __('Language (lang)','rrze-synonym') }
							value={ lang }
							onChange={ setLang }
							options={ LANG_OPTIONS }
						/>
						<TextControl
							label={ __('Pronunciation (optional)','rrze-synonym') }
							help={ __('Phonetic note, stored as data-pron','rrze-synonym') }
							value={ pron }
							onChange={ setPron }
							placeholder="you-are-ell"
						/>
						<Flex style={{ marginTop: 10 }} justify="flex-end" gap={ 8 }>
							{ isActive && (
								<FlexItem>
									<Button variant="secondary" onClick={ remove }>
										{ __('Remove','rrze-synonym') }
									</Button>
								</FlexItem>
							) }
							<FlexItem>
								<Button variant="primary" onClick={ apply }>
									{ isActive ? __('Update','rrze-synonym') : __('Apply','rrze-synonym') }
								</Button>
							</FlexItem>
						</Flex>
					</div>
				</Popover>
			) }
		</>
	);
};

// Register the format: generates <abbr class="rrze-syn" ...>URL</abbr>
registerFormatType( FORMAT_NAME, {
	title: __('Synonym/Acronym','rrze-synonym'),
	tagName: TAG_NAME,
	className: CLASS_NAME,
	attributes: {
		title: 'title',
		lang: 'lang',
		'data-pron': 'data-pron',
	},
	edit: SynonymUI,
} );
