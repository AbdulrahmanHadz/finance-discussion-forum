import{_ as f,t as S,r as i,o,c as d,w as y,a,b as C,d as u,e as T,f as k,F as b,T as v,P as I,N as R,g as _,h as m}from"./index.51d15672.js";import{C as M}from"./CloseIcon.2333a6d7.js";import{S as x}from"./SelectTags.89770421.js";const D={data(){return{tagsData:S().tags,tags:new Set}},created(){console.log(this.passedTags),this.passedTags.forEach(s=>this.tags.add(s)),console.log(this.tags)},methods:{close(){this.$emit("close")},updateInput(s){let e=s.target.value;s.target.checked?this.tags.add(e):this.tags.delete(e),this.$emit("update",this.tags)},tagChecked(s){return this.tags.has(s)}},components:{CloseIcon:M,SelectTags:x},props:{passedTags:{type:Object,default:[]}}},N={class:"tag-search-backdrop"},P={class:"tag-search-base",id:"search-tags","aria-label":"Tags to search"},V={class:"tag-search-header"},U=a("label",{class:"tag-search-header"},"Tags to search",-1),w={class:"tags-container"},q={class:"tag-checkbox-div"},E=["value","id","checked"];function F(s,e,h,n,r,t){const g=i("CloseIcon");return o(),d(v,{name:"tag-search-fade"},{default:y(()=>[a("div",N,[a("div",P,[a("div",V,[U,C(g,{"aria-label":"Close Tag Search",class:"btn-close",onClick:t.close},null,8,["onClick"])]),a("div",w,[(o(!0),u(b,null,T(r.tagsData,l=>(o(),u("label",{onChange:e[0]||(e[0]=(...p)=>t.updateInput&&t.updateInput(...p))},[a("div",q,[a("input",{type:"checkbox",value:l.id,id:l.id,checked:t.tagChecked(l.id)},null,8,E),a("span",null,k(l.name),1)])],32))),256))])])])]),_:1})}const B=f(D,[["render",F]]);const j={components:{PostCard:I,NoPostsError:R,TagsSearchModal:B},data(){return{searchTerm:null,tags:[],searchResults:[],tagsSearchResults:[],userData:{},isModalVisible:!1,searchFinished:!1}},created(){const s=this.$route.query.q;s&&(this.searchTerm=s),console.log(this.$route.query),this.searchTags(),console.log(this.searchResults)},methods:{updateUrl(){this.$router.replace({name:"search",query:{q:this.searchTerm}})},onSearchInput(s){this.searchTerm=s.target.value,this.updateUrl(),this.fetchSearch()},fetchSearch(){if(this.tags.length!=0&&this.tagsSearchResults.length==0){this.searchResults=[];return}_("posts",{title:this.searchTerm,order:{bestMatch:"desc"},ids:this.tagsSearchResults}).then(s=>{let e=s.data.data;this.searchResults=e}).then(()=>{this.fetchUserData()}).catch(s=>{console.log(s)})},fetchUserData(){let s=Array.from(new Set(this.searchResults.map(e=>e.authorId)));s=s.filter(e=>!Object.keys(this.userData).includes(e)),_("users",{ids:s}).then(e=>{e.data.data.forEach(n=>{let r=n.id;r in this.userData||(this.userData[r]=n)}),this.searchFinished=!0}).catch(e=>{console.log(e)})},showModal(){this.isModalVisible=!0},closeModal(){this.isModalVisible=!1},tagsSearchedNames(){return this.tags.length==0?"Tags to search":S().tags.filter(e=>{if(this.tags.includes(e.id))return e}).map(e=>e.name).join(", ")},updateSelectedTags(s){this.tags=Array.from(s),this.tagsSearchResults=[],this.searchTags()},searchTags(){if(this.tags.length==0){this.searchResults=this.fetchSearch();return}_("tags/post",{tags:this.tags}).then(s=>{let e=s.data.data,h=Object.keys(e);this.tagsSearchResults=h}).then(()=>{this.fetchSearch()}).catch(s=>{console.log(s)})},checkUnemptyFields(){return this.searchResults==null||this.searchResults.length==0}}},O={class:"search-page"},A=a("h3",{class:"headings"},"Search for a post",-1),L={class:"search-boxes"},z=["value"],G={class:"section"};function H(s,e,h,n,r,t){const g=i("TagsSearchModal"),l=i("NoPostsError"),p=i("PostCard");return o(),u("div",O,[A,a("div",L,[a("input",{autofocus:"",class:"search-box",value:r.searchTerm,onInput:e[0]||(e[0]=(...c)=>t.onSearchInput&&t.onSearchInput(...c)),placeholder:"Post title"},null,40,z),a("button",{type:"button",class:"tag-modal",onClick:e[1]||(e[1]=(...c)=>t.showModal&&t.showModal(...c)),text:"Post title"},k(t.tagsSearchedNames()),1),r.isModalVisible?(o(),d(g,{key:0,modelValue:r.tags,"onUpdate:modelValue":e[2]||(e[2]=c=>r.tags=c),passedTags:this.tags,onClose:t.closeModal,onUpdate:t.updateSelectedTags},null,8,["modelValue","passedTags","onClose","onUpdate"])):m("",!0)]),a("div",G,[t.checkUnemptyFields?(o(),d(l,{key:0,posts:r.searchResults},null,8,["posts"])):m("",!0),(o(!0),u(b,null,T(r.searchResults,c=>(o(),d(p,{key:c.id,data:c,user:r.userData[c.authorId]},null,8,["data","user"]))),128))])])}const W=f(j,[["render",H]]);export{W as default};