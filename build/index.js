(()=>{"use strict";var e={n:n=>{var r=n&&n.__esModule?()=>n.default:()=>n;return e.d(r,{a:r}),r},d:(n,r)=>{for(var l in r)e.o(r,l)&&!e.o(n,l)&&Object.defineProperty(n,l,{enumerable:!0,get:r[l]})},o:(e,n)=>Object.prototype.hasOwnProperty.call(e,n)};const n=window.wp.blocks,r=JSON.parse('{"u2":"create-block/rrze-synonym"}'),l=window.React,t=window.wp.i18n,a=window.wp.element,o=window.wp.data,s=window.wp.blockEditor,y=window.wp.components,_=window.wp.serverSideRender;var i=e.n(_);(0,n.registerBlockType)(r.u2,{edit:function({attributes:e,setAttributes:n}){const{register:r,tag:_,id:c,hstart:d,order:u,sort:m,lang:b,additional_class:p,color:g,load_open:z,expand_all_link:v,hide_title:w,hide_accordion:h,synonymstyle:f,synonym:E}=e,S=(0,s.useBlockProps)(),[k,C]=(0,a.useState)([""]),[q,O]=(0,a.useState)([""]),[R,j]=(0,a.useState)([""]);(0,a.useEffect)((()=>{n({register:r}),n({tag:_}),n({id:c}),n({hstart:d}),n({order:u}),n({sort:m}),n({lang:b}),n({additional_class:p}),n({color:g}),n({load_open:z}),n({expand_all_link:v}),n({hide_title:w}),n({hide_accordion:h}),n({synonymstyle:f}),n({synonym:E})}),[r,_,c,d,u,m,b,p,g,z,v,w,h,f,E,n]);const T=(0,o.useSelect)((e=>e("core").getEntityRecords("taxonomy","synonym_category")),[]),x=[{label:(0,t.__)("all","rrze-synonym"),value:""}];T&&Object.values(T).forEach((e=>{x.push({label:e.name,value:e.slug})}));const P=(0,o.useSelect)((e=>e("core").getEntityRecords("taxonomy","synonym_tag")),[]),A=[{label:(0,t.__)("all","rrze-synonym"),value:""}];P&&Object.values(P).forEach((e=>{A.push({label:e.name,value:e.slug})}));const B=(0,o.useSelect)((e=>e("core").getEntityRecords("postType","synonym",{per_page:-1,orderby:"title",order:"asc"})),[]),D=[{label:(0,t.__)("all","rrze-synonym"),value:0}];B&&Object.values(B).forEach((e=>{D.push({label:e.title.rendered?e.title.rendered:(0,t.__)("No title","rrze-synonym"),value:e.id})}));const F=[{label:(0,t.__)("all","rrze-faq"),value:""},{label:(0,t.__)("German","rrze-faq"),value:"de"},{label:(0,t.__)("English","rrze-faq"),value:"en"},{label:(0,t.__)("French","rrze-faq"),value:"fr"},{label:(0,t.__)("Spanish","rrze-faq"),value:"es"},{label:(0,t.__)("Russian","rrze-faq"),value:"ru"},{label:(0,t.__)("Chinese","rrze-faq"),value:"zh"}];return(0,t.__)("-- hidden --","rrze-synonym"),(0,t.__)("A - Z","rrze-synonym"),(0,t.__)("Tagcloud","rrze-synonym"),(0,t.__)("Tabs","rrze-synonym"),(0,t.__)("Title","rrze-synonym"),(0,t.__)("ID","rrze-synonym"),(0,t.__)("Sort field","rrze-synonym"),(0,t.__)("ASC","rrze-synonym"),(0,t.__)("DESC","rrze-synonym"),(0,l.createElement)(l.Fragment,null,(0,l.createElement)(s.InspectorControls,null,(0,l.createElement)(y.PanelBody,{title:(0,t.__)("Settings","rrze-synonym")},(0,l.createElement)(y.SelectControl,{label:(0,t.__)("synonym","rrze-synonym"),value:R,options:D,onChange:e=>{j(e),n({id:String(e)})},multiple:!0}),(0,l.createElement)(y.SelectControl,{label:(0,t.__)("Language","rrze-synonym"),options:F,onChange:e=>n({lang:e})}))),(0,l.createElement)("div",{...S},(0,l.createElement)(i(),{block:"create-block/rrze-synonym",attributes:e})))},save:function(){return null}})})();