<script>
import { postData } from '../javascript/api.js'
import UsernameField from '../components/login/username.vue';
import EmailField from '../components/login/email.vue';
import PasswordField from '../components/login/password.vue';
import SubmitButton from '../components/login/SubmitButton.vue';
import RememberCheckbox from '../components/login/rememberme.vue'
import { setLocalStorageExpiry } from '../javascript/loggedin.js'
import { checkEmpty, validateForm, validatePasswords } from '../javascript/validateInput.js'
import { goBack } from '../javascript/goToPrevious';

export default {
    data() {
        return {
            form: {
                email: null,
                username: null,
                password: null,
                confirmPassword: null,
            },
            required: {
                "username": { 'min': 3, 'max': 20 },
                "password": { 'min': 5, 'max': 72 },
                "confirmPassword": { 'min': 5, 'max': 72 },
                "email": null
            },
            successMessage: null,
            errorMessage: null,
            buttonDisabled: true,
            buttonKey: 0
        }
    },
    components: { UsernameField, PasswordField, SubmitButton, RememberCheckbox, EmailField },
    methods: {
        onSubmit(values) {
            postData('users', this.form)
                .then(res => {
                    console.log(res)
                    const data = res.data
                    const id = data.data.id
                    setLocalStorageExpiry(id, this.form.username)
                    this.successMessage = 'Registered.'
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
                    const messageInError = errorData.hasOwnProperty("message")
                    if (messageInError) {
                        this.errorMessage = errorData.message
                    }
                })
        },
        validate(event) {
            const anyEmpty = checkEmpty(this.form, Object.keys(this.required))
            if (anyEmpty) {
                this.buttonDisabled = true
                this.rerenderButton()
                return
            }

            this.errorMessage = null
            const passwordsMatch = validatePasswords(this.form.password, this.form.confirmPassword)
            if (passwordsMatch) {
                this.errorMessage = passwordsMatch
                this.buttonDisabled = true
                this.rerenderButton()
                return
            }

            this.errorMessage = null
            const validatedForm = validateForm(this.form, this.required)
            if (validatedForm) {
                this.errorMessage = validatedForm
                this.buttonDisabled = true
                this.rerenderButton()
                return
            }

            console.log(this.form)
            this.errorMessage = null
            this.buttonDisabled = false
            this.rerenderButton()
        },
        rerenderButton() { this.buttonKey++ }
    }
}

</script>

<template>
    <div class="stack full-container">
        <div class="mb-5 p-3 font-bold text-lg">
            <h4 v-if="successMessage != null" class="success">{{ successMessage }}</h4>
            <p v-if="errorMessage != null" class="error">{{ errorMessage }}</p>
        </div>
        <div>
            <form class="stack" id="myForm" @submit.prevent="onSubmit" @input="validate" novalidate="novalidate">
                <UsernameField v-model.trim="form.username" :min="required['username'].min"
                    :max="required['username'].max" />
                <EmailField v-model.trim="form.email" />
                <PasswordField v-model="form.password" :min="required['password'].min"
                    :max="required['password'].max" />
                <PasswordField label="Confirm Password" v-model="form.confirmPassword" :min="required['password'].min"
                    :max="required['password'].max" />
                <SubmitButton :key="buttonKey" :disabled="buttonDisabled" @click="onSubmit">
                    <template #submit>
                        Register
                    </template>
                </SubmitButton>
            </form>
        </div>
    </div>
</template>

<style>
.checkbox {
    font-size: 1rem;
    line-height: 1.5rem;
    font-weight: 500;
    color: white;
    margin-top: 3%;
    border-radius: 0.375rem;
}
</style>