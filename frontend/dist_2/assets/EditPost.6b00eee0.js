import{_ as u,g as i,u as p,p as m,r as g,o as r,d as f,c as l,h as n}from"./index.51d15672.js";import{R as I}from"./ResultMessages.ee1a2669.js";import{P as T}from"./PostEditing.ed22746f.js";import{g as P}from"./goToPrevious.cf24a95f.js";import"./SubmitButton.4666d5fc.js";import"./SelectTags.89770421.js";function M(){const s=_();return s&&s.hasOwnProperty("id")?s.id:null}function _(){return JSON.parse(localStorage.getItem("login"))}const k={created(){this.form.userId=this.userId=M(),this.fetchPost(),this.fetchTags()},data(){return{postData:null,form:{userId:null,title:null,content:null},tags:[],unselectedTags:[],successMessage:null,errorMessage:null,postId:this.$route.params.id,fetchingPost:!0,fetchingTags:!0}},components:{PostEditing:T,ResultMessages:I},methods:{fetchPost(){i(`posts/${this.postId}`).then(s=>{let t=s.data.data;this.postData=t}).then(()=>{if(this.userId!=this.postData.authorId){this.fetchingPost=!0,this.$router.replace({name:"post",params:{id:this.postId}});return}else this.form.title=this.postData.title,this.form.content=this.postData.content,this.fetchingPost=!1}).catch(s=>{console.log(s)})},fetchTags(){i("tags/post",{ids:[this.postId]}).then(s=>{let t=s.data.data;this.tags.push(...t[this.postId]),this.fetchingTags=!1}).catch(s=>{console.log(s)})},onSubmit(s){const t=s.form,e=s.tagIdsToAdd,a=s.tagIdsToRemove;(this.checkChangedTags(e,this.tags)||this.checkChangedTags(a,this.unselectedTags))&&(console.log("Updating post tags data"),this.tags=e,this.unselectedTags=a,this.postTags()),(this.form.title.localeCompare(t.title)==0||this.form.content.localeCompare(t.content)==0)&&(console.log("Updating post data"),this.form.title=t.title,this.form.content=t.content,this.updatePost())},checkChangedTags(s,t){const e=t.slice().sort();return s.length===t.length&&s.slice().sort().every(function(a,o){return a===e[o]})},updatePost(){p("posts",this.postId,this.form).then(s=>{console.log(s),this.successMessage="Post updated, redirecting back."}).catch(s=>{console.log(s);const e=s.response.data;"message"in e&&(this.errorMessage=e.message)}).finally(()=>{this.redirectToPost(this.postId)})},postTags(){m(`tags/post/${this.postId}`,{tagIdsToAdd:this.tags,tagIdsToRemove:this.unselectedTags,userId:this.userId}).then(s=>{console.log(s)}).catch(s=>{console.log(s);const e=s.response.data;"message"in e&&(this.errorMessage=e.message)})},redirectToPost(){setTimeout(()=>{this.$router.replace({name:"post",params:{id:this.postId}})},3e3)},cancelEdit(){P(this.$router)}}},E={key:0,class:"stack"};function D(s,t,e,a,o,c){const h=g("ResultMessages"),d=g("PostEditing");return o.fetchingPost?n("",!0):(r(),f("div",E,[o.successMessage!=null||o.errorMessage!=null?(r(),l(h,{key:0,successMessage:o.successMessage,errorMessage:o.errorMessage},null,8,["successMessage","errorMessage"])):n("",!0),!o.fetchingPost&&!o.fetchingTags?(r(),l(d,{key:1,onUpdate:c.onSubmit,form:this.form,tags:Object.values(this.tags),newPost:!1,submitButtonLabel:"Edit Post",onCancel:c.cancelEdit},null,8,["onUpdate","form","tags","onCancel"])):n("",!0)]))}const B=u(k,[["render",D]]);export{B as default};
