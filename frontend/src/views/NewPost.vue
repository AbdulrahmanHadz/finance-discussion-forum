<script>
import { postData } from '../javascript/api.js'
import { loggedInId } from '../javascript/loggedin.js'
import ResultMessages from '../components/ResultMessages.vue';
import PostEditing from '../components/post/PostEditing.vue';
import { goBack } from '../javascript/goToPrevious.js'

export default {
    created() {
        this.form.userId = this.userId = loggedInId()
    },
    data() {
        return {
            form: {
                userId: null,
                title: null,
                content: null,
            },
            tags: [],
            successMessage: null,
            errorMessage: null
        }
    },
    components: {
        PostEditing,
        ResultMessages
    },
    methods: {
        onSubmit(updatedValues) {
            this.form = updatedValues.form
            this.errorMessage = null
            if ((this.form.title == null || this.form.title.length == 0) || (this.form.content == null || this.form.content.length == 0)) {
                this.errorMessage = 'Fields cannot be empty'
                return
            }

            this.tags = updatedValues.tagIdsToAdd
            postData('posts', this.form)
                .then(res => {
                    console.log(res)
                    const data = res.data
                    const id = data.data.id
                    this.postTags(id)
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
        postTags(postId) {
            console.log(this.tags)
            if (this.tags && this.tags.length != 0) {
                postData(`tags/post/${postId}`, { 'tagIdsToAdd': this.tags, 'userId': this.userId })
                    .then(res => {
                        this.successMessage = 'Post added, redirecting to post.'
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
                    .finally(() => {
                        this.redirectToPost(postId)
                    })
            } else {
                this.successMessage = 'Post added, redirecting to post.'
                this.redirectToPost(this.postId)
            }
        },
        redirectToPost(postId) {
            setTimeout(() => { this.$router.push({ 'name': 'post', params: { 'id': postId } }) }, 3000);
        },
        cancelPostAddition() {
            goBack(this.$router)
        }
    }

}
</script>

<template>
    <div class="stack">
        <ResultMessages v-if="successMessage != null || errorMessage != null" :successMessage="successMessage"
            :errorMessage="errorMessage" />
        <PostEditing @update="onSubmit" :form="this.form" :tags="this.tags" @cancel="cancelPostAddition" />
    </div>
</template>
