<script>
import { postData } from '../javascript/api.js'
import UsernameField from '../components/login/username.vue';
import PasswordField from '../components/login/password.vue';
import SubmitButton from '../components/login/SubmitButton.vue';
import RememberCheckbox from '../components/login/rememberme.vue'
import { setLocalStorageExpiry } from '../javascript/loggedin.js'
import { checkEmpty, validateForm } from '../javascript/validateInput.js'
import { goBack } from '../javascript/goToPrevious';


export default {
    data() {
        return {
            form: {
                username: null,
                password: null
            },
            required: {
                "username": { 'min': 3, 'max': 100 },
                "password": { 'min': 5, 'max': 72 }
            },
            successMessage: null,
            errorMessage: null,
            buttonDisabled: true,
            buttonKey: 0
        }
    },
    components: { UsernameField, PasswordField, SubmitButton, RememberCheckbox },
    methods: {
        onSubmit(values) {
            console.log(this.form)
            const anyEmpty = checkEmpty(Object.keys(this.form), Object.keys(this.required))
            this.errorMessage = anyEmpty
            if (anyEmpty) {
                return
            }

            postData('users/login', this.form)
                .then(res => {
                    console.log(res)
                    const data = res.data
                    const id = data.data.id
                    setLocalStorageExpiry(id, this.form.username)
                    this.successMessage = 'Logged In.'
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
        },
        validate(event) {
            const anyEmpty = checkEmpty(this.form, Object.keys(this.required))
            if (anyEmpty) {
                this.buttonDisabled = true
                this.rerenderButton()
                return
            }

            this.errorMessage = null
            const validatedForm = validateForm(this.form, this.required)
            console.log(validatedForm)
            if (validatedForm) {
                this.errorMessage = validatedForm
                this.buttonDisabled = true
                this.rerenderButton()
                return
            }

            console.log(this.form)
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
        <form class="stack" id="myForm" @submit.prevent="onSubmit" @input="validate" novalidate="novalidate">
            <UsernameField label="Username or Email" v-model.trim="form.username" :min="required['username'].min"
                :max="required['username'].max" />
            <PasswordField v-model="form.password" :min="required['password'].min" :max="required['password'].max" />
            <!-- <RememberCheckbox v-model="form.remember" /> -->
            <SubmitButton :key="buttonKey" :disabled="buttonDisabled" @click="onSubmit">
                <template #submit>
                    Login
                </template>
            </SubmitButton>
        </form>
    </div>
</template>
