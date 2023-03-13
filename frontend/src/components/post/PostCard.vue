<script>
import { fetchData } from '../../javascript/api.js'
import CommentIcon from '../icons/CommentIcon.vue'
import EyeIcon from '../icons/EyeIcon.vue'
import ClockIcon from '../icons/ClockIcon.vue'
import { formatRelativeDate } from '../../javascript/relativeTime.js'


export default {
    components: {
        CommentIcon,
        EyeIcon,
        ClockIcon
    },
    data() {
        return {
            numComments: 0,
            postId: null,
            numViews: 0
        }
    },
    created() {
        this.postId = this.getPostId()
        this.fetchPostComments()

        console.log(this.views)
        if (this.views == 0) {
            this.fetchPostViews()
        } else {
            this.numViews = this.views
        }
    },
    props: {
        data: {
            type: Object, required: true
        },
        user: {
            type: Object, required: true
        },
        views: {
            type: Number, default: 0
        }
    },
    methods: {
        fetchPostComments() {
            if (this.postId == null) {
                return
            }

            fetchData(`comments/posts/${this.postId}`)
                .then(res => {
                    console.log(res)
                    const data = res.data
                    this.numComments = data.data.numReplies
                })
                .catch(err => {
                    console.log(err)
                })
        },
        fetchPostViews() {
            if (this.postId == null) {
                return
            }

            fetchData(`views/${this.postId}`, { 'postIds': [this.postId] })
                .then(res => {
                    console.log(res)
                    const data = res.data.data
                    if (data.length != 0) {
                        this.numViews = data[0].views
                    }
                })
                .catch(err => {
                    console.log(err)
                })
        },
        getPostId() {
            return this.data.id
        },
        goToPost() {
            this.$router.push({ 'name': 'post', params: { 'id': this.postId } })
        },
        convertDateTime(date) {
            return formatRelativeDate(date)
        },
        getUsername() {
            if (this.user) {
                return this.user.username
            }
            return null
        }
    }
}
</script>

<template>
    <div class="card" @click="goToPost">
        <div class="cardLeft">
            <h3 class="card-title">{{ data.title }}</h3>
        </div>
        <div class="card-right">
            <h3 class="username-card">{{ getUsername() }}</h3>
            <div class="icon-text">
                <EyeIcon class="comment-icon" />
                <p class="right-text">{{ numViews }}</p>
            </div>
            <div class="icon-text">
                <CommentIcon class="comment-icon" />
                <p class="right-text">{{ numComments }}</p>
            </div>
            <div class="icon-text">
                <ClockIcon class="comment-icon" />
                <p class="post-card-time">{{ convertDateTime(data.createdAt) }}</p>
            </div>
        </div>
    </div>
</template>

<style>
.card {
    /* margin-bottom: 1%; */
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    padding: 1%;
    padding-left: 2%;
    padding-right: 2%;
    background-color: var(--card-colour);
    border-radius: 0.375rem;
}

.card:hover {
    cursor: pointer;
}

.card-title {
    font-size: 1.5rem;
    line-height: 2rem;
    font-weight: 700;
    width: 400px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card-right {
    display: flex;
    justify-content: right;
    align-items: center;
    gap: 0.8rem;
    width: 300px;
}

.username-card {
    font-size: 1.25rem;
    line-height: 1.875rem;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
}

.icon-text {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}

.comment-icon {
    height: 20px;
    width: 20px;
}

.right-text {
    font-size: 1.125rem;
    line-height: 1.5rem;
    font-weight: 400;
}

.post-card-time {
    font-size: 0.875rem;
    line-height: 1.25rem;
    font-weight: 400;
}
</style>