<script>
import ErrorField from './error.vue'
import { validateInput } from '../../javascript/validateInput.js'


export default {
    data() {
        return {
            errorMessage: null
        }
    },
    methods: {
        validate(event) {
            this.$emit('update:modelValue', event.target.value)
            this.errorMessage = validateInput(this.label, event.target.value, this.min, this.max)
        }
    },
    components: {
        ErrorField
    },
    props: {
        label: {
            type: String, default: 'Password'
        },
        modelValue: {
            type: String
        },
        min: {
            type: Number
        },
        max: {
            type: Number
        }
    }
}
</script>

<template>
    <div class="stack">
        <label class="loginLabels">{{ label }}:</label>
        <input type="password" :value="modelValue" @input="validate" />
        <ErrorField v-if="errorMessage != null" :error="errorMessage" />
    </div>
</template>