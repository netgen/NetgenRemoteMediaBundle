(function(e){function t(t){for(var n,s,o=t[0],c=t[1],l=t[2],u=0,h=[];u<o.length;u++)s=o[u],Object.prototype.hasOwnProperty.call(i,s)&&i[s]&&h.push(i[s][0]),i[s]=0;for(n in c)Object.prototype.hasOwnProperty.call(c,n)&&(e[n]=c[n]);d&&d(t);while(h.length)h.shift()();return r.push.apply(r,l||[]),a()}function a(){for(var e,t=0;t<r.length;t++){for(var a=r[t],n=!0,o=1;o<a.length;o++){var c=a[o];0!==i[c]&&(n=!1)}n&&(r.splice(t--,1),e=s(s.s=a[0]))}return e}var n={},i={app:0},r=[];function s(t){if(n[t])return n[t].exports;var a=n[t]={i:t,l:!1,exports:{}};return e[t].call(a.exports,a,a.exports,s),a.l=!0,a.exports}s.m=e,s.c=n,s.d=function(e,t,a){s.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:a})},s.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},s.t=function(e,t){if(1&t&&(e=s(e)),8&t)return e;if(4&t&&"object"===typeof e&&e&&e.__esModule)return e;var a=Object.create(null);if(s.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)s.d(a,n,function(t){return e[t]}.bind(null,n));return a},s.n=function(e){var t=e&&e.__esModule?function(){return e["default"]}:function(){return e};return s.d(t,"a",t),t},s.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},s.p="/";var o=window["webpackJsonp"]=window["webpackJsonp"]||[],c=o.push.bind(o);o.push=t,o=o.slice();for(var l=0;l<o.length;l++)t(o[l]);var d=c;r.push([0,"chunk-vendors"]),a()})({0:function(e,t,a){e.exports=a("56d7")},"0170":function(e,t,a){},"1be4":function(e,t,a){},"1f12":function(e,t,a){"use strict";var n=a("20b0"),i=a.n(n);i.a},"20b0":function(e,t,a){},"3fb5":function(e,t,a){},"48a2":function(e,t,a){},"56d7":function(e,t,a){"use strict";a.r(t);a("8e6e"),a("28a5");var n=a("bd86"),i=(a("7f7f"),a("5df3"),a("4f7f"),a("75fc")),r=(a("96cf"),a("3b8d")),s=(a("456d"),a("ac6a"),a("cadf"),a("551c"),a("f751"),a("097d"),a("a026")),o=(a("3fb5"),a("6107"),function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("modal",{attrs:{title:this.$root.$data.NgRemoteMediaTranslations.browse_title},on:{close:function(t){return e.$emit("close")}}},[a("media-facets",{attrs:{folders:e.folders,facets:e.facets},on:{change:e.handleFacetsChange}}),a("media-galery",{attrs:{media:e.media,canLoadMore:e.canLoadMore,selectedMediaId:e.selectedMediaId},on:{loadMore:e.handleLoadMore,"media-selected":function(t){return e.$emit("media-selected",t)}}}),e.loading?a("i",{staticClass:"ng-icon ng-spinner"}):e._e()],1)}),c=[],l=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"mediaFacets"},[a("ul",{staticClass:"tabs"},[a("li",{class:{active:e.isType(e.TYPE_IMAGE)}},[a("span",{on:{click:function(t){return e.handleTypeChange(e.TYPE_IMAGE)}}},[e._v(e._s(this.$root.$data.NgRemoteMediaTranslations.browse_image_and_documents))])]),a("li",{class:{active:e.isType(e.TYPE_VIDEO)}},[a("span",{on:{click:function(t){return e.handleTypeChange(e.TYPE_VIDEO)}}},[e._v(e._s(this.$root.$data.NgRemoteMediaTranslations.browse_video))])])]),a("div",{staticClass:"body"},[a("div",{staticClass:"form-field"},[a("label",{attrs:{for:"folder"}},[e._v(e._s(this.$root.$data.NgRemoteMediaTranslations.browse_select_folder))]),a("v-select",{attrs:{options:e.folders,label:"name",reduce:function(e){return e.id},placeholder:this.$root.$data.NgRemoteMediaTranslations.browse_all_folders},on:{input:e.handleFolderChange},model:{value:e.selectedFolder,callback:function(t){e.selectedFolder=t},expression:"selectedFolder"}})],1),a("div",{staticClass:"search-wrapper"},[a("span",{staticClass:"search-label"},[e._v(e._s(this.$root.$data.NgRemoteMediaTranslations.search))]),a("div",{staticClass:"search"},[a("ul",{staticClass:"searchType"}),a("input",{directives:[{name:"model",rawName:"v-model",value:e.query,expression:"query"}],attrs:{type:"text",placeholder:this.$root.$data.NgRemoteMediaTranslations.search_by_name},domProps:{value:e.query},on:{keyup:e.handleQueryChange,input:function(t){t.target.composing||(e.query=t.target.value)}}})]),a("div",{staticClass:"search"},[a("ul",{staticClass:"searchType"}),a("input",{directives:[{name:"model",rawName:"v-model",value:e.tag,expression:"tag"}],attrs:{type:"text",placeholder:this.$root.$data.NgRemoteMediaTranslations.search_by_tag},domProps:{value:e.tag},on:{keyup:e.handleTagChange,input:function(t){t.target.composing||(e.tag=t.target.value)}}})])])])])},d=[],u="image",h="video",p="name",f="tag",m="all",v=a("4a7a"),g=a.n(v),b={name:"MediaFacets",props:["folders","facets"],data:function(){return{TYPE_IMAGE:u,TYPE_VIDEO:h,SEARCH_NAME:p,SEARCH_TAG:f,FOLDER_ALL:m,selectedFolder:this.facets.folder,query:this.facets.query}},methods:{handleSearchChange:function(e){this.$emit("change",{searchType:e})},handleTypeChange:function(e){this.$emit("change",{mediaType:e})},isType:function(e){return this.facets.mediaType===e},handleFolderChange:function(e){this.$emit("change",{folder:this.selectedFolder})},handleQueryChange:function(){this.$emit("change",{query:this.query})},handleTagChange:function(){this.$emit("change",{tag:this.tag})}},components:{"v-select":g.a}},y=b,C=(a("d0eb"),a("2877")),_=Object(C["a"])(y,l,d,!1,null,"a7c3db1c",null),w=_.exports,O=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"mediaGalery"},[a("div",{staticClass:"items"},[e.media.length?e._e():a("div",{staticClass:"folder-empty"},[a("span",{staticClass:"icon-folder"}),e._m(0)]),e._l(e.media,(function(t){return a("div",{key:t.id,staticClass:"media",class:{selected:t.resourceId===e.selectedMediaId}},["image"===t.type||"video"===t.type?a("div",{staticClass:"media-container"},[a("img",{staticClass:"img",attrs:{src:t.browse_url,alt:t.filename}}),a("Label",{staticClass:"filename"},[e._v(e._s(t.filename))]),a("div",{staticClass:"size-description"},[e._v(e._s(t.width)+" x "+e._s(t.height))])],1):a("div",{staticClass:"media-container"},[e._m(1,!0),a("Label",{staticClass:"filename"},[e._v(e._s(t.filename))]),a("div",{staticClass:"size-description"},[e._v(e._s(t.width)+" x "+e._s(t.height))])],1),a("button",{staticClass:"btn btn-blue select-btn",attrs:{type:"button"},on:{click:function(a){return e.$emit("media-selected",t)}}},[e._v("Select")])])}))],2),e.canLoadMore?a("div",{staticClass:"load-more-wrapper"},[a("button",{staticClass:"btn btn-blue",attrs:{type:"button"},on:{click:function(t){return e.$emit("loadMore")}}},[e._v("Load more")])]):e._e()])},S=[function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("span",[a("strong",[e._v("Folder is empty")]),e._v("Upload media from your local storage.")])},function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("span",{staticClass:"video-placeholder"},[a("span",{staticClass:"icon-play"})])}],V={name:"MediaGalery",props:["media","canLoadMore","onLoadMore","selectedMediaId"]},j=V,M=(a("8a70"),Object(C["a"])(j,O,S,!1,null,"35d36ca8",null)),x=M.exports,k=a("01c8"),$=function(e){var t=[];for(var a in e)t.push(encodeURIComponent(a)+"="+encodeURIComponent(e[a]));return t.join("&")},T=function(e){return e[0].toUpperCase()+e.slice(1)},I=function(e){var t=e.split("-"),a=Object(k["a"])(t),n=a[0],r=a.slice(1);return[n].concat(Object(i["a"])(r.map(T))).join("")},P=function(e,t){var a=Math.pow(10,t);return parseFloat(Math.round(e*a)/a).toFixed(t)},E={B:"KB",KB:"MB",MB:"GB",GB:"TB"},F=function e(t){var a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"B",n=E[a];return!n||t<1024?"".concat(P(t,2)," ").concat(a):e(t/1024,n)},z=a("b012"),R=a.n(z),A=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"overlay"},[a("div",{staticClass:"media-modal"},[a("div",{staticClass:"title"},[e._v("\n      "+e._s(e.title)+"\n      "),a("span",{staticClass:"close",on:{click:e.close}},[a("span",{staticClass:"icon-cancel"})])]),a("div",{staticClass:"body"},[e._t("default")],2)])])},N=[],L={name:"Modal",props:["title"],methods:{close:function(){this.$emit("close")}}},Q=L,D=(a("9474"),Object(C["a"])(Q,A,N,!1,null,"04f23be6",null)),B=D.exports;function q(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),a.push.apply(a,n)}return a}function U(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?q(a,!0).forEach((function(t){Object(n["a"])(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):q(a).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}var G=25,W={name:"MediaModal",props:["folders","selectedMediaId","paths"],components:{"media-facets":w,"media-galery":x,modal:B},data:function(){return{media:[],canLoadMore:!1,nextCursor:null,loading:!0,facets:{folder:"",searchType:p,mediaType:u,query:"",tag:""}}},methods:{debouncedLoad:R()((function(e){this.load(e)}),500),load:function(){var e=Object(r["a"])(regeneratorRuntime.mark((function e(){var t,a,n,i,r,s,o=arguments;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return t=o.length>0&&void 0!==o[0]?o[0]:{patch:!1},a=t.patch,this.loading=!0,this.abortController&&this.abortController.abort(),this.abortController=new AbortController,n={limit:G,offset:a?this.media.length:0,q:this.facets.query,mediatype:this.facets.mediaType,folder:this.facets.folder||m,search_type:this.facets.searchType,next_cursor:a?this.nextCursor:null,tag:this.facets.tag},i="".concat(this.paths.browse,"?").concat($(n)),e.prev=6,e.next=9,fetch(i,{signal:this.abortController.signal});case 9:return r=e.sent,e.next=12,r.json();case 12:s=e.sent,this.media=a?this.media.concat(s.hits):s.hits,this.canLoadMore=s.load_more,this.nextCursor=s.next_cursor,this.loading=!1,e.next=23;break;case 19:if(e.prev=19,e.t0=e["catch"](6),20===e.t0.code){e.next=23;break}throw e.t0;case 23:case"end":return e.stop()}}),e,this,[[6,19]])})));function t(){return e.apply(this,arguments)}return t}(),handleLoadMore:function(){this.debouncedLoad({patch:!0})},handleFacetsChange:function(e){this.facets=U({},this.facets,{},e),this.debouncedLoad()}},mounted:function(){this.load()}},Y=W,H=(a("d460"),Object(C["a"])(Y,o,c,!1,null,"420b29ab",null)),J=H.exports,K=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("modal",{attrs:{title:"Upload image"},on:{close:function(t){return e.$emit("close")}}},[e.loading?a("i",{staticClass:"ng-icon ng-spinner"}):a("div",[a("label",{attrs:{for:"folder"}},[e._v("Select Folder")]),a("select-folder",{attrs:{folders:e.folders},on:{change:e.handleFolderChange}}),a("input",{directives:[{name:"model",rawName:"v-model",value:e.newName,expression:"newName"}],attrs:{type:"text"},domProps:{value:e.newName},on:{input:function(t){t.target.composing||(e.newName=t.target.value)}}}),a("button",{attrs:{disabled:""===e.newName,type:"button"},on:{click:e.handleSaveClick}},[e._v("Save")])],1)])},X=[],Z=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"form-field"},[a("v-select",{attrs:{options:e.foldersWithNew,label:"name",reduce:function(e){return e.id},placeholder:"/"},on:{input:e.handleFolderChanged},scopedSlots:e._u([{key:"search",fn:function(t){return["checkbox"===t.attributes.type?a("input",e._g(e._b({directives:[{name:"model",rawName:"v-model",value:e.folderSearchQuery,expression:"folderSearchQuery"}],staticClass:"vs__search",attrs:{type:"checkbox"},domProps:{checked:Array.isArray(e.folderSearchQuery)?e._i(e.folderSearchQuery,null)>-1:e.folderSearchQuery},on:{change:function(t){var a=e.folderSearchQuery,n=t.target,i=!!n.checked;if(Array.isArray(a)){var r=null,s=e._i(a,r);n.checked?s<0&&(e.folderSearchQuery=a.concat([r])):s>-1&&(e.folderSearchQuery=a.slice(0,s).concat(a.slice(s+1)))}else e.folderSearchQuery=i}}},"input",t.attributes,!1),t.events)):"radio"===t.attributes.type?a("input",e._g(e._b({directives:[{name:"model",rawName:"v-model",value:e.folderSearchQuery,expression:"folderSearchQuery"}],staticClass:"vs__search",attrs:{type:"radio"},domProps:{checked:e._q(e.folderSearchQuery,null)},on:{change:function(t){e.folderSearchQuery=null}}},"input",t.attributes,!1),t.events)):a("input",e._g(e._b({directives:[{name:"model",rawName:"v-model",value:e.folderSearchQuery,expression:"folderSearchQuery"}],staticClass:"vs__search",attrs:{type:t.attributes.type},domProps:{value:e.folderSearchQuery},on:{input:function(t){t.target.composing||(e.folderSearchQuery=t.target.value)}}},"input",t.attributes,!1),t.events))]}},{key:"option",fn:function(t){return[t.new?a("div",[e._v("\n        "+e._s(t.name)+"\n        "),a("button",{attrs:{type:"button"}},[e._v("Create new")])]):t.added?a("div",[e._v(e._s(t.name)+" (new)")]):a("div",[e._v(e._s(t.name))])]}}]),model:{value:e.folder,callback:function(t){e.folder=t},expression:"folder"}})],1)},ee=[],te=(a("7514"),{name:"SelectFolder",props:["folders","selectedFolder"],data:function(){return{folderSearchQuery:"",addedFolders:[],folder:this.selectedFolder}},computed:{foldersWithNew:function(){var e=this,t=[].concat(Object(i["a"])(this.folders),Object(i["a"])(this.addedFolders));return 0===this.folderSearchQuery.length?t:t.find((function(t){return t.name===e.folderSearchQuery}))?t:[{name:this.folderSearchQuery,id:this.folderSearchQuery,new:!0}].concat(Object(i["a"])(t))}},methods:{handleFolderChanged:function(e){this.folderSearchQuery="";var t=[].concat(Object(i["a"])(this.folders),Object(i["a"])(this.addedFolders));t.find((function(t){return t.name===e}))||this.addedFolders.push({id:e,name:e,added:!0}),this.$emit("change",this.folder)}},components:{"v-select":g.a}}),ae=te,ne=(a("e624"),Object(C["a"])(ae,Z,ee,!1,null,"e0caddfc",null)),ie=ne.exports,re={name:"UploadModal",props:["folders","loading","name"],data:function(){return{selectedFolder:"",newName:this.name}},components:{"select-folder":ie,modal:B},methods:{handleFolderChange:function(e){this.selectedFolder=e},handleSaveClick:function(){var e=this.newName;this.selectedFolder&&(e="".concat(this.selectedFolder,"/").concat(this.newName)),this.$emit("save",e)}}},se=re,oe=Object(C["a"])(se,K,X,!1,null,"39c1a599",null),ce=oe.exports,le=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("modal",{attrs:{title:"Crop"},on:{close:function(t){return e.$emit("close")}}},[a("crop-sizes",{attrs:{availableVariations:e.availableVariations,allVariationValues:e.allVariationValues,imageSize:e.imageSize,selectedVariation:e.selectedVariation},on:{selected:e.handleVariationSelected,addedVariations:e.handleAddedVariations,removedVariation:e.handleRemovedVariation}}),e._l(e.availableVariations,(function(t,n){return a("div",{key:n,staticClass:"crop-container"},[n===e.selectedVariation?a("crop",{attrs:{value:e.allVariationValues[n],src:e.selectedImage.url,variation:e.availableVariations[n],imageSize:e.imageSize},on:{change:function(t){return e.handleVariationValueChange(n,t)}}}):e._e()],1)})),e.selectedVariation?e._e():a("div",{staticClass:"img-placeholder"},[a("img",{attrs:{src:e.selectedImage.url}})]),a("div",{staticClass:"action-strip"},[a("button",{staticClass:"btn",attrs:{type:"button"},on:{click:e.handleCancelClicked}},[e._v("Cancel")]),a("button",{staticClass:"btn btn-blue",attrs:{type:"button"},on:{click:e.handleSaveClicked}},[a("span",{staticClass:"icon-floppy"}),a("span",[e._v("Save sizes")])])])],2)},de=[],ue=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"sidebar-crop"},[a("div",{staticClass:"buttons"},[e.addingVariations?e._e():a("button",{staticClass:"btn",attrs:{type:"button"},on:{click:e.handleAddCropSize}},[e._v("Add crop size")]),e.addingVariations?a("button",{staticClass:"btn",attrs:{type:"button"},on:{click:e.handleCancel}},[e._v("Cancel")]):e._e(),e.addingVariations?a("button",{staticClass:"btn crop-btn-add",attrs:{type:"button"},on:{click:e.handleAdd}},[e._v("Add")]):e._e()]),e._m(0),a("div",{directives:[{name:"show",rawName:"v-show",value:e.addingVariations,expression:"addingVariations"}],class:{unselectedVariations:e.addingVariations}},e._l(e.unselectedVariations,(function(t){return a("div",{key:t,class:{disabled:!e.isVariationSelectable(t)}},[a("input",{directives:[{name:"model",rawName:"v-model",value:e.newSelection,expression:"newSelection"}],attrs:{type:"checkbox",id:t,disabled:!e.isVariationSelectable(t)},domProps:{value:t,checked:Array.isArray(e.newSelection)?e._i(e.newSelection,t)>-1:e.newSelection},on:{change:function(a){var n=e.newSelection,i=a.target,r=!!i.checked;if(Array.isArray(n)){var s=t,o=e._i(n,s);i.checked?o<0&&(e.newSelection=n.concat([s])):o>-1&&(e.newSelection=n.slice(0,o).concat(n.slice(o+1)))}else e.newSelection=r}}}),a("label",{attrs:{for:t}},[a("span",{staticClass:"name"},[e._v(e._s(t))]),a("span",{staticClass:"formatted-size"},[e._v(e._s(e.formattedSize(t)))])]),e.isVariationSelectable(t)?e._e():a("div",{staticClass:"legend-not-selectable"},[a("span",[e._v("Media is too small.")])])])})),0),a("div",{staticClass:"selectedVariations"},[a("ul",e._l(e.selectedVariations,(function(t){return a("li",{key:t,class:{set:!!e.allVariationValues[t],selected:e.selectedVariation===t,disabled:!e.isVariationSelectable(t)},on:{click:function(a){return e.handleVariationClicked(t)}}},[a("div",[a("span",{staticClass:"name"},[e._v(e._s(t))]),a("span",{staticClass:"formatted-size"},[e._v(e._s(e.formattedSize(t)))])]),e.addingVariations?e._e():a("a",{attrs:{href:"#"}},[a("span",{staticClass:"circle-orange"}),a("span",{staticClass:"icon-trash",on:{click:function(a){return e.removeItem(t)}}})])])})),0)])])},he=[function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"sidebar-crop-label"},[a("span",[e._v("Addded for Confirmation")])])}],pe=a("768b"),fe={name:"CropSizes",props:["availableVariations","allVariationValues","imageSize","selectedVariation"],data:function(){return{newSelection:[],addingVariations:!1}},computed:{unselectedVariations:function(){var e=Object.keys(this.availableVariations),t=Object.keys(this.allVariationValues);return e.difference(t)},selectedVariations:function(){return Object.getOwnPropertyNames(this.allVariationValues)}},methods:{handleAddCropSize:function(){this.addingVariations=!0},handleCancel:function(){this.addingVariations=!1,this.newSelection=[]},handleAdd:function(){this.$emit("addedVariations",this.newSelection),this.newSelection=[],this.addingVariations=!1},removeItem:function(e){this.$emit("removedVariation",e)},formattedSize:function(e){return"".concat(this.availableVariations[e][0]," x ").concat(this.availableVariations[e][1])},isVariationSelectable:function(e){var t=Object(pe["a"])(this.availableVariations[e],2),a=t[0],n=t[1];return this.imageSize.width>=a&&this.imageSize.height>=n},handleVariationClicked:function(e){this.isVariationSelectable(e)&&this.$emit("selected",e)}}},me=fe,ve=(a("1f12"),Object(C["a"])(me,ue,he,!1,null,"55a3bdf8",null)),ge=ve.exports,be=function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"crop"},[a("div",{ref:"cropper",staticClass:"cropper",style:e.cropperStyle},[a("img",{ref:"image",attrs:{src:e.src}}),a("div",{ref:"buttons",staticClass:"buttons",style:e.applyButtonStyle},[a("button",{staticClass:"btn btn-blue",attrs:{type:"button"},on:{click:e.handleReset}},[a("span",{staticClass:"icon-ccw"}),a("span",[e._v("Reset")])]),a("button",{staticClass:"btn btn-blue",attrs:{type:"button"},on:{click:e.handleApply}},[a("span",{staticClass:"icon-ok"}),a("span",[e._v("Apply")])])])]),a("div",[a("h4",[e._v("Preview")]),a("div",{ref:"preview",staticClass:"preview"})])])},ye=[],Ce=a("5435"),_e={name:"Crop",props:["value","variation","src","imageSize"],mounted:function(){this.setCropper()},beforeDestroy:function(){this.destroyCropper()},data:function(){return{crop:{},cropper:null}},methods:{setCropper:function(){var e,t=this.value||{},a=t.x,i=t.y,r=t.w,s=t.h,o={x:a,y:i,width:r,height:s},c=Object(pe["a"])(this.variation,2),l=c[0],d=c[1],u=l>0&&d>0?l/d:void 0;this.destroyCropper();this.$refs.cropper.clientWidth,this.imageSize.width;this.cropper=new Ce["a"](this.$refs.image,(e={viewMode:2,dragMode:"none",autoCrop:!0,data:o,aspectRatio:u,guides:!0,movable:!1,rotatable:!1},Object(n["a"])(e,"guides",!1),Object(n["a"])(e,"center",!1),Object(n["a"])(e,"zoomable",!1),Object(n["a"])(e,"scalable",!0),Object(n["a"])(e,"minCropBoxWidth",50),Object(n["a"])(e,"minCropBoxHeight",50),Object(n["a"])(e,"crop",this.handleCrop),Object(n["a"])(e,"preview",this.$refs.preview),e)),this.cropper.setData(o)},handleCrop:function(e){this.crop=this.cropper.getData(!0)},destroyCropper:function(){this.cropper&&this.cropper.destroy()},handleReset:function(){this.cropper.reset()},handleApply:function(){var e=this.cropper.getData(!0),t=e.x,a=e.y,n=e.width,i=e.height;this.$emit("change",{x:t,y:a,w:n,h:i})}},computed:{applyButtonStyle:function(){var e=this.crop,t=e.x,a=e.y,n=e.width,i=e.height,r=this.$refs.buttons?this.$refs.buttons.clientWidth:0,s=this.$refs.cropper?this.$refs.cropper.clientWidth/this.imageSize.width:1;return{top:"".concat((a+i)*s+10,"px"),left:"".concat((t+n)*s-r-1,"px")}},cropperStyle:function(){var e=this.imageSize.height/this.imageSize.width*100;return{"padding-bottom":"".concat(e,"%"),height:"0px",width:"100%"}}}},we=_e,Oe=(a("d21c"),Object(C["a"])(we,be,ye,!1,null,"b4d83f7e",null)),Se=Oe.exports,Ve=function(e){return function(t){return Object.keys(t).reduce((function(a,n){return e(t[n],n)&&(a[n]=t[n]),a}),{})}},je=function(e){return function(t){return!e(t)}},Me=function(e){return function(t){return e===t}},xe=function(e){return!!e},ke=je(Me(void 0));function $e(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),a.push.apply(a,n)}return a}function Te(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?$e(a,!0).forEach((function(t){Object(n["a"])(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):$e(a).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}var Ie={name:"CropModal",props:["availableVariations","selectedImage"],components:{modal:B,"crop-sizes":ge,crop:Se},data:function(){return{selectedVariation:null,newVariationValues:{}}},computed:{allVariationValues:function(){return Ve(ke)(Te({},this.selectedImage.variations,{},this.newVariationValues))},imageSize:function(){return{height:this.selectedImage.height,width:this.selectedImage.width}}},methods:{handleVariationSelected:function(e){this.selectedVariation=e},handleAddedVariations:function(e){this.newVariationValues=Te({},this.newVariationValues,{},e.reduce((function(e,t){return e[t]=null,e}),{}))},handleRemovedVariation:function(e){this.newVariationValues=Te({},this.newVariationValues,Object(n["a"])({},e,void 0))},handleVariationValueChange:function(e,t){this.newVariationValues=Te({},this.newVariationValues,Object(n["a"])({},e,t))},handleCancelClicked:function(){this.newVariationValues={},this.$emit("close")},handleSaveClicked:function(){this.$emit("change",Te({},this.newVariationValues)),this.newVariationValues={},this.$emit("close")}}},Pe=Ie,Ee=(a("e6ba"),Object(C["a"])(Pe,le,de,!1,null,"6540a3a8",null)),Fe=Ee.exports,ze={bind:function(e,t,a){var n=I(t.arg);a.context[n]=t.value}};a("b39d");function Re(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),a.push.apply(a,n)}return a}function Ae(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?Re(a,!0).forEach((function(t){Object(n["a"])(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):Re(a).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}s["a"].config.productionTip=!1;var Ne=function(){document.querySelectorAll(".ngremotemedia-type").forEach((function(e,t){window["remoteMedia".concat(t)]=new s["a"]({el:e,directives:{init:ze},data:{NgRemoteMediaTranslations:NgRemoteMediaTranslations,RemoteMediaSelectedImage:window["RemoteMediaSelectedImage_".concat(e.dataset.id)],RemoteMediaConfig:RemoteMediaConfig,folders:[],mediaModalOpen:!1,cropModalOpen:!1,uploadModalOpen:!1,uploadModalLoading:!1,selectedImage:{id:"",name:"",type:"image",url:"",browse_url:"",alternateText:"",tags:[],size:"",variations:{},height:0,width:0},config:{paths:{},availableVariations:{}},allTags:[]},computed:{nonImagePreviewClass:function(){return"video"===this.selectedImage.type?"ng-video":"ng-book"},formattedSize:function(){return F(this.selectedImage.size)},stringifiedVariations:function(){return JSON.stringify(Ve(xe)(this.selectedImage.variations))},isCroppable:function(){return!!this.selectedImage.id&&"image"===this.selectedImage.type&&Object.keys(this.config.availableVariations).length>0}},components:{"media-modal":J,"v-select":g.a,"crop-modal":Fe,"upload-modal":ce},methods:{fetchFolders:function(){var e=Object(r["a"])(regeneratorRuntime.mark((function e(){var t,a;return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:return e.next=2,fetch(this.config.paths.folders);case 2:return t=e.sent,e.next=5,t.json();case 5:a=e.sent,this.folders=a;case 7:case"end":return e.stop()}}),e,this)})));function t(){return e.apply(this,arguments)}return t}(),handleBrowseMediaClicked:function(){var e=Object(r["a"])(regeneratorRuntime.mark((function e(){return regeneratorRuntime.wrap((function(e){while(1)switch(e.prev=e.next){case 0:this.mediaModalOpen=!0,this.fetchFolders();case 2:case"end":return e.stop()}}),e,this)})));function t(){return e.apply(this,arguments)}return t}(),handleCropClicked:function(){this.cropModalOpen=!0},handleMediaModalClose:function(){this.mediaModalOpen=!1},handleCropModalClose:function(){this.cropModalOpen=!1},handleUploadModalClose:function(){this.uploadModalOpen=!1},handleTagsInput:function(e){this.allTags=Object(i["a"])(new Set([].concat(Object(i["a"])(this.allTags),Object(i["a"])(e))))},handleMediaSelected:function(e){this.selectedImage={id:e.resourceId,name:e.filename,type:e.type,url:e.url,alternateText:"",tags:e.tags,size:e.filesize,variations:{},height:e.height,width:e.width},this.mediaModalOpen=!1},handleRemoveMediaClicked:function(){this.selectedImage={id:"",name:"",type:"image",url:"",alternateText:"",tags:[],size:0,variations:{},height:0,width:0},this.$refs.fileInput.value=null},handleFileInputChange:function(e){this.uploadModalOpen=!0,this.uploadModalLoading=!0,this.fetchFolders();var t=e.target.files.item(0);if(t)if(this.selectedImage={id:t.name,name:t.name,type:this.getFileType(t),url:"",alternateText:"",tags:[],size:t.size,variations:{},height:0,width:0},"image"===this.selectedImage.type){var a=new FileReader;a.addEventListener("load",function(){this.$refs.image.onload=function(){this.selectedImage.width=this.$refs.image.naturalWidth,this.selectedImage.height=this.$refs.image.naturalHeight,this.uploadModalLoading=!1}.bind(this),this.selectedImage.url=a.result}.bind(this),!1),a.readAsDataURL(t)}else this.uploadModalLoading=!1},handleVariationCropChange:function(e){this.selectedImage=Ae({},this.selectedImage,{variations:Ae({},this.selectedImage.variations,{},e)})},handleUploadModalSave:function(e){this.selectedImage=Ae({},this.selectedImage,{name:e,id:e}),this.uploadModalOpen=!1},getFileType:function(e){var t=e.type.split("/")[0];return"video"!==t&&"image"!==t?"other":t}},mounted:function(){this.allTags=Object(i["a"])(this.selectedImage.tags)}})}))};"complete"===document.readyState||"loading"!==document.readyState&&!document.documentElement.doScroll?Ne():document.addEventListener("DOMContentLoaded",Ne)},"84d8":function(e,t,a){},"8a3f":function(e,t,a){},"8a70":function(e,t,a){"use strict";var n=a("fd72"),i=a.n(n);i.a},9474:function(e,t,a){"use strict";var n=a("abaa"),i=a.n(n);i.a},abaa:function(e,t,a){},b39d:function(e,t){Array.prototype.difference||(Array.prototype.difference=function(e){return this.filter((function(t){return e.indexOf(t)<0}))})},d0eb:function(e,t,a){"use strict";var n=a("0170"),i=a.n(n);i.a},d21c:function(e,t,a){"use strict";var n=a("1be4"),i=a.n(n);i.a},d460:function(e,t,a){"use strict";var n=a("48a2"),i=a.n(n);i.a},e624:function(e,t,a){"use strict";var n=a("84d8"),i=a.n(n);i.a},e6ba:function(e,t,a){"use strict";var n=a("8a3f"),i=a.n(n);i.a},fd72:function(e,t,a){}});
//# sourceMappingURL=app.js.map