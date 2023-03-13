<script>
import { fetchData } from '../javascript/api.js';
import { loggedInId } from '../javascript/loggedin';
import PostCard from '../components/post/PostCard.vue'
import NoPostsError from '../components/post/NoPostsCardError.vue'

export default {
    components: {
        PostCard,
        NoPostsError
    },
    data() {
        return {
            userId: loggedInId(),
            userData: {},
            username: 'username',
            email: 'email',
            numPosts: 0,
            posts: []
        }
    },
    created() {
        this.fetchLoggedInUserData()
        this.fetchNumberOfPosts()
        this.fetchPosts()
    },
    methods: {
        fetchLoggedInUserData() {
            fetchData(`users/${this.userId}`)
                .then(res => {
                    let data = res.data.data
                    this.username = data.username
                    this.email = data.email
                })
                .catch(err => {
                    console.log(err)
                })
        },
        fetchNumberOfPosts() {
            fetchData(`users/${this.userId}/posts`)
                .then(res => {
                    let data = res.data.data
                    this.numPosts = data.posts
                })
                .catch(err => {
                    console.log(err)
                })
        },
        fetchPosts() {
            fetchData('posts', { 'authorId': this.userId })
                .then(res => {
                    let data = res.data.data
                    this.posts = data
                }).catch(err => {
                    console.log(err)
                })
        }
    }
}
</script>

<template>
    <div class="full-container container">
        <div class="headings">
            <p>{{ username }}</p>
            <p>{{ email }}</p>

            <p>{{ numPosts }} Posts</p>
        </div>
        <div class="section">
            <NoPostsError :posts="posts" />
            <PostCard v-if="posts.length != 0" v-for="post in posts" :key="post.id" :data="post" :user="userData" />
        </div>
    </div>
</template>
<style>
.container {
    height: 100%;
    width: 90%;
    padding: 1%;
    margin: 0 auto;
}
</style>