<script>
import { RouterLink } from 'vue-router';

export default {
    props: {
        to: { type: String, required: true },
        name: { type: String, required: true }
    },
    components: { RouterLink }
}

</script>
<template>
    <RouterLink :to="to" class="navLink">
        {{ name }}
    </RouterLink>

</template>

<style>
.navLink {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    padding-right: 0.75rem;
    padding-left: 0.75rem;
    text-transform: uppercase;
    font-weight: 700;

    color: var(--header-text);
}

.navLink:hover {
    opacity: 75%;
}
</style>