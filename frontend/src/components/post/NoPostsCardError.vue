<script>

export default {
    props: {
        posts: {
            required: true
        }
    },
    methods: {
        goToCreate() {
            this.$router.replace({ name: 'new_post' })
        }
    }
}
</script>

<template>
    <p v-if="((posts && posts.length == 0)) || !posts" class="no-posts-error-msg">No posts, check back later or <a
            @click="goToCreate()" class="no-posts-error-link"> add a new post.</a>
    </p>
</template>

<style>
.no-posts-error-msg {
    font-size: 1.25rem;
    line-height: 1.75rem;
    font-weight: 700;
    color: var(--text-accent);
}

.no-posts-error-link {
    cursor: pointer;
    text-decoration: underline;
    color: #ceb9e8;
}
</style>

