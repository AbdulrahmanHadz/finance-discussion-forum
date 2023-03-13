<script>
import ErrorField from './error.vue'
import { validateEmail } from '../../javascript/validateInput.js'


export default {
    data() {
        return {
            errorMessage: null
        }
    },
    methods: {
        validate(event) {
            this.errorMessage = validateEmail(this.label, event.target.value)
            if (this.errorMessage == null) {
                this.$emit('update:modelValue', event.target.value)
            }
        }
    },
    components: {
        ErrorField
    },
    props: {
        label: {
            type: String, default: 'Email'
        },
        modelValue: {
            type: String
        }
    }
}
</script>

<template>
    <div class="stack">
        <label class="loginLabels">{{ label }}:</label>
        <input type="email" :value="modelValue" @input="validate" />
        <ErrorField v-if="errorMessage != null" :error="errorMessage" />
    </div>
</template>