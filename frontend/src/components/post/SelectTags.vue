<script>
import { tagsData } from '../../javascript/tags.js';

export default {
    props: {
        passedTags: {
            type: Object, default: []
        }
    },
    created() {
        console.log(this.passedTags)
        this.fetchTags()
    },
    data() {
        return {
            categories: [],
            tags: []
        }
    },
    methods: {
        fetchTags() {
            let tags = tagsData().tags
            this.categories = tags.filter(element =>
                element['description'] == null
            )
            this.tags = tags.filter(element =>
                element['description'] != null
            )

            this.categories = this.categories.map(res => {
                res['checked'] = this.isChecked(res.id)
                return res
            })

            this.tags = this.tags.map(res => {
                res['checked'] = this.isChecked(res.id)
                return res
            })
        },
        onInput(event) {
            const updateValues = { 'tagIdsToAdd': [], 'tagIdsToRemove': [] }
            let tag = event.target.value
            let checked = event.target.checked

            if (checked) {
                updateValues.tagIdsToAdd = [tag]
            } else {
                updateValues.tagIdsToRemove = [tag]
            }
            this.$emit('update', updateValues)
        },
        isChecked(tagId) {
            return Object.values(this.passedTags).includes(tagId)
        }
    }

}
</script>

<template>
    <div class="show-tags" v-if="(categories && tags)">
        <div class="tags-container">
            <p class="title-create">Categories:</p>

            <label v-for="category in categories" @change="onInput">
                <div class="tag-checkbox-div">
                    <input type="checkbox" :value="category.id" :id="category.id" :checked="category.checked">
                    <span>{{ category.name }}</span>
                </div>
            </label>
        </div>

        <div class="tags-container">
            <p class="title-create">Tags:</p>

            <label v-for="tag in tags" @change="onInput">
                <div class="tag-checkbox-div">
                    <input type="checkbox" :value="tag.id" :id="tag.id" :checked="tag.checked">
                    <span class=" checkbox-style">{{ tag.name }}</span>
                </div>
            </label>
        </div>
    </div>
</template>

<style>
.show-tags {
    display: flex;
    flex-direction: column;
}
</style>