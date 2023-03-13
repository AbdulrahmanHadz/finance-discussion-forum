<script>
import { fetchData, postData } from '../javascript/api.js'
import { loggedInId } from '../javascript/loggedin.js'
import ResultMessages from '../components/ResultMessages.vue';
import SubmitButton from '../components/login/SubmitButton.vue';
import { checkEmpty } from '../javascript/validateInput';
import { validateForm } from '../javascript/validateInput';
import { tagsData } from '../javascript/tags.js';
import { goBack } from '../javascript/goToPrevious';

export default {
    created() {
        this.form.userId = this.userId = loggedInId()
    },
    data() {
        return {
            form: {
                userId: null,
                name: null,
                description: null,
            },
            rules: {
                "name": { 'min': 1, 'max': 50 },
                "description": { 'min': 0, 'max': 255, required: false }
            },
            required: ['name'],
            successMessage: null,
            errorMessage: null
        }
    },
    components: {
        ResultMessages,
        SubmitButton
    },
    methods: {
        onSubmit() {
            if (this.errorMessage != null) {
                return
            }

            let error = false
            fetchData('tags', { 'name': this.form.name })
                .then(res => {
                    let data = res.data.data
                    if (data.length != 0) {
                        this.errorMessage = 'Name already taken.'
                        error = true
                        return
                    }

                    postData('tags', this.form)
                        .then(res => {
                            console.log(res)
                            this.successMessage = 'Tag Added'
                            tagsData().fetchTags()
                            setTimeout(() => {
                                this.successMessage = null
                                this.errorMessage = null
                                goBack(this.$router)
                            }, 2000);
                        })
                        .catch(err => {
                            console.log(err)
                            const errorResponse = err.response
                            const errorData = errorResponse.data

                            const messageInError = "message" in errorData
                            if (messageInError) {
                                this.errorMessage = errorData.message
                            }
                        })
                })
                .catch(err => {
                    console.log(err)
                    error = true
                })

            if (error) {
                return
            }
        },
        validate() {
            const anyEmpty = checkEmpty(this.form, this.required)
            if (anyEmpty) {
                this.errorMessage = anyEmpty
                return
            }

            this.errorMessage = null
            const validatedForm = validateForm(this.form, this.rules)
            if (validatedForm) {
                this.errorMessage = validatedForm
                return
            }
            this.errorMessage = null
        },
    }

}
</script>

<template>
    <div class="stack">
        <ResultMessages v-if="successMessage != null || errorMessage != null" :successMessage="successMessage"
            :errorMessage="errorMessage" />
        <div class="input-create-form-div">
            <form class="stack title-create-form" id="myForm" @submit.prevent="onSubmit" novalidate="novalidate">
                <div class="post-create-row">
                    <p class="title-create">Name:</p>
                    <input v-model="form.name" class="title-input-create" @input="validate" />
                </div>
                <div class="post-create-row">
                    <p class="title-create">Description:</p>
                    <textarea v-model="form.description" class="tag-description-create" @input="validate" />
                </div>
                <SubmitButton :disabled="false" @click="onSubmit">
                    <template #submit>
                        Add Tag
                    </template>
                </SubmitButton>
            </form>
        </div>
    </div>
</template>

<style>
.tag-description-create {
    background-color: var(--form-input);
    border-radius: 0.375rem;

    padding-left: 0.5rem;
    padding-right: 0.5rem;

    height: 5vh;
    width: 100%;
}
</style>