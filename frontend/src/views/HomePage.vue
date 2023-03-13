<script>
import { fetchData } from '../javascript/api.js'
import PostCard from '../components/post/PostCard.vue'
import ArrowRightIcon from '../components/icons/ArrowRightIcon.vue'
import NoPostsError from '../components/post/NoPostsCardError.vue'


export default {
    components: {
        PostCard,
        ArrowRightIcon,
        NoPostsError
    },
    data() {
        return {
            popularPosts: [],
            userData: {},
            latestPosts: [],
            views: []
        }
    },
    created() {
        this.fetchPosts()
        this.fetchPopular()
        this.fetchViews()
    },
    methods: {
        fetchPopular() {
            fetchData(`posts/popular`)
                .then(res => {
                    let popularIds = []
                    let popularPostsRaw = res.data.data
                    popularIds = popularPostsRaw.map(x => x['postId'])

                    if (popularIds) {
                        fetchData('posts', { ids: popularIds })
                            .then(resp => {
                                if (resp.data.data) {
                                    this.popularPosts = resp.data.data.map(function (item) {
                                        var n = popularIds.indexOf(item['id']);
                                        popularIds[n] = '';
                                        return [n, item]
                                    }).sort().map(function (j) { return j[1] })
                                    console.log(this.popularPosts)

                                    this.fetchUsersData(this.popularPosts)
                                }

                                if (this.popularPosts.length == 0) {
                                    this.popularPosts.push(...this.latestPosts)
                                }
                            })
                            .catch(erro => {
                                console.log(erro)
                            })
                    }
                })
                .catch(err => {
                    console.log(err)
                })
        },
        fetchPosts() {
            fetchData(`posts`, { limit: 5, order: { 'createdAt': 'desc' } })
                .then(res => {
                    let posts = res.data.data
                    if (posts) {
                        this.latestPosts = posts
                        this.fetchUsersData(posts)
                    }
                })
                .catch(err => {
                    console.log(err)
                    return null
                })
        },
        fetchUsersData(posts) {
            let userIds = Array.from(new Set(posts.map(x => x['authorId'])))
            userIds = userIds.filter(x => !Object.keys(this.userData).includes(x))

            fetchData('users', { ids: userIds })
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
        goToLatest() {
            this.$router.push('/browse')
        },
        fetchViews() {
            let posts = [...this.latestPosts, ...this.popularPosts]
            let postsIds = posts.map(post => {
                return post['id']
            })
            fetchData('views', { 'postIds': postsIds })
                .then(res => {
                    this.views = res.data.data
                })
                .catch(err => {
                    console.log(err)
                })
        },
        getViews(postId) {
            let view = this.views.filter(obj => {
                let found = obj.postId == postId
                if (found) {
                    return obj.views
                }
            })
            console.log(view)

            if (view != null || view.length != 0) {
                return view[0].views
            }
            return 0
        },
    }
}
</script>

<template>
    <div class="homepage-section-headings">
        <h3 class="headings">Popular</h3>
        <div class="section">
            <NoPostsError :posts="popularPosts" />
            <PostCard v-if="(Object.keys(userData).length != 0)" v-for="post in popularPosts.slice(0, 3)" :key="post.id"
                :data="post" :user="userData[post.authorId]" />
        </div>
    </div>
    <div class="homepage-section-headings">
        <div class="latest" @click="goToLatest">
            <h3 class="headings">Latest</h3>
            <ArrowRightIcon class="arrow-right-icon" />
        </div>
        <div class="section">
            <NoPostsError :posts="latestPosts" />
            <PostCard v-if="Object.keys(userData).length != 0" v-for="post in latestPosts.slice(0, 3)" :key="post.id"
                :data="post" :user="userData[post.authorId]" />
        </div>
    </div>
</template>

<style>
.latest:hover {
    cursor: pointer;
}

.latest {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.arrow-right-icon {
    width: 40px;
    height: 40px;
}

.homepage-section-headings {
    padding: 1%;
    width: 90%;
    margin: 0 auto;
}
</style>
