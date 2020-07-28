edited = false;

function createBlock() {
	const { registerBlockType } = wp.blocks;
	const { createElement } = wp.element;
	const { InspectorControls }  = wp.blockEditor;
	const { CheckboxControl, RadioControl, SelectControl, TextControl, TextareaControl, ToggleControl, ServerSideRender } = wp.components;
	const { serverSideRender } = wp;

	const phpConfig = eval( blockname + 'Config;' ); 


	registerBlockType( phpConfig.block.blocktype, {
		title: phpConfig.block.title,
		category: phpConfig.block.category,
		icon: phpConfig.block.icon,
		construct(){
			props.setAttributes({ countit: 0 });
		},
		edit( props ){
			const att = props.attributes;
			const setAtts = props.setAttributes;

			function changeField( val ){
				if ( phpConfig[this]['type'] == 'number' ){
					val = parseInt( val );
				}
				setAtts( {[this]: val} );
			}	
		
			function clean(obj) {
				for (var propName in obj) {
					if (obj[propName] === null || obj[propName] === undefined) {
						delete obj[propName];
				  	}
				}
			}
			

	
			if ( ( props['isSelected'] === false ) && ( edited === true ) ){
				clean( att );
				return createElement( serverSideRender, { block: phpConfig.block.blocktype, attributes: att });
			} else {
				var ret = [];
				ret.push( createElement( 'div', { className: 'components-placeholder__label' }, [ createElement( 'span', { className: 'editor-block-icon block-editor-block-icon dashicons-before dashicons-' + phpConfig.block.icon }, null ), phpConfig.block.title ] ) );

				for ( var fieldname in phpConfig ){
					switch( phpConfig[fieldname]['field_type'] ){
						case 'checkbox': 
							ret.push( createElement( CheckboxControl, { checked: ( typeof att[fieldname] !== 'undefined' ? att[fieldname] : phpConfig[fieldname]['default'] ), label: phpConfig[fieldname]['label'], onChange: changeField.bind( fieldname ) } ) );
							break;
						case 'radio': 
							var opts = [];
							for ( var v in phpConfig[fieldname]['values'] ){
								opts.push( JSON.parse( '{"value":"' + v + '", "label":"' + phpConfig[fieldname]['values'][v] + '"}' ) );
							}
							ret.push( createElement( RadioControl, { selected: ( typeof att[fieldname] !== 'undefined' ? att[fieldname] : phpConfig[fieldname]['default'] ), label: phpConfig[fieldname]['label'], onChange: changeField.bind( fieldname ), options: opts } ) );
							break;
						case 'multi_select': 
						case 'select': 
							// sort by value case-insensitively
							var sortable = [];
							for ( var v in phpConfig[fieldname]['values'] ){
								sortable.push([v, phpConfig[fieldname]['values'][v]]);
							}
							sortable.sort(function (a, b) {
								a = a[1];
								b = b[1];
								return a.toLowerCase().localeCompare(b.toLowerCase());
							});
							var opts = [];
							for ( var i = 0; i < sortable.length; i++ ){
								opts.push( JSON.parse( '{"value":"' + sortable[i][0] + '", "label":"' + sortable[i][1] + '"}' ) );
							}
							ret.push( createElement( SelectControl, { multiple: ( phpConfig[fieldname]['field_type'] == 'multi_select' ? 1 : 0 ), value: att[fieldname], label: phpConfig[fieldname]['label'], type: phpConfig[fieldname]['type'], onChange: changeField.bind( fieldname ), options: opts } ) );
							break;
						case 'text': 
							ret.push( createElement( TextControl, { value: att[fieldname], label: phpConfig[fieldname]['label'], type: phpConfig[fieldname]['type'], onChange: changeField.bind( fieldname ) } ) );
							break;
						case 'textarea': 
							ret.push( createElement( TextareaControl, { value: att[fieldname], label: phpConfig[fieldname]['label'], type: phpConfig[fieldname]['type'], onChange: changeField.bind( fieldname ) } ) );
							break;
						case 'toggle': 
							ret.push( createElement( ToggleControl, { checked: ( typeof att[fieldname] !== 'undefined' ? att[fieldname] : phpConfig[fieldname]['default'] ), label: phpConfig[fieldname]['label'], type: phpConfig[fieldname]['type'], onChange: changeField.bind( fieldname ) } ) );
							break;
					}
				}

				edited = true;

				if ( phpConfig.block.show_block == 'right'){
					return createElement('div', {}, [
						createElement( 'div', {}, phpConfig.block.message ),
						createElement( InspectorControls, {}, ret )
					] )
				}else{
					return createElement('div', { className: "components-placeholder" }, ret )
				}
			}
		},
		save( props ){
			return null;
		}
	} );
}

createBlock();