<script>
import { fetchData, postData, deleteData } from '../../javascript/api.js'
import PostSection from './PostSection.vue'
import { loggedInId } from '../../javascript/loggedin.js'
import ReplyModal from './ReplyModal.vue';
import { tagsData } from '../../javascript/tags.js';

export default {
    components: {
        PostSection, ReplyModal
    },
    props: {
        postData: {
            type: Object, required: true
        }
    },
    data() {
        return {
            postId: this.$route.params.id,
            postAuthorId: this.postData.authorId,
            userData: {},
            postComments: [],
            isModalVisible: false,
            form: {
                content: null
            },
            loggedInUserId: loggedInId(),
            tags: []
        }
    },
    mounted() {
        this.addView()
        this.fetchUserData()
        this.fetchTags()
    },
    methods: {
        addView() {
            console.log(this.loggedInUserId)
            console.log(this.postId)
            postData('views', { 'userId': this.loggedInUserId, 'postId': this.postId })
                .then(res => {
                    console.log(res)
                }).catch(err => {
                    console.log(err)
                })
        },
        fetchUserData() {
            fetchData(`users/${this.postData.authorId}`)
                .then(res => {
                    let user = res.data.data
                    let userId = user.id
                    if (!(userId in this.userData)) {
                        this.userData[userId] = user
                    }

                    this.fetchPostComments()
                })
                .catch(err => {
                    console.log(err)
                })
        },
        fetchPostComments() {
            let offset = 0
            let continueLoop = false
            do {
                fetchData(`comments`, { 'postIds': [this.postId], 'offset': offset })
                    .then(res => {
                        let resData = res.data
                        let commentsData = resData.data

                        this.postComments.push(...commentsData)
                        if (resData.limit == commentsData.length) {
                            offset += resData.limit
                            continueLoop = true
                        }
                        continueLoop = false
                    })
                    .catch(err => {
                        console.log(err)
                    })
            }
            while (continueLoop)
            this.getCommentsUsers()
        },
        getCommentsUsers() {
            let authorIds = Array.from(new Set(this.postComments.map(x => x['authorId'])))
            console.log(authorIds)
            authorIds = authorIds.filter(x => !Object.keys(this.userData).includes(x))
            console.log(authorIds)

            fetchData('users', { ids: authorIds })
                .then(res => {
                    let users = res.data.data
                    users.forEach(user => {
                        let userId = user.id
                        if (!(userId in this.userData)) {
                            this.userData[userId] = user
                        }
                    });
                })
                .catch(err => {
                    console.log(err)
                })
        },
        showModal() {
            if (loggedInId() == null) {
                this.$router.push('/login')
            } else {
                this.isModalVisible = true;
            }
        },
        closeModal() {
            this.isModalVisible = false;
        },
        updatePostComments(newValue) {
            console.log(newValue)
            this.postComments.push(newValue)
        },
        checkLoggedInUserToPostAuthor() {
            if (this.loggedInUserId == this.postAuthorId) {
                return true
            }
            return false
        },
        deletePost() {
            if (confirm("Do you really want to delete this post?")) {
                deleteData('posts', this.postId, { 'userId': this.postAuthorId })
                    .then(res => {
                        console.log(res)
                        this.$router.replace('/home')
                    })
                    .catch(error => {
                        console.log(error);
                    })
            }
        },
        editPost() {
            this.$router.replace({ 'name': 'edit_post', 'params': { 'id': this.postId } })
        },
        fetchTags() {
            fetchData(`tags/post`, { 'ids': [this.postId] })
                .then(res => {
                    let data = res.data.data
                    let tags = data[this.postId]
                    let tag_data = tagsData().tags
                    console.log(tags)

                    let postTags = tag_data.filter(elem => {
                        if (tags.includes(elem.id)) {
                            return elem
                        }
                    })

                    this.tags = postTags.map(elem => elem['name'])
                })
                .catch(err => {
                    console.log(err)
                })
        },
        deleteComment(deleteId) {
            this.postComments = this.postComments.filter(obj => obj.id != deleteId)
        }
    }
}
</script>

<template>
    <div class="post">
        <h3 class="headings pb-3">{{ postData.title }}</h3>
        <h3 class="tags-footer pb-6">{{ tags.join(', ') }}</h3>
        <div class="post-user-buttons">
            <v-btn elevation="2" color="green lighten-3" @click="showModal">Reply</v-btn>
            <ReplyModal v-if="isModalVisible" @close="closeModal" :postId="postId" @new="updatePostComments" />

            <div class="post-user-buttons" v-if="checkLoggedInUserToPostAuthor()">
                <v-btn elevation="2" color="grey darken-1" @click="editPost">Edit</v-btn>
                <v-btn elevation="2" color="red darken-2" @click="deletePost">Delete</v-btn>
            </div>
        </div>
        <div class="postSections">
            <PostSection v-if="Object.keys(userData).length != 0 && Object.keys(postData).length != 0"
                :userData="userData[postData.authorId]" :postData="postData" postType="post" />
            <div class="postBreak"></div>

            <PostSection v-if="postComments.length != 0" v-for="comment in postComments" :key="comment.id"
                :userData="userData[comment.authorId]" :postData="comment" @delete="deleteComment" />
        </div>
    </div>
</template>

<style>
.postBreak {
    height: 10px;
    background-color: gray;
    border-radius: 0.375rem;
}

.post {
    width: 90%;
    margin: 0 auto;
    padding: 1%;
}

.postSections {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.post-user-buttons {
    display: flex;
    margin-bottom: 1rem;
    gap: 1rem;
}

.replyButton {
    font-size: 1rem;
    line-height: 1.5rem;
    width: 100%;
    height: 100px;
}

.tags-footer {
    font-size: 1.55rem;
    line-height: 2rem;
    font-weight: 600;
}
</style>