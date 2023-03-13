<script>
export default {
    methods: {
        validate(event) {
            const value = event.target.value
            console.log(value)
            let parsed = false
            if (value == "true") {
                parsed = true
            } else {
                parsed = false
            }
            this.$emit('update:modelValue', parsed)
        }
    },
    props: {
        modelValue: {
            type: String
        }
    }
}
</script>

<template>
    <label class="checkbox">
        <input type="checkbox" :value="modelValue" @input="validate" true-value="true" false-value="false" />
        Remember Me
    </label>
</template>