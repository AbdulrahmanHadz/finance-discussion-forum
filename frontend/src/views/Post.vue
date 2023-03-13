<script>
import { fetchData } from '../javascript/api.js'
import Post from '../components/post/Post.vue'
import PageNotFound from './PageNotFound.vue'

export default {
    components: {
        Post,
        PageNotFound
    },
    data() {
        return {
            loadingData: true,
            postId: this.$route.params.id,
            postData: {}
        }
    },
    mounted() {
        this.fetchPostData()
    },
    methods: {
        fetchPostData() {
            fetchData(`posts/${this.postId}`)
                .then(res => {
                    this.postData = res.data.data
                    this.loadingData = false
                })
                .catch(err => {
                    console.log(err)
                    let response = err.response
                    if (response.status == 404) {
                        this.loadingData = false
                    }
                })
        }
    }
}
</script>

<template>
    <Post v-if="(Object.keys(postData).length != 0 && !loadingData)" :postData="postData" />
    <PageNotFound v-if="(Object.keys(postData).length == 0 && !loadingData)" />
</template>
