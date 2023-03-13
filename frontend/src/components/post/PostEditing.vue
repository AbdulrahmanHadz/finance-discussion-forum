<script>
import SubmitButton from '../login/SubmitButton.vue';
import SelectTagsCheckBoxes from './SelectTags.vue';
import { checkEmpty, validateForm } from '../../javascript/validateInput.js'
import ResultMessages from '../ResultMessages.vue';

export default {
    props: {
        form: {
            type: Object, required: false
        },
        tags: {
            type: Array, required: false
        },
        submitButtonLabel: {
            type: String, default: 'Create Post'
        }
    },
    created() {
        this.userId = this.form.userId
        this.selectedTags.push(...this.tags)
    },
    data() {
        return {
            required: {
                "title": { 'min': 5, 'max': 100 },
                "content": { 'min': 5, 'max': 4000 }
            },
            successMessage: null,
            errorMessage: null,
            buttonDisabled: false,
            buttonKey: 0,
            userId: null,
            selectedTags: [],
            unselectedTags: []
        }
    },
    components: {
        SubmitButton,
        SelectTagsCheckBoxes,
        ResultMessages
    },
    methods: {
        onSubmit() {
            this.$emit('update',
                { 'form': this.form, 'tagIdsToAdd': this.selectedTags, 'tagIdsToRemove': this.unselectedTags })
        },
        validate() {
            const anyEmpty = checkEmpty(this.form, Object.keys(this.required))
            if (anyEmpty) {
                this.errorMessage = anyEmpty
                return
            }

            this.errorMessage = null
            const validatedForm = validateForm(this.form, this.required)
            if (validatedForm) {
                this.errorMessage = validatedForm
                return
            }
        },
        updateTags(tagsArray) {
            this.selectedTags.push(...tagsArray.tagIdsToAdd)
            this.unselectedTags.push(...tagsArray.tagIdsToRemove)
        },
        cancelEdit() {
            this.$emit('cancel')
        }
    }

}
</script>

<template>
    <ResultMessages v-if="successMessage != null || errorMessage != null" :successMessage="successMessage"
        :errorMessage="errorMessage" />
    <div class="input-create-form-div">
        <form class="stack title-create-form" id="myForm" @submit.prevent="onSubmit" novalidate="novalidate">
            <div class="post-create-row">
                <p class="title-create">Title:</p>
                <input v-model="form.title" class="title-input-create" @input="validate" />
            </div>
            <div class="post-create-row">
                <p class="title-create">Message:</p>
                <textarea v-model="form.content" class="title-content-create" @input="validate" />
            </div>
            <SelectTagsCheckBoxes @update="updateTags" :passedTags="tags" />

            <div class="post-editing-buttons">
                <SubmitButton :key="buttonKey" :disabled="buttonDisabled" @click="cancelEdit" class="cancel-button">
                    <template #submit>
                        Cancel
                    </template>
                </SubmitButton>
                <!-- <v-btn elevation="2" color="red darken-2" @click="cancelEdit">Cancel</v-btn> -->
                <SubmitButton :key="buttonKey" :disabled="buttonDisabled" @click="onSubmit">
                    <template #submit>
                        {{ submitButtonLabel }}
                    </template>
                </SubmitButton>
            </div>
        </form>
    </div>
</template>

<style>
.title-content-create {
    background-color: var(--form-input);
    /* border: solid 2px black; */
    border-radius: 0.375rem;

    padding-left: 0.5rem;
    padding-right: 0.5rem;

    height: 50vh;
    width: 100%;
}

.post-editing-buttons {
    display: inline-block;
    justify-content: first baseline;
}

.cancel-button {
    background-color: var(--error);
    color: white;
}
</style>