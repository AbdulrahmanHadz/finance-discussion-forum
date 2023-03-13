<script>
import PostContent from './PostContent.vue'
import PostUser from './PostUser.vue'
import { formatRelativeDate } from '../../javascript/relativeTime.js'
import ReplyModal from './ReplyModal.vue'
import { loggedInId } from '../../javascript/loggedin'
import { updateData, deleteData, fetchData } from '../../javascript/api'

export default {
    data() {
        return {
            isModalVisible: false,
            loggedInUserId: loggedInId()
        }
    },
    props: {
        postData: {
            type: String, required: true
        },
        userData: {
            type: String, required: true
        },
        postType: {
            type: String, default: 'comment'
        }
    },
    components: {
        PostContent, PostUser, ReplyModal
    },
    methods: {
        convertDateTime(date) {
            return formatRelativeDate(date)
        },
        checkEdited() {
            if (this.postData.editedAt != null) {
                return 'Edited: ' + this.convertDateTime(this.postData.editedAt)
            } else {
                return 'Posted: ' + this.convertDateTime(this.postData.createdAt)
            }
        },
        showModal() {
            this.isModalVisible = true;
        },
        closeModal() {
            this.isModalVisible = false;
        },
        checkLoggedInUserToPostAuthor() {
            if (this.loggedInUserId == this.postData.authorId) {
                return true
            }
            return false
        },
        deletePost() {
            if (confirm("Do you really want to delete this comment?")) {
                deleteData('comments', this.postData.id, { 'userId': this.postData.authorId })
                    .then(res => {
                        console.log(res)
                        this.$emit('delete', this.postData.id)
                    })
                    .catch(error => {
                        console.log(error);
                    })
            }
        },
        editComment(content) {
            if (!this.postData) {
                return
            }
            console.log(content)
            this.postData.content = content.content
            this.postData.editedAt = content.editedAt
        },
    }
}

</script>

<template>
    <div class="section postSection" v-if="userData != null">
        <table class="postsTable">
            <tr>
                <PostUser :user="userData" />
                <PostContent :content="postData.content" />
            </tr>
            <tr class="content-footer-row">
                <td class="content-footer-buttons" v-if="postType !== 'post' && checkLoggedInUserToPostAuthor()">
                    <v-btn elevation="2" color="grey darken-1" @click="showModal">Edit</v-btn>
                    <ReplyModal v-if="isModalVisible" @close="closeModal" :postId="this.postData.id"
                        @update="editComment" :content="this.postData.content" />
                    <v-btn elevation="2" color="red darken-2" @click="deletePost">Delete</v-btn>
                </td>
                <td colspan="2" class="content-footer-data">{{ checkEdited() }}</td>
            </tr>
        </table>
    </div>
</template>

<style>
.postSection {
    gap: 3rem;
    padding-top: 2%;
    padding-bottom: 2%;
    padding-right: 0%;
    padding-left: 0%;
}

.postsTable {
    border-collapse: separate;
    border-spacing: 20px 0;
}

.userCard {
    background-color: var(--form-input);
    border-radius: 0.375rem;
    width: 20%;
    padding: 2%;
    vertical-align: top;
}


.content-footer-row {
    text-align: right;
}

.content-footer-data {
    padding-top: 5px;

    font-size: 1rem;
    line-height: 1.5rem;
    font-weight: 700;
}

.content-footer-buttons {
    text-align: left;
    padding-top: 1rem;
    gap: 1rem;
    display: flex;
}
</style>