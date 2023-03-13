<script>
import { formatRelativeDate } from '../../javascript/relativeTime.js'

export default {
    props: {
        user: {
            type: Object, required: true
        }
    },
    methods: {
        convertDateTime() {
            return formatRelativeDate(this.user.createdAt)
        }
    }
}
</script>

<template>
    <td class="userCard">
        <p class="username">{{ user.username }}</p>
        <p class="createdAt">Created: {{ convertDateTime() }}</p>
    </td>
</template>

<style>
.userCard {
    background-color: white;
    border-radius: 0.375rem;
    width: 30%;
    padding: 2%;
    vertical-align: top;
}

.username {
    font-size: 1.875rem;
    line-height: 2.25rem;
    font-weight: 700;
}

.createdAt {
    font-size: 0.875rem;
    line-height: 1.25rem;
    font-weight: 500;
}
</style>