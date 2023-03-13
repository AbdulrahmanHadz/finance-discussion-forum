<script>
import { fetchData } from '../javascript/api.js'
import PostCard from '../components/post/PostCard.vue'
import NoPostsError from '../components/post/NoPostsCardError.vue'
import TagsSearchModal from '../components/TagsSearchModal.vue'
import { tagsData } from '../javascript/tags.js'

export default {
    components: {
        PostCard,
        NoPostsError,
        TagsSearchModal
    },
    data() {
        return {
            searchTerm: null,
            tags: [],
            searchResults: [],
            tagsSearchResults: [],
            userData: {},
            isModalVisible: false,
            searchFinished: false
        }
    },
    created() {
        const querySearch = this.$route.query.q
        if (querySearch) {
            this.searchTerm = querySearch
        }
        console.log(this.$route.query)
        this.searchTags()
        console.log(this.searchResults)
    },
    methods: {
        updateUrl() {
            this.$router.replace({ 'name': 'search', 'query': { 'q': this.searchTerm } })
        },
        onSearchInput(event) {
            this.searchTerm = event.target.value
            this.updateUrl()
            this.fetchSearch()
        },
        fetchSearch() {
            if (this.tags.length != 0 && this.tagsSearchResults.length == 0) {
                this.searchResults = []
                return
            }

            fetchData('posts', { 'title': this.searchTerm, order: { 'bestMatch': 'desc' }, 'ids': this.tagsSearchResults })
                .then(res => {
                    let data = res.data.data
                    this.searchResults = data
                })
                .then(() => {
                    this.fetchUserData()
                })
                .catch(err => {
                    console.log(err)
                })
        },
        fetchUserData() {
            let authorIds = Array.from(new Set(this.searchResults.map(x => x['authorId'])))
            authorIds = authorIds.filter(x => !Object.keys(this.userData).includes(x))

            fetchData('users', { ids: authorIds })
                .then(res => {
                    let users = res.data.data
                    users.forEach(user => {
                        let userId = user.id
                        if (!(userId in this.userData)) {
                            this.userData[userId] = user
                        }
                    });
                    this.searchFinished = true
                })
                .catch(err => {
                    console.log(err)
                })
        },
        showModal() {
            this.isModalVisible = true;
        },
        closeModal() {
            this.isModalVisible = false;
        },
        tagsSearchedNames() {
            if (this.tags.length == 0) {
                return 'Tags to search'
            }

            let tagNames = tagsData().tags.filter(elem => {
                if (this.tags.includes(elem.id)) {
                    return elem
                }
            }).map(elem => {
                return elem.name
            })

            return tagNames.join(', ')
        },
        updateSelectedTags(tagsSet) {
            this.tags = Array.from(tagsSet)
            this.tagsSearchResults = []
            this.searchTags()
        },
        searchTags() {
            if (this.tags.length == 0) {
                this.searchResults = this.fetchSearch()
                return
            }
            fetchData('tags/post', { 'tags': this.tags })
                .then(res => {
                    let data = res.data.data
                    let postIds = Object.keys(data)
                    this.tagsSearchResults = postIds
                })
                .then(() => {
                    this.fetchSearch()
                })
                .catch(err => {
                    console.log(err)
                })
        },
        checkUnemptyFields() {
            return this.searchResults == null || this.searchResults.length == 0
        }
    }
}
</script>

<template>
    <div class="search-page">
        <h3 class="headings">Search for a post</h3>
        <div class="search-boxes">
            <input autofocus class="search-box" :value="searchTerm" @input="onSearchInput" placeholder="Post title" />
            <button type="button" class="tag-modal" @click="showModal" text="Post title">{{
                tagsSearchedNames()
            }}</button>
            <TagsSearchModal v-model="tags" :passedTags="this.tags" v-if="isModalVisible" @close="closeModal"
                @update="updateSelectedTags" />
        </div>
        <div class="section">
            <NoPostsError :posts="searchResults" v-if="checkUnemptyFields" />
            <PostCard v-for="post in searchResults" :key="post.id" :data="post" :user="userData[post.authorId]" />
        </div>
    </div>
</template>

<style>
.search-page {
    height: 100%;
    width: 90%;
    padding: 1%;

    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-start;

    margin: 0 auto;
}

.search-boxes {
    margin-top: 1rem;
    width: 100%;
    margin-bottom: 2rem;

    display: flex;
    flex-direction: row;
    justify-content: space-between;
}

.search-box {
    width: 60%;
    height: 60px;
    text-align: start;
    vertical-align: top;
}

.tag-modal {
    width: 38%;
    height: 60px;
    text-align: start;
    vertical-align: top;
    overflow: hidden;
    text-overflow: ellipsis;
    background-color: #9173b7;
    color: white;
    border-radius: 0.375rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
</style>