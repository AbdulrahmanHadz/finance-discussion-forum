<script>
import { postData, updateData, fetchData } from '../javascript/api.js'
import { loggedInId } from '../javascript/loggedIn.js'
import ResultMessages from '../components/ResultMessages.vue';
import PostEditing from '../components/post/PostEditing.vue';
import { goBack } from '../javascript/goToPrevious';

export default {
    created() {
        this.form.userId = this.userId = loggedInId()
        this.fetchPost()
        this.fetchTags()
    },
    data() {
        return {
            postData: null,
            form: {
                userId: null,
                title: null,
                content: null,
            },
            tags: [],
            unselectedTags: [],
            successMessage: null,
            errorMessage: null,
            postId: this.$route.params.id,
            fetchingPost: true,
            fetchingTags: true
        }
    },
    components: {
        PostEditing,
        ResultMessages
    },
    methods: {
        fetchPost() {
            fetchData(`posts/${this.postId}`)
                .then(res => {
                    let data = res.data.data
                    this.postData = data
                })
                .then(() => {
                    if (this.userId != this.postData.authorId) {
                        this.fetchingPost = true
                        this.$router.replace({ 'name': 'post', 'params': { 'id': this.postId } })
                        return
                    } else {
                        this.form.title = this.postData.title
                        this.form.content = this.postData.content
                        this.fetchingPost = false
                    }
                })
                .catch(err => {
                    console.log(err)
                })
        },
        fetchTags() {
            fetchData(`tags/post`, { 'ids': [this.postId] })
                .then(res => {
                    let data = res.data.data
                    this.tags.push(...data[this.postId])
                    this.fetchingTags = false
                })
                .catch(err => {
                    console.log(err)
                })
        },
        onSubmit(updatedValues) {
            const updateedForm = updatedValues.form
            const updatedTags = updatedValues.tagIdsToAdd
            const unselectedTags = updatedValues.tagIdsToRemove

            if (this.checkChangedTags(updatedTags, this.tags) || this.checkChangedTags(unselectedTags, this.unselectedTags)) {
                console.log('Updating post tags data')
                this.tags = updatedTags
                this.unselectedTags = unselectedTags
                this.postTags()
            }

            if (this.form.title.localeCompare(updateedForm.title) == 0 || this.form.content.localeCompare(updateedForm.content) == 0) {
                console.log('Updating post data')
                this.form.title = updateedForm.title
                this.form.content = updateedForm.content
                this.updatePost()
            }
        },
        checkChangedTags(baseArray, changedArray) {
            const changedArraySorted = changedArray.slice().sort();
            return baseArray.length === changedArray.length && baseArray.slice().sort().every(function (value, index) {
                return value === changedArraySorted[index];
            });
        },
        updatePost() {
            updateData('posts', this.postId, this.form)
                .then(res => {
                    console.log(res)
                    this.successMessage = 'Post updated, redirecting back.'
                })
                .catch(err => {
                    console.log(err)
                    const errorResponse = err.response
                    const errorData = errorResponse.data
                    const messageInError = "message" in errorData
                    if (messageInError) {
                        this.errorMessage = errorData.message
                    }
                })
                .finally(() => {
                    this.redirectToPost(this.postId)
                })
        },
        postTags() {
            postData(`tags/post/${this.postId}`,
                { 'tagIdsToAdd': this.tags, 'tagIdsToRemove': this.unselectedTags, 'userId': this.userId })
                .then(res => {
                    console.log(res)
                })
                .catch(err => {
                    console.log(err)
                    const errorResponse = err.response
                    const errorData = errorResponse.data
                    const messageInError = "message" in errorData
                    if (messageInError) {
                        this.errorMessage = errorData.message
                    }
                })
        },
        redirectToPost() {
            setTimeout(() => { this.$router.replace({ 'name': 'post', params: { 'id': this.postId } }) }, 3000);
        },
        cancelEdit() {
            goBack(this.$router)
        }
    }

}
</script>

<template>
    <div class="stack" v-if="!fetchingPost">
        <ResultMessages v-if="successMessage != null || errorMessage != null" :successMessage="successMessage"
            :errorMessage="errorMessage" />
        <PostEditing v-if="!fetchingPost && !fetchingTags" @update="onSubmit" :form="this.form"
            :tags="Object.values(this.tags)" :newPost="false" submitButtonLabel="Edit Post" @cancel="cancelEdit" />
    </div>
</template>