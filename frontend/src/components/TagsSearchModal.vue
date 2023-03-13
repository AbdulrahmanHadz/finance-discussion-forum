<script>
import CloseIcon from './icons/CloseIcon.vue'
import SelectTags from './post/SelectTags.vue';
import { tagsData } from '../javascript/tags.js';

export default {
    data() {
        return {
            tagsData: tagsData().tags,
            tags: new Set()
        }
    },
    created() {
        console.log(this.passedTags)
        this.passedTags.forEach(item => this.tags.add(item))
        console.log(this.tags)
    },
    methods: {
        close() {
            this.$emit('close');
        },
        updateInput(event) {
            let tag = event.target.value
            let checked = event.target.checked

            if (checked) {
                this.tags.add(tag)
            } else {
                this.tags.delete(tag)
            }
            this.$emit('update', this.tags)
        },
        tagChecked(tagId) {
            return this.tags.has(tagId)
        }
    },
    components: {
        CloseIcon,
        SelectTags
    },
    props: {
        passedTags: {
            type: Object, default: []
        }
    }
};
</script>

<template>
    <transition name="tag-search-fade">
        <div class="tag-search-backdrop">
            <div class="tag-search-base" id="search-tags" aria-label="Tags to search">
                <div class="tag-search-header">
                    <label class="tag-search-header">Tags to search</label>
                    <CloseIcon aria-label="Close Tag Search" class="btn-close" @click="close" />
                </div>
                <div class="tags-container">
                    <label v-for="tag in tagsData" @change="updateInput">
                        <div class="tag-checkbox-div">
                            <input type="checkbox" :value="tag.id" :id="tag.id" :checked="tagChecked(tag.id)">
                            <span>{{ tag.name }}</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </transition>
</template>

<style>
.tag-search-header {
    font-size: 1.25rem;
    line-height: 1.75rem;
    font-weight: 700;

    color: var(--header);
    justify-content: space-between;
    display: flex;
}


.tag-search-backdrop {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: rgba(0, 0, 0, 0.3);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    min-height: 10%;
}

.tag-search-base {
    background: #FFFFFF;
    box-shadow: 2px 2px 20px 1px;
    overflow-x: auto;
    display: flex;
    flex-direction: column;

    padding-top: 20px;
    padding-bottom: 20px;
    padding-right: 20px;
    padding-left: 20px;
    width: 50%;
    min-height: 10%;
    max-height: 90%;
    border-radius: 0.375rem;
}

.btn-close {
    cursor: pointer;
}

.tag-search-fade-enter,
.tag-search-fade-leave-to {
    opacity: 0;
}

.tag-search-fade-enter-active,
.tag-search-fade-leave-active {
    transition: opacity .7s ease;
}
</style>