<script>
import { ValidationProvider } from 'vee-validate';
const userError = ref(null);

export default {
    components: {
        ValidationProvider
    },
    emits: {
        validateUsername: (username) => {
            if (username) {
                if (username.length <= 5) {
                    console.log('Username should be at least 5 characters.')
                    userError.value = 'Username should be at least 5 characters.'
                    return false
                }
                userError.value = null
                return true
            }
            userError.value = 'Username cannot be empty.'
            return false
        }
    },

    methods: {
        validateUsername(username) {
            console.log(username)
            this.$emit('validateUsername', username)
        }
    }
}

</script>

<template>
    <div class="centeredDiv">
        <p class="py-2 text-white font-bold text-base">Username or Email:</p>
        <input v-model.trim="username" class="px-2 rounded-md" @input="validateUsername(username)">
        <p class="error text-rose-600 font-bold break-word text-xs border-2" v-if="userError != null">-
            {{
        userError
            }}
        </p>
    </div>
</template>